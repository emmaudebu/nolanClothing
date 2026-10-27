<?php
/**
 * Checkout (CHECKOUT.md, brief §5.4, §5.5, §7): a one-page classic checkout shown as three
 * steps, with only the fields needed, Nigerian phone numbers, delivery areas as cards,
 * store pickup with a branch picker (off until the client confirms it), restyled payment
 * methods, and the pickup branch on the order screen, emails and thank-you page.
 *
 * Layout: woocommerce/checkout/form-checkout.php, review-order.php and thankyou.php.
 * Behaviour: assets/js/checkout.js. Styles: assets/css/checkout.css.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * The checkout form itself, not the thank-you or pay-for-order pages.
 *
 * @return bool
 */
function nolasharon_is_checkout_form() {
	return is_checkout() && ! is_order_received_page() && ! is_checkout_pay_page();
}

/* Assets ------------------------------------------------------------------ */

/**
 * Checkout styles and script, only on the checkout pages. The state list uses the
 * native select, so WooCommerce's selectWoo is not needed here.
 */
function nolasharon_checkout_assets() {
	if ( ! is_checkout() ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-checkout', get_template_directory_uri() . '/assets/css/checkout.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_dequeue_script( 'selectWoo' );
	wp_dequeue_style( 'select2' );

	if ( ! nolasharon_is_checkout_form() ) {
		return;
	}
	// jQuery only to listen for and trigger WooCommerce's own checkout events.
	wp_enqueue_script( 'nolasharon-checkout', get_template_directory_uri() . '/assets/js/checkout.js', array( 'jquery', 'wc-checkout' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_add_inline_script(
		'nolasharon-checkout',
		'window.nsCheckout = ' . wp_json_encode(
			array(
				'qtyUrl'   => WC_AJAX::get_endpoint( 'ns_update_qty' ),
				'qtyNonce' => wp_create_nonce( 'ns-update-qty' ),
				'pickup'   => (bool) nolasharon_pickup_branches(),
				'payNotes' => nolasharon_payment_notes(),
				'i18n'     => array(
					'billing_first_name' => __( 'Please enter your first name.', 'nolasharon' ),
					'billing_last_name'  => __( 'Please enter your last name.', 'nolasharon' ),
					'billing_email'      => __( 'Please enter a valid email address.', 'nolasharon' ),
					'billing_phone'      => __( 'Please enter a valid Nigerian phone number, e.g. 0801 234 5678.', 'nolasharon' ),
					'billing_address_1'  => __( 'Please enter your delivery address.', 'nolasharon' ),
					'billing_city'       => __( 'Please enter your city or area.', 'nolasharon' ),
					'billing_state'      => __( 'Please choose your state.', 'nolasharon' ),
					'shipping_method'    => __( 'Please choose a delivery option.', 'nolasharon' ),
					'ns_pickup_branch'   => __( 'Please choose the branch you will collect from.', 'nolasharon' ),
					'required'           => __( 'This field is required.', 'nolasharon' ),
					/* translators: %s: branch name. */
					'pickupSummary'      => __( 'Store pickup · %s', 'nolasharon' ),
					/* translators: %s: order total. */
					'pay'                => __( 'Pay %s securely', 'nolasharon' ),
					/* translators: %s: order total. */
					'place'              => __( 'Place order · %s', 'nolasharon' ),
					'processing'         => __( 'Processing payment…', 'nolasharon' ),
					'qtyError'           => __( 'Sorry, the quantity could not be updated. Please try again.', 'nolasharon' ),
				),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_checkout_assets', 20 );

/**
 * Mark the page as script-enabled before first paint, so the steps start collapsed
 * instead of jumping closed once checkout.js runs. Without JavaScript every step stays open.
 */
function nolasharon_checkout_js_flag() {
	if ( nolasharon_is_checkout_form() ) {
		echo "<script>document.documentElement.classList.add('ns-co-js');</script>\n";
	}
}
add_action( 'wp_head', 'nolasharon_checkout_js_flag', 2 );

/**
 * Once the order is placed, forget the checkout step remembered for reloads.
 */
function nolasharon_checkout_forget_step() {
	if ( is_order_received_page() ) {
		echo "<script>try{sessionStorage.removeItem('ns-checkout-step');}catch(e){}</script>\n";
	}
}
add_action( 'wp_footer', 'nolasharon_checkout_forget_step' );

/* Fields ------------------------------------------------------------------ */

/**
 * Nigerian address labels. WooCommerce's address script re-applies these labels in the
 * browser, so they are set here as well as on the fields.
 *
 * @param array $locale Country address locales.
 * @return array
 */
function nolasharon_country_locale( $locale ) {
	$locale['NG']['postcode']  = array(
		'required' => false,
		'hidden'   => true,
	);
	$locale['NG']['address_1'] = array(
		'label'       => __( 'Delivery address', 'nolasharon' ),
		'placeholder' => __( 'House number, street name', 'nolasharon' ),
	);
	$locale['NG']['city']      = array(
		'label'       => __( 'City / area', 'nolasharon' ),
		'placeholder' => __( 'e.g. Ogudu, Ikeja, Lekki', 'nolasharon' ),
	);
	$locale['NG']['state']     = array(
		'label' => __( 'State', 'nolasharon' ),
	);
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'nolasharon_country_locale' );

/**
 * Checkout fields (CHECKOUT.md §3): contact details, then the delivery address.
 * Company, address line 2 and postcode are removed; the country is fixed to Nigeria.
 * The address is not required when the shopper collects from a branch.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function nolasharon_checkout_fields( $fields ) {
	unset(
		$fields['billing']['billing_company'],
		$fields['billing']['billing_address_2'],
		$fields['billing']['billing_postcode'],
		$fields['shipping']['shipping_company']
	);

	$billing = &$fields['billing'];

	$billing['billing_first_name']['autocomplete'] = 'given-name';
	$billing['billing_last_name']['autocomplete']  = 'family-name';

	$billing['billing_email'] = array_merge(
		$billing['billing_email'],
		array(
			'type'         => 'email',
			'autocomplete' => 'email',
			'placeholder'  => __( 'you@example.com', 'nolasharon' ),
			'class'        => array( 'form-row-first' ),
		)
	);

	$billing['billing_phone'] = array_merge(
		$billing['billing_phone'],
		array(
			'label'             => __( 'Phone number', 'nolasharon' ),
			'type'              => 'tel',
			'required'          => true,
			'autocomplete'      => 'tel-national',
			'placeholder'       => __( '801 234 5678', 'nolasharon' ),
			'class'             => array( 'form-row-last', 'ns-phone-row' ),
			'custom_attributes' => array( 'inputmode' => 'tel' ),
		)
	);

	if ( isset( $billing['billing_country'] ) ) {
		// Nigeria only: the field stays in the form (WooCommerce needs it) but is not shown.
		$billing['billing_country']['class'][] = 'ns-row-hidden';
		$billing['billing_country']['default'] = 'NG';
	}

	$billing['billing_address_1'] = array_merge(
		$billing['billing_address_1'],
		array(
			'label'        => __( 'Delivery address', 'nolasharon' ),
			'placeholder'  => __( 'House number, street name', 'nolasharon' ),
			'autocomplete' => 'street-address',
			'class'        => array( 'form-row-wide', 'address-field' ),
		)
	);
	$billing['billing_city']  = array_merge(
		$billing['billing_city'],
		array(
			'label'        => __( 'City / area', 'nolasharon' ),
			'placeholder'  => __( 'e.g. Ogudu, Ikeja, Lekki', 'nolasharon' ),
			'autocomplete' => 'address-level2',
			'class'        => array( 'form-row-first', 'address-field' ),
		)
	);
	$billing['billing_state'] = array_merge(
		$billing['billing_state'],
		array(
			'label'    => __( 'State', 'nolasharon' ),
			'required' => true,
			'class'    => array( 'form-row-last', 'address-field' ),
		)
	);

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Delivery instructions', 'nolasharon' );
		$fields['order']['order_comments']['placeholder'] = __( 'Landmark, gate colour, best time to call…', 'nolasharon' );
		$fields['order']['order_comments']['custom_attributes'] = array( 'rows' => 2 );
	}

	if ( nolasharon_is_pickup_method( nolasharon_chosen_method() ) ) {
		foreach ( array( 'billing_address_1', 'billing_city', 'billing_state' ) as $key ) {
			if ( isset( $billing[ $key ] ) ) {
				$billing[ $key ]['required'] = false;
			}
		}
	}
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'nolasharon_checkout_fields', 20 );

/**
 * Default the state to Lagos.
 *
 * @param string $value Default state.
 * @return string
 */
function nolasharon_default_checkout_state( $value ) {
	return $value ? $value : 'LA';
}
add_filter( 'default_checkout_billing_state', 'nolasharon_default_checkout_state' );

/**
 * The phone field: a fixed "+234" box in front of the input.
 *
 * @param string $field Field HTML.
 * @param string $key   Field key.
 * @return string
 */
function nolasharon_phone_prefix( $field, $key ) {
	if ( 'billing_phone' !== $key ) {
		return $field;
	}
	return preg_replace(
		'/(<span class="woocommerce-input-wrapper">)/',
		'$1<span class="ns-phone-prefix" aria-hidden="true">+234</span>',
		$field,
		1
	);
}
add_filter( 'woocommerce_form_field', 'nolasharon_phone_prefix', 10, 2 );

/**
 * Show a saved +234 number without the prefix, since the prefix box already shows it.
 *
 * @param mixed  $value Field value.
 * @param string $input Field key.
 * @return mixed
 */
function nolasharon_checkout_phone_value( $value, $input ) {
	if ( 'billing_phone' === $input && null === $value && WC()->customer ) {
		$phone = nolasharon_ng_phone( WC()->customer->get_billing_phone() );
		return $phone ? substr( $phone, 4 ) : null;
	}
	return $value;
}
add_filter( 'woocommerce_checkout_get_value', 'nolasharon_checkout_phone_value', 10, 2 );

/**
 * Save the phone number as +234XXXXXXXXXX whatever format was typed.
 *
 * @param array $data Posted checkout data.
 * @return array
 */
function nolasharon_checkout_posted_data( $data ) {
	if ( ! empty( $data['billing_phone'] ) ) {
		$phone = nolasharon_ng_phone( $data['billing_phone'] );
		if ( $phone ) {
			$data['billing_phone'] = $phone;
		}
	}
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'nolasharon_checkout_posted_data' );

/**
 * Validate the phone number and the pickup branch. Errors carry the field ID so the
 * checkout can open the step that holds it.
 *
 * @param array    $data   Posted checkout data.
 * @param WP_Error $errors Validation errors.
 */
function nolasharon_checkout_validate( $data, $errors ) {
	if ( '' !== $data['billing_phone'] && ! nolasharon_ng_phone( $data['billing_phone'] ) ) {
		$errors->add( 'billing_phone_validation', __( 'Please enter a valid Nigerian phone number, for example 0801 234 5678.', 'nolasharon' ), array( 'id' => 'billing_phone' ) );
	}
	if ( nolasharon_is_pickup_method( nolasharon_chosen_method() ) && ! nolasharon_posted_pickup_branch() ) {
		$errors->add( 'ns_pickup_branch_required', __( 'Please choose the branch you will collect your order from.', 'nolasharon' ), array( 'id' => 'ns_pickup_branch' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'nolasharon_checkout_validate', 10, 2 );

/* Delivery and pickup ------------------------------------------------------ */

/**
 * Whether a shipping method ID is store pickup.
 *
 * @param string $method_id Method or rate ID, e.g. "local_pickup:3".
 * @return bool
 */
function nolasharon_is_pickup_method( $method_id ) {
	foreach ( array( 'local_pickup', 'pickup_location', 'ns_branch_pickup' ) as $prefix ) {
		if ( 0 === strpos( (string) $method_id, $prefix ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Treat the theme's pickup rate like WooCommerce's own Local Pickup (no shipping address, store-based tax).
 *
 * @param string[] $methods Pickup method IDs.
 * @return string[]
 */
function nolasharon_local_pickup_methods( $methods ) {
	$methods[] = 'ns_branch_pickup';
	return $methods;
}
add_filter( 'woocommerce_local_pickup_methods', 'nolasharon_local_pickup_methods' );

/**
 * The shipping method chosen for this checkout request.
 *
 * @return string
 */
function nolasharon_chosen_method() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
	if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) && is_scalar( reset( $_POST['shipping_method'] ) ) ) {
		return wc_clean( wp_unslash( reset( $_POST['shipping_method'] ) ) );
	}
	// phpcs:enable
	$chosen = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();
	return $chosen ? (string) reset( $chosen ) : '';
}

/**
 * Store pickup rates: hidden until pickup is switched on and a branch offers it. When it is on,
 * pickup is offered in every state (it does not depend on the delivery zone), free of charge.
 *
 * @param WC_Shipping_Rate[] $rates Rates.
 * @return WC_Shipping_Rate[]
 */
function nolasharon_filter_pickup_rates( $rates ) {
	$enabled    = (bool) nolasharon_pickup_branches();
	$has_pickup = false;
	foreach ( $rates as $id => $rate ) {
		if ( nolasharon_is_pickup_method( $rate->get_method_id() ) ) {
			if ( $enabled ) {
				$has_pickup = true;
			} else {
				unset( $rates[ $id ] );
			}
		}
	}
	if ( $enabled && ! $has_pickup ) {
		$rates['ns_branch_pickup'] = new WC_Shipping_Rate( 'ns_branch_pickup', __( 'Store pickup', 'nolasharon' ), 0, array(), 'ns_branch_pickup' );
	}
	return $rates;
}
add_filter( 'woocommerce_package_rates', 'nolasharon_filter_pickup_rates', 20 );

/**
 * A short area note for each flat rate (e.g. "Ikeja, Ogudu, Yaba…"), shown on its card at checkout.
 *
 * @param array $fields Instance settings fields.
 * @return array
 */
function nolasharon_flat_rate_note_field( $fields ) {
	$fields['ns_note'] = array(
		'title'       => __( 'Checkout note (NolaSharon)', 'nolasharon' ),
		'type'        => 'text',
		'description' => __( 'Shown under the name at checkout, e.g. the areas this rate covers. Confirmed details only; leave empty to hide it.', 'nolasharon' ),
		'default'     => '',
		'desc_tip'    => true,
	);
	return $fields;
}
add_filter( 'woocommerce_shipping_instance_form_fields_flat_rate', 'nolasharon_flat_rate_note_field' );

/**
 * The checkout note for a rate, if one is set.
 *
 * @param WC_Shipping_Rate $rate Rate.
 * @return string
 */
function nolasharon_rate_note( $rate ) {
	if ( ! $rate->get_instance_id() ) {
		return '';
	}
	$method = WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );
	return $method ? trim( nolasharon_strip_placeholders( (string) $method->get_option( 'ns_note', '' ) ) ) : '';
}

/**
 * A rate's label for shoppers, without placeholders.
 *
 * @param WC_Shipping_Rate $rate Rate.
 * @return string
 */
function nolasharon_rate_label( $rate ) {
	$label = trim( nolasharon_strip_placeholders( $rate->get_label() ) );
	return '' !== $label ? $label : __( 'Delivery', 'nolasharon' );
}

/**
 * A rate's cost as shown to shoppers: "Free" only for pickup, otherwise the price.
 *
 * @param WC_Shipping_Rate $rate Rate.
 * @return string HTML.
 */
function nolasharon_rate_cost_html( $rate ) {
	$cost = (float) $rate->get_cost();
	if ( WC()->cart->display_prices_including_tax() ) {
		$cost += (float) $rate->get_shipping_tax();
	}
	if ( $cost <= 0 && nolasharon_is_pickup_method( $rate->get_method_id() ) ) {
		return '<span class="ns-free">' . esc_html__( 'Free', 'nolasharon' ) . '</span>';
	}
	return wc_price( $cost );
}

/**
 * The delivery area cards (the shipping methods for the chosen state) and the hidden pickup
 * rate that the Delivery/Pickup toggle selects. Refreshed with WooCommerce's checkout updates.
 */
function nolasharon_checkout_shipping_options() {
	echo '<div class="ns-ship-options">';
	if ( WC()->cart->needs_shipping() ) {
		$chosen = (array) WC()->session->get( 'chosen_shipping_methods', array() );
		foreach ( WC()->shipping()->get_packages() as $i => $package ) {
			$rates    = isset( $package['rates'] ) ? $package['rates'] : array();
			$selected = isset( $chosen[ $i ] ) ? $chosen[ $i ] : '';
			$delivery = array_filter(
				$rates,
				function ( $rate ) {
					return ! nolasharon_is_pickup_method( $rate->get_method_id() );
				}
			);
			$pickup   = array_diff_key( $rates, $delivery );

			if ( $delivery ) {
				echo '<div class="ns-options ns-options--areas" role="radiogroup" aria-labelledby="ns-areas-label">';
				foreach ( $delivery as $rate ) {
					nolasharon_shipping_card( $rate, $i, $selected );
				}
				echo '</div>';
			} else {
				echo '<p class="ns-options__empty">' . wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no delivery options for this address yet. Please check your state, or contact us.', 'nolasharon' ) ) ) . '</p>';
			}
			foreach ( $pickup as $rate ) {
				nolasharon_shipping_card( $rate, $i, $selected, true );
			}
		}
	}
	echo '</div>';
}

/**
 * One shipping option card, wrapping WooCommerce's own radio input.
 *
 * @param WC_Shipping_Rate $rate     Rate.
 * @param int              $index    Package index.
 * @param string           $selected Chosen rate ID.
 * @param bool             $pickup   Whether this is the pickup rate.
 */
function nolasharon_shipping_card( $rate, $index, $selected, $pickup = false ) {
	$id   = 'shipping_method_' . $index . '_' . sanitize_title( $rate->get_id() );
	$note = $pickup ? '' : nolasharon_rate_note( $rate );
	?>
	<label class="ns-option<?php echo $pickup ? ' ns-option--pickup-rate' : ''; ?>" for="<?php echo esc_attr( $id ); ?>">
		<input type="radio" class="shipping_method ns-option__input" name="shipping_method[<?php echo esc_attr( $index ); ?>]" data-index="<?php echo esc_attr( $index ); ?>" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $rate->get_id() ); ?>" <?php checked( $rate->get_id(), $selected ); ?><?php echo $pickup ? ' data-pickup-rate' : ''; ?> data-label="<?php echo esc_attr( nolasharon_rate_label( $rate ) ); ?>">
		<span class="ns-option__top">
			<span class="ns-option__title"><?php echo esc_html( nolasharon_rate_label( $rate ) ); ?></span>
			<span class="ns-option__dot" aria-hidden="true"></span>
		</span>
		<?php if ( $note ) : ?>
			<span class="ns-option__note"><?php echo esc_html( $note ); ?></span>
		<?php endif; ?>
		<span class="ns-option__price"><?php echo wp_kses_post( nolasharon_rate_cost_html( $rate ) ); ?></span>
		<?php do_action( 'woocommerce_after_shipping_rate', $rate, $index ); ?>
	</label>
	<?php
}

/**
 * Keep the delivery cards in step with WooCommerce's checkout updates.
 *
 * @param array $fragments Fragments.
 * @return array
 */
function nolasharon_checkout_fragments( $fragments ) {
	ob_start();
	nolasharon_checkout_shipping_options();
	$fragments['.ns-ship-options'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_update_order_review_fragments', 'nolasharon_checkout_fragments' );

/**
 * The pickup branch posted with the checkout, if it is a valid pickup branch.
 *
 * @return array|null
 */
function nolasharon_posted_pickup_branch() {
	$id = isset( $_POST['ns_pickup_branch'] ) ? absint( $_POST['ns_pickup_branch'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
	foreach ( nolasharon_pickup_branches() as $branch ) {
		if ( $branch['id'] === $id ) {
			return $branch;
		}
	}
	return null;
}

/**
 * Branch cards for store pickup. Empty fields are left out.
 */
function nolasharon_checkout_branch_options() {
	$posted = isset( $_POST['ns_pickup_branch'] ) ? absint( $_POST['ns_pickup_branch'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only, for redisplay.
	?>
	<div class="ns-options ns-options--branches form-row validate-required" id="ns_pickup_branch_field" role="radiogroup" aria-labelledby="ns-branches-label">
		<?php foreach ( nolasharon_pickup_branches() as $branch ) : ?>
			<?php
			$id   = 'ns_pickup_branch_' . $branch['id'];
			$name = $branch['tag'] ? $branch['name'] . ' (' . $branch['tag'] . ')' : $branch['name'];
			?>
			<label class="ns-option ns-option--row" for="<?php echo esc_attr( $id ); ?>">
				<input type="radio" class="ns-option__input" name="ns_pickup_branch" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $branch['id'] ); ?>" data-label="<?php echo esc_attr( $name ); ?>" <?php checked( $branch['id'], $posted ); ?>>
				<span class="ns-option__dot" aria-hidden="true"></span>
				<span class="ns-option__body">
					<span class="ns-option__titlerow">
						<span class="ns-option__title"><?php echo esc_html( $branch['name'] ); ?></span>
						<?php if ( $branch['is_new'] ) : ?>
							<span class="ns-pill ns-pill--new"><?php echo esc_html( $branch['tag'] ? $branch['tag'] : __( 'New', 'nolasharon' ) ); ?></span>
						<?php elseif ( $branch['tag'] || $branch['area'] ) : ?>
							<span class="ns-pill"><?php echo esc_html( $branch['tag'] ? $branch['tag'] : $branch['area'] ); ?></span>
						<?php endif; ?>
					</span>
					<?php if ( $branch['address'] ) : ?>
						<span class="ns-option__note"><?php echo esc_html( preg_replace( '/\s*\n\s*/', ', ', $branch['address'] ) ); ?></span>
					<?php endif; ?>
					<?php if ( $branch['hours_text'] ) : ?>
						<span class="ns-option__meta"><?php echo esc_html( str_replace( "\n", ' · ', $branch['hours_text'] ) ); ?></span>
					<?php endif; ?>
				</span>
				<span class="ns-free"><?php esc_html_e( 'Free', 'nolasharon' ); ?></span>
			</label>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Address, hours and instructions for a pickup branch.
 *
 * @param array $branch Branch.
 */
function nolasharon_pickup_branch_details( $branch ) {
	foreach ( array(
		'address'             => __( 'Address', 'nolasharon' ),
		'hours_text'          => __( 'Opening hours', 'nolasharon' ),
		'phone'               => __( 'Phone', 'nolasharon' ),
		'pickup_instructions' => __( 'Pickup instructions', 'nolasharon' ),
	) as $key => $label ) {
		if ( $branch[ $key ] ) {
			printf( '<p><strong>%1$s:</strong> %2$s</p>', esc_html( $label ), nl2br( esc_html( $branch[ $key ] ) ) );
		}
	}
}

/**
 * Save the pickup branch on the order (HPOS-safe order meta).
 *
 * @param WC_Order $order Order.
 */
function nolasharon_save_pickup_branch( $order ) {
	if ( ! nolasharon_is_pickup_method( nolasharon_chosen_method() ) ) {
		return;
	}
	$branch = nolasharon_posted_pickup_branch();
	if ( $branch ) {
		$order->update_meta_data( '_ns_pickup_branch', $branch['id'] );
		$order->update_meta_data( '_ns_pickup_branch_name', $branch['tag'] ? $branch['name'] . ' (' . $branch['tag'] . ')' : $branch['name'] );
	}
}
add_action( 'woocommerce_checkout_create_order', 'nolasharon_save_pickup_branch' );

/**
 * The pickup branch on an order, using the current branch details.
 *
 * @param WC_Order $order Order.
 * @return array|null
 */
function nolasharon_order_pickup_branch( $order ) {
	$id = (int) $order->get_meta( '_ns_pickup_branch' );
	if ( ! $id ) {
		return null;
	}
	$post = get_post( $id );
	if ( $post && 'ns_branch' === $post->post_type && 'trash' !== $post->post_status ) {
		return ns_branch_data( $post );
	}
	// Branch since removed: fall back to the name saved with the order.
	return array(
		'id'                  => $id,
		'name'                => $order->get_meta( '_ns_pickup_branch_name' ),
		'address'             => '',
		'hours_text'          => '',
		'phone'               => '',
		'pickup_instructions' => '',
	);
}

/**
 * A pickup branch's name with its tag, e.g. "NolaSharon Ikeja (Branch 1)".
 *
 * @param array $branch Branch from nolasharon_order_pickup_branch().
 * @return string
 */
function nolasharon_pickup_branch_name( $branch ) {
	return empty( $branch['tag'] ) ? $branch['name'] : $branch['name'] . ' (' . $branch['tag'] . ')';
}

/**
 * Pickup branch on the admin order screen.
 *
 * @param WC_Order $order Order.
 */
function nolasharon_admin_pickup_branch( $order ) {
	$branch = nolasharon_order_pickup_branch( $order );
	if ( $branch ) {
		echo '<div class="ns-admin-pickup"><h3>' . esc_html__( 'Store pickup', 'nolasharon' ) . '</h3><p><strong>' . esc_html( nolasharon_pickup_branch_name( $branch ) ) . '</strong></p>';
		nolasharon_pickup_branch_details( $branch );
		echo '</div>';
	}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'nolasharon_admin_pickup_branch' );

/**
 * Pickup branch in the customer and admin order emails.
 *
 * @param WC_Order $order         Order.
 * @param bool     $sent_to_admin Whether the email goes to the shop.
 * @param bool     $plain_text    Plain-text email.
 */
function nolasharon_email_pickup_branch( $order, $sent_to_admin, $plain_text ) {
	$branch = nolasharon_order_pickup_branch( $order );
	if ( ! $branch ) {
		return;
	}
	if ( $plain_text ) {
		echo "\n" . esc_html__( 'Store pickup:', 'nolasharon' ) . ' ' . esc_html( nolasharon_pickup_branch_name( $branch ) ) . "\n";
		foreach ( array( 'address', 'hours_text', 'pickup_instructions' ) as $key ) {
			if ( $branch[ $key ] ) {
				echo esc_html( $branch[ $key ] ) . "\n";
			}
		}
		return;
	}
	echo '<h2>' . esc_html__( 'Store pickup', 'nolasharon' ) . '</h2><p><strong>' . esc_html( nolasharon_pickup_branch_name( $branch ) ) . '</strong></p>';
	nolasharon_pickup_branch_details( $branch );
}
add_action( 'woocommerce_email_order_meta', 'nolasharon_email_pickup_branch', 20, 3 );

/* Payment ----------------------------------------------------------------- */

// Payment methods sit in step 3, apart from the order summary.
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
// No coupon box at checkout until the client asks for coupons ([[CONFIRM: extras]] in CLAUDE.md).
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
// The Terms link opens the Terms page instead of expanding the page inline.
remove_action( 'woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30 );

/**
 * Small chips beside each payment method, in place of the gateway's logo image.
 *
 * @return array Gateway ID => chip labels.
 */
function nolasharon_payment_chips() {
	return apply_filters(
		'nolasharon_payment_chips',
		array(
			'paystack' => array( __( 'Card', 'nolasharon' ), __( 'Transfer', 'nolasharon' ), __( 'USSD', 'nolasharon' ) ),
			'bacs'     => array( __( 'Manual', 'nolasharon' ) ),
		)
	);
}

/**
 * Reassurance text under the payment methods, per gateway.
 *
 * @return array Gateway ID => text.
 */
function nolasharon_payment_notes() {
	return apply_filters(
		'nolasharon_payment_notes',
		array(
			'paystack' => __( 'You will be taken to Paystack’s secure page to complete payment. We never see or store your card details.', 'nolasharon' ),
			'bacs'     => __( 'Our account details will be shown after you place your order. Your order is confirmed once we receive payment.', 'nolasharon' ),
		)
	);
}

/**
 * Payment method chips at checkout.
 *
 * @param string $icon       Icon HTML.
 * @param string $gateway_id Gateway ID.
 * @return string
 */
function nolasharon_gateway_chips( $icon, $gateway_id ) {
	$chips = nolasharon_payment_chips();
	if ( ! is_checkout() || empty( $chips[ $gateway_id ] ) ) {
		return $icon;
	}
	$html = '<span class="ns-chips">';
	foreach ( $chips[ $gateway_id ] as $chip ) {
		$html .= '<span class="ns-chip">' . esc_html( $chip ) . '</span>';
	}
	return $html . '</span>';
}
add_filter( 'woocommerce_gateway_icon', 'nolasharon_gateway_chips', 20, 2 );

/**
 * The payment method WooCommerce will select: the shopper's choice, else the first available.
 *
 * @return string
 */
function nolasharon_chosen_gateway() {
	$chosen    = WC()->session ? (string) WC()->session->get( 'chosen_payment_method' ) : '';
	$available = WC()->payment_gateways() ? WC()->payment_gateways()->get_available_payment_gateways() : array();
	if ( $chosen && isset( $available[ $chosen ] ) ) {
		return $chosen;
	}
	return $available ? (string) key( $available ) : '';
}

/**
 * Reassurance box under the payment methods, above the terms (switched by checkout.js
 * when the method changes; hidden for methods without a note).
 */
function nolasharon_payment_note() {
	$notes = nolasharon_payment_notes();
	$note  = isset( $notes[ nolasharon_chosen_gateway() ] ) ? $notes[ nolasharon_chosen_gateway() ] : '';
	?>
	<div class="ns-assure" data-pay-note<?php echo $note ? '' : ' hidden'; ?>>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>
		<span data-pay-note-text><?php echo esc_html( $note ); ?></span>
	</div>
	<?php
}
add_action( 'woocommerce_checkout_before_terms_and_conditions', 'nolasharon_payment_note' );

/**
 * Terms checkbox: Terms & Conditions and the Returns & Exchange Policy, both linked.
 *
 * @return string
 */
function nolasharon_terms_checkbox_text() {
	return sprintf(
		/* translators: 1: Terms & Conditions link, 2: Returns & Exchange Policy link. */
		__( 'I agree to the %1$s and %2$s.', 'nolasharon' ),
		'<a href="' . esc_url( get_permalink( wc_terms_and_conditions_page_id() ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Terms & Conditions', 'nolasharon' ) . '</a>',
		'<a href="' . esc_url( nolasharon_page_url( 'returns-policy' ) ) . '" target="_blank" rel="noopener">' . esc_html__( 'Returns & Exchange Policy', 'nolasharon' ) . '</a>'
	);
}
add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', 'nolasharon_terms_checkbox_text' );

/**
 * Place order label: "Pay ₦X securely" for Paystack, "Place order · ₦X" otherwise.
 * checkout.js keeps it current as the total or the payment method changes.
 *
 * @return string
 */
function nolasharon_order_button_label() {
	$total = WC()->cart ? html_entity_decode( wp_strip_all_tags( WC()->cart->get_total() ), ENT_QUOTES, 'UTF-8' ) : '';
	/* translators: %s: order total. */
	$format = 'paystack' === nolasharon_chosen_gateway() ? __( 'Pay %s securely', 'nolasharon' ) : __( 'Place order · %s', 'nolasharon' );
	return sprintf( $format, $total );
}
add_filter( 'woocommerce_order_button_text', 'nolasharon_order_button_label' );

/**
 * Place order button with a lock icon and the processing fill.
 *
 * @return string
 */
function nolasharon_order_button_html() {
	$label = nolasharon_order_button_label();
	return '<button type="submit" class="button alt ns-place" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $label ) . '" data-value="' . esc_attr( $label ) . '">'
		. '<span class="ns-place__fill" aria-hidden="true"></span>'
		. '<svg class="ns-place__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>'
		. '<span class="ns-place__label">' . esc_html( $label ) . '</span></button>';
}
add_filter( 'woocommerce_order_button_html', 'nolasharon_order_button_html' );

/* Order summary quantity --------------------------------------------------- */

/**
 * AJAX: change a cart line's quantity from the order summary (`?wc-ajax=ns_update_qty`).
 * At least 1, at most what is in stock. The checkout then refreshes its totals.
 */
function nolasharon_ajax_update_qty() {
	check_ajax_referer( 'ns-update-qty', 'security' );
	$key  = nolasharon_request_text( $_POST, 'key' );
	$qty  = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 0;
	$item = WC()->cart->get_cart_item( $key );
	if ( ! $item || $qty < 1 ) {
		wp_send_json_error();
	}
	$max = $item['data']->get_max_purchase_quantity();
	if ( $max > 0 ) {
		$qty = min( $qty, $max );
	}
	WC()->cart->set_quantity( $key, $qty );
	wp_send_json_success( array( 'qty' => $qty ) );
}
add_action( 'wc_ajax_ns_update_qty', 'nolasharon_ajax_update_qty' );

/* Thank-you page ----------------------------------------------------------- */

/**
 * How to reach support (phone, WhatsApp, email), each shown only when set.
 *
 * @param WC_Order $order Order.
 * @return array[] Each: array( label, url ).
 */
function nolasharon_order_support_links( $order ) {
	$links    = array();
	$whatsapp = nolasharon_whatsapp_url(
		/* translators: %s: order number. */
		sprintf( __( 'Hi, I have a question about order #%s', 'nolasharon' ), $order->get_order_number() )
	);
	$phone    = nolasharon_setting( 'nolasharon_phone' );
	$email    = nolasharon_setting( 'nolasharon_email' );
	if ( $whatsapp ) {
		$links[] = array( __( 'WhatsApp', 'nolasharon' ), $whatsapp );
	}
	if ( $phone ) {
		$links[] = array( $phone, nolasharon_tel_url( $phone ) );
	}
	if ( $email ) {
		$links[] = array( $email, 'mailto:' . $email );
	}
	return $links;
}

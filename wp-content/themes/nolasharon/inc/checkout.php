<?php
/**
 * Checkout (brief §5.4, §5.5, §7): only the fields needed, Nigerian phone numbers,
 * delivery or store pickup with a branch selector, and the pickup branch on the
 * order screen, emails and thank-you page. Pickup stays off until the client confirms it.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Whether a shipping method ID is store pickup.
 *
 * @param string $method_id Method or rate ID, e.g. "local_pickup:3".
 * @return bool
 */
function nolasharon_is_pickup_method( $method_id ) {
	return 0 === strpos( (string) $method_id, 'local_pickup' ) || 0 === strpos( (string) $method_id, 'pickup_location' );
}

/**
 * The shipping method chosen for this checkout request.
 *
 * @return string
 */
function nolasharon_chosen_method() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce verifies the checkout nonce.
	if ( isset( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
		return wc_clean( wp_unslash( reset( $_POST['shipping_method'] ) ) );
	}
	// phpcs:enable
	$chosen = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods', array() ) : array();
	return $chosen ? (string) reset( $chosen ) : '';
}

/**
 * Hide pickup rates until pickup is switched on and at least one branch offers it.
 *
 * @param WC_Shipping_Rate[] $rates Rates.
 * @return WC_Shipping_Rate[]
 */
function nolasharon_filter_pickup_rates( $rates ) {
	if ( nolasharon_pickup_branches() ) {
		return $rates;
	}
	foreach ( $rates as $id => $rate ) {
		if ( nolasharon_is_pickup_method( $rate->get_method_id() ) ) {
			unset( $rates[ $id ] );
		}
	}
	return $rates;
}
add_filter( 'woocommerce_package_rates', 'nolasharon_filter_pickup_rates', 20 );

/**
 * Nigeria: no postcode field (brief §5.4).
 *
 * @param array $locale Country address locales.
 * @return array
 */
function nolasharon_country_locale( $locale ) {
	$locale['NG']['postcode'] = array(
		'required' => false,
		'hidden'   => true,
	);
	$locale['NG']['state']['label'] = __( 'State', 'nolasharon' );
	$locale['NG']['city']['label']  = __( 'City / area', 'nolasharon' );
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'nolasharon_country_locale' );

/**
 * Checkout fields: drop the company field, label the rest clearly, and relax the
 * address when the customer collects from a branch.
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function nolasharon_checkout_fields( $fields ) {
	unset( $fields['billing']['billing_company'], $fields['shipping']['shipping_company'] );

	$fields['billing']['billing_address_1']['label']       = __( 'Delivery address', 'nolasharon' );
	$fields['billing']['billing_address_1']['placeholder'] = __( 'House number, street and nearest landmark', 'nolasharon' );
	if ( isset( $fields['billing']['billing_address_2'] ) ) {
		$fields['billing']['billing_address_2']['required']    = false;
		$fields['billing']['billing_address_2']['placeholder'] = __( 'Apartment, floor, etc. (optional)', 'nolasharon' );
	}
	$fields['billing']['billing_phone']['required']     = true;
	$fields['billing']['billing_phone']['type']         = 'tel';
	$fields['billing']['billing_phone']['placeholder']  = __( 'e.g. 0801 234 5678', 'nolasharon' );
	$fields['billing']['billing_email']['type']         = 'email';
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Delivery instructions', 'nolasharon' );
		$fields['order']['order_comments']['placeholder'] = __( 'Anything the rider or our team should know.', 'nolasharon' );
	}

	if ( nolasharon_is_pickup_method( nolasharon_chosen_method() ) ) {
		foreach ( array( 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode' ) as $key ) {
			if ( isset( $fields['billing'][ $key ] ) ) {
				$fields['billing'][ $key ]['required'] = false;
			}
		}
	}
	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'nolasharon_checkout_fields', 20 );

/**
 * Normalise a Nigerian phone number to 234XXXXXXXXXX, or return '' when invalid.
 *
 * @param string $phone Phone as typed.
 * @return string
 */
function nolasharon_ng_phone( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( preg_match( '/^0([789][01]\d{8})$/', $digits, $m ) ) {
		return '234' . $m[1];
	}
	if ( preg_match( '/^234([789][01]\d{8})$/', $digits ) ) {
		return $digits;
	}
	return '';
}

/**
 * Validate the phone number and the pickup branch.
 *
 * @param array    $data   Posted checkout data.
 * @param WP_Error $errors Validation errors.
 */
function nolasharon_checkout_validate( $data, $errors ) {
	if ( 'NG' === $data['billing_country'] && '' !== $data['billing_phone'] && ! nolasharon_ng_phone( $data['billing_phone'] ) ) {
		$errors->add( 'validation', __( 'Please enter a valid Nigerian phone number, for example 0801 234 5678.', 'nolasharon' ) );
	}
	if ( nolasharon_is_pickup_method( nolasharon_chosen_method() ) ) {
		if ( ! nolasharon_posted_pickup_branch() ) {
			$errors->add( 'validation', __( 'Please choose the branch you will collect your order from.', 'nolasharon' ) );
		}
	}
}
add_action( 'woocommerce_after_checkout_validation', 'nolasharon_checkout_validate', 10, 2 );

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
 * Branch selector, shown by theme.js when a pickup method is chosen.
 */
function nolasharon_pickup_branch_field() {
	$branches = nolasharon_pickup_branches();
	if ( ! $branches ) {
		return;
	}
	?>
	<div class="pickup-branch" data-pickup-branch hidden>
		<p class="form-row form-row-wide validate-required">
			<label for="ns-pickup-branch"><?php esc_html_e( 'Pickup branch', 'nolasharon' ); ?> <abbr class="required" title="<?php esc_attr_e( 'required', 'nolasharon' ); ?>">*</abbr></label>
			<select id="ns-pickup-branch" name="ns_pickup_branch">
				<option value=""><?php esc_html_e( 'Choose a branch', 'nolasharon' ); ?></option>
				<?php foreach ( $branches as $branch ) : ?>
					<option value="<?php echo esc_attr( $branch['id'] ); ?>"><?php echo esc_html( $branch['name'] ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php foreach ( $branches as $branch ) : ?>
			<div class="pickup-branch__info" data-branch-info="<?php echo esc_attr( $branch['id'] ); ?>" hidden>
				<?php nolasharon_pickup_branch_details( $branch ); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}
add_action( 'woocommerce_after_checkout_billing_form', 'nolasharon_pickup_branch_field' );

/**
 * Address, hours and instructions for a pickup branch.
 *
 * @param array $branch Branch.
 */
function nolasharon_pickup_branch_details( $branch ) {
	foreach ( array(
		'address'             => __( 'Address', 'nolasharon' ),
		'hours'               => __( 'Opening hours', 'nolasharon' ),
		'phone'               => __( 'Phone', 'nolasharon' ),
		'pickup_instructions' => __( 'Pickup instructions', 'nolasharon' ),
	) as $key => $label ) {
		if ( $branch[ $key ] ) {
			printf( '<p><strong>%1$s:</strong> %2$s</p>', esc_html( $label ), nl2br( esc_html( $branch[ $key ] ) ) );
		}
	}
}

/**
 * Save the pickup branch on the order.
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
		$order->update_meta_data( '_ns_pickup_branch_name', $branch['name'] );
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
	foreach ( nolasharon_get_branches( false ) as $branch ) {
		if ( $branch['id'] === $id ) {
			return $branch;
		}
	}
	// Branch since removed: fall back to the name saved with the order.
	return array_merge(
		array_fill_keys( array_keys( nolasharon_branch_fields() ), '' ),
		array(
			'id'   => $id,
			'name' => $order->get_meta( '_ns_pickup_branch_name' ),
		)
	);
}

/**
 * Pickup branch on the admin order screen.
 *
 * @param WC_Order $order Order.
 */
function nolasharon_admin_pickup_branch( $order ) {
	$branch = nolasharon_order_pickup_branch( $order );
	if ( $branch ) {
		echo '<p><strong>' . esc_html__( 'Pickup branch:', 'nolasharon' ) . '</strong> ' . esc_html( $branch['name'] ) . '</p>';
	}
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'nolasharon_admin_pickup_branch' );

/**
 * Pickup branch in the order emails.
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
		echo "\n" . esc_html__( 'Pickup branch:', 'nolasharon' ) . ' ' . esc_html( $branch['name'] ) . "\n";
		foreach ( array( 'address', 'hours', 'pickup_instructions' ) as $key ) {
			if ( $branch[ $key ] ) {
				echo esc_html( $branch[ $key ] ) . "\n";
			}
		}
		return;
	}
	echo '<h2>' . esc_html__( 'Store pickup', 'nolasharon' ) . '</h2><p><strong>' . esc_html( $branch['name'] ) . '</strong></p>';
	nolasharon_pickup_branch_details( $branch );
}
add_action( 'woocommerce_email_after_order_table', 'nolasharon_email_pickup_branch', 10, 3 );

/**
 * Thank-you page: pickup details and how to reach support (brief §5.5).
 *
 * @param int $order_id Order ID.
 */
function nolasharon_thankyou_extras( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$branch = nolasharon_order_pickup_branch( $order );
	if ( $branch ) {
		echo '<section class="thankyou-box"><h2>' . esc_html__( 'Store pickup', 'nolasharon' ) . '</h2><p><strong>' . esc_html( $branch['name'] ) . '</strong></p>';
		nolasharon_pickup_branch_details( $branch );
		echo '</section>';
	}

	$phone    = nolasharon_setting( 'nolasharon_phone' );
	$email    = nolasharon_setting( 'nolasharon_email' );
	$whatsapp = nolasharon_whatsapp_url(
		/* translators: %s: order number. */
		sprintf( __( 'Hi, I have a question about order #%s', 'nolasharon' ), $order->get_order_number() )
	);
	if ( ! $phone && ! $email && ! $whatsapp ) {
		return;
	}
	echo '<section class="thankyou-box"><h2>' . esc_html__( 'Questions about your order?', 'nolasharon' ) . '</h2><ul class="thankyou-contact">';
	if ( $whatsapp ) {
		echo '<li><a href="' . esc_url( $whatsapp ) . '" target="_blank" rel="noopener">' . esc_html__( 'Chat with us on WhatsApp', 'nolasharon' ) . '</a></li>';
	}
	if ( $phone ) {
		echo '<li><a href="' . esc_url( nolasharon_tel_url( $phone ), array( 'tel' ) ) . '">' . esc_html( $phone ) . '</a></li>';
	}
	if ( $email ) {
		echo '<li><a href="' . esc_url( 'mailto:' . $email, array( 'mailto' ) ) . '">' . esc_html( $email ) . '</a></li>';
	}
	echo '</ul></section>';
}
add_action( 'woocommerce_thankyou', 'nolasharon_thankyou_extras', 20 );

/**
 * Checkout page script: show the branch selector and hide the address for pickup.
 * WooCommerce re-renders the shipping options over AJAX, so listen at the document.
 */
function nolasharon_checkout_script() {
	if ( ! is_checkout() || is_order_received_page() ) {
		return;
	}
	?>
	<script>
	( function () {
		var addressKeys = [ 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode' ];
		function chosen() {
			var input = document.querySelector( 'input[name^="shipping_method"]:checked' ) || document.querySelector( 'input[type="hidden"][name^="shipping_method"]' );
			return input ? input.value : '';
		}
		function apply() {
			var pickup = /^(local_pickup|pickup_location)/.test( chosen() );
			addressKeys.forEach( function ( key ) {
				var row = document.getElementById( key + '_field' );
				if ( row ) {
					row.hidden = pickup;
				}
			} );
			var box = document.querySelector( '[data-pickup-branch]' );
			if ( box ) {
				box.hidden = ! pickup;
			}
		}
		document.addEventListener( 'change', function ( e ) {
			if ( e.target.name && e.target.name.indexOf( 'shipping_method' ) === 0 ) {
				apply();
			}
			if ( e.target.id === 'ns-pickup-branch' ) {
				document.querySelectorAll( '[data-branch-info]' ).forEach( function ( el ) {
					el.hidden = el.getAttribute( 'data-branch-info' ) !== e.target.value;
				} );
			}
		} );
		// The shipping options are replaced after each update; re-check when they change.
		var review = document.getElementById( 'order_review' );
		if ( review && 'MutationObserver' in window ) {
			new MutationObserver( apply ).observe( review, { childList: true, subtree: true } );
		}
		apply();
	} )();
	</script>
	<?php
}
add_action( 'wp_footer', 'nolasharon_checkout_script', 30 );

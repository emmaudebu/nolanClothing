<?php
/**
 * Checkout delivery fees from the client's delivery information (received 2026-11-20).
 *
 * Fees live in the database, so deploying the theme does not carry them to the live site.
 * The theme applies them once, on the first wp-admin page load by a shop manager after this
 * version is installed. Rates someone has already given a fee are left alone. Afterwards the
 * fees are edited in WooCommerce → Settings → Shipping (HANDOVER.md §6).
 * The Delivery & Shipping page, the delivery-cost FAQ and the delivery snippet list the same
 * fees; they are brought up to date once in the same way (0.15.3).
 *
 * Decision (2026-11-20, Ifeanyi): the client's ranges become exact fees by area and region.
 * International stays "contact us first"; online checkout is Nigeria-only.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * The zones and their flat rates: name, fee (naira) and the note under it at checkout.
 *
 * @return array[]
 */
function nolasharon_delivery_fee_plan() {
	return array(
		array(
			'name'      => 'Lagos',
			'order'     => 1,
			'locations' => array( array( 'code' => 'NG:LA', 'type' => 'state' ) ),
			'rates'     => array(
				array( 'Lagos – Mainland', 2500, 'Ikeja, Ogudu, Yaba, Surulere, Gbagada and other mainland areas · 24–48 hours' ),
				array( 'Lagos – Island', 3500, 'Victoria Island, Ikoyi, Lekki, Ajah · 24–48 hours' ),
				array( 'Lagos – Outskirts', 5000, 'Ikorodu, Epe, Badagry, Ibeju-Lekki · 24–48 hours' ),
			),
		),
		array(
			'name'      => 'South-West states',
			'order'     => 2,
			'locations' => array_map(
				function ( $state ) {
					return array(
						'code' => 'NG:' . $state,
						'type' => 'state',
					);
				},
				array( 'OG', 'OY', 'OS', 'ON', 'EK' )
			),
			'rates'     => array(
				array( 'Delivery to South-West states', 3500, 'Ogun, Oyo, Osun, Ondo, Ekiti · 2–5 working days' ),
			),
		),
		array(
			'name'      => 'Other Nigerian states',
			'order'     => 3,
			'locations' => array(
				array(
					'code' => 'NG',
					'type' => 'country',
				),
			),
			'rates'     => array(
				array( 'Delivery to other states', 7500, 'All other states · 2–5 working days' ),
			),
		),
	);
}

/**
 * Create or update the zones and flat rates in the plan. Zones are found by name and rates by
 * name (ignoring a "[[CONFIRM …]]" placeholder), so it is safe to run again; store pickup and any
 * other methods are untouched.
 *
 * @param bool $force Also overwrite rates that already have a fee.
 * @return string[] One line per rate: what was set or kept.
 */
function nolasharon_apply_delivery_fees( $force = false ) {
	$log = array();
	foreach ( nolasharon_delivery_fee_plan() as $spec ) {
		$zone = null;
		foreach ( WC_Shipping_Zones::get_zones() as $data ) {
			if ( $spec['name'] === $data['zone_name'] ) {
				$zone = new WC_Shipping_Zone( $data['id'] );
			}
		}
		if ( ! $zone ) {
			$zone = new WC_Shipping_Zone();
			$zone->set_zone_name( $spec['name'] );
		}
		$zone->set_zone_order( $spec['order'] );
		$zone->set_locations( $spec['locations'] );
		$zone->save();

		foreach ( $spec['rates'] as list( $title, $cost, $note ) ) {
			$method = null;
			foreach ( $zone->get_shipping_methods() as $candidate ) {
				if ( 'flat_rate' === $candidate->id && trim( nolasharon_strip_placeholders( $candidate->get_title() ) ) === $title ) {
					$method = $candidate;
				}
			}
			if ( ! $method ) {
				$method = WC_Shipping_Zones::get_shipping_method( $zone->add_shipping_method( 'flat_rate' ) );
			}
			$method->init_instance_settings();
			if ( ! $force && (float) $method->get_option( 'cost' ) > 0 ) {
				$log[] = sprintf( '%s: kept the existing fee', $title );
				continue;
			}
			update_option(
				$method->get_instance_option_key(),
				array_merge(
					$method->instance_settings,
					array(
						'title'   => $title,
						'cost'    => (string) $cost,
						'ns_note' => $note,
					)
				)
			);
			$log[] = sprintf( '%s: ₦%s', $title, number_format( $cost ) );
		}
	}
	WC_Cache_Helper::get_transient_version( 'shipping', true );
	return $log;
}

/**
 * The Delivery & Shipping page: the client's policy (received 2026-10-26) with the charges
 * section listing the checkout fees above. Change both together.
 *
 * @return string Block markup.
 */
function nolasharon_delivery_policy_content() {
	$h       = function ( $text ) {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
	};
	$p       = function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$ul      = function ( $items ) {
		$li = '';
		foreach ( $items as $item ) {
			$li .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->\n";
		}
		return "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$li}</ul>\n<!-- /wp:list -->\n\n";
	};
	$a       = function ( $slug, $text ) {
		return '<a href="' . esc_url( nolasharon_page_url( $slug ) ) . '">' . $text . '</a>';
	};
	$contact = $a( 'contact', 'contact us' );

	return $h( 'Delivery locations' )
		. $p( 'We deliver to all areas within Lagos and nationwide to all 36 states in Nigeria.' )
		. $p( 'We also deliver internationally. To order for delivery outside Nigeria, please ' . $contact . ' first so we can confirm the delivery fee for your destination.' )

		. $h( 'Delivery charges' )
		. $p( 'The delivery fee depends on where your order is going. Choose your area at checkout and the fee is added to your order total, so you see the full amount before you pay.' )
		. $ul(
			array(
				'<strong>Lagos Mainland</strong> (Ikeja, Ogudu, Yaba, Surulere, Gbagada and other mainland areas): ₦2,500',
				'<strong>Lagos Island</strong> (Victoria Island, Ikoyi, Lekki, Ajah): ₦3,500',
				'<strong>Lagos Outskirts</strong> (Ikorodu, Epe, Badagry, Ibeju-Lekki): ₦5,000',
				'<strong>South-West states</strong> (Ogun, Oyo, Osun, Ondo, Ekiti): ₦3,500',
				'<strong>All other states:</strong> ₦7,500',
				'<strong>International:</strong> calculated by weight and destination; please ' . $contact . ' before ordering',
				'<strong>Store pickup:</strong> free',
			)
		)

		. $h( 'Estimated delivery timelines' )
		. $ul(
			array(
				'<strong>Lagos:</strong> 24–48 hours',
				'<strong>Interstate:</strong> 2–5 working days',
				'<strong>International:</strong> 5–10 working days',
			)
		)
		. $p( 'Delivery timelines start once your order has been processed, after payment is confirmed.' )

		. $h( 'Order processing time' )
		. $p( 'Orders are processed within 12–24 hours of payment confirmation. Orders placed after 4pm, at weekends or on public holidays are processed on the next working day.' )

		. $h( 'Dispatch and updates' )
		. $p( 'Once your order is shipped, you will receive a dispatch notification by WhatsApp or SMS with the rider or tracking details. To check on your order, ' . $contact . ' or use our ' . $a( 'track-order', 'Track your order' ) . ' page.' )

		. $h( 'Failed delivery attempts' )
		. $p( 'We attempt delivery 2 times. If delivery fails because of a wrong address, or because your phone is switched off or unreachable, a re-delivery fee applies. If the failure is caused by our courier, re-delivery is free.' )

		. $h( 'Store pickup' )
		. $p( 'You can collect your order for free from either of ' . $a( 'store-locations', 'our two Ikeja stores' ) . '. Select “Pickup” at checkout and choose the store closest to you. Your order will be ready within 24 hours and we will send you a notification when it is. Please bring your Order ID when you come to collect.' );
}

/**
 * The FAQ answer and the delivery snippet's fee line that match the fees above.
 *
 * @return string[] faq (block markup), snippet (the whole snippet) and fees (its fee line).
 */
function nolasharon_delivery_copy() {
	$fees = 'Lagos Mainland ₦2,500, Island ₦3,500, Outskirts ₦5,000; South-West states ₦3,500, other states ₦7,500. The fee is shown at checkout.';
	return array(
		'faq'     => "<!-- wp:paragraph -->\n<p>In Lagos, delivery costs ₦2,500 to mainland areas, ₦3,500 to the Island (Victoria Island, Ikoyi, Lekki, Ajah) and ₦5,000 to the outskirts (Ikorodu, Epe, Badagry, Ibeju-Lekki). Outside Lagos, it costs ₦3,500 to the South-West states (Ogun, Oyo, Osun, Ondo, Ekiti) and ₦7,500 to all other states. International delivery is calculated by weight and destination. The fee is shown at checkout before you pay, and store pickup is free.</p>\n<!-- /wp:paragraph -->",
		'fees'    => $fees,
		'snippet' => "Lagos: 24–48 hours · Other states: 2–5 working days · International: 5–10 working days, after your order is processed (12–24 hours after payment).\n{$fees} Free pickup from our Ikeja stores.",
	);
}

/**
 * Bring the Delivery & Shipping page, the "How much does delivery cost?" FAQ and the delivery
 * snippet in line with the fees. Without $force, only text that is empty, still a placeholder or
 * still quotes the client's old ranges is replaced, so wording edited by hand is kept.
 *
 * @param bool $force Overwrite whatever is there.
 * @return string[] What was updated or kept.
 */
function nolasharon_apply_delivery_policy( $force = false ) {
	$old   = '₦2,500 – ₦5,000';
	$stale = function ( $text ) use ( $force, $old ) {
		return $force || '' === trim( wp_strip_all_tags( (string) $text ) ) || false !== strpos( $text, '[[CONFIRM' ) || false !== strpos( $text, $old );
	};
	$copy  = nolasharon_delivery_copy();
	$log   = array();

	$page = nolasharon_policy_page_id( 'delivery' );
	if ( $page && $stale( get_post_field( 'post_content', $page ) ) ) {
		wp_update_post(
			array(
				'ID'           => $page,
				'post_content' => nolasharon_delivery_policy_content(),
			)
		);
		// The client's own wording (approved 2026-10-26), so customers see it rather than the "being updated" note.
		update_post_meta( $page, 'ns_policy_approved', '1' );
		$log[] = 'Delivery & Shipping page updated';
	} elseif ( $page ) {
		$log[] = get_post_field( 'post_content', $page ) === nolasharon_delivery_policy_content() ? 'Delivery & Shipping page already up to date' : 'Delivery & Shipping page kept (edited by hand)';
	}

	$faq = get_posts(
		array(
			'post_type'   => 'ns_faq',
			'post_status' => 'any',
			'title'       => 'How much does delivery cost?',
			'numberposts' => 1,
		)
	);
	if ( $faq && $stale( $faq[0]->post_content ) ) {
		wp_update_post(
			array(
				'ID'           => $faq[0]->ID,
				'post_content' => $copy['faq'],
			)
		);
		$log[] = 'FAQ “How much does delivery cost?” updated';
	}

	$snippet = (string) get_theme_mod( 'nolasharon_delivery_info', '' );
	$line    = 'Within Lagos ₦2,500 – ₦5,000, outside Lagos ₦3,500 – ₦7,500; the exact fee is shown at checkout.';
	if ( $force || '' === trim( $snippet ) ) {
		set_theme_mod( 'nolasharon_delivery_info', $copy['snippet'] );
		$log[] = 'Delivery snippet updated';
	} elseif ( false !== strpos( $snippet, $line ) ) {
		set_theme_mod( 'nolasharon_delivery_info', str_replace( $line, $copy['fees'], $snippet ) );
		$log[] = 'Delivery snippet updated';
	}
	return $log;
}

/**
 * Apply the fees, and the policy text that lists them, once on a site that has never had them
 * (e.g. the live site after a deploy).
 */
function nolasharon_delivery_fees_once() {
	if ( ! class_exists( 'WC_Shipping_Zones' ) || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	$log = array();
	if ( ! get_option( 'nolasharon_delivery_fees_v1' ) ) {
		update_option( 'nolasharon_delivery_fees_v1', gmdate( 'c' ) );
		$log = nolasharon_apply_delivery_fees();
	}
	if ( ! get_option( 'nolasharon_delivery_policy_v1' ) ) {
		update_option( 'nolasharon_delivery_policy_v1', gmdate( 'c' ) );
		$log = array_merge( $log, nolasharon_apply_delivery_policy() );
	}
	if ( $log ) {
		set_transient( 'nolasharon_delivery_fees_notice', $log, DAY_IN_SECONDS );
	}
}
add_action( 'admin_init', 'nolasharon_delivery_fees_once' );

/**
 * Tell the shop manager what was set, once.
 */
function nolasharon_delivery_fees_notice() {
	$log = get_transient( 'nolasharon_delivery_fees_notice' );
	if ( ! $log || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	delete_transient( 'nolasharon_delivery_fees_notice' );
	printf(
		'<div class="notice notice-success is-dismissible"><p><strong>%1$s</strong> %2$s</p><p>%3$s</p></div>',
		esc_html__( 'Delivery fees and policy:', 'nolasharon' ),
		esc_html( implode( ' · ', $log ) ),
		wp_kses_post(
			sprintf(
				/* translators: 1: link to the shipping settings, 2: link to the pages list. */
				__( 'To change a fee, go to %1$s, then update the Delivery & Shipping Policy page and the FAQ to match (%2$s).', 'nolasharon' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) . '">' . esc_html__( 'WooCommerce → Settings → Shipping', 'nolasharon' ) . '</a>',
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=page' ) ) . '">' . esc_html__( 'Pages', 'nolasharon' ) . '</a>'
			)
		)
	);
}
add_action( 'admin_notices', 'nolasharon_delivery_fees_notice' );

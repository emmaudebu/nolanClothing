<?php
/**
 * Checkout delivery fees from the client's delivery information (received 2026-11-20).
 *
 * Fees live in the database, so deploying the theme does not carry them to the live site.
 * The theme applies them once, on the first wp-admin page load by a shop manager after this
 * version is installed. Rates someone has already given a fee are left alone. Afterwards the
 * fees are edited in WooCommerce → Settings → Shipping (HANDOVER.md §6).
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
 * Apply the fees once on a site that has never had them (e.g. the live site after a deploy).
 */
function nolasharon_delivery_fees_once() {
	if ( get_option( 'nolasharon_delivery_fees_v1' ) || ! class_exists( 'WC_Shipping_Zones' ) || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	update_option( 'nolasharon_delivery_fees_v1', gmdate( 'c' ) );
	set_transient( 'nolasharon_delivery_fees_notice', nolasharon_apply_delivery_fees(), DAY_IN_SECONDS );
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
		esc_html__( 'Checkout delivery fees:', 'nolasharon' ),
		esc_html( implode( ' · ', $log ) ),
		wp_kses_post(
			sprintf(
				/* translators: %s: link to the shipping settings. */
				__( 'To change them, go to %s.', 'nolasharon' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ) . '">' . esc_html__( 'WooCommerce → Settings → Shipping', 'nolasharon' ) . '</a>'
			)
		)
	);
}
add_action( 'admin_notices', 'nolasharon_delivery_fees_notice' );

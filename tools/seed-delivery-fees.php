<?php
/**
 * Checkout delivery fees from the client's delivery information (received 2026-11-20):
 * within Lagos ₦2,500 – ₦5,000 (24–48 hours), outside Lagos ₦3,500 – ₦7,500 (2–5 working days),
 * international by weight and destination (5–10 working days), all after payment confirmation.
 *
 * Decision (2026-11-20, Ifeanyi): the ranges become exact fees by area and region:
 *   Lagos – Mainland ₦2,500 · Lagos – Island ₦3,500 · Lagos – Outskirts ₦5,000
 *   South-West states (Ogun, Oyo, Osun, Ondo, Ekiti) ₦3,500 · all other states ₦7,500
 * International stays "contact us first"; online checkout is Nigeria-only.
 *
 * Run with `studio wp eval-file` locally, or `wp eval-file` on the live site. Safe to re-run:
 * zones are found by name (created if missing) and each rate is matched by its name, so
 * re-running resets these fees, names and notes to the values below. Store pickup is untouched.
 * To change a fee later without this script: WooCommerce → Settings → Shipping (see HANDOVER.md).
 */

$zones = array(
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
				return array( 'code' => 'NG:' . $state, 'type' => 'state' );
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
		'locations' => array( array( 'code' => 'NG', 'type' => 'country' ) ),
		'rates'     => array(
			array( 'Delivery to other states', 7500, 'All other states · 2–5 working days' ),
		),
	),
);

/**
 * Name without a "[[CONFIRM …]]" placeholder, for matching rates seeded earlier.
 *
 * @param string $title Rate name.
 * @return string
 */
$plain = function ( $title ) {
	return trim( preg_replace( '/\[\[CONFIRM[^\]]*\]\]/', '', $title ) );
};

foreach ( $zones as $spec ) {
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
			if ( 'flat_rate' === $candidate->id && $plain( $candidate->get_title() ) === $title ) {
				$method = $candidate;
			}
		}
		if ( ! $method ) {
			$instance_id = $zone->add_shipping_method( 'flat_rate' );
			$method      = WC_Shipping_Zones::get_shipping_method( $instance_id );
		}
		$method->init_instance_settings();
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
		WP_CLI::log( sprintf( '%s: %s = ₦%s', $spec['name'], $title, number_format( $cost ) ) );
	}
}

WC_Cache_Helper::get_transient_version( 'shipping', true );
WP_CLI::success( 'Delivery fees set.' );

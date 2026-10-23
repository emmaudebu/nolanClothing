<?php
/**
 * Store map (LOCATIONS.md §4): an empty Leaflet container, the marker data and the info bar.
 * Hidden until locations.js has drawn the map, so without JavaScript (or if the map
 * library fails to load) the cards simply take the full width.
 *
 * Args: branches (all branches on the page; only those with coordinates get a pin).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_pins = array();
foreach ( $args['branches'] as $nolasharon_b ) {
	if ( ! $nolasharon_b['has_coords'] ) {
		continue;
	}
	$nolasharon_status = ns_branch_status( $nolasharon_b );
	$nolasharon_pins[] = array(
		'slug'       => $nolasharon_b['slug'],
		'name'       => $nolasharon_b['name'],
		'tag'        => $nolasharon_b['tag'],
		'short'      => $nolasharon_b['area'] ? trim( $nolasharon_b['area'] . ( preg_match( '/\d+/', $nolasharon_b['tag'], $nolasharon_m ) ? ' ' . $nolasharon_m[0] : '' ) ) : $nolasharon_b['name'],
		'lat'        => $nolasharon_b['lat'],
		'lng'        => $nolasharon_b['lng'],
		'directions' => ns_branch_directions_url( $nolasharon_b ),
	);
}
if ( ! $nolasharon_pins ) {
	return;
}
?>
<div class="loc-map" data-loc-map hidden>
	<div class="loc-map__frame">
		<div class="loc-map__canvas" id="loc-map-canvas" role="region" aria-label="<?php esc_attr_e( 'Map of our stores', 'nolasharon' ); ?>"></div>
		<p class="loc-map__hint" data-map-hint><?php esc_html_e( 'Click the map to zoom with the scroll wheel.', 'nolasharon' ); ?></p>
		<div class="loc-map__info" aria-live="polite">
			<span class="loc-map__info-icon" aria-hidden="true"><?php echo nolasharon_icon( 'pin', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="loc-map__info-text">
				<span class="loc-map__info-name" data-info-name></span>
				<span class="loc-map__info-status" data-info-status></span>
			</span>
			<a class="loc-map__info-btn" href="#" target="_blank" rel="noopener" data-info-directions hidden><?php esc_html_e( 'Directions', 'nolasharon' ); ?></a>
		</div>
	</div>
	<script type="application/json" id="loc-map-data"><?php echo wp_json_encode( $nolasharon_pins ); ?></script>
</div>

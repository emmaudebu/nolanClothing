<?php
/**
 * Checkout delivery fees (client's delivery information, 2026-11-20). The fees, area names and
 * notes are defined in the theme (inc/delivery-fees.php), which also applies them once by itself
 * on a site that has never had them. Use this script to reset them to those values on demand:
 * `studio wp eval-file` locally, or `wp eval-file` on the live site. It overwrites fees set by hand.
 * To change a fee day to day: WooCommerce → Settings → Shipping (see HANDOVER.md).
 */

if ( ! function_exists( 'nolasharon_apply_delivery_fees' ) ) {
	WP_CLI::error( 'Activate the NolaSharon theme (version 0.15.2 or later) first.' );
}
foreach ( nolasharon_apply_delivery_fees( true ) as $line ) {
	WP_CLI::log( $line );
}
update_option( 'nolasharon_delivery_fees_v1', gmdate( 'c' ) );
WP_CLI::success( 'Delivery fees set.' );

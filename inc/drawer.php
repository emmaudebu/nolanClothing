<?php
/**
 * Cart panel: the bag in a side panel opened from the header cart (decision 2026-10-23:
 * bag only; checkout happens on the Checkout page).
 *
 * The panel's contents load over `?wc-ajax=ns_drawer` so cached pages never show a stale bag.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Render a drawer template part to a string.
 *
 * @param string $part Part name under template-parts/drawer/.
 * @return string
 */
function nolasharon_drawer_part( $part ) {
	ob_start();
	get_template_part( 'template-parts/drawer/' . $part );
	return ob_get_clean();
}

/**
 * AJAX: load the panel, change a quantity or remove an item.
 */
function nolasharon_drawer_ajax() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- checked below for every change.
	$op = isset( $_POST['op'] ) ? sanitize_key( $_POST['op'] ) : 'load';

	if ( 'load' !== $op && ! check_ajax_referer( 'ns-drawer', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Your session expired. Please refresh the page.', 'nolasharon' ) ), 403 );
	}

	$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
	if ( 'qty' === $op && WC()->cart->get_cart_item( $key ) ) {
		WC()->cart->set_quantity( $key, isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 1 );
	} elseif ( 'remove' === $op && WC()->cart->get_cart_item( $key ) ) {
		WC()->cart->remove_cart_item( $key );
	}
	// phpcs:enable

	WC()->cart->calculate_totals();
	$notices = wp_list_pluck( wc_get_notices( 'error' ), 'notice' );
	wc_clear_notices();

	// Straight after an add to cart: the "Added to your cart" view of that line.
	$added = 'load' === $op && isset( $_POST['added'] ) ? wc_clean( wp_unslash( $_POST['added'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only view.
	if ( $added && WC()->cart->get_cart_item( $added ) ) {
		ob_start();
		get_template_part( 'template-parts/drawer/added', null, array( 'key' => $added ) );
		$body = ob_get_clean();
	} else {
		$added = '';
		$body  = nolasharon_drawer_part( 'body' );
	}

	wp_send_json_success(
		array(
			'count'   => WC()->cart->get_cart_contents_count(),
			'added'   => (bool) $added,
			'body'    => $body,
			'message' => wp_strip_all_tags( implode( ' ', $notices ) ),
		)
	);
}
add_action( 'wc_ajax_ns_drawer', 'nolasharon_drawer_ajax' );

/**
 * Open the drawer on the next page load after something is added to the bag.
 */
function nolasharon_drawer_flag_open() {
	if ( WC()->session ) {
		WC()->session->set( 'nolasharon_open_drawer', 1 );
	}
}
add_action( 'woocommerce_add_to_cart', 'nolasharon_drawer_flag_open' );

/**
 * Panel shell in the footer (not on the cart and checkout pages, which show the bag themselves).
 */
function nolasharon_drawer_shell() {
	if ( is_cart() || is_checkout() ) {
		return;
	}
	$autopen = WC()->session && WC()->session->get( 'nolasharon_open_drawer' );
	if ( $autopen ) {
		WC()->session->set( 'nolasharon_open_drawer', null );
	}
	get_template_part( 'template-parts/drawer/shell', null, array( 'autopen' => $autopen ) );
}
add_action( 'wp_footer', 'nolasharon_drawer_shell', 5 );

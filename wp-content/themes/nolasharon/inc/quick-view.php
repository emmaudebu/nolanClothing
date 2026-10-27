<?php
/**
 * Quick view: a product summary in a modal, opened from the product cards,
 * plus an AJAX add-to-cart that hands over to the cart panel.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * AJAX: quick view markup for one product (`?wc-ajax=ns_quick_view&id=`).
 */
function nolasharon_quick_view_ajax() {
	$product = wc_get_product( isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only.
	if ( ! $product || 'publish' !== $product->get_status() || ! $product->is_visible() || post_password_required( $product->get_id() ) ) {
		wp_send_json_error( array( 'message' => __( 'This product is no longer available.', 'nolasharon' ) ), 404 );
	}

	ob_start();
	get_template_part( 'template-parts/quick-view', null, array( 'product' => $product ) );
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wc_ajax_ns_quick_view', 'nolasharon_quick_view_ajax' );

/**
 * AJAX: add to cart without leaving the page (`?wc-ajax=ns_add_to_cart`).
 * Handles simple products and size/colour variations, with WooCommerce's own validation and stock checks.
 */
function nolasharon_ajax_add_to_cart() {
	if ( ! check_ajax_referer( 'ns-add-to-cart', 'ns_nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'This page has been open a while. Please refresh it and try again.', 'nolasharon' ) ), 403 );
	}
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- checked above.
	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$attributes   = array();
	foreach ( $_POST as $key => $value ) {
		if ( 0 === strpos( (string) $key, 'attribute_' ) && is_scalar( $value ) ) {
			$attributes[ sanitize_title( wp_unslash( $key ) ) ] = wc_clean( wp_unslash( $value ) );
		}
	}
	// phpcs:enable
	$product = wc_get_product( $product_id );

	if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		wp_send_json_error( array( 'message' => __( 'Sorry, this product cannot be added right now.', 'nolasharon' ) ) );
	}

	if ( $product->is_type( 'variable' ) ) {
		$variation = $variation_id ? wc_get_product( $variation_id ) : null;
		if ( ! $variation || $variation->get_parent_id() !== $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Please choose your options.', 'nolasharon' ) ) );
		}
		if ( ! $variation->is_in_stock() ) {
			wp_send_json_error( array( 'message' => __( 'This combination is sold out. Please choose another.', 'nolasharon' ) ) );
		}
		$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $attributes );
		$added  = $passed ? WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes ) : false;
	} else {
		$passed = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$added  = $passed ? WC()->cart->add_to_cart( $product_id, $quantity ) : false;
	}

	// The cart panel opens straight away, so it should not reopen on the next page load.
	WC()->session->set( 'nolasharon_open_drawer', null );

	if ( ! $added ) {
		$messages = wp_list_pluck( wc_get_notices( 'error' ), 'notice' );
		wc_clear_notices();
		wp_send_json_error( array( 'message' => wp_strip_all_tags( implode( ' ', $messages ) ) ) );
	}

	wc_clear_notices();
	do_action( 'woocommerce_ajax_added_to_cart', $product_id );

	// The same fragments WooCommerce returns, so its header cart and widgets stay in sync.
	ob_start();
	woocommerce_mini_cart();
	$mini_cart = ob_get_clean();

	wp_send_json_success(
		array(
			'count'     => WC()->cart->get_cart_contents_count(),
			'key'       => $added,
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array( 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>' ) ),
			'cart_hash' => WC()->cart->get_cart_hash(),
		)
	);
}
add_action( 'wc_ajax_ns_add_to_cart', 'nolasharon_ajax_add_to_cart' );

/**
 * Empty quick view modal in the footer; theme.js fills it on demand.
 */
function nolasharon_quick_view_shell() {
	?>
	<dialog class="qv" id="quick-view" aria-label="<?php esc_attr_e( 'Quick view', 'nolasharon' ); ?>"
		data-endpoint="<?php echo esc_url( WC_AJAX::get_endpoint( 'ns_quick_view' ) ); ?>"
		data-add="<?php echo esc_url( WC_AJAX::get_endpoint( 'ns_add_to_cart' ) ); ?>"
		data-loading="<?php esc_attr_e( 'Loading…', 'nolasharon' ); ?>"
		data-adding="<?php esc_attr_e( 'Adding…', 'nolasharon' ); ?>"
		data-error="<?php esc_attr_e( 'Something went wrong. Please try again.', 'nolasharon' ); ?>">
		<div class="qv__inner" data-qv-body></div>
	</dialog>
	<?php
}
add_action( 'wp_footer', 'nolasharon_quick_view_shell', 5 );

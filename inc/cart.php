<?php
/**
 * Cart page (STORE-PAGES.md §4): classic [woocommerce_cart] with our templates, automatic
 * quantity updates, remove + undo, and "You may also like".
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Cart styles and script, only on the cart page. WooCommerce's own cart script is replaced
 * by cart.js; without JavaScript the form still posts normally.
 */
function nolasharon_cart_assets() {
	if ( ! is_cart() ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-cart', get_template_directory_uri() . '/assets/css/cart.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-cart', get_template_directory_uri() . '/assets/js/cart.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script(
		'nolasharon-cart',
		'nsCart',
		array(
			'addUrl'  => WC_AJAX::get_endpoint( 'add_to_cart' ),
			/* translators: %s: product name. */
			'removed' => __( '%s removed', 'nolasharon' ),
			'undo'    => __( 'Undo', 'nolasharon' ),
			/* translators: %s: product name. */
			'added'   => __( '%s added to your cart', 'nolasharon' ),
			'error'   => __( 'Something went wrong. Please try again.', 'nolasharon' ),
		)
	);
	wp_dequeue_script( 'wc-cart' );
}
add_action( 'wp_enqueue_scripts', 'nolasharon_cart_assets', 20 );

/**
 * Breadcrumb, "Your cart" heading with the item count, and the three-step indicator.
 * Shared by the filled and empty cart.
 */
function nolasharon_cart_header() {
	$count = WC()->cart->get_cart_contents_count();
	$steps = array( __( 'Cart', 'nolasharon' ), __( 'Checkout', 'nolasharon' ), __( 'Confirmation', 'nolasharon' ) );
	?>
	<nav class="ns-cart__crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
		<span aria-hidden="true">/</span>
		<span aria-current="page"><?php esc_html_e( 'Cart', 'nolasharon' ); ?></span>
	</nav>
	<div class="ns-cart__head ns-in">
		<h1 class="ns-cart__title">
			<?php esc_html_e( 'Your cart', 'nolasharon' ); ?>
			<?php if ( $count ) : ?>
				<span class="ns-cart__count" data-sync="count"><?php echo esc_html( sprintf( /* translators: %d: number of items. */ _n( '%d item', '%d items', $count, 'nolasharon' ), $count ) ); ?></span>
			<?php endif; ?>
		</h1>
		<ol class="ns-steps" aria-label="<?php esc_attr_e( 'Checkout steps', 'nolasharon' ); ?>">
			<?php foreach ( $steps as $i => $step ) : ?>
				<?php if ( $i ) : ?>
					<li class="ns-steps__line" aria-hidden="true"></li>
				<?php endif; ?>
				<li class="ns-steps__step<?php echo 0 === $i ? ' is-current' : ''; ?>"<?php echo 0 === $i ? ' aria-current="step"' : ''; ?>><span class="ns-steps__num" aria-hidden="true"><?php echo esc_html( $i + 1 ); ?></span><?php echo esc_html( $step ); ?></li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php
}

/**
 * Stock line for a cart item: "Only {n} left" at or below WooCommerce's low-stock threshold,
 * otherwise "In stock". Nothing when the product doesn't track stock.
 *
 * @param WC_Product $product Product.
 * @param int        $qty     Quantity in the cart.
 */
function nolasharon_cart_stock_line( $product, $qty ) {
	if ( $product->backorders_require_notification() && $product->is_on_backorder( $qty ) ) {
		printf( '<span class="ns-stock ns-stock--low">%s</span>', esc_html__( 'Available on backorder', 'nolasharon' ) );
		return;
	}
	if ( ! $product->managing_stock() ) {
		return;
	}
	$left = (int) $product->get_stock_quantity();
	if ( $left > 0 && $left <= wc_get_low_stock_amount( $product ) ) {
		/* translators: %d: number left in stock. */
		printf( '<span class="ns-stock ns-stock--low">%s</span>', esc_html( sprintf( __( 'Only %d left', 'nolasharon' ), $left ) ) );
	} else {
		printf( '<span class="ns-stock">%s</span>', esc_html__( 'In stock', 'nolasharon' ) );
	}
}

/**
 * The cart total before delivery: delivery is worked out at checkout, so any shipping
 * already in the session is left out here.
 *
 * @return string Price HTML.
 */
function nolasharon_cart_total_before_delivery() {
	$cart  = WC()->cart;
	$total = (float) $cart->get_total( 'edit' ) - (float) $cart->get_shipping_total() - (float) $cart->get_shipping_tax();
	return wc_price( max( 0, $total ) );
}

/**
 * "Prices in Nigerian naira (₦)", from the store currency so nothing is hard-coded.
 *
 * @return string
 */
function nolasharon_cart_currency_note() {
	$code  = get_woocommerce_currency();
	$names = get_woocommerce_currencies();
	/* translators: 1: currency name, 2: currency symbol. */
	return sprintf( __( 'Prices in %1$s (%2$s)', 'nolasharon' ), isset( $names[ $code ] ) ? $names[ $code ] : $code, html_entity_decode( get_woocommerce_currency_symbol( $code ) ) );
}

/**
 * Whether the cart shows the coupon box. Off until the client confirms they want discount
 * codes ([[CONFIRM: extras]]); turn on with add_filter( 'nolasharon_cart_coupons', '__return_true' ).
 *
 * @return bool
 */
function nolasharon_cart_coupons() {
	return wc_coupons_enabled() && apply_filters( 'nolasharon_cart_coupons', false );
}

/**
 * "You may also like": cross-sells of the cart items, topped up with the newest in-stock
 * products (never ones already in the cart), four in all.
 */
function nolasharon_cart_recommendations() {
	$in_cart = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		$in_cart[] = (int) $item['product_id'];
	}
	$products = array_filter(
		array_map( 'wc_get_product', WC()->cart->get_cross_sells() ),
		function ( $product ) use ( $in_cart ) {
			return $product && $product->is_visible() && $product->is_in_stock() && ! in_array( $product->get_id(), $in_cart, true );
		}
	);
	$products = array_slice( array_values( $products ), 0, 4 );
	if ( count( $products ) < 4 ) {
		$more     = wc_get_products(
			array(
				'status'       => 'publish',
				'visibility'   => 'catalog',
				'stock_status' => 'instock',
				'limit'        => 4 - count( $products ),
				'orderby'      => 'date',
				'order'        => 'DESC',
				'exclude'      => array_merge( $in_cart, wp_list_pluck( $products, 'id' ) ),
			)
		);
		$products = array_merge( $products, $more );
	}
	if ( $products ) {
		wc_get_template( 'cart/cross-sells.php', array( 'cross_sells' => $products ) );
	}
}

// Our templates print the checkout button, the recommendations and no shipping calculator.
remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );

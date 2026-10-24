<?php
/**
 * Mini-cart drawer (SHOP-PRODUCT.md §3.4). Opened from the header cart it shows the bag;
 * after an add to cart it says "Added to your cart" and shows the line just added. The body
 * is loaded by theme.js. Without JavaScript the header cart is a plain link to the cart.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;
?>
<dialog class="drawer" id="cart-drawer" aria-labelledby="drawer-title"
	data-endpoint="<?php echo esc_url( WC_AJAX::get_endpoint( 'ns_drawer' ) ); ?>"
	data-error="<?php esc_attr_e( 'Something went wrong. Please try again.', 'nolasharon' ); ?>"
	<?php echo ! empty( $args['autopen'] ) ? 'data-autopen' : ''; ?>>
	<header class="drawer__head">
		<span class="drawer__check" aria-hidden="true" data-drawer-check hidden><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" focusable="false"><path d="M5 12l5 5L20 7"/></svg></span>
		<h2 class="drawer__title" id="drawer-title" data-drawer-title data-bag="<?php esc_attr_e( 'Your cart', 'nolasharon' ); ?>" data-added="<?php esc_attr_e( 'Added to your cart', 'nolasharon' ); ?>"><?php esc_html_e( 'Your cart', 'nolasharon' ); ?></h2>
		<button class="drawer__close" type="button" data-close-dialog aria-label="<?php esc_attr_e( 'Close cart', 'nolasharon' ); ?>">
			<?php echo nolasharon_icon( 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</header>
	<div class="drawer__body" data-drawer-body>
		<p class="drawer__loading"><?php esc_html_e( 'Loading your cart…', 'nolasharon' ); ?></p>
	</div>
</dialog>

<?php
/**
 * Empty cart (STORE-PAGES.md §4.4): a gently bobbing bag, "Shop Now" and "You may also like".
 * cart.js cross-fades to this when the last item is removed.
 *
 * Based on WooCommerce template cart/cart-empty.php, version 7.0.1.
 *
 * @package NolaSharon
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ns-cart ns-cart--empty" data-cart>
	<?php nolasharon_cart_header(); ?>

	<?php
	/*
	 * Notices (such as "removed, Undo?") still print here.
	 */
	do_action( 'woocommerce_cart_is_empty' );
	?>

	<div class="ns-cempty ns-in ns-in--1">
		<div class="ns-cempty__art" aria-hidden="true">
			<span class="ns-cempty__bag"><svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" focusable="false"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/><path d="M10 13.5h.01M14 13.5h.01M10 16.5q2-1.6 4 0"/></svg></span>
		</div>
		<h2 class="ns-cempty__title cart-empty"><?php esc_html_e( 'Your cart is empty', 'nolasharon' ); ?></h2>
		<p class="ns-cempty__text"><?php esc_html_e( 'Looks like you haven’t added anything yet. Explore our collection and find something you love.', 'nolasharon' ); ?></p>
		<?php if ( wc_get_page_id( 'shop' ) > 0 ) : ?>
			<a class="ns-btn" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', nolasharon_shop_url() ) ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
		<?php endif; ?>
	</div>

	<?php nolasharon_cart_recommendations(); ?>
</div>

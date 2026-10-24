<?php
/**
 * Drawer after an add to cart (SHOP-PRODUCT.md §3.4): the line just added, the cart subtotal,
 * the delivery note and the ways on.
 *
 * Args: key (cart item key of the added line).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_item    = WC()->cart->get_cart_item( $args['key'] );
$nolasharon_product = $nolasharon_item['data'];
$nolasharon_rows    = nolasharon_cart_item_rows( $nolasharon_item );
$nolasharon_meta    = array();
foreach ( $nolasharon_rows as $nolasharon_label => $nolasharon_value ) {
	$nolasharon_meta[] = $nolasharon_label . ': ' . wp_strip_all_tags( $nolasharon_value );
}
$nolasharon_meta[]  = sprintf( /* translators: %d: quantity. */ __( 'Qty %d', 'nolasharon' ), $nolasharon_item['quantity'] );
$nolasharon_count   = WC()->cart->get_cart_contents_count();
?>
<div class="dadded">
	<div class="dadded__body">
		<div class="dadded__line">
			<span class="dadded__img"><?php nolasharon_product_image( $nolasharon_product ); ?></span>
			<span class="dadded__info">
				<span class="dadded__name"><?php echo esc_html( $nolasharon_product->get_title() ); ?></span>
				<span class="dadded__meta"><?php echo esc_html( implode( ' · ', $nolasharon_meta ) ); ?></span>
				<span class="dadded__price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $nolasharon_product, $nolasharon_item['quantity'] ) ); ?></span>
			</span>
		</div>
		<p class="dadded__sub">
			<span><?php echo esc_html( sprintf( /* translators: %d: number of items. */ _n( 'Cart subtotal (%d item)', 'Cart subtotal (%d items)', $nolasharon_count, 'nolasharon' ), $nolasharon_count ) ); ?></span>
			<strong><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></strong>
		</p>
		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<p class="dadded__note"><?php esc_html_e( 'Delivery fee is calculated at checkout.', 'nolasharon' ); ?></p>
		<?php endif; ?>
	</div>
	<div class="dadded__foot">
		<a class="ns-btn ns-btn--block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Proceed to Checkout', 'nolasharon' ); ?></a>
		<a class="ns-btn ns-btn--ghost ns-btn--block" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'nolasharon' ); ?></a>
		<button class="dadded__continue" type="button" data-close-dialog><?php esc_html_e( 'Continue shopping', 'nolasharon' ); ?></button>
	</div>
</div>

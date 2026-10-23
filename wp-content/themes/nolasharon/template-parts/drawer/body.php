<?php
/**
 * Cart panel body: the bag, totals and the way on to checkout (brief §5.3).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/drawer/bag' );

if ( WC()->cart->is_empty() ) {
	return;
}

$nolasharon_delivery = nolasharon_setting( 'nolasharon_delivery_info' );
?>
<section class="drawer-sec drawer-totals" aria-labelledby="summary-title">
	<h3 class="drawer-sec__title" id="summary-title"><?php echo nolasharon_icon( 'receipt', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Order Summary', 'nolasharon' ); ?></h3>
	<dl class="totals">
		<dt><?php esc_html_e( 'Subtotal', 'nolasharon' ); ?></dt>
		<dd><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></dd>
		<?php foreach ( WC()->cart->get_coupons() as $nolasharon_code => $nolasharon_coupon ) : ?>
			<dt><?php echo esc_html( sprintf( /* translators: %s: coupon code. */ __( 'Discount (%s)', 'nolasharon' ), $nolasharon_code ) ); ?></dt>
			<dd>-<?php echo wp_kses_post( wc_price( WC()->cart->get_coupon_discount_amount( $nolasharon_code, WC()->cart->display_cart_ex_tax ) ) ); ?></dd>
		<?php endforeach; ?>
		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<dt><?php esc_html_e( 'Delivery', 'nolasharon' ); ?></dt>
			<dd class="totals__note"><?php esc_html_e( 'Calculated at checkout', 'nolasharon' ); ?></dd>
		<?php endif; ?>
	</dl>
	<?php if ( ! nolasharon_unconfirmed( $nolasharon_delivery ) ) : ?>
		<p class="drawer-note"><?php echo nl2br( esc_html( $nolasharon_delivery ) ); ?></p>
	<?php endif; ?>

	<a class="drawer-submit" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
		<span><?php esc_html_e( 'Proceed to Checkout', 'nolasharon' ); ?></span>
		<?php echo nolasharon_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
	<div class="drawer-links">
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'nolasharon' ); ?></a>
		<button type="button" data-close-dialog><?php esc_html_e( 'Continue shopping', 'nolasharon' ); ?></button>
	</div>
	<input type="hidden" data-drawer-nonce value="<?php echo esc_attr( wp_create_nonce( 'ns-drawer' ) ); ?>">
</section>

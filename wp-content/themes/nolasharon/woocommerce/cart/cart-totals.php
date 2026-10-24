<?php
/**
 * Cart summary (STORE-PAGES.md §4.3): subtotal, discounts and fees, "Delivery: calculated at
 * checkout", the total before delivery and "Proceed to Checkout". On phones the button
 * moves to a sticky bar (cart.php).
 *
 * Based on WooCommerce template cart/cart-totals.php, version 2.3.6.
 *
 * @package NolaSharon
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_count = WC()->cart->get_cart_contents_count();
?>
<div class="cart_totals ns-sum<?php echo WC()->customer->has_calculated_shipping() ? ' calculated_shipping' : ''; ?>" data-sync="summary">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<h2 class="ns-sum__title"><?php esc_html_e( 'Summary', 'nolasharon' ); ?></h2>

	<dl class="ns-sum__rows">
		<div>
			<dt><?php echo esc_html( sprintf( /* translators: %d: number of items. */ _n( 'Subtotal (%d item)', 'Subtotal (%d items)', $nolasharon_count, 'nolasharon' ), $nolasharon_count ) ); ?></dt>
			<dd><?php wc_cart_totals_subtotal_html(); ?></dd>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $nolasharon_code => $nolasharon_coupon ) : ?>
			<div class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $nolasharon_code ) ); ?>">
				<dt><?php wc_cart_totals_coupon_label( $nolasharon_coupon ); ?></dt>
				<dd><?php wc_cart_totals_coupon_html( $nolasharon_coupon ); ?></dd>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>
			<div>
				<dt><?php esc_html_e( 'Delivery', 'nolasharon' ); ?></dt>
				<dd class="ns-sum__muted"><?php esc_html_e( 'Calculated at checkout', 'nolasharon' ); ?></dd>
			</div>
			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $nolasharon_fee ) : ?>
			<div class="fee">
				<dt><?php echo esc_html( $nolasharon_fee->name ); ?></dt>
				<dd><?php wc_cart_totals_fee_html( $nolasharon_fee ); ?></dd>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>
		<div class="ns-sum__total order-total">
			<dt><?php esc_html_e( 'Total', 'nolasharon' ); ?></dt>
			<dd><?php echo wp_kses_post( nolasharon_cart_total_before_delivery() ); ?></dd>
		</div>
		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
	</dl>
	<p class="ns-sum__currency"><?php echo esc_html( nolasharon_cart_currency_note() ); ?></p>

	<div class="wc-proceed-to-checkout ns-sum__go">
		<a class="ns-btn ns-btn--block checkout-button" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Proceed to Checkout', 'nolasharon' ); ?><svg class="ns-btn__arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>
	<p class="ns-sum__secure"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg><?php esc_html_e( 'Secure checkout', 'nolasharon' ); ?></p>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>

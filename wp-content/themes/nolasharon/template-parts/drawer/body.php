<?php
/**
 * Cart panel body (brief §5.3): the items scroll, while the subtotal and the way on to
 * checkout stay pinned at the bottom.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_delivery = nolasharon_setting( 'nolasharon_delivery_info' );
?>
<div class="dbag">
	<div class="dbag__items">
		<?php get_template_part( 'template-parts/drawer/bag' ); ?>
	</div>

	<?php if ( ! WC()->cart->is_empty() ) : ?>
		<footer class="dbag__foot">
			<dl class="dbag__totals">
				<?php foreach ( WC()->cart->get_coupons() as $nolasharon_code => $nolasharon_coupon ) : ?>
					<div class="dbag__row">
						<dt><?php echo esc_html( sprintf( /* translators: %s: coupon code. */ __( 'Discount (%s)', 'nolasharon' ), $nolasharon_code ) ); ?></dt>
						<dd>-<?php echo wp_kses_post( wc_price( WC()->cart->get_coupon_discount_amount( $nolasharon_code, WC()->cart->display_cart_ex_tax ) ) ); ?></dd>
					</div>
				<?php endforeach; ?>
				<div class="dbag__row dbag__row--sub">
					<dt><?php esc_html_e( 'Subtotal', 'nolasharon' ); ?></dt>
					<dd><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></dd>
				</div>
			</dl>
			<?php if ( WC()->cart->needs_shipping() ) : ?>
				<p class="dbag__note"><?php esc_html_e( 'Delivery fee is calculated at checkout.', 'nolasharon' ); ?></p>
			<?php endif; ?>
			<?php if ( ! nolasharon_unconfirmed( $nolasharon_delivery ) ) : ?>
				<p class="dbag__note dbag__note--info"><?php echo nl2br( esc_html( $nolasharon_delivery ) ); ?></p>
			<?php endif; ?>

			<a class="ns-btn ns-btn--block dbag__checkout" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
				<?php esc_html_e( 'Proceed to Checkout', 'nolasharon' ); ?>
				<?php echo nolasharon_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<div class="dbag__links">
				<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'nolasharon' ); ?></a>
				<button class="dbag__continue" type="button" data-close-dialog><?php esc_html_e( 'Continue shopping', 'nolasharon' ); ?></button>
			</div>
		</footer>
	<?php endif; ?>
	<input type="hidden" data-drawer-nonce value="<?php echo esc_attr( wp_create_nonce( 'ns-drawer' ) ); ?>">
</div>

<?php
/**
 * Checkout header (CHECKOUT.md §1): logo, "Secure checkout" and a way back to the cart,
 * with no other navigation to distract from paying.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;
?>
<header class="co-header">
	<div class="co-header__inner">
		<?php nolasharon_logo(); ?>
		<span class="co-header__secure">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
			<?php esc_html_e( 'Secure checkout', 'nolasharon' ); ?>
		</span>
		<a class="co-header__back" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php echo nolasharon_icon( 'prev', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'Back to cart', 'nolasharon' ); ?>
		</a>
	</div>
</header>

<?php
/**
 * Cart panel shell. Its body is loaded by theme.js when the drawer opens.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;
?>
<dialog class="drawer" id="cart-drawer" aria-labelledby="drawer-title"
	data-endpoint="<?php echo esc_url( WC_AJAX::get_endpoint( 'ns_drawer' ) ); ?>"
	data-error="<?php esc_attr_e( 'Something went wrong. Please try again.', 'nolasharon' ); ?>"
	<?php echo ! empty( $args['autopen'] ) ? 'data-autopen' : ''; ?>>
	<button class="drawer__close icon-btn" type="button" data-close-dialog aria-label="<?php esc_attr_e( 'Close cart', 'nolasharon' ); ?>">
		<?php echo nolasharon_icon( 'close', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
	<header class="drawer__head">
		<span class="drawer__logo" aria-hidden="true">NS</span>
		<div>
			<h2 class="drawer__title" id="drawer-title"><?php esc_html_e( 'Your Cart', 'nolasharon' ); ?></h2>
			<p class="drawer__date" data-drawer-date data-today="<?php esc_attr_e( 'Today', 'nolasharon' ); ?>"></p>
		</div>
	</header>
	<div class="drawer__body" data-drawer-body>
		<p class="drawer__loading"><?php esc_html_e( 'Loading your cart…', 'nolasharon' ); ?></p>
	</div>
</dialog>

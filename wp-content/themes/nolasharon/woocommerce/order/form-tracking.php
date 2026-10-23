<?php
/**
 * Track your order: the lookup form card (TRACK-ORDER.md §A2–§A3). Keeps WooCommerce's
 * field names (orderid, order_email), its nonce and POST, so the email never appears in a URL.
 *
 * Args (from the Track Order template): error, orderid, email.
 *
 * Based on WooCommerce template order/form-tracking.php, version 7.0.1.
 *
 * @package NolaSharon
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $post;

// phpcs:disable WordPress.Security.NonceVerification.Missing -- refilling the form only.
$nolasharon_error   = isset( $error ) ? $error : '';
$nolasharon_orderid = isset( $orderid ) ? $orderid : ( isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '' );
$nolasharon_email   = isset( $email ) && $email ? $email : ( isset( $_POST['order_email'] ) ? sanitize_email( wp_unslash( $_POST['order_email'] ) ) : '' );
// phpcs:enable
if ( ! $nolasharon_email && is_user_logged_in() ) {
	$nolasharon_email = WC()->customer ? WC()->customer->get_billing_email() : wp_get_current_user()->user_email;
}
$nolasharon_orderid = preg_replace( '/^#?NS-/i', '', $nolasharon_orderid );
?>
<form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>#trk-result" method="post" class="woocommerce-form woocommerce-form-track-order track_order trk-form<?php echo $nolasharon_error ? ' is-shake' : ''; ?>" novalidate data-track-form aria-describedby="trk-help">

	<?php do_action( 'woocommerce_order_tracking_form_start' ); ?>

	<h2 class="trk-form__title"><?php esc_html_e( 'Find your order', 'nolasharon' ); ?></h2>

	<p class="ns-field">
		<label for="orderid"><?php esc_html_e( 'Order number', 'nolasharon' ); ?></label>
		<span class="ns-phone">
			<span class="ns-phone__prefix trk-form__prefix" aria-hidden="true">#NS-</span>
			<input type="text" name="orderid" id="orderid" inputmode="numeric" autocomplete="off" placeholder="<?php esc_attr_e( 'e.g. 1048', 'nolasharon' ); ?>" value="<?php echo esc_attr( $nolasharon_orderid ); ?>" required aria-required="true">
		</span>
	</p>
	<p class="ns-field">
		<label for="order_email"><?php esc_html_e( 'Email address', 'nolasharon' ); ?></label>
		<input type="email" name="order_email" id="order_email" autocomplete="email" placeholder="<?php esc_attr_e( 'you@example.com', 'nolasharon' ); ?>" value="<?php echo esc_attr( $nolasharon_email ); ?>" required aria-required="true">
	</p>
	<p class="trk-form__help" id="trk-help"><?php esc_html_e( 'You’ll find your order number in your order confirmation email.', 'nolasharon' ); ?></p>

	<div class="ns-collapse trk-form__error<?php echo $nolasharon_error ? ' is-open' : ''; ?>" data-track-error>
		<div>
			<p class="trk-form__error-box" role="alert">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
				<span data-track-error-text><?php echo esc_html( $nolasharon_error ); ?></span>
			</p>
		</div>
	</div>

	<?php do_action( 'woocommerce_order_tracking_form' ); ?>

	<input type="hidden" name="track" value="1">
	<?php wp_nonce_field( 'woocommerce-order_tracking', 'woocommerce-order-tracking-nonce' ); ?>
	<button type="submit" class="ns-btn ns-btn--block" data-track-submit><?php esc_html_e( 'Track order', 'nolasharon' ); ?></button>

	<?php do_action( 'woocommerce_order_tracking_form_end' ); ?>
</form>

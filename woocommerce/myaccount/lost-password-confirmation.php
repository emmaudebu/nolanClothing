<?php
/**
 * Check your email (STORE-PAGES.md §2.4). Shown whether or not the email has an account,
 * so the screen never reveals which emails are registered. The address comes from
 * sessionStorage (never the URL); "Resend link" re-sends it after a 30-second wait.
 *
 * Based on WooCommerce template myaccount/lost-password-confirmation.php, version 3.9.0.
 *
 * @package NolaSharon
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;

nolasharon_auth_open();
?>

<div class="ns-auth__mail" aria-hidden="true">
	<span class="ns-auth__mail-icon"><?php echo nolasharon_icon( 'mail', 44 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span class="ns-auth__mail-badge"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" focusable="false"><path d="M5 12l5 5L20 7"/></svg></span>
</div>

<h1 class="ns-auth__title ns-auth__title--sm"><?php esc_html_e( 'Check your email', 'nolasharon' ); ?></h1>

<?php do_action( 'woocommerce_before_lost_password_confirmation_message' ); ?>

<p class="ns-auth__lead ns-auth__lead--dark">
	<?php
	printf(
		/* translators: %s: the email address the shopper typed. */
		esc_html__( 'If an account exists for %s, we’ve sent a link to reset your password.', 'nolasharon' ),
		'<strong data-sent-email>' . esc_html__( 'your email', 'nolasharon' ) . '</strong>'
	);
	?>
</p>
<p class="ns-auth__small"><?php esc_html_e( 'It can take a few minutes to arrive. Remember to check your spam or promotions folder.', 'nolasharon' ); ?></p>

<?php do_action( 'woocommerce_after_lost_password_confirmation_message' ); ?>

<a class="ns-btn ns-btn--block" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Back to sign in', 'nolasharon' ); ?></a>

<form method="post" class="ns-auth__resend" action="<?php echo esc_url( wc_get_account_endpoint_url( 'lost-password' ) ); ?>" data-resend-form>
	<input type="hidden" name="user_login" value="" data-resend-email />
	<input type="hidden" name="wc_reset_password" value="true" />
	<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
	<p class="ns-auth__switch">
		<?php esc_html_e( 'Didn’t get it?', 'nolasharon' ); ?>
		<a class="ns-textlink" href="<?php echo esc_url( wc_get_account_endpoint_url( 'lost-password' ) ); ?>" data-resend><?php esc_html_e( 'Resend link', 'nolasharon' ); ?></a>
	</p>
</form>

<?php
nolasharon_auth_close();

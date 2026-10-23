<?php
/**
 * Forgot password (STORE-PAGES.md §2.3). The email is prefilled from the sign-in form via
 * sessionStorage, never the URL. Reached with ?show-reset-form but no valid key, it is the
 * "link expired" screen with the same form to send a new link (§2.5).
 *
 * Based on WooCommerce template myaccount/form-lost-password.php, version 9.2.0.
 *
 * @package NolaSharon
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

// WooCommerce only shows this form with show-reset-form when the reset key failed.
$nolasharon_expired = ! empty( $_GET['show-reset-form'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen state.
if ( $nolasharon_expired ) {
	nolasharon_remove_notice( __( 'This key is invalid or has already been used. Please reset your password again if needed.', 'woocommerce' ) );
}

nolasharon_auth_open();
nolasharon_auth_back_to_signin();

do_action( 'woocommerce_before_lost_password_form' );
?>

<?php if ( $nolasharon_expired ) : ?>
	<span class="ns-icon-tile ns-icon-tile--lg" aria-hidden="true"><?php echo nolasharon_icon( 'clock', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<h1 class="ns-auth__title ns-auth__title--sm"><?php esc_html_e( 'This reset link has expired or was already used.', 'nolasharon' ); ?></h1>
	<p class="ns-auth__lead"><?php esc_html_e( 'Reset links work once and only for a limited time. Enter your email address and we’ll send you a new one.', 'nolasharon' ); ?></p>
<?php else : ?>
	<span class="ns-icon-tile ns-icon-tile--lg" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" focusable="false"><circle cx="8" cy="15" r="4"/><path d="M10.8 12.2L20 3M16 7l3 3M14 9l2 2"/></svg></span>
	<h1 class="ns-auth__title ns-auth__title--sm"><?php esc_html_e( 'Forgot your password?', 'nolasharon' ); ?></h1>
	<p class="ns-auth__lead"><?php esc_html_e( 'Enter the email address on your account and we’ll send you a link to reset your password.', 'nolasharon' ); ?></p>
<?php endif; ?>

<form method="post" class="woocommerce-ResetPassword lost_reset_password ns-auth__form" novalidate data-auth-form="lost">

	<p class="ns-field">
		<label for="user_login"><?php esc_html_e( 'Email address', 'nolasharon' ); ?></label>
		<input class="woocommerce-Input woocommerce-Input--text input-text" type="email" name="user_login" id="user_login" autocomplete="email" inputmode="email" placeholder="<?php esc_attr_e( 'you@example.com', 'nolasharon' ); ?>" required aria-required="true" data-required="email" data-remember-email />
	</p>

	<?php do_action( 'woocommerce_lostpassword_form' ); ?>

	<input type="hidden" name="wc_reset_password" value="true" />
	<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
	<button type="submit" class="ns-btn ns-btn--block" data-loading="sending"><?php echo esc_html( $nolasharon_expired ? __( 'Send a new link', 'nolasharon' ) : __( 'Send reset link', 'nolasharon' ) ); ?></button>

</form>
<?php
do_action( 'woocommerce_after_lost_password_form' );

nolasharon_auth_close();

<?php
/**
 * Sign in and Create account (STORE-PAGES.md §2.2): one screen, two panels behind
 * segmented tabs. #register opens the second tab. Without JavaScript both forms show,
 * one under the other. Keeps WooCommerce's field names, nonces and hooks.
 *
 * Based on WooCommerce template myaccount/form-login.php, version 9.9.0.
 *
 * @package NolaSharon
 * @version 9.9.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_can_register = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
// phpcs:disable WordPress.Security.NonceVerification -- read-only screen state.
$nolasharon_reset_done = ! empty( $_GET['password-reset'] );
$nolasharon_start      = $nolasharon_can_register && ! empty( $_POST['register'] ) ? 'register' : 'login';
// phpcs:enable
$nolasharon_return = nolasharon_auth_return_url();
$nolasharon_guest  = WC()->cart && ! WC()->cart->is_empty() ? wc_get_checkout_url() : nolasharon_shop_url();

if ( $nolasharon_reset_done ) {
	// Shown as the success card below instead of a notice.
	nolasharon_remove_notice( __( 'Your password has been reset successfully.', 'woocommerce' ) );
}

nolasharon_auth_open();

do_action( 'woocommerce_before_customer_login_form' );
?>

<?php if ( $nolasharon_reset_done ) : ?>
	<div class="ns-auth__done" role="status">
		<svg class="ns-success ns-success--sm" viewBox="0 0 88 88" aria-hidden="true" focusable="false"><circle class="ns-success__bg" cx="44" cy="44" r="40"/><circle class="ns-success__ring" cx="44" cy="44" r="40"/><path class="ns-success__tick" d="M28 45l11 11 21-23"/></svg>
		<p><strong><?php esc_html_e( 'Password updated.', 'nolasharon' ); ?></strong> <?php esc_html_e( 'You can now sign in with your new password.', 'nolasharon' ); ?></p>
	</div>
<?php endif; ?>

<?php if ( $nolasharon_can_register ) : ?>
	<div class="ns-auth__tabs" data-auth-tabs hidden>
		<div class="ns-seg" role="tablist" aria-label="<?php esc_attr_e( 'Account', 'nolasharon' ); ?>" style="--n: 2; --i: <?php echo 'register' === $nolasharon_start ? 1 : 0; ?>">
			<span class="ns-seg__thumb" aria-hidden="true"></span>
			<button type="button" class="ns-seg__btn" role="tab" id="ns-tab-login" aria-controls="login" aria-selected="<?php echo 'login' === $nolasharon_start ? 'true' : 'false'; ?>"><?php esc_html_e( 'Sign in', 'nolasharon' ); ?></button>
			<button type="button" class="ns-seg__btn" role="tab" id="ns-tab-register" aria-controls="register" aria-selected="<?php echo 'register' === $nolasharon_start ? 'true' : 'false'; ?>"><?php esc_html_e( 'Create account', 'nolasharon' ); ?></button>
		</div>
	</div>
<?php endif; ?>

<div class="ns-auth__panels" data-start="<?php echo esc_attr( $nolasharon_start ); ?>">

	<section class="ns-auth__panel" id="login" aria-labelledby="ns-login-title">
		<h1 class="ns-auth__title" id="ns-login-title"><?php esc_html_e( 'Welcome back', 'nolasharon' ); ?></h1>
		<p class="ns-auth__lead"><?php esc_html_e( 'Sign in to track your orders and check out faster.', 'nolasharon' ); ?></p>

		<form class="woocommerce-form woocommerce-form-login login ns-auth__form" method="post" novalidate data-auth-form="login">

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="ns-field">
				<label for="username"><?php esc_html_e( 'Email address', 'nolasharon' ); ?></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="email" inputmode="email" placeholder="<?php esc_attr_e( 'you@example.com', 'nolasharon' ); ?>" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" data-required="login-email" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized ?>
			</p>
			<p class="ns-field">
				<label for="password"><?php esc_html_e( 'Password', 'nolasharon' ); ?></label>
				<span class="ns-pw">
					<input class="input-text" type="password" name="password" id="password" autocomplete="current-password" placeholder="<?php esc_attr_e( 'Your password', 'nolasharon' ); ?>" required aria-required="true" data-required="password" />
					<?php nolasharon_password_eye(); ?>
				</span>
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="ns-auth__between">
				<label class="ns-check">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'nolasharon' ); ?></span>
				</label>
				<a class="ns-textlink" href="<?php echo esc_url( wp_lostpassword_url() ); ?>" data-forgot><?php esc_html_e( 'Forgot password?', 'nolasharon' ); ?></a>
			</div>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<input type="hidden" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>" />
			<?php if ( $nolasharon_return ) : ?>
				<input type="hidden" name="redirect" value="<?php echo esc_url( $nolasharon_return ); ?>" />
			<?php endif; ?>
			<button type="submit" class="ns-btn ns-btn--block woocommerce-form-login__submit" data-loading="signingIn"><?php esc_html_e( 'Sign in', 'nolasharon' ); ?></button>

			<p class="ns-auth__or"><span><?php esc_html_e( 'or', 'nolasharon' ); ?></span></p>
			<a class="ns-btn ns-btn--ghost ns-btn--block" href="<?php echo esc_url( $nolasharon_guest ); ?>"><?php esc_html_e( 'Continue as guest', 'nolasharon' ); ?></a>

			<?php if ( $nolasharon_can_register ) : ?>
				<p class="ns-auth__switch"><?php esc_html_e( 'New to NolaSharon?', 'nolasharon' ); ?> <a class="ns-textlink" href="#register" data-auth-go="register"><?php esc_html_e( 'Create an account', 'nolasharon' ); ?></a></p>
			<?php endif; ?>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>
	</section>

	<?php if ( $nolasharon_can_register ) : ?>
		<section class="ns-auth__panel" id="register" aria-labelledby="ns-register-title">
			<h2 class="ns-auth__title" id="ns-register-title"><?php esc_html_e( 'Create your account', 'nolasharon' ); ?></h2>
			<p class="ns-auth__lead"><?php esc_html_e( 'Save your details for faster checkout and easy order tracking.', 'nolasharon' ); ?></p>

			<form method="post" class="woocommerce-form woocommerce-form-register register ns-auth__form" novalidate data-auth-form="register" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<p class="ns-field">
						<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized ?>
					</p>
				<?php endif; ?>

				<p class="ns-field">
					<label for="reg_email"><?php esc_html_e( 'Email address', 'nolasharon' ); ?></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" inputmode="email" placeholder="<?php esc_attr_e( 'you@example.com', 'nolasharon' ); ?>" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" data-required="email" /><?php // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized ?>
				</p>

				<?php do_action( 'nolasharon_register_form_after_email' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="ns-field">
						<label for="reg_password"><?php esc_html_e( 'Password', 'nolasharon' ); ?></label>
						<span class="ns-pw">
							<input type="password" class="input-text" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'At least 8 characters', 'nolasharon' ); ?>" required aria-required="true" aria-describedby="reg_password_meter" data-required="password" />
							<?php nolasharon_password_eye(); ?>
						</span>
						<?php nolasharon_password_meter( 'reg_password' ); ?>
					</p>
				<?php else : ?>
					<p class="ns-auth__note"><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<input type="hidden" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>" />
				<?php if ( $nolasharon_return ) : ?>
					<input type="hidden" name="redirect" value="<?php echo esc_url( $nolasharon_return ); ?>" />
				<?php endif; ?>
				<button type="submit" class="ns-btn ns-btn--block woocommerce-form-register__submit" data-loading="creating"><?php esc_html_e( 'Create account', 'nolasharon' ); ?></button>

				<p class="ns-auth__switch"><?php esc_html_e( 'Already have an account?', 'nolasharon' ); ?> <a class="ns-textlink" href="#login" data-auth-go="login"><?php esc_html_e( 'Sign in', 'nolasharon' ); ?></a></p>

				<?php do_action( 'woocommerce_register_form_end' ); ?>

			</form>
		</section>
	<?php endif; ?>

</div>

<?php
do_action( 'woocommerce_after_customer_login_form' );

nolasharon_auth_close();

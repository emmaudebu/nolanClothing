<?php
/**
 * Set a new password (STORE-PAGES.md §2.5): strength meter, a live checklist and a
 * "passwords match" line. Keeps WooCommerce's reset_key, reset_login, password_1,
 * password_2 and nonce.
 *
 * Based on WooCommerce template myaccount/form-reset-password.php, version 9.2.0.
 *
 * @package NolaSharon
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_rules = array(
	'length' => __( 'At least 8 characters', 'nolasharon' ),
	'case'   => __( 'Upper and lowercase letters', 'nolasharon' ),
	'number' => __( 'At least one number', 'nolasharon' ),
);

nolasharon_auth_open();

do_action( 'woocommerce_before_reset_password_form' );
?>

<span class="ns-icon-tile ns-icon-tile--lg" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" focusable="false"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg></span>
<h1 class="ns-auth__title ns-auth__title--sm"><?php esc_html_e( 'Set a new password', 'nolasharon' ); ?></h1>
<p class="ns-auth__lead"><?php esc_html_e( 'Choose a strong password you haven’t used before.', 'nolasharon' ); ?></p>

<form method="post" class="woocommerce-ResetPassword lost_reset_password ns-auth__form" novalidate data-auth-form="reset">

	<p class="ns-field">
		<label for="password_1"><?php esc_html_e( 'New password', 'nolasharon' ); ?></label>
		<span class="ns-pw">
			<input type="password" class="input-text" name="password_1" id="password_1" autocomplete="new-password" required aria-required="true" aria-describedby="ns-pw-rules password_1_meter" data-required="password" />
			<?php nolasharon_password_eye(); ?>
		</span>
		<?php nolasharon_password_meter( 'password_1' ); ?>
	</p>

	<ul class="ns-rules" id="ns-pw-rules" aria-label="<?php esc_attr_e( 'Password requirements', 'nolasharon' ); ?>" data-rules-for="password_1">
		<?php foreach ( $nolasharon_rules as $nolasharon_key => $nolasharon_label ) : ?>
			<li data-rule="<?php echo esc_attr( $nolasharon_key ); ?>">
				<span class="ns-rules__dot" aria-hidden="true"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" focusable="false"><path d="M5 12l5 5L20 7"/></svg></span>
				<span><?php echo esc_html( $nolasharon_label ); ?></span>
				<span class="screen-reader-text" data-rule-state><?php esc_html_e( '(not met)', 'nolasharon' ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<p class="ns-field">
		<label for="password_2"><?php esc_html_e( 'Confirm new password', 'nolasharon' ); ?></label>
		<input type="password" class="input-text" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true" aria-describedby="ns-pw-match" data-required="password" />
		<span class="ns-field__hint ns-match" id="ns-pw-match" aria-live="polite" data-match-for="password_1"></span>
	</p>

	<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>" />
	<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>" />

	<?php do_action( 'woocommerce_resetpassword_form' ); ?>

	<input type="hidden" name="wc_reset_password" value="true" />
	<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
	<button type="submit" class="ns-btn ns-btn--block" data-loading="saving"><?php esc_html_e( 'Save new password', 'nolasharon' ); ?></button>

</form>
<?php
do_action( 'woocommerce_after_reset_password_form' );

nolasharon_auth_close();

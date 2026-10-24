<?php
/**
 * Account details (STORE-PAGES.md §3.6): name, display name, email, phone and a
 * collapsible "Change password" section. Keeps WooCommerce's field names, nonce and hooks.
 *
 * Based on WooCommerce template myaccount/form-edit-account.php, version 11.0.0.
 *
 * @package NolaSharon
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

// Keep the password section open after a failed save that involved it.
// phpcs:ignore WordPress.Security.NonceVerification.Missing -- read-only screen state.
$nolasharon_pw_open = ! empty( $_POST['password_current'] ) || ! empty( $_POST['password_1'] );

do_action( 'woocommerce_before_edit_account_form' );
?>

<section class="ns-card ns-details" aria-labelledby="ns-details-title">
	<h2 class="ns-details__title" id="ns-details-title"><?php esc_html_e( 'Account details', 'nolasharon' ); ?></h2>

	<form class="woocommerce-EditAccountForm edit-account ns-af" action="" method="post" novalidate data-account-form <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >

		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<div class="ns-af__fields">
			<p class="ns-field ns-af--half">
				<label for="account_first_name"><?php esc_html_e( 'First name', 'nolasharon' ); ?></label>
				<input type="text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" aria-required="true" data-required="first" />
			</p>
			<p class="ns-field ns-af--half">
				<label for="account_last_name"><?php esc_html_e( 'Last name', 'nolasharon' ); ?></label>
				<input type="text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" aria-required="true" data-required="text" />
			</p>
			<p class="ns-field ns-af--full">
				<label for="account_display_name"><?php esc_html_e( 'Display name', 'nolasharon' ); ?></label>
				<input type="text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr( $user->display_name ); ?>" aria-required="true" data-required="text" />
				<span class="ns-field__hint" id="account_display_name_description"><?php esc_html_e( 'This is how your name appears in your account.', 'nolasharon' ); ?></span>
			</p>
			<p class="ns-field ns-af--half">
				<label for="account_email"><?php esc_html_e( 'Email address', 'nolasharon' ); ?></label>
				<input type="email" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" aria-required="true" data-required="email" />
			</p>

			<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>
		</div>

		<button type="button" class="ns-details__toggle" aria-expanded="<?php echo $nolasharon_pw_open ? 'true' : 'false'; ?>" aria-controls="ns-pw-section" data-pw-toggle hidden>
			<span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg><?php esc_html_e( 'Change password', 'nolasharon' ); ?></span>
			<?php echo nolasharon_icon( 'chevron', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<div class="ns-collapse is-open" id="ns-pw-section" data-pw-section<?php echo $nolasharon_pw_open ? ' data-open' : ''; ?>>
			<fieldset class="ns-details__pw">
				<legend class="ns-details__legend"><?php esc_html_e( 'Change password', 'nolasharon' ); ?></legend>
				<div class="ns-af__fields">
					<p class="ns-field ns-af--full">
						<label for="password_current"><?php esc_html_e( 'Current password', 'nolasharon' ); ?></label>
						<span class="ns-pw">
							<input type="password" class="input-text" name="password_current" id="password_current" autocomplete="current-password" />
							<?php nolasharon_password_eye(); ?>
						</span>
					</p>
					<p class="ns-field ns-af--half">
						<label for="password_1"><?php esc_html_e( 'New password', 'nolasharon' ); ?></label>
						<span class="ns-pw">
							<input type="password" class="input-text" name="password_1" id="password_1" autocomplete="new-password" aria-describedby="password_1_meter" />
							<?php nolasharon_password_eye(); ?>
						</span>
						<?php nolasharon_password_meter( 'password_1' ); ?>
					</p>
					<p class="ns-field ns-af--half">
						<label for="password_2"><?php esc_html_e( 'Confirm new password', 'nolasharon' ); ?></label>
						<input type="password" class="input-text" name="password_2" id="password_2" autocomplete="new-password" aria-describedby="ns-pw-match" />
						<span class="ns-field__hint ns-match" id="ns-pw-match" aria-live="polite" data-match-for="password_1"></span>
					</p>
					<p class="ns-field__hint ns-af--full"><?php esc_html_e( 'Leave blank to keep your current password.', 'nolasharon' ); ?></p>
				</div>
			</fieldset>
		</div>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<input type="hidden" name="action" value="save_account_details" />
		<input type="hidden" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>" />
		<button type="submit" class="ns-btn ns-details__save" data-loading="saving"><?php esc_html_e( 'Save changes', 'nolasharon' ); ?></button>

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>
</section>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>

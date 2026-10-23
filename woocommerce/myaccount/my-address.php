<?php
/**
 * Addresses (STORE-PAGES.md §3.5). The shop ships to the billing address only, so this is a
 * single "Delivery address" card. "Edit" opens the form inline (account.js); without
 * JavaScript it links to WooCommerce's edit page. The form posts to edit-address/billing,
 * so WooCommerce's own handler and nonce save it.
 *
 * Based on WooCommerce template myaccount/my-address.php, version 9.3.0.
 *
 * @package NolaSharon
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_customer = new WC_Customer( get_current_user_id() );
$nolasharon_name     = trim( $nolasharon_customer->get_billing_first_name() . ' ' . $nolasharon_customer->get_billing_last_name() );
$nolasharon_states   = WC()->countries->get_states( 'NG' );
$nolasharon_state    = $nolasharon_customer->get_billing_state();
$nolasharon_lines    = array_filter(
	array(
		$nolasharon_customer->get_billing_address_1(),
		implode(
			', ',
			array_filter(
				array(
					$nolasharon_customer->get_billing_city(),
					isset( $nolasharon_states[ $nolasharon_state ] ) ? $nolasharon_states[ $nolasharon_state ] : $nolasharon_state,
				)
			)
		),
		ns_branch_format_phone( $nolasharon_customer->get_billing_phone() ),
	)
);
$nolasharon_has      = (bool) $nolasharon_customer->get_billing_address_1();
$nolasharon_edit_url = wc_get_endpoint_url( 'edit-address', 'billing' );
?>
<section class="ns-card ns-addr" aria-labelledby="ns-addr-title">
	<h2 class="ns-addr__title" id="ns-addr-title"><?php esc_html_e( 'Delivery address', 'nolasharon' ); ?></h2>
	<p class="ns-addr__lead"><?php esc_html_e( 'We’ll use this address at checkout. You can change it any time.', 'nolasharon' ); ?></p>

	<div class="ns-addr__card<?php echo $nolasharon_has ? '' : ' is-empty'; ?>">
		<span class="ns-addr__icon" aria-hidden="true"><?php echo nolasharon_account_icon( 'home', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<div class="ns-addr__text">
			<?php if ( $nolasharon_has ) : ?>
				<strong><?php echo esc_html( $nolasharon_name ); ?></strong>
				<?php foreach ( $nolasharon_lines as $nolasharon_line ) : ?>
					<span><?php echo esc_html( $nolasharon_line ); ?></span>
				<?php endforeach; ?>
			<?php else : ?>
				<strong><?php esc_html_e( 'No delivery address yet', 'nolasharon' ); ?></strong>
				<span><?php esc_html_e( 'Add one now to check out faster next time.', 'nolasharon' ); ?></span>
			<?php endif; ?>
		</div>
		<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_edit_url ); ?>" data-addr-edit data-label-cancel="<?php esc_attr_e( 'Cancel', 'nolasharon' ); ?>" aria-controls="ns-addr-form"><?php echo esc_html( $nolasharon_has ? __( 'Edit', 'nolasharon' ) : __( 'Add address', 'nolasharon' ) ); ?></a>
	</div>

	<div class="ns-collapse ns-addr__form" id="ns-addr-form" data-addr-form hidden>
		<div>
			<?php nolasharon_address_form( nolasharon_account_address_fields(), $nolasharon_edit_url ); ?>
		</div>
	</div>

	<?php do_action( 'woocommerce_my_account_after_my_address', 'billing' ); ?>
</section>

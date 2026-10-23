<?php
/**
 * Edit the delivery address (STORE-PAGES.md §3.5). The Addresses page shows the same form
 * inline; this page is used without JavaScript, and after a failed save.
 *
 * Based on WooCommerce template myaccount/form-edit-address.php, version 9.3.0.
 *
 * @package NolaSharon
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_address_form' ); ?>

<?php if ( ! $load_address ) : ?>
	<?php wc_get_template( 'myaccount/my-address.php' ); ?>
<?php else : ?>
	<section class="ns-card ns-addr" aria-labelledby="ns-addr-title">
		<a class="ns-auth__back ns-order__back" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address' ) ); ?>"><?php echo nolasharon_icon( 'prev', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Addresses', 'nolasharon' ); ?></a>
		<h2 class="ns-addr__title" id="ns-addr-title"><?php echo esc_html( apply_filters( 'woocommerce_my_account_edit_address_title', __( 'Delivery address', 'nolasharon' ), $load_address ) ); ?></h2>
		<?php nolasharon_address_form( $address, '', $load_address ); ?>
	</section>
<?php endif; ?>

<?php do_action( 'woocommerce_after_edit_account_address_form' ); ?>

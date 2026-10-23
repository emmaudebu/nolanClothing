<?php
/**
 * My Account menu (STORE-PAGES.md §3) with icons and a badge counting orders that need
 * payment, plus a "Need help?" card. "Log out" opens a confirmation dialog (account.js);
 * without JavaScript it is WooCommerce's normal nonce-protected link.
 *
 * Based on WooCommerce template myaccount/navigation.php, version 9.3.0.
 *
 * @package NolaSharon
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$nolasharon_unpaid   = nolasharon_unpaid_order_count();
$nolasharon_whatsapp = nolasharon_whatsapp_url( __( 'Hi, I have a question about my order.', 'nolasharon' ) );

do_action( 'woocommerce_before_account_navigation' );
?>

<aside class="ns-acct__side ns-in ns-in--1">
	<nav class="woocommerce-MyAccount-navigation ns-acct__nav" aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">
		<ul>
			<?php foreach ( wc_get_account_menu_items() as $nolasharon_endpoint => $nolasharon_label ) : ?>
				<?php $nolasharon_current = wc_is_current_account_menu_item( $nolasharon_endpoint ); ?>
				<li class="<?php echo wc_get_account_menu_item_classes( $nolasharon_endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by WooCommerce. ?>">
					<a class="ns-acct__link<?php echo 'customer-logout' === $nolasharon_endpoint ? ' ns-acct__link--out' : ''; ?>" href="<?php echo esc_url( wc_get_account_endpoint_url( $nolasharon_endpoint ) ); ?>"<?php echo $nolasharon_current ? ' aria-current="page"' : ''; ?><?php echo 'customer-logout' === $nolasharon_endpoint ? ' data-logout' : ''; ?>>
						<span class="ns-acct__bar" aria-hidden="true"></span>
						<?php echo nolasharon_account_icon( $nolasharon_endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
						<span><?php echo esc_html( $nolasharon_label ); ?></span>
						<?php if ( 'orders' === $nolasharon_endpoint && $nolasharon_unpaid ) : ?>
							<span class="ns-acct__badge"><?php echo esc_html( $nolasharon_unpaid ); ?><span class="screen-reader-text"> <?php echo esc_html( _n( 'order awaiting payment', 'orders awaiting payment', $nolasharon_unpaid, 'nolasharon' ) ); ?></span></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="ns-acct__help">
		<strong><?php esc_html_e( 'Need help?', 'nolasharon' ); ?></strong>
		<span><?php esc_html_e( 'Questions about an order? Our team is happy to help.', 'nolasharon' ); ?></span>
		<a href="<?php echo esc_url( nolasharon_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'nolasharon' ); ?> <span aria-hidden="true">→</span></a>
		<?php if ( $nolasharon_whatsapp ) : ?>
			<a href="<?php echo esc_url( $nolasharon_whatsapp ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat on WhatsApp', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
		<?php endif; ?>
	</div>
</aside>

<dialog class="ns-modal" id="ns-logout" aria-labelledby="ns-logout-title" aria-describedby="ns-logout-text">
	<span class="ns-modal__icon" aria-hidden="true"><?php echo nolasharon_account_icon( 'customer-logout', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<h2 class="ns-modal__title" id="ns-logout-title"><?php esc_html_e( 'Log out?', 'nolasharon' ); ?></h2>
	<p class="ns-modal__text" id="ns-logout-text"><?php esc_html_e( 'You’ll need to sign in again to see your orders and saved details.', 'nolasharon' ); ?></p>
	<div class="ns-modal__actions">
		<button type="button" class="ns-btn ns-btn--ghost" data-close-dialog><?php esc_html_e( 'Cancel', 'nolasharon' ); ?></button>
		<a class="ns-btn" href="<?php echo esc_url( wc_logout_url() ); ?>"><?php esc_html_e( 'Log out', 'nolasharon' ); ?></a>
	</div>
</dialog>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>

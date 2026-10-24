<?php
/**
 * My Account dashboard (STORE-PAGES.md §3.1): a "pay now" banner for the latest unpaid
 * order, the latest order with its tracker, and quick links.
 *
 * Based on WooCommerce template myaccount/dashboard.php, version 4.4.0.
 *
 * @package NolaSharon
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_customer = get_current_user_id();
$nolasharon_unpaid   = wc_get_orders(
	array(
		'customer_id' => $nolasharon_customer,
		'status'      => nolasharon_unpaid_statuses(),
		'limit'       => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
$nolasharon_unpaid   = $nolasharon_unpaid && $nolasharon_unpaid[0]->needs_payment() ? $nolasharon_unpaid[0] : null;
$nolasharon_latest   = wc_get_orders(
	array(
		'customer_id' => $nolasharon_customer,
		'limit'       => 1,
		'orderby'     => 'date',
		'order'       => 'DESC',
	)
);
$nolasharon_latest   = $nolasharon_latest ? $nolasharon_latest[0] : null;

$nolasharon_tiles = array(
	'orders'       => array( __( 'Orders', 'nolasharon' ), __( 'Track, view and pay for your orders.', 'nolasharon' ), wc_get_account_endpoint_url( 'orders' ) ),
	'edit-address' => array( __( 'Addresses', 'nolasharon' ), __( 'Update your delivery address.', 'nolasharon' ), wc_get_account_endpoint_url( 'edit-address' ) ),
	'edit-account' => array( __( 'Account details', 'nolasharon' ), __( 'Change your name, email or password.', 'nolasharon' ), wc_get_account_endpoint_url( 'edit-account' ) ),
	'store'        => array( __( 'Store locations', 'nolasharon' ), __( 'Visit us in Ikeja or our new Ogudu branch.', 'nolasharon' ), nolasharon_page_url( 'store-locations' ) ),
);
?>
<div class="ns-dash">
	<?php if ( $nolasharon_unpaid ) : ?>
		<div class="ns-paybox">
			<span class="ns-paybox__icon" aria-hidden="true"><?php echo nolasharon_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<p class="ns-paybox__text">
				<strong><?php echo esc_html( sprintf( /* translators: %s: order number. */ __( 'Order #%s is awaiting payment.', 'nolasharon' ), $nolasharon_unpaid->get_order_number() ) ); ?></strong>
				<span><?php esc_html_e( 'Complete payment so we can start preparing it.', 'nolasharon' ); ?></span>
			</p>
			<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( $nolasharon_unpaid->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Pay now', 'nolasharon' ); ?><span class="screen-reader-text"> <?php echo esc_html( sprintf( /* translators: %s: order number. */ __( 'for order #%s', 'nolasharon' ), $nolasharon_unpaid->get_order_number() ) ); ?></span></a>
		</div>
	<?php endif; ?>

	<?php if ( $nolasharon_latest ) : ?>
		<section class="ns-card ns-latest" aria-labelledby="ns-latest-title">
			<div class="ns-latest__head">
				<div>
					<span class="ns-eyebrow"><?php esc_html_e( 'Latest order', 'nolasharon' ); ?></span>
					<h2 class="ns-latest__title" id="ns-latest-title"><?php echo esc_html( sprintf( /* translators: %s: order number. */ __( 'Order #%s', 'nolasharon' ), $nolasharon_latest->get_order_number() ) ); ?></h2>
					<p class="ns-latest__meta">
						<?php
						echo wp_kses_post(
							sprintf(
								/* translators: 1: date, 2: number of items, 3: order total. */
								__( 'Placed %1$s · %2$s · %3$s', 'nolasharon' ),
								esc_html( wc_format_datetime( $nolasharon_latest->get_date_created(), 'j M Y' ) ),
								esc_html( sprintf( /* translators: %d: number of items. */ _n( '%d item', '%d items', nolasharon_order_item_count( $nolasharon_latest ), 'nolasharon' ), nolasharon_order_item_count( $nolasharon_latest ) ) ),
								$nolasharon_latest->get_formatted_order_total()
							)
						);
						?>
					</p>
				</div>
				<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_latest->get_view_order_url() ); ?>"><?php esc_html_e( 'View order', 'nolasharon' ); ?></a>
			</div>
			<?php nolasharon_order_tracker( $nolasharon_latest ); ?>
		</section>
	<?php else : ?>
		<section class="ns-card ns-empty">
			<span class="ns-icon-tile" aria-hidden="true"><?php echo nolasharon_account_icon( 'orders', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<h2 class="ns-empty__title"><?php esc_html_e( 'You haven’t placed any orders yet.', 'nolasharon' ); ?></h2>
			<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
		</section>
	<?php endif; ?>

	<nav class="ns-tiles" aria-label="<?php esc_attr_e( 'Quick links', 'nolasharon' ); ?>">
		<?php foreach ( $nolasharon_tiles as $nolasharon_icon => $nolasharon_tile ) : ?>
			<a class="ns-tile" href="<?php echo esc_url( $nolasharon_tile[2] ); ?>">
				<span class="ns-tile__icon" aria-hidden="true"><?php echo nolasharon_account_icon( $nolasharon_icon, 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="ns-tile__title"><?php echo esc_html( $nolasharon_tile[0] ); ?></span>
				<span class="ns-tile__text"><?php echo esc_html( $nolasharon_tile[1] ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php
	/**
	 * Kept for plugins that add to the dashboard.
	 */
	do_action( 'woocommerce_account_dashboard' );
	do_action( 'woocommerce_before_my_account' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	do_action( 'woocommerce_after_my_account' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
	?>
</div>

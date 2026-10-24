<?php
/**
 * My orders (STORE-PAGES.md §3.2): status chips (?status=, a GET form so they work without
 * JavaScript; account.js swaps the list in place), one card per order and pill pagination.
 *
 * Based on WooCommerce template myaccount/orders.php, version 9.5.0.
 *
 * @package NolaSharon
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_filter  = nolasharon_current_order_filter();
$nolasharon_filters = nolasharon_order_filters();
$nolasharon_base    = wc_get_account_endpoint_url( 'orders' );
$nolasharon_page    = function ( $page ) use ( $nolasharon_filter ) {
	$url = wc_get_endpoint_url( 'orders', $page > 1 ? $page : '' );
	return $nolasharon_filter ? add_query_arg( 'status', $nolasharon_filter, $url ) : $url;
};

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<section class="ns-card ns-orders" aria-labelledby="ns-orders-title" data-orders>
	<div class="ns-orders__head">
		<h2 class="ns-orders__title" id="ns-orders-title"><?php esc_html_e( 'My orders', 'nolasharon' ); ?></h2>
		<form class="ns-chips" method="get" action="<?php echo esc_url( $nolasharon_base ); ?>" role="group" aria-label="<?php esc_attr_e( 'Filter orders', 'nolasharon' ); ?>" data-order-filters>
			<?php foreach ( $nolasharon_filters as $nolasharon_key => $nolasharon_f ) : ?>
				<button type="submit" class="ns-chip" name="status" value="<?php echo esc_attr( $nolasharon_key ); ?>" aria-pressed="<?php echo $nolasharon_key === $nolasharon_filter ? 'true' : 'false'; ?>"><?php echo esc_html( $nolasharon_f[0] ); ?></button>
			<?php endforeach; ?>
		</form>
	</div>

	<div class="ns-orders__list" data-orders-list aria-live="polite">
		<?php if ( $has_orders ) : ?>
			<?php
			foreach ( $customer_orders->orders as $nolasharon_order_id ) :
				$nolasharon_order = wc_get_order( $nolasharon_order_id );
				if ( ! $nolasharon_order ) {
					continue;
				}
				?>
				<div class="ns-collapse is-open ns-orow-wrap">
					<div>
						<article class="ns-orow">
							<?php nolasharon_order_thumbs( $nolasharon_order ); ?>
							<div class="ns-orow__main">
								<h3 class="ns-orow__no"><?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $nolasharon_order->get_order_number() ); ?></h3>
								<span class="ns-orow__meta"><?php echo esc_html( nolasharon_order_summary_line( $nolasharon_order ) ); ?></span>
							</div>
							<?php nolasharon_order_pill( $nolasharon_order ); ?>
							<span class="ns-orow__total"><?php echo wp_kses_post( $nolasharon_order->get_formatted_order_total() ); ?></span>
							<span class="ns-orow__actions">
								<?php if ( $nolasharon_order->needs_payment() ) : ?>
									<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( $nolasharon_order->get_checkout_payment_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: order number. */ __( 'Pay for order #%s', 'nolasharon' ), $nolasharon_order->get_order_number() ) ); ?>"><?php esc_html_e( 'Pay', 'nolasharon' ); ?></a>
								<?php endif; ?>
								<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: order number. */ __( 'View order #%s', 'nolasharon' ), $nolasharon_order->get_order_number() ) ); ?>"><?php esc_html_e( 'View', 'nolasharon' ); ?></a>
							</span>
						</article>
					</div>
				</div>
			<?php endforeach; ?>

			<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

			<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
				<nav class="ns-pager" aria-label="<?php esc_attr_e( 'Order pages', 'nolasharon' ); ?>">
					<?php if ( 1 !== $current_page ) : ?>
						<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_page( $current_page - 1 ) ); ?>"><?php echo nolasharon_icon( 'prev', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
					<?php endif; ?>
					<span class="ns-pager__info"><?php echo esc_html( sprintf( /* translators: 1: current page, 2: number of pages. */ __( 'Page %1$d of %2$d', 'nolasharon' ), $current_page, $customer_orders->max_num_pages ) ); ?></span>
					<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
						<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_page( $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?><?php echo nolasharon_icon( 'next', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
		<?php else : ?>
			<div class="ns-empty ns-empty--flat">
				<p class="ns-empty__title"><?php echo esc_html( $nolasharon_filter ? __( 'No orders with this status.', 'nolasharon' ) : __( 'You haven’t placed any orders yet.', 'nolasharon' ) ); ?></p>
				<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>

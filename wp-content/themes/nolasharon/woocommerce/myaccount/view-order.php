<?php
/**
 * Order detail (STORE-PAGES.md §3.3): status, "Pay now" when unpaid, the tracker, items,
 * delivery address or pickup branch, totals, updates from the shop, "Order again" and
 * "Get help with this order". WooCommerce's woocommerce_view_order and
 * woocommerce_order_details_after_order_table hooks still run for plugins.
 *
 * Based on WooCommerce template myaccount/view-order.php, version 10.6.0.
 *
 * @package NolaSharon
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_notes  = $order->get_customer_order_notes();
$nolasharon_pickup = nolasharon_order_is_pickup( $order );
$nolasharon_branch = $nolasharon_pickup ? nolasharon_order_pickup_branch( $order ) : null;
$nolasharon_totals = $order->get_order_item_totals();
$nolasharon_pay    = isset( $nolasharon_totals['payment_method'] ) ? $nolasharon_totals['payment_method']['value'] : '';
unset( $nolasharon_totals['payment_method'], $nolasharon_totals['order_total'] );
$nolasharon_help   = nolasharon_order_help_link( $order );
$nolasharon_again  = $order->has_status( apply_filters( 'woocommerce_valid_order_statuses_for_order_again', array( 'completed' ) ) );
?>
<section class="ns-card ns-order" aria-labelledby="ns-order-title">
	<a class="ns-auth__back ns-order__back" href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php echo nolasharon_icon( 'prev', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'All orders', 'nolasharon' ); ?></a>

	<div class="ns-order__head">
		<div>
			<h2 class="ns-order__title" id="ns-order-title"><?php echo esc_html( sprintf( /* translators: %s: order number. */ __( 'Order #%s', 'nolasharon' ), $order->get_order_number() ) ); ?></h2>
			<p class="ns-order__date"><?php echo esc_html( sprintf( /* translators: %s: date. */ __( 'Placed %s', 'nolasharon' ), wc_format_datetime( $order->get_date_created(), 'j M Y' ) ) ); ?></p>
		</div>
		<?php nolasharon_order_pill( $order ); ?>
	</div>

	<?php if ( $order->needs_payment() ) : ?>
		<div class="ns-paybox ns-paybox--sm">
			<p class="ns-paybox__text"><?php esc_html_e( 'This order is awaiting payment.', 'nolasharon' ); ?></p>
			<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">
				<?php
				/* translators: %s: order total. */
				echo wp_kses_post( sprintf( __( 'Pay now · %s', 'nolasharon' ), $order->get_formatted_order_total() ) );
				?>
			</a>
		</div>
	<?php endif; ?>

	<?php nolasharon_order_tracker( $order ); ?>

	<ul class="ns-items">
		<?php
		foreach ( $order->get_items() as $nolasharon_item ) :
			$nolasharon_product = $nolasharon_item->get_product();
			$nolasharon_image   = $nolasharon_product ? $nolasharon_product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'sizes' => '64px', 'loading' => 'lazy' ) ) : '';
			$nolasharon_meta    = wc_display_item_meta(
				$nolasharon_item,
				array(
					'echo'      => false,
					'before'    => '',
					'after'     => '',
					'separator' => ' · ',
					'label_before' => '',
					'label_after'  => ': ',
				)
			);
			$nolasharon_link    = $nolasharon_product && $nolasharon_product->is_visible() ? $nolasharon_product->get_permalink( $nolasharon_item ) : '';
			?>
			<li class="ns-items__row">
				<span class="ns-items__img"><?php echo $nolasharon_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce image markup. ?></span>
				<span class="ns-items__main">
					<?php if ( $nolasharon_link ) : ?>
						<a class="ns-items__name" href="<?php echo esc_url( $nolasharon_link ); ?>"><?php echo esc_html( $nolasharon_item->get_name() ); ?></a>
					<?php else : ?>
						<span class="ns-items__name"><?php echo esc_html( $nolasharon_item->get_name() ); ?></span>
					<?php endif; ?>
					<span class="ns-items__meta"><?php echo wp_kses_post( wp_strip_all_tags( $nolasharon_meta ) ? wp_strip_all_tags( $nolasharon_meta ) . ' · ' : '' ); ?><?php echo esc_html( sprintf( /* translators: %d: quantity. */ __( 'Qty %d', 'nolasharon' ), $nolasharon_item->get_quantity() ) ); ?></span>
				</span>
				<span class="ns-items__price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $nolasharon_item ) ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>

	<div class="ns-order__boxes">
		<div class="ns-order__box">
			<?php if ( $nolasharon_pickup ) : ?>
				<span class="ns-order__label"><?php esc_html_e( 'Store pickup', 'nolasharon' ); ?></span>
				<?php if ( $nolasharon_branch ) : ?>
					<strong><?php echo esc_html( nolasharon_pickup_branch_name( $nolasharon_branch ) ); ?></strong>
					<?php foreach ( array( 'address', 'hours_text', 'phone', 'pickup_instructions' ) as $nolasharon_key ) : ?>
						<?php if ( ! empty( $nolasharon_branch[ $nolasharon_key ] ) ) : ?>
							<span><?php echo nl2br( esc_html( $nolasharon_branch[ $nolasharon_key ] ) ); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php else : ?>
					<span><?php esc_html_e( 'We’ll confirm the branch with you.', 'nolasharon' ); ?></span>
				<?php endif; ?>
			<?php else : ?>
				<span class="ns-order__label"><?php esc_html_e( 'Delivery address', 'nolasharon' ); ?></span>
				<?php $nolasharon_address = $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address(); ?>
				<address><?php echo wp_kses_post( $nolasharon_address ? $nolasharon_address : esc_html__( 'No address on this order.', 'nolasharon' ) ); ?></address>
				<?php if ( $order->get_billing_phone() ) : ?>
					<span><?php echo esc_html( $order->get_billing_phone() ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="ns-order__box">
			<dl class="ns-order__totals">
				<?php foreach ( $nolasharon_totals as $nolasharon_row ) : ?>
					<div><dt><?php echo esc_html( rtrim( wp_strip_all_tags( $nolasharon_row['label'] ), ':' ) ); ?></dt><dd><?php echo wp_kses_post( $nolasharon_row['value'] ); ?></dd></div>
				<?php endforeach; ?>
				<div class="ns-order__total"><dt><?php esc_html_e( 'Total', 'nolasharon' ); ?></dt><dd><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd></div>
			</dl>
			<?php if ( $nolasharon_pay ) : ?>
				<span class="ns-order__pay">
					<?php
					echo esc_html(
						$order->is_paid()
							/* translators: %s: payment method. */
							? sprintf( __( 'Paid with %s', 'nolasharon' ), wp_strip_all_tags( $nolasharon_pay ) )
							/* translators: %s: payment method. */
							: sprintf( __( '%s · not paid yet', 'nolasharon' ), wp_strip_all_tags( $nolasharon_pay ) )
					);
					?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $nolasharon_notes ) : ?>
		<div class="ns-order__updates">
			<h3><?php esc_html_e( 'Updates from us', 'nolasharon' ); ?></h3>
			<ol>
				<?php foreach ( $nolasharon_notes as $nolasharon_note ) : ?>
					<li>
						<time datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $nolasharon_note->comment_date_gmt ) ) ); ?>"><?php echo esc_html( date_i18n( 'j M Y, g:ia', strtotime( $nolasharon_note->comment_date ) ) ); ?></time>
						<?php echo wp_kses_post( wpautop( wptexturize( $nolasharon_note->comment_content ) ) ); ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

	<div class="ns-order__actions">
		<?php if ( $nolasharon_again ) : ?>
			<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' ) ); ?>"><?php esc_html_e( 'Order again', 'nolasharon' ); ?></a>
		<?php endif; ?>
		<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_help[0] ); ?>"<?php echo $nolasharon_help[1] ? ' target="_blank" rel="noopener"' : ''; ?>><?php esc_html_e( 'Get help with this order', 'nolasharon' ); ?><?php if ( $nolasharon_help[1] ) : ?><span class="screen-reader-text"> <?php esc_html_e( '(opens WhatsApp in a new tab)', 'nolasharon' ); ?></span><?php endif; ?></a>
	</div>
</section>

<?php do_action( 'woocommerce_view_order', $order_id ); ?>

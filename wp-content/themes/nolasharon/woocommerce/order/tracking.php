<?php
/**
 * Track your order: the result (TRACK-ORDER.md §A4). Status with the tracker and message,
 * the updates timeline, pickup branch or delivery area, items and help. Shows only what's
 * needed on a public lookup: no phone, email or street address.
 *
 * Based on WooCommerce template order/tracking.php, version 10.6.0.
 *
 * @package NolaSharon
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_pickup   = nolasharon_order_is_pickup( $order );
$nolasharon_branch   = $nolasharon_pickup ? nolasharon_order_pickup_branch( $order ) : null;
$nolasharon_status   = $order->get_status();
$nolasharon_closed   = in_array( $nolasharon_status, array( 'cancelled', 'failed', 'refunded' ), true );
$nolasharon_moving   = in_array( $nolasharon_status, array( 'pending', 'on-hold', 'processing', 'dispatched', 'ready-pickup' ), true );
$nolasharon_message  = nolasharon_track_status_message( $order );
$nolasharon_timeline = nolasharon_track_timeline( $order );
$nolasharon_count    = nolasharon_order_item_count( $order );
$nolasharon_totals   = $order->get_order_item_totals();
$nolasharon_pay      = isset( $nolasharon_totals['payment_method'] ) ? wp_strip_all_tags( $nolasharon_totals['payment_method']['value'] ) : '';
unset( $nolasharon_totals['payment_method'], $nolasharon_totals['order_total'], $nolasharon_totals['cart_subtotal'] );
/* translators: %s: order number. */
$nolasharon_whatsapp = nolasharon_whatsapp_url( sprintf( __( 'Hi, I need help with order #%s', 'nolasharon' ), $order->get_order_number() ) );
$nolasharon_courier  = $order->get_meta( 'ns_courier' );
$nolasharon_tracking = $order->get_meta( 'ns_tracking_number' );
$nolasharon_trk_url  = $order->get_meta( 'ns_tracking_url' );
?>
<div class="container trk-res" data-track-found>

	<section class="trk-card trk-status" aria-labelledby="trk-order-title">
		<div class="trk-status__head">
			<div>
				<span class="ns-eyebrow"><?php esc_html_e( 'Order status', 'nolasharon' ); ?></span>
				<h2 class="trk-status__title" id="trk-order-title"><?php echo esc_html( sprintf( /* translators: %s: order number. */ __( 'Order #%s', 'nolasharon' ), $order->get_order_number() ) ); ?></h2>
				<p class="trk-status__meta">
					<?php
					echo wp_kses_post(
						implode(
							' · ',
							array(
								/* translators: %s: date. */
								esc_html( sprintf( __( 'Placed %s', 'nolasharon' ), wc_format_datetime( $order->get_date_created(), 'j M Y' ) ) ),
								/* translators: %d: number of items. */
								esc_html( sprintf( _n( '%d item', '%d items', $nolasharon_count, 'nolasharon' ), $nolasharon_count ) ),
								$order->get_formatted_order_total(),
								esc_html( $nolasharon_pickup ? __( 'Store pickup', 'nolasharon' ) : __( 'Home delivery', 'nolasharon' ) ),
							)
						)
					);
					?>
				</p>
			</div>
			<span class="ns-pill ns-pill--<?php echo esc_attr( $nolasharon_status ); ?> trk-status__pill">
				<?php if ( $nolasharon_moving ) : ?>
					<span class="trk-pulse" aria-hidden="true"></span>
				<?php endif; ?>
				<?php echo esc_html( wc_get_order_status_name( $nolasharon_status ) ); ?>
			</span>
		</div>

		<?php if ( ! $nolasharon_closed ) : ?>
			<?php nolasharon_order_tracker( $order, true ); ?>
		<?php endif; ?>

		<?php if ( $nolasharon_message ) : ?>
			<div class="trk-status__msg<?php echo $nolasharon_closed ? ' is-closed' : ''; ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v5h1"/></svg>
				<p>
					<?php echo esc_html( $nolasharon_message ); ?>
					<?php if ( $nolasharon_closed ) : ?>
						<a href="<?php echo esc_url( nolasharon_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'nolasharon' ); ?></a>
					<?php endif; ?>
				</p>
				<?php if ( $order->needs_payment() ) : ?>
					<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Pay now', 'nolasharon' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $nolasharon_tracking ) : ?>
			<div class="trk-ship">
				<span class="trk-ship__label"><?php esc_html_e( 'Shipment', 'nolasharon' ); ?></span>
				<?php if ( $nolasharon_courier ) : ?>
					<span><?php echo esc_html( $nolasharon_courier ); ?></span>
				<?php endif; ?>
				<span class="trk-ship__number"><code data-copy-source><?php echo esc_html( $nolasharon_tracking ); ?></code>
					<button type="button" class="trk-ship__copy" data-copy hidden aria-label="<?php esc_attr_e( 'Copy tracking number', 'nolasharon' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a1 1 0 0 1 1-1h10"/></svg></button>
				</span>
				<?php if ( $nolasharon_trk_url ) : ?>
					<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( $nolasharon_trk_url ); ?>" target="_blank" rel="noopener">
						<?php
						/* translators: %s: courier name. */
						echo esc_html( $nolasharon_courier ? sprintf( __( 'Track with %s', 'nolasharon' ), $nolasharon_courier ) : __( 'Track shipment', 'nolasharon' ) );
						?>
						<span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</section>

	<div class="trk-res__cols">
		<section class="trk-card trk-updates" aria-labelledby="trk-updates-title">
			<h2 class="trk-card__title" id="trk-updates-title"><?php esc_html_e( 'Order updates', 'nolasharon' ); ?></h2>
			<ol class="trk-timeline">
				<?php foreach ( $nolasharon_timeline as $nolasharon_i => $nolasharon_event ) : ?>
					<li class="trk-timeline__item<?php echo 0 === $nolasharon_i && $nolasharon_moving ? ' is-current' : ''; ?>" style="--i: <?php echo esc_attr( $nolasharon_i ); ?>">
						<span class="trk-timeline__dot" aria-hidden="true"></span>
						<div>
							<h3 class="trk-timeline__title"><?php echo esc_html( $nolasharon_event['title'] ); ?></h3>
							<?php if ( $nolasharon_event['text'] ) : ?>
								<p class="trk-timeline__text"><?php echo esc_html( $nolasharon_event['text'] ); ?></p>
							<?php endif; ?>
							<time class="trk-timeline__when" datetime="<?php echo esc_attr( $nolasharon_event['time']->date( 'c' ) ); ?>"><?php echo esc_html( nolasharon_lagos_time( $nolasharon_event['time'] ) ); ?></time>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>

		<div class="trk-res__side">
			<section class="trk-card trk-fulfil" aria-labelledby="trk-fulfil-title">
				<span class="trk-fulfil__circle" aria-hidden="true"></span>
				<?php if ( $nolasharon_pickup ) : ?>
					<span class="trk-fulfil__label"><?php esc_html_e( 'Store pickup', 'nolasharon' ); ?></span>
					<h2 class="trk-fulfil__title" id="trk-fulfil-title"><?php echo esc_html( $nolasharon_branch ? nolasharon_pickup_branch_name( $nolasharon_branch ) : __( 'Branch to be confirmed with you', 'nolasharon' ) ); ?></h2>
					<?php if ( $nolasharon_branch ) : ?>
						<?php foreach ( array( 'address', 'hours_text' ) as $nolasharon_key ) : ?>
							<?php if ( ! empty( $nolasharon_branch[ $nolasharon_key ] ) ) : ?>
								<p><?php echo nl2br( esc_html( $nolasharon_branch[ $nolasharon_key ] ) ); ?></p>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php $nolasharon_dir = ! empty( $nolasharon_branch['slug'] ) ? ns_branch_directions_url( $nolasharon_branch ) : ''; ?>
						<?php if ( $nolasharon_dir ) : ?>
							<a class="trk-fulfil__btn" href="<?php echo esc_url( $nolasharon_dir ); ?>" target="_blank" rel="noopener"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M3 11l18-8-8 18-2-8z"/></svg><?php esc_html_e( 'Get directions', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
						<?php endif; ?>
					<?php endif; ?>
				<?php else : ?>
					<?php
					$nolasharon_states = WC()->countries->get_states( $order->get_shipping_country() ? $order->get_shipping_country() : $order->get_billing_country() );
					$nolasharon_city   = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
					$nolasharon_state  = $order->get_shipping_state() ? $order->get_shipping_state() : $order->get_billing_state();
					$nolasharon_state  = isset( $nolasharon_states[ $nolasharon_state ] ) ? $nolasharon_states[ $nolasharon_state ] : $nolasharon_state;
					$nolasharon_name   = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : $order->get_billing_first_name();
					?>
					<span class="trk-fulfil__label"><?php esc_html_e( 'Delivery address', 'nolasharon' ); ?></span>
					<h2 class="trk-fulfil__title" id="trk-fulfil-title"><?php echo esc_html( $nolasharon_name ? $nolasharon_name : __( 'Home delivery', 'nolasharon' ) ); ?></h2>
					<?php if ( $nolasharon_city || $nolasharon_state ) : ?>
						<p><?php echo esc_html( implode( ', ', array_filter( array( $nolasharon_city, $nolasharon_state ) ) ) ); ?></p>
					<?php endif; ?>
					<?php if ( $order->get_shipping_method() ) : ?>
						<p><?php echo esc_html( wp_strip_all_tags( $order->get_shipping_method() ) ); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</section>

			<section class="trk-card trk-items" aria-labelledby="trk-items-title">
				<h2 class="trk-card__title" id="trk-items-title"><?php esc_html_e( 'Items', 'nolasharon' ); ?></h2>
				<ul class="trk-items__list">
					<?php foreach ( $order->get_items() as $nolasharon_item ) : ?>
						<?php
						$nolasharon_product = $nolasharon_item->get_product();
						$nolasharon_meta    = wp_strip_all_tags(
							wc_display_item_meta(
								$nolasharon_item,
								array(
									'echo'         => false,
									'before'       => '',
									'after'        => '',
									'separator'    => ' · ',
									'label_before' => '',
									'label_after'  => ': ',
								)
							)
						);
						?>
						<li class="trk-items__row">
							<span class="trk-items__img"><?php echo $nolasharon_product ? $nolasharon_product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'sizes' => '56px' ) ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="trk-items__main">
								<span class="trk-items__name"><?php echo esc_html( $nolasharon_product ? $nolasharon_product->get_title() : $nolasharon_item->get_name() ); ?></span>
								<span class="trk-items__meta"><?php echo esc_html( ( $nolasharon_meta ? $nolasharon_meta . ' · ' : '' ) . sprintf( /* translators: %d: quantity. */ __( 'Qty %d', 'nolasharon' ), $nolasharon_item->get_quantity() ) ); ?></span>
							</span>
							<span class="trk-items__price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $nolasharon_item ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<dl class="trk-items__totals">
					<?php foreach ( $nolasharon_totals as $nolasharon_row ) : ?>
						<div><dt><?php echo esc_html( rtrim( wp_strip_all_tags( $nolasharon_row['label'] ), ':' ) ); ?></dt><dd><?php echo wp_kses_post( $nolasharon_row['value'] ); ?></dd></div>
					<?php endforeach; ?>
					<div class="trk-items__total"><dt><?php esc_html_e( 'Total', 'nolasharon' ); ?></dt><dd><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd></div>
				</dl>
				<?php if ( $nolasharon_pay ) : ?>
					<p class="trk-items__pay">
						<?php
						/* translators: %s: payment method. */
						echo esc_html( $order->is_paid() ? sprintf( __( 'Paid with %s', 'nolasharon' ), $nolasharon_pay ) : sprintf( __( '%s · not paid yet', 'nolasharon' ), $nolasharon_pay ) );
						?>
					</p>
				<?php endif; ?>
			</section>

			<section class="trk-card trk-help">
				<h2 class="trk-help__title"><?php esc_html_e( 'Need help with this order?', 'nolasharon' ); ?></h2>
				<p><?php esc_html_e( 'Send us your order number and we’ll get back to you.', 'nolasharon' ); ?></p>
				<div class="trk-help__actions">
					<?php if ( $nolasharon_whatsapp ) : ?>
						<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( $nolasharon_whatsapp ); ?>" target="_blank" rel="noopener"><?php echo nolasharon_icon( 'whatsapp', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'WhatsApp us', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
					<?php else : ?>
						<a class="ns-btn ns-btn--sm" href="<?php echo esc_url( nolasharon_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'nolasharon' ); ?></a>
					<?php endif; ?>
					<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php echo esc_url( nolasharon_track_order_url() ); ?>" data-track-another><?php esc_html_e( 'Track another order', 'nolasharon' ); ?></a>
				</div>
			</section>
		</div>
	</div>

	<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>
</div>

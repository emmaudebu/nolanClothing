<?php
/**
 * Order received (CHECKOUT.md §7): an animated confirmation card with the order details,
 * delivery or pickup information and how to reach support. A failed or cancelled payment
 * shows a clear message and a way to try again, with no success animation.
 *
 * Override of WooCommerce templates/checkout/thankyou.php, version 11.2.0. Its hooks are kept,
 * so bank transfer details and gateway messages still print (woocommerce_thankyou_{gateway})
 * and the order items table still follows (woocommerce_thankyou → woocommerce_order_details_table).
 *
 * @package NolaSharon
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-order ns-thanks">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );

		$nolasharon_failed = $order->has_status( array( 'failed', 'cancelled' ) );
		$nolasharon_branch = nolasharon_order_pickup_branch( $order );
		?>

		<div class="ns-thanks__card<?php echo $nolasharon_failed ? ' ns-thanks__card--failed' : ''; ?>">

			<?php if ( $nolasharon_failed ) : ?>

				<?php
				$nolasharon_failed_text = $order->has_status( 'cancelled' )
					? __( 'This order was cancelled and has not been paid. You can try the payment again below.', 'nolasharon' )
					: __( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' );
				$nolasharon_failed_text = apply_filters( 'woocommerce_thankyou_order_failed_text', $nolasharon_failed_text, $order );
				?>
				<span class="ns-thanks__icon ns-thanks__icon--failed" aria-hidden="true"><?php echo nolasharon_icon( 'close', 34 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<h1 class="ns-thanks__title"><?php esc_html_e( 'Payment not completed', 'nolasharon' ); ?></h1>
				<p class="ns-thanks__lead woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed" role="alert"><?php echo wp_kses_post( is_string( $nolasharon_failed_text ) ? $nolasharon_failed_text : '' ); ?></p>
				<div class="ns-thanks__actions woocommerce-thankyou-order-failed-actions">
					<a class="ns-btn" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Try payment again', 'nolasharon' ); ?></a>
					<?php if ( is_user_logged_in() ) : ?>
						<a class="ns-btn ns-btn--ghost" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
					<?php endif; ?>
				</div>

			<?php else : ?>

				<svg class="ns-thanks__check" width="88" height="88" viewBox="0 0 88 88" aria-hidden="true" focusable="false">
					<circle class="ns-thanks__disc" cx="44" cy="44" r="40"/>
					<circle class="ns-thanks__ring" cx="44" cy="44" r="40"/>
					<path class="ns-thanks__tick" d="M28 45l11 11 21-23"/>
				</svg>
				<h1 class="ns-thanks__title">
					<?php
					$nolasharon_first = $order->get_billing_first_name();
					/* translators: %s: customer's first name. */
					echo esc_html( $nolasharon_first ? sprintf( __( 'Thank you, %s!', 'nolasharon' ), $nolasharon_first ) : __( 'Thank you!', 'nolasharon' ) );
					?>
				</h1>
				<p class="ns-thanks__lead woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
					<?php
					if ( $order->get_billing_email() ) {
						printf(
							/* translators: %s: customer's email address. */
							esc_html__( 'Your order has been placed. A confirmation has been sent to %s.', 'nolasharon' ),
							'<strong>' . esc_html( $order->get_billing_email() ) . '</strong>'
						);
					} else {
						esc_html_e( 'Your order has been placed.', 'nolasharon' );
					}
					?>
				</p>

				<dl class="ns-thanks__details woocommerce-order-overview woocommerce-thankyou-order-details order_details">
					<div>
						<dt><?php esc_html_e( 'Order number', 'nolasharon' ); ?></dt>
						<dd><?php echo esc_html( $order->get_order_number() ); ?></dd>
					</div>
					<div>
						<dt><?php esc_html_e( 'Date', 'nolasharon' ); ?></dt>
						<dd><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></dd>
					</div>
					<div>
						<dt><?php echo esc_html( $order->is_paid() ? __( 'Amount paid', 'nolasharon' ) : __( 'Amount', 'nolasharon' ) ); ?></dt>
						<dd>
							<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
							<?php if ( ! $order->is_paid() ) : ?>
								<span class="ns-thanks__status"><?php echo esc_html( $order->has_status( 'on-hold' ) ? __( 'Awaiting payment', 'nolasharon' ) : wc_get_order_status_name( $order->get_status() ) ); ?></span>
							<?php endif; ?>
						</dd>
					</div>
					<?php if ( $nolasharon_branch ) : ?>
						<div>
							<dt><?php esc_html_e( 'Pickup from', 'nolasharon' ); ?></dt>
							<dd>
								<?php echo esc_html( $nolasharon_branch['name'] ); ?>
								<?php foreach ( array( 'address', 'hours' ) as $nolasharon_key ) : ?>
									<?php if ( $nolasharon_branch[ $nolasharon_key ] ) : ?>
										<span class="ns-thanks__sub"><?php echo nl2br( esc_html( $nolasharon_branch[ $nolasharon_key ] ) ); ?></span>
									<?php endif; ?>
								<?php endforeach; ?>
							</dd>
						</div>
					<?php elseif ( $order->get_billing_city() || $order->get_billing_state() ) : ?>
						<?php
						$nolasharon_states = WC()->countries->get_states( $order->get_billing_country() );
						$nolasharon_state  = isset( $nolasharon_states[ $order->get_billing_state() ] ) ? $nolasharon_states[ $order->get_billing_state() ] : $order->get_billing_state();
						$nolasharon_where  = implode( ', ', array_filter( array( $order->get_billing_city(), $nolasharon_state ) ) );
						?>
						<?php if ( $nolasharon_where ) : ?>
							<div>
								<dt><?php esc_html_e( 'Delivering to', 'nolasharon' ); ?></dt>
								<dd><?php echo esc_html( $nolasharon_where ); ?></dd>
							</div>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( $order->get_payment_method_title() ) : ?>
						<div>
							<dt><?php esc_html_e( 'Payment method', 'nolasharon' ); ?></dt>
							<dd><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>

				<div class="ns-thanks__actions">
					<a class="ns-btn" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Continue shopping', 'nolasharon' ); ?></a>
					<a class="ns-btn ns-btn--ghost" href="<?php echo esc_url( is_user_logged_in() && $order->get_user_id() === get_current_user_id() ? $order->get_view_order_url() : $order->get_checkout_order_received_url() . '#ns-order-items' ); ?>"><?php esc_html_e( 'View my order', 'nolasharon' ); ?></a>
				</div>

			<?php endif; ?>

			<?php $nolasharon_support = nolasharon_order_support_links( $order ); ?>
			<?php if ( $nolasharon_support ) : ?>
				<p class="ns-thanks__support">
					<?php esc_html_e( 'Questions about your order?', 'nolasharon' ); ?>
					<?php foreach ( $nolasharon_support as $nolasharon_i => $nolasharon_link ) : ?>
						<?php echo $nolasharon_i ? '<span aria-hidden="true">·</span>' : ''; ?>
						<a href="<?php echo esc_url( $nolasharon_link[1], array( 'https', 'tel', 'mailto' ) ); ?>"<?php echo 0 === strpos( $nolasharon_link[1], 'https' ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $nolasharon_link[0] ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>

		<div class="ns-thanks__more" id="ns-order-items">
			<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
			<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>
		</div>

	<?php else : ?>

		<div class="ns-thanks__card">
			<h1 class="ns-thanks__title"><?php esc_html_e( 'Thank you!', 'nolasharon' ); ?></h1>
			<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>
		</div>

	<?php endif; ?>

</div>

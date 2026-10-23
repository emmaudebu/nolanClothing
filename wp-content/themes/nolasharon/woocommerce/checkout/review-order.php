<?php
/**
 * Order summary at checkout (CHECKOUT.md §4): thumbnail with quantity badge, name,
 * variation, quantity +/−, line total; then subtotal, delivery or pickup, and total.
 *
 * Override of WooCommerce templates/checkout/review-order.php, version 11.2.0. The item
 * markup needs a thumbnail and quantity buttons that CSS alone cannot add; the table, its
 * class (WooCommerce refreshes it by that class) and every hook and filter are kept. The
 * delivery choice itself is made in step 2, so the shipping row here is a summary line.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_count = WC()->cart->get_cart_contents_count();
?>
<table class="shop_table woocommerce-checkout-review-order-table ns-review" data-count-label="<?php echo esc_attr( sprintf( /* translators: %s: number of items. */ _n( '%s item', '%s items', $nolasharon_count, 'nolasharon' ), number_format_i18n( $nolasharon_count ) ) ); ?>">
	<thead class="screen-reader-text">
		<tr>
			<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th class="product-total"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$visible  = apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key );

			if ( ! ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) ) {
				continue;
			}
			$cart_item_name = WC()->cart->get_item_product_name( $cart_item, $_product );
			$product_name   = apply_filters( 'woocommerce_cart_item_name', $cart_item_name, $cart_item, $cart_item_key );
			$max_qty        = $_product->get_max_purchase_quantity();
			$qty            = (int) $cart_item['quantity'];
			?>
			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<td class="product-name">
					<div class="ns-line">
						<span class="ns-line__thumb">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', array( 'alt' => '' ) ), $cart_item, $cart_item_key ) ); ?>
							<span class="ns-line__badge" aria-hidden="true"><?php echo esc_html( $qty ); ?></span>
						</span>
						<span class="ns-line__body">
							<span class="ns-line__name"><?php echo wp_kses_post( $product_name ); ?></span>
							<?php echo wc_get_formatted_cart_item_data( $cart_item, false, $cart_item_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php
							$qty_html = '<span class="screen-reader-text">' . esc_html( sprintf( /* translators: %s: quantity. */ __( 'Quantity: %s', 'nolasharon' ), $qty ) ) . '</span>';
							if ( ! $_product->is_sold_individually() ) {
								$qty_html .= '<span class="ns-qty" data-qty data-key="' . esc_attr( $cart_item_key ) . '" data-max="' . esc_attr( $max_qty ) . '">'
									. '<button type="button" class="ns-qty__btn" data-qty-step="-1" aria-label="' . esc_attr( sprintf( /* translators: %s: product name. */ __( 'Decrease quantity of %s', 'nolasharon' ), wp_strip_all_tags( $cart_item_name ) ) ) . '"' . disabled( $qty <= 1, true, false ) . '>' . nolasharon_icon( 'minus', 14 ) . '</button>'
									. '<span class="ns-qty__val" aria-hidden="true">' . esc_html( $qty ) . '</span>'
									. '<button type="button" class="ns-qty__btn" data-qty-step="1" aria-label="' . esc_attr( sprintf( /* translators: %s: product name. */ __( 'Increase quantity of %s', 'nolasharon' ), wp_strip_all_tags( $cart_item_name ) ) ) . '"' . disabled( $max_qty > 0 && $qty >= $max_qty, true, false ) . '>' . nolasharon_icon( 'plus', 14 ) . '</button>'
									. '</span>';
							}
							echo apply_filters( 'woocommerce_checkout_cart_item_quantity', $qty_html, $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</span>
					</div>
				</td>
				<td class="product-total">
					<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</td>
			</tr>
			<?php
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<?php
			$nolasharon_chosen = (array) WC()->session->get( 'chosen_shipping_methods', array() );
			foreach ( WC()->shipping()->get_packages() as $nolasharon_i => $nolasharon_package ) :
				$nolasharon_rate_id = isset( $nolasharon_chosen[ $nolasharon_i ] ) ? $nolasharon_chosen[ $nolasharon_i ] : '';
				$nolasharon_rate    = isset( $nolasharon_package['rates'][ $nolasharon_rate_id ] ) ? $nolasharon_package['rates'][ $nolasharon_rate_id ] : null;
				?>
				<tr class="woocommerce-shipping-totals shipping">
					<?php if ( ! $nolasharon_rate ) : ?>
						<th><?php esc_html_e( 'Delivery', 'nolasharon' ); ?></th>
						<td class="ns-review__pending"><?php esc_html_e( 'Chosen in step 2', 'nolasharon' ); ?></td>
					<?php elseif ( nolasharon_is_pickup_method( $nolasharon_rate->get_method_id() ) ) : ?>
						<th><?php esc_html_e( 'Store pickup', 'nolasharon' ); ?></th>
						<td><?php echo wp_kses_post( nolasharon_rate_cost_html( $nolasharon_rate ) ); ?></td>
					<?php else : ?>
						<?php /* translators: %s: delivery area, e.g. Lagos Mainland. */ ?>
						<th><?php echo esc_html( sprintf( __( 'Delivery · %s', 'nolasharon' ), nolasharon_rate_label( $nolasharon_rate ) ) ); ?></th>
						<td><?php echo wp_kses_post( nolasharon_rate_cost_html( $nolasharon_rate ) ); ?></td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</tfoot>
</table>

<?php
/**
 * Cart panel: the items, with chosen size/colour, quantity and remove.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="drawer-sec" data-drawer-bag aria-labelledby="bag-title">
	<h3 class="drawer-sec__title" id="bag-title"><?php echo nolasharon_icon( 'cart', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Your Cart', 'nolasharon' ); ?></h3>

	<?php if ( WC()->cart->is_empty() ) : ?>
		<div class="bag-empty">
			<p><?php esc_html_e( 'Your cart is empty.', 'nolasharon' ); ?></p>
			<a class="btn btn--dark" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
		</div>
	<?php else : ?>
		<?php
		foreach ( WC()->cart->get_cart() as $nolasharon_key => $nolasharon_item ) :
			$nolasharon_product = $nolasharon_item['data'];
			if ( ! $nolasharon_product || ! $nolasharon_product->exists() || $nolasharon_item['quantity'] <= 0 ) {
				continue;
			}
			$nolasharon_details = nolasharon_cart_item_rows( $nolasharon_item );
			$nolasharon_regular = (float) wc_get_price_to_display( $nolasharon_product, array( 'price' => $nolasharon_product->get_regular_price() ) );
			$nolasharon_current = (float) wc_get_price_to_display( $nolasharon_product );
			$nolasharon_max     = $nolasharon_product->get_max_purchase_quantity();
			?>
			<article class="bag-item">
				<button class="bag-item__remove" type="button" data-bag-remove="<?php echo esc_attr( $nolasharon_key ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Remove %s from your cart', 'nolasharon' ), $nolasharon_product->get_name() ) ); ?>">
					<?php echo nolasharon_icon( 'trash', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
				<div class="bag-item__card">
					<div class="bag-item__info">
						<a class="bag-item__name" href="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>"><?php echo esc_html( $nolasharon_product->get_title() ); ?></a>
						<p class="bag-item__price">
							<?php if ( $nolasharon_regular > $nolasharon_current ) : ?>
								<s><?php echo wp_kses_post( wc_price( $nolasharon_regular ) ); ?></s>
							<?php endif; ?>
							<span><?php echo wp_kses_post( wc_price( $nolasharon_current ) ); ?></span>
						</p>
						<?php if ( $nolasharon_product->is_sold_individually() ) : ?>
							<p class="bag-item__qty-note"><?php esc_html_e( 'Qty 1', 'nolasharon' ); ?></p>
						<?php else : ?>
							<div class="qty qty--sm">
								<button class="qty__btn" type="button" data-step="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'minus', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
								<input class="qty__input" type="number" value="<?php echo esc_attr( $nolasharon_item['quantity'] ); ?>" min="1"<?php echo $nolasharon_max > 0 ? ' max="' . esc_attr( $nolasharon_max ) . '"' : ''; ?> step="1" inputmode="numeric" data-bag-qty="<?php echo esc_attr( $nolasharon_key ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Quantity of %s', 'nolasharon' ), $nolasharon_product->get_name() ) ); ?>">
								<button class="qty__btn" type="button" data-step="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'plus', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							</div>
						<?php endif; ?>
					</div>
					<div class="bag-item__media">
						<?php nolasharon_product_image( $nolasharon_product ); ?>
					</div>
					<?php if ( $nolasharon_details ) : ?>
						<dl class="bag-item__details">
							<?php foreach ( $nolasharon_details as $nolasharon_label => $nolasharon_value ) : ?>
								<div><dt><?php echo esc_html( $nolasharon_label ); ?>:</dt> <dd><?php echo wp_kses_post( $nolasharon_value ); ?></dd></div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	<?php endif; ?>
</section>

<?php
/**
 * Cart panel items: photo, name, chosen colour/size, a quantity stepper, the line price and
 * remove. Or the empty state.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( WC()->cart->is_empty() ) : ?>
	<div class="dbag-empty">
		<span class="dbag-empty__icon" aria-hidden="true"><?php echo nolasharon_icon( 'cart', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<p class="dbag-empty__title"><?php esc_html_e( 'Your cart is empty', 'nolasharon' ); ?></p>
		<p class="dbag-empty__text"><?php esc_html_e( 'Find something you love in our collection.', 'nolasharon' ); ?></p>
		<a class="ns-btn" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
	</div>
	<?php
	return;
endif;
?>
<ul class="dbag-list" data-drawer-bag aria-label="<?php esc_attr_e( 'Items in your cart', 'nolasharon' ); ?>">
	<?php
	foreach ( WC()->cart->get_cart() as $nolasharon_key => $nolasharon_item ) :
		$nolasharon_product = $nolasharon_item['data'];
		if ( ! $nolasharon_product || ! $nolasharon_product->exists() || $nolasharon_item['quantity'] <= 0 ) {
			continue;
		}
		$nolasharon_qty     = (int) $nolasharon_item['quantity'];
		$nolasharon_details = array();
		foreach ( nolasharon_cart_item_rows( $nolasharon_item ) as $nolasharon_label => $nolasharon_value ) {
			$nolasharon_details[] = $nolasharon_label . ': ' . wp_strip_all_tags( $nolasharon_value );
		}
		$nolasharon_regular = (float) wc_get_price_to_display( $nolasharon_product, array( 'price' => $nolasharon_product->get_regular_price() ) );
		$nolasharon_current = (float) wc_get_price_to_display( $nolasharon_product );
		$nolasharon_max     = $nolasharon_product->get_max_purchase_quantity();
		$nolasharon_name    = $nolasharon_product->get_name();
		$nolasharon_link    = $nolasharon_product->get_permalink();
		?>
		<li class="ditem">
			<a class="ditem__img" href="<?php echo esc_url( $nolasharon_link ); ?>" tabindex="-1" aria-hidden="true"><?php nolasharon_product_image( $nolasharon_product ); ?></a>
			<div class="ditem__main">
				<div class="ditem__top">
					<a class="ditem__name" href="<?php echo esc_url( $nolasharon_link ); ?>"><?php echo esc_html( $nolasharon_product->get_title() ); ?></a>
					<button class="ditem__remove" type="button" data-bag-remove="<?php echo esc_attr( $nolasharon_key ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Remove %s from your cart', 'nolasharon' ), $nolasharon_name ) ); ?>">
						<?php echo nolasharon_icon( 'trash', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
				<?php if ( $nolasharon_details ) : ?>
					<p class="ditem__meta"><?php echo esc_html( implode( ' · ', $nolasharon_details ) ); ?></p>
				<?php endif; ?>
				<div class="ditem__bottom">
					<?php if ( $nolasharon_product->is_sold_individually() ) : ?>
						<span class="ditem__single"><?php esc_html_e( 'Qty 1', 'nolasharon' ); ?></span>
					<?php else : ?>
						<div class="dqty">
							<button class="dqty__btn" type="button" data-step="-1" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Decrease quantity of %s', 'nolasharon' ), $nolasharon_name ) ); ?>"<?php disabled( $nolasharon_qty <= 1 ); ?>><?php echo nolasharon_icon( 'minus', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
							<input class="dqty__input" type="number" value="<?php echo esc_attr( $nolasharon_qty ); ?>" min="1"<?php echo $nolasharon_max > 0 ? ' max="' . esc_attr( $nolasharon_max ) . '"' : ''; ?> step="1" inputmode="numeric" data-bag-qty="<?php echo esc_attr( $nolasharon_key ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Quantity of %s', 'nolasharon' ), $nolasharon_name ) ); ?>">
							<button class="dqty__btn" type="button" data-step="1" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Increase quantity of %s', 'nolasharon' ), $nolasharon_name ) ); ?>"<?php disabled( $nolasharon_max > 0 && $nolasharon_qty >= $nolasharon_max ); ?>><?php echo nolasharon_icon( 'plus', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
						</div>
					<?php endif; ?>
					<p class="ditem__price">
						<?php if ( $nolasharon_regular > $nolasharon_current ) : ?>
							<s><span class="screen-reader-text"><?php esc_html_e( 'Was', 'nolasharon' ); ?> </span><?php echo wp_kses_post( wc_price( $nolasharon_regular * $nolasharon_qty ) ); ?></s>
						<?php endif; ?>
						<span class="ditem__now"><?php echo wp_kses_post( wc_price( $nolasharon_current * $nolasharon_qty ) ); ?></span>
					</p>
				</div>
			</div>
		</li>
	<?php endforeach; ?>
</ul>

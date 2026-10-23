<?php
/**
 * Cart (STORE-PAGES.md §4.1–§4.3): item cards and a summary card. Quantities update on their
 * own after a pause and removed items slide away with an "Undo" toast (cart.js). Without
 * JavaScript the form posts with the "Update cart" button and remove links work as normal.
 * Parts marked data-sync are refreshed from the server after each change.
 *
 * Based on WooCommerce template cart/cart.php, version 11.2.0.
 *
 * @package NolaSharon
 * @version 11.2.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ns-cart" data-cart>
	<?php nolasharon_cart_header(); ?>

	<?php do_action( 'woocommerce_before_cart' ); ?>

	<div class="ns-cart__layout">
		<div class="ns-cart__main ns-in ns-in--1">
			<form class="woocommerce-cart-form ns-cart__form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" data-cart-form>
				<?php do_action( 'woocommerce_before_cart_table' ); ?>
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<div class="ns-citems">
					<?php
					foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
						$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
						$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

						if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
							continue;
						}
						$nolasharon_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_title(), $cart_item, $cart_item_key ); // Title without the variation's attributes; they show on the line below.
						$nolasharon_link = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
						$nolasharon_max  = $_product->get_max_purchase_quantity();
						$nolasharon_rows = nolasharon_cart_item_rows( $cart_item );
						$nolasharon_meta = array();
						foreach ( $nolasharon_rows as $nolasharon_label => $nolasharon_value ) {
							$nolasharon_meta[] = $nolasharon_label . ': ' . wp_strip_all_tags( $nolasharon_value );
						}
						$nolasharon_plain = wp_strip_all_tags( $nolasharon_name );
						?>
						<div class="ns-collapse is-open ns-citem-wrap" data-item="<?php echo esc_attr( $cart_item_key ); ?>" data-name="<?php echo esc_attr( $nolasharon_plain ); ?>">
							<div>
								<article class="ns-citem <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
									<?php
									$nolasharon_thumb = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'sizes' => '104px' ) ), $cart_item, $cart_item_key );
									if ( $nolasharon_link ) {
										printf( '<a class="ns-citem__img" href="%1$s" tabindex="-1" aria-hidden="true">%2$s</a>', esc_url( $nolasharon_link ), $nolasharon_thumb ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo '<span class="ns-citem__img" aria-hidden="true">' . $nolasharon_thumb . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									?>
									<div class="ns-citem__body">
										<div class="ns-citem__top">
											<h2 class="ns-citem__name">
												<?php if ( $nolasharon_link ) : ?>
													<a href="<?php echo esc_url( $nolasharon_link ); ?>"><?php echo wp_kses_post( $nolasharon_name ); ?></a>
												<?php else : ?>
													<?php echo wp_kses_post( $nolasharon_name ); ?>
												<?php endif; ?>
											</h2>
											<?php
											echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
												'woocommerce_cart_item_remove_link',
												sprintf(
													'<a href="%1$s" class="ns-citem__remove remove" aria-label="%2$s" data-cart-remove data-product_id="%3$s" data-product_sku="%4$s">%5$s</a>',
													esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
													/* translators: %s: product name. */
													esc_attr( sprintf( __( 'Remove %s from cart', 'nolasharon' ), $nolasharon_plain ) ),
													esc_attr( $product_id ),
													esc_attr( $_product->get_sku() ),
													nolasharon_icon( 'trash', 18 )
												),
												$cart_item_key
											);
											?>
										</div>
										<?php if ( $nolasharon_meta ) : ?>
											<p class="ns-citem__meta"><?php echo esc_html( implode( ' · ', $nolasharon_meta ) ); ?></p>
										<?php endif; ?>
										<?php do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key ); ?>
										<div data-sync="stock-<?php echo esc_attr( $cart_item_key ); ?>"><?php nolasharon_cart_stock_line( $_product, $cart_item['quantity'] ); ?></div>

										<div class="ns-citem__foot">
											<?php if ( $_product->is_sold_individually() ) : ?>
												<span class="ns-citem__single"><?php esc_html_e( 'Qty 1', 'nolasharon' ); ?></span>
												<input type="hidden" name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" value="1" />
											<?php else : ?>
												<span class="ns-qty">
													<button type="button" class="ns-qty__btn" data-step="-1" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Decrease quantity of %s', 'nolasharon' ), $nolasharon_plain ) ); ?>">−</button>
													<label class="screen-reader-text" for="qty-<?php echo esc_attr( $cart_item_key ); ?>"><?php echo esc_html( sprintf( /* translators: %s: product name. */ __( 'Quantity of %s', 'nolasharon' ), $nolasharon_plain ) ); ?></label>
													<input class="ns-qty__input qty" type="number" id="qty-<?php echo esc_attr( $cart_item_key ); ?>" name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="1"<?php echo $nolasharon_max > 0 ? ' max="' . esc_attr( $nolasharon_max ) . '"' : ''; ?> step="1" inputmode="numeric" data-qty />
													<button type="button" class="ns-qty__btn" data-step="1" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Increase quantity of %s', 'nolasharon' ), $nolasharon_plain ) ); ?>">+</button>
												</span>
											<?php endif; ?>
											<span class="ns-citem__price" data-sync="price-<?php echo esc_attr( $cart_item_key ); ?>">
												<strong><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>
												<?php if ( $cart_item['quantity'] > 1 ) : ?>
													<span><?php echo wp_kses_post( sprintf( /* translators: %s: unit price. */ __( '%s each', 'nolasharon' ), apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ) ) ); ?></span>
												<?php endif; ?>
											</span>
										</div>
									</div>
								</article>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<?php do_action( 'woocommerce_cart_contents' ); ?>

				<div class="ns-cart__actions">
					<?php if ( nolasharon_cart_coupons() ) : ?>
						<div class="ns-cart__coupon">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
							<input type="text" name="coupon_code" class="ns-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
							<button type="submit" class="ns-btn ns-btn--ghost ns-btn--sm" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php endif; ?>
					<button type="submit" class="ns-btn ns-btn--ghost ns-btn--sm ns-cart__update" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" data-update><?php esc_html_e( 'Update cart', 'nolasharon' ); ?></button>
					<?php do_action( 'woocommerce_cart_actions' ); ?>
					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</div>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_table' ); ?>
			</form>

			<?php if ( WC()->cart->needs_shipping() ) : ?>
				<p class="ns-cart__note"><?php echo nolasharon_icon( 'truck', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Delivery fees are calculated at checkout based on your area.', 'nolasharon' ); ?></span></p>
			<?php endif; ?>
			<a class="ns-auth__back ns-cart__continue" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php echo nolasharon_icon( 'prev', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Continue shopping', 'nolasharon' ); ?></a>
		</div>

		<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

		<aside class="cart-collaterals ns-cart__side ns-in ns-in--2" aria-label="<?php esc_attr_e( 'Cart summary', 'nolasharon' ); ?>">
			<?php do_action( 'woocommerce_cart_collaterals' ); ?>
		</aside>
	</div>

	<?php nolasharon_cart_recommendations(); ?>

	<div class="ns-cart__bar" data-sync="bar">
		<span class="ns-cart__bar-total"><span><?php esc_html_e( 'Total', 'nolasharon' ); ?></span><strong><?php echo wp_kses_post( nolasharon_cart_total_before_delivery() ); ?></strong></span>
		<a class="ns-btn ns-cart__bar-btn" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'nolasharon' ); ?><svg class="ns-btn__arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
	</div>

	<?php do_action( 'woocommerce_after_cart' ); ?>
</div>

<?php
/**
 * Buy form (SHOP-PRODUCT.md §3.3–§3.4): colour swatches, size buttons, the stock line,
 * quantity and Add to Cart. Shared by the product page ("single") and the quick view ("quick").
 *
 * Variable products post WooCommerce's own fields (variation_id + attribute_*), so without
 * JavaScript the normal add-to-cart still works and the chosen size and colour reach the
 * cart, orders and emails. theme.js disables sold-out options, keeps the price and stock
 * line in step, and adds to the cart in place.
 *
 * Args: product (WC_Product), context ("single" or "quick").
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_product  = $args['product'];
$nolasharon_quick    = isset( $args['context'] ) && 'quick' === $args['context'];
$nolasharon_pre      = $nolasharon_quick ? 'qv' : 'ns'; // Keeps IDs unique when both are on one page.
$nolasharon_variable = $nolasharon_product->is_type( 'variable' );
$nolasharon_groups   = $nolasharon_variable ? nolasharon_buy_attributes( $nolasharon_product ) : array();
$nolasharon_vars     = $nolasharon_variable ? nolasharon_buy_variations( $nolasharon_product ) : array();
$nolasharon_stock    = nolasharon_stock_status( $nolasharon_product );
$nolasharon_max      = $nolasharon_product->get_max_purchase_quantity();
$nolasharon_chart    = nolasharon_size_chart( $nolasharon_product );
$nolasharon_last     = $nolasharon_groups ? end( $nolasharon_groups ) : null;
$nolasharon_prompt   = $nolasharon_last ? sprintf( /* translators: %s: e.g. "size". */ __( 'Select a %s to see availability', 'nolasharon' ), strtolower( $nolasharon_last['label'] ) ) : '';
/* translators: %s: product details for the message, e.g. "Adire Wrap Dress (Indigo, size M)". */
$nolasharon_wa       = nolasharon_whatsapp_url( '__NS_WA__' );
?>
<form class="cart pbuy__form" id="<?php echo esc_attr( $nolasharon_pre ); ?>-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $nolasharon_product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" novalidate data-ajax
	data-add="<?php echo esc_url( WC_AJAX::get_endpoint( 'ns_add_to_cart' ) ); ?>"
	data-name="<?php echo esc_attr( $nolasharon_product->get_name() ); ?>"
	data-url="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>"
	<?php if ( $nolasharon_variable ) : ?>
		data-variations="<?php echo esc_attr( wp_json_encode( $nolasharon_vars ) ); ?>"
	<?php endif; ?>>

	<?php foreach ( $nolasharon_groups as $nolasharon_gi => $nolasharon_group ) : ?>
		<?php
		$nolasharon_gid   = $nolasharon_pre . '-attr-' . $nolasharon_gi;
		$nolasharon_lower = strtolower( $nolasharon_group['label'] );
		?>
		<div class="attr-group<?php echo $nolasharon_group['is_colour'] ? ' attr-group--colour' : ' attr-group--size'; ?>" role="radiogroup" aria-labelledby="<?php echo esc_attr( $nolasharon_gid ); ?>" data-attribute="<?php echo esc_attr( $nolasharon_group['name'] ); ?>" data-name="<?php echo esc_attr( $nolasharon_lower ); ?>">
			<div class="pbuy__row">
				<span class="pbuy__label" id="<?php echo esc_attr( $nolasharon_gid ); ?>">
					<?php echo esc_html( $nolasharon_group['label'] ); ?>: <span class="pbuy__chosen" data-chosen data-empty="<?php echo esc_attr( sprintf( /* translators: %s: e.g. "size". */ __( 'Select a %s', 'nolasharon' ), $nolasharon_lower ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s: e.g. "size". */ __( 'Select a %s', 'nolasharon' ), $nolasharon_lower ) ); ?></span>
				</span>
				<?php if ( $nolasharon_group['is_size'] && $nolasharon_chart ) : ?>
					<button class="pbuy__guide" type="button" data-open-dialog="<?php echo esc_attr( $nolasharon_pre ); ?>-size-guide">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M3 8h18v8H3zM7 8v3M11 8v4M15 8v3M19 8v4"/></svg>
						<?php esc_html_e( 'Size guide', 'nolasharon' ); ?>
					</button>
				<?php endif; ?>
			</div>
			<div class="<?php echo $nolasharon_group['is_colour'] ? 'swatch-grid' : 'size-grid'; ?>">
				<?php foreach ( $nolasharon_group['options'] as $nolasharon_opt ) : ?>
					<label class="<?php echo $nolasharon_group['is_colour'] ? 'swatch-opt' : 'size-opt'; ?>">
						<input type="radio" name="<?php echo esc_attr( $nolasharon_group['name'] ); ?>" value="<?php echo esc_attr( $nolasharon_opt['value'] ); ?>" data-label="<?php echo esc_attr( $nolasharon_opt['label'] ); ?>"<?php checked( 1 === count( $nolasharon_group['options'] ) ); ?>>
						<?php if ( $nolasharon_group['is_colour'] ) : ?>
							<span class="swatch-opt__ring" aria-hidden="true"><span class="swatch-opt__dot" style="--swatch: <?php echo esc_attr( $nolasharon_opt['hex'] ? $nolasharon_opt['hex'] : '#cccccc' ); ?>"></span></span>
							<span class="screen-reader-text"><?php echo esc_html( $nolasharon_opt['label'] ); ?></span>
						<?php else : ?>
							<span class="size-opt__box"><?php echo esc_html( $nolasharon_opt['label'] ); ?></span>
						<?php endif; ?>
						<span class="screen-reader-text" data-opt-status></span>
					</label>
				<?php endforeach; ?>
			</div>
			<p class="attr-group__error" role="alert" data-group-error hidden><?php echo esc_html( sprintf( /* translators: %s: e.g. "size". */ __( 'Please select a %s.', 'nolasharon' ), $nolasharon_lower ) ); ?></p>
		</div>
	<?php endforeach; ?>

	<?php if ( $nolasharon_variable ) : ?>
		<input type="hidden" name="variation_id" value="0">
	<?php endif; ?>

	<p class="pbuy__stock stock--<?php echo esc_attr( $nolasharon_variable ? 'none' : $nolasharon_stock['state'] ); ?>" data-stock aria-live="polite" data-prompt="<?php echo esc_attr( $nolasharon_prompt ); ?>" data-sold-out="<?php esc_attr_e( 'Sold out', 'nolasharon' ); ?>">
		<span class="pbuy__dot" aria-hidden="true"></span><span data-stock-text><?php echo esc_html( $nolasharon_variable ? $nolasharon_prompt : $nolasharon_stock['text'] ); ?></span>
	</p>

	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<div class="pbuy__actions">
		<?php if ( ! $nolasharon_product->is_sold_individually() ) : ?>
			<span class="pbuy__qty">
				<button class="pbuy__step qty__btn" type="button" data-step="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'nolasharon' ); ?>">−</button>
				<label class="screen-reader-text" for="<?php echo esc_attr( $nolasharon_pre ); ?>-qty"><?php esc_html_e( 'Quantity', 'nolasharon' ); ?></label>
				<input id="<?php echo esc_attr( $nolasharon_pre ); ?>-qty" class="qty__input" type="number" name="quantity" value="1" min="1"<?php echo $nolasharon_max > 0 ? ' max="' . esc_attr( $nolasharon_max ) . '"' : ''; ?> step="1" inputmode="numeric">
				<button class="pbuy__step qty__btn" type="button" data-step="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'nolasharon' ); ?>">+</button>
			</span>
		<?php else : ?>
			<input type="hidden" name="quantity" value="1">
		<?php endif; ?>
		<?php if ( ! $nolasharon_quick ) : ?>
			<button class="pbuy__submit single_add_to_cart_button" type="submit" name="add-to-cart" value="<?php echo esc_attr( $nolasharon_product->get_id() ); ?>" data-label-adding="<?php esc_attr_e( 'Adding…', 'nolasharon' ); ?>" data-label-added="<?php esc_attr_e( 'Added to cart', 'nolasharon' ); ?>">
				<span data-submit-label><?php esc_html_e( 'Add to Cart', 'nolasharon' ); ?></span>
			</button>
			<?php
			if ( function_exists( 'nolasharon_wishlist_button' ) ) {
				nolasharon_wishlist_button( $nolasharon_product, 'single' );
			}
			?>
		<?php endif; ?>
	</div>

	<input type="hidden" name="product_id" value="<?php echo esc_attr( $nolasharon_product->get_id() ); ?>">
	<input type="hidden" name="ns_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ns-add-to-cart' ) ); ?>">

	<p class="pbuy__hint" role="alert" data-hint
		data-msg-soldout="<?php esc_attr_e( 'This combination is sold out. Please choose another.', 'nolasharon' ); ?>"
		data-sold-out="<?php esc_attr_e( 'sold out', 'nolasharon' ); ?>"
		data-error="<?php esc_attr_e( 'Something went wrong. Please try again.', 'nolasharon' ); ?>"></p>

	<?php if ( $nolasharon_wa && ! $nolasharon_quick ) : ?>
		<a class="pbuy__ask" href="<?php echo esc_url( str_replace( '__NS_WA__', rawurlencode( sprintf( /* translators: 1: product name, 2: product URL. */ __( 'Hi, I\'m interested in %1$s – %2$s', 'nolasharon' ), $nolasharon_product->get_name(), $nolasharon_product->get_permalink() ) ), $nolasharon_wa ) ); ?>" target="_blank" rel="noopener" data-wa="<?php echo esc_url( $nolasharon_wa ); ?>" data-wa-text="<?php /* translators: 1: product name with options, 2: product URL. */ esc_attr_e( 'Hi, I\'m interested in %1$s – %2$s', 'nolasharon' ); ?>" data-wa-size="<?php /* translators: %s: size. */ esc_attr_e( 'size %s', 'nolasharon' ); ?>">
			<?php echo nolasharon_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'Ask about this item on WhatsApp', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span>
		</a>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>

	<?php
	if ( $nolasharon_chart ) {
		get_template_part(
			'template-parts/size-guide-modal',
			null,
			array(
				'chart' => $nolasharon_chart,
				'id'    => $nolasharon_pre . '-size-guide',
			)
		);
	}
	?>
</form>

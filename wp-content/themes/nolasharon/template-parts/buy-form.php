<?php
/**
 * Buy form: colour swatches, size buttons, stock status, quantity and Add to Cart.
 * Shared by the product page ("single") and the quick view ("quick").
 *
 * Variable products post WooCommerce's own fields (variation_id + attribute_*), so the
 * chosen size and colour appear in the cart, order screen and emails without extra code.
 * theme.js disables options with no in-stock variation (brief §5.2).
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
$nolasharon_weight   = $nolasharon_product->get_weight();
$nolasharon_max      = $nolasharon_product->get_max_purchase_quantity();
$nolasharon_missing  = implode( ' ' . __( 'and', 'nolasharon' ) . ' ', array_map( 'strtolower', wp_list_pluck( $nolasharon_groups, 'label' ) ) );
?>
<form class="cart pbuy__form" id="<?php echo esc_attr( $nolasharon_pre ); ?>-form" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $nolasharon_product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" novalidate
	<?php echo $nolasharon_quick ? ' data-ajax' : ''; ?>
	<?php if ( $nolasharon_variable ) : ?>
		data-variations="<?php echo esc_attr( wp_json_encode( $nolasharon_vars ) ); ?>"
	<?php endif; ?>>

	<?php foreach ( $nolasharon_groups as $nolasharon_gi => $nolasharon_group ) : ?>
		<?php $nolasharon_gid = $nolasharon_pre . '-attr-' . $nolasharon_gi; ?>
		<div class="attr-group<?php echo $nolasharon_group['is_colour'] ? ' attr-group--colour' : ''; ?>" role="radiogroup" aria-labelledby="<?php echo esc_attr( $nolasharon_gid ); ?>" data-attribute="<?php echo esc_attr( $nolasharon_group['name'] ); ?>">
			<div class="pbuy__row">
				<span class="pbuy__label" id="<?php echo esc_attr( $nolasharon_gid ); ?>">
					<?php echo esc_html( $nolasharon_group['label'] ); ?><span class="attr-group__chosen" data-chosen></span>
				</span>
				<?php if ( $nolasharon_group['is_size'] ) : ?>
					<button class="pbuy__link" type="button" data-open-dialog="size-guide">
						<?php esc_html_e( 'Size guide', 'nolasharon' ); ?>
						<?php echo nolasharon_icon( 'external', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				<?php endif; ?>
			</div>
			<div class="<?php echo $nolasharon_group['is_colour'] ? 'swatch-grid' : 'size-grid'; ?>">
				<?php foreach ( $nolasharon_group['options'] as $nolasharon_opt ) : ?>
					<label class="<?php echo $nolasharon_group['is_colour'] ? 'swatch-opt' : 'size-opt'; ?>">
						<input type="radio" name="<?php echo esc_attr( $nolasharon_group['name'] ); ?>" value="<?php echo esc_attr( $nolasharon_opt['value'] ); ?>" data-label="<?php echo esc_attr( $nolasharon_opt['label'] ); ?>">
						<?php if ( $nolasharon_group['is_colour'] ) : ?>
							<span class="swatch-opt__dot" style="--swatch: <?php echo esc_attr( $nolasharon_opt['hex'] ? $nolasharon_opt['hex'] : '#cccccc' ); ?>" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php echo esc_html( $nolasharon_opt['label'] ); ?></span>
						<?php else : ?>
							<span><?php echo esc_html( $nolasharon_opt['label'] ); ?></span>
						<?php endif; ?>
						<span class="opt-status screen-reader-text" data-opt-status></span>
					</label>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>

	<?php if ( $nolasharon_variable ) : ?>
		<input type="hidden" name="variation_id" value="0">
		<button class="pbuy__reset" type="button" data-reset hidden><?php esc_html_e( 'Clear selection', 'nolasharon' ); ?></button>
	<?php endif; ?>

	<p class="pbuy__stock-status" data-stock aria-live="polite"<?php echo $nolasharon_variable ? ' hidden' : ''; ?>>
		<span class="stock-dot stock-dot--<?php echo esc_attr( $nolasharon_stock['state'] ); ?>" aria-hidden="true"></span><span data-stock-text><?php echo esc_html( $nolasharon_stock['text'] ); ?></span>
	</p>

	<div class="pbuy__row pbuy__row--meta">
		<div>
			<label class="pbuy__label" for="<?php echo esc_attr( $nolasharon_pre ); ?>-qty"><?php esc_html_e( 'Quantity', 'nolasharon' ); ?></label>
			<div class="qty">
				<button class="qty__btn" type="button" data-step="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'minus', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				<input id="<?php echo esc_attr( $nolasharon_pre ); ?>-qty" class="qty__input" type="number" name="quantity" value="1" min="1"<?php echo $nolasharon_max > 0 ? ' max="' . esc_attr( $nolasharon_max ) . '"' : ''; ?> step="1" inputmode="numeric">
				<button class="qty__btn" type="button" data-step="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'plus', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</div>
		</div>
		<?php if ( $nolasharon_weight ) : ?>
			<div class="pbuy__weight">
				<span class="pbuy__label"><?php esc_html_e( 'Product Weight', 'nolasharon' ); ?></span>
				<span class="pbuy__weight-value"><?php echo nolasharon_icon( 'weight', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( wc_format_weight( $nolasharon_weight ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<?php if ( $nolasharon_quick ) : ?>
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $nolasharon_product->get_id() ); ?>">
	<?php else : ?>
		<?php if ( $nolasharon_variable ) : ?>
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $nolasharon_product->get_id() ); ?>">
		<?php endif; ?>
		<div class="pbuy__actions">
			<button class="pbuy__submit single_add_to_cart_button" type="submit" name="add-to-cart" value="<?php echo esc_attr( $nolasharon_product->get_id() ); ?>">
				<span><?php esc_html_e( 'Add to Cart', 'nolasharon' ); ?></span>
				<?php echo nolasharon_icon( 'cart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<?php
			if ( function_exists( 'nolasharon_wishlist_button' ) ) {
				nolasharon_wishlist_button( $nolasharon_product, 'single' );
			}
			?>
		</div>
	<?php endif; ?>
	<p class="pbuy__hint" aria-live="polite"
		data-msg-choose="<?php echo esc_attr( sprintf( /* translators: %s: e.g. "colour and size". */ __( 'Choose a %s to continue.', 'nolasharon' ), $nolasharon_missing ) ); ?>"
		data-msg-soldout="<?php esc_attr_e( 'This combination is sold out. Please choose another.', 'nolasharon' ); ?>"
		data-sold-out="<?php esc_attr_e( 'Sold out', 'nolasharon' ); ?>"></p>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>

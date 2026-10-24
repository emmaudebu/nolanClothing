<?php
/**
 * Shop filters (SHOP-PRODUCT.md §2.3): a sidebar on desktop and a bottom sheet on phones.
 * One GET form; sections fold open and shut. Sizes and colours offer only what the current
 * results contain. Without JavaScript the footer button submits; with it, every change
 * applies straight away (shop.js) and the button just closes the sheet.
 *
 * Args: found (number of results, for "Show {n} results").
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_current = nolasharon_current_filters();
$nolasharon_sel     = nolasharon_selected_cats();
$nolasharon_cats    = nolasharon_nav_categories();
$nolasharon_bands   = nolasharon_price_bands();
$nolasharon_found   = (int) $args['found'];
$nolasharon_action  = is_search() ? home_url( '/' ) : nolasharon_shop_url();

$nolasharon_section = function ( $id, $title ) {
	printf(
		'<section class="fsec" data-fsec="%1$s"><h3 class="fsec__head"><button type="button" class="fsec__toggle" aria-expanded="true" aria-controls="fsec-%1$s" data-fsec-toggle>%2$s%3$s</button></h3><div class="ns-collapse is-open fsec__body" id="fsec-%1$s"><div><div class="fsec__inner">',
		esc_attr( $id ),
		esc_html( $title ),
		nolasharon_icon( 'chevron', 18 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
};
$nolasharon_end = function () {
	echo '</div></div></div></section>';
};
?>
<div class="shop-scrim" data-sheet-scrim hidden></div>
<aside class="shop-filters" id="shop-filters" aria-labelledby="shop-filters-title" data-shop="filters">
	<div class="shop-filters__head">
		<span class="shop-filters__handle" aria-hidden="true"></span>
		<div class="shop-filters__titlebar">
			<h2 class="shop-filters__title" id="shop-filters-title"><?php esc_html_e( 'Filters', 'nolasharon' ); ?></h2>
			<button class="shop-filters__close" type="button" data-sheet-close aria-label="<?php esc_attr_e( 'Close filters', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
	</div>

	<form class="shop-filters__form" method="get" action="<?php echo esc_url( $nolasharon_action ); ?>" data-filters-form>
		<div class="shop-filters__scroll">
			<?php if ( is_search() ) : ?>
				<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
				<input type="hidden" name="post_type" value="product">
			<?php endif; ?>
			<?php if ( $nolasharon_current['orderby'] ) : ?>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $nolasharon_current['orderby'] ); ?>" data-filter-orderby>
			<?php endif; ?>

			<?php if ( $nolasharon_cats ) : ?>
				<?php $nolasharon_section( 'cat', __( 'Category', 'nolasharon' ) ); ?>
				<ul class="fcats">
					<?php foreach ( $nolasharon_cats as $nolasharon_cat ) : ?>
						<li>
							<label class="fcheck">
								<input type="checkbox" name="product_cat[]" value="<?php echo esc_attr( $nolasharon_cat->slug ); ?>" data-url="<?php echo esc_url( get_term_link( $nolasharon_cat ) ); ?>" <?php checked( in_array( $nolasharon_cat->slug, $nolasharon_sel, true ) ); ?>>
								<span class="fcheck__label"><?php echo esc_html( $nolasharon_cat->name ); ?></span>
								<span class="fcheck__count"><?php echo esc_html( $nolasharon_cat->count ); ?></span>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php $nolasharon_end(); ?>
			<?php endif; ?>

			<?php if ( $nolasharon_bands ) : ?>
				<?php $nolasharon_section( 'price', __( 'Price', 'nolasharon' ) ); ?>
				<div class="fprices">
					<label class="fprice">
						<input type="radio" name="price" value="" <?php checked( '', $nolasharon_current['price'] ); ?>>
						<span class="fprice__dot" aria-hidden="true"></span><?php esc_html_e( 'Any price', 'nolasharon' ); ?>
					</label>
					<?php foreach ( $nolasharon_bands as $nolasharon_key => $nolasharon_band ) : ?>
						<label class="fprice">
							<input type="radio" name="price" value="<?php echo esc_attr( $nolasharon_key ); ?>" <?php checked( $nolasharon_key, $nolasharon_current['price'] ); ?>>
							<span class="fprice__dot" aria-hidden="true"></span><?php echo esc_html( $nolasharon_band[2] ); ?>
						</label>
					<?php endforeach; ?>
				</div>
				<?php $nolasharon_end(); ?>
			<?php endif; ?>

			<?php foreach ( nolasharon_attribute_filters() as $nolasharon_key => $nolasharon_tax ) : ?>
				<?php $nolasharon_terms = nolasharon_filter_terms( $nolasharon_tax, $nolasharon_key ); ?>
				<?php if ( ! $nolasharon_terms ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php $nolasharon_colour = 'pa_colour' === $nolasharon_tax; ?>
				<?php $nolasharon_section( $nolasharon_key, wc_attribute_label( $nolasharon_tax ) ); ?>
				<div class="<?php echo $nolasharon_colour ? 'fcolours' : 'fsizes'; ?>">
					<?php foreach ( $nolasharon_terms as $nolasharon_row ) : ?>
						<?php $nolasharon_t = $nolasharon_row['term']; ?>
						<label class="<?php echo $nolasharon_colour ? 'fcolour' : 'fsize'; ?>">
							<input type="checkbox" name="<?php echo esc_attr( $nolasharon_key ); ?>[]" value="<?php echo esc_attr( $nolasharon_t->slug ); ?>" <?php checked( in_array( $nolasharon_t->slug, $nolasharon_current[ $nolasharon_key ], true ) ); ?>>
							<?php if ( $nolasharon_colour ) : ?>
								<?php $nolasharon_hex = sanitize_hex_color( get_term_meta( $nolasharon_t->term_id, 'nolasharon_hex', true ) ); ?>
								<span class="fcolour__ring" aria-hidden="true"><span class="fcolour__swatch" style="--swatch: <?php echo esc_attr( $nolasharon_hex ? $nolasharon_hex : '#cccccc' ); ?>"></span></span>
							<?php endif; ?>
							<span class="<?php echo $nolasharon_colour ? 'fcolour__name' : 'fsize__label'; ?>"><?php echo esc_html( $nolasharon_t->name ); ?><span class="screen-reader-text"> (<?php echo esc_html( sprintf( /* translators: %d: number of products. */ _n( '%d product', '%d products', $nolasharon_row['count'], 'nolasharon' ), $nolasharon_row['count'] ) ); ?>)</span></span>
						</label>
					<?php endforeach; ?>
				</div>
				<?php $nolasharon_end(); ?>
			<?php endforeach; ?>

			<?php $nolasharon_section( 'stock', __( 'Availability', 'nolasharon' ) ); ?>
			<label class="fswitch">
				<span><?php esc_html_e( 'In stock only', 'nolasharon' ); ?></span>
				<input type="checkbox" role="switch" name="stock" value="instock" <?php checked( 'instock', $nolasharon_current['stock'] ); ?>>
				<span class="fswitch__track" aria-hidden="true"><span class="fswitch__knob"></span></span>
			</label>
			<?php $nolasharon_end(); ?>

			<a class="shop-filters__clear" href="<?php echo esc_url( nolasharon_filter_base_url() ); ?>" data-shop-link><?php esc_html_e( 'Clear all filters', 'nolasharon' ); ?></a>
		</div>

		<div class="shop-filters__foot">
			<button class="ns-btn ns-btn--block" type="submit" data-sheet-submit data-label="<?php /* translators: %d: number of products. */ esc_attr_e( 'Show %d results', 'nolasharon' ); ?>" data-label-one="<?php esc_attr_e( 'Show 1 result', 'nolasharon' ); ?>">
				<?php echo esc_html( 1 === $nolasharon_found ? __( 'Show 1 result', 'nolasharon' ) : sprintf( /* translators: %d: number of products. */ __( 'Show %d results', 'nolasharon' ), $nolasharon_found ) ); ?>
			</button>
		</div>
	</form>
</aside>

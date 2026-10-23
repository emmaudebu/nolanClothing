<?php
/**
 * Shop filters panel. Slides in from the left; groups with nothing to offer are hidden.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_current = nolasharon_current_filters();
$nolasharon_cats    = nolasharon_nav_categories();
$nolasharon_here    = is_product_taxonomy() ? get_queried_object_id() : 0;
?>
<div class="filters-backdrop" aria-hidden="true"></div>
<aside class="filters" id="shop-filters" aria-labelledby="filters-title">
	<div class="filters__head">
		<h2 class="filters__title" id="filters-title"><?php esc_html_e( 'Filter', 'nolasharon' ); ?></h2>
		<button class="icon-btn" type="button" data-filters-close aria-label="<?php esc_attr_e( 'Close filters', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	</div>

	<?php if ( $nolasharon_cats ) : ?>
		<nav class="filters__group" aria-labelledby="filter-cat-title">
			<h3 class="filters__label" id="filter-cat-title"><?php esc_html_e( 'Category', 'nolasharon' ); ?></h3>
			<ul class="filters__cats">
				<li><a href="<?php echo esc_url( nolasharon_shop_url() ); ?>"<?php echo is_shop() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'All products', 'nolasharon' ); ?></a></li>
				<?php foreach ( $nolasharon_cats as $nolasharon_cat ) : ?>
					<li>
						<a href="<?php echo esc_url( get_term_link( $nolasharon_cat ) ); ?>"<?php echo $nolasharon_here === $nolasharon_cat->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $nolasharon_cat->name ); ?></a>
						<?php $nolasharon_children = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $nolasharon_cat->term_id, 'hide_empty' => true ) ); ?>
						<?php if ( $nolasharon_children && ! is_wp_error( $nolasharon_children ) ) : ?>
							<ul>
								<?php foreach ( $nolasharon_children as $nolasharon_child ) : ?>
									<li><a href="<?php echo esc_url( get_term_link( $nolasharon_child ) ); ?>"<?php echo $nolasharon_here === $nolasharon_child->term_id ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $nolasharon_child->name ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<form class="filters__form" method="get" action="<?php echo esc_url( $args['base'] ); ?>">
		<?php if ( is_search() ) : ?>
			<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>
		<?php if ( $nolasharon_current['orderby'] ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $nolasharon_current['orderby'] ); ?>">
		<?php endif; ?>

		<?php foreach ( nolasharon_attribute_filters() as $nolasharon_key => $nolasharon_tax ) : ?>
			<?php $nolasharon_terms = nolasharon_filter_terms( $nolasharon_tax ); ?>
			<?php if ( ! $nolasharon_terms ) : ?>
				<?php continue; ?>
			<?php endif; ?>
			<fieldset class="filters__group">
				<legend class="filters__label"><?php echo esc_html( wc_attribute_label( $nolasharon_tax ) ); ?></legend>
				<div class="<?php echo 'pa_colour' === $nolasharon_tax ? 'filters__swatches' : 'filters__chips'; ?>">
					<?php foreach ( $nolasharon_terms as $nolasharon_t ) : ?>
						<label class="filter-opt">
							<input type="checkbox" name="<?php echo esc_attr( $nolasharon_key ); ?>[]" value="<?php echo esc_attr( $nolasharon_t->slug ); ?>" <?php checked( in_array( $nolasharon_t->slug, $nolasharon_current[ $nolasharon_key ], true ) ); ?>>
							<?php if ( 'pa_colour' === $nolasharon_tax ) : ?>
								<?php $nolasharon_hex = sanitize_hex_color( get_term_meta( $nolasharon_t->term_id, 'nolasharon_hex', true ) ); ?>
								<span class="filter-opt__dot" style="--swatch: <?php echo esc_attr( $nolasharon_hex ? $nolasharon_hex : '#cccccc' ); ?>" aria-hidden="true"></span>
							<?php endif; ?>
							<span><?php echo esc_html( $nolasharon_t->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endforeach; ?>

		<fieldset class="filters__group">
			<legend class="filters__label"><?php esc_html_e( 'Price', 'nolasharon' ); ?></legend>
			<div class="filters__price">
				<label><span><?php esc_html_e( 'Min', 'nolasharon' ); ?></span><input type="number" name="min_price" min="0" step="500" inputmode="numeric" value="<?php echo esc_attr( $nolasharon_current['min_price'] ); ?>"></label>
				<label><span><?php esc_html_e( 'Max', 'nolasharon' ); ?></span><input type="number" name="max_price" min="0" step="500" inputmode="numeric" value="<?php echo esc_attr( $nolasharon_current['max_price'] ); ?>"></label>
			</div>
		</fieldset>

		<fieldset class="filters__group">
			<legend class="filters__label"><?php esc_html_e( 'Availability', 'nolasharon' ); ?></legend>
			<label class="filter-check"><input type="checkbox" name="ns_instock" value="1" <?php checked( $nolasharon_current['ns_instock'] ); ?>> <?php esc_html_e( 'In stock only', 'nolasharon' ); ?></label>
		</fieldset>

		<div class="filters__actions">
			<a class="btn btn--outline btn--sm" href="<?php echo esc_url( $args['base'] ); ?>"><?php esc_html_e( 'Clear', 'nolasharon' ); ?></a>
			<button class="btn btn--dark btn--sm" type="submit"><?php esc_html_e( 'Show results', 'nolasharon' ); ?></button>
		</div>
	</form>
</aside>

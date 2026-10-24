<?php
/**
 * Product listing (SHOP-PRODUCT.md §2): the shop, category pages and product search.
 * Head band with category pills, toolbar (filters, count, sort, grid density), active filter
 * chips, the filters (sidebar on desktop, bottom sheet on phones) and the grid with
 * pagination. Parts marked data-shop are swapped in place by shop.js; without JavaScript
 * every control is a link or a GET form.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$nolasharon_term   = is_product_taxonomy() && ! nolasharon_query_cats() ? get_queried_object() : null;
if ( 1 === count( nolasharon_query_cats() ) ) {
	// One category chosen in the filter form (without JavaScript): title the page after it.
	$nolasharon_term = get_term_by( 'slug', nolasharon_query_cats()[0], 'product_cat' );
	$nolasharon_term = $nolasharon_term ? $nolasharon_term : null;
}
$nolasharon_active = nolasharon_active_filters();
$nolasharon_cur    = nolasharon_current_filters();
$nolasharon_found  = (int) $wp_query->found_posts;
$nolasharon_cats   = nolasharon_nav_categories();
$nolasharon_sel    = nolasharon_selected_cats();
$nolasharon_sort   = $nolasharon_cur['orderby'] ? $nolasharon_cur['orderby'] : get_option( 'woocommerce_default_catalog_orderby', 'date' );

if ( is_search() ) {
	/* translators: %s: search terms. */
	$nolasharon_title = sprintf( __( 'Search results for “%s”', 'nolasharon' ), get_search_query() );
	$nolasharon_mark  = __( 'Search', 'nolasharon' );
} elseif ( $nolasharon_term instanceof WP_Term ) {
	$nolasharon_title = $nolasharon_term->name;
	$nolasharon_mark  = $nolasharon_term->name;
} else {
	$nolasharon_title = __( 'Shop', 'nolasharon' );
	$nolasharon_mark  = __( 'Shop', 'nolasharon' );
}
$nolasharon_intro = '';
if ( $nolasharon_term instanceof WP_Term ) {
	$nolasharon_intro = trim( $nolasharon_term->description );
} elseif ( ! is_search() ) {
	$nolasharon_intro = __( 'Explore the full NolaSharon Clothing collection.', 'nolasharon' );
}
$nolasharon_count_text = sprintf( /* translators: %d: number of products. */ _n( '%d product', '%d products', $nolasharon_found, 'nolasharon' ), $nolasharon_found );
?>
<header class="shop-head">
	<span class="shop-head__mark" aria-hidden="true"><?php echo esc_html( $nolasharon_mark ); ?></span>
	<div class="container shop-head__inner ns-in">
		<nav class="shop-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
			<span aria-hidden="true">/</span>
			<?php if ( is_shop() && ! is_search() && ! $nolasharon_term ) : ?>
				<span aria-current="page"><?php esc_html_e( 'Shop', 'nolasharon' ); ?></span>
			<?php else : ?>
				<a href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop', 'nolasharon' ); ?></a>
				<?php if ( $nolasharon_term instanceof WP_Term ) : ?>
					<?php foreach ( array_reverse( get_ancestors( $nolasharon_term->term_id, $nolasharon_term->taxonomy ) ) as $nolasharon_ancestor ) : ?>
						<?php $nolasharon_parent = get_term( $nolasharon_ancestor, $nolasharon_term->taxonomy ); ?>
						<span aria-hidden="true">/</span>
						<a href="<?php echo esc_url( get_term_link( $nolasharon_parent ) ); ?>"><?php echo esc_html( $nolasharon_parent->name ); ?></a>
					<?php endforeach; ?>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php echo esc_html( $nolasharon_term->name ); ?></span>
				<?php elseif ( is_search() ) : ?>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php esc_html_e( 'Search', 'nolasharon' ); ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</nav>
		<h1 class="shop-head__title"><?php echo esc_html( $nolasharon_title ); ?></h1>
		<?php if ( $nolasharon_intro ) : ?>
			<div class="shop-head__intro"><?php echo wp_kses_post( wpautop( $nolasharon_intro ) ); ?></div>
		<?php endif; ?>
		<?php if ( $nolasharon_cats && ! is_search() ) : ?>
			<nav class="shop-pills" aria-label="<?php esc_attr_e( 'Categories', 'nolasharon' ); ?>" data-shop="pills">
				<a class="shop-pill" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"<?php echo ! $nolasharon_sel ? ' aria-current="page"' : ''; ?> data-shop-link><?php esc_html_e( 'All', 'nolasharon' ); ?></a>
				<?php foreach ( $nolasharon_cats as $nolasharon_cat ) : ?>
					<?php $nolasharon_on = array( $nolasharon_cat->slug ) === $nolasharon_sel; ?>
					<a class="shop-pill" href="<?php echo esc_url( get_term_link( $nolasharon_cat ) ); ?>"<?php echo $nolasharon_on ? ' aria-current="page"' : ''; ?> data-shop-link><?php echo esc_html( $nolasharon_cat->name ); ?><span class="shop-pill__count"><?php echo esc_html( $nolasharon_cat->count ); ?></span></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>
</header>

<div class="container shop-body" data-shop-root data-base="<?php echo esc_url( nolasharon_filter_base_url() ); ?>">
	<?php woocommerce_output_all_notices(); ?>

	<div class="shop-bar">
		<div class="shop-bar__start">
			<button class="shop-bar__filters" type="button" aria-controls="shop-filters" aria-expanded="true" data-filters-toggle>
				<?php echo nolasharon_icon( 'filter', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span data-filters-label data-show="<?php esc_attr_e( 'Show filters', 'nolasharon' ); ?>" data-hide="<?php esc_attr_e( 'Hide filters', 'nolasharon' ); ?>"><?php esc_html_e( 'Filters', 'nolasharon' ); ?></span>
				<span class="shop-bar__badge" data-shop="badge"<?php echo $nolasharon_active ? '' : ' hidden'; ?>><?php echo esc_html( count( $nolasharon_active ) ); ?><span class="screen-reader-text"> <?php esc_html_e( 'active', 'nolasharon' ); ?></span></span>
			</button>
			<p class="shop-bar__count" aria-live="polite" data-shop="count"><?php echo esc_html( $nolasharon_count_text ); ?></p>
		</div>
		<div class="shop-bar__end">
			<form class="shop-sort" method="get" action="<?php echo esc_url( nolasharon_filter_base_url() ); ?>" data-sort-form>
				<label for="shop-orderby"><?php esc_html_e( 'Sort by', 'nolasharon' ); ?></label>
				<select id="shop-orderby" name="orderby" data-sort data-default="<?php echo esc_attr( get_option( 'woocommerce_default_catalog_orderby', 'date' ) ); ?>">
					<?php foreach ( nolasharon_sort_options() as $nolasharon_value => $nolasharon_label ) : ?>
						<option value="<?php echo esc_attr( $nolasharon_value ); ?>" <?php selected( $nolasharon_sort, $nolasharon_value ); ?>><?php echo esc_html( $nolasharon_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php nolasharon_filter_hidden_inputs( array( 'orderby' ) ); ?>
				<noscript><button class="ns-btn ns-btn--sm" type="submit"><?php esc_html_e( 'Sort', 'nolasharon' ); ?></button></noscript>
			</form>
			<div class="shop-density" role="group" aria-label="<?php esc_attr_e( 'Products per row', 'nolasharon' ); ?>" data-density hidden>
				<button type="button" data-cols="3" aria-pressed="true" aria-label="<?php esc_attr_e( '3 per row', 'nolasharon' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="9.5" y="3" width="5" height="18" rx="1"/><rect x="16" y="3" width="5" height="18" rx="1"/></svg></button>
				<button type="button" data-cols="4" aria-pressed="false" aria-label="<?php esc_attr_e( '4 per row', 'nolasharon' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><rect x="2" y="3" width="4" height="18" rx="1"/><rect x="7.3" y="3" width="4" height="18" rx="1"/><rect x="12.6" y="3" width="4" height="18" rx="1"/><rect x="18" y="3" width="4" height="18" rx="1"/></svg></button>
			</div>
		</div>
	</div>

	<div class="ns-collapse shop-chips<?php echo $nolasharon_active ? ' is-open' : ''; ?>" data-shop="chips">
		<div>
			<?php if ( $nolasharon_active ) : ?>
				<ul class="shop-chips__list" aria-label="<?php esc_attr_e( 'Active filters', 'nolasharon' ); ?>">
					<?php foreach ( $nolasharon_active as $nolasharon_chip ) : ?>
						<li><a class="shop-chip" href="<?php echo esc_url( $nolasharon_chip['remove'] ); ?>" data-shop-link><?php echo esc_html( $nolasharon_chip['label'] ); ?><span class="screen-reader-text"> <?php esc_html_e( '(remove filter)', 'nolasharon' ); ?></span><?php echo nolasharon_icon( 'close', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
					<?php endforeach; ?>
					<li><a class="shop-chips__clear" href="<?php echo esc_url( nolasharon_filter_base_url() ); ?>" data-shop-link><?php esc_html_e( 'Clear all', 'nolasharon' ); ?></a></li>
				</ul>
			<?php endif; ?>
		</div>
	</div>

	<div class="shop-layout" data-shop-layout>
		<?php get_template_part( 'template-parts/shop-filters', null, array( 'found' => $nolasharon_found ) ); ?>

		<div class="shop-main">
			<div class="shop-results" data-shop="results">
				<?php if ( have_posts() ) : ?>
					<div class="shop-grid" id="product-grid">
						<?php
						while ( have_posts() ) :
							the_post();
							$nolasharon_product = wc_get_product( get_the_ID() );
							if ( $nolasharon_product && $nolasharon_product->is_visible() ) {
								nolasharon_product_card( $nolasharon_product );
							}
						endwhile;
						?>
					</div>
					<?php
					$nolasharon_pages = paginate_links(
						array(
							'base'      => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
							'format'    => '',
							'current'   => max( 1, get_query_var( 'paged' ) ),
							'total'     => $wp_query->max_num_pages,
							'prev_text' => nolasharon_icon( 'prev', 16 ) . '<span class="screen-reader-text">' . esc_html__( 'Previous page', 'nolasharon' ) . '</span>',
							'next_text' => nolasharon_icon( 'next', 16 ) . '<span class="screen-reader-text">' . esc_html__( 'Next page', 'nolasharon' ) . '</span>',
							'type'      => 'array',
							'end_size'  => 1,
							'mid_size'  => 1,
							'before_page_number' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'nolasharon' ) . ' </span>',
						)
					);
					?>
					<?php if ( $nolasharon_pages ) : ?>
						<nav class="shop-pager" aria-label="<?php esc_attr_e( 'Pagination', 'nolasharon' ); ?>">
							<?php echo wp_kses_post( implode( '', $nolasharon_pages ) ); ?>
						</nav>
					<?php endif; ?>
				<?php else : ?>
					<div class="shop-empty">
						<span class="shop-empty__icon" aria-hidden="true"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M8.5 11h5"/></svg></span>
						<?php if ( $nolasharon_active || $nolasharon_cur['product_cat'] ) : ?>
							<h2 class="shop-empty__title"><?php esc_html_e( 'No products match these filters', 'nolasharon' ); ?></h2>
							<p><?php esc_html_e( 'Try removing a filter or browse the full collection.', 'nolasharon' ); ?></p>
							<a class="ns-btn" href="<?php echo esc_url( nolasharon_filter_base_url() ); ?>" data-shop-link><?php esc_html_e( 'Clear filters', 'nolasharon' ); ?></a>
						<?php else : ?>
							<h2 class="shop-empty__title"><?php esc_html_e( 'Nothing here yet', 'nolasharon' ); ?></h2>
							<p><?php esc_html_e( 'New pieces are on the way. Browse the full collection in the meantime.', 'nolasharon' ); ?></p>
							<a class="ns-btn" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

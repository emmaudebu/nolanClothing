<?php
/**
 * Product listing: shop (all products), categories, tags and product search (brief §5.1).
 *
 * Filters use WooCommerce's own query vars (filter_size, filter_colour, min_price, max_price,
 * orderby) plus ns_instock, so filtered pages are plain, shareable URLs.
 * "Load More" is a link to the next page, enhanced by theme.js to append in place.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$nolasharon_next_url = $wp_query->max_num_pages > max( 1, get_query_var( 'paged' ) )
	? next_posts( $wp_query->max_num_pages, false )
	: '';
$nolasharon_term     = is_product_taxonomy() ? get_queried_object() : null;
$nolasharon_base     = nolasharon_filter_base_url();
$nolasharon_active   = nolasharon_active_filters();
$nolasharon_orderby  = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : get_option( 'woocommerce_default_catalog_orderby', 'date' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<header class="archive-banner">
	<div class="container">
		<h1 class="archive-banner__title"><?php woocommerce_page_title(); ?></h1>
	</div>
</header>

<div class="container archive-products">
	<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a></li>
			<?php if ( is_shop() && ! is_search() ) : ?>
				<li aria-current="page"><?php esc_html_e( 'Shop', 'nolasharon' ); ?></li>
			<?php else : ?>
				<li><a href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop', 'nolasharon' ); ?></a></li>
				<?php if ( $nolasharon_term instanceof WP_Term ) : ?>
					<?php foreach ( array_reverse( get_ancestors( $nolasharon_term->term_id, $nolasharon_term->taxonomy ) ) as $nolasharon_ancestor ) : ?>
						<?php $nolasharon_parent = get_term( $nolasharon_ancestor, $nolasharon_term->taxonomy ); ?>
						<li><a href="<?php echo esc_url( get_term_link( $nolasharon_parent ) ); ?>"><?php echo esc_html( $nolasharon_parent->name ); ?></a></li>
					<?php endforeach; ?>
					<li aria-current="page"><?php echo esc_html( $nolasharon_term->name ); ?></li>
				<?php elseif ( is_search() ) : ?>
					<li aria-current="page"><?php echo esc_html( sprintf( /* translators: %s: search terms. */ __( 'Search: %s', 'nolasharon' ), get_search_query() ) ); ?></li>
				<?php endif; ?>
			<?php endif; ?>
		</ol>
	</nav>

	<?php if ( $nolasharon_term instanceof WP_Term && '' !== trim( $nolasharon_term->description ) ) : ?>
		<div class="archive-intro entry-content"><?php echo wp_kses_post( wpautop( $nolasharon_term->description ) ); ?></div>
	<?php endif; ?>

	<?php woocommerce_output_all_notices(); ?>

	<div class="shop-toolbar">
		<button class="shop-toolbar__filter" type="button" data-filters-open aria-controls="shop-filters" aria-expanded="false">
			<?php echo nolasharon_icon( 'filter', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'Filter', 'nolasharon' ); ?>
			<?php if ( $nolasharon_active ) : ?>
				<span class="shop-toolbar__count"><?php echo esc_html( count( $nolasharon_active ) ); ?></span>
			<?php endif; ?>
		</button>
		<p class="shop-toolbar__results" aria-live="polite">
			<?php echo esc_html( sprintf( /* translators: %d: number of products. */ _n( '%d product', '%d products', (int) $wp_query->found_posts, 'nolasharon' ), (int) $wp_query->found_posts ) ); ?>
		</p>
		<form class="shop-toolbar__sort" method="get" action="<?php echo esc_url( $nolasharon_base ); ?>">
			<label for="shop-orderby"><?php esc_html_e( 'Sort by', 'nolasharon' ); ?></label>
			<select id="shop-orderby" name="orderby" data-autosubmit>
				<?php foreach ( nolasharon_sort_options() as $nolasharon_value => $nolasharon_label ) : ?>
					<option value="<?php echo esc_attr( $nolasharon_value ); ?>" <?php selected( $nolasharon_orderby, $nolasharon_value ); ?>><?php echo esc_html( $nolasharon_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php nolasharon_filter_hidden_inputs( array( 'orderby' ) ); ?>
			<noscript><button type="submit"><?php esc_html_e( 'Sort', 'nolasharon' ); ?></button></noscript>
		</form>
	</div>

	<?php if ( $nolasharon_active ) : ?>
		<ul class="filter-chips" aria-label="<?php esc_attr_e( 'Active filters', 'nolasharon' ); ?>">
			<?php foreach ( $nolasharon_active as $nolasharon_chip ) : ?>
				<li><a href="<?php echo esc_url( $nolasharon_chip['remove'] ); ?>"><?php echo esc_html( $nolasharon_chip['label'] ); ?><span class="screen-reader-text"><?php esc_html_e( '(remove filter)', 'nolasharon' ); ?></span><?php echo nolasharon_icon( 'close', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
			<?php endforeach; ?>
			<li><a class="filter-chips__clear" href="<?php echo esc_url( $nolasharon_base ); ?>"><?php esc_html_e( 'Clear all', 'nolasharon' ); ?></a></li>
		</ul>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="product-grid product-grid--archive" id="product-grid">
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

		<?php if ( $nolasharon_next_url ) : ?>
			<div class="load-more-wrap">
				<a class="load-more" href="<?php echo esc_url( $nolasharon_next_url ); ?>" data-grid="product-grid">
					<span class="load-more__label"><?php esc_html_e( 'Load More', 'nolasharon' ); ?></span>
					<?php echo nolasharon_icon( 'cloud-down', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<div class="archive-products__empty">
			<p class="empty-note"><?php echo $nolasharon_active ? esc_html__( 'No products match these filters.', 'nolasharon' ) : esc_html__( 'No products found here yet. Check back soon.', 'nolasharon' ); ?></p>
			<a class="btn btn--dark" href="<?php echo esc_url( $nolasharon_active ? $nolasharon_base : nolasharon_shop_url() ); ?>"><?php echo $nolasharon_active ? esc_html__( 'Clear filters', 'nolasharon' ) : esc_html__( 'Shop Now', 'nolasharon' ); ?></a>
		</div>
	<?php endif; ?>
</div>

<?php get_template_part( 'template-parts/shop-filters', null, array( 'base' => $nolasharon_base ) ); ?>

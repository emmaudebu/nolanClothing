<?php
/**
 * Product listing: shop (all products), categories, tags and product search.
 * "Load More" is a link to the next page, enhanced by theme.js to append in place.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$nolasharon_next_url = $wp_query->max_num_pages > max( 1, get_query_var( 'paged' ) )
	? next_posts( $wp_query->max_num_pages, false )
	: '';
?>
<header class="archive-banner">
	<div class="container">
		<h1 class="archive-banner__title"><?php woocommerce_page_title(); ?></h1>
	</div>
</header>

<div class="container archive-products">
	<?php woocommerce_output_all_notices(); ?>

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
		<p class="empty-note archive-products__empty"><?php esc_html_e( 'No products found here yet. Check back soon.', 'nolasharon' ); ?></p>
	<?php endif; ?>
</div>

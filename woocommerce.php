<?php
/**
 * Wrapper for WooCommerce pages. Listings and single products use the theme's
 * own layouts; anything else falls back to WooCommerce's default output.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_shop() || is_product_taxonomy() ) {
	get_template_part( 'template-parts/archive-product' );
} elseif ( is_product() ) {
	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/single-product' );
	}
} else {
	?>
	<div class="container content-area content-area--shop">
		<?php woocommerce_content(); ?>
	</div>
	<?php
}

get_footer();

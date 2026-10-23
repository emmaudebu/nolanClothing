<?php
/**
 * Wrapper for WooCommerce pages. Shop, category and tag archives use the
 * theme's own listing layout; everything else (single product) uses WooCommerce's.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_shop() || is_product_taxonomy() ) {
	get_template_part( 'template-parts/archive-product' );
} else {
	?>
	<div class="container content-area content-area--shop">
		<?php woocommerce_content(); ?>
	</div>
	<?php
}

get_footer();

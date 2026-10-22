<?php
/**
 * Wrapper for WooCommerce shop, category and product pages.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container content-area content-area--shop">
	<?php woocommerce_content(); ?>
</div>
<?php
get_footer();

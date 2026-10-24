<?php
/**
 * Not found (brief §5.11): friendly message, product search, and ways back.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container content-area not-found">
	<h1 class="page-title"><?php esc_html_e( 'Sorry, we can’t find that page', 'nolasharon' ); ?></h1>
	<p><?php esc_html_e( 'It may have moved or no longer exists. Try a search, or head back to the shop.', 'nolasharon' ); ?></p>
	<form class="not-found__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="nf-search"><?php esc_html_e( 'Search products', 'nolasharon' ); ?></label>
		<input id="nf-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products...', 'nolasharon' ); ?>">
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>
		<button class="btn btn--dark btn--sm" type="submit"><?php esc_html_e( 'Search', 'nolasharon' ); ?></button>
	</form>
	<p class="not-found__actions">
		<a class="btn btn--dark" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
		<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
	</p>
</div>
<?php
get_footer();

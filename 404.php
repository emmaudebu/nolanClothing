<?php
/**
 * Not found.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container content-area">
	<h1 class="page-title"><?php esc_html_e( 'Page not found', 'nolasharon' ); ?></h1>
	<p><?php esc_html_e( 'The page you are looking for has moved or no longer exists.', 'nolasharon' ); ?></p>
	<p><a class="btn btn--dark" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop now', 'nolasharon' ); ?></a></p>
</div>
<?php
get_footer();

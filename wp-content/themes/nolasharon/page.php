<?php
/**
 * Default page template.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Checkout, the thank-you page, the cart and My Account print their own heading and layout.
$nolasharon_own_title = function_exists( 'is_checkout' ) && is_checkout() && ! is_checkout_pay_page();
$nolasharon_layout    = '';
if ( $nolasharon_own_title ) {
	$nolasharon_layout = ' content-area--checkout';
} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
	$nolasharon_own_title = true;
	$nolasharon_layout    = nolasharon_is_auth_page() ? ' content-area--auth' : ' content-area--account';
} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
	$nolasharon_own_title = true;
	$nolasharon_layout    = ' content-area--cart';
}
?>
<div class="container content-area<?php echo esc_attr( $nolasharon_layout ); ?>">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'entry' ); ?>>
			<?php if ( ! $nolasharon_own_title ) : ?>
				<h1 class="page-title"><?php the_title(); ?></h1>
			<?php endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</div>
<?php
get_footer();

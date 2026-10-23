<?php
/**
 * Default page template.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Checkout and the thank-you page print their own heading.
$nolasharon_own_title = function_exists( 'is_checkout' ) && is_checkout() && ! is_checkout_pay_page();
?>
<div class="container content-area<?php echo $nolasharon_own_title ? ' content-area--checkout' : ''; ?>">
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

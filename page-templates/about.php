<?php
/**
 * Template Name: About
 *
 * About Us (CONTENT-PAGES.md §1): the page content, built from the NolaSharon block patterns
 * in the block editor, with the stores section from the Branch posts placed before the
 * closing call to action (inc/patterns.php).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ab">
		<div class="ab-crumbs">
			<nav class="container ch-crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php the_title(); ?></span>
			</nav>
		</div>
		<div class="ab-content">
			<?php the_content(); ?>
			<?php echo nolasharon_about_stores(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template part output; empty if already shown. ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();

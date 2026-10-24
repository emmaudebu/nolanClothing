<?php
/**
 * Fallback template for posts, archives and search results.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="container content-area">
	<?php if ( is_home() || is_archive() || is_search() ) : ?>
		<h1 class="page-title"><?php echo is_search() ? esc_html( sprintf( /* translators: %s: search query. */ __( 'Results for "%s"', 'nolasharon' ), get_search_query() ) ) : wp_kses_post( get_the_archive_title() ); ?></h1>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'entry' ); ?>>
				<?php if ( is_singular() ) : ?>
					<h1 class="page-title"><?php the_title(); ?></h1>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php else : ?>
					<h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="entry-summary"><?php the_excerpt(); ?></div>
				<?php endif; ?>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'nolasharon' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();

<?php
/**
 * Store Locations page (slug "store-locations"): one card per branch from the Branches admin.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nolasharon_branches = nolasharon_get_branches();
?>
<header class="archive-banner">
	<div class="container">
		<h1 class="archive-banner__title"><?php the_title(); ?></h1>
	</div>
</header>

<div class="container content-area locations">
	<!-- DRAFT: client to approve -->
	<p class="locations__intro"><?php esc_html_e( 'Visit us at any of our Lagos stores. Our Ikeja locations remain open, and Ogudu is our newest branch.', 'nolasharon' ); ?></p>

	<?php
	while ( have_posts() ) :
		the_post();
		if ( '' !== trim( get_the_content() ) ) :
			?>
			<div class="entry-content locations__content"><?php the_content(); ?></div>
			<?php
		endif;
	endwhile;
	?>

	<?php if ( $nolasharon_branches ) : ?>
		<div class="branch-grid">
			<?php
			foreach ( $nolasharon_branches as $nolasharon_branch ) {
				get_template_part( 'template-parts/branch-card', null, array( 'branch' => $nolasharon_branch ) );
			}
			?>
		</div>
	<?php else : ?>
		<p class="empty-note"><?php esc_html_e( 'Store details are coming soon.', 'nolasharon' ); ?></p>
	<?php endif; ?>

	<?php if ( current_user_can( 'edit_pages' ) ) : ?>
		<p class="admin-note"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ns_branch' ) ); ?>"><?php esc_html_e( 'Edit branches in wp-admin → Branches (only editors see this).', 'nolasharon' ); ?></a></p>
	<?php endif; ?>
</div>
<?php
nolasharon_branch_schema( $nolasharon_branches );
get_footer();

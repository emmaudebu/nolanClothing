<?php
/**
 * Template Name: Store Locations
 *
 * Store Locations (LOCATIONS.md §1): hero, new-branch spotlight, the store finder (area
 * tabs, branch cards and map) and ways to shop without visiting. Branch data comes from
 * wp-admin → Branches. Without JavaScript every card shows and every link works.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nolasharon_branches  = ns_get_branches( array( 'include_drafts' => true ) );
$nolasharon_spotlight = ns_branch_spotlight( $nolasharon_branches );
$nolasharon_has_map   = (bool) array_filter( wp_list_pluck( $nolasharon_branches, 'has_coords' ) );
$nolasharon_areas     = array();
foreach ( $nolasharon_branches as $nolasharon_b ) {
	if ( $nolasharon_b['area'] && ! in_array( $nolasharon_b['area'], $nolasharon_areas, true ) ) {
		$nolasharon_areas[] = $nolasharon_b['area'];
	}
}
$nolasharon_whatsapp = nolasharon_whatsapp_url();
?>
<div class="loc">
	<section class="loc-hero">
		<span class="loc-hero__mark" aria-hidden="true"><?php esc_html_e( 'Lagos', 'nolasharon' ); ?></span>
		<div class="container loc-hero__inner">
			<div class="loc-hero__copy">
				<nav class="loc-hero__crumbs loc-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>
				<p class="loc-hero__eyebrow loc-in loc-in--1"><?php esc_html_e( 'Visit our stores', 'nolasharon' ); ?></p>
				<h1 class="loc-hero__title loc-in loc-in--1"><?php echo esc_html( apply_filters( 'nolasharon_locations_heading', __( 'Find a NolaSharon store near you', 'nolasharon' ) ) ); ?></h1>
				<div class="loc-hero__intro loc-in loc-in--2">
					<?php
					while ( have_posts() ) :
						the_post();
						if ( '' !== trim( get_the_content() ) ) :
							?>
							<div class="entry-content"><?php the_content(); ?></div>
						<?php else : ?>
							<?php // DRAFT: client to approve. Replace by adding the approved intro as the page content. ?>
							<p><?php esc_html_e( 'Visit us at any of our Lagos stores. Our Ikeja locations remain open, and Ogudu is our newest branch.', 'nolasharon' ); ?></p>
							<?php
						endif;
					endwhile;
					?>
				</div>
			</div>
			<div class="loc-hero__actions loc-in loc-in--3">
				<a class="loc-btn loc-btn--dark" href="#branches"><?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'See all locations', 'nolasharon' ); ?></a>
				<a class="loc-btn loc-btn--ghost" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Online', 'nolasharon' ); ?></a>
			</div>
		</div>
	</section>

	<?php
	if ( $nolasharon_spotlight ) {
		get_template_part(
			'template-parts/branch-spotlight',
			null,
			array(
				'branch'  => $nolasharon_spotlight,
				'context' => 'locations',
				'map'     => $nolasharon_has_map,
			)
		);
	}
	?>

	<section class="container loc-finder" id="branches" aria-labelledby="loc-stores-title" data-loc-finder>
		<div class="loc-finder__head">
			<div>
				<h2 class="loc-finder__title" id="loc-stores-title"><?php esc_html_e( 'Our stores', 'nolasharon' ); ?></h2>
				<?php if ( $nolasharon_has_map ) : ?>
					<p class="loc-finder__help" data-map-help hidden><?php esc_html_e( 'Select a store to see it on the map.', 'nolasharon' ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( count( $nolasharon_areas ) > 1 ) : ?>
				<div class="loc-tabs" role="group" aria-label="<?php esc_attr_e( 'Filter stores by area', 'nolasharon' ); ?>" data-loc-tabs hidden style="--tabs: <?php echo esc_attr( count( $nolasharon_areas ) + 1 ); ?>">
					<span class="loc-tabs__thumb" aria-hidden="true"></span>
					<button type="button" class="loc-tabs__btn" data-area="" aria-pressed="true"><?php esc_html_e( 'All', 'nolasharon' ); ?></button>
					<?php foreach ( $nolasharon_areas as $nolasharon_area ) : ?>
						<button type="button" class="loc-tabs__btn" data-area="<?php echo esc_attr( sanitize_title( $nolasharon_area ) ); ?>" aria-pressed="false"><?php echo esc_html( $nolasharon_area ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $nolasharon_branches ) : ?>
			<div class="loc-finder__grid<?php echo $nolasharon_has_map ? ' has-map' : ''; ?>">
				<div class="loc-finder__list">
					<?php foreach ( $nolasharon_branches as $nolasharon_b ) : ?>
						<div class="loc-item" data-area="<?php echo esc_attr( sanitize_title( $nolasharon_b['area'] ) ); ?>">
							<div class="loc-item__inner">
								<?php
								get_template_part(
									'template-parts/branch-card',
									null,
									array(
										'branch' => $nolasharon_b,
										'map'    => $nolasharon_has_map,
									)
								);
								?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php
				if ( $nolasharon_has_map ) {
					get_template_part( 'template-parts/branch-map', null, array( 'branches' => $nolasharon_branches ) );
				}
				?>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'Store details are coming soon.', 'nolasharon' ); ?></p>
		<?php endif; ?>

		<?php if ( current_user_can( 'edit_pages' ) ) : ?>
			<p class="admin-note"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ns_branch' ) ); ?>"><?php esc_html_e( 'Edit branches in wp-admin → Branches (only editors see this).', 'nolasharon' ); ?></a></p>
		<?php endif; ?>
	</section>

	<section class="loc-cta" aria-labelledby="loc-cta-title">
		<div class="container loc-cta__inner">
			<div class="loc-cta__head">
				<h2 class="loc-cta__title" id="loc-cta-title"><?php esc_html_e( 'Can’t make it to a store?', 'nolasharon' ); ?></h2>
				<p><?php esc_html_e( 'Shop the full collection online, or get in touch and we’ll help.', 'nolasharon' ); ?></p>
			</div>
			<a class="loc-cta__card" href="<?php echo esc_url( nolasharon_shop_url() ); ?>">
				<?php echo nolasharon_icon( 'cart', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="loc-cta__name"><?php esc_html_e( 'Shop online', 'nolasharon' ); ?></span>
				<span class="loc-cta__text"><?php esc_html_e( 'Browse new arrivals and order online.', 'nolasharon' ); ?></span>
				<span class="loc-cta__link"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?> <span aria-hidden="true">→</span></span>
			</a>
			<a class="loc-cta__card" href="<?php echo esc_url( $nolasharon_whatsapp ? $nolasharon_whatsapp : nolasharon_page_url( 'contact' ) ); ?>"<?php echo $nolasharon_whatsapp ? ' target="_blank" rel="noopener"' : ''; ?>>
				<?php echo nolasharon_icon( $nolasharon_whatsapp ? 'whatsapp' : 'chat', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="loc-cta__name"><?php esc_html_e( 'Chat with us', 'nolasharon' ); ?></span>
				<span class="loc-cta__text"><?php esc_html_e( 'Ask about sizes, stock at a branch, or your order.', 'nolasharon' ); ?></span>
				<span class="loc-cta__link"><?php echo esc_html( $nolasharon_whatsapp ? __( 'Chat on WhatsApp', 'nolasharon' ) : __( 'Contact Us', 'nolasharon' ) ); ?> <span aria-hidden="true">→</span></span>
			</a>
			<a class="loc-cta__card" href="<?php echo esc_url( nolasharon_page_url( 'faqs' ) ); ?>">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6V14M12 17h.01"/></svg>
				<span class="loc-cta__name"><?php esc_html_e( 'Questions?', 'nolasharon' ); ?></span>
				<span class="loc-cta__text"><?php esc_html_e( 'Read our FAQs on delivery, pickup and exchanges.', 'nolasharon' ); ?></span>
				<span class="loc-cta__link"><?php esc_html_e( 'Read FAQs', 'nolasharon' ); ?> <span aria-hidden="true">→</span></span>
			</a>
		</div>
	</section>
</div>
<?php
ns_branch_schema( $nolasharon_branches );
get_footer();

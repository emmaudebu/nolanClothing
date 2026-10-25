<?php
/**
 * Template Name: Collections
 *
 * Our Collections: a hero with quick links, then one editorial row per collection
 * (alternating sides) and a closing call to action. Collections come from the product
 * categories (inc/collections.php); nothing on the page needs editing.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_cats = function_exists( 'nolasharon_nav_categories' ) ? nolasharon_nav_categories() : array();
$nolasharon_rows = array();
foreach ( $nolasharon_cats as $nolasharon_i => $nolasharon_term ) {
	$nolasharon_rows[] = nolasharon_collection_data( $nolasharon_term, $nolasharon_i );
}
$nolasharon_tilts = array( -4, 3, -3, 4 );

get_header();
?>
<div class="cl">
	<section class="ch-hero cl-hero">
		<span class="ch-mark cl-hero__mark" aria-hidden="true"><?php esc_html_e( 'Collections', 'nolasharon' ); ?></span>
		<div class="container cl-hero__inner">
			<nav class="ch-crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php esc_html_e( 'Collections', 'nolasharon' ); ?></span>
			</nav>
			<span class="ch-eyebrow ns-in ns-in--1"><?php esc_html_e( 'Our collections', 'nolasharon' ); ?></span>
			<h1 class="cl-hero__title ns-in ns-in--1"><?php esc_html_e( 'Explore our collections', 'nolasharon' ); ?></h1>
			<?php // DRAFT: client to approve. ?>
			<p class="cl-hero__intro ns-in ns-in--2"><?php esc_html_e( 'Browse by collection to find pieces for every day and every occasion.', 'nolasharon' ); ?></p>
			<?php if ( count( $nolasharon_rows ) > 1 ) : ?>
				<nav class="cl-jump ns-in ns-in--3" aria-label="<?php esc_attr_e( 'Jump to a collection', 'nolasharon' ); ?>">
					<?php foreach ( $nolasharon_rows as $nolasharon_row ) : ?>
						<a class="cl-jump__chip" href="#collection-<?php echo esc_attr( $nolasharon_row['term']->slug ); ?>">
							<?php echo esc_html( $nolasharon_row['term']->name ); ?>
							<span class="cl-jump__n"><?php echo esc_html( $nolasharon_row['count'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
	</section>

	<div class="container cl-list">
		<?php if ( ! $nolasharon_rows ) : ?>
			<div class="cl-empty">
				<p class="cl-empty__title"><?php esc_html_e( 'Our collections are on the way', 'nolasharon' ); ?></p>
				<p><?php esc_html_e( 'Check back soon, or browse everything in the shop.', 'nolasharon' ); ?></p>
				<a class="ns-btn" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?></a>
			</div>
		<?php endif; ?>

		<?php foreach ( $nolasharon_rows as $nolasharon_i => $nolasharon_row ) : ?>
			<?php
			$nolasharon_term = $nolasharon_row['term'];
			$nolasharon_id   = 'collection-' . $nolasharon_term->slug;
			?>
			<section class="cl-row cl-row--<?php echo esc_attr( $nolasharon_row['tone'] ); ?><?php echo $nolasharon_i % 2 ? ' cl-row--flip' : ''; ?>" id="<?php echo esc_attr( $nolasharon_id ); ?>" aria-labelledby="<?php echo esc_attr( $nolasharon_id ); ?>-title" style="--tilt: <?php echo esc_attr( $nolasharon_tilts[ $nolasharon_i % 4 ] ); ?>deg">
				<a class="cl-row__media" href="<?php echo esc_url( $nolasharon_row['url'] ); ?>" tabindex="-1" aria-hidden="true">
					<span class="cl-row__ring"></span>
					<span class="cl-row__index"><?php echo esc_html( sprintf( '%02d', $nolasharon_i + 1 ) ); ?></span>
					<?php if ( $nolasharon_row['photo'] ) : ?>
						<span class="cl-row__photo">
							<?php echo wp_get_attachment_image( $nolasharon_row['photo'], 'woocommerce_single', false, array( 'alt' => '', 'loading' => $nolasharon_i ? 'lazy' : 'eager', 'sizes' => '(min-width: 900px) 360px, 60vw' ) ); ?>
						</span>
					<?php endif; ?>
					<?php if ( $nolasharon_row['photo_2'] ) : ?>
						<span class="cl-row__photo cl-row__photo--small">
							<?php echo wp_get_attachment_image( $nolasharon_row['photo_2'], 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy', 'sizes' => '(min-width: 900px) 180px, 30vw' ) ); ?>
						</span>
					<?php endif; ?>
				</a>
				<div class="cl-row__body">
					<p class="cl-row__eyebrow"><?php echo esc_html( sprintf( /* translators: %s: number, e.g. 01. */ __( '%s — Collection', 'nolasharon' ), sprintf( '%02d', $nolasharon_i + 1 ) ) ); ?></p>
					<h2 class="cl-row__title" id="<?php echo esc_attr( $nolasharon_id ); ?>-title"><?php echo esc_html( $nolasharon_term->name ); ?></h2>
					<?php if ( $nolasharon_row['summary'] ) : ?>
						<p class="cl-row__text"><?php echo esc_html( $nolasharon_row['summary'] ); ?></p>
					<?php endif; ?>
					<p class="cl-row__count"><?php echo esc_html( sprintf( /* translators: %d: number of products. */ _n( '%d piece', '%d pieces', $nolasharon_row['count'], 'nolasharon' ), $nolasharon_row['count'] ) ); ?></p>
					<?php if ( $nolasharon_row['children'] ) : ?>
						<ul class="cl-row__subs" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: collection name. */ __( 'Inside %s', 'nolasharon' ), $nolasharon_term->name ) ); ?>">
							<?php foreach ( $nolasharon_row['children'] as $nolasharon_child ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $nolasharon_child ) ); ?>"><?php echo esc_html( $nolasharon_child->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<a class="cl-row__btn" href="<?php echo esc_url( $nolasharon_row['url'] ); ?>">
						<?php echo esc_html( sprintf( /* translators: %s: collection name. */ __( 'Shop %s', 'nolasharon' ), $nolasharon_term->name ) ); ?>
						<?php echo nolasharon_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</section>
		<?php endforeach; ?>
	</div>

	<?php if ( $nolasharon_rows ) : ?>
		<section class="container cl-end" aria-labelledby="cl-end-title">
			<div class="cl-end__inner">
				<div>
					<h2 class="cl-end__title" id="cl-end-title"><?php esc_html_e( 'Not sure where to start?', 'nolasharon' ); ?></h2>
					<p><?php esc_html_e( 'See what has just arrived, or browse everything in one place.', 'nolasharon' ); ?></p>
				</div>
				<div class="cl-end__actions">
					<a class="cl-end__btn" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', nolasharon_shop_url() ) ); ?>"><?php esc_html_e( 'View New Arrivals', 'nolasharon' ); ?></a>
					<a class="cl-end__btn cl-end__btn--ghost" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop all products', 'nolasharon' ); ?></a>
				</div>
			</div>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();

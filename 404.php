<?php
/**
 * Not found (brief §5.11, CONTENT-PAGES.md §5): "4 [photo] 4" with the middle "0" swinging
 * like a garment on a hanger, a product search, ways back and quick links. The photo comes
 * from the Customizer, else the newest product. Kept out of search results.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_img = (int) get_theme_mod( 'nolasharon_404_image' );
$nolasharon_src = $nolasharon_img ? wp_get_attachment_image_url( $nolasharon_img, 'medium_large' ) : '';
if ( ! $nolasharon_src && function_exists( 'wc_get_products' ) ) {
	$nolasharon_latest = wc_get_products(
		array(
			'limit'   => 5,
			'status'  => 'publish',
			'orderby' => 'date',
			'order'   => 'DESC',
		)
	);
	foreach ( $nolasharon_latest as $nolasharon_p ) {
		if ( $nolasharon_p->get_image_id() ) {
			$nolasharon_src = wp_get_attachment_image_url( $nolasharon_p->get_image_id(), 'medium_large' );
			break;
		}
	}
}
if ( ! $nolasharon_src ) {
	$nolasharon_src = get_template_directory_uri() . '/assets/img/wrap-dress.svg';
}

$nolasharon_links = array(
	array( __( 'Shop', 'nolasharon' ), __( 'Browse the collection', 'nolasharon' ), nolasharon_shop_url(), 'cart' ),
	array( __( 'Track order', 'nolasharon' ), __( 'See where your order is', 'nolasharon' ), nolasharon_track_order_url(), 'truck' ),
	array( __( 'Store locations', 'nolasharon' ), __( 'Visit us in Lagos', 'nolasharon' ), nolasharon_page_url( 'store-locations' ), 'pin' ),
	array( __( 'Contact us', 'nolasharon' ), __( 'We’re happy to help', 'nolasharon' ), nolasharon_page_url( 'contact' ), 'chat' ),
);

get_header();
?>
<section class="nf">
	<span class="nf__blob nf__blob--1" aria-hidden="true"></span>
	<span class="nf__blob nf__blob--2" aria-hidden="true"></span>
	<div class="container nf__inner">
		<p class="nf__code" aria-hidden="true">
			<span class="nf__digit">4</span>
			<span class="nf__zero"><span class="nf__hook"></span><span class="nf__frame"><img src="<?php echo esc_url( $nolasharon_src ); ?>" alt="" width="200" height="250"></span></span>
			<span class="nf__digit">4</span>
		</p>
		<h1 class="nf__title ns-in ns-in--1"><?php esc_html_e( 'This page has wandered off', 'nolasharon' ); ?></h1>
		<p class="nf__text ns-in ns-in--2"><?php esc_html_e( 'The page you’re looking for may have moved or no longer exists. Try a search, or head back to the shop.', 'nolasharon' ); ?></p>
		<form class="nf-search ns-in ns-in--2" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="nf-search"><?php esc_html_e( 'Search products', 'nolasharon' ); ?></label>
			<svg class="nf-search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
			<input id="nf-search" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products', 'nolasharon' ); ?>">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<input type="hidden" name="post_type" value="product">
			<?php endif; ?>
			<button type="submit"><?php esc_html_e( 'Search', 'nolasharon' ); ?></button>
		</form>
		<div class="nf__actions ns-in ns-in--3">
			<a class="ns-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to homepage', 'nolasharon' ); ?></a>
			<a class="ns-btn ns-btn--ghost" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', nolasharon_shop_url() ) ); ?>"><?php esc_html_e( 'View New Arrivals', 'nolasharon' ); ?></a>
		</div>
		<ul class="nf__links ns-in ns-in--3">
			<?php foreach ( $nolasharon_links as $nolasharon_l ) : ?>
				<li>
					<a href="<?php echo esc_url( $nolasharon_l[2] ); ?>">
						<span class="nf__link-icon" aria-hidden="true"><?php echo nolasharon_icon( $nolasharon_l[3], 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="nf__link-title"><?php echo esc_html( $nolasharon_l[0] ); ?></span>
						<span class="nf__link-text"><?php echo esc_html( $nolasharon_l[1] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();

<?php
/**
 * Main navigation (brief §3). Uses the "Primary" menu when one is assigned in
 * Appearance → Menus; otherwise Home · Shop · Categories ▾ · About Us · Store Locations · Contact Us.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( has_nav_menu( 'primary' ) ) {
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'main-nav__list',
			'depth'          => 2,
		)
	);
	return;
}

$nolasharon_categories = nolasharon_nav_categories();
$nolasharon_item       = function ( $label, $url, $current = false ) {
	printf(
		'<li%3$s><a href="%1$s"%4$s>%2$s</a></li>',
		esc_url( $url ),
		esc_html( $label ),
		$current ? ' class="current-menu-item"' : '',
		$current ? ' aria-current="page"' : ''
	);
};
?>
<ul class="main-nav__list">
	<?php
	$nolasharon_item( __( 'Home', 'nolasharon' ), home_url( '/' ), is_front_page() );
	$nolasharon_item( __( 'Shop', 'nolasharon' ), nolasharon_shop_url(), function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() || is_product() ) );
	?>
	<?php if ( $nolasharon_categories ) : ?>
		<li class="has-sub">
			<button class="main-nav__sub-toggle" type="button" aria-expanded="false" aria-controls="nav-categories">
				<?php esc_html_e( 'Categories', 'nolasharon' ); ?>
				<?php echo nolasharon_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<ul class="sub-menu" id="nav-categories">
				<?php
				foreach ( $nolasharon_categories as $nolasharon_cat ) {
					$nolasharon_item( $nolasharon_cat->name, get_term_link( $nolasharon_cat ), is_tax( 'product_cat', $nolasharon_cat->term_id ) );
				}
				?>
			</ul>
		</li>
	<?php endif; ?>
	<?php
	$nolasharon_item( __( 'About Us', 'nolasharon' ), nolasharon_page_url( 'about-us' ), is_page( 'about-us' ) );
	$nolasharon_item( __( 'Store Locations', 'nolasharon' ), nolasharon_page_url( 'store-locations' ), is_page( 'store-locations' ) );
	$nolasharon_item( __( 'Contact Us', 'nolasharon' ), nolasharon_page_url( 'contact' ), is_page( 'contact' ) );
	?>
	<?php if ( function_exists( 'nolasharon_wishlist_url' ) ) : ?>
		<li class="main-nav__mobile-only"><a href="<?php echo esc_url( nolasharon_wishlist_url() ); ?>"><?php esc_html_e( 'Wishlist', 'nolasharon' ); ?></a></li>
	<?php endif; ?>
</ul>

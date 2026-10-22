<?php
/**
 * Main navigation. Uses the "Primary" and "Resources" menus when assigned,
 * otherwise falls back to the default links from the approved design.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_menu_args = array(
	'container'   => false,
	'items_wrap'  => '%3$s',
	'echo'        => false,
	'fallback_cb' => false,
	'depth'       => 1,
);

$nolasharon_primary = has_nav_menu( 'primary' )
	? wp_nav_menu( array_merge( $nolasharon_menu_args, array( 'theme_location' => 'primary' ) ) )
	: '';

$nolasharon_resources = has_nav_menu( 'resources' )
	? wp_nav_menu( array_merge( $nolasharon_menu_args, array( 'theme_location' => 'resources' ) ) )
	: '';

if ( ! $nolasharon_primary ) {
	$nolasharon_links = array(
		__( 'Home', 'nolasharon' )        => home_url( '/' ),
		__( 'Collections', 'nolasharon' ) => nolasharon_shop_url(),
		__( 'Women', 'nolasharon' )       => nolasharon_category_url( 'women' ),
		__( 'Men', 'nolasharon' )         => nolasharon_category_url( 'men' ),
		__( 'Our Story', 'nolasharon' )   => nolasharon_page_url( 'our-story' ),
	);
	foreach ( $nolasharon_links as $nolasharon_label => $nolasharon_url ) {
		$nolasharon_primary .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $nolasharon_url ), esc_html( $nolasharon_label ) );
	}
}

if ( ! $nolasharon_resources ) {
	$nolasharon_links = array(
		__( 'FAQs', 'nolasharon' )        => nolasharon_page_url( 'faqs' ),
		__( 'Size Guide', 'nolasharon' )  => nolasharon_page_url( 'size-guide' ),
		__( 'Track Order', 'nolasharon' ) => nolasharon_page_url( 'track-order' ),
		__( 'Contact Us', 'nolasharon' )  => nolasharon_page_url( 'contact' ),
	);
	foreach ( $nolasharon_links as $nolasharon_label => $nolasharon_url ) {
		$nolasharon_resources .= sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $nolasharon_url ), esc_html( $nolasharon_label ) );
	}
}
?>
<ul class="main-nav__list">
	<?php echo $nolasharon_primary; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above or by wp_nav_menu. ?>
	<li class="has-sub">
		<button class="main-nav__sub-toggle" type="button" aria-expanded="false" aria-controls="nav-resources">
			<?php esc_html_e( 'Resources', 'nolasharon' ); ?>
			<?php echo nolasharon_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<ul class="sub-menu" id="nav-resources">
			<?php echo $nolasharon_resources; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above or by wp_nav_menu. ?>
		</ul>
	</li>
</ul>

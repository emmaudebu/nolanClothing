<?php
/**
 * NolaSharon theme setup.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

define( 'NOLASHARON_VERSION', '0.9.0' );

require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/woocommerce.php';
require get_template_directory() . '/inc/product.php';
require get_template_directory() . '/inc/wishlist.php';
require get_template_directory() . '/inc/drawer.php';
require get_template_directory() . '/inc/quick-view.php';
require get_template_directory() . '/inc/shop-filters.php';
require get_template_directory() . '/inc/branches.php';
require get_template_directory() . '/inc/checkout.php';
require get_template_directory() . '/inc/account.php';
require get_template_directory() . '/inc/cart.php';
require get_template_directory() . '/inc/content.php';

/**
 * Theme supports and menus.
 */
function nolasharon_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 360,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 480,
			'single_image_width'    => 900,
		)
	);
	// No wc-product-gallery-* support: the theme has its own gallery and lightbox,
	// so WooCommerce's zoom, slider and PhotoSwipe scripts are not loaded.

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'nolasharon' ),
		)
	);
}
add_action( 'after_setup_theme', 'nolasharon_setup' );

/**
 * Styles and scripts.
 */
function nolasharon_assets() {
	wp_enqueue_style(
		'nolasharon-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'nolasharon', get_stylesheet_uri(), array( 'nolasharon-fonts' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-ui', get_template_directory_uri() . '/assets/js/ui.js', array(), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script(
		'nolasharon-ui',
		'nsUIStrings',
		array(
			'showPassword' => __( 'Show password', 'nolasharon' ),
			'hidePassword' => __( 'Hide password', 'nolasharon' ),
			/* translators: %s: strength, e.g. "Good". */
			'strength'     => __( 'Password strength: %s', 'nolasharon' ),
			'veryWeak'     => __( 'Very weak', 'nolasharon' ),
			'weak'         => __( 'Weak', 'nolasharon' ),
			'good'         => __( 'Good', 'nolasharon' ),
			'strong'       => __( 'Strong', 'nolasharon' ),
			'tooWeak'      => __( 'Add more characters, numbers or symbols.', 'nolasharon' ),
		)
	);
	wp_enqueue_script( 'nolasharon', get_template_directory_uri() . '/assets/js/theme.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'nolasharon_assets' );

/**
 * Preconnect to Google Fonts.
 *
 * @param array  $urls          URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array
 */
function nolasharon_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'nolasharon_resource_hints', 10, 2 );

/**
 * Apply the saved light/dark preference before first paint to avoid a flash, and flag that
 * JavaScript runs (html.ns-js) so enhanced-only controls such as the password eye can show.
 */
function nolasharon_theme_mode_script() {
	echo "<script>document.documentElement.classList.add('ns-js');try{var m=localStorage.getItem('ns-theme');if(m)document.documentElement.setAttribute('data-theme',m);}catch(e){}</script>\n";
}
add_action( 'wp_head', 'nolasharon_theme_mode_script', 1 );

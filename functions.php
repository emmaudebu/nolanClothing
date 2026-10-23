<?php
/**
 * NolaSharon theme setup.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

define( 'NOLASHARON_VERSION', '0.2.0' );

require get_template_directory() . '/inc/helpers.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/woocommerce.php';

/**
 * Theme supports and menus.
 */
function nolasharon_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 480,
			'single_image_width'    => 900,
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'   => __( 'Primary navigation', 'nolasharon' ),
			'resources' => __( 'Resources dropdown', 'nolasharon' ),
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
		'https://fonts.googleapis.com/css2?family=Allura&family=Playfair+Display:wght@400;500;600&family=Poppins:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'nolasharon', get_stylesheet_uri(), array( 'nolasharon-fonts' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon', get_template_directory_uri() . '/assets/js/theme.js', array(), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
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
 * Apply the saved light/dark preference before first paint to avoid a flash.
 */
function nolasharon_theme_mode_script() {
	echo "<script>try{var m=localStorage.getItem('ns-theme');if(m)document.documentElement.setAttribute('data-theme',m);}catch(e){}</script>\n";
}
add_action( 'wp_head', 'nolasharon_theme_mode_script', 1 );

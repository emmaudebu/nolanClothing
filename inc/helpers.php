<?php
/**
 * Template helpers: links, logo, icons and placeholder images.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL of a page by slug, falling back to the home page when it does not exist yet.
 *
 * @param string $slug Page slug.
 * @return string
 */
function nolasharon_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' );
}

/**
 * URL of a product category by slug, falling back to the shop page.
 *
 * @param string $slug Product category slug.
 * @return string
 */
function nolasharon_category_url( $slug ) {
	$term = taxonomy_exists( 'product_cat' ) ? get_term_by( 'slug', $slug, 'product_cat' ) : false;
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return nolasharon_shop_url();
}

/**
 * Shop page URL.
 *
 * @return string
 */
function nolasharon_shop_url() {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}
	return home_url( '/' );
}

/**
 * URL of a bundled placeholder illustration.
 *
 * @param string $name File name without extension, e.g. "wrap-dress".
 * @return string
 */
function nolasharon_img( $name ) {
	return get_template_directory_uri() . '/assets/img/' . sanitize_file_name( $name ) . '.svg';
}

/**
 * Print the text logo ("NolaSharon" over "CLOTHING").
 *
 * @param string $tag Wrapper element: "a" links to home, anything else renders a span.
 */
function nolasharon_logo( $tag = 'a' ) {
	$inner = '<span class="logo__name">NolaSharon</span><span class="logo__sub">Clothing</span>';
	if ( 'a' === $tag ) {
		printf( '<a class="logo" href="%1$s" rel="home" aria-label="%2$s">%3$s</a>', esc_url( home_url( '/' ) ), esc_attr__( 'NolaSharon Clothing, home', 'nolasharon' ), $inner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
	} else {
		echo '<span class="logo">' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup.
	}
}

/**
 * Inline stroke icon.
 *
 * @param string $name Icon name.
 * @param int    $size Width and height in px.
 * @return string SVG markup.
 */
function nolasharon_icon( $name, $size = 20 ) {
	$paths = array(
		'search'     => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
		'cart'       => '<path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
		'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
		'moon'       => '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5z"/>',
		'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'      => '<path d="M6 6l12 12M18 6L6 18"/>',
		'chevron'    => '<path d="M6 9l6 6 6-6"/>',
		'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'truck'      => '<path d="M3 7h11v10H3zM14 10h4l3 3v4h-7"/>',
		'cloud-down' => '<path d="M7 17.5A4.5 4.5 0 0 1 6.6 8.5a6 6 0 0 1 11.6 1.6A3.8 3.8 0 0 1 17.5 17.5"/><path d="M12 11v9M9 17l3 3 3-3"/>',
		'play'     =>'<path d="M8 5v14l11-7z" fill="currentColor" stroke="none"/>',
		'whatsapp'   => '<path d="M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 0 1-1.8-1.8l.8-1-1-2L9 9.5z" fill="currentColor" stroke="none"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf(
		'<svg class="icon icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		absint( $size ),
		$paths[ $name ]
	);
}

/**
 * WhatsApp chat URL from the Customizer number, or the contact page if none is set.
 *
 * @return string
 */
function nolasharon_whatsapp_url() {
	$number = preg_replace( '/\D+/', '', (string) get_theme_mod( 'nolasharon_whatsapp', '' ) );
	return $number ? 'https://wa.me/' . $number : nolasharon_page_url( 'contact' );
}

/**
 * Extract a YouTube video ID from a URL or bare ID.
 *
 * @param string $value URL or ID.
 * @return string Empty when it cannot be parsed.
 */
function nolasharon_youtube_id( $value ) {
	$value = trim( (string) $value );
	if ( preg_match( '~^[A-Za-z0-9_-]{11}$~', $value ) ) {
		return $value;
	}
	if ( preg_match( '~(?:youtu\.be/|v=|embed/|shorts/)([A-Za-z0-9_-]{11})~', $value, $m ) ) {
		return $m[1];
	}
	return '';
}

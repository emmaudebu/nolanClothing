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
 * URL of a bundled logo file (assets/img/logo/).
 *
 * @param string $file File name.
 * @return string
 */
function nolasharon_logo_url( $file ) {
	return get_template_directory_uri() . '/assets/img/logo/' . $file;
}

/**
 * Print the logo. A logo uploaded in Customizer → Site Identity wins; otherwise the bundled
 * NolaSharon logo: the "NS" + wordmark lockup in the header (wordmark only on small screens)
 * and the full crest in the footer.
 *
 * @param string $tag     Wrapper element: "a" links to home, anything else renders a span.
 * @param string $variant "header" or "full" (the crest, for dark backgrounds such as the footer).
 */
function nolasharon_logo( $tag = 'a', $variant = 'header' ) {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$alt     = 'a' === $tag ? '' : __( 'NolaSharon Clothing', 'nolasharon' );
	if ( $logo_id ) {
		$inner = wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'logo__img', 'alt' => $alt ) );
	} elseif ( 'full' === $variant ) {
		$inner = sprintf( '<img class="logo__img logo__img--crest" src="%1$s" alt="%2$s" width="291" height="260" loading="lazy" decoding="async">', esc_url( nolasharon_logo_url( 'logo-full.webp' ) ), esc_attr( $alt ) );
	} else {
		$inner = sprintf(
			'<img class="logo__img logo__img--lockup" src="%1$s" alt="%3$s" width="373" height="73" decoding="async"><img class="logo__img logo__img--word" src="%2$s" alt="" width="290" height="73" decoding="async">',
			esc_url( nolasharon_logo_url( 'logo-lockup.webp' ) ),
			esc_url( nolasharon_logo_url( 'logo-wordmark.webp' ) ),
			esc_attr( $alt )
		);
	}
	if ( 'a' === $tag ) {
		printf( '<a class="logo" href="%1$s" rel="home" aria-label="%2$s">%3$s</a>', esc_url( home_url( '/' ) ), esc_attr__( 'NolaSharon Clothing, home', 'nolasharon' ), $inner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	} else {
		echo '<span class="logo">' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}

/**
 * Browser tab and home-screen icons from the bundled "NS" mark, until a Site Icon is set
 * in Customizer → Site Identity.
 */
function nolasharon_site_icons() {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" sizes="32x32">' . "\n", esc_url( nolasharon_logo_url( 'icon-32.png' ) ) );
	printf( '<link rel="icon" href="%s" sizes="192x192">' . "\n", esc_url( nolasharon_logo_url( 'icon-192.png' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( nolasharon_logo_url( 'icon-180.png' ) ) );
}
add_action( 'wp_head', 'nolasharon_site_icons', 5 );
add_action( 'login_head', 'nolasharon_site_icons' );
add_action( 'admin_head', 'nolasharon_site_icons' );

/**
 * WooCommerce emails: the crest in the email header until an image is set under
 * WooCommerce → Settings → Emails (PNG, as many email apps don't show WebP).
 *
 * @param string $url Header image URL setting.
 * @return string
 */
function nolasharon_email_header_image( $url ) {
	return $url ? $url : nolasharon_logo_url( 'logo-full.png' );
}
add_filter( 'option_woocommerce_email_header_image', 'nolasharon_email_header_image' );

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
		'play'       => '<path d="M8 5v14l11-7z" fill="currentColor" stroke="none"/>',
		'external'   => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
		'heart'      => '<path d="M12 20s-7.5-4.6-9.2-9.3C1.6 7.4 3.7 4 7.1 4c2 0 3.4 1.1 4.9 2.9C13.5 5.1 14.9 4 16.9 4c3.4 0 5.5 3.4 4.3 6.7C19.5 15.4 12 20 12 20z"/>',
		'star'       => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/>',
		'trash'      => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
		'user'       => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="10" r="3"/><path d="M6.2 18.4a6.5 6.5 0 0 1 11.6 0"/>',
		'store'      => '<path d="M4 10v10h16V10M3 6l1.5-3h15L21 6v2a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0z"/><path d="M10 20v-5h4v5"/>',
		'receipt'    => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
		'minus'      => '<path d="M5 12h14"/>',
		'plus'       => '<path d="M12 5v14M5 12h14"/>',
		'prev'       => '<path d="M15 6l-6 6 6 6"/>',
		'next'       => '<path d="M9 6l6 6-6 6"/>',
		'weight'     => '<path d="M5.5 20h13L17 9H7z"/><path d="M9.5 9a2.5 2.5 0 1 1 5 0"/>',
		'pin'        => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'phone'      => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
		'filter'     => '<path d="M4 6h16M7 12h10M10 18h4"/>',
		'card'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
		'chat'       => '<path d="M4 5h16v11H8l-4 4z"/><path d="M8 10h8M8 13h5"/>',
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
 * WhatsApp chat URL, or an empty string when no verified number is set (brief rule 5).
 *
 * @param string $message Prefilled message; defaults to the Customizer message.
 * @return string
 */
function nolasharon_whatsapp_url( $message = null ) {
	$number = preg_replace( '/\D+/', '', (string) nolasharon_setting( 'nolasharon_whatsapp' ) );
	if ( ! $number ) {
		return '';
	}
	$message = null === $message ? (string) nolasharon_setting( 'nolasharon_wa_message' ) : $message;
	return 'https://wa.me/' . $number . ( '' !== $message ? '?text=' . rawurlencode( $message ) : '' );
}

/**
 * A tel: link for a phone number, or an empty string.
 *
 * @param string $phone Phone number as typed.
 * @return string
 */
function nolasharon_tel_url( $phone ) {
	$digits = preg_replace( '/[^\d+]/', '', (string) $phone );
	return $digits ? 'tel:' . $digits : '';
}

/**
 * Whether a value is still a "[[CONFIRM: ...]]" placeholder or empty, so it must not be shown.
 *
 * @param mixed $value Value.
 * @return bool
 */
function nolasharon_unconfirmed( $value ) {
	$value = trim( (string) $value );
	return '' === $value || false !== strpos( $value, '[[CONFIRM' );
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

/**
 * Normalise a Nigerian phone number to +234XXXXXXXXXX, or return '' when it is not one.
 * Accepts 0803 123 4567, 803 123 4567 and +234 803 123 4567.
 *
 * @param string $phone Phone as typed.
 * @return string
 */
function nolasharon_ng_phone( $phone ) {
	$digits = preg_replace( '/\D+/', '', (string) $phone );
	if ( preg_match( '/^(?:234|0)?([789][01]\d{8})$/', $digits, $m ) ) {
		return '+234' . $m[1];
	}
	return '';
}

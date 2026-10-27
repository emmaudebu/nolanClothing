<?php
/**
 * Light security hardening (CLAUDE.md §10): don't reveal usernames to visitors (REST user
 * list, ?author= scans, author archives; generic login errors are in inc/account.php), hide
 * the WordPress version, and add a nosniff header. Logged-in users, the block editor and WooCommerce are unaffected.
 * Server-level protection (firewall, login limiting, backups) still comes from the host and
 * a security plugin.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the public user list from the REST API for visitors. The block editor (logged in)
 * still uses it.
 *
 * @param array $endpoints REST endpoints.
 * @return array
 */
function nolasharon_hide_rest_users( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		foreach ( array_keys( $endpoints ) as $route ) {
			if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
				unset( $endpoints[ $route ] );
			}
		}
	}
	return $endpoints;
}
add_filter( 'rest_endpoints', 'nolasharon_hide_rest_users' );

/**
 * Author archives aren't part of the shop, and "?author=1" scans reveal usernames: send
 * visitors to the homepage instead.
 */
function nolasharon_block_author_scans() {
	if ( is_user_logged_in() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check.
	if ( is_author() || isset( $_GET['author'] ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'nolasharon_block_author_scans', -2 );

/**
 * Remove author links from the users' oEmbed/author data shown publicly.
 *
 * @param array $data oEmbed response data.
 * @return array
 */
function nolasharon_oembed_hide_author( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
}
add_filter( 'oembed_response_data', 'nolasharon_oembed_hide_author' );

/**
 * Hide the WordPress version from the page source and feeds.
 */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Stop browsers guessing file types (protects against some content-sniffing attacks).
 */
function nolasharon_security_headers() {
	if ( ! headers_sent() ) {
		header( 'X-Content-Type-Options: nosniff' );
	}
}
add_action( 'send_headers', 'nolasharon_security_headers' );
add_action( 'login_init', 'nolasharon_security_headers' );

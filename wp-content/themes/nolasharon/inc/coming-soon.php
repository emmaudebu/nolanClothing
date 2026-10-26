<?php
/**
 * Coming soon mode: while switched on (Customizer → NolaSharon Settings → Coming soon page,
 * or the admin bar), visitors get an animated coming soon page with a 503 "temporarily
 * unavailable" status so search engines keep the site's place. Logged-in staff browse the
 * normal site with a reminder in the admin bar, and can preview the page.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether coming soon mode is on.
 *
 * @return bool
 */
function nolasharon_coming_soon_on() {
	return (bool) nolasharon_setting( 'nolasharon_cs_enabled' );
}

/**
 * Whether the current visitor should see the normal site.
 *
 * @return bool
 */
function nolasharon_coming_soon_bypass() {
	return current_user_can( 'edit_posts' );
}

/**
 * Launch time as a Unix timestamp (Lagos/site time), or 0.
 *
 * @return int
 */
function nolasharon_coming_soon_launch() {
	$value = nolasharon_setting( 'nolasharon_cs_date' );
	if ( ! $value ) {
		return 0;
	}
	$date = date_create( $value, wp_timezone() );
	return $date ? $date->getTimestamp() : 0;
}

/**
 * Serve the coming soon page instead of the requested page, for visitors only. Logins, the
 * dashboard, REST/AJAX and WooCommerce endpoints (payment callbacks, cart fragments) are
 * left alone. Staff can preview it with ?coming-soon=preview.
 */
function nolasharon_coming_soon_gate() {
	$preview = isset( $_GET['coming-soon'] ) && 'preview' === $_GET['coming-soon'] && nolasharon_coming_soon_bypass(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preview.
	if ( ! $preview ) {
		if ( ! nolasharon_coming_soon_on() || nolasharon_coming_soon_bypass() ) {
			return;
		}
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || is_robots() || is_customize_preview()
			|| isset( $_GET['wc-ajax'] ) || isset( $_GET['wc-api'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
	}

	if ( ! $preview ) {
		status_header( 503 );
		$launch = nolasharon_coming_soon_launch();
		header( 'Retry-After: ' . ( $launch > time() ? max( 3600, $launch - time() ) : DAY_IN_SECONDS ) );
	}
	nocache_headers();
	get_template_part( 'template-parts/coming-soon' );
	exit;
}
add_action( 'template_redirect', 'nolasharon_coming_soon_gate', -1 );

/**
 * Admin bar: show whether the page is on, with one-click on/off, preview and settings.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function nolasharon_coming_soon_admin_bar( $bar ) {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$on     = nolasharon_coming_soon_on();
	$toggle = wp_nonce_url( admin_url( 'admin-post.php?action=nolasharon_coming_soon&state=' . ( $on ? 'off' : 'on' ) ), 'nolasharon_coming_soon' );
	$bar->add_node(
		array(
			'id'    => 'ns-coming-soon',
			'title' => $on ? '<span class="ns-cs-dot is-on" aria-hidden="true"></span>' . esc_html__( 'Coming soon: ON', 'nolasharon' ) : '<span class="ns-cs-dot" aria-hidden="true"></span>' . esc_html__( 'Coming soon: off', 'nolasharon' ),
			'href'  => admin_url( 'customize.php?autofocus[section]=nolasharon_coming_soon' ),
			'meta'  => array( 'title' => $on ? __( 'Visitors see the coming soon page', 'nolasharon' ) : __( 'The shop is open to visitors', 'nolasharon' ) ),
		)
	);
	$bar->add_node(
		array(
			'parent' => 'ns-coming-soon',
			'id'     => 'ns-coming-soon-toggle',
			'title'  => $on ? esc_html__( 'Turn off (open the shop)', 'nolasharon' ) : esc_html__( 'Turn on (hide the shop)', 'nolasharon' ),
			'href'   => $toggle,
		)
	);
	$bar->add_node(
		array(
			'parent' => 'ns-coming-soon',
			'id'     => 'ns-coming-soon-preview',
			'title'  => esc_html__( 'Preview the page', 'nolasharon' ),
			'href'   => add_query_arg( 'coming-soon', 'preview', home_url( '/' ) ),
			'meta'   => array( 'target' => '_blank' ),
		)
	);
	$bar->add_node(
		array(
			'parent' => 'ns-coming-soon',
			'id'     => 'ns-coming-soon-edit',
			'title'  => esc_html__( 'Edit text and launch date', 'nolasharon' ),
			'href'   => admin_url( 'customize.php?autofocus[section]=nolasharon_coming_soon' ),
		)
	);
}
add_action( 'admin_bar_menu', 'nolasharon_coming_soon_admin_bar', 90 );

/**
 * Admin bar dot colours (front end and dashboard).
 */
function nolasharon_coming_soon_admin_bar_css() {
	if ( ! is_admin_bar_showing() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	echo '<style id="ns-cs-bar">#wpadminbar .ns-cs-dot{display:inline-block;width:8px;height:8px;margin:0 7px 1px 0;border-radius:50%;background:#7c7c7c}#wpadminbar .ns-cs-dot.is-on{background:#f0b849;box-shadow:0 0 0 3px rgba(240,184,73,.25)}</style>' . "\n";
}
add_action( 'wp_head', 'nolasharon_coming_soon_admin_bar_css' );
add_action( 'admin_head', 'nolasharon_coming_soon_admin_bar_css' );

/**
 * Handle the admin bar on/off link.
 */
function nolasharon_coming_soon_toggle() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to change this.', 'nolasharon' ), 403 );
	}
	check_admin_referer( 'nolasharon_coming_soon' );
	$state = isset( $_GET['state'] ) && 'on' === $_GET['state'];
	set_theme_mod( 'nolasharon_cs_enabled', $state );
	wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
	exit;
}
add_action( 'admin_post_nolasharon_coming_soon', 'nolasharon_coming_soon_toggle' );

/**
 * Dashboard reminder while the page is on.
 */
function nolasharon_coming_soon_notice() {
	if ( ! nolasharon_coming_soon_on() || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$off = wp_nonce_url( admin_url( 'admin-post.php?action=nolasharon_coming_soon&state=off' ), 'nolasharon_coming_soon' );
	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a> · <a href="%5$s" target="_blank">%6$s</a></p></div>',
		esc_html__( 'Coming soon mode is on.', 'nolasharon' ),
		esc_html__( 'Visitors see the coming soon page instead of the shop.', 'nolasharon' ),
		esc_url( $off ),
		esc_html__( 'Turn it off', 'nolasharon' ),
		esc_url( add_query_arg( 'coming-soon', 'preview', home_url( '/' ) ) ),
		esc_html__( 'Preview', 'nolasharon' )
	);
}
add_action( 'admin_notices', 'nolasharon_coming_soon_notice' );

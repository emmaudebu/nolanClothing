<?php
/**
 * Theme-native wishlist.
 *
 * Guests keep their list in the `ns_wishlist` cookie; logged-in customers in user meta
 * (merged from the cookie when they log in). The cookie mirrors the list for everyone so
 * theme.js can show the right heart states even on cached pages.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

define( 'NOLASHARON_WISHLIST_COOKIE', 'ns_wishlist' );
define( 'NOLASHARON_WISHLIST_MAX', 100 );

/**
 * Clean a list of product IDs.
 *
 * @param array $ids Raw IDs.
 * @return int[]
 */
function nolasharon_wishlist_clean( $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	return array_slice( $ids, -NOLASHARON_WISHLIST_MAX );
}

/**
 * Product IDs in the guest cookie.
 *
 * @return int[]
 */
function nolasharon_wishlist_cookie_ids() {
	$raw = isset( $_COOKIE[ NOLASHARON_WISHLIST_COOKIE ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ NOLASHARON_WISHLIST_COOKIE ] ) ) : '';
	return nolasharon_wishlist_clean( explode( ',', $raw ) );
}

/**
 * Current visitor's wishlist.
 *
 * @return int[]
 */
function nolasharon_wishlist_get() {
	if ( is_user_logged_in() ) {
		return nolasharon_wishlist_clean( get_user_meta( get_current_user_id(), 'nolasharon_wishlist', true ) );
	}
	return nolasharon_wishlist_cookie_ids();
}

/**
 * Save the wishlist (user meta when logged in) and mirror it to the cookie.
 *
 * @param int[] $ids     Product IDs.
 * @param int   $user_id User to save for; defaults to the current user.
 */
function nolasharon_wishlist_save( $ids, $user_id = 0 ) {
	$ids     = nolasharon_wishlist_clean( $ids );
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( $user_id ) {
		update_user_meta( $user_id, 'nolasharon_wishlist', $ids );
	}
	if ( ! headers_sent() ) {
		setcookie(
			NOLASHARON_WISHLIST_COOKIE,
			implode( ',', $ids ),
			array(
				'expires'  => time() + 90 * DAY_IN_SECONDS,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => false, // theme.js reads it to set heart states.
				'samesite' => 'Lax',
			)
		);
	}
}

/**
 * Merge a guest's cookie list into their account on login.
 *
 * @param string  $user_login Username.
 * @param WP_User $user       User.
 */
function nolasharon_wishlist_merge_on_login( $user_login, $user ) {
	$saved = nolasharon_wishlist_clean( get_user_meta( $user->ID, 'nolasharon_wishlist', true ) );
	nolasharon_wishlist_save( array_merge( $saved, nolasharon_wishlist_cookie_ids() ), $user->ID );
}
add_action( 'wp_login', 'nolasharon_wishlist_merge_on_login', 10, 2 );

/**
 * Clear the cookie on logout so the next visitor on this device starts empty.
 */
function nolasharon_wishlist_clear_on_logout() {
	setcookie( NOLASHARON_WISHLIST_COOKIE, '', time() - YEAR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
}
add_action( 'wp_logout', 'nolasharon_wishlist_clear_on_logout' );

/**
 * AJAX: add or remove a product.
 */
function nolasharon_wishlist_toggle() {
	// Only signed-in lists live server-side, so only they need CSRF protection.
	if ( is_user_logged_in() && ! check_ajax_referer( 'nolasharon_wishlist', 'nonce', false ) ) {
		wp_send_json_error( array( 'message' => __( 'Please refresh the page and try again.', 'nolasharon' ) ), 403 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$product    = $product_id ? wc_get_product( $product_id ) : null;
	if ( ! $product || 'publish' !== $product->get_status() ) {
		wp_send_json_error( array( 'message' => __( 'Product not found.', 'nolasharon' ) ), 404 );
	}

	$ids   = nolasharon_wishlist_get();
	$added = ! in_array( $product_id, $ids, true );
	$ids   = $added ? array_merge( $ids, array( $product_id ) ) : array_diff( $ids, array( $product_id ) );
	nolasharon_wishlist_save( $ids );

	$ids = nolasharon_wishlist_clean( $ids );
	wp_send_json_success(
		array(
			'added' => $added,
			'ids'   => $ids,
			'count' => count( $ids ),
		)
	);
}
add_action( 'wp_ajax_nolasharon_wishlist', 'nolasharon_wishlist_toggle' );
add_action( 'wp_ajax_nopriv_nolasharon_wishlist', 'nolasharon_wishlist_toggle' );

/**
 * Wishlist page URL.
 *
 * @return string
 */
function nolasharon_wishlist_url() {
	return nolasharon_page_url( 'wishlist' );
}

/**
 * Heart toggle button. Its pressed state is set by theme.js from the cookie.
 *
 * @param WC_Product $product Product.
 * @param string     $context "card" or "single".
 */
function nolasharon_wishlist_button( $product, $context = 'card' ) {
	printf(
		'<button class="wish-btn wish-btn--%1$s" type="button" data-wishlist="%2$d" aria-pressed="false" aria-label="%3$s">%4$s</button>',
		esc_attr( $context ),
		absint( $product->get_id() ),
		/* translators: %s: product name. */
		esc_attr( sprintf( __( 'Save %s to wishlist', 'nolasharon' ), $product->get_name() ) ),
		nolasharon_icon( 'heart', 'single' === $context ? 22 : 18 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
	);
}

/**
 * Data for theme.js.
 */
function nolasharon_wishlist_script_data() {
	wp_localize_script(
		'nolasharon',
		'nolasharonWishlist',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => is_user_logged_in() ? wp_create_nonce( 'nolasharon_wishlist' ) : '',
			'cookie'  => NOLASHARON_WISHLIST_COOKIE,
			'error'   => __( 'Could not update your wishlist. Please try again.', 'nolasharon' ),
			'added'   => __( 'Saved to your wishlist', 'nolasharon' ),
			'removed' => __( 'Removed from your wishlist', 'nolasharon' ),
			'url'     => nolasharon_wishlist_url(),
			'view'    => __( 'View', 'nolasharon' ),
			/* translators: %d: number of saved products. */
			'label'   => __( 'Wishlist, %d saved', 'nolasharon' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_wishlist_script_data', 20 );

/**
 * [nolasharon_wishlist] shortcode for the Wishlist page.
 *
 * @return string
 */
function nolasharon_wishlist_shortcode() {
	$products = array_filter(
		array_map( 'wc_get_product', array_reverse( nolasharon_wishlist_get() ) ),
		function ( $product ) {
			return $product && 'publish' === $product->get_status() && $product->is_visible();
		}
	);

	ob_start();
	?>
	<div class="wishlist" data-wishlist-page>
		<?php if ( $products ) : ?>
			<div class="product-grid product-grid--archive">
				<?php
				foreach ( $products as $product ) {
					nolasharon_product_card( $product );
				}
				?>
			</div>
		<?php endif; ?>
		<div class="wishlist__empty"<?php echo $products ? ' hidden' : ''; ?>>
			<p><?php esc_html_e( 'Your wishlist is empty. Tap the heart on any piece to save it here.', 'nolasharon' ); ?></p>
			<a class="btn btn--dark" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop now', 'nolasharon' ); ?></a>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'nolasharon_wishlist', 'nolasharon_wishlist_shortcode' );

/**
 * The wishlist depends on the visitor, so keep its page out of page caches.
 */
function nolasharon_wishlist_nocache() {
	if ( is_page( 'wishlist' ) ) {
		nocache_headers();
	}
}
add_action( 'template_redirect', 'nolasharon_wishlist_nocache' );

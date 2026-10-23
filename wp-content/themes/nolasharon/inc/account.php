<?php
/**
 * Accounts (STORE-PAGES.md §2–§3): sign in, create account, password reset and the
 * My Account area. Classic WooCommerce templates plus hooks, like the checkout.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Whether this is a signed-out account screen (sign in, create account, password reset).
 * These use the split-screen layout without the site header and footer.
 *
 * @return bool
 */
function nolasharon_is_auth_page() {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return false;
	}
	return ! is_user_logged_in() || is_wc_endpoint_url( 'lost-password' );
}

/**
 * Account styles and scripts, only on My Account.
 */
function nolasharon_account_assets() {
	if ( ! is_account_page() ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-account', get_template_directory_uri() . '/assets/css/account.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-account', get_template_directory_uri() . '/assets/js/account.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script(
		'nolasharon-account',
		'nsAccount',
		array(
			'signingIn'     => __( 'Signing in…', 'nolasharon' ),
			'creating'      => __( 'Creating your account…', 'nolasharon' ),
			'sending'       => __( 'Sending link…', 'nolasharon' ),
			'saving'        => __( 'Saving…', 'nolasharon' ),
			/* translators: %d: seconds left. */
			'resendIn'      => __( 'Resend in %ds', 'nolasharon' ),
			'resend'        => __( 'Resend link', 'nolasharon' ),
			'yourEmail'     => __( 'your email', 'nolasharon' ),
			'emailInvalid'  => __( 'Please enter a valid email address.', 'nolasharon' ),
			'passwordEmpty' => __( 'Please enter your password.', 'nolasharon' ),
			'firstEmpty'    => __( 'Please enter your first name.', 'nolasharon' ),
			'phoneInvalid'  => __( 'Please enter a valid Nigerian phone number, for example 0801 234 5678.', 'nolasharon' ),
			'match'         => __( 'Passwords match', 'nolasharon' ),
			'noMatch'       => __( 'Passwords don’t match yet', 'nolasharon' ),
			'met'           => __( '(met)', 'nolasharon' ),
			'notMet'        => __( '(not met)', 'nolasharon' ),
			'required'      => __( 'This field is required.', 'nolasharon' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_account_assets', 20 );

/**
 * Remove queued WooCommerce notices whose text contains $needle (for messages a screen
 * replaces with its own, such as "Your password has been reset successfully.").
 *
 * @param string $needle Text to look for.
 */
function nolasharon_remove_notice( $needle ) {
	$all     = wc_get_notices();
	$changed = false;
	foreach ( $all as $type => $notices ) {
		foreach ( $notices as $i => $notice ) {
			if ( false !== strpos( wp_strip_all_tags( $notice['notice'] ), $needle ) ) {
				unset( $all[ $type ][ $i ] );
				$changed = true;
			}
		}
	}
	if ( $changed ) {
		wc_set_notices( array_filter( array_map( 'array_values', $all ) ) );
	}
}

/**
 * Header account link: initials when signed in, a person icon ("Sign in") otherwise.
 */
function nolasharon_header_account() {
	$url = wc_get_page_permalink( 'myaccount' );
	if ( ! is_user_logged_in() ) {
		printf(
			'<a class="icon-btn header-account" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr__( 'Sign in', 'nolasharon' ),
			nolasharon_icon( 'user', 22 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		);
		return;
	}
	printf(
		'<a class="icon-btn header-account" href="%1$s" aria-label="%2$s"%3$s><span class="header-account__initials" aria-hidden="true">%4$s</span></a>',
		esc_url( $url ),
		esc_attr__( 'My account', 'nolasharon' ),
		is_account_page() ? ' aria-current="page"' : '',
		esc_html( nolasharon_user_initials() )
	);
}

/**
 * Up to two initials for the signed-in customer, from their name or email.
 *
 * @param WP_User|null $user User; the current user by default.
 * @return string
 */
function nolasharon_user_initials( $user = null ) {
	$user  = $user ? $user : wp_get_current_user();
	$parts = array_filter( array( $user->first_name, $user->last_name ) );
	if ( ! $parts ) {
		$parts = array_filter( preg_split( '/[\s._-]+/', $user->display_name ? $user->display_name : strtok( $user->user_email, '@' ) ) );
	}
	$letters = '';
	foreach ( array_slice( array_values( $parts ), 0, 2 ) as $part ) {
		$letters .= mb_strtoupper( mb_substr( $part, 0, 1 ) );
	}
	return $letters ? $letters : '•';
}

/* Split-screen layout -------------------------------------------------------- */

/**
 * Open the sign-in layout: brand panel on the left, the form column on the right.
 */
function nolasharon_auth_open() {
	$image    = (int) nolasharon_setting( 'nolasharon_auth_image' );
	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$benefits = array(
		'truck' => __( 'Track your orders in one place', 'nolasharon' ),
		'bolt'  => __( 'Check out faster with saved details', 'nolasharon' ),
		'pin'   => __( 'Save your delivery address', 'nolasharon' ),
	);
	?>
	<div class="ns-auth">
		<aside class="ns-auth__brand">
			<span class="ns-auth__circle" aria-hidden="true"></span>
			<span class="ns-auth__ring" aria-hidden="true"></span>
			<a class="ns-auth__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'NolaSharon Clothing, home', 'nolasharon' ); ?>">
				<?php if ( $logo_id ) : ?>
					<?php echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'alt' => '' ) ); ?>
				<?php else : ?>
					<span class="ns-auth__logo-name">NolaSharon</span><span class="ns-auth__logo-sub"><?php esc_html_e( 'Clothing', 'nolasharon' ); ?></span>
				<?php endif; ?>
			</a>
			<div class="ns-auth__big">
				<div class="ns-auth__photo">
					<?php
					if ( $image ) {
						echo wp_get_attachment_image( $image, 'medium_large', false, array( 'alt' => '' ) );
					} else {
						printf( '<img src="%s" alt="" width="250" height="300">', esc_url( nolasharon_img( 'hero-figure' ) ) );
					}
					?>
				</div>
				<p class="ns-auth__line"><?php echo wp_kses( __( 'Your Style,<br>Your Way.', 'nolasharon' ), array( 'br' => array() ) ); ?></p>
				<ul class="ns-auth__benefits">
					<?php foreach ( $benefits as $icon => $label ) : ?>
						<li><span class="ns-auth__benefit-icon" aria-hidden="true"><?php echo 'bolt' === $icon ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" focusable="false"><path d="M13 2L4 14h7l-1 8 9-12h-7z"/></svg>' : nolasharon_icon( $icon, 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<p class="ns-auth__copy">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> NolaSharon Clothing</p>
		</aside>
		<div class="ns-auth__main">
			<div class="ns-auth__top">
				<a class="ns-auth__back" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php echo nolasharon_icon( 'prev', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Back to shop', 'nolasharon' ); ?></a>
			</div>
			<div class="ns-auth__center">
				<div class="ns-auth__col ns-in">
	<?php
}

/**
 * Close the sign-in layout.
 */
function nolasharon_auth_close() {
	echo '</div></div></div></div>';
}

/**
 * "← Back to sign in" link for the password screens.
 */
function nolasharon_auth_back_to_signin() {
	printf(
		'<a class="ns-auth__back ns-auth__back--in" href="%1$s">%2$s%3$s</a>',
		esc_url( wc_get_page_permalink( 'myaccount' ) ),
		nolasharon_icon( 'prev', 16 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Back to sign in', 'nolasharon' )
	);
}

/**
 * Where to send the shopper after signing in or registering: back to checkout when they
 * came from there (?return=checkout), otherwise WooCommerce's default (My Account).
 *
 * @return string Empty for the default.
 */
function nolasharon_auth_return_url() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect hint.
	return isset( $_GET['return'] ) && 'checkout' === $_GET['return'] ? wc_get_checkout_url() : '';
}

/**
 * Eye button that shows or hides the password in the field before it (ui.js).
 */
function nolasharon_password_eye() {
	printf(
		'<button type="button" class="ns-pw__eye" aria-pressed="false" aria-label="%s"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/><path class="ns-pw__slash" d="M3 3l18 18"/></svg></button>',
		esc_attr__( 'Show password', 'nolasharon' )
	);
}

/**
 * Four-bar strength meter for a password field, fed by WooCommerce's zxcvbn score (ui.js).
 *
 * @param string $for ID of the password input.
 */
function nolasharon_password_meter( $for ) {
	printf(
		'<span class="ns-meter" id="%1$s_meter" data-for="%1$s" data-score="0"><span class="ns-meter__bars" aria-hidden="true"><span></span><span></span><span></span><span></span></span><span class="ns-meter__text" aria-live="polite" data-empty="%2$s">%2$s</span></span>',
		esc_attr( $for ),
		esc_attr__( 'Use 8+ characters with a mix of letters and numbers.', 'nolasharon' )
	);
}

/* Create account: extra fields ---------------------------------------------- */

/**
 * First and last name, side by side, at the top of the registration form.
 */
function nolasharon_register_name_fields() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- refilling the form after a failed post.
	$first = isset( $_POST['ns_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_first_name'] ) ) : '';
	$last  = isset( $_POST['ns_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_last_name'] ) ) : '';
	// phpcs:enable
	?>
	<div class="ns-auth__row">
		<p class="ns-field">
			<label for="reg_first_name"><?php esc_html_e( 'First name', 'nolasharon' ); ?></label>
			<input type="text" name="ns_first_name" id="reg_first_name" autocomplete="given-name" value="<?php echo esc_attr( $first ); ?>" required aria-required="true" data-required="first">
		</p>
		<p class="ns-field">
			<label for="reg_last_name"><?php esc_html_e( 'Last name', 'nolasharon' ); ?></label>
			<input type="text" name="ns_last_name" id="reg_last_name" autocomplete="family-name" value="<?php echo esc_attr( $last ); ?>">
		</p>
	</div>
	<?php
}
add_action( 'woocommerce_register_form_start', 'nolasharon_register_name_fields' );

/**
 * Optional phone number with the +234 box, after the email field.
 */
function nolasharon_register_phone_field() {
	$phone = isset( $_POST['ns_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_phone'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	?>
	<p class="ns-field">
		<label for="reg_phone"><?php esc_html_e( 'Phone number', 'nolasharon' ); ?> <span class="ns-field__opt"><?php esc_html_e( '(optional)', 'nolasharon' ); ?></span></label>
		<span class="ns-phone">
			<span class="ns-phone__prefix" aria-hidden="true">+234</span>
			<input type="tel" name="ns_phone" id="reg_phone" inputmode="tel" autocomplete="tel-national" placeholder="<?php esc_attr_e( '801 234 5678', 'nolasharon' ); ?>" value="<?php echo esc_attr( $phone ); ?>" data-phone>
		</span>
	</p>
	<?php
}
add_action( 'nolasharon_register_form_after_email', 'nolasharon_register_phone_field' );

/**
 * Validate the extra registration fields.
 *
 * @param string   $username Username (generated from the email).
 * @param string   $email    Email.
 * @param WP_Error $errors   Errors.
 */
function nolasharon_register_validate( $username, $email, $errors ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce checked the nonce.
	if ( ! isset( $_POST['ns_first_name'] ) ) {
		return; // Not our form (e.g. an account created at checkout).
	}
	if ( '' === trim( sanitize_text_field( wp_unslash( $_POST['ns_first_name'] ) ) ) ) {
		$errors->add( 'ns_first_name', __( 'Please enter your first name.', 'nolasharon' ) );
	}
	$phone = isset( $_POST['ns_phone'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['ns_phone'] ) ) ) : '';
	if ( '' !== $phone && ! nolasharon_ng_phone( $phone ) ) {
		$errors->add( 'ns_phone', __( 'Please enter a valid Nigerian phone number, for example 0801 234 5678.', 'nolasharon' ) );
	}
	// phpcs:enable
}
add_action( 'woocommerce_register_post', 'nolasharon_register_validate', 10, 3 );

/**
 * Save the name and phone on the new account and as the billing details.
 *
 * @param int $customer_id New customer ID.
 */
function nolasharon_register_save( $customer_id ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- WooCommerce checked the nonce.
	if ( ! isset( $_POST['ns_first_name'] ) ) {
		return;
	}
	$first = sanitize_text_field( wp_unslash( $_POST['ns_first_name'] ) );
	$last  = isset( $_POST['ns_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_last_name'] ) ) : '';
	$phone = isset( $_POST['ns_phone'] ) ? nolasharon_ng_phone( sanitize_text_field( wp_unslash( $_POST['ns_phone'] ) ) ) : '';
	// phpcs:enable

	$customer = new WC_Customer( $customer_id );
	$customer->set_first_name( $first );
	$customer->set_last_name( $last );
	$customer->set_display_name( trim( $first . ' ' . $last ) );
	$customer->set_billing_first_name( $first );
	$customer->set_billing_last_name( $last );
	$customer->set_billing_email( $customer->get_email() );
	$customer->set_billing_country( 'NG' );
	if ( $phone ) {
		$customer->set_billing_phone( $phone );
	}
	$customer->save();
}
add_action( 'woocommerce_created_customer', 'nolasharon_register_save' );

/* Redirects and welcome messages ------------------------------------------- */

/**
 * After signing in: a welcome toast. WooCommerce already sends the shopper to My Account,
 * or back to the page they signed in from (such as checkout).
 *
 * @param string  $redirect Redirect URL.
 * @param WP_User $user     User.
 * @return string
 */
function nolasharon_login_redirect( $redirect, $user ) {
	$name = $user instanceof WP_User ? ( $user->first_name ? $user->first_name : $user->display_name ) : '';
	// A shopper with no cart has no session cookie yet, so the notice would be lost.
	// With the cookie set, WooCommerce moves this guest session to the account next request.
	if ( WC()->session && ! WC()->session->has_session() ) {
		WC()->session->set_customer_session_cookie( true );
	}
	/* translators: %s: first name. */
	wc_add_notice( $name ? sprintf( __( 'Welcome back, %s!', 'nolasharon' ), $name ) : __( 'Welcome back!', 'nolasharon' ) );
	return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'nolasharon_login_redirect', 10, 2 );

/**
 * After creating an account on My Account: our welcome toast instead of WooCommerce's.
 *
 * @param string $redirect Redirect URL.
 * @return string
 */
function nolasharon_registration_redirect( $redirect ) {
	nolasharon_remove_notice( 'Your account was created successfully' );
	$user = wp_get_current_user();
	/* translators: %s: first name. */
	wc_add_notice( $user->first_name ? sprintf( __( 'Welcome to NolaSharon, %s! Your account is ready.', 'nolasharon' ), $user->first_name ) : __( 'Welcome to NolaSharon! Your account is ready.', 'nolasharon' ) );
	return $redirect;
}
add_filter( 'woocommerce_registration_redirect', 'nolasharon_registration_redirect' );

/* Security ---------------------------------------------------------------- */

/**
 * One message for a wrong email, username or password, so the form never reveals which
 * emails have accounts.
 *
 * @param WP_User|WP_Error|null $user Authentication result.
 * @return WP_User|WP_Error|null
 */
function nolasharon_generic_login_error( $user ) {
	if ( is_wp_error( $user ) && array_intersect( $user->get_error_codes(), array( 'invalid_username', 'invalid_email', 'incorrect_password' ) ) ) {
		return new WP_Error( 'ns_login_failed', __( 'Incorrect email or password.', 'nolasharon' ) );
	}
	return $user;
}
add_filter( 'authenticate', 'nolasharon_generic_login_error', 99 );

/**
 * Forgot password: when no account matches, show the same "Check your email" screen as
 * when one does, instead of WooCommerce's "Invalid username or email."
 *
 * @param WP_Error      $errors    Errors so far.
 * @param WP_User|false $user_data Matching user, or false.
 */
function nolasharon_lost_password_unknown( $errors, $user_data ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce checked the nonce; this only redirects.
	if ( $user_data || empty( $_POST['wc_reset_password'] ) || $errors->has_errors() ) {
		return;
	}
	wp_safe_redirect( add_query_arg( 'reset-link-sent', 'true', wc_get_account_endpoint_url( 'lost-password' ) ) );
	exit;
}
add_action( 'lostpassword_post', 'nolasharon_lost_password_unknown', 10, 2 );

/* My Account: menu ------------------------------------------------------------ */

/**
 * Menu (STORE-PAGES.md §3): Dashboard · Orders · Addresses · Account details · Log out.
 * Downloads and Payment methods are not used.
 *
 * @param array $items endpoint => label.
 * @return array
 */
function nolasharon_account_menu_items( $items ) {
	unset( $items['downloads'], $items['payment-methods'] );
	if ( isset( $items['edit-address'] ) ) {
		$items['edit-address'] = __( 'Addresses', 'nolasharon' );
	}
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'nolasharon_account_menu_items', 20 );

/**
 * Order statuses that still need the customer to pay.
 *
 * @return string[] Without the wc- prefix.
 */
function nolasharon_unpaid_statuses() {
	return apply_filters( 'woocommerce_valid_order_statuses_for_payment', array( 'pending', 'failed' ), null );
}

/**
 * How many of the customer's orders are waiting for payment (the Orders badge).
 *
 * @return int
 */
function nolasharon_unpaid_order_count() {
	static $count = null;
	if ( null === $count ) {
		$count = is_user_logged_in() ? count(
			wc_get_orders(
				array(
					'customer_id' => get_current_user_id(),
					'status'      => nolasharon_unpaid_statuses(),
					'limit'       => -1,
					'return'      => 'ids',
				)
			)
		) : 0;
	}
	return $count;
}

/**
 * Icon for an account menu item or dashboard tile.
 *
 * @param string $endpoint Endpoint key.
 * @param int    $size     Size in px.
 * @return string SVG markup.
 */
function nolasharon_account_icon( $endpoint, $size = 19 ) {
	$paths = array(
		'dashboard'       => '<path d="M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z"/>',
		'orders'          => '<path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
		'edit-address'    => '<path d="M12 21s-7-6.1-7-11.5a7 7 0 0 1 14 0C19 14.9 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'edit-account'    => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
		'customer-logout' => '<path d="M15 4h4a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1h-4M10 17l-5-5 5-5M5 12h11"/>',
		'store'           => '<path d="M4 10l1.5-5h13L20 10M4 10v10h16V10M4 10h16M9 20v-5h6v5"/>',
		'home'            => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
	);
	$path = isset( $paths[ $endpoint ] ) ? $paths[ $endpoint ] : '<circle cx="12" cy="12" r="8"/>';
	return sprintf( '<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>', absint( $size ), $path );
}

/* Order statuses (STORE-PAGES.md §3.4) ---------------------------------------- */

/**
 * Whether the "Dispatched" and "Ready for pickup" statuses are switched on. They stay off
 * until the client confirms the fulfilment process ([[CONFIRM: fulfilment process]]).
 *
 * @return bool
 */
function nolasharon_fulfilment_statuses_on() {
	return (bool) nolasharon_setting( 'nolasharon_fulfilment_statuses' );
}

/**
 * Register wc-dispatched and wc-ready-pickup.
 */
function nolasharon_register_order_statuses() {
	if ( ! nolasharon_fulfilment_statuses_on() ) {
		return;
	}
	register_post_status(
		'wc-dispatched',
		array(
			'label'                     => _x( 'Dispatched', 'Order status', 'nolasharon' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders. */
			'label_count'               => _n_noop( 'Dispatched <span class="count">(%s)</span>', 'Dispatched <span class="count">(%s)</span>', 'nolasharon' ),
		)
	);
	register_post_status(
		'wc-ready-pickup',
		array(
			'label'                     => _x( 'Ready for pickup', 'Order status', 'nolasharon' ),
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders. */
			'label_count'               => _n_noop( 'Ready for pickup <span class="count">(%s)</span>', 'Ready for pickup <span class="count">(%s)</span>', 'nolasharon' ),
		)
	);
}
add_action( 'init', 'nolasharon_register_order_statuses' );

/**
 * Add the two statuses after "Processing" in WooCommerce's list.
 *
 * @param array $statuses wc-status => label.
 * @return array
 */
function nolasharon_order_statuses( $statuses ) {
	if ( ! nolasharon_fulfilment_statuses_on() ) {
		return $statuses;
	}
	$out = array();
	foreach ( $statuses as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			$out['wc-dispatched']   = _x( 'Dispatched', 'Order status', 'nolasharon' );
			$out['wc-ready-pickup'] = _x( 'Ready for pickup', 'Order status', 'nolasharon' );
		}
	}
	return $out;
}
add_filter( 'wc_order_statuses', 'nolasharon_order_statuses' );

/**
 * Dispatched and ready-for-pickup orders have been paid.
 *
 * @param string[] $statuses Paid statuses.
 * @return string[]
 */
function nolasharon_paid_statuses( $statuses ) {
	return nolasharon_fulfilment_statuses_on() ? array_merge( $statuses, array( 'dispatched', 'ready-pickup' ) ) : $statuses;
}
add_filter( 'woocommerce_order_is_paid_statuses', 'nolasharon_paid_statuses' );

/**
 * Remember when an order reached each fulfilment step, for the tracker's dates.
 *
 * @param int      $order_id Order ID.
 * @param string   $from     Old status.
 * @param string   $to       New status.
 * @param WC_Order $order    Order.
 */
function nolasharon_record_status_date( $order_id, $from, $to, $order ) {
	if ( in_array( $to, array( 'dispatched', 'ready-pickup' ), true ) ) {
		$order->update_meta_data( '_ns_status_date_' . $to, time() );
		$order->save_meta_data();
	}
}
add_action( 'woocommerce_order_status_changed', 'nolasharon_record_status_date', 10, 4 );

/* Orders: helpers ------------------------------------------------------------- */

/**
 * Whether an order is collected from a branch rather than delivered.
 *
 * @param WC_Order $order Order.
 * @return bool
 */
function nolasharon_order_is_pickup( $order ) {
	if ( $order->get_meta( '_ns_pickup_branch' ) ) {
		return true;
	}
	foreach ( $order->get_shipping_methods() as $method ) {
		if ( nolasharon_is_pickup_method( $method->get_method_id() ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Status pill.
 *
 * @param WC_Order $order Order.
 */
function nolasharon_order_pill( $order ) {
	printf( '<span class="ns-pill ns-pill--%1$s">%2$s</span>', esc_attr( $order->get_status() ), esc_html( wc_get_order_status_name( $order->get_status() ) ) );
}

/**
 * Number of items in an order, less refunds.
 *
 * @param WC_Order $order Order.
 * @return int
 */
function nolasharon_order_item_count( $order ) {
	return max( 0, $order->get_item_count() - $order->get_item_count_refunded() );
}

/**
 * "{date} · {n} items" line.
 *
 * @param WC_Order $order Order.
 * @return string
 */
function nolasharon_order_summary_line( $order ) {
	$count = nolasharon_order_item_count( $order );
	return wc_format_datetime( $order->get_date_created(), 'j M Y' ) . ' · ' . sprintf(
		/* translators: %d: number of items. */
		_n( '%d item', '%d items', $count, 'nolasharon' ),
		$count
	);
}

/**
 * Up to $max overlapping product thumbnails for an order.
 *
 * @param WC_Order $order Order.
 * @param int      $max   Most images to show.
 */
function nolasharon_order_thumbs( $order, $max = 2 ) {
	$shown = 0;
	echo '<span class="ns-thumbs" aria-hidden="true">';
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		$image   = $product ? (int) $product->get_image_id() : 0;
		if ( ! $image && $product && $product->get_parent_id() ) {
			$parent = wc_get_product( $product->get_parent_id() );
			$image  = $parent ? (int) $parent->get_image_id() : 0;
		}
		if ( $image ) {
			echo wp_get_attachment_image(
				$image,
				'woocommerce_thumbnail',
				false,
				array(
					'alt'     => '',
					'sizes'   => '44px',
					'loading' => 'lazy',
				)
			);
		} else {
			echo '<span class="ns-thumbs__blank"></span>';
		}
		if ( ++$shown >= $max ) {
			break;
		}
	}
	echo '</span>';
}

/**
 * Order tracker (STORE-PAGES.md §3.4): four steps from the order status. Dates come from the
 * order's own dates; never an estimate. Cancelled, failed and refunded orders get a note
 * instead, and so does any status the tracker doesn't know.
 *
 * @param WC_Order $order Order.
 */
function nolasharon_order_tracker( $order ) {
	$status = $order->get_status();
	$steps  = array(
		'pending'      => 0,
		'on-hold'      => 0,
		'processing'   => 1,
		'dispatched'   => 2,
		'ready-pickup' => 2,
		'completed'    => 3,
	);
	if ( ! isset( $steps[ $status ] ) ) {
		printf( '<p class="ns-tracker-note">%s</p>', esc_html( sprintf( /* translators: %s: order status, e.g. Cancelled. */ __( 'This order is %s.', 'nolasharon' ), strtolower( wc_get_order_status_name( $status ) ) ) ) );
		return;
	}
	$pickup = nolasharon_order_is_pickup( $order );
	$done   = $steps[ $status ];
	$third  = $pickup ? 'ready-pickup' : 'dispatched';
	$dates  = array(
		$order->get_date_created(),
		$order->get_date_paid(),
		$order->get_meta( '_ns_status_date_' . $third ) ? new WC_DateTime( '@' . (int) $order->get_meta( '_ns_status_date_' . $third ) ) : null,
		$order->get_date_completed(),
	);
	$labels = array(
		__( 'Order placed', 'nolasharon' ),
		__( 'Processing', 'nolasharon' ),
		$pickup ? __( 'Ready for pickup', 'nolasharon' ) : __( 'Dispatched', 'nolasharon' ),
		$pickup ? __( 'Collected', 'nolasharon' ) : __( 'Delivered', 'nolasharon' ),
	);
	?>
	<ol class="ns-tracker" style="--fill: <?php echo esc_attr( $done / 3 ); ?>" aria-label="<?php esc_attr_e( 'Order progress', 'nolasharon' ); ?>" data-tracker>
		<li class="ns-tracker__fill" aria-hidden="true"></li>
		<?php foreach ( $labels as $i => $label ) : ?>
			<?php
			$state = $i <= $done ? 'is-done' : ( $i === $done + 1 ? 'is-next' : '' );
			$date  = $i <= $done && $dates[ $i ] ? $dates[ $i ] : null;
			?>
			<li class="ns-tracker__step <?php echo esc_attr( $state ); ?>"<?php echo $i === $done ? ' aria-current="step"' : ''; ?>>
				<span class="ns-tracker__dot" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" focusable="false"><path d="M5 12l5 5L20 7"/></svg></span>
				<span class="ns-tracker__label"><?php echo esc_html( $label ); ?><span class="screen-reader-text"> <?php echo $i <= $done ? esc_html__( '(done)', 'nolasharon' ) : esc_html__( '(not yet)', 'nolasharon' ); ?></span></span>
				<?php if ( $date ) : ?>
					<time class="ns-tracker__when" datetime="<?php echo esc_attr( $date->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $date, 'j M' ) ); ?></time>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
	<?php
}

/**
 * Link for "Get help with this order": WhatsApp with the order number when a number is
 * set, otherwise the Contact page.
 *
 * @param WC_Order $order Order.
 * @return array( url, external )
 */
function nolasharon_order_help_link( $order ) {
	/* translators: %s: order number. */
	$whatsapp = nolasharon_whatsapp_url( sprintf( __( 'Hi, I need help with order #%s', 'nolasharon' ), $order->get_order_number() ) );
	return $whatsapp ? array( $whatsapp, true ) : array( nolasharon_page_url( 'contact' ), false );
}

/* Orders list: status filter ---------------------------------------------------- */

/**
 * Filter chips for the orders list: key => array( label, statuses ).
 *
 * @return array
 */
function nolasharon_order_filters() {
	$filters = array(
		''           => array( __( 'All', 'nolasharon' ), array() ),
		'processing' => array( __( 'Processing', 'nolasharon' ), array( 'processing' ) ),
		'pending'    => array( __( 'Pending payment', 'nolasharon' ), nolasharon_unpaid_statuses() ),
	);
	if ( nolasharon_fulfilment_statuses_on() ) {
		$filters['dispatched']   = array( __( 'Dispatched', 'nolasharon' ), array( 'dispatched' ) );
		$filters['ready-pickup'] = array( __( 'Ready for pickup', 'nolasharon' ), array( 'ready-pickup' ) );
	}
	$filters['completed'] = array( __( 'Completed', 'nolasharon' ), array( 'completed' ) );
	return $filters;
}

/**
 * The chosen filter from ?status=, or '' for all.
 *
 * @return string
 */
function nolasharon_current_order_filter() {
	$key = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only list filter.
	return isset( nolasharon_order_filters()[ $key ] ) ? $key : '';
}

/**
 * Apply ?status= to the orders query.
 *
 * @param array $args Query args.
 * @return array
 */
function nolasharon_orders_query( $args ) {
	$key = nolasharon_current_order_filter();
	if ( $key ) {
		$args['status'] = nolasharon_order_filters()[ $key ][1];
	}
	return $args;
}
add_filter( 'woocommerce_my_account_my_orders_query', 'nolasharon_orders_query' );

// The order page prints its own items, totals and "Order again" (view-order.php).
remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 );
remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button' );

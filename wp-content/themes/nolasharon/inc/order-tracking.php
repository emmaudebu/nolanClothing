<?php
/**
 * Track your order (TRACK-ORDER.md Part A): "NS-" order numbers, the lookup (order number +
 * email, rate-limited, one generic error), status messages, the updates timeline, optional
 * courier details and links to the page from emails and the thank-you page.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* Order numbers ------------------------------------------------------------------ */

/**
 * Order numbers read "NS-{ID}" everywhere (emails, My Account, admin). If the client later
 * uses a sequential-number plugin, map number → ID in nolasharon_tracking_order_id().
 *
 * @param string $number Order number.
 * @return string
 */
function nolasharon_order_number( $number ) {
	return 0 === strpos( (string) $number, 'NS-' ) ? $number : 'NS-' . $number;
}
add_filter( 'woocommerce_order_number', 'nolasharon_order_number' );

/**
 * "NS-1048", "#1048" and "1048" all mean order 1048.
 *
 * @param string $value Typed order number.
 * @return int
 */
function nolasharon_tracking_order_id( $value ) {
	return absint( preg_replace( '/\D+/', '', (string) $value ) );
}
add_filter( 'woocommerce_shortcode_order_tracking_order_id', 'nolasharon_tracking_order_id' );

/* The page ------------------------------------------------------------------------ */

/**
 * Create the "Track your order" page once (also on theme activation), with WooCommerce's
 * shortcode as the fallback content and the Track Order template.
 */
function nolasharon_track_order_page() {
	if ( get_option( 'nolasharon_track_page_v1' ) && get_page_by_path( 'track-order' ) ) {
		return;
	}
	$page = get_page_by_path( 'track-order' );
	$id   = $page ? $page->ID : wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Track your order', 'nolasharon' ),
			'post_name'    => 'track-order',
			'post_content' => '<!-- wp:shortcode -->[woocommerce_order_tracking]<!-- /wp:shortcode -->',
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/track-order.php' );
		update_option( 'nolasharon_track_page_v1', 1 );
	}
}
add_action( 'after_switch_theme', 'nolasharon_track_order_page' );
add_action( 'admin_init', 'nolasharon_track_order_page' );

/**
 * URL of the Track your order page.
 *
 * @return string
 */
function nolasharon_track_order_url() {
	return nolasharon_page_url( 'track-order' );
}

/**
 * Track Order page assets.
 */
function nolasharon_track_order_assets() {
	if ( ! is_page_template( 'page-templates/track-order.php' ) ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-track', get_template_directory_uri() . '/assets/css/track-order.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-track', get_template_directory_uri() . '/assets/js/track-order.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script(
		'nolasharon-track',
		'nsTrack',
		array(
			'invalid' => __( 'Please enter your order number and a valid email address.', 'nolasharon' ),
			'finding' => __( 'Finding your order…', 'nolasharon' ),
			'copied'  => __( 'Tracking number copied', 'nolasharon' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_track_order_assets', 20 );

/**
 * The lookup result shouldn't be indexed.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function nolasharon_track_order_noindex( $robots ) {
	if ( is_page( 'track-order' ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'nolasharon_track_order_noindex' );

/* Lookup ------------------------------------------------------------------------- */

/**
 * Run the lookup for a POSTed form: nonce, rate limit (10 per IP per 10 minutes), then
 * order number + billing email. Wrong number and wrong email give the same message.
 *
 * @return array { order: WC_Order|null, error: string, submitted: bool, orderid: string, email: string }
 */
function nolasharon_track_order_lookup() {
	static $result = null;
	if ( null !== $result ) {
		return $result;
	}
	$result = array(
		'order'     => null,
		'error'     => '',
		'submitted' => false,
		'orderid'   => '',
		'email'     => '',
	);
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified below.
	if ( 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) || ! isset( $_POST['orderid'] ) ) {
		return $result;
	}
	$result['submitted'] = true;
	$result['orderid']   = nolasharon_request_text( $_POST, 'orderid' );
	$result['email']     = sanitize_email( nolasharon_request_text( $_POST, 'order_email' ) );
	$nonce               = nolasharon_request_text( $_POST, 'woocommerce-order-tracking-nonce' );
	// phpcs:enable

	$generic = __( 'We couldn’t find an order with those details. Check the order number and the email address you used at checkout, then try again.', 'nolasharon' );

	if ( ! wp_verify_nonce( $nonce, 'woocommerce-order_tracking' ) ) {
		$result['error'] = __( 'This page has been open a while. Please try again.', 'nolasharon' );
		return $result;
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key      = 'ns_track_' . md5( $ip . wp_salt( 'nonce' ) );
	$attempts = (int) get_transient( $key );
	if ( $attempts >= 10 ) {
		$result['error'] = __( 'Too many attempts. Please wait a few minutes and try again.', 'nolasharon' );
		return $result;
	}
	set_transient( $key, $attempts + 1, 10 * MINUTE_IN_SECONDS );

	$id = nolasharon_tracking_order_id( $result['orderid'] );
	if ( ! $id || ! is_email( $result['email'] ) ) {
		$result['error'] = __( 'Please enter your order number and a valid email address.', 'nolasharon' );
		return $result;
	}
	$order = wc_get_order( $id );
	if ( $order instanceof WC_Order && 'shop_order' === $order->get_type() && strtolower( $order->get_billing_email() ) === strtolower( $result['email'] ) ) {
		$result['order'] = $order;
		do_action( 'woocommerce_track_order', $order->get_id() );
	} else {
		$result['error'] = $generic;
	}
	return $result;
}

/* Result content ---------------------------------------------------------------- */

/**
 * One-line status message for the result (DRAFT, client to approve; change with the
 * nolasharon_track_status_message filter).
 *
 * @param WC_Order $order Order.
 * @return string
 */
function nolasharon_track_status_message( $order ) {
	$pickup = nolasharon_order_is_pickup( $order );
	$branch = $pickup ? nolasharon_order_pickup_branch( $order ) : null;
	switch ( $order->get_status() ) {
		case 'pending':
		case 'failed':
			$message = __( 'We’re waiting for payment for this order.', 'nolasharon' );
			break;
		case 'on-hold':
			$message = __( 'We’ve received your order and are checking your payment.', 'nolasharon' );
			break;
		case 'processing':
			$message = $pickup ? __( 'Your order is being prepared. We’ll update this page and email you when it’s ready to collect.', 'nolasharon' ) : __( 'Your order is being prepared for dispatch.', 'nolasharon' );
			break;
		case 'dispatched':
			$message = __( 'Your order is on its way.', 'nolasharon' );
			break;
		case 'ready-pickup':
			/* translators: %s: branch name. */
			$message = $branch ? sprintf( __( 'Your order is ready to collect from %s. Please bring your order number.', 'nolasharon' ), nolasharon_pickup_branch_name( $branch ) ) : __( 'Your order is ready to collect. Please bring your order number.', 'nolasharon' );
			break;
		case 'completed':
			$message = __( 'This order has been completed. Thank you for shopping with us!', 'nolasharon' );
			break;
		case 'cancelled':
			$message = __( 'This order was cancelled. If you weren’t expecting this, please contact us.', 'nolasharon' );
			break;
		case 'refunded':
			$message = __( 'This order has been refunded. Contact us if you have any questions.', 'nolasharon' );
			break;
		default:
			$message = '';
	}
	return apply_filters( 'nolasharon_track_status_message', $message, $order );
}

/**
 * The order's updates, newest first: notes staff marked "Note to customer", plus events from
 * the order's own dates (placed, paid, dispatched / ready, completed). Private notes never show.
 *
 * @param WC_Order $order Order.
 * @return array[] { title, text, time (WC_DateTime) }
 */
function nolasharon_track_timeline( $order ) {
	$events = array();
	$add    = function ( $title, $text, $date ) use ( &$events ) {
		if ( $date ) {
			$events[] = array(
				'title' => $title,
				'text'  => $text,
				'time'  => $date,
			);
		}
	};
	$add( __( 'Order placed', 'nolasharon' ), __( 'We received your order.', 'nolasharon' ), $order->get_date_created() );
	if ( $order->get_date_paid() ) {
		/* translators: %s: payment method. */
		$add( __( 'Payment received', 'nolasharon' ), $order->get_payment_method_title() ? sprintf( __( 'Paid via %s.', 'nolasharon' ), $order->get_payment_method_title() ) : '', $order->get_date_paid() );
	}
	foreach ( array(
		'dispatched'   => __( 'Dispatched', 'nolasharon' ),
		'ready-pickup' => __( 'Ready for pickup', 'nolasharon' ),
	) as $status => $label ) {
		$stamp = (int) $order->get_meta( '_ns_status_date_' . $status );
		if ( $stamp ) {
			$add( $label, '', new WC_DateTime( '@' . $stamp ) );
		}
	}
	if ( $order->get_date_completed() ) {
		$add( __( 'Completed', 'nolasharon' ), '', $order->get_date_completed() );
	}
	foreach ( $order->get_customer_order_notes() as $note ) {
		$text  = trim( wp_strip_all_tags( $note->comment_content ) );
		$first = preg_split( '/(?<=[.!?])\s+/', $text, 2 );
		$title = isset( $first[1] ) && mb_strlen( $first[0] ) <= 80 ? rtrim( $first[0], '.' ) : __( 'Update', 'nolasharon' );
		$add( $title, isset( $first[1] ) && $title === rtrim( $first[0], '.' ) ? $first[1] : $text, new WC_DateTime( $note->comment_date_gmt, new DateTimeZone( 'UTC' ) ) );
	}
	usort(
		$events,
		function ( $a, $b ) {
			return $b['time']->getTimestamp() <=> $a['time']->getTimestamp();
		}
	);
	return $events;
}

/**
 * A time in Lagos, e.g. "13 Oct 2026, 2:10 pm".
 *
 * @param WC_DateTime $date Date.
 * @return string
 */
function nolasharon_lagos_time( $date ) {
	return wp_date( 'j M Y, g:i a', $date->getTimestamp(), new DateTimeZone( 'Africa/Lagos' ) );
}

/* Courier tracking (TRACK-ORDER.md §A5, optional) --------------------------------- */

/**
 * Courier fields on the admin order screen (only used if the client works with a courier
 * that gives tracking numbers).
 *
 * @param WC_Order $order Order.
 */
function nolasharon_courier_fields( $order ) {
	echo '<div class="ns-courier" style="clear:both;padding-top:12px"><h3>' . esc_html__( 'Shipment tracking (optional)', 'nolasharon' ) . '</h3>';
	wp_nonce_field( 'nolasharon_courier', 'nolasharon_courier_nonce' );
	foreach ( array(
		'ns_courier'         => __( 'Courier name', 'nolasharon' ),
		'ns_tracking_number' => __( 'Tracking number', 'nolasharon' ),
		'ns_tracking_url'    => __( 'Tracking link', 'nolasharon' ),
	) as $key => $label ) {
		printf(
			'<p class="form-field form-field-wide"><label for="%1$s">%2$s</label><input type="%3$s" id="%1$s" name="%1$s" value="%4$s" style="width:100%%"></p>',
			esc_attr( $key ),
			esc_html( $label ),
			'ns_tracking_url' === $key ? 'url' : 'text',
			esc_attr( $order->get_meta( $key ) )
		);
	}
	echo '<p class="description">' . esc_html__( 'Shown to the customer on the Track your order page when filled in.', 'nolasharon' ) . '</p></div>';
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'nolasharon_courier_fields', 20 );

/**
 * Save the courier fields.
 *
 * @param int $order_id Order ID.
 */
function nolasharon_courier_save( $order_id ) {
	if ( ! isset( $_POST['nolasharon_courier_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_courier_nonce'] ), 'nolasharon_courier' ) || ! current_user_can( 'edit_shop_orders' ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$order->update_meta_data( 'ns_courier', isset( $_POST['ns_courier'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_courier'] ) ) : '' );
	$order->update_meta_data( 'ns_tracking_number', isset( $_POST['ns_tracking_number'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_tracking_number'] ) ) : '' );
	$order->update_meta_data( 'ns_tracking_url', isset( $_POST['ns_tracking_url'] ) ? esc_url_raw( wp_unslash( $_POST['ns_tracking_url'] ) ) : '' );
	$order->save_meta_data();
}
add_action( 'woocommerce_process_shop_order_meta', 'nolasharon_courier_save', 50 );

/* Links to the page ----------------------------------------------------------------- */

/**
 * "Track your order" button in customer order emails.
 *
 * @param WC_Order $order         Order.
 * @param bool     $sent_to_admin Whether the email goes to the shop.
 * @param bool     $plain_text    Plain-text email.
 */
function nolasharon_email_track_link( $order, $sent_to_admin, $plain_text ) {
	if ( $sent_to_admin || ! $order instanceof WC_Order ) {
		return;
	}
	$url = nolasharon_track_order_url();
	if ( $plain_text ) {
		/* translators: %s: URL. */
		echo "\n" . esc_html( sprintf( __( 'Track your order: %s', 'nolasharon' ), $url ) ) . "\n";
		return;
	}
	printf(
		'<p style="margin:24px 0;text-align:center"><a href="%1$s" style="display:inline-block;padding:14px 28px;border-radius:999px;background:#2B1B12;color:#ffffff;font-weight:600;text-decoration:none">%2$s</a></p><p style="text-align:center;font-size:13px;color:#555555">%3$s</p>',
		esc_url( $url ),
		esc_html__( 'Track your order', 'nolasharon' ),
		/* translators: %s: order number. */
		esc_html( sprintf( __( 'Use order number %s and this email address.', 'nolasharon' ), $order->get_order_number() ) )
	);
}
add_action( 'woocommerce_email_after_order_table', 'nolasharon_email_track_link', 30, 3 );

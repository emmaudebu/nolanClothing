<?php
/**
 * Template Name: Track Order
 *
 * Track your order (TRACK-ORDER.md §A2–§A4): the lookup hero with the form card, then the
 * result (or tips before a search). A normal POST; track-order.js adds validation, the
 * loading state and the reveal. The page content holds [woocommerce_order_tracking] as a
 * fallback if another template is chosen.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_lookup = nolasharon_track_order_lookup();
$nolasharon_order  = $nolasharon_lookup['order'];
$nolasharon_orders = is_user_logged_in() ? wc_get_account_endpoint_url( 'orders' ) : add_query_arg( 'return', 'orders', wc_get_page_permalink( 'myaccount' ) );

get_header();
?>
<div class="trk<?php echo $nolasharon_order ? ' has-result' : ''; ?>" data-track>
	<section class="trk-hero">
		<span class="trk-hero__mark" aria-hidden="true"><?php esc_html_e( 'Track', 'nolasharon' ); ?></span>
		<div class="container trk-hero__inner">
			<div class="trk-hero__copy ns-in">
				<nav class="trk-crumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>
				<span class="trk-hero__eyebrow"><?php esc_html_e( 'Order tracking', 'nolasharon' ); ?></span>
				<h1 class="trk-hero__title"><?php the_title(); ?></h1>
				<p class="trk-hero__lead"><?php esc_html_e( 'Enter your order number and the email address you used at checkout to see the latest status of your order.', 'nolasharon' ); ?></p>
				<p class="trk-hero__account">
					<span class="trk-hero__truck" aria-hidden="true"><?php echo nolasharon_icon( 'truck', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php esc_html_e( 'Have an account?', 'nolasharon' ); ?> <a href="<?php echo esc_url( $nolasharon_orders ); ?>"><?php esc_html_e( 'See all your orders', 'nolasharon' ); ?></a></span>
				</p>
			</div>
			<div class="trk-hero__form ns-in ns-in--2">
				<?php wc_get_template( 'order/form-tracking.php', $nolasharon_lookup ); ?>
			</div>
		</div>
	</section>

	<div class="ns-collapse trk-result<?php echo $nolasharon_order ? ' is-open' : ''; ?>" id="trk-result" data-track-result tabindex="-1">
		<div>
			<?php
			if ( $nolasharon_order ) {
				wc_get_template( 'order/tracking.php', array( 'order' => $nolasharon_order ) );
			}
			?>
		</div>
	</div>

	<div class="ns-collapse trk-tips<?php echo $nolasharon_order ? '' : ' is-open'; ?>" data-track-tips>
		<div>
			<section class="container trk-tips__grid" aria-label="<?php esc_attr_e( 'Help with tracking', 'nolasharon' ); ?>">
				<div class="trk-tip">
					<span class="ns-icon-tile trk-tip__icon" aria-hidden="true"><?php echo nolasharon_icon( 'mail', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2 class="trk-tip__title"><?php esc_html_e( 'Where’s my order number?', 'nolasharon' ); ?></h2>
					<p><?php esc_html_e( 'It’s in the confirmation email we sent after you placed your order.', 'nolasharon' ); ?></p>
				</div>
				<div class="trk-tip">
					<span class="ns-icon-tile trk-tip__icon" aria-hidden="true"><?php echo nolasharon_icon( 'user', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2 class="trk-tip__title"><?php esc_html_e( 'Have an account?', 'nolasharon' ); ?></h2>
					<p><a href="<?php echo esc_url( $nolasharon_orders ); ?>"><?php echo is_user_logged_in() ? esc_html__( 'Open your orders', 'nolasharon' ) : esc_html__( 'Sign in', 'nolasharon' ); ?></a> <?php esc_html_e( 'to see every order and its status in one place.', 'nolasharon' ); ?></p>
				</div>
				<div class="trk-tip">
					<span class="ns-icon-tile trk-tip__icon" aria-hidden="true"><?php echo nolasharon_icon( 'chat', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<h2 class="trk-tip__title"><?php esc_html_e( 'Still need help?', 'nolasharon' ); ?></h2>
					<p><a href="<?php echo esc_url( nolasharon_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'nolasharon' ); ?></a> <?php esc_html_e( 'with your order number and we’ll help.', 'nolasharon' ); ?></p>
				</div>
			</section>
		</div>
	</div>
</div>
<?php
get_footer();

<?php
/**
 * My Account shell (STORE-PAGES.md §3): greeting band, the menu (a sidebar on desktop, a
 * scrolling row of pills on phones) and the endpoint content.
 *
 * Based on WooCommerce template myaccount/my-account.php, version 3.5.0.
 *
 * @package NolaSharon
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_user = wp_get_current_user();
$nolasharon_name = $nolasharon_user->first_name ? $nolasharon_user->first_name : $nolasharon_user->display_name;
?>
<div class="ns-acct">
	<section class="ns-acct__band">
		<span class="ns-acct__circle" aria-hidden="true"></span>
		<div class="ns-acct__inner ns-acct__band-inner ns-in">
			<div class="ns-acct__hello">
				<span class="ns-acct__avatar" aria-hidden="true"><?php echo esc_html( nolasharon_user_initials( $nolasharon_user ) ); ?></span>
				<div>
					<span class="ns-acct__eyebrow"><?php esc_html_e( 'My account', 'nolasharon' ); ?></span>
					<h1 class="ns-acct__title"><?php echo esc_html( sprintf( /* translators: %s: first name. */ __( 'Hello, %s', 'nolasharon' ), $nolasharon_name ) ); ?></h1>
				</div>
			</div>
			<p class="ns-acct__intro"><?php esc_html_e( 'Manage your orders, delivery address and account details in one place.', 'nolasharon' ); ?></p>
		</div>
	</section>

	<div class="ns-acct__inner ns-acct__body">
		<?php do_action( 'woocommerce_account_navigation' ); ?>

		<div class="woocommerce-MyAccount-content ns-acct__content ns-in ns-in--2">
			<?php do_action( 'woocommerce_account_content' ); ?>
		</div>
	</div>
</div>

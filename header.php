<?php
/**
 * Site header.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'nolasharon' ); ?></a>

<?php if ( function_exists( 'nolasharon_is_checkout_form' ) && nolasharon_is_checkout_form() ) : ?>
	<?php get_template_part( 'template-parts/checkout-header' ); ?>
<?php elseif ( function_exists( 'nolasharon_is_auth_page' ) && nolasharon_is_auth_page() ) : ?>
	<?php // Sign-in screens: the brand panel carries the logo and a "Back to shop" link. ?>
<?php else : ?>

<?php
$nolasharon_announce = nolasharon_setting( 'nolasharon_announce_text' );
if ( ! nolasharon_unconfirmed( $nolasharon_announce ) ) :
	$nolasharon_announce_link = nolasharon_setting( 'nolasharon_announce_link' );
	?>
	<div class="announce" id="announce" data-key="<?php echo esc_attr( md5( $nolasharon_announce ) ); ?>">
		<p class="announce__text">
			<?php if ( $nolasharon_announce_link ) : ?>
				<a href="<?php echo esc_url( $nolasharon_announce_link ); ?>"><?php echo esc_html( $nolasharon_announce ); ?></a>
			<?php else : ?>
				<?php echo esc_html( $nolasharon_announce ); ?>
			<?php endif; ?>
		</p>
		<button class="announce__close" type="button" aria-label="<?php esc_attr_e( 'Dismiss announcement', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'close', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	</div>
	<script>try{var a=document.getElementById('announce');if(localStorage.getItem('ns-announce')===a.getAttribute('data-key'))a.hidden=true;}catch(e){}</script>
<?php endif; ?>

<header class="site-header">
	<div class="container site-header__inner">
		<?php nolasharon_logo(); ?>

		<nav class="main-nav" id="main-nav" aria-label="<?php esc_attr_e( 'Main', 'nolasharon' ); ?>">
			<?php get_template_part( 'template-parts/nav' ); ?>
		</nav>

		<div class="site-header__tools">
			<form class="header-search" id="header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="header-search-input"><?php esc_html_e( 'Search products', 'nolasharon' ); ?></label>
				<?php echo nolasharon_icon( 'search', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<input id="header-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products...', 'nolasharon' ); ?>">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
			</form>

			<button class="icon-btn search-toggle" type="button" aria-label="<?php esc_attr_e( 'Search', 'nolasharon' ); ?>" aria-controls="header-search" aria-expanded="false">
				<?php echo nolasharon_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<?php if ( function_exists( 'nolasharon_wishlist_url' ) ) : ?>
				<a class="icon-btn header-wishlist" href="<?php echo esc_url( nolasharon_wishlist_url() ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'nolasharon' ); ?>">
					<?php echo nolasharon_icon( 'heart', 21 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="header-wishlist__count" data-wishlist-count hidden>0</span>
				</a>
			<?php endif; ?>

			<?php
			if ( function_exists( 'nolasharon_header_account' ) ) {
				nolasharon_header_account();
			}
			if ( function_exists( 'nolasharon_header_cart' ) ) {
				nolasharon_header_cart();
			}
			?>

			<button class="icon-btn menu-toggle" type="button" aria-label="<?php esc_attr_e( 'Open menu', 'nolasharon' ); ?>" aria-controls="main-nav" aria-expanded="false" data-label-open="<?php esc_attr_e( 'Open menu', 'nolasharon' ); ?>" data-label-close="<?php esc_attr_e( 'Close menu', 'nolasharon' ); ?>">
				<?php echo nolasharon_icon( 'menu', 22 ) . nolasharon_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
</header>

<?php endif; ?>

<main id="main" class="site-main">

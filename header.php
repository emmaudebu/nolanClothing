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

<header class="site-header">
	<div class="container site-header__inner">
		<?php nolasharon_logo(); ?>

		<nav class="main-nav" id="main-nav" aria-label="<?php esc_attr_e( 'Main', 'nolasharon' ); ?>">
			<?php get_template_part( 'template-parts/nav' ); ?>
		</nav>

		<div class="site-header__tools">
			<button class="icon-btn theme-toggle" type="button" aria-label="<?php esc_attr_e( 'Switch to dark theme', 'nolasharon' ); ?>" data-label-dark="<?php esc_attr_e( 'Switch to dark theme', 'nolasharon' ); ?>" data-label-light="<?php esc_attr_e( 'Switch to light theme', 'nolasharon' ); ?>">
				<?php echo nolasharon_icon( 'sun' ) . nolasharon_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

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

			<?php
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

<main id="main" class="site-main">

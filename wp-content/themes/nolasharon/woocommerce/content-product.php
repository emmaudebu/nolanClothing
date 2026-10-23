<?php
/**
 * Product in a WooCommerce loop (shortcodes, widgets, plugin loops): the theme's card
 * (SHOP-PRODUCT.md §2.4), so every listing looks the same.
 *
 * Based on WooCommerce template content-product.php, version 9.4.0.
 *
 * @package NolaSharon
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'pcard-item', $product ); ?>>
	<?php nolasharon_product_card( $product ); ?>
</li>

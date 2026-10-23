<?php
/**
 * "You may also like" (STORE-PAGES.md §4.5): cross-sells, topped up with new in-stock pieces
 * by nolasharon_cart_recommendations(). Products with sizes link to their page; simple
 * in-stock products add to the cart in place (cart.js), or by link without JavaScript.
 *
 * Based on WooCommerce template cart/cross-sells.php, version 9.6.0.
 *
 * @package NolaSharon
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $cross_sells ) ) {
	return;
}
?>
<section class="cross-sells ns-recs" aria-labelledby="ns-recs-title">
	<div class="ns-recs__head">
		<h2 class="ns-recs__title" id="ns-recs-title"><?php echo esc_html( apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may also like', 'nolasharon' ) ) ); ?></h2>
		<a class="ns-recs__all" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'View all', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( 'products', 'nolasharon' ); ?></span></a>
	</div>
	<ul class="ns-recs__grid">
		<?php
		foreach ( $cross_sells as $nolasharon_product ) :
			$nolasharon_simple = $nolasharon_product->is_type( 'simple' ) && $nolasharon_product->is_purchasable() && $nolasharon_product->is_in_stock();
			$nolasharon_name   = $nolasharon_product->get_name();
			?>
			<li class="ns-rec">
				<div class="ns-rec__media">
					<?php echo $nolasharon_product->get_image( 'woocommerce_thumbnail', array( 'alt' => '', 'class' => 'ns-rec__img', 'sizes' => '(min-width: 900px) 280px, 45vw' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php if ( $nolasharon_simple ) : ?>
						<a class="ns-rec__pill" href="<?php echo esc_url( $nolasharon_product->add_to_cart_url() ); ?>" data-rec-add="<?php echo esc_attr( $nolasharon_product->get_id() ); ?>" data-name="<?php echo esc_attr( $nolasharon_name ); ?>" rel="nofollow"><?php esc_html_e( 'Add to cart', 'nolasharon' ); ?><span class="screen-reader-text">: <?php echo esc_html( $nolasharon_name ); ?></span></a>
					<?php else : ?>
						<a class="ns-rec__pill" href="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true"><?php echo esc_html( $nolasharon_product->is_type( 'variable' ) ? __( 'Choose size', 'nolasharon' ) : __( 'View', 'nolasharon' ) ); ?></a>
					<?php endif; ?>
				</div>
				<h3 class="ns-rec__name"><a href="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>"><?php echo esc_html( $nolasharon_name ); ?></a></h3>
				<span class="ns-rec__price"><?php echo wp_kses_post( $nolasharon_product->get_price_html() ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</section>

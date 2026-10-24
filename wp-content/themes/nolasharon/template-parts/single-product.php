<?php
/**
 * Single product (SHOP-PRODUCT.md §3): breadcrumb, gallery and a sticky info column with the
 * buy form, then accordions, related products and the mobile sticky add bar. Keeps
 * WooCommerce's notices, structured data and before/after hooks.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

global $product;

$nolasharon_product = wc_get_product( get_the_ID() );
if ( ! $nolasharon_product ) {
	return;
}
$product = $nolasharon_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WooCommerce's own templates and filters read it.

if ( post_password_required() ) {
	echo '<div class="container content-area">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

$nolasharon_cat      = nolasharon_primary_category( $nolasharon_product );
$nolasharon_tags     = get_the_terms( get_the_ID(), 'product_tag' );
$nolasharon_eyebrow  = array_filter( array( $nolasharon_cat ? $nolasharon_cat->name : '', $nolasharon_tags && ! is_wp_error( $nolasharon_tags ) ? $nolasharon_tags[0]->name : '' ) );
$nolasharon_own_form = $nolasharon_product->is_type( array( 'simple', 'variable' ) );
$nolasharon_buyable  = $nolasharon_product->is_purchasable() && $nolasharon_product->is_in_stock();
$nolasharon_excerpt  = $nolasharon_product->get_short_description();
$nolasharon_tabs     = apply_filters( 'woocommerce_product_tabs', array() );
/* translators: 1: product name, 2: product URL. */
$nolasharon_ask_url  = nolasharon_whatsapp_url( sprintf( __( 'Hi, I\'m interested in %1$s – %2$s', 'nolasharon' ), $nolasharon_product->get_name(), $nolasharon_product->get_permalink() ) );

// "You may also like": upsells first, then related products from the same category.
$nolasharon_related_ids = array_slice( array_unique( array_merge( $nolasharon_product->get_upsell_ids(), wc_get_related_products( $nolasharon_product->get_id(), 8 ) ) ), 0, 4 );
$nolasharon_related     = array_filter(
	array_map( 'wc_get_product', $nolasharon_related_ids ),
	function ( $item ) {
		return $item && $item->is_visible();
	}
);

do_action( 'woocommerce_before_single_product' ); // Notices such as "added to cart".
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'container pp', $nolasharon_product ); ?>>

	<nav class="pp__crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
		<span aria-hidden="true">/</span>
		<a href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop', 'nolasharon' ); ?></a>
		<?php if ( $nolasharon_cat ) : ?>
			<span aria-hidden="true">/</span>
			<a href="<?php echo esc_url( get_term_link( $nolasharon_cat ) ); ?>"><?php echo esc_html( $nolasharon_cat->name ); ?></a>
		<?php endif; ?>
		<span aria-hidden="true">/</span>
		<span aria-current="page"><?php echo esc_html( $nolasharon_product->get_name() ); ?></span>
	</nav>

	<div class="pp__layout">
		<?php get_template_part( 'template-parts/product-gallery', null, array( 'product' => $nolasharon_product ) ); ?>

		<div class="pinfo ns-in ns-in--1">
			<div class="pinfo__head">
				<?php if ( $nolasharon_eyebrow ) : ?>
					<span class="pinfo__eyebrow"><?php echo esc_html( implode( ' · ', $nolasharon_eyebrow ) ); ?></span>
				<?php endif; ?>
				<h1 class="product_title pinfo__title"><?php echo esc_html( $nolasharon_product->get_name() ); ?></h1>
				<div class="pinfo__price-row">
					<div class="pinfo__price" data-price><?php nolasharon_product_price( $nolasharon_product ); ?></div>
					<?php if ( $nolasharon_product->get_sku() ) : ?>
						<span class="pinfo__sku"><?php echo esc_html( sprintf( /* translators: %s: SKU. */ __( 'SKU: %s', 'nolasharon' ), $nolasharon_product->get_sku() ) ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( '' !== trim( wp_strip_all_tags( $nolasharon_excerpt ) ) ) : ?>
				<div class="pinfo__short"><?php echo wp_kses_post( wpautop( $nolasharon_excerpt ) ); ?></div>
			<?php endif; ?>

			<?php if ( ! $nolasharon_buyable ) : ?>
				<p class="pbuy__stock stock--outofstock"><span class="pbuy__dot" aria-hidden="true"></span><?php esc_html_e( 'Sold out', 'nolasharon' ); ?></p>
				<?php if ( $nolasharon_ask_url ) : ?>
					<a class="pbuy__ask" href="<?php echo esc_url( $nolasharon_ask_url ); ?>" target="_blank" rel="noopener"><?php echo nolasharon_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Ask about this item on WhatsApp', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
				<?php endif; ?>
			<?php elseif ( $nolasharon_own_form ) : ?>
				<?php get_template_part( 'template-parts/buy-form', null, array( 'product' => $nolasharon_product ) ); ?>
			<?php else : ?>
				<?php woocommerce_template_single_add_to_cart(); ?>
			<?php endif; ?>

			<div class="pinfo__tiles">
				<p class="pinfo__tile"><?php echo nolasharon_icon( 'truck', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Delivery fee shown at checkout', 'nolasharon' ); ?></span></p>
				<a class="pinfo__tile" href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) ); ?>"><?php echo nolasharon_icon( 'store', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Visit us in Ikeja or Ogudu', 'nolasharon' ); ?></span></a>
			</div>

			<?php if ( $nolasharon_tabs ) : ?>
				<div class="pacc">
					<?php foreach ( $nolasharon_tabs as $nolasharon_key => $nolasharon_tab ) : ?>
						<?php
						if ( empty( $nolasharon_tab['callback'] ) ) {
							continue;
						}
						$nolasharon_open = ! empty( $nolasharon_tab['open'] );
						$nolasharon_id   = 'pacc-' . sanitize_html_class( $nolasharon_key );
						?>
						<section class="pacc__item">
							<h2 class="pacc__head">
								<button type="button" class="pacc__toggle" aria-expanded="true" aria-controls="<?php echo esc_attr( $nolasharon_id ); ?>" data-acc-toggle>
									<?php echo esc_html( wp_strip_all_tags( apply_filters( 'woocommerce_product_' . $nolasharon_key . '_tab_title', $nolasharon_tab['title'], $nolasharon_key ) ) ); ?>
									<span class="pacc__icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" focusable="false"><path d="M12 5v14M5 12h14"/></svg></span>
								</button>
							</h2>
							<div class="ns-collapse is-open pacc__body" id="<?php echo esc_attr( $nolasharon_id ); ?>"<?php echo $nolasharon_open ? '' : ' data-start-closed'; ?>>
								<div><div class="pacc__inner entry-content"><?php call_user_func( $nolasharon_tab['callback'], $nolasharon_key, $nolasharon_tab ); ?></div></div>
							</div>
						</section>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $nolasharon_related ) : ?>
		<section class="pp__related" aria-labelledby="related-title">
			<div class="pp__related-head">
				<h2 class="pp__related-title" id="related-title"><?php esc_html_e( 'You may also like', 'nolasharon' ); ?></h2>
				<a class="pp__related-all" href="<?php echo esc_url( $nolasharon_cat ? get_term_link( $nolasharon_cat ) : nolasharon_shop_url() ); ?>"><?php esc_html_e( 'View all', 'nolasharon' ); ?></a>
			</div>
			<div class="product-grid pp__related-grid">
				<?php
				foreach ( $nolasharon_related as $nolasharon_item ) {
					nolasharon_product_card( $nolasharon_item, 'pcard--compact' );
				}
				?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php if ( $nolasharon_buyable && $nolasharon_own_form ) : ?>
	<div class="sticky-buy" data-sticky-buy>
		<div class="sticky-buy__info">
			<span class="sticky-buy__meta" data-sticky-meta data-empty="<?php echo esc_attr( $nolasharon_product->is_type( 'variable' ) ? __( 'Select a size', 'nolasharon' ) : $nolasharon_product->get_name() ); ?>"><?php echo esc_html( $nolasharon_product->is_type( 'variable' ) ? __( 'Select a size', 'nolasharon' ) : $nolasharon_product->get_name() ); ?></span>
			<span class="sticky-buy__price" data-sticky-price><?php nolasharon_product_price( $nolasharon_product ); ?></span>
		</div>
		<button class="ns-btn sticky-buy__btn" type="button" data-sticky-submit><?php esc_html_e( 'Add to Cart', 'nolasharon' ); ?></button>
	</div>
<?php endif; ?>

<?php
if ( isset( WC()->structured_data ) && method_exists( WC()->structured_data, 'generate_product_data' ) ) {
	WC()->structured_data->generate_product_data( $nolasharon_product );
}
do_action( 'woocommerce_after_single_product' );

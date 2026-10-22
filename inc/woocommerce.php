<?php
/**
 * WooCommerce integration: wrappers, header cart, product flags and product cards.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

// The theme's woocommerce.php template provides the page wrapper.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

/**
 * Current cart item count.
 *
 * @return int
 */
function nolasharon_cart_count() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/**
 * Header cart link. Also returned as an AJAX fragment so the badge updates after add-to-cart.
 */
function nolasharon_header_cart() {
	$count = nolasharon_cart_count();
	printf(
		'<a class="header-cart icon-btn" href="%1$s" aria-label="%2$s">%3$s<span class="header-cart__count">%4$d</span></a>',
		esc_url( wc_get_cart_url() ),
		/* translators: %d: number of items in the cart. */
		esc_attr( sprintf( _n( 'Cart, %d item', 'Cart, %d items', $count, 'nolasharon' ), $count ) ),
		nolasharon_icon( 'cart', 22 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		absint( $count )
	);
}

/**
 * Refresh the header cart through WooCommerce cart fragments.
 *
 * @param array $fragments Fragments keyed by selector.
 * @return array
 */
function nolasharon_cart_fragment( $fragments ) {
	ob_start();
	nolasharon_header_cart();
	$fragments['a.header-cart'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'nolasharon_cart_fragment' );

/**
 * Product flags that drive the card badges.
 *
 * @return array Meta key => label.
 */
function nolasharon_product_flags() {
	return array(
		'_nolasharon_ready_to_ship' => __( 'Ready to ship', 'nolasharon' ),
		'_nolasharon_made_to_fit'   => __( 'Made-to-fit available', 'nolasharon' ),
	);
}

/**
 * Flag checkboxes on the product edit screen (General tab).
 */
function nolasharon_product_flag_fields() {
	echo '<div class="options_group">';
	foreach ( nolasharon_product_flags() as $key => $label ) {
		woocommerce_wp_checkbox(
			array(
				'id'    => $key,
				'label' => $label,
			)
		);
	}
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'nolasharon_product_flag_fields' );

/**
 * Save product flags. WooCommerce has already verified the nonce and capability at this point.
 *
 * @param WC_Product $product Product being saved.
 */
function nolasharon_save_product_flags( $product ) {
	foreach ( array_keys( nolasharon_product_flags() ) as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product->update_meta_data( $key, isset( $_POST[ $key ] ) ? 'yes' : 'no' );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'nolasharon_save_product_flags' );

/**
 * Whether a product flag is on.
 *
 * @param WC_Product $product Product.
 * @param string     $key     Meta key from nolasharon_product_flags().
 * @return bool
 */
function nolasharon_product_has_flag( $product, $key ) {
	return 'yes' === $product->get_meta( $key );
}

/**
 * Product image, or the bundled placeholder illustration named in `_nolasharon_placeholder`.
 *
 * @param WC_Product $product Product.
 */
function nolasharon_product_image( $product ) {
	if ( $product->get_image_id() ) {
		echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => $product->get_name() ) );
		return;
	}
	$placeholder = $product->get_meta( '_nolasharon_placeholder' );
	$src         = $placeholder ? nolasharon_img( $placeholder ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );
	printf( '<img src="%1$s" alt="%2$s" width="400" height="500" loading="lazy" decoding="async">', esc_url( $src ), esc_attr( $product->get_name() ) );
}

/**
 * Price block: struck regular price, red sale price and "% off" when on sale.
 *
 * @param WC_Product $product Product.
 */
function nolasharon_product_price( $product ) {
	if ( $product->is_type( 'variable' ) ) {
		$regular = (float) $product->get_variation_regular_price( 'min' );
		$current = (float) $product->get_variation_price( 'min' );
	} else {
		$regular = (float) $product->get_regular_price();
		$current = (float) $product->get_price();
	}

	if ( '' === $product->get_price() ) {
		return;
	}

	echo '<span class="price-row">';
	if ( $product->is_on_sale() && $regular > $current && $regular > 0 ) {
		$pct = (int) round( ( $regular - $current ) / $regular * 100 );
		printf(
			'<s class="price-row__regular"><span class="screen-reader-text">%1$s</span>%2$s</s> <span class="price-row__sale"><span class="screen-reader-text">%3$s</span>%4$s</span> <span class="price-row__off">%5$s</span>',
			esc_html__( 'Was', 'nolasharon' ),
			wp_kses_post( wc_price( $regular ) ),
			esc_html__( 'Now', 'nolasharon' ),
			wp_kses_post( wc_price( $current ) ),
			/* translators: %d: discount percentage. */
			esc_html( sprintf( __( '%d%% off', 'nolasharon' ), $pct ) )
		);
	} else {
		echo '<span class="price-row__current">' . wp_kses_post( wc_price( $current ) ) . '</span>';
	}
	echo '</span>';
}

/**
 * Product card used on the homepage (and later the shop grid).
 *
 * @param WC_Product $product Product.
 */
function nolasharon_product_card( $product ) {
	?>
	<a class="product-card" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<span class="product-card__media">
			<?php nolasharon_product_image( $product ); ?>
			<?php if ( nolasharon_product_has_flag( $product, '_nolasharon_ready_to_ship' ) ) : ?>
				<span class="badge badge--ready"><?php echo nolasharon_icon( 'truck', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Ready to ship!', 'nolasharon' ); ?></span>
			<?php elseif ( nolasharon_product_has_flag( $product, '_nolasharon_made_to_fit' ) ) : ?>
				<span class="badge badge--mtf"><?php esc_html_e( 'Made to fit', 'nolasharon' ); ?></span>
			<?php endif; ?>
		</span>
		<span class="product-card__name"><?php echo esc_html( $product->get_name() ); ?></span>
		<?php nolasharon_product_price( $product ); ?>
	</a>
	<?php
}

<?php
/**
 * WooCommerce integration: wrappers, header cart, categories, badges and product cards.
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
 * Portrait (4:5) catalogue thumbnails to match the product cards. WooCommerce regenerates them on demand.
 *
 * @param array $size Image size: width, height, crop.
 * @return array
 */
function nolasharon_thumbnail_size( $size ) {
	return array(
		'width'  => 480,
		'height' => 600,
		'crop'   => 1,
	);
}
add_filter( 'woocommerce_get_image_size_thumbnail', 'nolasharon_thumbnail_size' );

/**
 * Six rows of four on product listings (brief §5.1: about 24 per page).
 *
 * @return int
 */
function nolasharon_products_per_page() {
	return 24;
}
add_filter( 'loop_shop_per_page', 'nolasharon_products_per_page' );

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
		'<a class="header-cart icon-btn" href="%1$s" aria-label="%2$s" data-label-one="%5$s" data-label-many="%6$s">%3$s<span class="header-cart__count">%4$d</span></a>',
		esc_url( wc_get_cart_url() ),
		/* translators: %d: number of items in the cart. */
		esc_attr( sprintf( _n( 'Cart, %d item', 'Cart, %d items', $count, 'nolasharon' ), $count ) ),
		nolasharon_icon( 'cart', 22 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
		absint( $count ),
		/* translators: %d: number of items in the cart (always 1). */
		esc_attr( _n( 'Cart, %d item', 'Cart, %d items', 1, 'nolasharon' ) ),
		/* translators: %d: number of items in the cart. */
		esc_attr( _n( 'Cart, %d item', 'Cart, %d items', 2, 'nolasharon' ) )
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
 * Top-level product categories for the menu, footer and homepage.
 * Skips the default "Uncategorized" bucket and the "Sample" demo category.
 *
 * @return WP_Term[]
 */
function nolasharon_nav_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
			'exclude'    => array_filter( array( (int) get_option( 'default_product_cat' ) ) ),
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	return array_values(
		array_filter(
			$terms,
			function ( $term ) {
				return 'sample' !== $term->slug;
			}
		)
	);
}

/**
 * Categories for the homepage cards: those ticked "Show on homepage", else the top-level ones.
 *
 * @return WP_Term[]
 */
function nolasharon_home_categories() {
	$all     = nolasharon_nav_categories();
	$flagged = array_filter(
		$all,
		function ( $term ) {
			return '1' === get_term_meta( $term->term_id, 'nolasharon_home', true );
		}
	);
	return array_slice( $flagged ? array_values( $flagged ) : $all, 0, 4 );
}

/**
 * "Show on homepage" checkbox on the product category screens.
 *
 * @param WP_Term|string $term Term being edited, or the taxonomy slug on the add screen.
 */
function nolasharon_category_home_field( $term ) {
	$checked = $term instanceof WP_Term && '1' === get_term_meta( $term->term_id, 'nolasharon_home', true );
	wp_nonce_field( 'nolasharon_cat_home', 'nolasharon_cat_home_nonce' );
	if ( $term instanceof WP_Term ) {
		echo '<tr class="form-field"><th scope="row">' . esc_html__( 'Homepage', 'nolasharon' ) . '</th><td>';
	} else {
		echo '<div class="form-field">';
	}
	printf( '<label><input type="checkbox" name="nolasharon_home" value="1" %1$s> %2$s</label>', checked( $checked, true, false ), esc_html__( 'Show on homepage', 'nolasharon' ) );
	echo $term instanceof WP_Term ? '</td></tr>' : '</div>';
}
add_action( 'product_cat_add_form_fields', 'nolasharon_category_home_field' );
add_action( 'product_cat_edit_form_fields', 'nolasharon_category_home_field' );

/**
 * Save the "Show on homepage" flag.
 *
 * @param int $term_id Term ID.
 */
function nolasharon_category_home_save( $term_id ) {
	if ( ! isset( $_POST['nolasharon_cat_home_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_cat_home_nonce'] ), 'nolasharon_cat_home' ) || ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	update_term_meta( $term_id, 'nolasharon_home', empty( $_POST['nolasharon_home'] ) ? '' : '1' );
}
add_action( 'created_product_cat', 'nolasharon_category_home_save' );
add_action( 'edited_product_cat', 'nolasharon_category_home_save' );

/**
 * Card badges (brief §6): "Sold out", "Sale" and "New" (younger than the configured days). Never "Bestseller".
 *
 * @param WC_Product $product Product.
 * @return array class => label
 */
function nolasharon_product_badges( $product ) {
	if ( ! $product->is_in_stock() ) {
		return array( 'soldout' => __( 'Sold out', 'nolasharon' ) );
	}
	$badges = array();
	if ( $product->is_on_sale() ) {
		$badges['sale'] = __( 'Sale', 'nolasharon' );
	}
	$days    = (int) nolasharon_setting( 'nolasharon_new_days' );
	$created = $product->get_date_created();
	if ( $days > 0 && $created && $created->getTimestamp() > time() - $days * DAY_IN_SECONDS ) {
		$badges['new'] = __( 'New', 'nolasharon' );
	}
	return $badges;
}

/**
 * Colour terms on a product, with their swatch hex.
 *
 * @param WC_Product $product Product.
 * @return array[] { name, slug, hex }
 */
function nolasharon_product_colours( $product ) {
	if ( ! taxonomy_exists( 'pa_colour' ) ) {
		return array();
	}
	$terms = wc_get_product_terms( $product->get_id(), 'pa_colour', array( 'fields' => 'all' ) );
	return array_map(
		function ( $term ) {
			return array(
				'name' => $term->name,
				'slug' => $term->slug,
				'hex'  => sanitize_hex_color( get_term_meta( $term->term_id, 'nolasharon_hex', true ) ),
			);
		},
		$terms
	);
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
 * Product card used on the homepage, listings, related products and the wishlist.
 *
 * The product name link stretches over the whole card (see .product-card__name::after),
 * so the quick view and wishlist buttons can sit on top without nesting inside a link.
 *
 * @param WC_Product $product Product.
 */
function nolasharon_product_card( $product ) {
	?>
	<div class="product-card">
		<span class="product-card__media">
			<?php /* The image links too; hidden from tab order and screen readers since the name link already covers it. */ ?>
			<a class="product-card__img-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
				<?php nolasharon_product_image( $product ); ?>
			</a>
			<?php $badges = nolasharon_product_badges( $product ); ?>
			<?php if ( $badges ) : ?>
				<span class="badges">
					<?php foreach ( $badges as $class => $label ) : ?>
						<span class="badge badge--<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></span>
					<?php endforeach; ?>
				</span>
			<?php endif; ?>
			<button class="quick-view-btn" type="button" data-quick-view="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Quick view: %s', 'nolasharon' ), $product->get_name() ) ); ?>"><?php esc_html_e( 'Quick View', 'nolasharon' ); ?></button>
		</span>
		<a class="product-card__name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		<?php nolasharon_product_price( $product ); ?>
		<?php $colours = array_slice( nolasharon_product_colours( $product ), 0, 6 ); ?>
		<?php if ( $colours ) : ?>
			<span class="card-swatches">
				<span class="screen-reader-text"><?php esc_html_e( 'Colours:', 'nolasharon' ); ?></span>
				<?php foreach ( $colours as $colour ) : ?>
					<span class="card-swatch" title="<?php echo esc_attr( $colour['name'] ); ?>" style="--swatch: <?php echo esc_attr( $colour['hex'] ? $colour['hex'] : '#cccccc' ); ?>"><span class="screen-reader-text"><?php echo esc_html( $colour['name'] ); ?></span></span>
				<?php endforeach; ?>
			</span>
		<?php endif; ?>
		<?php nolasharon_wishlist_button( $product, 'card' ); ?>
	</div>
	<?php
}

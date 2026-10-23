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
 * Tile colours for the homepage collection section (STORE-PAGES.md §1).
 *
 * @return array tone => label
 */
function nolasharon_tile_tones() {
	return array(
		'brand' => __( 'Brown (white text)', 'nolasharon' ),
		'dark'  => __( 'Dark brown (white text)', 'nolasharon' ),
		'sand'  => __( 'Sand (dark text)', 'nolasharon' ),
		'cream' => __( 'Cream (dark text)', 'nolasharon' ),
	);
}

/**
 * Categories for the homepage collection section: those ticked "Show on the homepage",
 * else the top-level ones, ordered by "Homepage order" then name. Up to four.
 *
 * @return WP_Term[]
 */
function nolasharon_home_categories() {
	$all     = nolasharon_nav_categories();
	$flagged = array_filter(
		$all,
		function ( $term ) {
			return '1' === get_term_meta( $term->term_id, 'ns_home_featured', true );
		}
	);
	$terms   = $flagged ? array_values( $flagged ) : $all;
	usort(
		$terms,
		function ( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, 'ns_home_order', true );
			$ob = (int) get_term_meta( $b->term_id, 'ns_home_order', true );
			// Unnumbered categories go after numbered ones.
			$oa = $oa > 0 ? $oa : PHP_INT_MAX;
			$ob = $ob > 0 ? $ob : PHP_INT_MAX;
			return $oa === $ob ? strcasecmp( $a->name, $b->name ) : $oa <=> $ob;
		}
	);
	return array_slice( $terms, 0, 4 );
}

/**
 * Move the old "Show on homepage" flag to ns_home_featured (runs once).
 */
function nolasharon_migrate_category_meta() {
	if ( get_option( 'nolasharon_cat_meta_v2' ) ) {
		return;
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'meta_key'   => 'nolasharon_home', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off migration.
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( '1' === get_term_meta( $term->term_id, 'nolasharon_home', true ) ) {
				update_term_meta( $term->term_id, 'ns_home_featured', '1' );
			}
			delete_term_meta( $term->term_id, 'nolasharon_home' );
		}
	}
	update_option( 'nolasharon_cat_meta_v2', 1 );
}
add_action( 'init', 'nolasharon_migrate_category_meta', 20 );

/**
 * Homepage fields on the product category screens: show, order, tile colour, subtitle.
 *
 * @param WP_Term|string $term Term being edited, or the taxonomy slug on the add screen.
 */
function nolasharon_category_home_field( $term ) {
	$edit     = $term instanceof WP_Term;
	$featured = $edit && '1' === get_term_meta( $term->term_id, 'ns_home_featured', true );
	$order    = $edit ? (int) get_term_meta( $term->term_id, 'ns_home_order', true ) : 0;
	$tone     = $edit ? get_term_meta( $term->term_id, 'ns_tile_tone', true ) : '';
	$subtitle = $edit ? get_term_meta( $term->term_id, 'ns_tile_subtitle', true ) : '';

	$fields = array(
		'ns_home_featured' => array(
			__( 'Homepage', 'nolasharon' ),
			sprintf( '<label><input type="checkbox" name="ns_home_featured" id="ns_home_featured" value="1" %1$s> %2$s</label>', checked( $featured, true, false ), esc_html__( 'Show in "Shop Our Collection" on the homepage', 'nolasharon' ) ),
			__( 'Up to four categories show. If none are ticked, the top-level categories show.', 'nolasharon' ),
		),
		'ns_home_order'    => array(
			__( 'Homepage order', 'nolasharon' ),
			sprintf( '<input type="number" name="ns_home_order" id="ns_home_order" min="0" step="1" value="%s" style="width:6em">', $order ? esc_attr( $order ) : '' ),
			__( '1 is the big tile, then 2, 3 and 4.', 'nolasharon' ),
		),
		'ns_tile_tone'     => array(
			__( 'Tile colour', 'nolasharon' ),
			'<select name="ns_tile_tone" id="ns_tile_tone"><option value="">' . esc_html__( 'Automatic', 'nolasharon' ) . '</option>' . implode(
				'',
				array_map(
					function ( $key, $label ) use ( $tone ) {
						return sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), selected( $tone, $key, false ), esc_html( $label ) );
					},
					array_keys( nolasharon_tile_tones() ),
					nolasharon_tile_tones()
				)
			) . '</select>',
			'',
		),
		'ns_tile_subtitle' => array(
			__( 'Tile subtitle', 'nolasharon' ),
			sprintf( '<input type="text" name="ns_tile_subtitle" id="ns_tile_subtitle" value="%s" maxlength="80">', esc_attr( $subtitle ) ),
			__( 'Optional short line under the name on the large tiles, e.g. "Dresses, sets and everyday pieces." Hidden when empty.', 'nolasharon' ),
		),
	);

	if ( ! $edit ) {
		wp_nonce_field( 'nolasharon_cat_home', 'nolasharon_cat_home_nonce' );
	}
	foreach ( $fields as $id => $field ) {
		$desc = $field[2] ? '<p class="description">' . esc_html( $field[2] ) . '</p>' : '';
		if ( $edit ) {
			if ( 'ns_home_featured' === $id ) {
				wp_nonce_field( 'nolasharon_cat_home', 'nolasharon_cat_home_nonce' );
			}
			printf( '<tr class="form-field"><th scope="row"><label for="%1$s">%2$s</label></th><td>%3$s%4$s</td></tr>', esc_attr( $id ), esc_html( $field[0] ), $field[1], $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		} else {
			printf( '<div class="form-field"><label for="%1$s">%2$s</label>%3$s%4$s</div>', esc_attr( $id ), esc_html( $field[0] ), $field[1], $desc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
	}
}
add_action( 'product_cat_add_form_fields', 'nolasharon_category_home_field' );
add_action( 'product_cat_edit_form_fields', 'nolasharon_category_home_field' );

/**
 * Save the homepage fields.
 *
 * @param int $term_id Term ID.
 */
function nolasharon_category_home_save( $term_id ) {
	if ( ! isset( $_POST['nolasharon_cat_home_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_cat_home_nonce'] ), 'nolasharon_cat_home' ) || ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	$tone = isset( $_POST['ns_tile_tone'] ) ? sanitize_key( $_POST['ns_tile_tone'] ) : '';
	update_term_meta( $term_id, 'ns_home_featured', empty( $_POST['ns_home_featured'] ) ? '' : '1' );
	update_term_meta( $term_id, 'ns_home_order', isset( $_POST['ns_home_order'] ) ? absint( $_POST['ns_home_order'] ) : 0 );
	update_term_meta( $term_id, 'ns_tile_tone', isset( nolasharon_tile_tones()[ $tone ] ) ? $tone : '' );
	update_term_meta( $term_id, 'ns_tile_subtitle', isset( $_POST['ns_tile_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_tile_subtitle'] ) ) : '' );
}
add_action( 'created_product_cat', 'nolasharon_category_home_save' );
add_action( 'edited_product_cat', 'nolasharon_category_home_save' );

/**
 * Card and gallery badges (SHOP-PRODUCT.md §2.4): "New" (younger than the configured days),
 * "Sale −{x}%" and "Sold out". Never "Bestseller".
 *
 * @param WC_Product $product Product.
 * @return array class => label
 */
function nolasharon_product_badges( $product ) {
	if ( ! $product->is_in_stock() ) {
		return array( 'soldout' => __( 'Sold out', 'nolasharon' ) );
	}
	$badges  = array();
	$days    = (int) nolasharon_setting( 'nolasharon_new_days' );
	$created = $product->get_date_created();
	if ( $days > 0 && $created && $created->getTimestamp() > time() - $days * DAY_IN_SECONDS ) {
		$badges['new'] = __( 'New', 'nolasharon' );
	}
	if ( $product->is_on_sale() ) {
		$pct           = nolasharon_sale_percent( $product );
		/* translators: %d: discount percentage. */
		$badges['sale'] = $pct ? sprintf( __( 'Sale −%d%%', 'nolasharon' ), $pct ) : __( 'Sale', 'nolasharon' );
	}
	return $badges;
}

/**
 * The biggest discount on a product, in whole percent (variable products: across variations).
 *
 * @param WC_Product $product Product or variation.
 * @return int
 */
function nolasharon_sale_percent( $product ) {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}
	$pairs = array();
	if ( $product->is_type( 'variable' ) ) {
		$prices = $product->get_variation_prices( true );
		foreach ( $prices['price'] as $id => $price ) {
			$pairs[] = array( (float) $prices['regular_price'][ $id ], (float) $price );
		}
	} else {
		$pairs[] = array( (float) $product->get_regular_price(), (float) $product->get_price() );
	}
	$best = 0;
	foreach ( $pairs as $pair ) {
		if ( $pair[0] > 0 && $pair[1] < $pair[0] ) {
			$best = max( $best, (int) round( ( $pair[0] - $pair[1] ) / $pair[0] * 100 ) );
		}
	}
	return $best;
}

/**
 * The product's main category, skipping the "Sample" demo bucket.
 *
 * @param WC_Product $product Product.
 * @return WP_Term|null
 */
function nolasharon_primary_category( $product ) {
	$id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
	foreach ( (array) get_the_terms( $id, 'product_cat' ) as $term ) {
		if ( $term instanceof WP_Term && 'sample' !== $term->slug && 'uncategorized' !== $term->slug ) {
			return $term;
		}
	}
	return null;
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
 * Price block: struck regular price, red sale price and "% off" when on sale. Used on the
 * product page, in the quick view and for variation prices.
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
			'<span class="price-row__sale"><span class="screen-reader-text">%1$s</span>%2$s</span> <s class="price-row__regular"><span class="screen-reader-text">%3$s</span>%4$s</s> <span class="price-row__off">%5$s</span>',
			esc_html__( 'Now', 'nolasharon' ),
			wp_kses_post( wc_price( $current ) ),
			esc_html__( 'Was', 'nolasharon' ),
			wp_kses_post( wc_price( $regular ) ),
			/* translators: %d: discount percentage. */
			esc_html( sprintf( __( 'Save %d%%', 'nolasharon' ), $pct ) )
		);
	} else {
		echo '<span class="price-row__current">' . wp_kses_post( wc_price( $current ) ) . '</span>';
	}
	echo '</span>';
}

/**
 * Product card (SHOP-PRODUCT.md §2.4) for listings, the homepage, related products and the
 * wishlist: 4:5 image that cross-fades to the first gallery image on hover, badges, a
 * Quick view pill (or "View details" when sold out), category, name, price and colour dots.
 * The image and name link to the product; the image link is out of the tab order.
 *
 * @param WC_Product $product Product.
 * @param string     $class   Extra class, e.g. "pcard--compact".
 */
function nolasharon_product_card( $product, $class = '' ) {
	$url      = $product->get_permalink();
	$name     = $product->get_name();
	$soldout  = ! $product->is_in_stock();
	$category = nolasharon_primary_category( $product );
	$second   = array_slice( $product->get_gallery_image_ids(), 0, 1 );
	$colours  = nolasharon_product_colours( $product );
	$badges   = nolasharon_product_badges( $product );
	?>
	<article class="pcard<?php echo $soldout ? ' is-soldout' : ''; ?><?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>">
		<div class="pcard__media">
			<a class="pcard__img" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true">
				<?php nolasharon_product_image( $product ); ?>
				<?php if ( $second && $product->get_image_id() ) : ?>
					<?php echo wp_get_attachment_image( $second[0], 'woocommerce_thumbnail', false, array( 'alt' => '', 'class' => 'pcard__alt', 'loading' => 'lazy' ) ); ?>
				<?php endif; ?>
			</a>
			<?php if ( $badges ) : ?>
				<span class="pcard__badges">
					<?php foreach ( $badges as $type => $label ) : ?>
						<span class="pbadge pbadge--<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $label ); ?></span>
					<?php endforeach; ?>
				</span>
			<?php endif; ?>
			<?php if ( $soldout ) : ?>
				<a class="pcard__cta" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true"><?php esc_html_e( 'View details', 'nolasharon' ); ?></a>
			<?php else : ?>
				<button class="pcard__cta quick-view-btn" type="button" data-quick-view="<?php echo esc_attr( $product->get_id() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Quick view: %s', 'nolasharon' ), $name ) ); ?>"><?php esc_html_e( 'Quick view', 'nolasharon' ); ?></button>
			<?php endif; ?>
			<?php
			if ( function_exists( 'nolasharon_wishlist_button' ) ) {
				nolasharon_wishlist_button( $product, 'card' );
			}
			?>
		</div>
		<div class="pcard__body">
			<?php if ( $category ) : ?>
				<span class="pcard__cat"><?php echo esc_html( $category->name ); ?></span>
			<?php endif; ?>
			<a class="pcard__name" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $name ); ?></a>
			<?php nolasharon_card_price( $product ); ?>
			<?php if ( $colours ) : ?>
				<span class="pcard__dots">
					<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: colour names. */ __( 'Colours: %s', 'nolasharon' ), implode( ', ', wp_list_pluck( $colours, 'name' ) ) ) ); ?></span>
					<?php foreach ( array_slice( $colours, 0, 5 ) as $colour ) : ?>
						<span class="pcard__dot" title="<?php echo esc_attr( $colour['name'] ); ?>" style="--swatch: <?php echo esc_attr( $colour['hex'] ? $colour['hex'] : '#cccccc' ); ?>" aria-hidden="true"></span>
					<?php endforeach; ?>
					<?php if ( count( $colours ) > 5 ) : ?>
						<span class="pcard__more" aria-hidden="true">+<?php echo esc_html( count( $colours ) - 5 ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Card price: the sale price in red with the regular price struck through after it.
 * Variable products show their lowest price.
 *
 * @param WC_Product $product Product.
 */
function nolasharon_card_price( $product ) {
	if ( '' === $product->get_price() ) {
		return;
	}
	if ( $product->is_type( 'variable' ) ) {
		$regular = (float) $product->get_variation_regular_price( 'min' );
		$current = (float) $product->get_variation_price( 'min' );
	} else {
		$regular = (float) $product->get_regular_price();
		$current = (float) $product->get_price();
	}
	echo '<span class="pcard__price">';
	if ( $product->is_on_sale() && $regular > $current ) {
		printf(
			'<span class="pcard__now"><span class="screen-reader-text">%1$s</span>%2$s</span> <s class="pcard__was"><span class="screen-reader-text">%3$s</span>%4$s</s>',
			esc_html__( 'Now', 'nolasharon' ),
			wp_kses_post( wc_price( $current ) ),
			esc_html__( 'Was', 'nolasharon' ),
			wp_kses_post( wc_price( $regular ) )
		);
	} else {
		echo wp_kses_post( wc_price( $current ) );
	}
	echo '</span>';
}

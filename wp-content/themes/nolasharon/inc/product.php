<?php
/**
 * Products (brief §6): Size and Colour attributes with per-variation stock, colour swatches,
 * the Details fields (fabric, fit, care) and the data the buy form uses to disable sold-out options.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Create the global Size (pa_size) and Colour (pa_colour) attributes once.
 */
function nolasharon_ensure_attributes() {
	if ( get_option( 'nolasharon_attributes_ready' ) || ! function_exists( 'wc_create_attribute' ) ) {
		return;
	}
	$existing = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_name' );
	foreach ( array( 'size' => __( 'Size', 'nolasharon' ), 'colour' => __( 'Colour', 'nolasharon' ) ) as $slug => $name ) {
		if ( ! in_array( $slug, $existing, true ) ) {
			wc_create_attribute(
				array(
					'name'         => $name,
					'slug'         => $slug,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => false,
				)
			);
		}
	}
	update_option( 'nolasharon_attributes_ready', 1 );
}
add_action( 'admin_init', 'nolasharon_ensure_attributes' );
add_action( 'after_switch_theme', 'nolasharon_ensure_attributes' );

/**
 * Swatch colour field on Colour terms (Products → Attributes → Colour → Configure terms).
 *
 * @param WP_Term|string $term Term being edited, or the taxonomy on the add screen.
 */
function nolasharon_colour_hex_field( $term ) {
	$value = $term instanceof WP_Term ? get_term_meta( $term->term_id, 'nolasharon_hex', true ) : '';
	wp_nonce_field( 'nolasharon_colour_hex', 'nolasharon_colour_hex_nonce' );
	$input = sprintf( '<input type="color" name="nolasharon_hex" id="nolasharon-hex" value="%s">', esc_attr( $value ? $value : '#cccccc' ) );
	$help  = esc_html__( 'Used for the colour swatch on product pages and cards.', 'nolasharon' );
	if ( $term instanceof WP_Term ) {
		printf( '<tr class="form-field"><th scope="row"><label for="nolasharon-hex">%1$s</label></th><td>%2$s<p class="description">%3$s</p></td></tr>', esc_html__( 'Swatch colour', 'nolasharon' ), $input, $help ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	} else {
		printf( '<div class="form-field"><label for="nolasharon-hex">%1$s</label>%2$s<p>%3$s</p></div>', esc_html__( 'Swatch colour', 'nolasharon' ), $input, $help ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
add_action( 'pa_colour_add_form_fields', 'nolasharon_colour_hex_field' );
add_action( 'pa_colour_edit_form_fields', 'nolasharon_colour_hex_field' );

/**
 * Save the swatch colour.
 *
 * @param int $term_id Term ID.
 */
function nolasharon_colour_hex_save( $term_id ) {
	if ( ! isset( $_POST['nolasharon_colour_hex_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_colour_hex_nonce'] ), 'nolasharon_colour_hex' ) || ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	update_term_meta( $term_id, 'nolasharon_hex', isset( $_POST['nolasharon_hex'] ) ? sanitize_hex_color( wp_unslash( $_POST['nolasharon_hex'] ) ) : '' );
}
add_action( 'created_pa_colour', 'nolasharon_colour_hex_save' );
add_action( 'edited_pa_colour', 'nolasharon_colour_hex_save' );

/**
 * Details fields shown in the product page "Details" panel when filled.
 *
 * @return array meta key => label
 */
function nolasharon_detail_fields() {
	return array(
		'_nolasharon_fabric' => __( 'Fabric / material', 'nolasharon' ),
		'_nolasharon_fit'    => __( 'Fit', 'nolasharon' ),
		'_nolasharon_care'   => __( 'Care instructions', 'nolasharon' ),
	);
}

/**
 * Details fields on the product edit screen (General tab).
 */
function nolasharon_detail_fields_html() {
	echo '<div class="options_group">';
	foreach ( nolasharon_detail_fields() as $key => $label ) {
		woocommerce_wp_textarea_input(
			array(
				'id'          => $key,
				'label'       => $label,
				'rows'        => 3,
				'desc_tip'    => true,
				'description' => __( 'Shown under "Details" on the product page. Leave empty to hide. Accurate information only.', 'nolasharon' ),
			)
		);
	}
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'nolasharon_detail_fields_html' );

/**
 * Save the Details fields. WooCommerce has already verified the nonce and capability.
 *
 * @param WC_Product $product Product being saved.
 */
function nolasharon_detail_fields_save( $product ) {
	foreach ( array_keys( nolasharon_detail_fields() ) as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product->update_meta_data( $key, isset( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : '' );
	}
}
add_action( 'woocommerce_admin_process_product_object', 'nolasharon_detail_fields_save' );

/**
 * Customer-facing stock text. Status is always written out, never shown by colour alone.
 *
 * @param WC_Product $product Product or variation.
 * @return array { text, state } where state is instock, low, backorder or outofstock.
 */
function nolasharon_stock_status( $product ) {
	if ( ! $product->is_in_stock() ) {
		return array(
			'text'  => __( 'Sold out', 'nolasharon' ),
			'state' => 'outofstock',
		);
	}
	if ( $product->is_on_backorder( 1 ) ) {
		return array(
			'text'  => __( 'Available on backorder', 'nolasharon' ),
			'state' => 'backorder',
		);
	}
	$qty = $product->managing_stock() ? (int) $product->get_stock_quantity() : null;
	if ( null !== $qty && $qty > 0 && $qty <= wc_get_low_stock_amount( $product ) ) {
		return array(
			/* translators: %d: units left. */
			'text'  => sprintf( _n( 'Only %d left in stock', 'Only %d left in stock', $qty, 'nolasharon' ), $qty ),
			'state' => 'low',
		);
	}
	return array(
		'text'  => __( 'In stock', 'nolasharon' ),
		'state' => 'instock',
	);
}

/**
 * Options for the buy form: each variation attribute with its choices, colour first, then size.
 *
 * @param WC_Product_Variable $product Variable product.
 * @return array[] { name, label, is_colour, is_size, options: [ { value, label, hex } ] }
 */
function nolasharon_buy_attributes( $product ) {
	$groups = array();
	foreach ( $product->get_variation_attributes() as $name => $values ) {
		$is_tax  = taxonomy_exists( $name );
		$options = array();
		if ( $is_tax ) {
			// Keep the admin's term order rather than the order values were added.
			foreach ( wc_get_product_terms( $product->get_id(), $name, array( 'fields' => 'all' ) ) as $term ) {
				if ( in_array( $term->slug, $values, true ) ) {
					$options[] = array(
						'value' => $term->slug,
						'label' => $term->name,
						'hex'   => 'pa_colour' === $name ? sanitize_hex_color( get_term_meta( $term->term_id, 'nolasharon_hex', true ) ) : '',
					);
				}
			}
		} else {
			foreach ( $values as $value ) {
				$options[] = array(
					'value' => $value,
					'label' => $value,
					'hex'   => '',
				);
			}
		}
		$groups[] = array(
			'name'      => 'attribute_' . sanitize_title( $name ),
			'label'     => wc_attribute_label( $name, $product ),
			'is_colour' => 'pa_colour' === $name,
			'is_size'   => 'pa_size' === $name,
			'options'   => $options,
		);
	}
	usort(
		$groups,
		function ( $a, $b ) {
			return ( $b['is_colour'] <=> $a['is_colour'] ) ?: ( $b['is_size'] <=> $a['is_size'] );
		}
	);
	return $groups;
}

/**
 * Compact variation data for theme.js: attributes, stock, price markup and limits.
 *
 * @param WC_Product_Variable $product Variable product.
 * @return array[]
 */
function nolasharon_buy_variations( $product ) {
	$data = array();
	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );
		if ( ! $variation || ! $variation->exists() || 'publish' !== $variation->get_status() || '' === $variation->get_price() ) {
			continue;
		}
		ob_start();
		nolasharon_product_price( $variation );
		$price  = ob_get_clean();
		$stock  = nolasharon_stock_status( $variation );
		$max    = $variation->get_max_purchase_quantity();
		$image  = (int) $variation->get_image_id();
		$data[] = array(
			'image' => $image && $image !== (int) $product->get_image_id() ? nolasharon_gallery_image( $image, $product->get_name() ) : null,
			'id'    => $variation->get_id(),
			'attrs' => $variation->get_variation_attributes(),
			'ok'    => $variation->is_purchasable() && $variation->is_in_stock(),
			'stock' => $stock['text'],
			'state' => $stock['state'],
			'price' => $price,
			'max'   => $max > 0 ? $max : 0,
		);
	}
	return $data;
}

/**
 * Detail rows for a cart line: chosen size/colour plus anything other plugins add.
 *
 * @param array $cart_item Cart item.
 * @return array label => value (value may contain markup from WooCommerce).
 */
function nolasharon_cart_item_rows( $cart_item ) {
	$rows = array();
	if ( ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $name => $value ) {
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );
			if ( taxonomy_exists( $taxonomy ) ) {
				$term  = get_term_by( 'slug', $value, $taxonomy );
				$value = $term && ! is_wp_error( $term ) ? $term->name : $value;
				$label = wc_attribute_label( $taxonomy );
			} else {
				$label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cart_item['data'] );
			}
			if ( '' !== $value ) {
				$rows[ $label ] = $value;
			}
		}
	}
	foreach ( apply_filters( 'woocommerce_get_item_data', array(), $cart_item ) as $row ) {
		$rows[ isset( $row['key'] ) ? $row['key'] : $row['name'] ] = isset( $row['display'] ) ? $row['display'] : $row['value'];
	}
	return $rows;
}

/* Product page (SHOP-PRODUCT.md §3) ------------------------------------------------ */

/**
 * Product page styles and scripts: the gallery, PhotoSwipe lightbox and size guide.
 */
function nolasharon_product_assets() {
	if ( ! is_product() ) {
		return;
	}
	wp_enqueue_style( 'photoswipe-default-skin' );
	wp_enqueue_style( 'nolasharon-product', get_template_directory_uri() . '/assets/css/product.css', array( 'nolasharon', 'photoswipe-default-skin' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'wc-photoswipe-ui-default' );
	wp_enqueue_script( 'nolasharon-product', get_template_directory_uri() . '/assets/js/product.js', array( 'nolasharon', 'wc-photoswipe-ui-default' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'nolasharon_product_assets', 20 );

/**
 * PhotoSwipe's markup in the footer of product pages.
 */
function nolasharon_photoswipe_markup() {
	if ( is_product() ) {
		wc_get_template( 'single-product/photoswipe.php' );
	}
}
add_action( 'wp_footer', 'nolasharon_photoswipe_markup' );

/**
 * One gallery image's sources.
 *
 * @param int    $image_id Attachment ID.
 * @param string $fallback Alt text when the media library has none.
 * @return array|null { id, src, srcset, full, w, h, thumb, alt }
 */
function nolasharon_gallery_image( $image_id, $fallback ) {
	$main = wp_get_attachment_image_src( $image_id, 'woocommerce_single' );
	$full = wp_get_attachment_image_src( $image_id, 'full' );
	if ( ! $main || ! $full ) {
		return null;
	}
	$alt = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
	return array(
		'id'     => $image_id,
		'src'    => $main[0],
		'srcset' => (string) wp_get_attachment_image_srcset( $image_id, 'woocommerce_single' ),
		'full'   => $full[0],
		'w'      => (int) $full[1],
		'h'      => (int) $full[2],
		'thumb'  => (string) wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ),
		'alt'    => '' !== $alt ? $alt : $fallback,
	);
}

/**
 * The product's images: featured first, then the gallery. Alt text from the media library,
 * else "{Product} – image {n}". Products without photos use their placeholder illustration.
 *
 * @param WC_Product $product Product.
 * @return array[] See nolasharon_gallery_image().
 */
function nolasharon_product_images( $product ) {
	$images = array();
	$ids    = array_values( array_unique( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) ) );
	foreach ( $ids as $i => $id ) {
		/* translators: 1: product name, 2: image number. */
		$image = nolasharon_gallery_image( $id, sprintf( __( '%1$s – image %2$d', 'nolasharon' ), $product->get_name(), $i + 1 ) );
		if ( $image ) {
			$images[] = $image;
		}
	}
	if ( ! $images ) {
		$placeholder = $product->get_meta( '_nolasharon_placeholder' );
		$src         = $placeholder ? nolasharon_img( $placeholder ) : wc_placeholder_img_src( 'woocommerce_single' );
		$images[]    = array(
			'id'     => 0,
			'src'    => $src,
			'srcset' => '',
			'full'   => $src,
			'w'      => 800,
			'h'      => 1000,
			'thumb'  => $src,
			'alt'    => $product->get_name(),
		);
	}
	return $images;
}

/* Size charts (SHOP-PRODUCT.md §3.5) -------------------------------------------- */

/**
 * Parse a size chart typed as rows: the first line is the header (e.g. "Size, Bust, Waist,
 * Hips"), then one line per size ("S, 34, 28, 37"). Measurements in inches.
 *
 * @param string $text Chart text.
 * @return array|null { headers: string[], rows: string[][] }
 */
function nolasharon_parse_size_chart( $text ) {
	$lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
	if ( count( $lines ) < 2 ) {
		return null;
	}
	$split   = function ( $line ) {
		return array_map( 'trim', preg_split( '/\t|,|\|/', $line ) );
	};
	$headers = $split( array_shift( $lines ) );
	$rows    = array();
	foreach ( $lines as $line ) {
		$cells = array_pad( array_slice( $split( $line ), 0, count( $headers ) ), count( $headers ), '' );
		if ( '' !== $cells[0] ) {
			$rows[] = $cells;
		}
	}
	return $rows ? array(
		'headers' => $headers,
		'rows'    => $rows,
	) : null;
}

/**
 * The size chart for a product: its own override, else its (or a parent) category's chart.
 * Nothing when neither exists, so the "Size guide" link stays hidden.
 *
 * @param WC_Product $product Product.
 * @return array|null { headers, rows, category }
 */
function nolasharon_size_chart( $product ) {
	$chart    = nolasharon_parse_size_chart( $product->get_meta( '_ns_size_chart' ) );
	$category = nolasharon_primary_category( $product );
	if ( ! $chart ) {
		foreach ( (array) get_the_terms( $product->get_id(), 'product_cat' ) as $term ) {
			if ( ! $term instanceof WP_Term ) {
				continue;
			}
			foreach ( array_merge( array( $term->term_id ), get_ancestors( $term->term_id, 'product_cat' ) ) as $term_id ) {
				$chart = nolasharon_parse_size_chart( get_term_meta( $term_id, 'ns_size_chart', true ) );
				if ( $chart ) {
					$category = get_term( $term_id, 'product_cat' );
					break 2;
				}
			}
		}
	}
	if ( $chart ) {
		$chart['category'] = $category instanceof WP_Term ? $category->name : '';
	}
	return $chart;
}

/**
 * "Size chart" field on the product category screens.
 *
 * @param WP_Term|string $term Term being edited, or the taxonomy on the add screen.
 */
function nolasharon_size_chart_field( $term ) {
	$edit  = $term instanceof WP_Term;
	$value = $edit ? get_term_meta( $term->term_id, 'ns_size_chart', true ) : '';
	$input = sprintf( '<textarea name="ns_size_chart" id="ns_size_chart" rows="7" style="font-family:monospace">%s</textarea>', esc_textarea( $value ) );
	$help  = esc_html__( 'The client\'s own measurements, in inches. First line: the column names, e.g. "Size, Bust, Waist, Hips". Then one line per size, e.g. "S, 34, 28, 37". Products in this category show a "Size guide" link only when this is filled in.', 'nolasharon' );
	wp_nonce_field( 'nolasharon_size_chart', 'nolasharon_size_chart_nonce' );
	if ( $edit ) {
		printf( '<tr class="form-field"><th scope="row"><label for="ns_size_chart">%1$s</label></th><td>%2$s<p class="description">%3$s</p></td></tr>', esc_html__( 'Size chart', 'nolasharon' ), $input, $help ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	} else {
		printf( '<div class="form-field"><label for="ns_size_chart">%1$s</label>%2$s<p>%3$s</p></div>', esc_html__( 'Size chart', 'nolasharon' ), $input, $help ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
add_action( 'product_cat_add_form_fields', 'nolasharon_size_chart_field', 20 );
add_action( 'product_cat_edit_form_fields', 'nolasharon_size_chart_field', 20 );

/**
 * Save the category size chart.
 *
 * @param int $term_id Term ID.
 */
function nolasharon_size_chart_save( $term_id ) {
	if ( ! isset( $_POST['nolasharon_size_chart_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_size_chart_nonce'] ), 'nolasharon_size_chart' ) || ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	update_term_meta( $term_id, 'ns_size_chart', isset( $_POST['ns_size_chart'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ns_size_chart'] ) ) : '' );
}
add_action( 'created_product_cat', 'nolasharon_size_chart_save' );
add_action( 'edited_product_cat', 'nolasharon_size_chart_save' );

/**
 * Per-product size chart override (Inventory tab).
 */
function nolasharon_product_size_chart_field() {
	woocommerce_wp_textarea_input(
		array(
			'id'          => '_ns_size_chart',
			'label'       => __( 'Size chart (optional)', 'nolasharon' ),
			'rows'        => 5,
			'desc_tip'    => true,
			'description' => __( 'Only if this product fits differently from its category. Same format as the category size chart: a header line, then one line per size, in inches.', 'nolasharon' ),
		)
	);
}
add_action( 'woocommerce_product_options_inventory_product_data', 'nolasharon_product_size_chart_field' );

/**
 * Save the per-product size chart.
 *
 * @param WC_Product $product Product being saved.
 */
function nolasharon_product_size_chart_save( $product ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce verified the nonce.
	$product->update_meta_data( '_ns_size_chart', isset( $_POST['_ns_size_chart'] ) ? sanitize_textarea_field( wp_unslash( $_POST['_ns_size_chart'] ) ) : '' );
}
add_action( 'woocommerce_admin_process_product_object', 'nolasharon_product_size_chart_save' );

/* Accordions (SHOP-PRODUCT.md §3.3) ---------------------------------------------- */

/**
 * The product page's accordion sections, built on woocommerce_product_tabs so tabs added
 * by plugins still appear: Description, Details, Delivery information, Returns &
 * exchanges. Reviews and "Additional information" are dropped; empty sections are left out.
 *
 * @param array $tabs Tabs.
 * @return array
 */
function nolasharon_product_tabs( $tabs ) {
	global $product;
	unset( $tabs['reviews'], $tabs['additional_information'], $tabs['description'] );
	if ( ! $product instanceof WC_Product ) {
		return $tabs;
	}

	$out = array();
	if ( '' !== trim( wp_strip_all_tags( $product->get_description() ) ) ) {
		$out['description'] = array(
			'title'    => __( 'Description', 'nolasharon' ),
			'priority' => 10,
			'callback' => function () {
				the_content();
			},
			'open'     => true,
		);
	}

	$details = array();
	foreach ( nolasharon_detail_fields() as $key => $label ) {
		$value = trim( (string) $product->get_meta( $key ) );
		if ( ! nolasharon_unconfirmed( $value ) ) {
			$details[ $label ] = $value;
		}
	}
	if ( $details ) {
		$out['details'] = array(
			'title'    => __( 'Details', 'nolasharon' ),
			'priority' => 20,
			'callback' => function () use ( $details ) {
				echo '<dl class="pacc__details">';
				foreach ( $details as $label => $value ) {
					printf( '<div><dt>%1$s</dt><dd>%2$s</dd></div>', esc_html( $label ), nl2br( esc_html( $value ) ) );
				}
				echo '</dl>';
			},
		);
	}

	$delivery = nolasharon_setting( 'nolasharon_delivery_info' );
	if ( ! nolasharon_unconfirmed( $delivery ) ) {
		$out['delivery'] = array(
			'title'    => __( 'Delivery information', 'nolasharon' ),
			'priority' => 30,
			'callback' => function () use ( $delivery ) {
				echo wp_kses_post( wpautop( esc_html( $delivery ) ) );
				printf( '<p><a href="%1$s">%2$s</a></p>', esc_url( nolasharon_page_url( 'delivery-policy' ) ), esc_html__( 'Read our Delivery Policy', 'nolasharon' ) );
			},
		);
	}

	$returns = nolasharon_setting( 'nolasharon_returns_info' );
	if ( ! nolasharon_unconfirmed( $returns ) ) {
		$out['returns'] = array(
			'title'    => __( 'Returns & exchanges', 'nolasharon' ),
			'priority' => 40,
			'callback' => function () use ( $returns ) {
				echo wp_kses_post( wpautop( esc_html( $returns ) ) );
				printf( '<p><a href="%1$s">%2$s</a></p>', esc_url( nolasharon_page_url( 'returns-policy' ) ), esc_html__( 'Read our Returns & Exchange Policy', 'nolasharon' ) );
			},
		);
	}

	// Anything a plugin added comes after ours.
	foreach ( $tabs as $key => $tab ) {
		$tab['priority'] = 50 + ( isset( $tab['priority'] ) ? (int) $tab['priority'] : 0 );
		$out[ $key ]     = $tab;
	}
	return $out;
}
add_filter( 'woocommerce_product_tabs', 'nolasharon_product_tabs', 98 );

/**
 * Reviews stay off until the client asks for them (CLAUDE.md: no fake social proof).
 *
 * @return string
 */
function nolasharon_reviews_off() {
	return 'no';
}
add_filter( 'pre_option_woocommerce_enable_reviews', 'nolasharon_reviews_off' );

/**
 * Show every variation's data in the page, so availability is worked out without requests.
 *
 * @return int
 */
function nolasharon_variation_threshold() {
	return 100;
}
add_filter( 'woocommerce_ajax_variation_threshold', 'nolasharon_variation_threshold' );

/**
 * Without JavaScript the buy form posts the chosen size and colour but no variation ID, and
 * WooCommerce no longer works that out itself. Find the matching variation first.
 */
function nolasharon_resolve_posted_variation() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- mirrors WooCommerce's own add-to-cart, which has no nonce.
	if ( empty( $_REQUEST['add-to-cart'] ) || ! empty( $_REQUEST['variation_id'] ) ) {
		return;
	}
	$product = wc_get_product( absint( $_REQUEST['add-to-cart'] ) );
	if ( ! $product || ! $product->is_type( 'variable' ) ) {
		return;
	}
	$attributes = array();
	foreach ( $_REQUEST as $key => $value ) {
		if ( 0 === strpos( (string) $key, 'attribute_' ) && is_string( $value ) ) {
			$attributes[ sanitize_title( wp_unslash( $key ) ) ] = wc_clean( wp_unslash( $value ) );
		}
	}
	// phpcs:enable
	if ( ! $attributes ) {
		return;
	}
	$variation_id = ( new WC_Product_Data_Store_CPT() )->find_matching_product_variation( $product, $attributes );
	if ( $variation_id ) {
		$_REQUEST['variation_id'] = $variation_id;
		$_POST['variation_id']    = $variation_id;
	}
}
add_action( 'wp_loaded', 'nolasharon_resolve_posted_variation', 19 );

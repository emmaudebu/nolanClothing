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
	if ( null !== $qty && $qty > 0 && $qty <= 5 ) {
		return array(
			/* translators: %d: units left. */
			'text'  => sprintf( _n( 'Only %d left', 'Only %d left', $qty, 'nolasharon' ), $qty ),
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
		$data[] = array(
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

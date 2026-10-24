<?php
/**
 * Bags and Shoes (client confirmed 2026-10-25): the two categories, the Shoe size terms,
 * extra colours and 8 [SAMPLE] products with placeholder images.
 *
 * Run from the site folder with this file and the sample-images folder copied beside it:
 *   studio wp eval-file ns-tmp/seed-bags-shoes.php
 *
 * Safe to re-run: it only adds what is missing and never deletes products. Prices, stock,
 * sizes and details are demo values; every product is titled [SAMPLE], sits in the "Sample"
 * category and must be deleted before launch. The real shoe size range and the Shoes size
 * chart come from the client.
 */

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

nolasharon_ensure_attributes();
// Attribute taxonomies created this request are not registered until the next one.
foreach ( array( 'pa_shoe-size' => 'Shoe size' ) as $tax => $label ) {
	if ( ! taxonomy_exists( $tax ) ) {
		register_taxonomy( $tax, 'product', array( 'hierarchical' => false, 'label' => $label ) );
	}
}

$term = function ( $taxonomy, $name, $hex = '' ) {
	$t = get_term_by( 'name', $name, $taxonomy );
	if ( ! $t ) {
		$res = wp_insert_term( $name, $taxonomy );
		$t   = get_term( $res['term_id'], $taxonomy );
	}
	if ( $hex ) {
		update_term_meta( $t->term_id, 'nolasharon_hex', $hex );
	}
	return $t;
};

/* Categories ------------------------------------------------------------------------- */

$cat = function ( $name, $slug, $order ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $t ) {
		$res = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		$t   = get_term( $res['term_id'], 'product_cat' );
	}
	update_term_meta( $t->term_id, 'order', $order );
	if ( ! get_term_meta( $t->term_id, 'ns_home_order', true ) ) {
		update_term_meta( $t->term_id, 'ns_home_order', $order );
	}
	return (int) $t->term_id;
};
$cats = array(
	'women'  => $cat( 'Women', 'women', 1 ),
	'men'    => $cat( 'Men', 'men', 2 ),
	'shoes'  => $cat( 'Shoes', 'shoes', 3 ),
	'bags'   => $cat( 'Bags', 'bags', 4 ),
	'sample' => (int) get_term_by( 'slug', 'sample', 'product_cat' )->term_id,
);

/* Attribute terms -------------------------------------------------------------------- */

// Demo shoe sizes (EU). Replace with the client's real range.
$shoe_sizes = array();
foreach ( range( 36, 45 ) as $i => $size ) {
	$t = $term( 'pa_shoe-size', (string) $size );
	update_term_meta( $t->term_id, 'order', $i );
	$shoe_sizes[ (string) $size ] = $t;
}
foreach ( array(
	'Tan'        => '#A86B3C',
	'Brown'      => '#6B4A2E',
	'Black'      => '#111111',
	'Gold'       => '#C9A227',
	'Wine'       => '#7B1E3A',
	'Cream'      => '#EFE6D2',
	'Olive'      => '#5B6B3A',
	'Terracotta' => '#C65A2E',
) as $name => $hex ) {
	$term( 'pa_colour', $name, $hex );
}

/* Images ----------------------------------------------------------------------------- */

$image = function ( $name ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'name' => 'sample-' . $name, 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $found ) {
		return (int) $found[0];
	}
	$src = __DIR__ . '/sample-images/' . $name . '.png';
	if ( ! file_exists( $src ) ) {
		WP_CLI::warning( "Missing image {$src}" );
		return 0;
	}
	$tmp = wp_tempnam( $name . '.png' );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => $name . '.png', 'tmp_name' => $tmp ), 0, '[SAMPLE] ' . $name );
	return is_wp_error( $id ) ? 0 : (int) $id;
};

/* Products --------------------------------------------------------------------------- */

// name, image, categories, regular, sale, colours, shoe sizes (empty = bag, colour only),
// days old, featured, sold-out combos ("Colour|Size" or "Colour"), dimensions, features, material.
$products = array(
	array( 'Leather Tote Bag', 'tote-bag', array( 'bags' ), 55000, 0, array( 'Tan', 'Black' ), array(), 4, true, array(), 'Width 38 cm × Height 30 cm × Depth 14 cm; handle drop 22 cm', 'Two top handles, magnetic closure, inner zip pocket', 'Leather' ),
	array( 'Beaded Evening Clutch', 'clutch-bag', array( 'bags' ), 28000, 24000, array( 'Gold', 'Cream' ), array(), 8, false, array( 'Cream' ), 'Width 26 cm × Height 15 cm × Depth 5 cm', 'Detachable chain strap, clasp closure', 'Beaded fabric' ),
	array( 'Mini Crossbody Bag', 'crossbody-bag', array( 'bags' ), 32000, 0, array( 'Black', 'Wine' ), array(), 15, false, array(), 'Width 22 cm × Height 17 cm × Depth 7 cm; strap drop 55 cm', 'Adjustable shoulder strap, zip closure, card slot', 'Faux leather' ),
	array( 'Ankara Top-Handle Bag', 'ankara-bag', array( 'bags' ), 26000, 0, array( 'Terracotta' ), array(), 30, true, array(), 'Width 30 cm × Height 24 cm × Depth 11 cm', 'Top handle, snap closure, lined interior', 'Ankara cotton with faux-leather trim' ),
	array( 'Block-Heel Mules', 'block-heels', array( 'shoes', 'women' ), 42000, 36000, array( 'Wine', 'Black' ), array( '36', '37', '38', '39', '40', '41' ), 3, true, array( 'Wine|36', 'Black|41' ), '', 'Heel height 7 cm, cushioned insole, slip-on', 'Suede-look upper' ),
	array( 'Embellished Flat Sandals', 'flat-sandals', array( 'shoes', 'women' ), 25000, 0, array( 'Gold' ), array( '36', '37', '38', '39', '40', '41' ), 12, false, array( 'Gold|39' ), '', 'Flat sole, embellished straps, buckle fastening', 'Faux leather' ),
	array( 'Classic Leather Loafers', 'loafers', array( 'shoes', 'men' ), 58000, 0, array( 'Black', 'Brown' ), array( '40', '41', '42', '43', '44', '45' ), 6, true, array(), '', 'Slip-on, padded insole, stitched sole', 'Leather' ),
	array( 'Canvas Sneakers', 'sneakers', array( 'shoes', 'men', 'women' ), 35000, 0, array( 'Cream', 'Olive' ), array( '38', '39', '40', '41', '42', '43', '44', '45' ), 20, false, array( 'Olive|38' ), '', 'Lace-up, cushioned insole, rubber sole', 'Canvas' ),
);

$added = 0;
foreach ( $products as $i => $row ) {
	list( $name, $img, $in, $regular, $sale, $colours, $sizes, $days, $featured, $soldout, $dimensions, $features, $material ) = $row;
	if ( get_page_by_title( '[SAMPLE] ' . $name, OBJECT, 'product' ) ) { // phpcs:ignore WordPress.WP.DeprecatedFunctions
		continue;
	}
	$colour_terms = array_map( function ( $c ) { return get_term_by( 'name', $c, 'pa_colour' ); }, $colours );
	$size_terms   = array_map( function ( $s ) use ( $shoe_sizes ) { return $shoe_sizes[ $s ]; }, $sizes );
	$is_shoe      = (bool) $sizes;

	$product = new WC_Product_Variable();
	$product->set_name( '[SAMPLE] ' . $name );
	$product->set_status( 'publish' );
	$product->set_description( '[SAMPLE] Demo product for layout testing. Replace with the client’s product description. [[CONFIRM: product description]]' );
	$product->set_short_description( '[SAMPLE] Demo product. Replace with the client’s short description.' );
	$product->set_category_ids( array_filter( array_merge( array_map( function ( $c ) use ( $cats ) { return $cats[ $c ]; }, $in ), array( $cats['sample'] ) ) ) );
	$product->set_featured( $featured );
	$product->set_image_id( $image( $img ) );
	$product->set_date_created( time() - $days * DAY_IN_SECONDS - $i * 60 );
	$product->update_meta_data( '_nolasharon_fabric', '[SAMPLE] ' . $material );
	$product->update_meta_data( '_nolasharon_features', '[SAMPLE] ' . $features );
	if ( $dimensions ) {
		$product->update_meta_data( '_nolasharon_dimensions', '[SAMPLE] ' . $dimensions );
	}
	if ( $is_shoe ) {
		$product->update_meta_data( '_nolasharon_fit', '[SAMPLE] True to size. [[CONFIRM: shoe fit guidance]]' );
	}

	$attrs = array();
	$sets  = array( 'pa_colour' => $colour_terms );
	if ( $is_shoe ) {
		$sets['pa_shoe-size'] = $size_terms;
	}
	foreach ( $sets as $tax => $terms ) {
		$a = new WC_Product_Attribute();
		$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
		$a->set_name( $tax );
		$a->set_options( wp_list_pluck( $terms, 'term_id' ) );
		$a->set_visible( true );
		$a->set_variation( true );
		$attrs[] = $a;
	}
	$product->set_attributes( $attrs );
	$parent_id = $product->save();

	foreach ( $colour_terms as $colour ) {
		foreach ( $is_shoe ? $size_terms : array( null ) as $size ) {
			$v    = new WC_Product_Variation();
			$v->set_parent_id( $parent_id );
			$atts = array( 'pa_colour' => $colour->slug );
			if ( $size ) {
				$atts['pa_shoe-size'] = $size->slug;
			}
			$v->set_attributes( $atts );
			$v->set_regular_price( (string) $regular );
			if ( $sale ) {
				$v->set_sale_price( (string) $sale );
			}
			$v->set_manage_stock( true );
			$key = $colour->name . ( $size ? '|' . $size->name : '' );
			$v->set_stock_quantity( in_array( $key, $soldout, true ) ? 0 : ( $size ? 4 : 6 ) );
			$v->set_status( 'publish' );
			$v->save();
		}
	}
	WC_Product_Variable::sync( $parent_id );
	wc_delete_product_transients( $parent_id );
	++$added;
}

// Category thumbnails for the homepage tiles and menus (placeholders until real photos).
foreach ( array( 'bags' => 'tote-bag', 'shoes' => 'block-heels' ) as $slug => $img ) {
	if ( ! get_term_meta( $cats[ $slug ], 'thumbnail_id', true ) ) {
		update_term_meta( $cats[ $slug ], 'thumbnail_id', $image( $img ) );
	}
}

WP_CLI::success( "{$added} bag and shoe sample products added." );

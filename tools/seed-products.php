<?php
/**
 * Local seed: replace the old demo products with [SAMPLE] variable products (brief §6).
 * Prices and stock are demo values; every product is in the "Sample" category and must be deleted before launch.
 */

// Remove the earlier simple demo products (keep their images for reuse).
foreach ( wc_get_products( array( 'limit' => -1, 'status' => array( 'publish', 'draft', 'private' ) ) ) as $old ) {
	foreach ( $old->get_children() as $child ) {
		wp_delete_post( $child, true );
	}
	wp_delete_post( $old->get_id(), true );
}

$image = function ( $name ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'name__in' => array( $name, $name . '-2' ), 'posts_per_page' => 1, 'fields' => 'ids' ) );
	return $found ? (int) $found[0] : 0;
};

$term_id = function ( $taxonomy, $name, $hex = '' ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( ! $term ) {
		$res  = wp_insert_term( $name, $taxonomy );
		$term = get_term( $res['term_id'], $taxonomy );
	}
	if ( $hex ) {
		update_term_meta( $term->term_id, 'nolasharon_hex', $hex );
	}
	return $term;
};

$sizes   = array( 'S', 'M', 'L', 'XL' );
$colours = array(
	'Indigo'     => '#1E2A5A',
	'Black'      => '#111111',
	'Gold'       => '#C9A227',
	'Wine'       => '#7B1E3A',
	'Olive'      => '#5B6B3A',
	'Cream'      => '#EFE6D2',
	'Navy'       => '#1F2B4D',
	'Terracotta' => '#C65A2E',
);
foreach ( $sizes as $i => $size ) {
	$t = $term_id( 'pa_size', $size );
	update_term_meta( $t->term_id, 'order', $i );
}
foreach ( $colours as $name => $hex ) {
	$term_id( 'pa_colour', $name, $hex );
}

$cat = function ( $slug ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	return $t ? $t->term_id : 0;
};

// name, image, categories, regular, sale, colours, days old, featured, sold-out combos ("Colour|Size"), whole product sold out.
$products = array(
	array( 'Adire Wrap Dress', 'wrap-dress', array( 'women' ), 45000, 38000, array( 'Indigo', 'Black' ), 3, true, array( 'Indigo|S', 'Black|XL' ), false ),
	array( 'Agbada Three-Piece', 'agbada', array( 'men' ), 120000, 0, array( 'Cream', 'Navy' ), 5, true, array(), false ),
	array( 'Ankara Two-Piece Set', 'two-piece', array( 'women' ), 52000, 44000, array( 'Terracotta' ), 10, false, array( 'Terracotta|M' ), false ),
	array( 'Linen Kaftan', 'kaftan', array( 'men' ), 60000, 0, array( 'Olive', 'Cream' ), 45, true, array(), false ),
	array( 'Ada Bubu Gown', 'bubu', array( 'women' ), 40000, 0, array( 'Wine', 'Gold' ), 60, false, array( 'Gold|S', 'Gold|M', 'Gold|L', 'Gold|XL' ), false ),
	array( 'Occasion Gown', 'occasion-gown', array( 'women' ), 150000, 127500, array( 'Gold' ), 2, true, array(), false ),
	array( 'Senator Set', 'senator-set', array( 'men' ), 75000, 0, array( 'Navy', 'Black' ), 20, false, array(), false ),
	array( 'Adire Wide-Leg Pants', 'adire-pants', array( 'women' ), 35000, 0, array( 'Indigo' ), 90, false, array(), true ),
);

$size_terms = array_map( function ( $s ) { return get_term_by( 'name', $s, 'pa_size' ); }, $sizes );

foreach ( $products as $i => $row ) {
	list( $name, $img, $cats, $regular, $sale, $product_colours, $days, $featured, $soldout, $all_out ) = $row;
	$colour_terms = array_map( function ( $c ) { return get_term_by( 'name', $c, 'pa_colour' ); }, $product_colours );

	$product = new WC_Product_Variable();
	$product->set_name( '[SAMPLE] ' . $name );
	$product->set_status( 'publish' );
	$product->set_description( '[SAMPLE] Demo product for layout testing. Replace with the client’s product description. [[CONFIRM: product description]]' );
	$product->set_short_description( '[SAMPLE] Demo product. Replace with the client’s short description.' );
	$product->set_category_ids( array_filter( array_merge( array_map( $cat, $cats ), array( $cat( 'sample' ) ) ) ) );
	$product->set_featured( $featured );
	$product->set_image_id( $image( $img ) );
	if ( 'wrap-dress' === $img ) {
		$product->set_gallery_image_ids( array_filter( array( $image( 'adire-pants' ), $image( 'two-piece' ), $image( 'bubu' ) ) ) );
	}
	$product->set_date_created( time() - $days * DAY_IN_SECONDS - $i * 60 );

	$attrs = array();
	foreach ( array( 'pa_colour' => $colour_terms, 'pa_size' => $size_terms ) as $tax => $terms ) {
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
		foreach ( $size_terms as $size ) {
			$v = new WC_Product_Variation();
			$v->set_parent_id( $parent_id );
			$v->set_attributes( array( 'pa_colour' => $colour->slug, 'pa_size' => $size->slug ) );
			$v->set_regular_price( (string) $regular );
			if ( $sale ) {
				$v->set_sale_price( (string) $sale );
			}
			$v->set_manage_stock( true );
			$out = $all_out || in_array( $colour->name . '|' . $size->name, $soldout, true );
			$v->set_stock_quantity( $out ? 0 : ( 'M' === $size->name ? 3 : 8 ) );
			$v->set_status( 'publish' );
			$v->save();
		}
	}
	WC_Product_Variable::sync( $parent_id );
	wc_delete_product_transients( $parent_id );
}

// Category images for the homepage cards.
foreach ( array( 'women' => 'wrap-dress', 'men' => 'agbada' ) as $slug => $img ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $t && $image( $img ) ) {
		update_term_meta( $t->term_id, 'thumbnail_id', $image( $img ) );
	}
}

echo count( $products ) . " sample products seeded\n";

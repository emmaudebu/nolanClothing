<?php
/**
 * Collections page: every top-level product category shown as an editorial row (photo,
 * name, description, piece count, subcategories, "Shop …"), the destination of the
 * "Explore Our Collection" links. Categories, their order, tile colour, subtitle and photo
 * are edited under Products → Categories, so the page needs no editing itself.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Create the Collections page once, on the Collections template.
 */
function nolasharon_collections_page() {
	if ( get_option( 'nolasharon_collections_page_v1' ) && get_page_by_path( 'collections' ) ) {
		return;
	}
	$page = get_page_by_path( 'collections' );
	$id   = $page ? $page->ID : wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Our Collections', 'nolasharon' ),
			'post_name'   => 'collections',
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', 'page-templates/collections.php' );
		update_option( 'nolasharon_collections_page_v1', 1 );
	}
}
add_action( 'after_switch_theme', 'nolasharon_collections_page' );
add_action( 'admin_init', 'nolasharon_collections_page' );

/**
 * URL of the Collections page (the shop until the page exists).
 *
 * @return string
 */
function nolasharon_collections_url() {
	$page = get_page_by_path( 'collections' );
	return $page && 'publish' === $page->post_status ? get_permalink( $page ) : nolasharon_shop_url();
}

/**
 * Collections page styles.
 */
function nolasharon_collections_assets() {
	if ( is_page_template( 'page-templates/collections.php' ) ) {
		wp_enqueue_style( 'nolasharon-content', get_template_directory_uri() . '/assets/css/content.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'nolasharon_collections_assets', 20 );

/**
 * Everything a collection row shows: the category, its tone, subtitle or description,
 * piece count, child categories, the main photo (category image, else its newest product)
 * and a second photo from its newest product.
 *
 * @param WP_Term $term  Product category.
 * @param int     $index Position, for the automatic tone.
 * @return array
 */
function nolasharon_collection_data( $term, $index ) {
	$tones   = array( 'brand', 'dark', 'sand', 'cream' );
	$tone    = get_term_meta( $term->term_id, 'ns_tile_tone', true );
	$tone    = isset( nolasharon_tile_tones()[ $tone ] ) ? $tone : $tones[ $index % 4 ];
	$summary = get_term_meta( $term->term_id, 'ns_tile_subtitle', true );
	if ( ! $summary && $term->description ) {
		$summary = wp_trim_words( wp_strip_all_tags( $term->description ), 28 );
	}

	$photos = array();
	$thumb  = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $thumb ) {
		$photos[] = $thumb;
	}
	$latest = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 3,
			'orderby'  => 'date',
			'order'    => 'DESC',
			'category' => array( $term->slug ),
			'return'   => 'objects',
		)
	);
	foreach ( $latest as $product ) {
		$id = (int) $product->get_image_id();
		if ( $id && ! in_array( $id, $photos, true ) ) {
			$photos[] = $id;
		}
	}

	$children = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term->term_id,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
		)
	);

	return array(
		'term'     => $term,
		'tone'     => $tone,
		'summary'  => $summary,
		'count'    => (int) $term->count,
		'children' => is_wp_error( $children ) ? array() : $children,
		'photo'    => isset( $photos[0] ) ? $photos[0] : 0,
		'photo_2'  => isset( $photos[1] ) ? $photos[1] : 0,
		'url'      => get_term_link( $term ),
	);
}

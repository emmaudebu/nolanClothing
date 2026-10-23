<?php
/**
 * Lightweight shop filters (brief §5.1): category, price range, size, colour and availability,
 * plus sorting. Built on WooCommerce's own query vars, no filter plugin.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Attribute filters offered on the shop: query var => taxonomy.
 *
 * @return array
 */
function nolasharon_attribute_filters() {
	return array(
		'filter_colour' => 'pa_colour',
		'filter_size'   => 'pa_size',
	);
}

/**
 * Checkbox groups submit arrays (filter_size[]=s&filter_size[]=m); WooCommerce expects
 * "s,m". Convert before WooCommerce reads them, and match any chosen value (OR).
 */
function nolasharon_normalize_filter_query() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only listing filters.
	foreach ( array_keys( nolasharon_attribute_filters() ) as $key ) {
		if ( isset( $_GET[ $key ] ) && is_array( $_GET[ $key ] ) ) {
			$_GET[ $key ] = implode( ',', array_map( 'sanitize_title', wp_unslash( $_GET[ $key ] ) ) );
		}
		if ( ! empty( $_GET[ $key ] ) ) {
			$_GET[ 'query_type_' . substr( $key, 7 ) ] = 'or';
		}
	}
	// phpcs:enable
}
add_action( 'init', 'nolasharon_normalize_filter_query', 1 );

/**
 * "In stock only" filter.
 *
 * @param WP_Query $query Product query.
 */
function nolasharon_instock_filter( $query ) {
	if ( empty( $_GET['ns_instock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$meta   = (array) $query->get( 'meta_query' );
	$meta[] = array(
		'key'   => '_stock_status',
		'value' => 'instock',
	);
	$query->set( 'meta_query', $meta );
}
add_action( 'woocommerce_product_query', 'nolasharon_instock_filter' );

/**
 * Sort options (brief §5.1). Popularity is left out until sales make it meaningful.
 *
 * @return array orderby => label
 */
function nolasharon_sort_options() {
	return array(
		'date'       => __( 'Latest', 'nolasharon' ),
		'price'      => __( 'Price: low to high', 'nolasharon' ),
		'price-desc' => __( 'Price: high to low', 'nolasharon' ),
	);
}

/**
 * The current listing's URL without filters or paging.
 *
 * @return string
 */
function nolasharon_filter_base_url() {
	if ( is_search() ) {
		return add_query_arg(
			array(
				's'         => get_search_query(),
				'post_type' => 'product',
			),
			home_url( '/' )
		);
	}
	if ( is_product_taxonomy() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? nolasharon_shop_url() : $link;
	}
	return nolasharon_shop_url();
}

/**
 * Current filter values from the URL.
 *
 * @return array
 */
function nolasharon_current_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$current = array();
	foreach ( array_keys( nolasharon_attribute_filters() ) as $key ) {
		$current[ $key ] = isset( $_GET[ $key ] ) ? array_filter( array_map( 'sanitize_title', explode( ',', wc_clean( wp_unslash( $_GET[ $key ] ) ) ) ) ) : array();
	}
	$current['min_price']  = isset( $_GET['min_price'] ) && '' !== $_GET['min_price'] ? absint( $_GET['min_price'] ) : '';
	$current['max_price']  = isset( $_GET['max_price'] ) && '' !== $_GET['max_price'] ? absint( $_GET['max_price'] ) : '';
	$current['ns_instock'] = ! empty( $_GET['ns_instock'] );
	$current['orderby']    = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : '';
	// phpcs:enable
	return $current;
}

/**
 * Build a listing URL from filter values.
 *
 * @param array $filters Filter values as from nolasharon_current_filters().
 * @return string
 */
function nolasharon_filter_url( $filters ) {
	$args = array();
	foreach ( $filters as $key => $value ) {
		if ( is_array( $value ) ) {
			if ( $value ) {
				$args[ $key ] = implode( ',', $value );
			}
		} elseif ( true === $value ) {
			$args[ $key ] = 1;
		} elseif ( '' !== $value && false !== $value ) {
			$args[ $key ] = $value;
		}
	}
	return add_query_arg( $args, nolasharon_filter_base_url() );
}

/**
 * Active filters as removable chips.
 *
 * @return array[] { label, remove }
 */
function nolasharon_active_filters() {
	$current = nolasharon_current_filters();
	$chips   = array();
	foreach ( nolasharon_attribute_filters() as $key => $taxonomy ) {
		foreach ( $current[ $key ] as $slug ) {
			$term = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $slug, $taxonomy ) : false;
			if ( ! $term ) {
				continue;
			}
			$without         = $current;
			$without[ $key ] = array_values( array_diff( $current[ $key ], array( $slug ) ) );
			$chips[]         = array(
				'label'  => wc_attribute_label( $taxonomy ) . ': ' . $term->name,
				'remove' => nolasharon_filter_url( $without ),
			);
		}
	}
	if ( '' !== $current['min_price'] || '' !== $current['max_price'] ) {
		$without              = $current;
		$without['min_price'] = '';
		$without['max_price'] = '';
		$chips[]              = array(
			'label'  => sprintf(
				/* translators: 1: minimum price, 2: maximum price. */
				__( 'Price: %1$s – %2$s', 'nolasharon' ),
				'' !== $current['min_price'] ? wp_strip_all_tags( wc_price( $current['min_price'], array( 'decimals' => 0 ) ) ) : __( 'any', 'nolasharon' ),
				'' !== $current['max_price'] ? wp_strip_all_tags( wc_price( $current['max_price'], array( 'decimals' => 0 ) ) ) : __( 'any', 'nolasharon' )
			),
			'remove' => nolasharon_filter_url( $without ),
		);
	}
	if ( $current['ns_instock'] ) {
		$without               = $current;
		$without['ns_instock'] = false;
		$chips[]               = array(
			'label'  => __( 'In stock only', 'nolasharon' ),
			'remove' => nolasharon_filter_url( $without ),
		);
	}
	return $chips;
}

/**
 * Hidden inputs carrying the current filters, so one form does not drop another's values.
 *
 * @param string[] $exclude Keys the form sets itself.
 */
function nolasharon_filter_hidden_inputs( $exclude = array() ) {
	$current = nolasharon_current_filters();
	if ( is_search() ) {
		$current['s']         = get_search_query();
		$current['post_type'] = 'product';
	}
	foreach ( $current as $key => $value ) {
		if ( in_array( $key, $exclude, true ) || '' === $value || false === $value || array() === $value ) {
			continue;
		}
		printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( is_array( $value ) ? implode( ',', $value ) : ( true === $value ? '1' : $value ) ) );
	}
}

/**
 * Attribute terms that have products, for a filter group.
 *
 * @param string $taxonomy Attribute taxonomy.
 * @return WP_Term[]
 */
function nolasharon_filter_terms( $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

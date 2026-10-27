<?php
/**
 * Shop filters (brief §5.1, SHOP-PRODUCT.md §2.3): category, price presets, size, colour and
 * "in stock only", plus sorting. Every control is a plain query arg, so filtered pages are
 * shareable URLs and work without JavaScript; shop.js applies them in place. No filter plugin.
 *
 * Query args: product_cat (several, comma-separated), price=min-max (a preset band),
 * filter_size / filter_colour (WooCommerce layered nav, OR), stock=instock, orderby.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Listing styles and script on the shop, category and product search pages.
 */
function nolasharon_shop_assets() {
	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-shop', get_template_directory_uri() . '/assets/css/shop.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-shop', get_template_directory_uri() . '/assets/js/shop.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'nolasharon_shop_assets', 20 );

/**
 * Attribute filters offered on the shop: query var => taxonomy.
 *
 * @return array
 */
function nolasharon_attribute_filters() {
	return array(
		'filter_size'      => 'pa_size',
		'filter_shoe-size' => 'pa_shoe-size',
		'filter_colour'    => 'pa_colour',
	);
}

/**
 * Price presets from the theme setting (one "min-max" band per line; "-50000" or "50000-"
 * leave an end open).
 *
 * @return array key => array( min, max, label )
 */
function nolasharon_price_bands() {
	$bands = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) nolasharon_setting( 'nolasharon_price_ranges' ) ) as $line ) {
		if ( ! preg_match( '/^\s*(\d*)\s*-\s*(\d*)\s*$/', $line, $m ) || ( '' === $m[1] && '' === $m[2] ) ) {
			continue;
		}
		$min   = '' === $m[1] ? 0 : (int) $m[1];
		$max   = '' === $m[2] ? 0 : (int) $m[2];
		$price = function ( $amount ) {
			return wp_strip_all_tags( wc_price( $amount, array( 'decimals' => 0 ) ) );
		};
		if ( ! $min ) {
			/* translators: %s: price. */
			$label = sprintf( __( 'Under %s', 'nolasharon' ), $price( $max ) );
		} elseif ( ! $max ) {
			/* translators: %s: price. */
			$label = sprintf( __( 'Over %s', 'nolasharon' ), $price( $min ) );
		} else {
			$label = $price( $min ) . '–' . $price( $max );
		}
		$bands[ $min . '-' . ( $max ? $max : '' ) ] = array( $min, $max, $label );
	}
	return $bands;
}

/**
 * Turn the form's values into what WordPress and WooCommerce read: checkbox arrays into
 * comma lists (OR), and a price band into min_price / max_price.
 */
function nolasharon_normalize_filter_query() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only listing filters.
	foreach ( array_merge( array_keys( nolasharon_attribute_filters() ), array( 'product_cat' ) ) as $key ) {
		if ( isset( $_GET[ $key ] ) && is_array( $_GET[ $key ] ) ) {
			// Only plain values: nested lists ("?filter_size[][]=s") are dropped.
			$_GET[ $key ] = implode( ',', array_filter( array_map( 'sanitize_title', array_filter( wp_unslash( $_GET[ $key ] ), 'is_scalar' ) ) ) );
			if ( '' === $_GET[ $key ] ) {
				unset( $_GET[ $key ] );
			}
		}
	}
	foreach ( array_keys( nolasharon_attribute_filters() ) as $key ) {
		if ( ! empty( $_GET[ $key ] ) ) {
			$_GET[ 'query_type_' . substr( $key, 7 ) ] = 'or';
		}
	}
	if ( ! empty( $_GET['product_cat'] ) ) {
		$_REQUEST['product_cat'] = $_GET['product_cat']; // WordPress reads query vars from the request.
	}
	if ( preg_match( '/^(\d*)-(\d*)$/', nolasharon_request_text( $_GET, 'price' ), $m ) && '-' !== $m[0] ) {
		if ( '' !== $m[1] && (int) $m[1] > 0 ) {
			$_GET['min_price'] = (int) $m[1];
		}
		if ( '' !== $m[2] ) {
			$_GET['max_price'] = (int) $m[2];
		}
	}
	// phpcs:enable
}
add_action( 'init', 'nolasharon_normalize_filter_query', 1 );

/**
 * "In stock only".
 *
 * @param WP_Query $query Product query.
 */
function nolasharon_instock_filter( $query ) {
	if ( empty( $_GET['stock'] ) || 'instock' !== $_GET['stock'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
 * The listing's own URL without filters or paging: the category, the search, or the shop.
 * With several categories chosen (?product_cat=a,b) it is the shop.
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
	if ( is_product_taxonomy() && ! nolasharon_query_cats() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? nolasharon_shop_url() : $link;
	}
	return nolasharon_shop_url();
}

/**
 * Categories chosen with the filter (?product_cat=), as opposed to a category page's own URL.
 *
 * @return string[] Slugs.
 */
function nolasharon_query_cats() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return isset( $_GET['product_cat'] ) && is_string( $_GET['product_cat'] ) ? array_values( array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET['product_cat'] ) ) ) ) ) : array();
}

/**
 * Categories that count as selected: the filter's, or the category page being viewed.
 *
 * @return string[] Slugs.
 */
function nolasharon_selected_cats() {
	$cats = nolasharon_query_cats();
	if ( ! $cats && is_tax( 'product_cat' ) ) {
		$cats = array( get_queried_object()->slug );
	}
	return $cats;
}

/**
 * Current filter values from the URL.
 *
 * @return array
 */
function nolasharon_current_filters() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$current = array( 'product_cat' => nolasharon_query_cats() );
	foreach ( array_keys( nolasharon_attribute_filters() ) as $key ) {
		$current[ $key ] = isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? array_values( array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_GET[ $key ] ) ) ) ) ) : array();
	}
	$price            = nolasharon_request_text( $_GET, 'price' );
	$current['price'] = isset( nolasharon_price_bands()[ $price ] ) ? $price : '';
	$current['stock'] = isset( $_GET['stock'] ) && 'instock' === $_GET['stock'] ? 'instock' : '';
	$orderby          = nolasharon_request_text( $_GET, 'orderby' );
	$current['orderby'] = isset( nolasharon_sort_options()[ $orderby ] ) ? $orderby : '';
	// phpcs:enable
	return $current;
}

/**
 * Whether any filter or sort is applied (filtered URLs are noindex).
 *
 * @return bool
 */
function nolasharon_has_filters() {
	return (bool) array_filter( nolasharon_current_filters() );
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
		} elseif ( '' !== $value ) {
			$args[ $key ] = $value;
		}
	}
	$base = nolasharon_filter_base_url();
	// One filter category left: use that category's own page.
	if ( isset( $args['product_cat'] ) && 1 === count( $filters['product_cat'] ) && ! is_search() ) {
		$term = get_term_by( 'slug', $filters['product_cat'][0], 'product_cat' );
		if ( $term ) {
			$base = get_term_link( $term );
			unset( $args['product_cat'] );
		}
	}
	return add_query_arg( $args, $base );
}

/**
 * Active filters as removable chips (categories chosen with the filter, price, sizes,
 * colours, stock).
 *
 * @return array[] { label, remove }
 */
function nolasharon_active_filters() {
	$current = nolasharon_current_filters();
	$chips   = array();
	if ( count( $current['product_cat'] ) > 1 ) {
		foreach ( $current['product_cat'] as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term ) {
				$without                = $current;
				$without['product_cat'] = array_values( array_diff( $current['product_cat'], array( $slug ) ) );
				$chips[]                = array(
					'label'  => $term->name,
					'remove' => nolasharon_filter_url( $without ),
				);
			}
		}
	}
	if ( $current['price'] ) {
		$without          = $current;
		$without['price'] = '';
		$chips[]          = array(
			'label'  => nolasharon_price_bands()[ $current['price'] ][2],
			'remove' => nolasharon_filter_url( $without ),
		);
	}
	foreach ( nolasharon_attribute_filters() as $key => $taxonomy ) {
		foreach ( $current[ $key ] as $slug ) {
			$term = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $slug, $taxonomy ) : false;
			if ( ! $term ) {
				continue;
			}
			$without         = $current;
			$without[ $key ] = array_values( array_diff( $current[ $key ], array( $slug ) ) );
			$chips[]         = array(
				'label'  => ( in_array( $taxonomy, nolasharon_size_taxonomies(), true ) ? __( 'Size', 'nolasharon' ) . ' ' : '' ) . $term->name,
				'remove' => nolasharon_filter_url( $without ),
			);
		}
	}
	if ( $current['stock'] ) {
		$without          = $current;
		$without['stock'] = '';
		$chips[]          = array(
			'label'  => __( 'In stock only', 'nolasharon' ),
			'remove' => nolasharon_filter_url( $without ),
		);
	}
	return $chips;
}

/**
 * Hidden inputs carrying values a form doesn't set itself (e.g. the sort form keeps the filters).
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
		if ( in_array( $key, $exclude, true ) || '' === $value || array() === $value ) {
			continue;
		}
		printf( '<input type="hidden" name="%1$s" value="%2$s">', esc_attr( $key ), esc_attr( is_array( $value ) ? implode( ',', $value ) : $value ) );
	}
}

/**
 * Attribute terms for a filter group, each with how many of the current results it would
 * match (WooCommerce's layered-nav counts). Terms with no matches are left out, unless chosen.
 *
 * @param string $taxonomy Attribute taxonomy.
 * @param string $key      Query var, e.g. filter_size.
 * @return array[] { term, count }
 */
function nolasharon_filter_terms( $taxonomy, $key ) {
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
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	$counts = array();
	if ( class_exists( '\Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer' ) && WC()->query && WC_Query::get_main_query() ) {
		$counts = wc_get_container()->get( \Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer::class )
			->get_filtered_term_product_counts( wp_list_pluck( $terms, 'term_id' ), $taxonomy, 'or' );
	}
	$chosen = nolasharon_current_filters()[ $key ];
	$out    = array();
	foreach ( $terms as $term ) {
		$count = isset( $counts[ $term->term_id ] ) ? (int) $counts[ $term->term_id ] : 0;
		if ( $count || in_array( $term->slug, $chosen, true ) ) {
			$out[] = array(
				'term'  => $term,
				'count' => $count,
			);
		}
	}
	return $out;
}

/**
 * Filtered and sorted listings are noindex; plain shop and category pages stay indexable.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function nolasharon_noindex_filtered( $robots ) {
	if ( ( is_shop() || is_product_taxonomy() ) && nolasharon_has_filters() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['max-image-preview'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'nolasharon_noindex_filtered', 20 );

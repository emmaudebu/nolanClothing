<?php
/**
 * Homepage hero slideshow: the pictures beside the headline come from the newest, featured or
 * hand-picked products, or from uploaded images, and play with a choice of transitions.
 * Two designs share it: "editorial" (light background, the picture filling the right side, a trust
 * row and a product card) and "classic" (brown banner, framed picture).
 * Everything is set in Customizer → NolaSharon Settings → Homepage hero.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Transition effects (key => label). "random" is added in the Customizer.
 *
 * @return array
 */
function nolasharon_hero_effects() {
	return array(
		'fade'     => __( 'Fade', 'nolasharon' ),
		'slide'    => __( 'Slide sideways', 'nolasharon' ),
		'slide-up' => __( 'Slide up', 'nolasharon' ),
		'zoom-in'  => __( 'Zoom in', 'nolasharon' ),
		'zoom-out' => __( 'Zoom out', 'nolasharon' ),
		'kenburns' => __( 'Fade with a slow zoom', 'nolasharon' ),
		'flip'     => __( 'Card flip', 'nolasharon' ),
		'cube'     => __( 'Cube turn', 'nolasharon' ),
		'blur'     => __( 'Blur', 'nolasharon' ),
		'wipe'     => __( 'Wipe', 'nolasharon' ),
		'split'    => __( 'Split open', 'nolasharon' ),
		'circle'   => __( 'Circle reveal', 'nolasharon' ),
	);
}

/**
 * Customizer active callback: show a control only while every listed setting has one of its values.
 *
 * @param array $conditions Setting ID => allowed values.
 * @return callable
 */
function nolasharon_hero_when( $conditions ) {
	return function () use ( $conditions ) {
		foreach ( $conditions as $id => $values ) {
			if ( ! in_array( nolasharon_setting( $id ), $values, true ) ) {
				return false;
			}
		}
		return true;
	};
}

/**
 * The slides to play: each an attachment ID plus, for products, the link, name and price.
 * Falls back to the campaign image; an empty result means the placeholder illustration.
 *
 * @return array[]
 */
function nolasharon_hero_slides() {
	$source = nolasharon_setting( 'nolasharon_hero_source' );
	$slides = array();

	if ( 'gallery' === $source ) {
		foreach ( explode( ',', (string) nolasharon_setting( 'nolasharon_hero_gallery' ) ) as $id ) {
			if ( wp_attachment_is_image( (int) $id ) ) {
				$slides[] = array( 'image' => (int) $id );
			}
		}
	} elseif ( in_array( $source, array( 'recent', 'featured', 'selected' ), true ) && function_exists( 'wc_get_products' ) ) {
		$args = array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => max( 1, min( 12, (int) nolasharon_setting( 'nolasharon_hero_count' ) ) ),
		);
		if ( 'selected' === $source ) {
			$ids             = array_filter( array_map( 'absint', explode( ',', (string) nolasharon_setting( 'nolasharon_hero_products' ) ) ) );
			$args['include'] = $ids ? $ids : array( 0 );
			$args['limit']   = count( $args['include'] );
		} else {
			$args['stock_status'] = 'instock';
			$args['orderby']      = 'date';
			$args['order']        = 'DESC';
			if ( 'featured' === $source ) {
				$args['featured'] = true;
			}
		}
		$products = wc_get_products( $args );
		if ( 'selected' === $source ) {
			// Play in the order they were ticked.
			usort(
				$products,
				function ( $a, $b ) use ( $ids ) {
					return array_search( $a->get_id(), $ids, true ) - array_search( $b->get_id(), $ids, true );
				}
			);
		}
		foreach ( $products as $product ) {
			if ( $product->get_image_id() ) {
				$slides[] = array(
					'image'   => (int) $product->get_image_id(),
					'url'     => $product->get_permalink(),
					'title'   => $product->get_name(),
					'price'   => $product->get_price_html(),
					'product' => $product,
				);
			}
		}
	}

	if ( ! $slides && nolasharon_setting( 'nolasharon_hero_image' ) ) {
		$slides[] = array( 'image' => (int) nolasharon_setting( 'nolasharon_hero_image' ) );
	}
	return $slides;
}

/**
 * The editorial design's card product when a slide is not a product, or for "Always the same
 * product": the one chosen in the Customizer, else the newest featured product, else the newest.
 *
 * @return WC_Product|null
 */
function nolasharon_hero_spotlight() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return null;
	}
	$product = wc_get_product( (int) nolasharon_setting( 'nolasharon_hero_card_product' ) );
	if ( $product && 'publish' === $product->get_status() && $product->is_visible() && $product->get_image_id() ) {
		return $product;
	}
	foreach ( array( true, false ) as $featured ) {
		$args = array(
			'status'       => 'publish',
			'visibility'   => 'catalog',
			'stock_status' => 'instock',
			'limit'        => 5,
			'orderby'      => 'date',
			'order'        => 'DESC',
		);
		if ( $featured ) {
			$args['featured'] = true;
		}
		foreach ( wc_get_products( $args ) as $product ) {
			if ( $product->get_image_id() ) {
				return $product;
			}
		}
	}
	return null;
}

/**
 * Editorial product card contents, one per slide when the card follows the slides, otherwise
 * one. A null entry hides the card on that slide.
 *
 * @param array[] $slides From nolasharon_hero_slides().
 * @return array Products (or null), keyed like the slides.
 */
function nolasharon_hero_cards( $slides ) {
	$mode = nolasharon_setting( 'nolasharon_hero_card' );
	if ( 'none' === $mode ) {
		return array();
	}
	$spotlight = nolasharon_hero_spotlight();
	if ( 'product' === $mode || count( $slides ) < 2 ) {
		$product = 'slide' === $mode && ! empty( $slides[0]['product'] ) ? $slides[0]['product'] : $spotlight;
		return $product ? array( $product ) : array();
	}
	$cards = array();
	foreach ( $slides as $slide ) {
		$cards[] = ! empty( $slide['product'] ) ? $slide['product'] : $spotlight;
	}
	return array_filter( $cards ) ? $cards : array();
}

/**
 * Picture size and position from the Customizer, as CSS variables.
 */
function nolasharon_hero_css() {
	if ( ! is_front_page() ) {
		return;
	}
	$flex = array(
		'top'    => 'flex-start',
		'left'   => 'flex-start',
		'center' => 'center',
		'bottom' => 'flex-end',
		'right'  => 'flex-end',
	);
	$vars = array(
		'--hs-h'      => (int) nolasharon_setting( 'nolasharon_hero_media_h' ) . 'px',
		'--hs-w'      => (int) nolasharon_setting( 'nolasharon_hero_media_w' ) . 'px',
		'--hs-h-m'    => (int) nolasharon_setting( 'nolasharon_hero_media_h_m' ) . 'px',
		'--hs-radius' => (int) nolasharon_setting( 'nolasharon_hero_radius' ) . 'px',
		'--hs-speed'  => (int) nolasharon_setting( 'nolasharon_hero_speed' ) . 'ms',
		'--hs-fit'    => 'contain' === nolasharon_setting( 'nolasharon_hero_fit' ) ? 'contain' : 'cover',
		'--hs-valign' => $flex[ nolasharon_setting( 'nolasharon_hero_valign' ) ] ?? 'center',
		'--hs-halign' => $flex[ nolasharon_setting( 'nolasharon_hero_halign' ) ] ?? 'center',
		'--he-bg'     => sanitize_hex_color( nolasharon_setting( 'nolasharon_hero_bg' ) ) ? nolasharon_setting( 'nolasharon_hero_bg' ) : '#f4ede5',
		'--he-h'      => (int) nolasharon_setting( 'nolasharon_hero_ed_h' ) . 'px',
		'--he-w'      => (int) nolasharon_setting( 'nolasharon_hero_ed_w' ) . '%',
	);
	$css  = '';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	echo '<style id="nolasharon-hero-vars">:root{' . esc_html( $css ) . "}</style>\n";
}
add_action( 'wp_head', 'nolasharon_hero_css', 20 );

/**
 * Slider script, on the homepage only.
 */
function nolasharon_hero_assets() {
	if ( is_front_page() ) {
		wp_enqueue_script( 'nolasharon-hero', get_template_directory_uri() . '/assets/js/hero-slider.js', array(), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'nolasharon_hero_assets', 20 );

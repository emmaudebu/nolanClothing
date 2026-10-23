<?php
/**
 * Product gallery (SHOP-PRODUCT.md §3.2). Desktop: vertical thumbnails beside the main image,
 * which cross-fades between photos and zooms under the cursor; phones: a swipe carousel with
 * dots. Either opens the full-screen lightbox (PhotoSwipe, product.js). Variation images
 * switch in when a colour or size is chosen. Without JavaScript the first photo shows and
 * the carousel still swipes.
 *
 * Args: product (WC_Product).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_product = $args['product'];
$nolasharon_images  = nolasharon_product_images( $nolasharon_product );
$nolasharon_total   = count( $nolasharon_images );
$nolasharon_badges  = nolasharon_product_badges( $nolasharon_product );
$nolasharon_items   = array_map(
	function ( $image ) {
		return array(
			'src'   => $image['full'],
			'w'     => $image['w'],
			'h'     => $image['h'],
			'title' => $image['alt'],
		);
	},
	$nolasharon_images
);
?>
<div class="pg ns-in" data-gallery data-items="<?php echo esc_attr( wp_json_encode( $nolasharon_items ) ); ?>" data-hint-zoom="<?php esc_attr_e( 'Hover to zoom', 'nolasharon' ); ?>">
	<?php if ( $nolasharon_total > 1 ) : ?>
		<div class="pg__thumbs" role="group" aria-label="<?php esc_attr_e( 'Product images', 'nolasharon' ); ?>">
			<?php foreach ( $nolasharon_images as $nolasharon_i => $nolasharon_image ) : ?>
				<button type="button" class="pg__thumb" data-show="<?php echo esc_attr( $nolasharon_i ); ?>" aria-pressed="<?php echo 0 === $nolasharon_i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: image number, 2: total images. */ __( 'Show image %1$d of %2$d', 'nolasharon' ), $nolasharon_i + 1, $nolasharon_total ) ); ?>">
					<img src="<?php echo esc_url( $nolasharon_image['thumb'] ); ?>" alt="" width="78" height="96" loading="lazy" decoding="async">
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="pg__main" data-zoom>
		<?php foreach ( $nolasharon_images as $nolasharon_i => $nolasharon_image ) : ?>
			<img class="pg__slide<?php echo 0 === $nolasharon_i ? ' is-active' : ''; ?>" src="<?php echo esc_url( $nolasharon_image['src'] ); ?>"<?php echo $nolasharon_image['srcset'] ? ' srcset="' . esc_attr( $nolasharon_image['srcset'] ) . '" sizes="(min-width: 861px) 52vw, 100vw"' : ''; ?> alt="<?php echo esc_attr( $nolasharon_image['alt'] ); ?>" width="800" height="1000" data-index="<?php echo esc_attr( $nolasharon_i ); ?>"<?php echo 0 === $nolasharon_i ? ' fetchpriority="high"' : ' loading="lazy" aria-hidden="true"'; ?> decoding="async">
		<?php endforeach; ?>
		<button type="button" class="pg__open" data-open-lightbox aria-label="<?php esc_attr_e( 'Open the image full screen', 'nolasharon' ); ?>"></button>
		<span class="pg__hint" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M11 8v6M8 11h6"/></svg><?php esc_html_e( 'Hover to zoom', 'nolasharon' ); ?></span>
	</div>

	<div class="pg__track" data-track tabindex="0" role="group" aria-label="<?php esc_attr_e( 'Product images, swipe to see more', 'nolasharon' ); ?>">
		<?php foreach ( $nolasharon_images as $nolasharon_i => $nolasharon_image ) : ?>
			<button type="button" class="pg__cell" data-open-lightbox="<?php echo esc_attr( $nolasharon_i ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: image number, 2: total images. */ __( 'Image %1$d of %2$d, open full screen', 'nolasharon' ), $nolasharon_i + 1, $nolasharon_total ) ); ?>">
				<img src="<?php echo esc_url( $nolasharon_image['src'] ); ?>"<?php echo $nolasharon_image['srcset'] ? ' srcset="' . esc_attr( $nolasharon_image['srcset'] ) . '" sizes="100vw"' : ''; ?> alt="<?php echo esc_attr( $nolasharon_image['alt'] ); ?>" width="800" height="1000"<?php echo $nolasharon_i ? ' loading="lazy"' : ''; ?> decoding="async">
			</button>
		<?php endforeach; ?>
	</div>
	<?php if ( $nolasharon_total > 1 ) : ?>
		<div class="pg__dots" aria-hidden="true">
			<?php for ( $nolasharon_i = 0; $nolasharon_i < $nolasharon_total; $nolasharon_i++ ) : ?>
				<span class="pg__dot<?php echo 0 === $nolasharon_i ? ' is-active' : ''; ?>"></span>
			<?php endfor; ?>
		</div>
	<?php endif; ?>

	<?php if ( $nolasharon_badges ) : ?>
		<span class="pg__badges">
			<?php foreach ( $nolasharon_badges as $nolasharon_type => $nolasharon_label ) : ?>
				<span class="pbadge pbadge--<?php echo esc_attr( $nolasharon_type ); ?>"><?php echo esc_html( $nolasharon_label ); ?></span>
			<?php endforeach; ?>
		</span>
	<?php endif; ?>
</div>

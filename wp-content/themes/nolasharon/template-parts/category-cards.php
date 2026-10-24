<?php
/**
 * Homepage "Shop Our Collection" (STORE-PAGES.md §1, brief §4 D): up to four product
 * categories as a bento grid. Categories, order, tile colour and subtitle come from the
 * product category screens (Products → Categories). On phones the first tile is full width
 * and the rest scroll sideways.
 *
 * Args: categories (WP_Term[], from nolasharon_home_categories()).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_cats  = array_values( $args['categories'] );
$nolasharon_count = count( $nolasharon_cats );
if ( ! $nolasharon_count ) {
	return;
}

// Tile size by position for 1–4 categories: lg (two columns, two rows), wide (two columns), sm (one cell).
$nolasharon_layouts = array(
	1 => array( 'banner' ),
	2 => array( 'lg', 'lg' ),
	3 => array( 'lg', 'wide', 'wide' ),
	4 => array( 'lg', 'wide', 'sm', 'sm' ),
);
$nolasharon_sizes   = $nolasharon_layouts[ $nolasharon_count ];
$nolasharon_tones   = array( 'brand', 'dark', 'sand', 'cream' );
$nolasharon_tilts   = array( -4, 3, -5, 4 );
$nolasharon_shop    = nolasharon_shop_url();
?>
<section class="section ns-cols ns-cols--<?php echo esc_attr( $nolasharon_count ); ?>" aria-labelledby="collections-title">
	<div class="container">
		<div class="ns-cols__head">
			<div>
				<p class="ns-cols__eyebrow"><?php esc_html_e( 'Shop by category', 'nolasharon' ); ?></p>
				<h2 class="ns-cols__title" id="collections-title"><?php esc_html_e( 'Shop Our Collection', 'nolasharon' ); ?></h2>
			</div>
			<div class="ns-cols__aside">
				<?php // DRAFT: client to approve. ?>
				<p class="ns-cols__intro"><?php esc_html_e( 'Find your next favourite piece, from everyday essentials to standout looks for every occasion.', 'nolasharon' ); ?></p>
				<a class="ns-cols__link" href="<?php echo esc_url( $nolasharon_shop ); ?>"><?php esc_html_e( 'Explore Our Collection', 'nolasharon' ); ?> <span aria-hidden="true">→</span></a>
			</div>
		</div>

		<div class="ns-cols__grid">
			<?php
			foreach ( $nolasharon_cats as $nolasharon_i => $nolasharon_cat ) :
				$nolasharon_size     = $nolasharon_sizes[ $nolasharon_i ];
				$nolasharon_big      = 'sm' !== $nolasharon_size;
				$nolasharon_tone     = get_term_meta( $nolasharon_cat->term_id, 'ns_tile_tone', true );
				$nolasharon_tone     = isset( nolasharon_tile_tones()[ $nolasharon_tone ] ) ? $nolasharon_tone : $nolasharon_tones[ $nolasharon_i % 4 ];
				$nolasharon_subtitle = $nolasharon_big ? get_term_meta( $nolasharon_cat->term_id, 'ns_tile_subtitle', true ) : '';
				$nolasharon_thumb    = (int) get_term_meta( $nolasharon_cat->term_id, 'thumbnail_id', true );
				$nolasharon_num      = sprintf( '%02d', $nolasharon_i + 1 );
				$nolasharon_id       = 'ns-col-' . $nolasharon_cat->term_id;

				// The tiles after the first share a wrapper that becomes the sideways row on phones.
				if ( 1 === $nolasharon_i ) {
					echo '<div class="ns-cols__rest">';
				}
				?>
				<a class="ns-col ns-col--<?php echo esc_attr( $nolasharon_size . ' ns-col--' . $nolasharon_tone ); ?>" href="<?php echo esc_url( get_term_link( $nolasharon_cat ) ); ?>" aria-labelledby="<?php echo esc_attr( $nolasharon_id ); ?>"<?php echo $nolasharon_subtitle ? ' aria-describedby="' . esc_attr( $nolasharon_id ) . '-sub"' : ''; ?> style="--tilt: <?php echo esc_attr( $nolasharon_tilts[ $nolasharon_i % 4 ] ); ?>deg">
					<span class="ns-col__ring" aria-hidden="true"></span>
					<span class="ns-col__text">
						<span class="ns-col__index" aria-hidden="true"><?php echo esc_html( $nolasharon_num ); ?><?php if ( $nolasharon_big ) : ?><span class="ns-col__index-long"> — <?php esc_html_e( 'Collection', 'nolasharon' ); ?></span><?php endif; ?></span>
						<span class="ns-col__name" id="<?php echo esc_attr( $nolasharon_id ); ?>"><?php echo esc_html( $nolasharon_cat->name ); ?></span>
						<?php if ( $nolasharon_subtitle ) : ?>
							<span class="ns-col__sub" id="<?php echo esc_attr( $nolasharon_id ); ?>-sub"><?php echo esc_html( $nolasharon_subtitle ); ?></span>
						<?php endif; ?>
					</span>
					<?php if ( $nolasharon_thumb ) : ?>
						<span class="ns-col__photo">
							<?php
							echo wp_get_attachment_image(
								$nolasharon_thumb,
								'woocommerce_thumbnail',
								false,
								array(
									'alt'     => '',
									'loading' => 'lazy',
									'sizes'   => 'lg' === $nolasharon_size || 'banner' === $nolasharon_size ? '(min-width: 900px) 320px, 55vw' : '(min-width: 900px) 220px, 150px',
								)
							);
							?>
						</span>
					<?php endif; ?>
					<span class="ns-col__go" aria-hidden="true">
						<span class="ns-col__arrow"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
						<?php if ( $nolasharon_big ) : ?>
							<span class="ns-col__cta"><?php echo esc_html( sprintf( /* translators: %s: category name. */ __( 'Shop %s', 'nolasharon' ), $nolasharon_cat->name ) ); ?></span>
						<?php endif; ?>
					</span>
				</a>
				<?php
				if ( $nolasharon_i === $nolasharon_count - 1 && $nolasharon_i > 0 ) {
					echo '</div>';
				}
			endforeach;
			?>
		</div>

		<a class="ns-btn ns-btn--ghost ns-btn--block ns-cols__more" href="<?php echo esc_url( $nolasharon_shop ); ?>"><?php esc_html_e( 'Explore Our Collection', 'nolasharon' ); ?></a>
	</div>
</section>

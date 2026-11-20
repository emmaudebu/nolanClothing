<?php
/**
 * Homepage hero pictures (inc/hero.php): a slideshow when there is more than one slide, a single
 * picture otherwise, and the placeholder illustration when there is nothing to show. The editorial
 * design adds the product card (one per slide when it follows the slides).
 * assets/js/hero-slider.js runs the transitions; without it the first slide simply stays up.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_editorial = isset( $args['layout'] ) && 'editorial' === $args['layout'];
$nolasharon_slides    = nolasharon_hero_slides();
$nolasharon_count     = count( $nolasharon_slides );
$nolasharon_effect    = nolasharon_setting( 'nolasharon_hero_effect' );
$nolasharon_interval  = (int) nolasharon_setting( 'nolasharon_hero_interval' );
$nolasharon_cards     = $nolasharon_editorial ? nolasharon_hero_cards( $nolasharon_slides ) : array();
$nolasharon_classes   = array( 'hslider' );
// The editorial design shows the product card instead of captions on the picture.
foreach ( $nolasharon_editorial ? array( 'arrows', 'dots' ) : array( 'arrows', 'dots', 'captions' ) as $nolasharon_option ) {
	if ( nolasharon_setting( 'nolasharon_hero_' . $nolasharon_option ) ) {
		$nolasharon_classes[] = 'hslider--' . $nolasharon_option;
	}
}
if ( ! $nolasharon_slides ) {
	$nolasharon_classes[] = 'hslider--art';
}
?>
<div class="hero__media">
	<div class="<?php echo esc_attr( implode( ' ', $nolasharon_classes ) ); ?>"
		<?php if ( $nolasharon_count > 1 ) : ?>
			data-hslider
			data-effect="<?php echo esc_attr( $nolasharon_effect ); ?>"
			data-interval="<?php echo esc_attr( $nolasharon_interval ); ?>"
			role="region"
			aria-roledescription="<?php esc_attr_e( 'carousel', 'nolasharon' ); ?>"
			aria-label="<?php esc_attr_e( 'Featured pieces', 'nolasharon' ); ?>"
		<?php endif; ?>
		data-fx="<?php echo esc_attr( 'random' === $nolasharon_effect ? 'fade' : $nolasharon_effect ); ?>">
		<div class="hslider__track" id="hslider-track">
			<?php
			if ( ! $nolasharon_slides ) {
				printf( '<div class="hslider__slide is-active"><img src="%1$s" alt="%2$s" width="600" height="700" fetchpriority="high"></div>', esc_url( nolasharon_img( 'hero-figure' ) ), esc_attr__( 'Illustration of a woman in an indigo gown and gold head wrap', 'nolasharon' ) );
			}
			foreach ( $nolasharon_slides as $nolasharon_i => $nolasharon_slide ) :
				$nolasharon_title = $nolasharon_slide['title'] ?? '';
				$nolasharon_img   = wp_get_attachment_image(
					$nolasharon_slide['image'],
					'large',
					false,
					array_merge(
						array(
							'class' => 'hslider__img',
							'sizes' => '(max-width: 760px) 100vw, 600px',
						),
						$nolasharon_title ? array( 'alt' => $nolasharon_title ) : array(),
						0 === $nolasharon_i ? array(
							'fetchpriority' => 'high',
							'loading'       => 'eager',
						) : array( 'loading' => 'lazy' )
					)
				);
				?>
				<div class="hslider__slide<?php echo 0 === $nolasharon_i ? ' is-active' : ''; ?>"
					<?php if ( $nolasharon_count > 1 ) : ?>
						role="group" aria-roledescription="<?php esc_attr_e( 'slide', 'nolasharon' ); ?>"
						<?php /* translators: 1: slide number, 2: number of slides. */ ?>
						aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'nolasharon' ), $nolasharon_i + 1, $nolasharon_count ) ); ?>"
					<?php endif; ?>>
					<?php if ( ! empty( $nolasharon_slide['url'] ) ) : ?>
						<a class="hslider__link" href="<?php echo esc_url( $nolasharon_slide['url'] ); ?>">
							<?php echo $nolasharon_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?>
							<span class="hslider__cap">
								<span class="hslider__name"><?php echo esc_html( $nolasharon_title ); ?></span>
								<?php if ( $nolasharon_slide['price'] ) : ?>
									<span class="hslider__price"><?php echo wp_kses_post( $nolasharon_slide['price'] ); ?></span>
								<?php endif; ?>
							</span>
						</a>
					<?php else : ?>
						<?php echo $nolasharon_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core image markup. ?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $nolasharon_count > 1 ) : ?>
			<button type="button" class="hslider__arrow hslider__arrow--prev" data-hslider-prev aria-controls="hslider-track" aria-label="<?php esc_attr_e( 'Previous slide', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'prev', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<button type="button" class="hslider__arrow hslider__arrow--next" data-hslider-next aria-controls="hslider-track" aria-label="<?php esc_attr_e( 'Next slide', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'next', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			<div class="hslider__bar">
				<div class="hslider__dots" role="group" aria-label="<?php esc_attr_e( 'Choose a slide', 'nolasharon' ); ?>">
					<?php for ( $nolasharon_i = 0; $nolasharon_i < $nolasharon_count; $nolasharon_i++ ) : ?>
						<?php /* translators: %d: slide number. */ ?>
						<button type="button" class="hslider__dot" data-hslider-dot="<?php echo (int) $nolasharon_i; ?>" aria-controls="hslider-track" aria-label="<?php echo esc_attr( sprintf( __( 'Show slide %d', 'nolasharon' ), $nolasharon_i + 1 ) ); ?>"<?php echo 0 === $nolasharon_i ? ' aria-current="true"' : ''; ?>></button>
					<?php endfor; ?>
				</div>
				<button type="button" class="hslider__toggle" data-hslider-toggle
					data-label-pause="<?php esc_attr_e( 'Pause the slideshow', 'nolasharon' ); ?>"
					data-label-play="<?php esc_attr_e( 'Play the slideshow', 'nolasharon' ); ?>"
					aria-label="<?php esc_attr_e( 'Pause the slideshow', 'nolasharon' ); ?>">
					<span class="hslider__icon-pause"><?php echo nolasharon_icon( 'pause', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="hslider__icon-play"><?php echo nolasharon_icon( 'play', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $nolasharon_cards ) : ?>
		<div class="hcard<?php echo $nolasharon_cards[0] ? '' : ' is-empty'; ?>">
			<?php
			foreach ( $nolasharon_cards as $nolasharon_i => $nolasharon_product ) :
				$nolasharon_active = 0 === $nolasharon_i ? ' is-active' : '';
				if ( ! $nolasharon_product ) {
					printf( '<span class="hcard__item%s" data-hcard-item></span>', esc_attr( $nolasharon_active ) );
					continue;
				}
				$nolasharon_gallery = $nolasharon_product->get_gallery_image_ids();
				$nolasharon_thumb   = $nolasharon_gallery ? $nolasharon_gallery[0] : $nolasharon_product->get_image_id();
				?>
				<a class="hcard__item<?php echo esc_attr( $nolasharon_active ); ?>" href="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>" data-hcard-item>
					<span class="hcard__thumb"><?php echo wp_get_attachment_image( $nolasharon_thumb, 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?></span>
					<span class="hcard__body">
						<?php if ( nolasharon_setting( 'nolasharon_hero_card_label' ) ) : ?>
							<span class="hcard__label"><?php echo esc_html( nolasharon_setting( 'nolasharon_hero_card_label' ) ); ?></span>
						<?php endif; ?>
						<span class="hcard__name"><?php echo esc_html( $nolasharon_product->get_name() ); ?></span>
						<span class="hcard__price"><?php echo wp_kses_post( $nolasharon_product->get_price_html() ); ?></span>
						<span class="hcard__cta"><?php esc_html_e( 'Shop Now', 'nolasharon' ); ?> <?php echo nolasharon_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>

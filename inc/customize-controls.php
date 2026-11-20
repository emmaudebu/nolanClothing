<?php
/**
 * Custom Customizer controls: an ordered image gallery and an ordered product picker.
 * Both save a comma-separated list of IDs; assets/js/customize-controls.js does the editing.
 *
 * Loaded from nolasharon_customize_register(), when WP_Customize_Control exists.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * IDs saved by one of these controls.
 *
 * @param string $value Saved value.
 * @return int[]
 */
function nolasharon_control_ids( $value ) {
	return array_values( array_filter( array_map( 'absint', explode( ',', (string) $value ) ) ) );
}

/**
 * Pick images from the media library, drag to reorder.
 */
class NolaSharon_Gallery_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'nolasharon_gallery';

	/**
	 * Render the control.
	 */
	public function render_content() {
		?>
		<div class="ns-gallery" data-setting="<?php echo esc_attr( $this->id ); ?>"
			data-title="<?php esc_attr_e( 'Choose slideshow images', 'nolasharon' ); ?>"
			data-button="<?php esc_attr_e( 'Use these images', 'nolasharon' ); ?>"
			data-remove="<?php esc_attr_e( 'Remove', 'nolasharon' ); ?>">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<ul class="ns-gallery__list">
				<?php
				foreach ( nolasharon_control_ids( $this->value() ) as $id ) {
					$src = wp_get_attachment_image_url( $id, 'thumbnail' );
					if ( ! $src ) {
						continue;
					}
					printf(
						'<li class="ns-gallery__item" draggable="true" data-id="%1$d"><img src="%2$s" alt=""><button type="button" class="ns-gallery__remove" aria-label="%3$s">&times;</button></li>',
						(int) $id,
						esc_url( $src ),
						esc_attr__( 'Remove', 'nolasharon' )
					);
				}
				?>
			</ul>
			<p class="ns-gallery__actions">
				<button type="button" class="button ns-gallery__add"><?php esc_html_e( 'Add or change images', 'nolasharon' ); ?></button>
				<button type="button" class="button-link button-link-delete ns-gallery__clear"><?php esc_html_e( 'Remove all', 'nolasharon' ); ?></button>
			</p>
		</div>
		<?php
	}
}

/**
 * Tick products from the catalogue; ticked ones play in the order they were ticked.
 */
class NolaSharon_Products_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'nolasharon_products';

	/**
	 * Render the control.
	 */
	public function render_content() {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return;
		}
		$chosen   = nolasharon_control_ids( $this->value() );
		$products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 300,
				'orderby' => 'title',
				'order'   => 'ASC',
			)
		);
		// Chosen products first, in their play order; then the rest A–Z.
		usort(
			$products,
			function ( $a, $b ) use ( $chosen ) {
				$pa = array_search( $a->get_id(), $chosen, true );
				$pb = array_search( $b->get_id(), $chosen, true );
				$pa = false === $pa ? PHP_INT_MAX : $pa;
				$pb = false === $pb ? PHP_INT_MAX : $pb;
				return $pa === $pb ? strcasecmp( $a->get_name(), $b->get_name() ) : $pa - $pb;
			}
		);
		?>
		<div class="ns-pick" data-setting="<?php echo esc_attr( $this->id ); ?>">
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<input type="search" class="ns-pick__search" placeholder="<?php esc_attr_e( 'Search products…', 'nolasharon' ); ?>" aria-label="<?php esc_attr_e( 'Search products', 'nolasharon' ); ?>">
			<ul class="ns-pick__list">
				<?php
				foreach ( $products as $product ) :
					$order = array_search( $product->get_id(), $chosen, true );
					$thumb = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : '';
					?>
					<li class="ns-pick__item" data-name="<?php echo esc_attr( strtolower( $product->get_name() ) ); ?>">
						<label>
							<input type="checkbox" value="<?php echo (int) $product->get_id(); ?>" <?php checked( false !== $order ); ?>>
							<?php if ( $thumb ) : ?>
								<img src="<?php echo esc_url( $thumb ); ?>" alt="">
							<?php else : ?>
								<span class="ns-pick__noimg" title="<?php esc_attr_e( 'No product image: it will be skipped', 'nolasharon' ); ?>"></span>
							<?php endif; ?>
							<span class="ns-pick__name"><?php echo esc_html( $product->get_name() ); ?></span>
							<b class="ns-pick__n"><?php echo false !== $order ? (int) $order + 1 : ''; ?></b>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}

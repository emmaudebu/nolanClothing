<?php
/**
 * Quick view modal contents.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_product  = $args['product'];
$nolasharon_buyable  = $nolasharon_product->is_type( array( 'simple', 'variable' ) ) && $nolasharon_product->is_purchasable() && $nolasharon_product->is_in_stock();
$nolasharon_desc     = $nolasharon_product->get_description() ? $nolasharon_product->get_description() : $nolasharon_product->get_short_description();
$nolasharon_image_id = $nolasharon_product->get_image_id();
?>
<header class="qv__head">
	<div class="qv__thumb">
		<?php
		if ( $nolasharon_image_id ) {
			echo wp_get_attachment_image( $nolasharon_image_id, 'woocommerce_thumbnail', false, array( 'alt' => '' ) );
		} else {
			nolasharon_product_image( $nolasharon_product );
		}
		?>
	</div>
	<div class="qv__summary">
		<h2 class="qv__name" id="qv-title"><?php echo esc_html( $nolasharon_product->get_name() ); ?></h2>
		<div data-price><?php nolasharon_product_price( $nolasharon_product ); ?></div>
		<a class="qv__more" href="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>">
			<?php esc_html_e( 'See more details', 'nolasharon' ); ?>
			<?php echo nolasharon_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</header>

<div class="qv__body">
	<?php if ( $nolasharon_buyable ) : ?>
		<?php get_template_part( 'template-parts/buy-form', null, array( 'product' => $nolasharon_product, 'context' => 'quick' ) ); ?>
	<?php elseif ( ! $nolasharon_product->is_in_stock() ) : ?>
		<p class="pbuy__stock stock--outofstock"><span class="pbuy__dot" aria-hidden="true"></span><?php esc_html_e( 'Sold out', 'nolasharon' ); ?></p>
	<?php else : ?>
		<p class="drawer-note"><?php esc_html_e( 'This piece has more options. See the full details to choose yours.', 'nolasharon' ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== trim( wp_strip_all_tags( $nolasharon_desc ) ) ) : ?>
		<h3 class="qv__subtitle"><?php esc_html_e( 'Description', 'nolasharon' ); ?></h3>
		<div class="qv__desc entry-content"><?php echo wp_kses_post( wpautop( $nolasharon_desc ) ); ?></div>
	<?php endif; ?>
</div>

<footer class="qv__foot">
	<button class="qv__close" type="button" data-close-dialog><?php esc_html_e( 'Close', 'nolasharon' ); ?></button>
	<?php if ( $nolasharon_buyable ) : ?>
		<button class="pbuy__submit qv__add" type="submit" form="qv-form">
			<?php echo nolasharon_icon( 'cart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php esc_html_e( 'Add to Cart', 'nolasharon' ); ?></span>
		</button>
	<?php else : ?>
		<a class="pbuy__submit qv__add" href="<?php echo esc_url( $nolasharon_product->get_permalink() ); ?>"><span><?php esc_html_e( 'View product', 'nolasharon' ); ?></span></a>
	<?php endif; ?>
</footer>

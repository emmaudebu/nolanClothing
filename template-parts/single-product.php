<?php
/**
 * Single product: breadcrumb, image mosaic, description and the buying box.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_product = wc_get_product( get_the_ID() );
if ( ! $nolasharon_product ) {
	return;
}

if ( post_password_required() ) {
	echo '<div class="container content-area">' . get_the_password_form() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

// Breadcrumb category: the product's first category, skipping the "Sample" demo bucket.
$nolasharon_crumb = null;
foreach ( (array) get_the_terms( get_the_ID(), 'product_cat' ) as $nolasharon_term ) {
	if ( $nolasharon_term instanceof WP_Term && 'sample' !== $nolasharon_term->slug ) {
		$nolasharon_crumb = $nolasharon_term;
		break;
	}
}

// Images: featured first, then the gallery. Products without photos show their placeholder illustration.
$nolasharon_images = array();
foreach ( array_filter( array_merge( array( $nolasharon_product->get_image_id() ), $nolasharon_product->get_gallery_image_ids() ) ) as $nolasharon_image_id ) {
	$nolasharon_attr = array( 'alt' => $nolasharon_product->get_name() );
	if ( count( $nolasharon_images ) < 4 ) {
		$nolasharon_attr['loading'] = 'eager'; // The first four sit above the fold in the mosaic.
	}
	$nolasharon_images[] = array(
		'html' => wp_get_attachment_image( $nolasharon_image_id, 'woocommerce_single', false, $nolasharon_attr ),
		'full' => wp_get_attachment_image_url( $nolasharon_image_id, 'full' ),
	);
}
if ( ! $nolasharon_images ) {
	$nolasharon_placeholder = $nolasharon_product->get_meta( '_nolasharon_placeholder' );
	$nolasharon_src         = $nolasharon_placeholder ? nolasharon_img( $nolasharon_placeholder ) : wc_placeholder_img_src( 'woocommerce_single' );
	$nolasharon_images[]    = array(
		'html' => sprintf( '<img src="%1$s" alt="%2$s" width="400" height="500">', esc_url( $nolasharon_src ), esc_attr( $nolasharon_product->get_name() ) ),
		'full' => $nolasharon_src,
	);
}
$nolasharon_image_count = count( $nolasharon_images );
$nolasharon_gallery_mod = $nolasharon_image_count >= 4 ? 'mosaic' : 'count-' . $nolasharon_image_count;

$nolasharon_own_form = $nolasharon_product->is_type( array( 'simple', 'variable' ) );
$nolasharon_buyable  = $nolasharon_product->is_purchasable() && $nolasharon_product->is_in_stock();
$nolasharon_content  = get_the_content();
$nolasharon_excerpt  = $nolasharon_product->get_short_description();
/* translators: 1: product name, 2: product URL. */
$nolasharon_ask_url = nolasharon_whatsapp_url( sprintf( __( "Hi, I'm interested in %1\$s (%2\$s)", 'nolasharon' ), $nolasharon_product->get_name(), $nolasharon_product->get_permalink() ) );

// Details panel: only the fields that are filled in.
$nolasharon_details = array();
foreach ( nolasharon_detail_fields() as $nolasharon_key => $nolasharon_label ) {
	$nolasharon_value = trim( (string) $nolasharon_product->get_meta( $nolasharon_key ) );
	if ( ! nolasharon_unconfirmed( $nolasharon_value ) ) {
		$nolasharon_details[ $nolasharon_label ] = $nolasharon_value;
	}
}
if ( $nolasharon_product->get_sku() ) {
	$nolasharon_details[ __( 'Product code', 'nolasharon' ) ] = $nolasharon_product->get_sku();
}
$nolasharon_delivery = nolasharon_setting( 'nolasharon_delivery_info' );
$nolasharon_returns  = nolasharon_setting( 'nolasharon_returns_info' );

do_action( 'woocommerce_before_single_product' ); // Prints notices such as "added to cart".
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'container single-product-page', $nolasharon_product ); ?>>

	<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
		<ol>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a></li>
			<li><a href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop', 'nolasharon' ); ?></a></li>
			<?php if ( $nolasharon_crumb ) : ?>
				<li><a href="<?php echo esc_url( get_term_link( $nolasharon_crumb ) ); ?>"><?php echo esc_html( $nolasharon_crumb->name ); ?></a></li>
			<?php endif; ?>
			<li aria-current="page"><?php echo esc_html( $nolasharon_product->get_name() ); ?></li>
		</ol>
	</nav>

	<div class="pgallery pgallery--<?php echo esc_attr( $nolasharon_gallery_mod ); ?>">
		<?php foreach ( $nolasharon_images as $nolasharon_i => $nolasharon_image ) : ?>
			<button class="pgallery__item" type="button" data-index="<?php echo esc_attr( $nolasharon_i ); ?>" data-full="<?php echo esc_url( $nolasharon_image['full'] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: image number, 2: total images. */ __( 'View image %1$d of %2$d', 'nolasharon' ), $nolasharon_i + 1, $nolasharon_image_count ) ); ?>">
				<?php echo $nolasharon_image['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built by wp_get_attachment_image() or escaped above. ?>
			</button>
		<?php endforeach; ?>
	</div>

	<div class="pdetail">
		<div class="pdetail__head">
			<h1 class="product_title pdetail__title"><?php echo esc_html( $nolasharon_product->get_name() ); ?></h1>
			<?php if ( wc_review_ratings_enabled() && $nolasharon_product->get_review_count() > 0 ) : ?>
				<a class="pdetail__rating" href="#reviews">
					<?php echo wc_get_rating_html( $nolasharon_product->get_average_rating() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce markup. ?>
					<span><?php echo esc_html( sprintf( /* translators: %d: number of reviews. */ _n( '%d review', '%d reviews', $nolasharon_product->get_review_count(), 'nolasharon' ), $nolasharon_product->get_review_count() ) ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<div class="pdetail__desc">
			<?php if ( '' !== trim( wp_strip_all_tags( $nolasharon_excerpt ) ) ) : ?>
				<div class="pdetail__short entry-content"><?php echo wp_kses_post( wpautop( $nolasharon_excerpt ) ); ?></div>
			<?php endif; ?>

			<div class="accordion">
				<?php if ( '' !== trim( $nolasharon_content ) ) : ?>
					<details class="accordion__item" open>
						<summary><?php esc_html_e( 'Description', 'nolasharon' ); ?></summary>
						<div class="accordion__body entry-content"><?php the_content(); ?></div>
					</details>
				<?php endif; ?>
				<?php if ( $nolasharon_details ) : ?>
					<details class="accordion__item">
						<summary><?php esc_html_e( 'Details', 'nolasharon' ); ?></summary>
						<dl class="accordion__body detail-list">
							<?php foreach ( $nolasharon_details as $nolasharon_label => $nolasharon_value ) : ?>
								<dt><?php echo esc_html( $nolasharon_label ); ?></dt>
								<dd><?php echo nl2br( esc_html( $nolasharon_value ) ); ?></dd>
							<?php endforeach; ?>
						</dl>
					</details>
				<?php endif; ?>
				<?php if ( ! nolasharon_unconfirmed( $nolasharon_delivery ) ) : ?>
					<details class="accordion__item">
						<summary><?php esc_html_e( 'Delivery information', 'nolasharon' ); ?></summary>
						<div class="accordion__body entry-content">
							<?php echo wp_kses_post( wpautop( $nolasharon_delivery ) ); ?>
							<p><a href="<?php echo esc_url( nolasharon_page_url( 'delivery-policy' ) ); ?>"><?php esc_html_e( 'Read our Delivery Policy', 'nolasharon' ); ?></a></p>
						</div>
					</details>
				<?php endif; ?>
				<?php if ( ! nolasharon_unconfirmed( $nolasharon_returns ) ) : ?>
					<details class="accordion__item">
						<summary><?php esc_html_e( 'Returns & exchanges', 'nolasharon' ); ?></summary>
						<div class="accordion__body entry-content">
							<?php echo wp_kses_post( wpautop( $nolasharon_returns ) ); ?>
							<p><a href="<?php echo esc_url( nolasharon_page_url( 'returns-policy' ) ); ?>"><?php esc_html_e( 'Read our Returns & Exchange Policy', 'nolasharon' ); ?></a></p>
						</div>
					</details>
				<?php endif; ?>
			</div>
		</div>

		<div class="pbuy">
			<div class="pbuy__price" data-price><?php nolasharon_product_price( $nolasharon_product ); ?></div>

			<?php if ( ! $nolasharon_buyable ) : ?>
				<p class="pbuy__stock-status pbuy__stock-status--out"><span class="stock-dot stock-dot--outofstock" aria-hidden="true"></span><?php esc_html_e( 'Sold out', 'nolasharon' ); ?></p>
				<div class="pbuy__actions">
					<?php if ( $nolasharon_ask_url ) : ?>
						<a class="pbuy__submit pbuy__submit--wa" href="<?php echo esc_url( $nolasharon_ask_url ); ?>" target="_blank" rel="noopener"><span><?php esc_html_e( 'Ask on WhatsApp', 'nolasharon' ); ?></span><?php echo nolasharon_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php else : ?>
						<button class="pbuy__submit" type="button" disabled><?php esc_html_e( 'Sold out', 'nolasharon' ); ?></button>
					<?php endif; ?>
					<?php nolasharon_wishlist_button( $nolasharon_product, 'single' ); ?>
				</div>
			<?php elseif ( $nolasharon_own_form ) : ?>
				<?php get_template_part( 'template-parts/buy-form', null, array( 'product' => $nolasharon_product ) ); ?>
			<?php else : ?>
				<?php woocommerce_template_single_add_to_cart(); ?>
			<?php endif; ?>

			<?php if ( $nolasharon_buyable && $nolasharon_ask_url ) : ?>
				<a class="pbuy__ask" href="<?php echo esc_url( $nolasharon_ask_url ); ?>" target="_blank" rel="noopener"><?php echo nolasharon_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Ask about this item', 'nolasharon' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if ( $nolasharon_buyable && $nolasharon_own_form ) : ?>
	<div class="sticky-buy" data-sticky-buy hidden>
		<div class="sticky-buy__info">
			<span class="sticky-buy__name"><?php echo esc_html( $nolasharon_product->get_name() ); ?></span>
			<span class="sticky-buy__price" data-sticky-price><?php nolasharon_product_price( $nolasharon_product ); ?></span>
		</div>
		<button class="sticky-buy__btn" type="button" data-sticky-submit="ns-form"><?php esc_html_e( 'Add to Cart', 'nolasharon' ); ?></button>
	</div>
<?php endif; ?>

<?php if ( comments_open() || get_comments_number() ) : ?>
	<section class="container previews" aria-label="<?php esc_attr_e( 'Customer reviews', 'nolasharon' ); ?>">
		<?php comments_template(); // WooCommerce swaps in its reviews template for products. ?>
	</section>
<?php endif; ?>

<?php
$nolasharon_related = array_filter( array_map( 'wc_get_product', wc_get_related_products( $nolasharon_product->get_id(), 4 ) ) );
if ( $nolasharon_related ) :
	?>
	<section class="section section--tint" aria-labelledby="related-title">
		<div class="container">
			<h2 class="section__title section__title--spaced" id="related-title"><?php esc_html_e( 'You may also like', 'nolasharon' ); ?></h2>
			<div class="product-grid">
				<?php
				foreach ( $nolasharon_related as $nolasharon_item ) {
					nolasharon_product_card( $nolasharon_item );
				}
				?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/size-guide' ); ?>

<dialog class="lightbox" id="lightbox" aria-label="<?php esc_attr_e( 'Product images', 'nolasharon' ); ?>">
	<button class="lightbox__close icon-btn" type="button" data-close-dialog aria-label="<?php esc_attr_e( 'Close', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'close', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	<img class="lightbox__img" src="" alt="<?php echo esc_attr( $nolasharon_product->get_name() ); ?>">
	<?php if ( $nolasharon_image_count > 1 ) : ?>
		<button class="lightbox__nav lightbox__nav--prev icon-btn" type="button" data-dir="-1" aria-label="<?php esc_attr_e( 'Previous image', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'prev', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<button class="lightbox__nav lightbox__nav--next icon-btn" type="button" data-dir="1" aria-label="<?php esc_attr_e( 'Next image', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'next', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<p class="lightbox__count" aria-live="polite"></p>
	<?php endif; ?>
</dialog>

<?php
if ( isset( WC()->structured_data ) && method_exists( WC()->structured_data, 'generate_product_data' ) ) {
	WC()->structured_data->generate_product_data( $nolasharon_product );
}
do_action( 'woocommerce_after_single_product' );

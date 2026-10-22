<?php
/**
 * Homepage.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nolasharon_collections = array(
	array( 'women', __( 'Women', 'nolasharon' ), 'wrap-dress' ),
	array( 'men', __( 'Men', 'nolasharon' ), 'agbada' ),
	array( 'adire-edit', __( 'Adire Edit', 'nolasharon' ), 'adire-pants' ),
	array( 'occasion-wear', __( 'Occasion Wear', 'nolasharon' ), 'occasion-gown' ),
);

$nolasharon_products = function_exists( 'wc_get_products' )
	? wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => 8,
			'orderby'    => 'date',
			'order'      => 'DESC',
		)
	)
	: array();

$nolasharon_video_id = nolasharon_youtube_id( get_theme_mod( 'nolasharon_video', '' ) );
?>

<section class="hero" aria-labelledby="hero-title">
	<span class="hero__watermark" aria-hidden="true">NolaSharon</span>
	<div class="container hero__inner">
		<div class="hero__copy">
			<span class="hero__script" aria-hidden="true">NolaSharon</span>
			<h1 class="hero__title" id="hero-title"><?php echo wp_kses( __( 'Wear your <br>story.', 'nolasharon' ), array( 'br' => array() ) ); ?></h1>
			<span class="hero__pill"><?php esc_html_e( 'New collection', 'nolasharon' ); ?></span>
			<p class="hero__text"><?php esc_html_e( 'Handmade ready-to-wear and made-to-fit pieces for women and men, delivered from Nigeria to the world.', 'nolasharon' ); ?></p>
			<div class="hero__actions">
				<a class="btn btn--dark" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Shop now', 'nolasharon' ); ?></a>
				<a class="btn btn--light" href="<?php echo esc_url( nolasharon_category_url( 'made-to-fit' ) ); ?>"><?php esc_html_e( 'Get it made to fit', 'nolasharon' ); ?></a>
			</div>
		</div>
		<div class="hero__media">
			<img src="<?php echo esc_url( nolasharon_img( 'hero-figure' ) ); ?>" alt="<?php esc_attr_e( 'Illustration of a woman in an indigo adire gown and gold gele', 'nolasharon' ); ?>" width="600" height="700" fetchpriority="high">
		</div>
	</div>
</section>

<section class="section container" aria-labelledby="collections-title">
	<div class="section__head">
		<h2 class="section__title" id="collections-title"><?php esc_html_e( 'Shop Our Collections', 'nolasharon' ); ?></h2>
		<a class="text-link" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'View all collections', 'nolasharon' ); ?></a>
	</div>
	<div class="collection-grid">
		<?php
		foreach ( $nolasharon_collections as $nolasharon_collection ) :
			list( $nolasharon_slug, $nolasharon_name, $nolasharon_fallback ) = $nolasharon_collection;
			$nolasharon_term     = taxonomy_exists( 'product_cat' ) ? get_term_by( 'slug', $nolasharon_slug, 'product_cat' ) : false;
			$nolasharon_thumb_id = $nolasharon_term ? (int) get_term_meta( $nolasharon_term->term_id, 'thumbnail_id', true ) : 0;
			$nolasharon_name     = $nolasharon_term ? $nolasharon_term->name : $nolasharon_name;
			?>
			<a class="collection-tile" href="<?php echo esc_url( nolasharon_category_url( $nolasharon_slug ) ); ?>">
				<?php
				if ( $nolasharon_thumb_id ) {
					echo wp_get_attachment_image( $nolasharon_thumb_id, 'woocommerce_thumbnail', false, array( 'alt' => '' ) );
				} else {
					printf( '<img src="%s" alt="" width="400" height="500" loading="lazy" decoding="async">', esc_url( nolasharon_img( $nolasharon_fallback ) ) );
				}
				?>
				<span class="collection-tile__name"><?php echo esc_html( $nolasharon_name ); ?><?php echo nolasharon_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</section>

<section class="section section--tint" aria-labelledby="new-in-title">
	<div class="container">
		<h2 class="section__title section__title--spaced" id="new-in-title"><?php esc_html_e( 'New In', 'nolasharon' ); ?></h2>
		<?php if ( $nolasharon_products ) : ?>
			<div class="product-grid">
				<?php
				foreach ( $nolasharon_products as $nolasharon_product ) {
					nolasharon_product_card( $nolasharon_product );
				}
				?>
			</div>
		<?php else : ?>
			<p class="empty-note"><?php esc_html_e( 'New pieces are on the way. Check back soon.', 'nolasharon' ); ?></p>
		<?php endif; ?>
		<div class="section__cta">
			<a class="btn btn--outline" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'View all products', 'nolasharon' ); ?></a>
		</div>
	</div>
</section>

<section class="section container studio" aria-labelledby="studio-title">
	<h2 class="section__title studio__title" id="studio-title"><?php esc_html_e( 'Take a walk in our production space', 'nolasharon' ); ?></h2>
	<a class="text-link" href="<?php echo esc_url( nolasharon_page_url( 'our-story' ) ); ?>"><?php esc_html_e( 'Read about us', 'nolasharon' ); ?></a>
	<?php if ( $nolasharon_video_id ) : ?>
		<?php /* Lightweight embed: the YouTube iframe only loads after the visitor presses play. */ ?>
		<div class="video-embed" data-video-id="<?php echo esc_attr( $nolasharon_video_id ); ?>">
			<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nolasharon_video_id . '/hqdefault.jpg' ); ?>" alt="" loading="lazy" decoding="async">
			<button class="video-embed__play" type="button" aria-label="<?php esc_attr_e( 'Play video: our production space', 'nolasharon' ); ?>">
				<?php echo nolasharon_icon( 'play', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
		<div class="video-embed video-embed--empty">
			<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=nolasharon_video' ) ); ?>"><?php esc_html_e( 'Add the studio video in Appearance → Customize → NolaSharon: homepage (only admins see this).', 'nolasharon' ); ?></a>
		</div>
	<?php endif; ?>
</section>

<?php
get_footer();

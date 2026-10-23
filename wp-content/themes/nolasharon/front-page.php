<?php
/**
 * Homepage (brief §4, sections C–I). The announcement bar (A), header (B) and footer (J)
 * come from header.php and footer.php. Every section hides itself when it has nothing to show.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nolasharon_has_wc     = function_exists( 'wc_get_products' );
$nolasharon_categories = $nolasharon_has_wc ? nolasharon_home_categories() : array();
$nolasharon_new        = $nolasharon_has_wc ? wc_get_products(
	array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => 8,
		'orderby'    => 'date',
		'order'      => 'DESC',
	)
) : array();
$nolasharon_featured   = $nolasharon_has_wc ? wc_get_products(
	array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'featured'   => true,
		'limit'      => 4,
	)
) : array();
$nolasharon_branches   = ns_get_branches( array( 'include_drafts' => true ) );
$nolasharon_spotlight  = ns_branch_spotlight( $nolasharon_branches );
$nolasharon_video_id   = nolasharon_youtube_id( nolasharon_setting( 'nolasharon_video' ) );
$nolasharon_new_url    = add_query_arg( 'orderby', 'date', nolasharon_shop_url() );

// Hero settings.
$nolasharon_hero_img   = (int) nolasharon_setting( 'nolasharon_hero_image' );
$nolasharon_btn1_label = nolasharon_setting( 'nolasharon_hero_btn1_label' );
$nolasharon_btn1_link  = nolasharon_setting( 'nolasharon_hero_btn1_link' ) ? nolasharon_setting( 'nolasharon_hero_btn1_link' ) : nolasharon_shop_url();
$nolasharon_btn2_label = nolasharon_setting( 'nolasharon_hero_btn2_label' );
$nolasharon_btn2_link  = nolasharon_setting( 'nolasharon_hero_btn2_link' ) ? nolasharon_setting( 'nolasharon_hero_btn2_link' ) : nolasharon_page_url( 'store-locations' );

// Brand introduction.
$nolasharon_brand_text = nolasharon_setting( 'nolasharon_brand_text' );
$nolasharon_brand_img  = (int) nolasharon_setting( 'nolasharon_brand_image' );

// Trust strip: only items backed by a filled-in setting (brief §4 I).
$nolasharon_trust = array();
if ( ! nolasharon_unconfirmed( nolasharon_setting( 'nolasharon_trust_payment' ) ) ) {
	$nolasharon_trust[] = array( 'card', __( 'Payment', 'nolasharon' ), nolasharon_setting( 'nolasharon_trust_payment' ), '' );
}
if ( ! nolasharon_unconfirmed( nolasharon_setting( 'nolasharon_trust_delivery' ) ) ) {
	$nolasharon_trust[] = array( 'truck', __( 'Delivery', 'nolasharon' ), nolasharon_setting( 'nolasharon_trust_delivery' ), nolasharon_page_url( 'delivery-policy' ) );
}
if ( function_exists( 'nolasharon_pickup_branches' ) && nolasharon_pickup_branches() ) {
	$nolasharon_trust[] = array( 'store', __( 'Store pickup', 'nolasharon' ), __( 'Collect your order from one of our branches.', 'nolasharon' ), nolasharon_page_url( 'store-locations' ) );
}
if ( nolasharon_whatsapp_url() || nolasharon_setting( 'nolasharon_phone' ) ) {
	$nolasharon_trust[] = array( 'chat', __( 'Customer support', 'nolasharon' ), nolasharon_whatsapp_url() ? __( 'Questions? Chat with us on WhatsApp.', 'nolasharon' ) : __( 'Questions? Give us a call.', 'nolasharon' ), nolasharon_page_url( 'contact' ) );
}
?>

<section class="hero" aria-labelledby="hero-title">
	<span class="hero__watermark" aria-hidden="true">NolaSharon</span>
	<div class="container hero__inner">
		<div class="hero__copy">
			<?php if ( nolasharon_setting( 'nolasharon_hero_eyebrow' ) ) : ?>
				<span class="hero__eyebrow"><?php echo esc_html( nolasharon_setting( 'nolasharon_hero_eyebrow' ) ); ?></span>
			<?php endif; ?>
			<h1 class="hero__title" id="hero-title"><?php echo esc_html( nolasharon_setting( 'nolasharon_hero_title' ) ); ?></h1>
			<?php if ( nolasharon_setting( 'nolasharon_hero_text' ) ) : ?>
				<p class="hero__text"><?php echo esc_html( nolasharon_setting( 'nolasharon_hero_text' ) ); ?></p>
			<?php endif; ?>
			<div class="hero__actions">
				<?php if ( $nolasharon_btn1_label ) : ?>
					<a class="btn btn--dark" href="<?php echo esc_url( $nolasharon_btn1_link ); ?>"><?php echo esc_html( $nolasharon_btn1_label ); ?></a>
				<?php endif; ?>
				<?php if ( $nolasharon_btn2_label ) : ?>
					<a class="btn btn--light" href="<?php echo esc_url( $nolasharon_btn2_link ); ?>"><?php echo esc_html( $nolasharon_btn2_label ); ?></a>
				<?php endif; ?>
			</div>
		</div>
		<div class="hero__media">
			<?php
			if ( $nolasharon_hero_img ) {
				echo wp_get_attachment_image(
					$nolasharon_hero_img,
					'large',
					false,
					array(
						'fetchpriority' => 'high',
						'loading'       => 'eager',
					)
				);
			} else {
				printf( '<img src="%1$s" alt="%2$s" width="600" height="700" fetchpriority="high">', esc_url( nolasharon_img( 'hero-figure' ) ), esc_attr__( 'Illustration of a woman in an indigo gown and gold head wrap', 'nolasharon' ) );
			}
			?>
		</div>
	</div>
</section>

<?php
if ( $nolasharon_categories ) {
	get_template_part( 'template-parts/category-cards', null, array( 'categories' => $nolasharon_categories ) );
}
?>

<?php if ( $nolasharon_new ) : ?>
	<section class="section section--tint" aria-labelledby="new-in-title">
		<div class="container">
			<h2 class="section__title section__title--spaced" id="new-in-title"><?php esc_html_e( 'New Arrivals', 'nolasharon' ); ?></h2>
			<div class="product-grid">
				<?php
				foreach ( $nolasharon_new as $nolasharon_product ) {
					nolasharon_product_card( $nolasharon_product );
				}
				?>
			</div>
			<div class="section__cta">
				<a class="ns-btn ns-btn--ghost" href="<?php echo esc_url( $nolasharon_new_url ); ?>"><?php esc_html_e( 'View New Arrivals', 'nolasharon' ); ?></a>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $nolasharon_featured ) : ?>
	<section class="section container" aria-labelledby="featured-title">
		<h2 class="section__title section__title--spaced" id="featured-title"><?php esc_html_e( 'Featured Pieces', 'nolasharon' ); ?></h2>
		<div class="product-grid">
			<?php
			foreach ( $nolasharon_featured as $nolasharon_product ) {
				nolasharon_product_card( $nolasharon_product );
			}
			?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! nolasharon_unconfirmed( $nolasharon_brand_text ) ) : ?>
	<section class="section brand-intro" aria-labelledby="brand-title">
		<div class="container brand-intro__inner">
			<?php if ( $nolasharon_brand_img ) : ?>
				<div class="brand-intro__media"><?php echo wp_get_attachment_image( $nolasharon_brand_img, 'large', false, array( 'alt' => '' ) ); ?></div>
			<?php endif; ?>
			<div class="brand-intro__copy">
				<h2 class="section__title" id="brand-title"><?php echo esc_html( nolasharon_setting( 'nolasharon_brand_title' ) ); ?></h2>
				<?php echo wp_kses_post( wpautop( esc_html( $nolasharon_brand_text ) ) ); ?>
				<a class="btn btn--outline" href="<?php echo esc_url( nolasharon_page_url( 'about-us' ) ); ?>"><?php esc_html_e( 'About Us', 'nolasharon' ); ?></a>
			</div>
		</div>
	</section>
<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
	<p class="container admin-note"><a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=nolasharon_brand' ) ); ?>"><?php esc_html_e( 'Brand introduction is hidden until the client-approved description is added in Customizer → NolaSharon Settings (only admins see this).', 'nolasharon' ); ?></a></p>
<?php endif; ?>

<?php
if ( $nolasharon_spotlight ) {
	get_template_part(
		'template-parts/branch-spotlight',
		null,
		array(
			'branch'   => $nolasharon_spotlight,
			'context'  => 'home',
			'branches' => $nolasharon_branches,
		)
	);
}
?>

<?php if ( $nolasharon_trust ) : ?>
	<section class="trust" aria-label="<?php esc_attr_e( 'Shopping with us', 'nolasharon' ); ?>">
		<ul class="container trust__list">
			<?php foreach ( $nolasharon_trust as $nolasharon_item ) : ?>
				<li class="trust__item">
					<?php echo nolasharon_icon( $nolasharon_item[0], 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<div>
						<strong><?php echo esc_html( $nolasharon_item[1] ); ?></strong>
						<?php if ( $nolasharon_item[3] ) : ?>
							<a href="<?php echo esc_url( $nolasharon_item[3] ); ?>"><?php echo esc_html( $nolasharon_item[2] ); ?></a>
						<?php else : ?>
							<span><?php echo esc_html( $nolasharon_item[2] ); ?></span>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<?php if ( $nolasharon_video_id ) : ?>
	<section class="section container studio" aria-labelledby="studio-title">
		<?php $nolasharon_video_title = nolasharon_setting( 'nolasharon_video_title' ); ?>
		<?php if ( $nolasharon_video_title ) : ?>
			<h2 class="section__title studio__title" id="studio-title"><?php echo esc_html( $nolasharon_video_title ); ?></h2>
		<?php endif; ?>
		<a class="text-link" href="<?php echo esc_url( nolasharon_page_url( 'about-us' ) ); ?>"><?php esc_html_e( 'Read about us', 'nolasharon' ); ?></a>
		<?php /* Lightweight embed: the YouTube iframe only loads after the visitor presses play. */ ?>
		<div class="video-embed" data-video-id="<?php echo esc_attr( $nolasharon_video_id ); ?>">
			<img src="<?php echo esc_url( 'https://i.ytimg.com/vi/' . $nolasharon_video_id . '/hqdefault.jpg' ); ?>" alt="" loading="lazy" decoding="async">
			<button class="video-embed__play" type="button" aria-label="<?php echo esc_attr( $nolasharon_video_title ? sprintf( /* translators: %s: video title. */ __( 'Play video: %s', 'nolasharon' ), $nolasharon_video_title ) : __( 'Play video', 'nolasharon' ) ); ?>">
				<?php echo nolasharon_icon( 'play', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</section>
<?php endif; ?>

<?php
get_footer();

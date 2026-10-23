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
$nolasharon_branches   = nolasharon_get_branches();
$nolasharon_spotlight  = nolasharon_spotlight_branch( $nolasharon_branches );
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

<?php if ( $nolasharon_categories ) : ?>
	<section class="section container" aria-labelledby="collections-title">
		<div class="section__head">
			<h2 class="section__title" id="collections-title"><?php esc_html_e( 'Shop Our Collection', 'nolasharon' ); ?></h2>
			<a class="text-link" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Explore Our Collection', 'nolasharon' ); ?></a>
		</div>
		<div class="collection-grid collection-grid--<?php echo esc_attr( count( $nolasharon_categories ) ); ?>">
			<?php foreach ( $nolasharon_categories as $nolasharon_cat ) : ?>
				<?php $nolasharon_thumb_id = (int) get_term_meta( $nolasharon_cat->term_id, 'thumbnail_id', true ); ?>
				<a class="collection-tile" href="<?php echo esc_url( get_term_link( $nolasharon_cat ) ); ?>">
					<?php
					if ( $nolasharon_thumb_id ) {
						echo wp_get_attachment_image( $nolasharon_thumb_id, 'woocommerce_thumbnail', false, array( 'alt' => '' ) );
					} else {
						echo '<span class="collection-tile__placeholder" aria-hidden="true"></span>';
					}
					?>
					<span class="collection-tile__name"><?php echo esc_html( $nolasharon_cat->name ); ?><?php echo nolasharon_icon( 'arrow', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

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
				<a class="btn btn--outline" href="<?php echo esc_url( $nolasharon_new_url ); ?>"><?php esc_html_e( 'View New Arrivals', 'nolasharon' ); ?></a>
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

<?php if ( $nolasharon_spotlight ) : ?>
	<?php
	$nolasharon_status     = nolasharon_branch_status_label( $nolasharon_spotlight );
	$nolasharon_directions = nolasharon_branch_directions_url( $nolasharon_spotlight );
	$nolasharon_area       = $nolasharon_spotlight['area'] ? $nolasharon_spotlight['area'] : $nolasharon_spotlight['name'];
	?>
	<section class="section spotlight" aria-labelledby="spotlight-title">
		<div class="container spotlight__inner">
			<div class="spotlight__media">
				<?php
				if ( $nolasharon_spotlight['image_id'] ) {
					echo wp_get_attachment_image( $nolasharon_spotlight['image_id'], 'large', false, array( 'alt' => '' ) );
				} else {
					echo '<span class="spotlight__placeholder" aria-hidden="true">' . nolasharon_icon( 'store', 64 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
			<div class="spotlight__copy">
				<?php if ( $nolasharon_spotlight['is_draft'] ) : ?>
					<span class="tag tag--draft"><?php esc_html_e( 'Draft branch: only editors see this section', 'nolasharon' ); ?></span>
				<?php endif; ?>
				<p class="spotlight__eyebrow"><?php echo esc_html( $nolasharon_status ? $nolasharon_status : __( 'New location', 'nolasharon' ) ); ?></p>
				<h2 class="section__title" id="spotlight-title"><?php echo esc_html( sprintf( /* translators: %s: area, e.g. Ogudu. */ __( 'Now also in %s', 'nolasharon' ), $nolasharon_area ) ); ?></h2>
				<!-- DRAFT: client to approve -->
				<p class="spotlight__text"><?php echo esc_html( sprintf( /* translators: %s: area, e.g. Ogudu. */ __( 'Our Ikeja stores remain open. %s is our newest location.', 'nolasharon' ), $nolasharon_area ) ); ?></p>
				<?php if ( $nolasharon_spotlight['address'] ) : ?>
					<p class="spotlight__address"><?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( $nolasharon_spotlight['address'] ); ?></p>
				<?php endif; ?>
				<div class="spotlight__actions">
					<a class="btn btn--light" href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) . '#branch-' . $nolasharon_spotlight['id'] ); ?>"><?php esc_html_e( 'Visit Our Store', 'nolasharon' ); ?></a>
					<?php if ( $nolasharon_directions ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $nolasharon_directions ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get Directions', 'nolasharon' ); ?></a>
					<?php endif; ?>
				</div>
				<?php if ( count( $nolasharon_branches ) > 1 ) : ?>
					<ul class="spotlight__branches" aria-label="<?php esc_attr_e( 'All our stores', 'nolasharon' ); ?>">
						<?php foreach ( $nolasharon_branches as $nolasharon_branch ) : ?>
							<li>
								<a href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) . '#branch-' . $nolasharon_branch['id'] ); ?>"><?php echo esc_html( $nolasharon_branch['name'] ); ?></a>
								<?php $nolasharon_label = nolasharon_branch_status_label( $nolasharon_branch ); ?>
								<?php if ( $nolasharon_label ) : ?>
									<span>· <?php echo esc_html( $nolasharon_label ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

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

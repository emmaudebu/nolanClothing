<?php
/**
 * Contact Us page (slug "contact", brief §5.8). Contact rows come from Customizer → NolaSharon
 * Settings and are hidden while empty. The page content holds the contact form shortcode.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

$nolasharon_phone    = nolasharon_setting( 'nolasharon_phone' );
$nolasharon_email    = nolasharon_setting( 'nolasharon_email' );
$nolasharon_hours    = nolasharon_setting( 'nolasharon_hours' );
$nolasharon_whatsapp = nolasharon_whatsapp_url();
$nolasharon_social   = array_filter(
	array(
		__( 'Instagram', 'nolasharon' ) => nolasharon_setting( 'nolasharon_instagram' ),
		__( 'Facebook', 'nolasharon' )  => nolasharon_setting( 'nolasharon_facebook' ),
		__( 'TikTok', 'nolasharon' )    => nolasharon_setting( 'nolasharon_tiktok' ),
	)
);
$nolasharon_branches = nolasharon_get_branches( false );
$nolasharon_any      = $nolasharon_phone || $nolasharon_email || $nolasharon_whatsapp || $nolasharon_hours || $nolasharon_social;
?>
<header class="archive-banner">
	<div class="container">
		<h1 class="archive-banner__title"><?php the_title(); ?></h1>
	</div>
</header>

<div class="container content-area contact">
	<div class="contact__grid">
		<section class="contact__details" aria-labelledby="contact-reach">
			<h2 class="section__title" id="contact-reach"><?php esc_html_e( 'Get in touch', 'nolasharon' ); ?></h2>
			<?php if ( $nolasharon_any ) : ?>
				<ul class="contact-list">
					<?php if ( $nolasharon_phone ) : ?>
						<li><?php echo nolasharon_icon( 'phone', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong><?php esc_html_e( 'Call us', 'nolasharon' ); ?></strong><a href="<?php echo esc_url( nolasharon_tel_url( $nolasharon_phone ), array( 'tel' ) ); ?>"><?php echo esc_html( $nolasharon_phone ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( $nolasharon_whatsapp ) : ?>
						<li><?php echo nolasharon_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong><?php esc_html_e( 'WhatsApp', 'nolasharon' ); ?></strong><a href="<?php echo esc_url( $nolasharon_whatsapp ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat with us', 'nolasharon' ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( $nolasharon_email ) : ?>
						<li><?php echo nolasharon_icon( 'mail', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong><?php esc_html_e( 'Email', 'nolasharon' ); ?></strong><a href="<?php echo esc_url( 'mailto:' . $nolasharon_email, array( 'mailto' ) ); ?>"><?php echo esc_html( $nolasharon_email ); ?></a></span></li>
					<?php endif; ?>
					<?php if ( $nolasharon_hours ) : ?>
						<li><?php echo nolasharon_icon( 'clock', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><strong><?php esc_html_e( 'Customer service hours', 'nolasharon' ); ?></strong><?php echo nl2br( esc_html( $nolasharon_hours ) ); ?></span></li>
					<?php endif; ?>
				</ul>
				<?php if ( $nolasharon_social ) : ?>
					<p class="contact__social">
						<?php foreach ( $nolasharon_social as $nolasharon_label => $nolasharon_url ) : ?>
							<a href="<?php echo esc_url( $nolasharon_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $nolasharon_label ); ?></a>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>
			<?php else : ?>
				<p class="empty-note"><?php esc_html_e( 'Our contact details are coming soon.', 'nolasharon' ); ?></p>
				<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
					<p class="admin-note"><a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=nolasharon_contact' ) ); ?>"><?php esc_html_e( 'Add the confirmed phone, WhatsApp, email and hours in Customizer → NolaSharon Settings → Contact & social (only admins see this).', 'nolasharon' ); ?></a></p>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $nolasharon_branches ) : ?>
				<h3 class="contact__sub"><?php esc_html_e( 'Our stores', 'nolasharon' ); ?></h3>
				<ul class="contact__branches">
					<?php foreach ( $nolasharon_branches as $nolasharon_branch ) : ?>
						<li><a href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) . '#branch-' . $nolasharon_branch['id'] ); ?>"><?php echo esc_html( $nolasharon_branch['name'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
				<a class="btn btn--outline btn--sm" href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) ); ?>"><?php esc_html_e( 'Visit Our Store', 'nolasharon' ); ?></a>
			<?php endif; ?>
		</section>

		<section class="contact__form" aria-labelledby="contact-message">
			<h2 class="section__title" id="contact-message"><?php esc_html_e( 'Send us a message', 'nolasharon' ); ?></h2>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<div class="entry-content"><?php the_content(); ?></div>
			<?php endwhile; ?>
		</section>
	</div>
</div>
<?php
get_footer();

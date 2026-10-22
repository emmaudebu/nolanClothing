<?php
/**
 * Site footer and floating WhatsApp button.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_phone   = (string) get_theme_mod( 'nolasharon_phone', '' );
$nolasharon_columns = array(
	array(
		__( 'Shop Women', 'nolasharon' )  => nolasharon_category_url( 'women' ),
		__( 'Shop Men', 'nolasharon' )    => nolasharon_category_url( 'men' ),
		__( 'Collections', 'nolasharon' ) => nolasharon_shop_url(),
	),
	array(
		__( 'Our Story', 'nolasharon' )  => nolasharon_page_url( 'our-story' ),
		__( 'FAQs', 'nolasharon' )       => nolasharon_page_url( 'faqs' ),
		__( 'Size Guide', 'nolasharon' ) => nolasharon_page_url( 'size-guide' ),
	),
	array(
		__( 'Contact Us', 'nolasharon' )  => nolasharon_page_url( 'contact' ),
		__( 'Call Us', 'nolasharon' )     => $nolasharon_phone ? 'tel:' . preg_replace( '/[^\d+]/', '', $nolasharon_phone ) : nolasharon_page_url( 'contact' ),
		__( 'Track Order', 'nolasharon' ) => nolasharon_page_url( 'track-order' ),
	),
	array(
		__( 'Instagram', 'nolasharon' ) => get_theme_mod( 'nolasharon_instagram', '' ),
		__( 'TikTok', 'nolasharon' )    => get_theme_mod( 'nolasharon_tiktok', '' ),
		__( 'WhatsApp', 'nolasharon' )  => nolasharon_whatsapp_url(),
	),
);
?>
</main>

<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__top">
			<?php nolasharon_logo( 'span' ); ?>
			<nav class="site-footer__cols" aria-label="<?php esc_attr_e( 'Footer', 'nolasharon' ); ?>">
				<?php foreach ( $nolasharon_columns as $nolasharon_column ) : ?>
					<ul>
						<?php
						foreach ( $nolasharon_column as $nolasharon_label => $nolasharon_url ) :
							if ( ! $nolasharon_url ) {
								continue; // Social link not configured yet.
							}
							$nolasharon_external = 0 === strpos( $nolasharon_url, 'http' ) && false === strpos( $nolasharon_url, home_url() );
							?>
							<li><a href="<?php echo esc_url( $nolasharon_url, array( 'http', 'https', 'tel' ) ); ?>"<?php echo $nolasharon_external ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $nolasharon_label ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endforeach; ?>
			</nav>
		</div>
		<div class="site-footer__bottom">
			<p>
				&copy; <?php esc_html_e( 'NolaSharon Clothing', 'nolasharon' ); ?> <?php echo esc_html( wp_date( 'Y' ) ); ?>
				<span aria-hidden="true">|</span>
				<button class="footer-theme-toggle theme-toggle" type="button" aria-label="<?php esc_attr_e( 'Switch to dark theme', 'nolasharon' ); ?>" data-label-dark="<?php esc_attr_e( 'Switch to dark theme', 'nolasharon' ); ?>" data-label-light="<?php esc_attr_e( 'Switch to light theme', 'nolasharon' ); ?>"><?php esc_html_e( 'Theme', 'nolasharon' ); ?> <?php echo nolasharon_icon( 'sun', 14 ) . nolasharon_icon( 'moon', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</p>
			<p>
				<a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : nolasharon_page_url( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'nolasharon' ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( nolasharon_page_url( 'our-policies' ) ); ?>"><?php esc_html_e( 'Our Policies', 'nolasharon' ); ?></a>
			</p>
		</div>
	</div>
</footer>

<a class="whatsapp-float" href="<?php echo esc_url( nolasharon_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'nolasharon' ); ?>">
	<?php echo nolasharon_icon( 'whatsapp', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</a>

<?php wp_footer(); ?>
</body>
</html>

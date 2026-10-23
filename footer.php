<?php
/**
 * Site footer (brief §4 J) and the floating WhatsApp button.
 * Contact rows and social links only appear once they are set in the Customizer.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_phone    = nolasharon_setting( 'nolasharon_phone' );
$nolasharon_email    = nolasharon_setting( 'nolasharon_email' );
$nolasharon_whatsapp = nolasharon_whatsapp_url();
$nolasharon_branches = function_exists( 'nolasharon_get_branches' ) ? nolasharon_get_branches( false ) : array();
$nolasharon_cats     = function_exists( 'nolasharon_nav_categories' ) ? nolasharon_nav_categories() : array();

$nolasharon_shop_links = array( __( 'Shop all', 'nolasharon' ) => nolasharon_shop_url() );
foreach ( $nolasharon_cats as $nolasharon_cat ) {
	$nolasharon_shop_links[ $nolasharon_cat->name ] = get_term_link( $nolasharon_cat );
}

$nolasharon_columns = array(
	__( 'Shop', 'nolasharon' )    => $nolasharon_shop_links,
	__( 'Help', 'nolasharon' )    => array(
		__( 'FAQs', 'nolasharon' )                => nolasharon_page_url( 'faqs' ),
		__( 'Delivery Policy', 'nolasharon' )     => nolasharon_page_url( 'delivery-policy' ),
		__( 'Returns & Exchange', 'nolasharon' )  => nolasharon_page_url( 'returns-policy' ),
		__( 'Size Guide', 'nolasharon' )          => nolasharon_page_url( 'size-guide' ),
		__( 'Contact Us', 'nolasharon' )          => nolasharon_page_url( 'contact' ),
	),
	__( 'Visit Us', 'nolasharon' ) => array(),
	__( 'Contact', 'nolasharon' )  => array(
		$nolasharon_phone ? $nolasharon_phone : __( 'Call us', 'nolasharon' ) => nolasharon_tel_url( $nolasharon_phone ),
		__( 'WhatsApp', 'nolasharon' )  => $nolasharon_whatsapp,
		$nolasharon_email ? $nolasharon_email : __( 'Email', 'nolasharon' ) => $nolasharon_email ? 'mailto:' . $nolasharon_email : '',
		__( 'Instagram', 'nolasharon' ) => nolasharon_setting( 'nolasharon_instagram' ),
		__( 'Facebook', 'nolasharon' )  => nolasharon_setting( 'nolasharon_facebook' ),
		__( 'TikTok', 'nolasharon' )    => nolasharon_setting( 'nolasharon_tiktok' ),
	),
);
foreach ( $nolasharon_branches as $nolasharon_branch ) {
	$nolasharon_label = $nolasharon_branch['name'];
	if ( 'opening_soon' === $nolasharon_branch['status'] ) {
		$nolasharon_label .= ' · ' . __( 'opening soon', 'nolasharon' );
	}
	$nolasharon_columns[ __( 'Visit Us', 'nolasharon' ) ][ $nolasharon_label ] = nolasharon_page_url( 'store-locations' ) . '#branch-' . $nolasharon_branch['id'];
}
if ( ! $nolasharon_columns[ __( 'Visit Us', 'nolasharon' ) ] ) {
	$nolasharon_columns[ __( 'Visit Us', 'nolasharon' ) ][ __( 'Store Locations', 'nolasharon' ) ] = nolasharon_page_url( 'store-locations' );
}
?>
</main>

<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php nolasharon_logo( 'span' ); ?>
				<!-- DRAFT: client to approve -->
				<p class="site-footer__line"><?php esc_html_e( 'Fashion for women and men. Shop online or visit us in Lagos.', 'nolasharon' ); ?></p>
			</div>
			<nav class="site-footer__cols" aria-label="<?php esc_attr_e( 'Footer', 'nolasharon' ); ?>">
				<?php foreach ( $nolasharon_columns as $nolasharon_heading => $nolasharon_links ) : ?>
					<?php $nolasharon_links = array_filter( $nolasharon_links ); ?>
					<?php if ( ! $nolasharon_links ) : ?>
						<?php continue; ?>
					<?php endif; ?>
					<div>
						<h2 class="site-footer__heading"><?php echo esc_html( $nolasharon_heading ); ?></h2>
						<ul>
							<?php
							foreach ( $nolasharon_links as $nolasharon_label => $nolasharon_url ) :
								$nolasharon_external = 0 === strpos( $nolasharon_url, 'http' ) && false === strpos( $nolasharon_url, home_url() );
								?>
								<li><a href="<?php echo esc_url( $nolasharon_url, array( 'http', 'https', 'tel', 'mailto' ) ); ?>"<?php echo $nolasharon_external ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $nolasharon_label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</nav>
		</div>
		<div class="site-footer__bottom">
			<p>
				&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php esc_html_e( 'NolaSharon Clothing', 'nolasharon' ); ?>
				<span aria-hidden="true">|</span>
				<button class="footer-theme-toggle theme-toggle" type="button" aria-label="<?php esc_attr_e( 'Switch to dark theme', 'nolasharon' ); ?>" data-label-dark="<?php esc_attr_e( 'Switch to dark theme', 'nolasharon' ); ?>" data-label-light="<?php esc_attr_e( 'Switch to light theme', 'nolasharon' ); ?>"><?php esc_html_e( 'Theme', 'nolasharon' ); ?> <?php echo nolasharon_icon( 'sun', 14 ) . nolasharon_icon( 'moon', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
			</p>
			<p>
				<a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : nolasharon_page_url( 'privacy-policy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'nolasharon' ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( nolasharon_page_url( 'terms-and-conditions' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'nolasharon' ); ?></a>
			</p>
		</div>
	</div>
</footer>

<?php
// Floating WhatsApp button: only once a verified number is set (brief rule 5).
if ( $nolasharon_whatsapp ) :
	$nolasharon_wa_href = $nolasharon_whatsapp;
	if ( function_exists( 'is_product' ) && is_product() ) {
		/* translators: 1: product name, 2: product URL. */
		$nolasharon_wa_href = nolasharon_whatsapp_url( sprintf( __( "Hi, I'm interested in %1\$s (%2\$s)", 'nolasharon' ), get_the_title(), get_permalink() ) );
	}
	?>
	<a class="whatsapp-float" href="<?php echo esc_url( $nolasharon_wa_href ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', 'nolasharon' ); ?>">
		<?php echo nolasharon_icon( 'whatsapp', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>

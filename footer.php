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
$nolasharon_branches = function_exists( 'ns_get_branches' ) ? ns_get_branches() : array();
$nolasharon_cats     = function_exists( 'nolasharon_nav_categories' ) ? nolasharon_nav_categories() : array();

$nolasharon_shop_links = array(
	__( 'All products', 'nolasharon' ) => nolasharon_shop_url(),
	__( 'New arrivals', 'nolasharon' ) => add_query_arg( 'orderby', 'date', nolasharon_shop_url() ),
);
foreach ( $nolasharon_cats as $nolasharon_cat ) {
	$nolasharon_shop_links[ $nolasharon_cat->name ] = get_term_link( $nolasharon_cat );
}

$nolasharon_columns = array(
	__( 'Shop', 'nolasharon' )     => $nolasharon_shop_links,
	__( 'Help', 'nolasharon' )     => array(
		__( 'FAQs', 'nolasharon' )               => nolasharon_page_url( 'faqs' ),
		__( 'Delivery policy', 'nolasharon' )    => nolasharon_policy_url( 'delivery' ),
		__( 'Returns & exchange', 'nolasharon' ) => nolasharon_policy_url( 'returns' ),
		__( 'Track your order', 'nolasharon' )   => nolasharon_page_url( 'track-order' ),
	),
	__( 'Visit us', 'nolasharon' ) => array(),
	__( 'Contact', 'nolasharon' )  => array(
		__( 'Contact us', 'nolasharon' ) => nolasharon_page_url( 'contact' ),
		__( 'WhatsApp', 'nolasharon' )   => $nolasharon_whatsapp,
		$nolasharon_phone ? $nolasharon_phone : __( 'Call us', 'nolasharon' ) => nolasharon_tel_url( $nolasharon_phone ),
		$nolasharon_email ? $nolasharon_email : __( 'Email', 'nolasharon' ) => $nolasharon_email ? 'mailto:' . $nolasharon_email : '',
		__( 'Instagram', 'nolasharon' )  => nolasharon_setting( 'nolasharon_instagram' ),
		__( 'Facebook', 'nolasharon' )   => nolasharon_setting( 'nolasharon_facebook' ),
		__( 'TikTok', 'nolasharon' )     => nolasharon_setting( 'nolasharon_tiktok' ),
	),
);
foreach ( $nolasharon_branches as $nolasharon_branch ) {
	// e.g. "Ikeja (Branch 1)", "Ogudu (New)".
	$nolasharon_label = $nolasharon_branch['area'] ? $nolasharon_branch['area'] : $nolasharon_branch['name'];
	if ( $nolasharon_branch['tag'] ) {
		$nolasharon_label .= ' (' . $nolasharon_branch['tag'] . ')';
	}
	$nolasharon_columns[ __( 'Visit us', 'nolasharon' ) ][ $nolasharon_label ] = ns_branch_url( $nolasharon_branch );
}
if ( ! $nolasharon_columns[ __( 'Visit us', 'nolasharon' ) ] ) {
	$nolasharon_columns[ __( 'Visit us', 'nolasharon' ) ][ __( 'Store Locations', 'nolasharon' ) ] = nolasharon_page_url( 'store-locations' );
}
?>
</main>

<?php if ( ! ( function_exists( 'nolasharon_is_auth_page' ) && nolasharon_is_auth_page() ) ) : ?>
<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__top">
			<div class="site-footer__brand">
				<?php nolasharon_logo( 'span', 'full' ); ?>
				<?php if ( nolasharon_setting( 'nolasharon_footer_line' ) ) : ?>
					<p class="site-footer__line"><?php echo esc_html( nolasharon_setting( 'nolasharon_footer_line' ) ); ?></p>
				<?php endif; ?>
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
			</p>
			<p>
				<a href="<?php echo esc_url( nolasharon_policy_url( 'privacy' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'nolasharon' ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( nolasharon_policy_url( 'terms' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'nolasharon' ); ?></a>
			</p>
		</div>
	</div>
</footer>
<?php endif; ?>

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

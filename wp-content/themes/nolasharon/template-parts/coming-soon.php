<?php
/**
 * Coming soon page (a complete document; no shop header or footer). Text, launch date and
 * background come from Customizer → NolaSharon Settings → Coming soon page; contact links
 * and branches from the usual settings. Anything not set is left out.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_logo_id  = (int) get_theme_mod( 'custom_logo' );
$nolasharon_launch   = nolasharon_coming_soon_launch();
$nolasharon_bg       = (int) nolasharon_setting( 'nolasharon_cs_image' );
$nolasharon_channels = function_exists( 'nolasharon_contact_channels' ) ? nolasharon_contact_channels() : array();
$nolasharon_socials  = function_exists( 'nolasharon_contact_socials' ) ? array_filter(
	nolasharon_contact_socials(),
	function ( $s ) {
		return 0 === strpos( $s['url'], 'http' );
	}
) : array();
$nolasharon_branches = array();
if ( nolasharon_setting( 'nolasharon_cs_stores' ) && function_exists( 'ns_get_branches' ) ) {
	$nolasharon_branches = array_filter(
		ns_get_branches(),
		function ( $b ) {
			return in_array( $b['status'], array( 'open', 'opening_soon' ), true );
		}
	);
}
$nolasharon_has_new = (bool) array_filter( wp_list_pluck( $nolasharon_branches, 'is_new' ) );
$nolasharon_css     = get_template_directory_uri() . '/assets/css/coming-soon.css';
$nolasharon_js      = get_template_directory_uri() . '/assets/js/coming-soon.js';
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex">
	<title><?php echo esc_html( nolasharon_setting( 'nolasharon_cs_eyebrow' ) . ' · ' . get_bloginfo( 'name' ) ); ?></title>
	<meta name="description" content="<?php echo esc_attr( nolasharon_setting( 'nolasharon_cs_text' ) ); ?>">
	<?php if ( function_exists( 'nolasharon_site_icons' ) ) { nolasharon_site_icons(); } ?>
	<?php if ( has_site_icon() ) { wp_site_icon(); } ?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;1,500&family=Poppins:wght@400;500;600&display=swap">
	<link rel="stylesheet" href="<?php echo esc_url( add_query_arg( 'ver', NOLASHARON_VERSION, $nolasharon_css ) ); ?>">
	<script>document.documentElement.classList.add('ns-js');</script>
</head>
<body class="cs">
	<div class="cs__bg" aria-hidden="true">
		<?php if ( $nolasharon_bg ) : ?>
			<?php echo wp_get_attachment_image( $nolasharon_bg, 'full', false, array( 'class' => 'cs__photo', 'alt' => '', 'decoding' => 'async' ) ); ?>
		<?php endif; ?>
		<span class="cs__glow cs__glow--1"></span>
		<span class="cs__glow cs__glow--2"></span>
		<span class="cs__ring cs__ring--1"></span>
		<span class="cs__ring cs__ring--2"></span>
		<span class="cs__sparkles" data-cs-sparkles></span>
	</div>

	<main class="cs__main">
		<?php $nolasharon_logo_src = $nolasharon_logo_id ? wp_get_attachment_image_url( $nolasharon_logo_id, 'medium' ) : nolasharon_logo_url( 'logo-full.webp' ); ?>
		<div class="cs__logo" style="--logo: url('<?php echo esc_url( $nolasharon_logo_src ); ?>')">
			<?php
			if ( $nolasharon_logo_id ) {
				echo wp_get_attachment_image( $nolasharon_logo_id, 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) );
			} else {
				printf( '<img src="%1$s" alt="%2$s" width="291" height="260">', esc_url( $nolasharon_logo_src ), esc_attr( get_bloginfo( 'name' ) ) );
			}
			?>
			<span class="cs__shine" aria-hidden="true"></span>
		</div>

		<?php if ( nolasharon_setting( 'nolasharon_cs_eyebrow' ) ) : ?>
			<p class="cs__eyebrow cs-in" style="--d: .5s"><span aria-hidden="true"></span><?php echo esc_html( nolasharon_setting( 'nolasharon_cs_eyebrow' ) ); ?><span aria-hidden="true"></span></p>
		<?php endif; ?>
		<h1 class="cs__title">
			<?php
			// Each word rises in turn.
			foreach ( preg_split( '/\s+/', trim( (string) nolasharon_setting( 'nolasharon_cs_title' ) ) ) as $nolasharon_w => $nolasharon_word ) {
				printf( '<span class="cs__word" style="--i: %1$d"><span>%2$s</span></span> ', (int) $nolasharon_w, esc_html( $nolasharon_word ) );
			}
			?>
		</h1>
		<?php if ( nolasharon_setting( 'nolasharon_cs_text' ) ) : ?>
			<p class="cs__text cs-in" style="--d: 1.1s"><?php echo nl2br( esc_html( nolasharon_setting( 'nolasharon_cs_text' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped first. ?></p>
		<?php endif; ?>

		<?php if ( $nolasharon_launch > time() ) : ?>
			<div class="cs__count cs-in" style="--d: 1.3s" data-cs-launch="<?php echo esc_attr( $nolasharon_launch ); ?>" role="timer" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: launch date and time. */ __( 'Launching %s', 'nolasharon' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $nolasharon_launch ) ) ); ?>">
				<?php
				foreach ( array(
					'd' => __( 'Days', 'nolasharon' ),
					'h' => __( 'Hours', 'nolasharon' ),
					'm' => __( 'Minutes', 'nolasharon' ),
					's' => __( 'Seconds', 'nolasharon' ),
				) as $nolasharon_unit => $nolasharon_label ) :
					?>
					<div class="cs__unit">
						<span class="cs__num" data-cs-<?php echo esc_attr( $nolasharon_unit ); ?> aria-hidden="true">00</span>
						<span class="cs__label" aria-hidden="true"><?php echo esc_html( $nolasharon_label ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="cs__date cs-in" style="--d: 1.4s"><?php echo esc_html( sprintf( /* translators: %s: launch date. */ __( 'Opening %s', 'nolasharon' ), wp_date( 'l j F Y', $nolasharon_launch ) ) ); ?></p>
		<?php endif; ?>

		<?php if ( $nolasharon_channels || $nolasharon_socials ) : ?>
			<div class="cs__actions cs-in" style="--d: 1.5s">
				<?php foreach ( $nolasharon_channels as $nolasharon_c ) : ?>
					<a class="cs__btn<?php echo 'whatsapp' === $nolasharon_c['key'] ? ' cs__btn--wa' : ''; ?>" href="<?php echo esc_url( $nolasharon_c['url'], array( 'https', 'http', 'tel', 'mailto' ) ); ?>"<?php echo $nolasharon_c['external'] ? ' target="_blank" rel="noopener"' : ''; ?>>
						<?php echo nolasharon_icon( $nolasharon_c['icon'], 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( 'whatsapp' === $nolasharon_c['key'] ? __( 'Chat on WhatsApp', 'nolasharon' ) : $nolasharon_c['value'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
			<?php if ( $nolasharon_socials ) : ?>
				<p class="cs__socials cs-in" style="--d: 1.6s">
					<?php foreach ( $nolasharon_socials as $nolasharon_s ) : ?>
						<a href="<?php echo esc_url( $nolasharon_s['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $nolasharon_s['label'] ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $nolasharon_branches ) : ?>
			<section class="cs__stores cs-in" style="--d: 1.7s" aria-labelledby="cs-stores">
				<h2 class="cs__stores-title" id="cs-stores"><?php esc_html_e( 'Visit us in store', 'nolasharon' ); ?></h2>
				<?php if ( $nolasharon_has_new ) : ?>
					<?php // DRAFT: client to approve. Ogudu is an additional branch. ?>
					<p class="cs__stores-note"><?php esc_html_e( 'Our Ikeja stores remain open, and Ogudu is our newest branch.', 'nolasharon' ); ?></p>
				<?php endif; ?>
				<ul class="cs__store-list">
					<?php foreach ( $nolasharon_branches as $nolasharon_b ) : ?>
						<?php
						$nolasharon_where = $nolasharon_b['address'] ? preg_replace( '/\s*\n\s*/', ', ', $nolasharon_b['address'] ) : $nolasharon_b['area'];
						$nolasharon_dir   = function_exists( 'ns_branch_directions_url' ) ? ns_branch_directions_url( $nolasharon_b ) : '';
						?>
						<li class="cs__store">
							<span class="cs__store-name"><?php echo esc_html( $nolasharon_b['name'] ); ?>
								<?php if ( 'opening_soon' === $nolasharon_b['status'] ) : ?>
									<span class="cs__store-tag"><?php esc_html_e( 'Opening soon', 'nolasharon' ); ?></span>
								<?php endif; ?>
							</span>
							<?php if ( $nolasharon_where ) : ?>
								<span class="cs__store-where"><?php echo esc_html( $nolasharon_where ); ?></span>
							<?php endif; ?>
							<?php if ( $nolasharon_dir && 'open' === $nolasharon_b['status'] ) : ?>
								<a class="cs__store-dir" href="<?php echo esc_url( $nolasharon_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'nolasharon' ); ?> <span aria-hidden="true">→</span></a>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>
	</main>

	<footer class="cs__foot cs-in" style="--d: 1.9s">
		<?php echo esc_html( str_replace( '{year}', wp_date( 'Y' ), (string) nolasharon_setting( 'nolasharon_copyright' ) ) ); ?>
	</footer>

	<script src="<?php echo esc_url( add_query_arg( 'ver', NOLASHARON_VERSION, $nolasharon_js ) ); ?>" defer></script>
</body>
</html>

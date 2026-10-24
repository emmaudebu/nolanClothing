<?php
/**
 * Homepage store spotlight (TRACK-ORDER.md Part B §H, brief §4 H): the new branch in a dark
 * card, always saying the Ikeja stores remain open, and every published branch as a row
 * linking to its card on Store Locations. Ogudu is an additional branch, never a move.
 *
 * Args: branch (the branch marked new), branches (all branches to list).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_b    = $args['branch'];
$nolasharon_area = $nolasharon_b['area'] ? $nolasharon_b['area'] : $nolasharon_b['name'];
$nolasharon_when = '';
if ( 'opening_soon' === $nolasharon_b['status'] && $nolasharon_b['opening_date'] ) {
	/* translators: %s: opening date. */
	$nolasharon_when = sprintf( __( 'Opening %s', 'nolasharon' ), wp_date( get_option( 'date_format' ), strtotime( $nolasharon_b['opening_date'] . ' 12:00' ) ) );
} elseif ( 'open' === $nolasharon_b['status'] ) {
	$nolasharon_when = __( 'Now open', 'nolasharon' );
}
?>
<section class="hspot" id="spotlight" aria-labelledby="hspot-title">
	<div class="container">
		<div class="hspot__card">
			<span class="hspot__circle" aria-hidden="true"></span>
			<span class="hspot__ring" aria-hidden="true"></span>
			<div class="hspot__copy">
				<?php if ( $nolasharon_b['is_draft'] ) : ?>
					<span class="tag tag--draft"><?php esc_html_e( 'Draft branch: only editors see this section', 'nolasharon' ); ?></span>
				<?php endif; ?>
				<span class="hspot__pill"><span aria-hidden="true"></span><?php esc_html_e( 'New branch', 'nolasharon' ); ?></span>
				<h2 class="hspot__title" id="hspot-title"><?php echo esc_html( sprintf( /* translators: %s: area, e.g. Ogudu. */ __( 'Now also in %s', 'nolasharon' ), $nolasharon_area ) ); ?></h2>
				<?php // DRAFT: client to approve. Must keep the "Ikeja remains open" meaning. ?>
				<p class="hspot__text"><?php echo esc_html( sprintf( /* translators: %s: area, e.g. Ogudu. */ __( 'Our Ikeja stores remain open, and %s is our newest location. Visit whichever is closest to you.', 'nolasharon' ), $nolasharon_area ) ); ?></p>
				<?php if ( $nolasharon_when ) : ?>
					<p class="hspot__when"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><?php echo esc_html( $nolasharon_when ); ?></p>
				<?php endif; ?>
				<a class="hspot__btn" href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) ); ?>"><?php esc_html_e( 'Visit Our Store', 'nolasharon' ); ?><?php echo nolasharon_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
			</div>
			<?php if ( $args['branches'] ) : ?>
				<ul class="hspot__list" aria-label="<?php esc_attr_e( 'Our stores', 'nolasharon' ); ?>">
					<?php foreach ( $args['branches'] as $nolasharon_item ) : ?>
						<?php
						$nolasharon_status = ns_branch_status( $nolasharon_item );
						$nolasharon_where  = $nolasharon_item['address'] ? preg_replace( '/\s*\n\s*/', ', ', $nolasharon_item['address'] ) : $nolasharon_item['area'];
						?>
						<li>
							<a class="hspot__row" href="<?php echo esc_url( ns_branch_url( $nolasharon_item ) ); ?>">
								<span class="hspot__icon" aria-hidden="true"><?php echo nolasharon_icon( 'store', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="hspot__info">
									<span class="hspot__name"><?php echo esc_html( $nolasharon_item['name'] ); ?>
										<?php if ( $nolasharon_item['tag'] || $nolasharon_item['is_new'] ) : ?>
											<span class="hspot__tag<?php echo $nolasharon_item['is_new'] ? ' is-new' : ''; ?>"><?php echo esc_html( $nolasharon_item['tag'] ? $nolasharon_item['tag'] : __( 'New', 'nolasharon' ) ); ?></span>
										<?php endif; ?>
									</span>
									<span class="hspot__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $nolasharon_status['label'], $nolasharon_where ) ) ) ); ?></span>
								</span>
								<?php echo nolasharon_icon( 'next', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>

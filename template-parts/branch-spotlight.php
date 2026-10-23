<?php
/**
 * New-branch spotlight (LOCATIONS.md §1.4, brief §4 H): "Now also in {area}", always saying
 * the Ikeja stores remain open. Ogudu is an additional branch, never a move.
 *
 * Args: branch (the is_new branch), context ("locations" selects it on the map; "home" links
 * to the Store Locations page and lists every branch), branches (for the home list), map
 * (bool: the Store Locations page has a map).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_b          = $args['branch'];
$nolasharon_home       = isset( $args['context'] ) && 'home' === $args['context'];
$nolasharon_area       = $nolasharon_b['area'] ? $nolasharon_b['area'] : $nolasharon_b['name'];
$nolasharon_directions = ns_branch_directions_url( $nolasharon_b );

if ( 'opening_soon' === $nolasharon_b['status'] ) {
	$nolasharon_when = $nolasharon_b['opening_date']
		/* translators: %s: opening date. */
		? sprintf( __( 'Opening %s', 'nolasharon' ), wp_date( get_option( 'date_format' ), strtotime( $nolasharon_b['opening_date'] . ' 12:00' ) ) )
		: '';
} elseif ( 'temporarily_closed' === $nolasharon_b['status'] ) {
	$nolasharon_when = __( 'Temporarily closed', 'nolasharon' );
} else {
	$nolasharon_when = __( 'Now open', 'nolasharon' );
}
?>
<section class="loc-spot<?php echo $nolasharon_home ? ' loc-spot--home section' : ''; ?>" id="<?php echo esc_attr( $nolasharon_home ? 'spotlight' : sanitize_title( $nolasharon_area ) ); ?>" aria-labelledby="loc-spot-title">
	<div class="container">
		<div class="loc-spot__card loc-in loc-in--2">
			<div class="loc-spot__copy">
				<?php if ( $nolasharon_b['is_draft'] ) : ?>
					<span class="tag tag--draft"><?php esc_html_e( 'Draft branch: only editors see this section', 'nolasharon' ); ?></span>
				<?php endif; ?>
				<span class="loc-spot__pill"><span aria-hidden="true"></span><?php esc_html_e( 'New branch', 'nolasharon' ); ?></span>
				<h2 class="loc-spot__title" id="loc-spot-title"><?php echo esc_html( sprintf( /* translators: %s: area, e.g. Ogudu. */ __( 'Now also in %s', 'nolasharon' ), $nolasharon_area ) ); ?></h2>
				<?php // DRAFT: client to approve. ?>
				<p class="loc-spot__text"><?php echo esc_html( sprintf( /* translators: %s: area, e.g. Ogudu. */ __( 'We’re growing. Our new %s store adds to our Ikeja locations, which remain open. Visit us at whichever is closest to you.', 'nolasharon' ), $nolasharon_area ) ); ?></p>
				<ul class="loc-spot__meta">
					<?php if ( $nolasharon_when ) : ?>
						<li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><?php echo esc_html( $nolasharon_when ); ?></li>
					<?php endif; ?>
					<li><?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $nolasharon_area . ', ' . __( 'Lagos', 'nolasharon' ) ); ?><?php echo $nolasharon_b['address'] ? esc_html( ' · ' . preg_replace( '/\s*\n\s*/', ', ', $nolasharon_b['address'] ) ) : ''; ?></span></li>
				</ul>
				<div class="loc-spot__actions">
					<?php if ( $nolasharon_home ) : ?>
						<a class="loc-spot__btn" href="<?php echo esc_url( ns_branch_url( $nolasharon_b ) ); ?>"><?php esc_html_e( 'Visit Our Store', 'nolasharon' ); ?><?php echo nolasharon_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php elseif ( ! empty( $args['map'] ) && $nolasharon_b['has_coords'] ) : ?>
						<button type="button" class="loc-spot__btn" data-select-branch="<?php echo esc_attr( $nolasharon_b['slug'] ); ?>" data-scroll-map><?php esc_html_e( 'View on map', 'nolasharon' ); ?><?php echo nolasharon_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<?php else : ?>
						<a class="loc-spot__btn" href="#<?php echo esc_attr( $nolasharon_b['anchor'] ); ?>"><?php esc_html_e( 'See store details', 'nolasharon' ); ?><?php echo nolasharon_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
					<?php endif; ?>
					<?php if ( $nolasharon_directions ) : ?>
						<a class="loc-spot__btn loc-spot__btn--ghost" href="<?php echo esc_url( $nolasharon_directions ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
					<?php endif; ?>
				</div>
				<?php if ( $nolasharon_home && ! empty( $args['branches'] ) && count( $args['branches'] ) > 1 ) : ?>
					<ul class="loc-spot__list" aria-label="<?php esc_attr_e( 'All our stores', 'nolasharon' ); ?>">
						<?php foreach ( $args['branches'] as $nolasharon_item ) : ?>
							<?php $nolasharon_item_status = ns_branch_status( $nolasharon_item ); ?>
							<li>
								<a href="<?php echo esc_url( ns_branch_url( $nolasharon_item ) ); ?>"><?php echo esc_html( ns_branch_label( $nolasharon_item ) ); ?></a>
								<span>· <?php echo esc_html( $nolasharon_item_status['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<div class="loc-spot__media">
				<?php if ( $nolasharon_b['image_id'] ) : ?>
					<?php echo wp_get_attachment_image( $nolasharon_b['image_id'], 'large', false, array( 'alt' => '', 'class' => 'loc-spot__img' ) ); ?>
				<?php else : ?>
					<span class="loc-spot__ring loc-spot__ring--lg" aria-hidden="true"></span>
					<span class="loc-spot__ring" aria-hidden="true"></span>
					<span class="loc-spot__store" aria-hidden="true">
						<span class="loc-spot__awning"></span>
						<span class="loc-spot__sign">
							<span class="loc-spot__brand">NolaSharon</span>
							<span class="loc-spot__area"><?php echo esc_html( $nolasharon_area ); ?></span>
							<span class="loc-spot__door"></span>
						</span>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

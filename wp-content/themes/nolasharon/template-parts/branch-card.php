<?php
/**
 * One branch (LOCATIONS.md §3). Every detail row is hidden while its field is empty.
 *
 * Args: branch (ns_branch_data()), variant ("full" on Store Locations, "compact" on the
 * Contact page), map (bool: the page has a map, so the card gets a pin button).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_b          = $args['branch'];
$nolasharon_full       = ! isset( $args['variant'] ) || 'compact' !== $args['variant'];
$nolasharon_map        = ! empty( $args['map'] ) && $nolasharon_b['has_coords'];
$nolasharon_status     = ns_branch_status( $nolasharon_b );
$nolasharon_directions = ns_branch_directions_url( $nolasharon_b );
$nolasharon_hours      = ns_branch_hours_rows( $nolasharon_b );
$nolasharon_pickup     = nolasharon_setting( 'nolasharon_pickup_enabled' ) && ! $nolasharon_b['is_draft'];
$nolasharon_label      = ns_branch_label( $nolasharon_b );
$nolasharon_has_rows   = $nolasharon_b['address'] || $nolasharon_b['landmark'] || $nolasharon_b['phone'] || $nolasharon_pickup;
$nolasharon_live       = 'open' === $nolasharon_b['status'] && $nolasharon_b['has_hours'];
?>
<article class="bcard<?php echo $nolasharon_full ? '' : ' bcard--compact'; ?>" id="<?php echo esc_attr( $nolasharon_b['anchor'] ); ?>" data-branch="<?php echo esc_attr( $nolasharon_b['slug'] ); ?>"<?php echo $nolasharon_b['is_new'] ? ' data-new' : ''; ?><?php echo $nolasharon_live ? ' data-hours="' . esc_attr( wp_json_encode( $nolasharon_b['hours'] ) ) . '"' : ''; ?>>
	<div class="bcard__head">
		<span class="bcard__icon" aria-hidden="true"><?php echo nolasharon_icon( 'store', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<div class="bcard__title">
			<div class="bcard__name-row">
				<h3 class="bcard__name"><?php echo esc_html( $nolasharon_b['name'] ); ?></h3>
				<?php if ( $nolasharon_b['tag'] ) : ?>
					<span class="bcard__tag<?php echo $nolasharon_b['is_new'] ? ' bcard__tag--new' : ''; ?>"><?php echo esc_html( $nolasharon_b['tag'] ); ?></span>
				<?php elseif ( $nolasharon_b['is_new'] ) : ?>
					<span class="bcard__tag bcard__tag--new"><?php esc_html_e( 'New', 'nolasharon' ); ?></span>
				<?php endif; ?>
			</div>
			<p class="bcard__status bcard__status--<?php echo esc_attr( $nolasharon_status['tone'] ); ?>" data-status-line>
				<span class="bcard__dot" aria-hidden="true"></span><span data-status-text><?php echo esc_html( $nolasharon_status['label'] ); ?></span>
			</p>
			<?php if ( $nolasharon_b['is_draft'] ) : ?>
				<span class="tag tag--draft"><?php esc_html_e( 'Draft: only editors see this', 'nolasharon' ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $nolasharon_map ) : ?>
			<button type="button" class="bcard__pin" data-select-branch="<?php echo esc_attr( $nolasharon_b['slug'] ); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: branch name and tag. */ __( 'Show %s on map', 'nolasharon' ), str_replace( ' · ', ' ', $nolasharon_label ) ) ); ?>">
				<?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( $nolasharon_has_rows ) : ?>
		<dl class="bcard__rows">
			<?php if ( $nolasharon_b['address'] ) : ?>
				<div class="bcard__row"><?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><div><dt><?php esc_html_e( 'Address', 'nolasharon' ); ?></dt><dd><?php echo nl2br( esc_html( $nolasharon_b['address'] ) ); ?></dd></div></div>
			<?php endif; ?>
			<?php if ( $nolasharon_full && $nolasharon_b['landmark'] ) : ?>
				<div class="bcard__row"><?php echo nolasharon_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><div><dt><?php esc_html_e( 'Nearest landmark', 'nolasharon' ); ?></dt><dd><?php echo esc_html( $nolasharon_b['landmark'] ); ?></dd></div></div>
			<?php endif; ?>
			<?php if ( $nolasharon_b['phone'] ) : ?>
				<div class="bcard__row"><?php echo nolasharon_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><div><dt><?php esc_html_e( 'Phone', 'nolasharon' ); ?></dt><dd><a href="<?php echo esc_url( $nolasharon_b['tel'], array( 'tel' ) ); ?>"><?php echo esc_html( $nolasharon_b['phone'] ); ?></a></dd></div></div>
			<?php endif; ?>
			<?php if ( $nolasharon_full && $nolasharon_pickup ) : ?>
				<div class="bcard__row"><?php echo nolasharon_icon( 'cart', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><div><dt><?php esc_html_e( 'Order pickup', 'nolasharon' ); ?></dt><dd>
					<?php echo $nolasharon_b['pickup_enabled'] ? esc_html__( 'Available', 'nolasharon' ) : esc_html__( 'Not available', 'nolasharon' ); ?>
					<?php if ( $nolasharon_b['pickup_enabled'] && $nolasharon_b['pickup_instructions'] ) : ?>
						<span class="bcard__sub"><?php echo nl2br( esc_html( $nolasharon_b['pickup_instructions'] ) ); ?></span>
					<?php endif; ?>
				</dd></div></div>
			<?php endif; ?>
		</dl>
	<?php elseif ( ! $nolasharon_hours ) : ?>
		<p class="bcard__pending"><?php esc_html_e( 'Full details coming soon.', 'nolasharon' ); ?></p>
	<?php endif; ?>

	<?php if ( $nolasharon_full && $nolasharon_hours ) : ?>
		<?php $nolasharon_hid = 'hours-' . $nolasharon_b['slug']; ?>
		<div class="bcard__hours">
			<button type="button" class="bcard__hours-toggle" aria-expanded="true" aria-controls="<?php echo esc_attr( $nolasharon_hid ); ?>" data-hours-toggle>
				<span><?php echo nolasharon_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Opening hours', 'nolasharon' ); ?></span>
				<?php echo nolasharon_icon( 'chevron', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<div class="bcard__hours-panel" id="<?php echo esc_attr( $nolasharon_hid ); ?>">
				<div class="bcard__hours-inner">
					<dl class="bcard__hours-list">
						<?php foreach ( $nolasharon_hours as $nolasharon_row ) : ?>
							<dt><?php echo esc_html( $nolasharon_row['days'] ); ?></dt>
							<dd<?php echo $nolasharon_row['closed'] ? ' class="is-closed"' : ''; ?>><?php echo esc_html( $nolasharon_row['time'] ); ?></dd>
						<?php endforeach; ?>
					</dl>
					<?php if ( $nolasharon_b['hours_note'] ) : ?>
						<p class="bcard__hours-note"><?php echo esc_html( $nolasharon_b['hours_note'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $nolasharon_directions || $nolasharon_b['tel'] ) : ?>
		<div class="bcard__actions">
			<?php if ( $nolasharon_directions ) : ?>
				<a class="bcard__btn bcard__btn--dark" href="<?php echo esc_url( $nolasharon_directions ); ?>" target="_blank" rel="noopener">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M3 11l18-8-8 18-2-8z"/></svg>
					<?php esc_html_e( 'Get directions', 'nolasharon' ); ?><span class="screen-reader-text"> <?php echo esc_html( sprintf( /* translators: %s: branch name. */ __( 'to %s (opens in a new tab)', 'nolasharon' ), $nolasharon_label ) ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $nolasharon_full && $nolasharon_b['tel'] ) : ?>
				<a class="bcard__btn bcard__btn--ghost" href="<?php echo esc_url( $nolasharon_b['tel'], array( 'tel' ) ); ?>"><?php echo nolasharon_icon( 'phone', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Call store', 'nolasharon' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</article>

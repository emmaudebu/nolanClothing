<?php
/**
 * One branch listing. Every row is hidden while its field is empty.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_b          = $args['branch'];
$nolasharon_status     = nolasharon_branch_status_label( $nolasharon_b );
$nolasharon_directions = nolasharon_branch_directions_url( $nolasharon_b );
$nolasharon_tel        = nolasharon_tel_url( $nolasharon_b['phone'] );
$nolasharon_embed      = $nolasharon_b['map_embed'] && preg_match( '~^https://(www\.)?google\.[a-z.]+/maps/embed~', $nolasharon_b['map_embed'] ) ? $nolasharon_b['map_embed'] : '';
?>
<article class="branch-card<?php echo $nolasharon_b['is_new'] ? ' branch-card--new' : ''; ?>" id="branch-<?php echo esc_attr( $nolasharon_b['id'] ); ?>">
	<div class="branch-card__head">
		<h2 class="branch-card__name"><?php echo esc_html( $nolasharon_b['name'] ); ?></h2>
		<div class="branch-card__tags">
			<?php if ( $nolasharon_b['is_new'] ) : ?>
				<span class="tag tag--new"><?php esc_html_e( 'New', 'nolasharon' ); ?></span>
			<?php endif; ?>
			<?php if ( $nolasharon_status ) : ?>
				<span class="tag"><?php echo esc_html( $nolasharon_status ); ?></span>
			<?php endif; ?>
			<?php if ( $nolasharon_b['is_draft'] ) : ?>
				<span class="tag tag--draft"><?php esc_html_e( 'Draft: only editors see this', 'nolasharon' ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<dl class="branch-card__rows">
		<?php if ( $nolasharon_b['address'] ) : ?>
			<div><dt><?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Address', 'nolasharon' ); ?></dt><dd><?php echo nl2br( esc_html( $nolasharon_b['address'] ) ); ?></dd></div>
		<?php endif; ?>
		<?php if ( $nolasharon_b['landmark'] ) : ?>
			<div><dt><?php echo nolasharon_icon( 'pin', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Nearest landmark', 'nolasharon' ); ?></dt><dd><?php echo esc_html( $nolasharon_b['landmark'] ); ?></dd></div>
		<?php endif; ?>
		<?php if ( $nolasharon_b['hours'] ) : ?>
			<div><dt><?php echo nolasharon_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Opening hours', 'nolasharon' ); ?></dt><dd><?php echo nl2br( esc_html( $nolasharon_b['hours'] ) ); ?></dd></div>
		<?php endif; ?>
		<?php if ( $nolasharon_tel ) : ?>
			<div><dt><?php echo nolasharon_icon( 'phone', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Phone', 'nolasharon' ); ?></dt><dd><a href="<?php echo esc_url( $nolasharon_tel, array( 'tel' ) ); ?>"><?php echo esc_html( $nolasharon_b['phone'] ); ?></a></dd></div>
		<?php endif; ?>
		<?php if ( nolasharon_setting( 'nolasharon_pickup_enabled' ) && ! $nolasharon_b['is_draft'] ) : ?>
			<div><dt><?php echo nolasharon_icon( 'store', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Order pickup', 'nolasharon' ); ?></dt><dd><?php echo $nolasharon_b['pickup_enabled'] ? esc_html__( 'Available', 'nolasharon' ) : esc_html__( 'Not available', 'nolasharon' ); ?></dd></div>
		<?php endif; ?>
	</dl>

	<?php if ( ! $nolasharon_b['address'] && ! $nolasharon_b['hours'] ) : ?>
		<p class="branch-card__pending"><?php esc_html_e( 'Full details coming soon.', 'nolasharon' ); ?></p>
	<?php endif; ?>

	<?php if ( $nolasharon_embed ) : ?>
		<div class="branch-card__map">
			<iframe src="<?php echo esc_url( $nolasharon_embed ); ?>" title="<?php echo esc_attr( sprintf( /* translators: %s: branch name. */ __( 'Map: %s', 'nolasharon' ), $nolasharon_b['name'] ) ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
		</div>
	<?php endif; ?>

	<?php if ( $nolasharon_directions || $nolasharon_tel ) : ?>
		<div class="branch-card__actions">
			<?php if ( $nolasharon_directions ) : ?>
				<a class="btn btn--dark btn--sm" href="<?php echo esc_url( $nolasharon_directions ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get Directions', 'nolasharon' ); ?></a>
			<?php endif; ?>
			<?php if ( $nolasharon_tel ) : ?>
				<a class="btn btn--outline btn--sm" href="<?php echo esc_url( $nolasharon_tel, array( 'tel' ) ); ?>"><?php esc_html_e( 'Call', 'nolasharon' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</article>

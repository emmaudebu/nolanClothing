<?php
/**
 * Size guide modal. Content comes from the "Size Guide" page so the client can edit it.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_guide = get_page_by_path( 'size-guide' );
$nolasharon_body  = $nolasharon_guide ? trim( $nolasharon_guide->post_content ) : '';
?>
<dialog class="modal" id="size-guide" aria-labelledby="size-guide-title">
	<div class="modal__head">
		<h2 class="modal__title" id="size-guide-title"><?php esc_html_e( 'Size guide', 'nolasharon' ); ?></h2>
		<button class="icon-btn" type="button" data-close-dialog aria-label="<?php esc_attr_e( 'Close size guide', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'close', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	</div>
	<div class="modal__body entry-content">
		<?php if ( '' !== $nolasharon_body ) : ?>
			<?php echo apply_filters( 'the_content', $nolasharon_body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- page content filtered like the_content(). ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Our full size chart is coming soon.', 'nolasharon' ); ?></p>
			<p>
				<?php esc_html_e( 'Not sure which size to pick? Choose "Made to fit" where available, or message us and we will help.', 'nolasharon' ); ?>
				<a href="<?php echo esc_url( nolasharon_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat on WhatsApp', 'nolasharon' ); ?></a>
			</p>
			<?php if ( current_user_can( 'edit_pages' ) && $nolasharon_guide ) : ?>
				<p class="modal__admin-note"><a href="<?php echo esc_url( get_edit_post_link( $nolasharon_guide ) ); ?>"><?php esc_html_e( 'Add the size chart to the Size Guide page (only admins see this).', 'nolasharon' ); ?></a></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</dialog>

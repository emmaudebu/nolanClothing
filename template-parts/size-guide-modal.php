<?php
/**
 * Size guide modal (SHOP-PRODUCT.md §3.5): the client's chart for the product's category (or
 * the product's own), an in/cm toggle, the chosen size's row highlighted, how to measure,
 * and help choosing. Only rendered when a chart exists.
 *
 * Args: chart (from nolasharon_size_chart()), id (dialog ID).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_chart = $args['chart'];
$nolasharon_id    = $args['id'];
$nolasharon_help  = nolasharon_whatsapp_url( __( 'Hi, I need help choosing a size.', 'nolasharon' ) );
$nolasharon_help  = $nolasharon_help ? $nolasharon_help : nolasharon_page_url( 'contact' );
$nolasharon_notes = array();
foreach ( array_map( 'strtolower', $nolasharon_chart['headers'] ) as $nolasharon_header ) {
	if ( false !== strpos( $nolasharon_header, 'bust' ) || false !== strpos( $nolasharon_header, 'chest' ) ) {
		$nolasharon_notes[ ucfirst( $nolasharon_header ) ] = __( 'Around the fullest part of your chest.', 'nolasharon' );
	} elseif ( false !== strpos( $nolasharon_header, 'waist' ) ) {
		$nolasharon_notes[ ucfirst( $nolasharon_header ) ] = __( 'Around your natural waistline.', 'nolasharon' );
	} elseif ( false !== strpos( $nolasharon_header, 'hip' ) ) {
		$nolasharon_notes[ ucfirst( $nolasharon_header ) ] = __( 'Around the fullest part of your hips.', 'nolasharon' );
	}
}
?>
<dialog class="ns-modal sg" id="<?php echo esc_attr( $nolasharon_id ); ?>" aria-labelledby="<?php echo esc_attr( $nolasharon_id ); ?>-title" data-size-guide>
	<div class="sg__head">
		<h2 class="ns-modal__title" id="<?php echo esc_attr( $nolasharon_id ); ?>-title"><?php esc_html_e( 'Size guide', 'nolasharon' ); ?></h2>
		<button class="sg__close" type="button" data-close-dialog aria-label="<?php esc_attr_e( 'Close size guide', 'nolasharon' ); ?>"><?php echo nolasharon_icon( 'close', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	</div>
	<p class="sg__sub" data-unit-line data-in="<?php echo esc_attr( $nolasharon_chart['category'] ? sprintf( /* translators: %s: category. */ __( '%s clothing · measurements in inches', 'nolasharon' ), $nolasharon_chart['category'] ) : __( 'Measurements in inches', 'nolasharon' ) ); ?>" data-cm="<?php echo esc_attr( $nolasharon_chart['category'] ? sprintf( /* translators: %s: category. */ __( '%s clothing · measurements in centimetres', 'nolasharon' ), $nolasharon_chart['category'] ) : __( 'Measurements in centimetres', 'nolasharon' ) ); ?>"><?php echo esc_html( $nolasharon_chart['category'] ? sprintf( /* translators: %s: category. */ __( '%s clothing · measurements in inches', 'nolasharon' ), $nolasharon_chart['category'] ) : __( 'Measurements in inches', 'nolasharon' ) ); ?></p>
	<div class="ns-seg sg__units" role="group" aria-label="<?php esc_attr_e( 'Units', 'nolasharon' ); ?>" style="--n: 2; --i: 0" data-units hidden>
		<span class="ns-seg__thumb" aria-hidden="true"></span>
		<button type="button" class="ns-seg__btn" aria-pressed="true" data-unit="in"><?php esc_html_e( 'in', 'nolasharon' ); ?></button>
		<button type="button" class="ns-seg__btn" aria-pressed="false" data-unit="cm"><?php esc_html_e( 'cm', 'nolasharon' ); ?></button>
	</div>
	<div class="sg__table" tabindex="0" role="region" aria-labelledby="<?php echo esc_attr( $nolasharon_id ); ?>-title">
		<table>
			<thead>
				<tr>
					<?php foreach ( $nolasharon_chart['headers'] as $nolasharon_header ) : ?>
						<th scope="col"><?php echo esc_html( $nolasharon_header ); ?></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $nolasharon_chart['rows'] as $nolasharon_row ) : ?>
					<tr data-size="<?php echo esc_attr( strtolower( $nolasharon_row[0] ) ); ?>">
						<?php foreach ( $nolasharon_row as $nolasharon_ci => $nolasharon_cell ) : ?>
							<?php if ( 0 === $nolasharon_ci ) : ?>
								<th scope="row"><?php echo esc_html( $nolasharon_cell ); ?></th>
							<?php else : ?>
								<td data-in="<?php echo esc_attr( $nolasharon_cell ); ?>"><?php echo esc_html( $nolasharon_cell ); ?></td>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ( $nolasharon_notes ) : ?>
		<div class="sg__notes">
			<?php foreach ( $nolasharon_notes as $nolasharon_label => $nolasharon_note ) : ?>
				<p><strong><?php echo esc_html( $nolasharon_label ); ?></strong><br><?php echo esc_html( $nolasharon_note ); ?></p>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<p class="sg__help"><?php esc_html_e( 'Between sizes?', 'nolasharon' ); ?> <a href="<?php echo esc_url( $nolasharon_help ); ?>"<?php echo 0 === strpos( $nolasharon_help, 'https://wa.me' ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php esc_html_e( 'Chat with us', 'nolasharon' ); ?></a> <?php esc_html_e( 'and we’ll help you choose.', 'nolasharon' ); ?></p>
</dialog>

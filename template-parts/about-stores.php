<?php
/**
 * About Us "Our stores" (CONTENT-PAGES.md §1.6): a card per branch from the Branch posts,
 * saying the Ikeja stores remain open. Ogudu is an additional branch, never a move.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_branches = ns_get_branches( array( 'include_drafts' => true ) );
if ( ! $nolasharon_branches ) {
	return;
}
?>
<section class="ab-section ab-stores" aria-labelledby="ab-stores-title">
	<p class="ab-eyebrow"><?php esc_html_e( 'Our stores', 'nolasharon' ); ?></p>
	<h2 class="ab-h2" id="ab-stores-title"><?php esc_html_e( 'Come and see us in Lagos', 'nolasharon' ); ?></h2>
	<?php // DRAFT: client to approve. Must keep the "Ikeja remains open" meaning. ?>
	<p class="ab-stores__intro"><?php esc_html_e( 'Our Ikeja stores remain open, and Ogudu is our newest branch.', 'nolasharon' ); ?></p>
	<ul class="ab-stores__grid">
		<?php foreach ( $nolasharon_branches as $nolasharon_b ) : ?>
			<?php
			$nolasharon_status = ns_branch_status( $nolasharon_b );
			$nolasharon_where  = $nolasharon_b['address'] ? preg_replace( '/\s*\n\s*/', ', ', $nolasharon_b['address'] ) : $nolasharon_b['area'];
			$nolasharon_dir    = ns_branch_directions_url( $nolasharon_b );
			?>
			<li class="ab-store<?php echo $nolasharon_b['is_new'] ? ' is-new' : ''; ?>">
				<span class="ab-store__icon" aria-hidden="true"><?php echo nolasharon_icon( 'store', 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<?php if ( $nolasharon_b['tag'] || $nolasharon_b['is_new'] || $nolasharon_b['is_draft'] ) : ?>
					<span class="ab-store__tags">
						<?php if ( $nolasharon_b['tag'] || $nolasharon_b['is_new'] ) : ?>
							<span class="ab-store__tag"><?php echo esc_html( $nolasharon_b['tag'] ? $nolasharon_b['tag'] : __( 'New', 'nolasharon' ) ); ?></span>
						<?php endif; ?>
						<?php if ( $nolasharon_b['is_draft'] ) : ?>
							<span class="tag tag--draft"><?php esc_html_e( 'Draft', 'nolasharon' ); ?></span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
				<h3 class="ab-store__name"><a href="<?php echo esc_url( ns_branch_url( $nolasharon_b ) ); ?>"><?php echo esc_html( $nolasharon_b['name'] ); ?></a></h3>
				<p class="ab-store__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $nolasharon_status['label'], $nolasharon_where ) ) ) ); ?></p>
				<?php if ( $nolasharon_dir ) : ?>
					<a class="ab-store__dir" href="<?php echo esc_url( $nolasharon_dir ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'nolasharon' ); ?> <span aria-hidden="true">→</span><span class="screen-reader-text"> <?php echo esc_html( sprintf( /* translators: %s: branch name. */ __( 'to %s (opens in a new tab)', 'nolasharon' ), $nolasharon_b['name'] ) ); ?></span></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
	<a class="ns-btn ns-btn--ghost" href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) ); ?>"><?php esc_html_e( 'All store details', 'nolasharon' ); ?></a>
</section>

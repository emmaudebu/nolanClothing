<?php
/**
 * Template Name: Policy
 *
 * Policy pages (CONTENT-PAGES.md §4): hero with tabs linking the four policies, "Last
 * updated" (from the Effective date field), the content with numbered H2 sections, an "On
 * this page" list that follows the reader, a print button and a contact card. The client
 * writes the wording in the block editor; sections still waiting for it are hidden from
 * visitors, and editors see a notice.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$nolasharon_raw      = get_post_field( 'post_content', get_the_ID() );
	$nolasharon_sections = nolasharon_policy_sections( apply_filters( 'the_content', $nolasharon_raw ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
	$nolasharon_date     = get_post_meta( get_the_ID(), 'ns_effective_date', true );
	$nolasharon_count    = count( $nolasharon_sections['toc'] );
	$nolasharon_pending  = false !== strpos( $nolasharon_raw, '[[CONFIRM' );
	?>
	<div class="pol">
		<section class="pol-hero">
			<span class="pol-hero__mark" aria-hidden="true"><?php esc_html_e( 'Policies', 'nolasharon' ); ?></span>
			<div class="container pol-hero__inner">
				<nav class="pol-crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>
				<div class="pol-hero__copy ns-in ns-in--1" data-pol-fade>
					<span class="pol-hero__eyebrow"><?php esc_html_e( 'Store policies', 'nolasharon' ); ?></span>
					<h1 class="pol-hero__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="pol-hero__summary"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</div>
				<nav class="pol-tabs ns-in ns-in--2" aria-label="<?php esc_attr_e( 'Store policies', 'nolasharon' ); ?>">
					<?php foreach ( nolasharon_policy_definitions() as $nolasharon_key => $nolasharon_def ) : ?>
						<?php $nolasharon_pid = nolasharon_policy_page_id( $nolasharon_key ); ?>
						<?php if ( $nolasharon_pid ) : ?>
							<a class="pol-tabs__tab" href="<?php echo esc_url( get_permalink( $nolasharon_pid ) ); ?>"<?php echo get_the_ID() === $nolasharon_pid ? ' aria-current="page"' : ''; ?> data-pol-tab><?php echo esc_html( $nolasharon_def['label'] ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</nav>
			</div>
		</section>

		<div class="container pol-body">
			<?php if ( $nolasharon_sections['toc'] ) : ?>
				<nav class="pol-toc" aria-label="<?php esc_attr_e( 'On this page', 'nolasharon' ); ?>" data-pol-toc>
					<button class="pol-toc__toggle" type="button" aria-expanded="false" aria-controls="pol-toc-list" data-pol-toc-toggle hidden>
						<?php esc_html_e( 'On this page', 'nolasharon' ); ?>
						<?php echo nolasharon_icon( 'chevron', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
					<p class="pol-toc__label"><?php esc_html_e( 'On this page', 'nolasharon' ); ?></p>
					<div class="pol-toc__wrap" id="pol-toc-list">
						<span class="pol-toc__bar" aria-hidden="true"></span>
						<ol class="pol-toc__list">
							<?php foreach ( $nolasharon_sections['toc'] as $nolasharon_id => $nolasharon_text ) : ?>
								<li><a href="#<?php echo esc_attr( $nolasharon_id ); ?>"><?php echo esc_html( $nolasharon_text ); ?></a></li>
							<?php endforeach; ?>
						</ol>
					</div>
					<button class="pol-print ns-btn ns-btn--ghost ns-btn--sm" type="button" data-pol-print hidden>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M6 9V3h12v6M6 18H4v-7h16v7h-2M7 14h10v7H7z"/></svg>
						<?php esc_html_e( 'Print this policy', 'nolasharon' ); ?>
					</button>
				</nav>
			<?php endif; ?>

			<article class="pol-article" data-pol-fade>
				<?php if ( $nolasharon_date || $nolasharon_count ) : ?>
					<div class="pol-meta">
						<?php if ( $nolasharon_date ) : ?>
							<span class="pol-meta__chip"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/></svg><?php echo esc_html( sprintf( /* translators: %s: date. */ __( 'Last updated: %s', 'nolasharon' ), wp_date( get_option( 'date_format' ), strtotime( $nolasharon_date . ' 12:00' ) ) ) ); ?></span>
						<?php endif; ?>
						<?php if ( $nolasharon_count ) : ?>
							<span class="pol-meta__chip"><?php echo esc_html( sprintf( /* translators: %d: number of sections. */ _n( '%d section', '%d sections', $nolasharon_count, 'nolasharon' ), $nolasharon_count ) ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $nolasharon_pending && nolasharon_sees_placeholders() ) : ?>
					<div class="pol-notice" role="note">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M11 12h1v5h1"/></svg>
						<span><?php esc_html_e( 'This page shows the structure only. The policy wording must be supplied or approved by NolaSharon Clothing before launch. (Only editors see this notice.)', 'nolasharon' ); ?></span>
					</div>
				<?php endif; ?>

				<div class="pol-content entry-content">
					<?php echo $nolasharon_sections['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?>
				</div>

				<div class="pol-end">
					<div>
						<p class="pol-end__title"><?php esc_html_e( 'Questions about this policy?', 'nolasharon' ); ?></p>
						<p><?php esc_html_e( 'We’re happy to explain anything that isn’t clear.', 'nolasharon' ); ?></p>
					</div>
					<a class="pol-end__btn" href="<?php echo esc_url( nolasharon_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'nolasharon' ); ?></a>
				</div>
			</article>
		</div>
	</div>
	<?php
endwhile;

get_footer();

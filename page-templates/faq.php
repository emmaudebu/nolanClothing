<?php
/**
 * Template Name: FAQs
 *
 * Help centre (CONTENT-PAGES.md §3): hero with a search box, topic chips with counts, the
 * questions as accordion cards, a no-results state and a "Still have a question?" card.
 * Questions are edited under FAQs in wp-admin. Without JavaScript the search and chips work
 * as plain links (?q= and ?topic=) and every answer is shown.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_all    = nolasharon_faqs();
$nolasharon_topics = nolasharon_faq_topics( $nolasharon_all );
$nolasharon_q      = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
$nolasharon_topic  = isset( $_GET['topic'] ) ? sanitize_key( $_GET['topic'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter.
$nolasharon_colors = array();
foreach ( $nolasharon_topics as $nolasharon_t ) {
	$nolasharon_colors[ $nolasharon_t['term']->slug ] = $nolasharon_t['color'];
}

// Server-side filter, so the page works without JavaScript. JS takes over from here.
$nolasharon_shown = array_filter(
	$nolasharon_all,
	function ( $faq ) use ( $nolasharon_q, $nolasharon_topic ) {
		if ( $nolasharon_topic && $faq['topic'] !== $nolasharon_topic ) {
			return false;
		}
		return '' === $nolasharon_q || false !== stripos( $faq['question'] . ' ' . wp_strip_all_tags( $faq['answer'] ), $nolasharon_q );
	}
);
$nolasharon_count  = count( $nolasharon_shown );
if ( $nolasharon_q ) {
	/* translators: 1: number of questions, 2: search words. */
	$nolasharon_line = sprintf( _n( '%1$d question matches “%2$s”', '%1$d questions match “%2$s”', $nolasharon_count, 'nolasharon' ), $nolasharon_count, $nolasharon_q );
} else {
	/* translators: %d: number of questions. */
	$nolasharon_line = sprintf( _n( '%d question', '%d questions', $nolasharon_count, 'nolasharon' ), $nolasharon_count );
}
$nolasharon_whatsapp = nolasharon_whatsapp_url( __( 'Hi, I have a question.', 'nolasharon' ) );

get_header();
?>
<div class="faq" data-faq>
	<section class="ch-hero faq-hero">
		<span class="ch-mark faq-hero__mark" aria-hidden="true">?</span>
		<div class="container faq-hero__inner">
			<nav class="ch-crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
				<span aria-hidden="true">/</span>
				<span aria-current="page"><?php the_title(); ?></span>
			</nav>
			<span class="ch-eyebrow ns-in ns-in--1"><?php esc_html_e( 'Help centre', 'nolasharon' ); ?></span>
			<h1 class="faq-hero__title ns-in ns-in--1"><?php esc_html_e( 'How can we help?', 'nolasharon' ); ?></h1>
			<p class="faq-hero__intro ns-in ns-in--2"><?php esc_html_e( 'Answers to common questions about orders, delivery, sizing and exchanges.', 'nolasharon' ); ?></p>
			<?php if ( $nolasharon_all ) : ?>
				<form class="faq-search ns-in ns-in--2" role="search" method="get" action="<?php the_permalink(); ?>" data-faq-form>
					<label class="screen-reader-text" for="faq-q"><?php esc_html_e( 'Search the questions', 'nolasharon' ); ?></label>
					<svg class="faq-search__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
					<input type="search" id="faq-q" name="q" value="<?php echo esc_attr( $nolasharon_q ); ?>" placeholder="<?php esc_attr_e( 'Search, e.g. delivery, size, exchange', 'nolasharon' ); ?>" autocomplete="off" data-faq-q>
					<?php if ( $nolasharon_topic ) : ?>
						<input type="hidden" name="topic" value="<?php echo esc_attr( $nolasharon_topic ); ?>">
					<?php endif; ?>
					<button type="button" class="faq-search__clear" data-faq-clear<?php echo $nolasharon_q ? '' : ' hidden'; ?>>
						<?php echo nolasharon_icon( 'close', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Clear search', 'nolasharon' ); ?></span>
					</button>
					<button type="submit" class="faq-search__go"><?php esc_html_e( 'Search', 'nolasharon' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
	</section>

	<div class="container faq-body">
		<?php if ( $nolasharon_topics ) : ?>
			<nav class="faq-chips" aria-label="<?php esc_attr_e( 'Topics', 'nolasharon' ); ?>">
				<a class="faq-chip" href="<?php echo esc_url( remove_query_arg( 'topic' ) ); ?>" data-faq-topic=""<?php echo $nolasharon_topic ? '' : ' aria-current="true"'; ?>>
					<?php esc_html_e( 'All', 'nolasharon' ); ?> <span class="faq-chip__n"><?php echo esc_html( count( $nolasharon_all ) ); ?></span>
				</a>
				<?php foreach ( $nolasharon_topics as $nolasharon_t ) : ?>
					<a class="faq-chip" href="<?php echo esc_url( add_query_arg( 'topic', $nolasharon_t['term']->slug ) ); ?>" data-faq-topic="<?php echo esc_attr( $nolasharon_t['term']->slug ); ?>" style="--dot:<?php echo esc_attr( $nolasharon_t['color'] ); ?>"<?php echo $nolasharon_topic === $nolasharon_t['term']->slug ? ' aria-current="true"' : ''; ?>>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="<?php echo esc_attr( $nolasharon_t['path'] ); ?>"/></svg>
						<?php echo esc_html( $nolasharon_t['term']->name ); ?> <span class="faq-chip__n"><?php echo esc_html( $nolasharon_t['count'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<p class="faq-result" aria-live="polite" data-faq-result><?php echo esc_html( $nolasharon_line ); ?></p>

		<?php if ( $nolasharon_all ) : ?>
			<ul class="faq-list">
				<?php foreach ( $nolasharon_all as $nolasharon_faq ) : ?>
					<?php $nolasharon_id = 'faq-' . $nolasharon_faq['id']; ?>
					<li class="faq-item" data-faq-item data-topic="<?php echo esc_attr( $nolasharon_faq['topic'] ); ?>" style="--dot:<?php echo esc_attr( isset( $nolasharon_colors[ $nolasharon_faq['topic'] ] ) ? $nolasharon_colors[ $nolasharon_faq['topic'] ] : '#8c6448' ); ?>"<?php echo in_array( $nolasharon_faq, $nolasharon_shown, true ) ? '' : ' hidden'; ?>>
						<h2 class="faq-item__q">
							<button type="button" id="<?php echo esc_attr( $nolasharon_id ); ?>-q" aria-expanded="false" aria-controls="<?php echo esc_attr( $nolasharon_id ); ?>" data-faq-toggle>
								<span class="faq-item__dot" aria-hidden="true"></span>
								<span class="faq-item__text">
									<span data-faq-text><?php echo esc_html( $nolasharon_faq['question'] ); ?></span>
									<?php if ( $nolasharon_faq['pending'] ) : ?>
										<span class="tag tag--draft"><?php esc_html_e( 'Hidden from visitors until answered', 'nolasharon' ); ?></span>
									<?php endif; ?>
								</span>
								<span class="faq-item__icon" aria-hidden="true"></span>
							</button>
						</h2>
						<div class="faq-item__a" id="<?php echo esc_attr( $nolasharon_id ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $nolasharon_id ); ?>-q">
							<div>
								<div class="faq-item__body entry-content" data-faq-text>
									<?php echo $nolasharon_faq['answer']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the_content output. ?>
								</div>
								<?php if ( $nolasharon_faq['link'] && $nolasharon_faq['url'] ) : ?>
									<a class="faq-item__link" href="<?php echo esc_url( $nolasharon_faq['url'] ); ?>"><?php echo esc_html( $nolasharon_faq['link'] ); ?> <span aria-hidden="true">→</span></a>
								<?php endif; ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="faq-empty" data-faq-empty<?php echo $nolasharon_count ? ' hidden' : ''; ?>>
			<p class="faq-empty__title"><?php echo esc_html( $nolasharon_all ? __( 'No matching questions', 'nolasharon' ) : __( 'Our questions and answers are on the way', 'nolasharon' ) ); ?></p>
			<p><?php esc_html_e( 'Try a different word, or ask us directly.', 'nolasharon' ); ?></p>
			<?php if ( $nolasharon_all ) : ?>
				<a class="ns-btn ns-btn--ghost ns-btn--sm" href="<?php the_permalink(); ?>" data-faq-reset><?php esc_html_e( 'Show all questions', 'nolasharon' ); ?></a>
			<?php endif; ?>
		</div>

		<section class="faq-help" aria-labelledby="faq-help-title">
			<div>
				<h2 class="faq-help__title" id="faq-help-title"><?php esc_html_e( 'Still have a question?', 'nolasharon' ); ?></h2>
				<p><?php esc_html_e( 'Our team is happy to help with anything not covered here.', 'nolasharon' ); ?></p>
			</div>
			<div class="faq-help__actions">
				<?php if ( $nolasharon_whatsapp ) : ?>
					<a class="faq-help__btn faq-help__btn--wa" href="<?php echo esc_url( $nolasharon_whatsapp ); ?>" target="_blank" rel="noopener"><?php echo nolasharon_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Chat on WhatsApp', 'nolasharon' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
				<?php endif; ?>
				<a class="faq-help__btn" href="<?php echo esc_url( nolasharon_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact Us', 'nolasharon' ); ?></a>
			</div>
		</section>
	</div>
</div>
<?php
get_footer();

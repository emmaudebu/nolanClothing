<?php
/**
 * Template Name: Contact
 *
 * Contact Us (CONTENT-PAGES.md §2): dark hero, a card per contact channel set in the
 * Customizer, the Fluent Forms "Contact" form with a success state, and a sidebar with
 * customer service hours, the stores and quick answers. Anything not set is left out.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

$nolasharon_channels = nolasharon_contact_channels();
$nolasharon_socials  = nolasharon_contact_socials();
$nolasharon_hours    = nolasharon_contact_hours();
$nolasharon_branches = ns_get_branches( array( 'include_drafts' => true ) );
$nolasharon_form_id  = nolasharon_contact_form_id();

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="ct">
		<section class="ct-hero">
			<span class="ct-hero__circle" aria-hidden="true"></span>
			<span class="ct-hero__ring" aria-hidden="true"></span>
			<div class="container ct-hero__inner">
				<nav class="ch-crumbs ct-crumbs ns-in" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nolasharon' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nolasharon' ); ?></a>
					<span aria-hidden="true">/</span>
					<span aria-current="page"><?php the_title(); ?></span>
				</nav>
				<span class="ch-eyebrow ct-eyebrow ns-in ns-in--1"><?php esc_html_e( 'Contact us', 'nolasharon' ); ?></span>
				<h1 class="ct-hero__title ns-in ns-in--1"><?php esc_html_e( 'We’d love to hear from you', 'nolasharon' ); ?></h1>
				<p class="ct-hero__intro ns-in ns-in--2"><?php esc_html_e( 'Questions about a product, an order or a store visit? Reach us the way that suits you best.', 'nolasharon' ); ?></p>
			</div>
		</section>

		<div class="container ct-main<?php echo $nolasharon_channels || $nolasharon_socials ? ' has-cards' : ''; ?>">
			<?php if ( $nolasharon_channels || $nolasharon_socials ) : ?>
				<ul class="ct-cards ns-in ns-in--2" aria-label="<?php esc_attr_e( 'Ways to reach us', 'nolasharon' ); ?>">
					<?php foreach ( $nolasharon_channels as $nolasharon_c ) : ?>
						<li>
							<a class="ct-card ct-card--<?php echo esc_attr( $nolasharon_c['key'] ); ?>" href="<?php echo esc_url( $nolasharon_c['url'], array( 'https', 'http', 'tel', 'mailto' ) ); ?>"<?php echo $nolasharon_c['external'] ? ' target="_blank" rel="noopener"' : ''; ?>>
								<span class="ct-card__icon" aria-hidden="true"><?php echo nolasharon_icon( $nolasharon_c['icon'], 22 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<span class="ct-card__title"><?php echo esc_html( $nolasharon_c['title'] ); ?></span>
								<span class="ct-card__value"><?php echo esc_html( $nolasharon_c['value'] ); ?></span>
								<span class="ct-card__arrow" aria-hidden="true"><?php echo nolasharon_icon( 'arrow', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
								<?php if ( $nolasharon_c['external'] ) : ?>
									<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
					<?php if ( $nolasharon_socials ) : ?>
						<li>
							<div class="ct-card ct-card--social">
								<span class="ct-card__icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/></svg></span>
								<span class="ct-card__title"><?php esc_html_e( 'Follow us', 'nolasharon' ); ?></span>
								<span class="ct-card__socials">
									<?php foreach ( $nolasharon_socials as $nolasharon_s ) : ?>
										<a href="<?php echo esc_url( $nolasharon_s['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $nolasharon_s['label'] ); ?><span class="screen-reader-text"> <?php echo esc_html( $nolasharon_s['handle'] ); ?> <?php esc_html_e( '(opens in a new tab)', 'nolasharon' ); ?></span></a>
									<?php endforeach; ?>
								</span>
							</div>
						</li>
					<?php endif; ?>
				</ul>
			<?php elseif ( current_user_can( 'edit_theme_options' ) ) : ?>
				<p class="ct-admin admin-note"><a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=nolasharon_contact' ) ); ?>"><?php esc_html_e( 'Add the confirmed phone, WhatsApp, email, hours and social links in Customizer → NolaSharon Settings → Contact & social. Each one appears here as a card. (Only admins see this.)', 'nolasharon' ); ?></a></p>
			<?php endif; ?>

			<div class="ct-grid">
				<section class="ct-form ns-in ns-in--3" aria-labelledby="ct-form-title" data-ct>
					<div class="ct-form__body" data-ct-body>
						<h2 class="ct-form__title" id="ct-form-title"><?php esc_html_e( 'Send us a message', 'nolasharon' ); ?></h2>
						<p class="ct-form__intro"><?php esc_html_e( 'Fill in the form and our team will get back to you by email.', 'nolasharon' ); ?></p>
						<?php if ( $nolasharon_form_id ) : ?>
							<?php echo do_shortcode( '[fluentform id="' . absint( $nolasharon_form_id ) . '"]' ); ?>
						<?php elseif ( nolasharon_has_text( get_the_content() ) ) : ?>
							<div class="entry-content"><?php the_content(); ?></div>
						<?php else : ?>
							<p class="ct-form__none">
								<?php esc_html_e( 'Our contact form is coming soon.', 'nolasharon' ); ?>
								<a href="<?php echo esc_url( nolasharon_page_url( 'faqs' ) ); ?>"><?php esc_html_e( 'Read our FAQs', 'nolasharon' ); ?></a>
							</p>
							<?php if ( current_user_can( 'manage_options' ) ) : ?>
								<p class="admin-note"><?php esc_html_e( 'Install and activate the free Fluent Forms plugin, then open any wp-admin screen: the theme creates the “Contact” form for you. (Only admins see this.)', 'nolasharon' ); ?></p>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<div class="ct-sent" data-ct-sent tabindex="-1" hidden>
						<svg class="ct-sent__check" width="88" height="88" viewBox="0 0 88 88" aria-hidden="true" focusable="false"><circle cx="44" cy="44" r="40" fill="none" stroke="currentColor" stroke-width="4"/><path d="M27 45l11 11 23-24" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						<h2 class="ct-sent__title" data-ct-sent-title><?php esc_html_e( 'Thank you!', 'nolasharon' ); ?></h2>
						<p class="ct-sent__text" data-ct-sent-text><?php esc_html_e( 'Your message has been sent. We’ll reply as soon as we can.', 'nolasharon' ); ?></p>
						<div class="ct-sent__actions">
							<a class="ns-btn" href="<?php echo esc_url( nolasharon_shop_url() ); ?>"><?php esc_html_e( 'Continue shopping', 'nolasharon' ); ?></a>
							<a class="ns-btn ns-btn--ghost" href="<?php the_permalink(); ?>" data-ct-again><?php esc_html_e( 'Send another message', 'nolasharon' ); ?></a>
						</div>
					</div>
				</section>

				<aside class="ct-side">
					<?php if ( $nolasharon_hours ) : ?>
						<section class="ct-box ct-box--hours ns-in ns-in--3" aria-labelledby="ct-hours-title">
							<h2 class="ct-box__title" id="ct-hours-title"><?php echo nolasharon_icon( 'clock', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Customer service hours', 'nolasharon' ); ?></h2>
							<dl class="ct-hours">
								<?php foreach ( $nolasharon_hours as $nolasharon_row ) : ?>
									<div><dt><?php echo esc_html( $nolasharon_row['day'] ); ?></dt><dd><?php echo esc_html( $nolasharon_row['time'] ); ?></dd></div>
								<?php endforeach; ?>
							</dl>
						</section>
					<?php endif; ?>

					<?php if ( $nolasharon_branches ) : ?>
						<section class="ct-box ns-in ns-in--3" aria-labelledby="ct-stores-title">
							<h2 class="ct-box__title" id="ct-stores-title"><?php echo nolasharon_icon( 'store', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php esc_html_e( 'Visit a store', 'nolasharon' ); ?></h2>
							<?php // DRAFT: client to approve. Ogudu is an additional branch. ?>
							<p class="ct-box__text"><?php esc_html_e( 'Our Ikeja stores remain open, and Ogudu is our newest branch.', 'nolasharon' ); ?></p>
							<ul class="ct-stores">
								<?php foreach ( $nolasharon_branches as $nolasharon_b ) : ?>
									<?php $nolasharon_status = ns_branch_status( $nolasharon_b ); ?>
									<li>
										<a href="<?php echo esc_url( ns_branch_url( $nolasharon_b ) ); ?>">
											<span class="ct-stores__name"><?php echo esc_html( $nolasharon_b['name'] ); ?>
												<?php if ( $nolasharon_b['is_new'] ) : ?>
													<span class="ct-stores__new"><?php esc_html_e( 'New', 'nolasharon' ); ?></span>
												<?php endif; ?>
												<?php if ( $nolasharon_b['is_draft'] ) : ?>
													<span class="tag tag--draft"><?php esc_html_e( 'Draft', 'nolasharon' ); ?></span>
												<?php endif; ?>
											</span>
											<span class="ct-stores__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $nolasharon_status['label'], $nolasharon_b['area'] ) ) ) ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
							<a class="ct-box__link" href="<?php echo esc_url( nolasharon_page_url( 'store-locations' ) ); ?>"><?php esc_html_e( 'All store details', 'nolasharon' ); ?> <span aria-hidden="true">→</span></a>
						</section>
					<?php endif; ?>

					<section class="ct-box ct-box--dark ns-in ns-in--3" aria-labelledby="ct-quick-title">
						<h2 class="ct-box__title" id="ct-quick-title"><?php esc_html_e( 'Quick answers', 'nolasharon' ); ?></h2>
						<p class="ct-box__text"><?php esc_html_e( 'Many questions are already answered in our help centre.', 'nolasharon' ); ?></p>
						<div class="ct-quick">
							<a href="<?php echo esc_url( nolasharon_page_url( 'faqs' ) ); ?>"><?php esc_html_e( 'Read FAQs', 'nolasharon' ); ?> <span aria-hidden="true">→</span></a>
							<a href="<?php echo esc_url( nolasharon_track_order_url() ); ?>"><?php esc_html_e( 'Track an order', 'nolasharon' ); ?> <span aria-hidden="true">→</span></a>
						</div>
					</section>
				</aside>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();

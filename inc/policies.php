<?php
/**
 * Policy pages (CONTENT-PAGES.md §4): Delivery & Shipping, Returns & Exchange, Privacy and
 * Terms. Normal pages on the "Policy" template; the client writes the wording in the block
 * editor with H2 headings. This file maps which page is which, seeds the structure (headings
 * plus [[CONFIRM: …]] lines, never policy terms), adds the "Effective date" field and numbers
 * the H2s for the contents list.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * The four policies: key => label, default slug, summary (page excerpt seed) and sections
 * (heading => what the client needs to cover).
 *
 * @return array
 */
function nolasharon_policy_definitions() {
	return array(
		'delivery' => array(
			'label'    => __( 'Delivery & Shipping', 'nolasharon' ),
			'title'    => __( 'Delivery & Shipping Policy', 'nolasharon' ),
			'slug'     => 'delivery-policy',
			'summary'  => __( 'How and where we deliver, what it costs, how long it takes, and store pickup.', 'nolasharon' ),
			'sections' => array(
				'Delivery locations'           => 'areas you deliver to (Lagos areas, other states)',
				'Delivery charges'             => 'fee per area or zone, and any changes for large orders',
				'Estimated delivery timelines' => 'typical timelines per area (only what you can reliably meet)',
				'Order processing time'        => 'how long before an order is dispatched; cut-off times, weekends and public holidays',
				'Dispatch and updates'         => 'courier or logistics provider (if any) and how customers receive updates',
				'Failed delivery attempts'     => 'what happens if no one is available, and any re-delivery charge',
				'Store pickup'                 => 'whether pickup is available and from which branches; how customers know an order is ready and what to bring',
			),
		),
		'returns'  => array(
			'label'    => __( 'Returns & Exchange', 'nolasharon' ),
			'title'    => __( 'Returns & Exchange Policy', 'nolasharon' ),
			'slug'     => 'returns-policy',
			'summary'  => __( 'Whether we accept returns and exchanges, the conditions, and how to make a request.', 'nolasharon' ),
			'sections' => array(
				'Returns'                => 'whether returns are accepted',
				'Exchanges'              => 'whether exchanges are permitted (size, colour, other items)',
				'Timeframe'              => 'how many days customers have to request a return or exchange',
				'Condition of items'     => 'required condition: unworn, tags attached, original packaging',
				'Exclusions'             => 'items that can’t be returned or exchanged (e.g. sale items, if applicable)',
				'How to make a request'  => 'steps, contact channel and what information to provide',
				'Return delivery costs'  => 'who pays for return delivery',
				'Refunds'                => 'whether refunds are offered, the method and the timeline',
			),
		),
		'privacy'  => array(
			'label'    => __( 'Privacy Policy', 'nolasharon' ),
			'title'    => __( 'Privacy Policy', 'nolasharon' ),
			'slug'     => 'privacy-policy',
			'summary'  => __( 'How we collect, use, store and protect your personal information.', 'nolasharon' ),
			'sections' => array(
				'Who we are'                  => 'business name and contact details',
				'Information we collect'      => 'account, order, delivery and contact-form details; anything collected automatically (cookies, analytics), if used',
				'How we use your information' => 'processing orders, delivery and customer support; marketing only if customers opt in',
				'Payments'                    => 'the payment processor used (e.g. Paystack); the site does not store card details',
				'Sharing your information'    => 'couriers, payment providers and other service providers',
				'Storage and security'        => 'how data is protected and how long it is kept',
				'Your rights'                 => 'access, correction and deletion requests (NDPA), and how to make them',
				'Changes and contact'         => 'how updates to this policy are communicated, and who to contact about privacy',
			),
		),
		'terms'    => array(
			'label'    => __( 'Terms & Conditions', 'nolasharon' ),
			'title'    => __( 'Terms & Conditions', 'nolasharon' ),
			'slug'     => 'terms-and-conditions',
			'summary'  => __( 'The terms that apply when you shop on our website.', 'nolasharon' ),
			'sections' => array(
				'About these terms'         => 'who the terms apply to and when',
				'Orders'                    => 'when an order is accepted and confirmed',
				'Pricing'                   => 'prices in Nigerian naira; what happens if a price is listed in error',
				'Product availability'      => 'stock, sold-out items and product images',
				'Payment'                   => 'accepted methods and payment confirmation',
				'Delivery'                  => 'reference to the Delivery & Shipping policy',
				'Cancellations'             => 'whether and when customers or the store can cancel an order',
				'Returns and exchanges'     => 'reference to the Returns & Exchange policy',
				'Customer responsibilities' => 'accurate details and account security',
				'Other terms'               => 'governing law and other relevant terms (professional advice recommended)',
			),
		),
	);
}

/**
 * The page used for a policy: the Customizer choice, else the WordPress privacy page /
 * WooCommerce terms page, else the default slug.
 *
 * @param string $key delivery, returns, privacy or terms.
 * @return int Page ID, or 0.
 */
function nolasharon_policy_page_id( $key ) {
	$id = (int) get_theme_mod( 'nolasharon_policy_' . $key );
	if ( ! $id && 'privacy' === $key ) {
		$id = (int) get_option( 'wp_page_for_privacy_policy' );
	}
	if ( ! $id && 'terms' === $key && function_exists( 'wc_terms_and_conditions_page_id' ) ) {
		$id = (int) wc_terms_and_conditions_page_id();
	}
	if ( ! $id ) {
		$defs = nolasharon_policy_definitions();
		$page = isset( $defs[ $key ] ) ? get_page_by_path( $defs[ $key ]['slug'] ) : null;
		$id   = $page ? $page->ID : 0;
	}
	return $id && 'publish' === get_post_status( $id ) ? $id : 0;
}

/**
 * URL of a policy page.
 *
 * @param string $key Policy key.
 * @return string
 */
function nolasharon_policy_url( $key ) {
	$id = nolasharon_policy_page_id( $key );
	return $id ? get_permalink( $id ) : home_url( '/' );
}

/**
 * "Which page is which" in Customizer → NolaSharon Settings → Policy pages.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function nolasharon_policy_customizer( $wp_customize ) {
	$wp_customize->add_section(
		'nolasharon_policies',
		array(
			'title'       => __( 'Policy pages', 'nolasharon' ),
			'description' => __( 'Which page holds each policy. They appear as tabs on every policy page and in the footer. Leave a choice empty to use the default page.', 'nolasharon' ),
			'panel'       => 'nolasharon',
		)
	);
	foreach ( nolasharon_policy_definitions() as $key => $def ) {
		$wp_customize->add_setting(
			'nolasharon_policy_' . $key,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			'nolasharon_policy_' . $key,
			array(
				'label'   => $def['label'],
				'section' => 'nolasharon_policies',
				'type'    => 'dropdown-pages',
			)
		);
	}
}
add_action( 'customize_register', 'nolasharon_policy_customizer', 20 );

/**
 * Seed the policy pages: create any that are missing with their H2 sections and one
 * [[CONFIRM: …]] line each, and put every policy page on the Policy template with its
 * summary. Runs once on activation; tools/seed-content.php calls it with $overwrite.
 *
 * @param bool $overwrite Replace existing page content with the seed structure.
 */
function nolasharon_seed_policy_pages( $overwrite = false ) {
	foreach ( nolasharon_policy_definitions() as $key => $def ) {
		$content = '';
		foreach ( $def['sections'] as $heading => $cover ) {
			$content .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $heading ) . "</h2>\n<!-- /wp:heading -->\n\n";
			$content .= "<!-- wp:paragraph -->\n<p>[[CONFIRM: " . esc_html( $cover ) . "]]</p>\n<!-- /wp:paragraph -->\n\n";
		}
		$id = nolasharon_policy_page_id( $key );
		if ( ! $id ) {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $def['title'],
					'post_name'    => $def['slug'],
					'post_content' => $content,
					'post_excerpt' => $def['summary'],
				)
			);
		} elseif ( $overwrite ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => $content,
				)
			);
		}
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_wp_page_template', 'page-templates/policy.php' );
		if ( '' === get_post_field( 'post_excerpt', $id ) ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_excerpt' => $def['summary'],
				)
			);
		}
		if ( 'privacy' === $key ) {
			update_option( 'wp_page_for_privacy_policy', $id );
		} elseif ( 'terms' === $key ) {
			update_option( 'woocommerce_terms_page_id', $id );
		}
	}
	update_option( 'nolasharon_policies_v1', 1 );
}
add_action( 'after_switch_theme', 'nolasharon_seed_policy_pages' );

/**
 * Run the seed once on existing installs too.
 */
function nolasharon_maybe_seed_policies() {
	if ( ! get_option( 'nolasharon_policies_v1' ) ) {
		nolasharon_seed_policy_pages();
	}
}
add_action( 'admin_init', 'nolasharon_maybe_seed_policies' );

/**
 * Pages get an excerpt box (used as the policy summary under the title).
 */
function nolasharon_page_excerpts() {
	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'init', 'nolasharon_page_excerpts' );

/* Effective date --------------------------------------------------------------- */

/**
 * "Effective date" box on pages using the Policy template. Shown as "Last updated" only once
 * set, so correcting a typo doesn't change it.
 */
function nolasharon_policy_meta_box() {
	add_meta_box( 'ns-policy-date', __( 'Policy: effective date', 'nolasharon' ), 'nolasharon_policy_meta_box_html', 'page', 'side' );
}
add_action( 'add_meta_boxes_page', 'nolasharon_policy_meta_box' );

/**
 * Effective date field.
 *
 * @param WP_Post $post Page.
 */
function nolasharon_policy_meta_box_html( $post ) {
	wp_nonce_field( 'nolasharon_policy_date', 'nolasharon_policy_date_nonce' );
	printf(
		'<p><label for="ns_effective_date">%1$s</label><br><input type="date" id="ns_effective_date" name="ns_effective_date" value="%2$s"></p><p class="description">%3$s</p>',
		esc_html__( 'Last updated', 'nolasharon' ),
		esc_attr( get_post_meta( $post->ID, 'ns_effective_date', true ) ),
		esc_html__( 'Only for pages on the Policy template. Hidden until set. Change it only when the policy itself changes.', 'nolasharon' )
	);
}

/**
 * Save the effective date.
 *
 * @param int $post_id Page ID.
 */
function nolasharon_policy_date_save( $post_id ) {
	if ( ! isset( $_POST['nolasharon_policy_date_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_policy_date_nonce'] ), 'nolasharon_policy_date' ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	$date = isset( $_POST['ns_effective_date'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_effective_date'] ) ) : '';
	update_post_meta( $post_id, 'ns_effective_date', preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ? $date : '' );
}
add_action( 'save_post_page', 'nolasharon_policy_date_save' );

/* Contents list --------------------------------------------------------------- */

/**
 * Give each H2 an id and a "01", "02"… number, and list them for "On this page".
 *
 * @param string $html Rendered (and placeholder-filtered) content.
 * @return array { html, toc: array( id => heading text ) }
 */
function nolasharon_policy_sections( $html ) {
	$toc   = array();
	$count = 0;
	$html  = preg_replace_callback(
		'~<h2([^>]*)>(.*?)</h2>~is',
		function ( $m ) use ( &$toc, &$count ) {
			++$count;
			$text = trim( wp_strip_all_tags( $m[2] ) );
			$id   = sanitize_title( $text );
			$id   = $id && ! isset( $toc[ $id ] ) ? $id : 'section-' . $count;
			$toc[ $id ] = $text;
			$attrs = preg_replace( '~\sid="[^"]*"~', '', $m[1] );
			return sprintf( '<h2%1$s id="%2$s"><span class="pol-num" aria-hidden="true">%3$s</span>%4$s</h2>', $attrs, esc_attr( $id ), sprintf( '%02d', $count ), $m[2] );
		},
		$html
	);
	return array(
		'html' => $html,
		'toc'  => $toc,
	);
}

/**
 * Policy page assets.
 */
function nolasharon_policy_assets() {
	if ( ! is_page_template( 'page-templates/policy.php' ) ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-content', get_template_directory_uri() . '/assets/css/content.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-policy', get_template_directory_uri() . '/assets/js/policy.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'nolasharon_policy_assets', 20 );

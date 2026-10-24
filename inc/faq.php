<?php
/**
 * FAQs (CONTENT-PAGES.md §3): the ns_faq post type (question = title, answer = content,
 * menu order = display order), ns_faq_topic topics with an icon and colour dot, an optional
 * link per question, the seed, and FAQPage schema. A question stays hidden from visitors
 * until its answer no longer contains [[CONFIRM.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the post type and topics.
 */
function nolasharon_register_faqs() {
	register_post_type(
		'ns_faq',
		array(
			'labels'       => array(
				'name'          => __( 'FAQs', 'nolasharon' ),
				'singular_name' => __( 'FAQ', 'nolasharon' ),
				'add_new_item'  => __( 'Add question', 'nolasharon' ),
				'edit_item'     => __( 'Edit question', 'nolasharon' ),
				'menu_name'     => __( 'FAQs', 'nolasharon' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-editor-help',
			'supports'     => array( 'title', 'editor', 'page-attributes' ),
		)
	);
	register_taxonomy(
		'ns_faq_topic',
		'ns_faq',
		array(
			'labels'            => array(
				'name'          => __( 'Topics', 'nolasharon' ),
				'singular_name' => __( 'Topic', 'nolasharon' ),
			),
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'hierarchical'      => true,
		)
	);
}
add_action( 'init', 'nolasharon_register_faqs' );

/**
 * Topic icons (stroke paths).
 *
 * @return array key => array( label, path )
 */
function nolasharon_faq_icons() {
	return array(
		'orders'   => array( __( 'Bag / lock', 'nolasharon' ), 'M5 11h14v10H5zM8 11V8a4 4 0 0 1 8 0v3' ),
		'delivery' => array( __( 'Truck', 'nolasharon' ), 'M3 7h11v10H3zM14 10h4l3 3v4h-7' ),
		'sizing'   => array( __( 'Tape measure', 'nolasharon' ), 'M3 8h18v8H3zM7 8v3M11 8v4M15 8v3M19 8v4' ),
		'returns'  => array( __( 'Return arrow', 'nolasharon' ), 'M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5' ),
		'support'  => array( __( 'Chat', 'nolasharon' ), 'M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20z' ),
	);
}

/**
 * Icon and colour fields on the topic screens.
 *
 * @param WP_Term|string $term Term being edited, or the taxonomy on the add screen.
 */
function nolasharon_faq_topic_fields( $term ) {
	$edit  = $term instanceof WP_Term;
	$icon  = $edit ? get_term_meta( $term->term_id, 'ns_icon', true ) : '';
	$color = $edit ? get_term_meta( $term->term_id, 'ns_color', true ) : '';
	$opts  = '';
	foreach ( nolasharon_faq_icons() as $key => $def ) {
		$opts .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), selected( $icon, $key, false ), esc_html( $def[0] ) );
	}
	wp_nonce_field( 'nolasharon_faq_topic', 'nolasharon_faq_topic_nonce' );
	$rows = array(
		array( 'ns_icon', __( 'Icon', 'nolasharon' ), '<select name="ns_icon" id="ns_icon">' . $opts . '</select>' ),
		array( 'ns_color', __( 'Dot colour', 'nolasharon' ), sprintf( '<input type="color" name="ns_color" id="ns_color" value="%s">', esc_attr( $color ? $color : '#8c6448' ) ) ),
	);
	foreach ( $rows as $row ) {
		if ( $edit ) {
			printf( '<tr class="form-field"><th scope="row"><label for="%1$s">%2$s</label></th><td>%3$s</td></tr>', esc_attr( $row[0] ), esc_html( $row[1] ), $row[2] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		} else {
			printf( '<div class="form-field"><label for="%1$s">%2$s</label>%3$s</div>', esc_attr( $row[0] ), esc_html( $row[1] ), $row[2] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
	}
}
add_action( 'ns_faq_topic_add_form_fields', 'nolasharon_faq_topic_fields' );
add_action( 'ns_faq_topic_edit_form_fields', 'nolasharon_faq_topic_fields' );

/**
 * Save the topic fields.
 *
 * @param int $term_id Term ID.
 */
function nolasharon_faq_topic_save( $term_id ) {
	if ( ! isset( $_POST['nolasharon_faq_topic_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_faq_topic_nonce'] ), 'nolasharon_faq_topic' ) || ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$icon = isset( $_POST['ns_icon'] ) ? sanitize_key( $_POST['ns_icon'] ) : '';
	update_term_meta( $term_id, 'ns_icon', isset( nolasharon_faq_icons()[ $icon ] ) ? $icon : '' );
	update_term_meta( $term_id, 'ns_color', isset( $_POST['ns_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['ns_color'] ) ) : '' );
}
add_action( 'created_ns_faq_topic', 'nolasharon_faq_topic_save' );
add_action( 'edited_ns_faq_topic', 'nolasharon_faq_topic_save' );

/**
 * Optional link under an answer ("Track your order" → URL).
 */
function nolasharon_faq_meta_box() {
	add_meta_box( 'ns-faq-link', __( 'Link under the answer (optional)', 'nolasharon' ), 'nolasharon_faq_meta_box_html', 'ns_faq', 'side' );
}
add_action( 'add_meta_boxes_ns_faq', 'nolasharon_faq_meta_box' );

/**
 * Link fields.
 *
 * @param WP_Post $post FAQ.
 */
function nolasharon_faq_meta_box_html( $post ) {
	wp_nonce_field( 'nolasharon_faq_link', 'nolasharon_faq_link_nonce' );
	printf(
		'<p><label for="ns_link_label">%1$s</label><input type="text" class="widefat" id="ns_link_label" name="ns_link_label" value="%2$s"></p><p><label for="ns_link_url">%3$s</label><input type="url" class="widefat" id="ns_link_url" name="ns_link_url" value="%4$s"></p>',
		esc_html__( 'Link text', 'nolasharon' ),
		esc_attr( get_post_meta( $post->ID, 'ns_link_label', true ) ),
		esc_html__( 'Link address', 'nolasharon' ),
		esc_attr( get_post_meta( $post->ID, 'ns_link_url', true ) )
	);
}

/**
 * Save the link fields.
 *
 * @param int $post_id FAQ ID.
 */
function nolasharon_faq_link_save( $post_id ) {
	if ( ! isset( $_POST['nolasharon_faq_link_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nolasharon_faq_link_nonce'] ), 'nolasharon_faq_link' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, 'ns_link_label', isset( $_POST['ns_link_label'] ) ? sanitize_text_field( wp_unslash( $_POST['ns_link_label'] ) ) : '' );
	update_post_meta( $post_id, 'ns_link_url', isset( $_POST['ns_link_url'] ) ? esc_url_raw( wp_unslash( $_POST['ns_link_url'] ) ) : '' );
}
add_action( 'save_post_ns_faq', 'nolasharon_faq_link_save' );

/* Seed ---------------------------------------------------------------------------- */

/**
 * Seed topics and questions once (CLAUDE.md §5.9 plus account, tracking and returns
 * questions). Only procedural answers are drafted; everything that depends on policy is a
 * [[CONFIRM: …]] line, so those questions stay hidden until the client answers them. Also
 * puts the FAQs page on the FAQ template.
 */
function nolasharon_seed_faqs() {
	if ( get_option( 'nolasharon_faqs_v1' ) ) {
		return;
	}
	$topics = array(
		'orders'   => array( __( 'Orders & payment', 'nolasharon' ), '#8c6448' ),
		'delivery' => array( __( 'Delivery & pickup', 'nolasharon' ), '#2e7d32' ),
		'sizing'   => array( __( 'Sizing & products', 'nolasharon' ), '#1f4e8c' ),
		'returns'  => array( __( 'Returns & exchanges', 'nolasharon' ), '#8a4b0f' ),
		'support'  => array( __( 'Support', 'nolasharon' ), '#6e4a31' ),
	);
	$ids    = array();
	$order  = 0;
	foreach ( $topics as $slug => $topic ) {
		$term = term_exists( $slug, 'ns_faq_topic' );
		if ( ! $term ) {
			$term = wp_insert_term( $topic[0], 'ns_faq_topic', array( 'slug' => $slug ) );
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$ids[ $slug ] = (int) $term['term_id'];
		update_term_meta( $ids[ $slug ], 'ns_icon', $slug );
		update_term_meta( $ids[ $slug ], 'ns_color', $topic[1] );
		update_term_meta( $ids[ $slug ], 'ns_order', ++$order );
	}

	$p      = function ( $text ) {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->";
	};
	$draft  = '<!-- DRAFT: client to approve -->' . "\n";
	$faqs   = array(
		array( 'orders', 'How do I place an order?', $draft . $p( 'Browse the shop, choose your size and colour on the product page, then tap Add to Cart. When you’re ready, open your cart and select Proceed to Checkout. Enter your details, choose how you’d like to receive your order, and pay to complete it.' ), 'Start shopping', nolasharon_shop_url() ),
		array( 'orders', 'What payment methods do you accept?', $p( '[[CONFIRM: payment methods, e.g. card, bank transfer, USSD]]' ), '', '' ),
		array( 'orders', 'Do I need an account to shop?', $draft . $p( 'No. You can check out as a guest. Creating an account lets you see your orders in one place and check out faster next time.' ), 'Create an account', wc_get_page_permalink( 'myaccount' ) ),
		array( 'orders', 'How do I track my order?', $draft . $p( 'Use our Track your order page with your order number and the email address you used at checkout. If you have an account, you can also see every order in My Account.' ), 'Track your order', nolasharon_page_url( 'track-order' ) ),
		array( 'delivery', 'Where do you deliver?', $p( '[[CONFIRM: delivery areas]]' ), '', '' ),
		array( 'delivery', 'How much does delivery cost?', $p( 'Delivery fees depend on your area and are shown at checkout before you pay. [[CONFIRM: delivery fees]]' ), 'Delivery policy', nolasharon_page_url( 'delivery-policy' ) ),
		array( 'delivery', 'How long does delivery take?', $p( '[[CONFIRM: delivery timelines]]' ), '', '' ),
		array( 'delivery', 'Can I collect my order from a store?', $p( '[[CONFIRM: whether store pickup is offered and from which branches]] Our Ikeja stores remain open, and Ogudu is our newest branch.' ), 'See our stores', nolasharon_page_url( 'store-locations' ) ),
		array( 'sizing', 'How do I choose my size?', $draft . $p( 'Where a product has a size guide, tap Size guide next to the sizes to compare your measurements with the chart. If you’re between sizes, contact us and we’ll help you choose.' ), '', '' ),
		array( 'sizing', 'What happens if a product is out of stock?', $p( 'Sold-out sizes and colours are marked on the product page and can’t be added to your cart. [[CONFIRM: restock information]]' ), '', '' ),
		array( 'returns', 'Can I exchange a product?', $p( '[[CONFIRM: exchange policy: whether exchanges are allowed, the time limit and the condition items must be in]]' ), 'Returns & exchange policy', nolasharon_page_url( 'returns-policy' ) ),
		array( 'returns', 'How do I request a return or exchange?', $p( '[[CONFIRM: steps to request a return or exchange]]' ), '', '' ),
		array( 'support', 'How do I contact customer support?', $draft . $p( 'Our Contact page lists every way to reach us, and you can send us a message from there.' ), 'Contact us', nolasharon_page_url( 'contact' ) ),
	);
	foreach ( $faqs as $i => $faq ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'ns_faq',
				'post_status'  => 'publish',
				'post_title'   => $faq[1],
				'post_content' => $faq[2],
				'menu_order'   => $i + 1,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			if ( isset( $ids[ $faq[0] ] ) ) {
				wp_set_object_terms( $id, $ids[ $faq[0] ], 'ns_faq_topic' );
			}
			if ( $faq[3] ) {
				update_post_meta( $id, 'ns_link_label', $faq[3] );
				update_post_meta( $id, 'ns_link_url', $faq[4] );
			}
		}
	}

	$page = get_page_by_path( 'faqs' );
	if ( $page ) {
		update_post_meta( $page->ID, '_wp_page_template', 'page-templates/faq.php' );
	}
	update_option( 'nolasharon_faqs_v1', 1 );
}
add_action( 'after_switch_theme', 'nolasharon_seed_faqs', 20 );
add_action( 'admin_init', 'nolasharon_seed_faqs' );

/* Data and schema ------------------------------------------------------------------ */

/**
 * Topics in display order, with icon path, colour and how many visible questions each has.
 *
 * @param array $faqs From nolasharon_faqs().
 * @return array[] { term, path, color, count }
 */
function nolasharon_faq_topics( $faqs ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'ns_faq_topic',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort(
		$terms,
		function ( $a, $b ) {
			return (int) get_term_meta( $a->term_id, 'ns_order', true ) <=> (int) get_term_meta( $b->term_id, 'ns_order', true );
		}
	);
	$icons = nolasharon_faq_icons();
	$out   = array();
	foreach ( $terms as $term ) {
		$count = count( wp_list_filter( $faqs, array( 'topic' => $term->slug ) ) );
		if ( ! $count ) {
			continue;
		}
		$icon  = get_term_meta( $term->term_id, 'ns_icon', true );
		$out[] = array(
			'term'  => $term,
			'path'  => isset( $icons[ $icon ] ) ? $icons[ $icon ][1] : 'M12 12h.01',
			'color' => get_term_meta( $term->term_id, 'ns_color', true ) ? get_term_meta( $term->term_id, 'ns_color', true ) : '#8c6448',
			'count' => $count,
		);
	}
	return $out;
}

/**
 * The questions to show: published, ordered by topic then menu order. Visitors only get
 * questions whose answer is confirmed; editors see the rest flagged as pending.
 *
 * @return array[] { id, question, answer (HTML), topic (slug), link, url, pending }
 */
function nolasharon_faqs() {
	$posts = get_posts(
		array(
			'post_type'      => 'ns_faq',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);
	$faqs  = array();
	foreach ( $posts as $post ) {
		$pending = false !== strpos( $post->post_content, '[[CONFIRM' );
		if ( $pending && ! nolasharon_sees_placeholders() ) {
			continue;
		}
		$terms  = get_the_terms( $post, 'ns_faq_topic' );
		$faqs[] = array(
			'id'       => $post->ID,
			'question' => get_the_title( $post ),
			'answer'   => apply_filters( 'the_content', $post->post_content ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- core filter.
			'topic'    => $terms && ! is_wp_error( $terms ) ? $terms[0]->slug : '',
			'link'     => get_post_meta( $post->ID, 'ns_link_label', true ),
			'url'      => get_post_meta( $post->ID, 'ns_link_url', true ),
			'pending'  => $pending,
		);
	}
	// Group by topic order, keeping menu order within each topic.
	$rank = array();
	foreach ( nolasharon_faq_topics( $faqs ) as $i => $topic ) {
		$rank[ $topic['term']->slug ] = $i;
	}
	usort(
		$faqs,
		function ( $a, $b ) use ( $rank ) {
			$ra = isset( $rank[ $a['topic'] ] ) ? $rank[ $a['topic'] ] : 99;
			$rb = isset( $rank[ $b['topic'] ] ) ? $rank[ $b['topic'] ] : 99;
			return $ra <=> $rb;
		}
	);
	return $faqs;
}

/**
 * FAQPage JSON-LD on the FAQs page, for confirmed answers only.
 */
function nolasharon_faq_schema() {
	if ( ! is_page_template( 'page-templates/faq.php' ) ) {
		return;
	}
	$entities = array();
	foreach ( nolasharon_faqs() as $faq ) {
		if ( $faq['pending'] ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $faq['question'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => trim( wp_strip_all_tags( $faq['answer'] ) ),
			),
		);
	}
	if ( $entities ) {
		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $entities,
			)
		) . "</script>\n";
	}
}
add_action( 'wp_head', 'nolasharon_faq_schema' );

/**
 * FAQ page assets.
 */
function nolasharon_faq_assets() {
	if ( ! is_page_template( 'page-templates/faq.php' ) ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-content', get_template_directory_uri() . '/assets/css/content.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-faq', get_template_directory_uri() . '/assets/js/faq.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script(
		'nolasharon-faq',
		'nsFaq',
		array(
			/* translators: %d: number of questions. */
			'all'     => __( '%d questions', 'nolasharon' ),
			'allOne'  => __( '1 question', 'nolasharon' ),
			/* translators: 1: number of questions, 2: search words. */
			'match'   => __( '%1$d questions match “%2$s”', 'nolasharon' ),
			/* translators: %s: search words. */
			'matchOne' => __( '1 question matches “%s”', 'nolasharon' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_faq_assets', 20 );

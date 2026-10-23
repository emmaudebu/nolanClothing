<?php
/**
 * Placeholder handling and FAQ schema for page content (brief rules 1–2, §5.9, §5.10).
 *
 * Pages may contain "[[CONFIRM: what is needed]]" until the client supplies the facts.
 * Editors see them highlighted; visitors never do. A section (H2 + its content) or an FAQ
 * that has nothing confirmed in it is left out for visitors.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current visitor may see placeholders.
 *
 * @return bool
 */
function nolasharon_sees_placeholders() {
	return current_user_can( 'edit_pages' );
}

/**
 * Remove placeholders and the elements that only held a placeholder.
 *
 * @param string $html Markup.
 * @return string
 */
function nolasharon_strip_placeholders( $html ) {
	$html = preg_replace( '~<(p|li)[^>]*>\s*\[\[CONFIRM:[^\]]*\]\]\s*</\1>~i', '', $html );
	$html = preg_replace( '~\[\[CONFIRM:[^\]]*\]\]~', '', $html );
	$html = preg_replace( '~<ul[^>]*>\s*</ul>~i', '', $html );
	return $html;
}

/**
 * Whether markup has any visible text left.
 *
 * @param string $html Markup.
 * @return bool
 */
function nolasharon_has_text( $html ) {
	return '' !== trim( html_entity_decode( wp_strip_all_tags( $html ) ), " \t\n\r\0\x0B\xC2\xA0" );
}

/**
 * Highlight placeholders for editors; for visitors, drop them and any H2 section left empty.
 *
 * @param string $content Rendered content.
 * @return string
 */
function nolasharon_filter_placeholders( $content ) {
	if ( false === strpos( $content, '[[CONFIRM' ) || is_admin() ) {
		return $content;
	}
	if ( nolasharon_sees_placeholders() ) {
		return preg_replace_callback(
			'~\[\[CONFIRM:\s*([^\]]*)\]\]~',
			function ( $m ) {
				/* translators: %s: what the client still needs to confirm. */
				return '<mark class="confirm">' . esc_html( sprintf( __( 'To confirm: %s', 'nolasharon' ), $m[1] ) ) . '</mark>';
			},
			$content
		);
	}

	$parts = preg_split( '~(<h2[^>]*>.*?</h2>)~is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	$out   = nolasharon_strip_placeholders( array_shift( $parts ) );
	for ( $i = 0; $i < count( $parts ); $i += 2 ) {
		$body = nolasharon_strip_placeholders( isset( $parts[ $i + 1 ] ) ? $parts[ $i + 1 ] : '' );
		if ( nolasharon_has_text( $body ) ) {
			$out .= $parts[ $i ] . $body;
		}
	}
	if ( ! nolasharon_has_text( $out ) ) {
		$out = '<p class="page-pending">' . esc_html__( 'This page is being updated. For help in the meantime, please contact us.', 'nolasharon' ) . ' <a href="' . esc_url( nolasharon_page_url( 'contact' ) ) . '">' . esc_html__( 'Contact Us', 'nolasharon' ) . '</a></p>';
	}
	return $out;
}
add_filter( 'the_content', 'nolasharon_filter_placeholders', 20 );

/**
 * FAQ items (core Details blocks) whose answer is still a placeholder are hidden from visitors.
 *
 * @param string $html  Rendered block.
 * @param array  $block Block.
 * @return string
 */
function nolasharon_filter_faq_block( $html, $block ) {
	if ( 'core/details' !== $block['blockName'] || nolasharon_sees_placeholders() || false === strpos( $html, '[[CONFIRM' ) ) {
		return $html;
	}
	$answer = preg_replace( '~<summary[^>]*>.*?</summary>~is', '', $html );
	return nolasharon_has_text( nolasharon_strip_placeholders( $answer ) ) ? $html : '';
}
add_filter( 'render_block', 'nolasharon_filter_faq_block', 10, 2 );

/**
 * FAQPage JSON-LD on the FAQs page, from its Details blocks with confirmed answers only.
 */
function nolasharon_faq_schema() {
	if ( ! is_page( 'faqs' ) ) {
		return;
	}
	$entities = array();
	foreach ( parse_blocks( get_post_field( 'post_content', get_queried_object_id() ) ) as $block ) {
		if ( 'core/details' !== $block['blockName'] ) {
			continue;
		}
		$html = render_block( $block );
		if ( ! preg_match( '~<summary[^>]*>(.*?)</summary>~is', $html, $m ) ) {
			continue;
		}
		$answer = preg_replace( '~<summary[^>]*>.*?</summary>~is', '', $html );
		if ( false !== strpos( $answer, '[[CONFIRM' ) || ! nolasharon_has_text( $answer ) ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $m[1] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => trim( wp_strip_all_tags( $answer ) ),
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
 * Admin bar reminder of how many placeholders are left on the page being viewed.
 *
 * @param WP_Admin_Bar $bar Admin bar.
 */
function nolasharon_placeholder_admin_bar( $bar ) {
	if ( is_admin() || ! is_singular() || ! nolasharon_sees_placeholders() ) {
		return;
	}
	$count = substr_count( (string) get_post_field( 'post_content', get_queried_object_id() ), '[[CONFIRM' );
	if ( $count ) {
		$bar->add_node(
			array(
				'id'    => 'ns-confirm',
				/* translators: %d: number of placeholders. */
				'title' => esc_html( sprintf( _n( '%d detail to confirm', '%d details to confirm', $count, 'nolasharon' ), $count ) ),
				'href'  => get_edit_post_link( get_queried_object_id() ),
			)
		);
	}
}
add_action( 'admin_bar_menu', 'nolasharon_placeholder_admin_bar', 90 );

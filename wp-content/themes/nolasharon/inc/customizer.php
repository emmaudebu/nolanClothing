<?php
/**
 * Customizer panel "NolaSharon Settings": everything the client edits without a developer.
 * Empty settings hide their element on the front end (brief §8).
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setting definitions, grouped by section.
 *
 * Each field: id => array( label, type, sanitize callback, default, description ).
 *
 * @return array
 */
function nolasharon_settings_schema() {
	$text     = 'sanitize_text_field';
	$textarea = 'sanitize_textarea_field';
	$url      = 'esc_url_raw';

	return array(
		'nolasharon_announcement' => array(
			'title'  => __( 'Announcement bar', 'nolasharon' ),
			'fields' => array(
				'nolasharon_announce_text' => array( __( 'Announcement text', 'nolasharon' ), 'text', $text, '', __( 'Confirmed information only. Leave empty to hide the bar.', 'nolasharon' ) ),
				'nolasharon_announce_link'  => array( __( 'Announcement link (optional)', 'nolasharon' ), 'url', $url, '', '' ),
				'nolasharon_announce_label' => array( __( 'Announcement link text', 'nolasharon' ), 'text', $text, __( 'Find out more', 'nolasharon' ), __( 'e.g. "Find a store". Shown after the text when a link is set.', 'nolasharon' ) ),
			),
		),
		'nolasharon_hero'         => array(
			'title'  => __( 'Homepage hero', 'nolasharon' ),
			'fields' => array(
				'nolasharon_hero_image'       => array( __( 'Campaign image', 'nolasharon' ), 'image', 'absint', 0, __( 'Falls back to the placeholder illustration.', 'nolasharon' ) ),
				'nolasharon_hero_eyebrow'     => array( __( 'Eyebrow (small outlined label above the headline; empty hides it)', 'nolasharon' ), 'text', $text, __( 'NolaSharon Clothing · Lagos', 'nolasharon' ), '' ),
				'nolasharon_hero_title'       => array( __( 'Headline', 'nolasharon' ), 'text', $text, __( 'Your Style, Your Way.', 'nolasharon' ), '' ),
				'nolasharon_hero_text'        => array( __( 'Supporting text', 'nolasharon' ), 'textarea', $textarea, __( 'Discover fashion pieces to complement your style and shop the NolaSharon Clothing collection.', 'nolasharon' ), '' ),
				'nolasharon_hero_btn1_label'  => array( __( 'Main button label', 'nolasharon' ), 'text', $text, __( 'Shop Now', 'nolasharon' ), '' ),
				'nolasharon_hero_btn1_link'   => array( __( 'Main button link (empty = Shop)', 'nolasharon' ), 'url', $url, '', '' ),
				'nolasharon_hero_btn2_label'  => array( __( 'Second button label (empty hides it)', 'nolasharon' ), 'text', $text, __( 'Visit Our Store', 'nolasharon' ), '' ),
				'nolasharon_hero_btn2_link'   => array( __( 'Second button link (empty = Store Locations)', 'nolasharon' ), 'url', $url, '', '' ),
			),
		),
		'nolasharon_brand'        => array(
			'title'  => __( 'Homepage: brand introduction', 'nolasharon' ),
			'fields' => array(
				'nolasharon_brand_title' => array( __( 'Heading', 'nolasharon' ), 'text', $text, __( 'Style and confidence, made easy', 'nolasharon' ), __( 'Draft heading: client to approve.', 'nolasharon' ) ),
				'nolasharon_brand_text'  => array( __( 'Short brand description', 'nolasharon' ), 'textarea', $textarea, '', __( 'Client-approved copy. Leave empty to hide the section.', 'nolasharon' ) ),
				'nolasharon_brand_image'   => array( __( 'Photo 1 (large, tilted left)', 'nolasharon' ), 'image', 'absint', 0, '' ),
				'nolasharon_brand_image_2' => array( __( 'Photo 2 (framed, tilted right)', 'nolasharon' ), 'image', 'absint', 0, '' ),
			),
		),
		'nolasharon_trust'        => array(
			'title'  => __( 'Homepage: customer trust strip', 'nolasharon' ),
			'fields' => array(
				'nolasharon_trust_payment'  => array( __( 'Payment methods line', 'nolasharon' ), 'text', $text, '', __( 'e.g. "Secure payment by card, transfer and USSD", once the gateway is confirmed. Empty hides it.', 'nolasharon' ) ),
				'nolasharon_trust_delivery' => array( __( 'Delivery line', 'nolasharon' ), 'text', $text, '', __( 'e.g. "Delivery across Lagos". Confirmed areas only. Empty hides it.', 'nolasharon' ) ),
			),
		),
		'nolasharon_account'      => array(
			'title'  => __( 'Sign-in page', 'nolasharon' ),
			'fields' => array(
				'nolasharon_auth_image' => array( __( 'Campaign photo beside the sign-in form', 'nolasharon' ), 'image', 'absint', 0, __( 'Client-approved photo. Falls back to the placeholder illustration.', 'nolasharon' ) ),
			),
		),
		'nolasharon_404'          => array(
			'title'  => __( 'Page not found (404)', 'nolasharon' ),
			'fields' => array(
				'nolasharon_404_image' => array( __( 'Image inside the "0"', 'nolasharon' ), 'image', 'absint', 0, __( 'Empty uses the newest product photo.', 'nolasharon' ) ),
			),
		),
		'nolasharon_contact'      => array(
			'title'  => __( 'Contact & social', 'nolasharon' ),
			'fields' => array(
				'nolasharon_phone'      => array( __( 'Business phone', 'nolasharon' ), 'text', $text, '', '' ),
				'nolasharon_whatsapp'   => array( __( 'WhatsApp number (verified, international format, e.g. 2348012345678)', 'nolasharon' ), 'text', $text, '', __( 'The WhatsApp buttons only appear once this is filled in.', 'nolasharon' ) ),
				'nolasharon_wa_message' => array( __( 'Prefilled WhatsApp message', 'nolasharon' ), 'text', $text, __( 'Hi NolaSharon Clothing, I have a question.', 'nolasharon' ), '' ),
				'nolasharon_email'      => array( __( 'Business email', 'nolasharon' ), 'email', 'sanitize_email', '', '' ),
				'nolasharon_hours'      => array( __( 'Customer service hours', 'nolasharon' ), 'textarea', $textarea, '', '' ),
				'nolasharon_instagram'  => array( __( 'Instagram URL', 'nolasharon' ), 'url', $url, '', '' ),
				'nolasharon_facebook'   => array( __( 'Facebook URL', 'nolasharon' ), 'url', $url, '', '' ),
				'nolasharon_tiktok'     => array( __( 'TikTok URL', 'nolasharon' ), 'url', $url, '', '' ),
				'nolasharon_footer_line' => array( __( 'Footer line under the logo', 'nolasharon' ), 'text', $text, __( 'Fashion for women and men. Shop online or visit us in Lagos.', 'nolasharon' ), __( 'Draft: client to approve. Empty hides it.', 'nolasharon' ) ),
			),
		),
		'nolasharon_store'        => array(
			'title'  => __( 'Shop, delivery & pickup', 'nolasharon' ),
			'fields' => array(
				'nolasharon_delivery_info'  => array( __( 'Delivery information snippet', 'nolasharon' ), 'textarea', $textarea, '', __( 'Shown on product pages and in the bag. Confirmed details only. Empty hides it.', 'nolasharon' ) ),
				'nolasharon_returns_info'   => array( __( 'Returns & exchanges snippet', 'nolasharon' ), 'textarea', $textarea, '', __( 'Shown on product pages with a link to the policy. Empty hides it.', 'nolasharon' ) ),
				'nolasharon_pickup_enabled' => array( __( 'Enable store pickup at checkout', 'nolasharon' ), 'checkbox', 'nolasharon_sanitize_checkbox', false, __( 'Only switch on once the client confirms which branches offer pickup. Branches also need "Pickup available" ticked.', 'nolasharon' ) ),
				'nolasharon_fulfilment_statuses' => array( __( 'Use "Dispatched" and "Ready for pickup" order statuses', 'nolasharon' ), 'checkbox', 'nolasharon_sanitize_checkbox', false, __( 'Adds two order statuses staff can set, and a matching step on the customer\'s order tracker. Only switch on once the client confirms the fulfilment process.', 'nolasharon' ) ),
				'nolasharon_price_ranges'   => array( __( 'Shop price filter bands', 'nolasharon' ), 'textarea', $textarea, "0-30000\n30000-50000\n50000-", __( 'One band per line as min-max in naira, e.g. 0-30000, 30000-50000, 50000- (an empty end means no limit). Adjust to suit the real prices.', 'nolasharon' ) ),
				'nolasharon_new_days'       => array( __( '"New" badge: product age in days', 'nolasharon' ), 'number', 'absint', 30, '' ),
				'nolasharon_video'          => array( __( 'Homepage video (YouTube URL, optional)', 'nolasharon' ), 'url', $url, '', __( 'Shown inside the brand introduction when set. Client-supplied videos only.', 'nolasharon' ) ),
				'nolasharon_video_title'    => array( __( 'Video section heading', 'nolasharon' ), 'text', $text, '', '' ),
			),
		),
	);
}

/**
 * Sanitize a checkbox value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function nolasharon_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Read a theme setting with its schema default.
 *
 * @param string $id Setting ID.
 * @return mixed
 */
function nolasharon_setting( $id ) {
	static $defaults = null;
	if ( null === $defaults ) {
		$defaults = array();
		foreach ( nolasharon_settings_schema() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$defaults[ $key ] = $field[3];
			}
		}
	}
	$value = get_theme_mod( $id, isset( $defaults[ $id ] ) ? $defaults[ $id ] : '' );
	return is_string( $value ) ? trim( $value ) : $value;
}

/**
 * Register the panel, sections and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function nolasharon_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'nolasharon',
		array(
			'title'    => __( 'NolaSharon Settings', 'nolasharon' ),
			'priority' => 30,
		)
	);

	foreach ( nolasharon_settings_schema() as $section_id => $section ) {
		$wp_customize->add_section(
			$section_id,
			array(
				'title' => $section['title'],
				'panel' => 'nolasharon',
			)
		);
		foreach ( $section['fields'] as $id => $field ) {
			list( $label, $type, $sanitize, $default, $description ) = $field;
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $default,
					'sanitize_callback' => $sanitize,
				)
			);
			$args = array(
				'label'       => $label,
				'description' => $description,
				'section'     => $section_id,
			);
			if ( 'image' === $type ) {
				$wp_customize->add_control(
					new WP_Customize_Media_Control(
						$wp_customize,
						$id,
						array_merge( $args, array( 'mime_type' => 'image' ) )
					)
				);
			} else {
				$wp_customize->add_control( $id, array_merge( $args, array( 'type' => $type ) ) );
			}
		}
	}
}
add_action( 'customize_register', 'nolasharon_customize_register' );

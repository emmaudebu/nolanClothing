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
 * Each field: id => array( label, type, sanitize callback, default, description, extra ), where
 * the optional extra holds control args (choices, input_attrs) and the setting transport.
 *
 * @return array
 */
function nolasharon_settings_schema() {
	$text     = 'sanitize_text_field';
	$textarea = 'sanitize_textarea_field';
	$url      = 'esc_url_raw';
	$px       = function ( $min, $max ) {
		return array(
			'input_attrs' => array(
				'min'  => $min,
				'max'  => $max,
				'step' => 1,
			),
			'transport'   => 'postMessage',
		);
	};
	$align    = array(
		'left'   => __( 'Left', 'nolasharon' ),
		'center' => __( 'Centre', 'nolasharon' ),
		'right'  => __( 'Right', 'nolasharon' ),
	);

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
		'nolasharon_header_logo'  => array(
			'title'       => __( 'Header logo', 'nolasharon' ),
			'description' => __( 'Sizes in pixels. Width 0 = automatic (keeps the logo’s shape). If you set both, the logo fits inside that box without stretching. To change the logo file itself, use Site Identity.', 'nolasharon' ),
			'fields'      => array(
				'nolasharon_logo_h'   => array( __( 'Height on computers', 'nolasharon' ), 'number', 'nolasharon_sanitize_px', 46, '', $px( 20, 120 ) ),
				'nolasharon_logo_w'   => array( __( 'Width on computers (0 = automatic)', 'nolasharon' ), 'number', 'nolasharon_sanitize_px', 0, '', $px( 0, 480 ) ),
				'nolasharon_logo_h_m' => array( __( 'Height on tablets and phones', 'nolasharon' ), 'number', 'nolasharon_sanitize_px', 36, __( 'Below 1240px wide, where the header shows the wordmark only. Very small phones show it slightly smaller.', 'nolasharon' ), $px( 20, 70 ) ),
				'nolasharon_logo_w_m' => array( __( 'Width on tablets and phones (0 = automatic)', 'nolasharon' ), 'number', 'nolasharon_sanitize_px', 0, __( 'Keep it under about 180px so the menu and cart icons still fit on phones.', 'nolasharon' ), $px( 0, 240 ) ),
			),
		),
		'nolasharon_footer'       => array(
			'title'  => __( 'Footer', 'nolasharon' ),
			'fields' => array(
				'nolasharon_footer_logo_h'   => array( __( 'Logo height (px)', 'nolasharon' ), 'number', 'nolasharon_sanitize_px', 124, '', $px( 30, 300 ) ),
				'nolasharon_footer_logo_w'   => array( __( 'Logo width (px, 0 = automatic)', 'nolasharon' ), 'number', 'nolasharon_sanitize_px', 0, '', $px( 0, 400 ) ),
				'nolasharon_footer_line'     => array( __( 'Line under the logo', 'nolasharon' ), 'textarea', $textarea, __( 'Fashion for women and men. Shop online or visit us in Lagos.', 'nolasharon' ), __( 'Draft: client to approve. Empty hides it.', 'nolasharon' ) ),
				'nolasharon_footer_layout'   => array(
					__( 'Layout', 'nolasharon' ),
					'select',
					'nolasharon_sanitize_choice',
					'side',
					'',
					array(
						'choices'   => array(
							'side'         => __( 'Logo on the left, links on the right', 'nolasharon' ),
							'side-reverse' => __( 'Links on the left, logo on the right', 'nolasharon' ),
							'stacked'      => __( 'Logo above the links', 'nolasharon' ),
						),
						'transport' => 'postMessage',
					),
				),
				'nolasharon_footer_align'    => array(
					__( 'Alignment of the logo, line and links', 'nolasharon' ),
					'select',
					'nolasharon_sanitize_choice',
					'left',
					'',
					array(
						'choices'   => $align,
						'transport' => 'postMessage',
					),
				),
				'nolasharon_footer_h_shop'    => array( __( 'Column heading: shop links', 'nolasharon' ), 'text', $text, __( 'Shop', 'nolasharon' ), __( 'Empty hides the column.', 'nolasharon' ) ),
				'nolasharon_footer_h_help'    => array( __( 'Column heading: help links', 'nolasharon' ), 'text', $text, __( 'Help', 'nolasharon' ), __( 'Empty hides the column.', 'nolasharon' ) ),
				'nolasharon_footer_h_visit'   => array( __( 'Column heading: stores', 'nolasharon' ), 'text', $text, __( 'Visit us', 'nolasharon' ), __( 'Empty hides the column.', 'nolasharon' ) ),
				'nolasharon_footer_h_contact' => array( __( 'Column heading: contact', 'nolasharon' ), 'text', $text, __( 'Contact', 'nolasharon' ), __( 'Empty hides the column.', 'nolasharon' ) ),
				'nolasharon_copyright'       => array( __( 'Copyright text', 'nolasharon' ), 'text', $text, __( '© {year} NolaSharon Clothing', 'nolasharon' ), __( '{year} is replaced with the current year. Empty hides it.', 'nolasharon' ) ),
				'nolasharon_footer_policies' => array( __( 'Show Privacy Policy and Terms & Conditions links', 'nolasharon' ), 'checkbox', 'nolasharon_sanitize_checkbox', true, '' ),
				'nolasharon_footer_bottom_align' => array(
					__( 'Alignment of the copyright bar', 'nolasharon' ),
					'select',
					'nolasharon_sanitize_choice',
					'spread',
					'',
					array(
						'choices'   => array_merge( array( 'spread' => __( 'Copyright left, links right', 'nolasharon' ) ), $align ),
						'transport' => 'postMessage',
					),
				),
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
 * Sanitize a pixel size (0 allowed, capped at 600).
 *
 * @param mixed $value Raw value.
 * @return int
 */
function nolasharon_sanitize_px( $value ) {
	return min( 600, absint( $value ) );
}

/**
 * Sanitize a select value against its control's choices.
 *
 * @param string               $value   Raw value.
 * @param WP_Customize_Setting $setting Setting.
 * @return string
 */
function nolasharon_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control ? $control->choices : array();
	return isset( $choices[ $value ] ) ? $value : $setting->default;
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
				'title'       => $section['title'],
				'description' => isset( $section['description'] ) ? $section['description'] : '',
				'panel'       => 'nolasharon',
			)
		);
		foreach ( $section['fields'] as $id => $field ) {
			list( $label, $type, $sanitize, $default, $description ) = $field;
			$extra = isset( $field[5] ) ? $field[5] : array();
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $default,
					'sanitize_callback' => $sanitize,
					'transport'         => isset( $extra['transport'] ) ? $extra['transport'] : 'refresh',
				)
			);
			unset( $extra['transport'] );
			$args = array_merge(
				array(
					'label'       => $label,
					'description' => $description,
					'section'     => $section_id,
				),
				$extra
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

/**
 * Logo sizes from the Customizer, as CSS variables (0 = automatic).
 */
function nolasharon_logo_size_css() {
	$px   = function ( $id ) {
		$v = (int) nolasharon_setting( $id );
		return $v ? $v . 'px' : 'auto';
	};
	$vars = array(
		'--ns-logo-h'   => $px( 'nolasharon_logo_h' ),
		'--ns-logo-w'   => $px( 'nolasharon_logo_w' ),
		'--ns-logo-h-m' => $px( 'nolasharon_logo_h_m' ),
		'--ns-logo-w-m' => $px( 'nolasharon_logo_w_m' ),
		'--ns-flogo-h'  => $px( 'nolasharon_footer_logo_h' ),
		'--ns-flogo-w'  => $px( 'nolasharon_footer_logo_w' ),
	);
	$css  = '';
	foreach ( $vars as $name => $value ) {
		$css .= $name . ':' . $value . ';';
	}
	echo '<style id="nolasharon-logo-sizes">:root{' . esc_html( $css ) . "}</style>\n";
}
add_action( 'wp_head', 'nolasharon_logo_size_css', 20 );

/**
 * Live preview for logo sizes and footer alignment.
 */
function nolasharon_customize_preview_js() {
	wp_enqueue_script( 'nolasharon-customize-preview', get_template_directory_uri() . '/assets/js/customize-preview.js', array( 'customize-preview' ), NOLASHARON_VERSION, true );
}
add_action( 'customize_preview_init', 'nolasharon_customize_preview_js' );

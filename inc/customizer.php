<?php
/**
 * Customizer settings for contact details and homepage content the client will change.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the "NolaSharon" Customizer panel.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function nolasharon_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'nolasharon_contact',
		array(
			'title'    => __( 'NolaSharon: contact & social', 'nolasharon' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'nolasharon_whatsapp'  => array( __( 'WhatsApp number (international format, e.g. 2348012345678)', 'nolasharon' ), 'sanitize_text_field' ),
		'nolasharon_phone'     => array( __( 'Phone number for "Call Us"', 'nolasharon' ), 'sanitize_text_field' ),
		'nolasharon_instagram' => array( __( 'Instagram URL', 'nolasharon' ), 'esc_url_raw' ),
		'nolasharon_tiktok'    => array( __( 'TikTok URL', 'nolasharon' ), 'esc_url_raw' ),
	);
	foreach ( $fields as $id => $field ) {
		$wp_customize->add_setting( $id, array( 'sanitize_callback' => $field[1] ) );
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field[0],
				'section' => 'nolasharon_contact',
				'type'    => 'esc_url_raw' === $field[1] ? 'url' : 'text',
			)
		);
	}

	$wp_customize->add_section(
		'nolasharon_home',
		array(
			'title'    => __( 'NolaSharon: homepage', 'nolasharon' ),
			'priority' => 31,
		)
	);
	$wp_customize->add_setting( 'nolasharon_video', array( 'sanitize_callback' => 'esc_url_raw' ) );
	$wp_customize->add_control(
		'nolasharon_video',
		array(
			'label'       => __( 'Production space video (YouTube URL)', 'nolasharon' ),
			'description' => __( 'Shown in "Take a walk in our production space".', 'nolasharon' ),
			'section'     => 'nolasharon_home',
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'nolasharon_customize_register' );

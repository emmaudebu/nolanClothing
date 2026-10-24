<?php
/**
 * Contact Us (CONTENT-PAGES.md §2): channel helpers, the Fluent Forms "Contact" form
 * (created once by the theme, then edited in Fluent Forms), its notification address kept
 * in step with the Customizer email, and extra checks on the message. Entries stay in
 * wp-admin; nothing is sent to any other service.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether Fluent Forms is active.
 *
 * @return bool
 */
function nolasharon_has_fluentform() {
	return defined( 'FLUENTFORM_VERSION' ) && function_exists( 'wpFluent' );
}

/**
 * The Contact form's ID, if it still exists.
 *
 * @return int
 */
function nolasharon_contact_form_id() {
	global $wpdb;
	$id = (int) get_option( 'nolasharon_contact_form' );
	if ( ! $id || ! nolasharon_has_fluentform() ) {
		return 0;
	}
	$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}fluentform_forms WHERE id = %d AND status = 'published'", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return $exists ? $id : 0;
}

/**
 * Where contact messages are emailed: the Customizer business email, else the site admin.
 *
 * @return string
 */
function nolasharon_contact_recipient() {
	$email = nolasharon_setting( 'nolasharon_email' );
	return is_email( $email ) ? $email : '{wp.admin_email}';
}

/**
 * A Fluent Forms field definition.
 *
 * @param string $element  Element type.
 * @param string $name     Field name.
 * @param string $label    Label.
 * @param array  $settings Extra settings.
 * @param array  $attrs    Extra attributes.
 * @return array
 */
function nolasharon_ff_field( $element, $name, $label, $settings = array(), $attrs = array() ) {
	static $index = 0;
	return array(
		'index'          => $index++,
		'element'        => $element,
		'attributes'     => array_merge(
			array(
				'name'        => $name,
				'value'       => '',
				'class'       => '',
				'placeholder' => '',
			),
			$attrs
		),
		'settings'       => array_merge(
			array(
				'container_class'    => '',
				'label'              => $label,
				'label_placement'    => '',
				'admin_field_label'  => $label,
				'help_message'       => '',
				'validation_rules'   => array(
					'required' => array(
						'value'   => false,
						'message' => __( 'This field is required.', 'nolasharon' ),
					),
				),
				'conditional_logics' => array(),
			),
			$settings
		),
		'editor_options' => array( 'template' => 'inputText' ),
		'uniqElKey'      => 'el_ns_' . $name,
	);
}

/**
 * Create the "Contact" form once, with honeypot spam protection switched on. The client can
 * then edit wording, fields and notifications under Fluent Forms.
 */
function nolasharon_seed_contact_form() {
	global $wpdb;
	if ( ! nolasharon_has_fluentform() || nolasharon_contact_form_id() || ! ( current_user_can( 'manage_options' ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) ) {
		return;
	}
	$required = function ( $message ) {
		return array(
			'required' => array(
				'value'   => true,
				'message' => $message,
			),
		);
	};

	$fields = array(
		nolasharon_ff_field( 'input_text', 'full_name', __( 'Your name', 'nolasharon' ), array( 'validation_rules' => $required( __( 'Please enter your name.', 'nolasharon' ) ) ), array( 'type' => 'text', 'maxlength' => '80' ) ),
		nolasharon_ff_field(
			'input_email',
			'email',
			__( 'Email', 'nolasharon' ),
			array(
				'validation_rules' => $required( __( 'Please enter your email address.', 'nolasharon' ) ) + array(
					'email' => array(
						'value'   => true,
						'message' => __( 'Please enter a valid email address, e.g. name@example.com.', 'nolasharon' ),
					),
				),
			),
			array( 'type' => 'email' )
		),
		nolasharon_ff_field( 'input_text', 'phone', __( 'Phone (optional)', 'nolasharon' ), array(), array( 'type' => 'text', 'maxlength' => '20' ) ),
		array_merge(
			nolasharon_ff_field(
				'input_radio',
				'topic',
				__( 'What is it about?', 'nolasharon' ),
				array(
					'display_type'       => 'inline',
					'advanced_options'   => array(
						array(
							'label' => __( 'An order', 'nolasharon' ),
							'value' => 'order',
						),
						array(
							'label' => __( 'A product', 'nolasharon' ),
							'value' => 'product',
						),
						array(
							'label' => __( 'A store visit', 'nolasharon' ),
							'value' => 'store',
						),
						array(
							'label' => __( 'Something else', 'nolasharon' ),
							'value' => 'other',
						),
					),
					'validation_rules'   => $required( __( 'Please choose what your message is about.', 'nolasharon' ) ),
					'randomize_options'  => 'no',
					'container_class'    => 'ns-ff-chips',
				),
				array( 'type' => 'radio' )
			),
			array(
				'editor_options' => array(
					'template' => 'inputCheckable',
				),
			)
		),
		nolasharon_ff_field(
			'input_text',
			'order_number',
			__( 'Order number', 'nolasharon' ),
			array(
				'prefix_label'       => '#NS-',
				'container_class'    => 'ns-ff-order',
				'help_message'       => __( 'You’ll find it in your order confirmation email.', 'nolasharon' ),
				'conditional_logics' => array(
					'type'       => 'any',
					'status'     => true,
					'conditions' => array(
						array(
							'field'    => 'topic',
							'value'    => 'order',
							'operator' => '=',
						),
					),
				),
			),
			array(
				'type'        => 'text',
				'maxlength'   => '12',
				'placeholder' => '1234',
			)
		),
		array_merge(
			nolasharon_ff_field( 'textarea', 'message', __( 'Message', 'nolasharon' ), array( 'validation_rules' => $required( __( 'Please write your message.', 'nolasharon' ) ) ), array( 'rows' => 5, 'cols' => 2, 'maxlength' => '1000', 'placeholder' => __( 'How can we help?', 'nolasharon' ) ) ),
			array( 'editor_options' => array( 'template' => 'inputTextarea' ) )
		),
		array(
			'index'          => 99,
			'element'        => 'custom_html',
			'attributes'     => array(),
			'settings'       => array(
				/* translators: %s: Privacy Policy link. */
				'html_codes'         => '<p class="ns-ff-consent">' . sprintf( __( 'We only use your details to reply to you. See our %s.', 'nolasharon' ), '<a href="' . esc_url( nolasharon_policy_url( 'privacy' ) ) . '">' . __( 'Privacy Policy', 'nolasharon' ) . '</a>' ) . '</p>',
				'conditional_logics' => array(),
				'container_class'    => '',
			),
			'editor_options' => array( 'template' => 'customHTML' ),
			'uniqElKey'      => 'el_ns_consent',
		),
	);

	$form = array(
		'fields'       => $fields,
		'submitButton' => array(
			'uniqElKey'      => 'el_ns_submit',
			'element'        => 'button',
			'attributes'     => array(
				'type'  => 'submit',
				'class' => 'ns-ff-submit',
			),
			'settings'       => array(
				'align'           => 'left',
				'button_style'    => '',
				'container_class' => '',
				'help_message'    => '',
				'button_size'     => 'md',
				'button_ui'       => array(
					'type'    => 'default',
					'text'    => __( 'Send message', 'nolasharon' ),
					'img_url' => '',
				),
			),
			'editor_options' => array( 'title' => __( 'Submit Button', 'fluentform' ) ),
		),
	);

	$now = current_time( 'mysql' );
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prefix . 'fluentform_forms',
		array(
			'title'       => 'Contact',
			'status'      => 'published',
			'form_fields' => wp_json_encode( $form ),
			'has_payment' => 0,
			'type'        => 'form',
			'created_by'  => get_current_user_id(),
			'created_at'  => $now,
			'updated_at'  => $now,
		)
	);
	$id = (int) $wpdb->insert_id;
	if ( ! $id ) {
		return;
	}

	$meta = array(
		'formSettings'         => array(
			'confirmation' => array(
				'redirectTo'           => 'samePage',
				'messageToShow'        => __( 'Thank you. Your message has been sent and we’ll reply as soon as we can.', 'nolasharon' ),
				'customPage'           => null,
				'samePageFormBehavior' => 'hide_form',
				'customUrl'            => null,
			),
			'restrictions' => array(
				'denyEmptySubmission' => array(
					'enabled' => true,
					'message' => __( 'Please fill in the form before sending.', 'nolasharon' ),
				),
			),
			'layout'       => array(
				'labelPlacement'        => 'top',
				'helpMessagePlacement'  => 'under_input',
				'errorMessagePlacement' => 'inline',
				'cssClassName'          => 'ns-ff',
				'asteriskPlacement'     => 'asterisk-right',
			),
		),
		'notifications'        => array(
			'name'           => 'Contact message',
			'sendTo'         => array(
				'type'  => 'email',
				'email' => nolasharon_contact_recipient(),
				'field' => 'email',
			),
			'fromName'       => '',
			'fromEmail'      => '',
			'replyTo'        => '{inputs.email}',
			'bcc'            => '',
			'subject'        => 'Contact form: {inputs.full_name}',
			'message'        => '<p>{all_data}</p><p>Sent from: {embed_post.permalink}</p>',
			'conditionals'   => array(
				'status'     => false,
				'type'       => 'all',
				'conditions' => array(),
			),
			'enabled'        => true,
			'email_template' => '',
		),
		'template_name'        => 'nolasharon_contact',
		'_primary_email_field' => 'email',
	);
	foreach ( $meta as $key => $value ) {
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . 'fluentform_form_meta',
			array(
				'form_id'  => $id,
				'meta_key' => $key, // phpcs:ignore WordPress.DB.SlowDBQuery
				'value'    => is_array( $value ) ? wp_json_encode( $value ) : $value,
			)
		);
	}

	// Global Fluent Forms settings: honeypot on, and no IP addresses stored with entries
	// (only what's needed to reply; the Privacy Policy relies on this).
	$global                               = (array) get_option( '_fluentform_global_form_settings', array() );
	$global['misc']                       = isset( $global['misc'] ) ? (array) $global['misc'] : array();
	$global['misc']['honeypotStatus']     = 'yes';
	$global['misc']['isIpLogingDisabled'] = true;
	update_option( '_fluentform_global_form_settings', $global );

	update_option( 'nolasharon_contact_form', $id );
}
add_action( 'admin_init', 'nolasharon_seed_contact_form' );

/**
 * Keep the notification address in step when the business email changes in the Customizer.
 */
function nolasharon_sync_contact_recipient() {
	global $wpdb;
	$id = nolasharon_contact_form_id();
	if ( ! $id ) {
		return;
	}
	$table = $wpdb->prefix . 'fluentform_form_meta';
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT id, value FROM {$table} WHERE form_id = %d AND meta_key = 'notifications'", $id ) ); // phpcs:ignore WordPress.DB
	foreach ( $rows as $row ) {
		$value = json_decode( $row->value, true );
		if ( isset( $value['name'] ) && 'Contact message' === $value['name'] ) {
			$value['sendTo']['email'] = nolasharon_contact_recipient();
			$wpdb->update( $table, array( 'value' => wp_json_encode( $value ) ), array( 'id' => $row->id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}
	}
}
add_action( 'customize_save_after', 'nolasharon_sync_contact_recipient' );

/**
 * Server-side checks the form builder can't express: message length, a sensible phone
 * number, and an order number when the message is about an order.
 *
 * @param array  $errors Errors keyed by field name.
 * @param array  $data   Submitted data.
 * @param object $form   Form.
 * @return array
 */
function nolasharon_contact_validate( $errors, $data, $form ) {
	if ( (int) $form->id !== nolasharon_contact_form_id() ) {
		return $errors;
	}
	$message = isset( $data['message'] ) ? trim( (string) $data['message'] ) : '';
	if ( '' !== $message && mb_strlen( $message ) < 10 ) {
		$errors['message'] = array( __( 'Please write at least 10 characters so we can help.', 'nolasharon' ) );
	} elseif ( mb_strlen( $message ) > 1000 ) {
		$errors['message'] = array( __( 'Please keep your message under 1,000 characters.', 'nolasharon' ) );
	}
	$phone = isset( $data['phone'] ) ? trim( (string) $data['phone'] ) : '';
	if ( '' !== $phone && ! preg_match( '/^\+?[0-9 ()-]{7,20}$/', $phone ) ) {
		$errors['phone'] = array( __( 'Please enter a valid phone number, e.g. 0801 234 5678.', 'nolasharon' ) );
	}
	if ( isset( $data['topic'] ) && 'order' === $data['topic'] ) {
		$order = isset( $data['order_number'] ) ? preg_replace( '/\D/', '', (string) $data['order_number'] ) : '';
		if ( '' === $order ) {
			$errors['order_number'] = array( __( 'Please enter your order number.', 'nolasharon' ) );
		}
	}
	return $errors;
}
add_filter( 'fluentform/validation_errors', 'nolasharon_contact_validate', 10, 3 );

/**
 * Leave Fluent Forms' own stylesheet off the Contact page; the theme styles the form.
 */
function nolasharon_contact_assets() {
	if ( ! is_page_template( 'page-templates/contact.php' ) ) {
		return;
	}
	wp_enqueue_style( 'nolasharon-content', get_template_directory_uri() . '/assets/css/content.css', array( 'nolasharon' ), NOLASHARON_VERSION );
	wp_enqueue_script( 'nolasharon-contact', get_template_directory_uri() . '/assets/js/contact.js', array( 'nolasharon-ui' ), NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_localize_script(
		'nolasharon-contact',
		'nsContact',
		array(
			/* translators: %s: first name. */
			'thanks'  => __( 'Thank you, %s!', 'nolasharon' ),
			'thanks0' => __( 'Thank you!', 'nolasharon' ),
			/* translators: %s: email address. */
			'sent'    => __( 'Your message has been sent. We’ll reply to %s as soon as we can.', 'nolasharon' ),
			'sent0'   => __( 'Your message has been sent. We’ll reply as soon as we can.', 'nolasharon' ),
			/* translators: 1: characters typed, 2: limit. */
			'count'   => __( '%1$d / %2$d', 'nolasharon' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_contact_assets', 20 );

/**
 * Drop Fluent Forms' stylesheets on the Contact page; content.css styles the form.
 */
function nolasharon_contact_ff_styles() {
	if ( is_page_template( 'page-templates/contact.php' ) ) {
		wp_dequeue_style( 'fluent-form-styles' );
		wp_dequeue_style( 'fluentform-public-default' );
	}
}
add_action( 'wp_print_styles', 'nolasharon_contact_ff_styles', 100 );
add_action( 'wp_print_footer_scripts', 'nolasharon_contact_ff_styles', 1 );

/* Channels ------------------------------------------------------------------------- */

/**
 * "+234 801 234 5678" from 2348012345678 or 08012345678.
 *
 * @param string $number Number as entered.
 * @return string
 */
function nolasharon_format_phone( $number ) {
	$digits = preg_replace( '/\D/', '', $number );
	if ( 0 === strpos( $digits, '0' ) && 11 === strlen( $digits ) ) {
		$digits = '234' . substr( $digits, 1 );
	}
	if ( 0 === strpos( $digits, '234' ) && 13 === strlen( $digits ) ) {
		return '+234 ' . substr( $digits, 3, 3 ) . ' ' . substr( $digits, 6, 3 ) . ' ' . substr( $digits, 9 );
	}
	return $number;
}

/**
 * Contact channels with a value set in the Customizer (each becomes a card).
 *
 * @return array[] { key, title, value, url, icon, external }
 */
function nolasharon_contact_channels() {
	$out      = array();
	$whatsapp = nolasharon_whatsapp_url( __( 'Hi, I have a question.', 'nolasharon' ) );
	if ( $whatsapp ) {
		$out[] = array(
			'key'      => 'whatsapp',
			'title'    => __( 'WhatsApp', 'nolasharon' ),
			'value'    => nolasharon_format_phone( nolasharon_setting( 'nolasharon_whatsapp' ) ),
			'url'      => $whatsapp,
			'icon'     => 'whatsapp',
			'external' => true,
		);
	}
	$phone = nolasharon_setting( 'nolasharon_phone' );
	if ( $phone ) {
		$out[] = array(
			'key'      => 'phone',
			'title'    => __( 'Call us', 'nolasharon' ),
			'value'    => nolasharon_format_phone( $phone ),
			'url'      => nolasharon_tel_url( $phone ),
			'icon'     => 'phone',
			'external' => false,
		);
	}
	$email = nolasharon_setting( 'nolasharon_email' );
	if ( $email ) {
		$out[] = array(
			'key'      => 'email',
			'title'    => __( 'Email', 'nolasharon' ),
			'value'    => $email,
			'url'      => 'mailto:' . $email,
			'icon'     => 'mail',
			'external' => false,
		);
	}
	return $out;
}

/**
 * Social profiles with a URL set, with a handle worked out from the address.
 *
 * @return array[] { label, url, handle }
 */
function nolasharon_contact_socials() {
	$out = array();
	foreach (
		array(
			'nolasharon_instagram' => __( 'Instagram', 'nolasharon' ),
			'nolasharon_facebook'  => __( 'Facebook', 'nolasharon' ),
			'nolasharon_tiktok'    => __( 'TikTok', 'nolasharon' ),
		) as $id => $label
	) {
		$url = nolasharon_setting( $id );
		if ( ! $url ) {
			continue;
		}
		$path  = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
		$parts = $path ? explode( '/', $path ) : array();
		$out[] = array(
			'label'  => $label,
			'url'    => $url,
			'handle' => $parts ? '@' . ltrim( $parts[0], '@' ) : $label,
		);
	}
	return $out;
}

/**
 * Customer service hours as rows ("Mon–Fri: 9am–6pm" → day, time).
 *
 * @return array[] { day, time }
 */
function nolasharon_contact_hours() {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) nolasharon_setting( 'nolasharon_hours' ) ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts  = preg_split( '/\s*(?::\s|\t|\s[–-]{2}\s|\s\|\s)\s*/u', $line, 2 );
		$rows[] = array(
			'day'  => $parts[0],
			'time' => isset( $parts[1] ) ? $parts[1] : '',
		);
	}
	return $rows;
}

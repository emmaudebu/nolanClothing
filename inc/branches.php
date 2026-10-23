<?php
/**
 * Branches: a private post type the client edits in wp-admin (Branches menu).
 * Feeds the Store Locations page, the homepage spotlight, the footer and store pickup.
 *
 * Every branch has its own fields; nothing is copied from one branch to another (brief §7.3).
 * Empty fields are hidden on the front end.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the ns_branch post type.
 */
function nolasharon_register_branches() {
	register_post_type(
		'ns_branch',
		array(
			'labels'          => array(
				'name'          => __( 'Branches', 'nolasharon' ),
				'singular_name' => __( 'Branch', 'nolasharon' ),
				'add_new_item'  => __( 'Add new branch', 'nolasharon' ),
				'edit_item'     => __( 'Edit branch', 'nolasharon' ),
				'all_items'     => __( 'All branches', 'nolasharon' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 56,
			'menu_icon'       => 'dashicons-store',
			'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'page',
		)
	);
}
add_action( 'init', 'nolasharon_register_branches' );

/**
 * Branch fields: key => array( label, type, help ).
 *
 * @return array
 */
function nolasharon_branch_fields() {
	return array(
		'area'                => array( __( 'Area', 'nolasharon' ), 'text', __( 'e.g. Ikeja or Ogudu.', 'nolasharon' ) ),
		'status'              => array( __( 'Status', 'nolasharon' ), 'status', '' ),
		'opening_date'        => array( __( 'Opening date', 'nolasharon' ), 'date', __( 'Only for branches that are opening soon. Leave empty until confirmed.', 'nolasharon' ) ),
		'is_new'              => array( __( 'Show a "New" badge', 'nolasharon' ), 'checkbox', '' ),
		'address'             => array( __( 'Full address', 'nolasharon' ), 'textarea', __( 'Confirmed address only. Hidden on the site while empty.', 'nolasharon' ) ),
		'landmark'            => array( __( 'Nearest landmark', 'nolasharon' ), 'text', '' ),
		'hours'               => array( __( 'Opening hours', 'nolasharon' ), 'textarea', __( 'One line per day or range, e.g. "Mon–Sat: 9am–7pm".', 'nolasharon' ) ),
		'phone'               => array( __( 'Branch phone', 'nolasharon' ), 'text', '' ),
		'whatsapp'            => array( __( 'Branch WhatsApp (optional, international format)', 'nolasharon' ), 'text', '' ),
		'map_url'             => array( __( 'Google Maps link (for "Get Directions")', 'nolasharon' ), 'url', '' ),
		'map_embed'           => array( __( 'Google Maps embed URL (optional)', 'nolasharon' ), 'url', __( 'The src="…" value from Google Maps → Share → Embed a map.', 'nolasharon' ) ),
		'lat'                 => array( __( 'Latitude (optional)', 'nolasharon' ), 'text', '' ),
		'lng'                 => array( __( 'Longitude (optional)', 'nolasharon' ), 'text', '' ),
		'pickup_enabled'      => array( __( 'Pickup available at this branch', 'nolasharon' ), 'checkbox', __( 'Store pickup must also be switched on in Customizer → NolaSharon Settings.', 'nolasharon' ) ),
		'pickup_instructions' => array( __( 'Pickup instructions', 'nolasharon' ), 'textarea', __( 'e.g. what to bring and when to come. Confirmed details only.', 'nolasharon' ) ),
	);
}

/**
 * Status options.
 *
 * @return array
 */
function nolasharon_branch_statuses() {
	return array(
		'open'               => __( 'Open', 'nolasharon' ),
		'opening_soon'       => __( 'Opening soon', 'nolasharon' ),
		'temporarily_closed' => __( 'Temporarily closed', 'nolasharon' ),
	);
}

/**
 * Meta box on the branch edit screen.
 */
function nolasharon_branch_meta_box() {
	add_meta_box( 'ns-branch-details', __( 'Branch details', 'nolasharon' ), 'nolasharon_branch_meta_box_html', 'ns_branch', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'nolasharon_branch_meta_box' );

/**
 * Meta box markup.
 *
 * @param WP_Post $post Branch.
 */
function nolasharon_branch_meta_box_html( $post ) {
	wp_nonce_field( 'ns_branch_save', 'ns_branch_nonce' );
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( nolasharon_branch_fields() as $key => $field ) {
		list( $label, $type, $help ) = $field;
		$id    = 'ns-branch-' . $key;
		$value = get_post_meta( $post->ID, '_ns_branch_' . $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $label ) . '</label></th><td>';
		switch ( $type ) {
			case 'textarea':
				printf( '<textarea class="large-text" rows="3" id="%1$s" name="ns_branch[%2$s]">%3$s</textarea>', esc_attr( $id ), esc_attr( $key ), esc_textarea( $value ) );
				break;
			case 'checkbox':
				printf( '<input type="checkbox" id="%1$s" name="ns_branch[%2$s]" value="1" %3$s>', esc_attr( $id ), esc_attr( $key ), checked( '1', $value, false ) );
				break;
			case 'status':
				printf( '<select id="%1$s" name="ns_branch[%2$s]">', esc_attr( $id ), esc_attr( $key ) );
				foreach ( nolasharon_branch_statuses() as $status => $status_label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $status ), selected( $value ? $value : 'open', $status, false ), esc_html( $status_label ) );
				}
				echo '</select>';
				break;
			default:
				printf( '<input class="regular-text" type="%1$s" id="%2$s" name="ns_branch[%3$s]" value="%4$s">', esc_attr( $type ), esc_attr( $id ), esc_attr( $key ), esc_attr( $value ) );
		}
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Save branch fields.
 *
 * @param int $post_id Branch ID.
 */
function nolasharon_branch_save( $post_id ) {
	if ( ! isset( $_POST['ns_branch_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ns_branch_nonce'] ), 'ns_branch_save' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$posted = isset( $_POST['ns_branch'] ) && is_array( $_POST['ns_branch'] ) ? wp_unslash( $_POST['ns_branch'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per field below.

	foreach ( nolasharon_branch_fields() as $key => $field ) {
		$raw = isset( $posted[ $key ] ) ? $posted[ $key ] : '';
		switch ( $field[1] ) {
			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				break;
			case 'checkbox':
				$value = $raw ? '1' : '';
				break;
			case 'url':
				$value = esc_url_raw( $raw );
				break;
			case 'status':
				$value = array_key_exists( $raw, nolasharon_branch_statuses() ) ? $raw : 'open';
				break;
			case 'date':
				$value = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $raw ) ? $raw : '';
				break;
			default:
				$value = sanitize_text_field( $raw );
		}
		update_post_meta( $post_id, '_ns_branch_' . $key, $value );
	}
}
add_action( 'save_post_ns_branch', 'nolasharon_branch_save' );

/**
 * Branches as arrays, in menu order.
 *
 * Visitors only get published branches. Editors also see drafts (flagged) so the
 * layout can be checked before the client confirms the details.
 *
 * @param bool $include_drafts_for_editors Show drafts to logged-in editors.
 * @return array[]
 */
function nolasharon_get_branches( $include_drafts_for_editors = true ) {
	$statuses = array( 'publish' );
	if ( $include_drafts_for_editors && current_user_can( 'edit_pages' ) ) {
		$statuses[] = 'draft';
	}
	$posts = get_posts(
		array(
			'post_type'      => 'ns_branch',
			'post_status'    => $statuses,
			'posts_per_page' => 50,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
		)
	);

	$branches = array();
	foreach ( $posts as $post ) {
		$branch = array(
			'id'       => $post->ID,
			'name'     => get_the_title( $post ),
			'is_draft' => 'publish' !== $post->post_status,
			'image_id' => (int) get_post_thumbnail_id( $post ),
		);
		foreach ( array_keys( nolasharon_branch_fields() ) as $key ) {
			$value = get_post_meta( $post->ID, '_ns_branch_' . $key, true );
			// Placeholders are never shown to visitors (brief §5.7).
			$branch[ $key ] = nolasharon_unconfirmed( $value ) ? '' : $value;
		}
		$branch['status'] = $branch['status'] ? $branch['status'] : 'open';
		$branches[]       = $branch;
	}
	return $branches;
}

/**
 * The newest branch to spotlight on the homepage: flagged "New", else the Ogudu one.
 *
 * @param array[] $branches Branches from nolasharon_get_branches().
 * @return array|null
 */
function nolasharon_spotlight_branch( $branches ) {
	foreach ( $branches as $branch ) {
		if ( $branch['is_new'] ) {
			return $branch;
		}
	}
	foreach ( $branches as $branch ) {
		if ( 'ogudu' === strtolower( $branch['area'] ) ) {
			return $branch;
		}
	}
	return null;
}

/**
 * Published branches offering pickup, when pickup is switched on site-wide.
 *
 * @return array[]
 */
function nolasharon_pickup_branches() {
	if ( ! nolasharon_setting( 'nolasharon_pickup_enabled' ) ) {
		return array();
	}
	return array_values(
		array_filter(
			nolasharon_get_branches( false ),
			function ( $branch ) {
				return $branch['pickup_enabled'] && 'temporarily_closed' !== $branch['status'];
			}
		)
	);
}

/**
 * Status label for a branch: "Opening 12 March 2027", "Opening soon", "Now open" or "Temporarily closed".
 *
 * @param array $branch Branch.
 * @return string
 */
function nolasharon_branch_status_label( $branch ) {
	if ( 'opening_soon' === $branch['status'] ) {
		return $branch['opening_date']
			/* translators: %s: opening date. */
			? sprintf( __( 'Opening %s', 'nolasharon' ), date_i18n( get_option( 'date_format' ), strtotime( $branch['opening_date'] ) ) )
			: __( 'Opening soon', 'nolasharon' );
	}
	if ( 'temporarily_closed' === $branch['status'] ) {
		return __( 'Temporarily closed', 'nolasharon' );
	}
	return $branch['is_new'] ? __( 'Now open', 'nolasharon' ) : '';
}

/**
 * Directions link: the saved Maps link, else a Maps search for the confirmed address.
 *
 * @param array $branch Branch.
 * @return string
 */
function nolasharon_branch_directions_url( $branch ) {
	if ( $branch['map_url'] ) {
		return $branch['map_url'];
	}
	if ( $branch['address'] ) {
		return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $branch['name'] . ', ' . $branch['address'] );
	}
	return '';
}

/**
 * Seed draft branches once (brief §5.7). Runs on theme activation, and once on admin load
 * for sites where the theme was already active.
 */
function nolasharon_seed_branches() {
	if ( get_option( 'nolasharon_branches_seeded' ) ) {
		return;
	}
	$seeds = array(
		array( 'NolaSharon Ikeja (Branch 1)', 'Ikeja', 'open', '' ),
		array( 'NolaSharon Ikeja (Branch 2)', 'Ikeja', 'open', '' ),
		array( 'NolaSharon Ogudu', 'Ogudu', 'opening_soon', '1' ),
	);
	foreach ( $seeds as $i => $seed ) {
		$id = wp_insert_post(
			array(
				'post_type'   => 'ns_branch',
				'post_status' => 'draft',
				'post_title'  => $seed[0],
				'menu_order'  => $i,
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_ns_branch_area', $seed[1] );
			update_post_meta( $id, '_ns_branch_status', $seed[2] );
			update_post_meta( $id, '_ns_branch_is_new', $seed[3] );
		}
	}
	update_option( 'nolasharon_branches_seeded', 1 );
}
add_action( 'after_switch_theme', 'nolasharon_seed_branches' );
add_action( 'admin_init', 'nolasharon_seed_branches' );

/**
 * Admin notice while any branch is unpublished or has no confirmed address.
 */
function nolasharon_branch_notice() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-ns_branch', 'ns_branch', 'themes' ), true ) ) {
		return;
	}
	$pending = array();
	foreach ( nolasharon_get_branches() as $branch ) {
		if ( $branch['is_draft'] || ! $branch['address'] ) {
			$pending[] = $branch['name'];
		}
	}
	if ( ! $pending ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Branch details not confirmed.', 'nolasharon' ),
		/* translators: %s: list of branch names. */
		esc_html( sprintf( __( 'Waiting on the client for: %s. Add the confirmed address, hours and phone, then publish.', 'nolasharon' ), implode( ', ', $pending ) ) ),
		esc_url( admin_url( 'edit.php?post_type=ns_branch' ) ),
		esc_html__( 'Edit branches', 'nolasharon' )
	);
}
add_action( 'admin_notices', 'nolasharon_branch_notice' );

/**
 * ClothingStore JSON-LD for each published branch with a confirmed address (brief §5.7).
 *
 * @param array[] $branches Branches.
 */
function nolasharon_branch_schema( $branches ) {
	$items = array();
	foreach ( $branches as $branch ) {
		if ( $branch['is_draft'] || ! $branch['address'] ) {
			continue;
		}
		$item = array(
			'@type'   => 'ClothingStore',
			'name'    => $branch['name'],
			'url'     => nolasharon_page_url( 'store-locations' ),
			'address' => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => $branch['address'],
				'addressLocality' => $branch['area'],
				'addressRegion'   => 'Lagos',
				'addressCountry'  => 'NG',
			),
		);
		if ( $branch['phone'] ) {
			$item['telephone'] = $branch['phone'];
		}
		if ( $branch['map_url'] ) {
			$item['hasMap'] = $branch['map_url'];
		}
		if ( is_numeric( $branch['lat'] ) && is_numeric( $branch['lng'] ) ) {
			$item['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => (float) $branch['lat'],
				'longitude' => (float) $branch['lng'],
			);
		}
		$items[] = $item;
	}
	if ( $items ) {
		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $items,
			)
		) . '</script>';
	}
}

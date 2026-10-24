<?php
/**
 * Branches (LOCATIONS.md §2, brief §5.7): a private post type the client edits in wp-admin.
 * Feeds the Store Locations page, the homepage spotlight, the footer, the Contact page
 * and the checkout pickup list, so a change to one branch shows everywhere.
 *
 * Every branch has its own fields; nothing is copied from one branch to another.
 * Empty fields are hidden on the front end and "[[CONFIRM" text is never shown.
 *
 * @package NolaSharon
 */

defined( 'ABSPATH' ) || exit;

/* Post type and meta ------------------------------------------------------- */

/**
 * Register the ns_branch post type and its meta.
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
				'menu_name'     => __( 'Branches', 'nolasharon' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => true,
			'menu_position'   => 56,
			'menu_icon'       => 'dashicons-store',
			'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
			'capability_type' => 'page', // Shop Managers can edit pages, so they can edit branches.
		)
	);

	foreach ( ns_branch_meta_fields() as $key => $field ) {
		register_post_meta(
			'ns_branch',
			$key,
			array(
				'type'              => 'checkbox' === $field['input'] ? 'boolean' : ( 'number' === $field['input'] ? 'number' : 'string' ),
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => function ( $value ) use ( $key ) {
					return ns_branch_sanitize( $key, $value );
				},
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}
add_action( 'init', 'nolasharon_register_branches' );

/**
 * Branch meta fields: key => label, input type and help text.
 *
 * @return array[]
 */
function ns_branch_meta_fields() {
	return array(
		'ns_tag'                 => array(
			'label' => __( 'Tag', 'nolasharon' ),
			'input' => 'text',
			'help'  => __( 'Short label shown next to the name, e.g. "Branch 1", "Main store" or "New".', 'nolasharon' ),
		),
		'ns_area'                => array(
			'label' => __( 'Area', 'nolasharon' ),
			'input' => 'area',
			'help'  => __( 'e.g. Ikeja or Ogudu. Branches in the same area are grouped under one filter tab.', 'nolasharon' ),
		),
		'ns_status'              => array(
			'label' => __( 'Status', 'nolasharon' ),
			'input' => 'status',
			'help'  => '',
		),
		'ns_opening_date'        => array(
			'label' => __( 'Opening date', 'nolasharon' ),
			'input' => 'date',
			'help'  => __( 'Only for "Opening soon". Leave empty until the date is confirmed; the page then says "Opening soon".', 'nolasharon' ),
		),
		'ns_is_new'              => array(
			'label' => __( 'New branch', 'nolasharon' ),
			'input' => 'checkbox',
			'help'  => __( 'Shows the "New" pill and the "Now also in…" spotlight. Our Ikeja stores remain open: the spotlight always says so.', 'nolasharon' ),
		),
		'ns_address'             => array(
			'label' => __( 'Full address', 'nolasharon' ),
			'input' => 'textarea',
			'help'  => __( 'Confirmed street address only. Hidden on the site while empty.', 'nolasharon' ),
		),
		'ns_landmark'            => array(
			'label' => __( 'Nearest landmark', 'nolasharon' ),
			'input' => 'text',
			'help'  => '',
		),
		'ns_phone'               => array(
			'label' => __( 'Branch phone', 'nolasharon' ),
			'input' => 'tel',
			'help'  => __( 'Any format, e.g. 0803 123 4567. Saved as +234…', 'nolasharon' ),
		),
		'ns_whatsapp'            => array(
			'label' => __( 'Branch WhatsApp (optional)', 'nolasharon' ),
			'input' => 'tel',
			'help'  => '',
		),
		'ns_hours'               => array(
			'label' => __( 'Opening hours', 'nolasharon' ),
			'input' => 'hours',
			'help'  => __( 'Leave a day empty if its hours are not confirmed yet. Tick "Closed" for days the store is shut.', 'nolasharon' ),
		),
		'ns_hours_note'          => array(
			'label' => __( 'Hours note', 'nolasharon' ),
			'input' => 'text',
			'help'  => __( 'e.g. "Public holiday hours may vary".', 'nolasharon' ),
		),
		'ns_map_url'             => array(
			'label' => __( 'Google Maps link', 'nolasharon' ),
			'input' => 'url',
			'help'  => __( 'Used for "Get directions". In Google Maps, open the store, then Share → Copy link.', 'nolasharon' ),
		),
		'ns_lat'                 => array(
			'label' => __( 'Latitude', 'nolasharon' ),
			'input' => 'number',
			'help'  => '',
		),
		'ns_lng'                 => array(
			'label' => __( 'Longitude', 'nolasharon' ),
			'input' => 'number',
			'help'  => __( 'For the map pin. In Google Maps, right-click the store and click the numbers at the top to copy them (latitude first, e.g. 6.60, 3.35).', 'nolasharon' ),
		),
		'ns_pickup_enabled'      => array(
			'label' => __( 'Online order pickup', 'nolasharon' ),
			'input' => 'checkbox',
			'help'  => __( 'Customers can collect online orders here. Store pickup must also be switched on in Customize → NolaSharon Settings.', 'nolasharon' ),
		),
		'ns_pickup_instructions' => array(
			'label' => __( 'Pickup instructions', 'nolasharon' ),
			'input' => 'textarea',
			'help'  => __( 'Shown on the branch card and at checkout. Confirmed details only.', 'nolasharon' ),
		),
	);
}

/**
 * Status options.
 *
 * @return array
 */
function ns_branch_statuses() {
	return array(
		'open'               => __( 'Open', 'nolasharon' ),
		'opening_soon'       => __( 'Opening soon', 'nolasharon' ),
		'temporarily_closed' => __( 'Temporarily closed', 'nolasharon' ),
	);
}

/**
 * Sanitise one branch meta value.
 *
 * @param string $key   Meta key.
 * @param mixed  $value Raw value.
 * @return mixed
 */
function ns_branch_sanitize( $key, $value ) {
	$fields = ns_branch_meta_fields();
	$input  = isset( $fields[ $key ] ) ? $fields[ $key ]['input'] : 'text';
	switch ( $input ) {
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'checkbox':
			return (bool) $value;
		case 'url':
			return esc_url_raw( (string) $value );
		case 'number':
			$value = str_replace( ',', '.', trim( (string) $value ) );
			return is_numeric( $value ) ? (float) $value : '';
		case 'status':
			return array_key_exists( $value, ns_branch_statuses() ) ? $value : 'open';
		case 'date':
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $value ) ? $value : '';
		case 'tel':
			$value = sanitize_text_field( (string) $value );
			$ng    = nolasharon_ng_phone( $value );
			return $ng ? $ng : $value;
		case 'hours':
			return wp_json_encode( ns_branch_hours( $value ) );
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Normalise opening hours to seven rows, Monday (1) to Sunday (7).
 * A row is "known" when it is marked closed or has both times.
 *
 * @param mixed $raw JSON string or array of rows.
 * @return array[] Each: day, open, close, closed.
 */
function ns_branch_hours( $raw ) {
	$rows = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
	$rows = is_array( $rows ) ? $rows : array();
	$out  = array();
	for ( $day = 1; $day <= 7; $day++ ) {
		$out[ $day ] = array(
			'day'    => $day,
			'open'   => '',
			'close'  => '',
			'closed' => false,
		);
	}
	foreach ( $rows as $row ) {
		$day = isset( $row['day'] ) ? (int) $row['day'] : 0;
		if ( $day < 1 || $day > 7 ) {
			continue;
		}
		$time = function ( $t ) {
			return preg_match( '/^([01]\d|2[0-3]):[0-5]\d$/', (string) $t ) ? (string) $t : '';
		};
		$out[ $day ] = array(
			'day'    => $day,
			'open'   => $time( isset( $row['open'] ) ? $row['open'] : '' ),
			'close'  => $time( isset( $row['close'] ) ? $row['close'] : '' ),
			'closed' => ! empty( $row['closed'] ),
		);
	}
	return array_values( $out );
}

/* One-time migration from the first version's meta keys -------------------- */

/**
 * Move branch data saved under the old "_ns_branch_*" keys to the LOCATIONS.md keys, take the
 * tag out of seeded titles like "NolaSharon Ikeja (Branch 2)", and give the Store Locations page
 * its template. Runs once.
 */
function nolasharon_branches_migrate() {
	if ( get_option( 'nolasharon_branches_v2' ) ) {
		return;
	}
	$map = array( 'area', 'address', 'landmark', 'phone', 'whatsapp', 'map_url', 'lat', 'lng', 'pickup_enabled', 'pickup_instructions', 'status', 'opening_date', 'is_new' );
	foreach ( get_posts( array( 'post_type' => 'ns_branch', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $post ) {
		foreach ( $map as $old ) {
			$value = get_post_meta( $post->ID, '_ns_branch_' . $old, true );
			if ( '' !== $value && ! metadata_exists( 'post', $post->ID, 'ns_' . $old ) ) {
				update_post_meta( $post->ID, 'ns_' . $old, ns_branch_sanitize( 'ns_' . $old, $value ) );
			}
		}
		// Free-text hours cannot be turned into day rows reliably; keep them visible to the editor as the note.
		$hours = get_post_meta( $post->ID, '_ns_branch_hours', true );
		if ( $hours && ! get_post_meta( $post->ID, 'ns_hours_note', true ) ) {
			update_post_meta( $post->ID, 'ns_hours_note', sanitize_text_field( preg_replace( '/\s*\n\s*/', ' · ', $hours ) ) );
		}
		if ( ! get_post_meta( $post->ID, 'ns_tag', true ) ) {
			if ( preg_match( '/^(.*?)\s*\(([^)]+)\)\s*$/', $post->post_title, $m ) ) {
				update_post_meta( $post->ID, 'ns_tag', sanitize_text_field( $m[2] ) );
				wp_update_post( array( 'ID' => $post->ID, 'post_title' => $m[1] ) );
			} elseif ( get_post_meta( $post->ID, 'ns_is_new', true ) ) {
				update_post_meta( $post->ID, 'ns_tag', __( 'New', 'nolasharon' ) );
			}
		}
	}
	nolasharon_locations_page();
	update_option( 'nolasharon_branches_v2', 1 );
}
add_action( 'init', 'nolasharon_branches_migrate', 20 );

/* Seed drafts and the Store Locations page --------------------------------- */

/**
 * Seed draft branches with empty details, only when there are no branches at all
 * (LOCATIONS.md §2). [[CONFIRM: number of Ikeja branches]]
 */
function nolasharon_seed_branches() {
	$existing = get_posts( array( 'post_type' => 'ns_branch', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids' ) );
	if ( $existing || get_option( 'nolasharon_branches_seeded' ) ) {
		return;
	}
	$seeds = array(
		array( 'NolaSharon Ikeja', 'Branch 1', 'Ikeja', 'open', false ),
		array( 'NolaSharon Ikeja', 'Branch 2', 'Ikeja', 'open', false ),
		array( 'NolaSharon Ogudu', 'New', 'Ogudu', 'opening_soon', true ),
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
			update_post_meta( $id, 'ns_tag', $seed[1] );
			update_post_meta( $id, 'ns_area', $seed[2] );
			update_post_meta( $id, 'ns_status', $seed[3] );
			update_post_meta( $id, 'ns_is_new', $seed[4] );
		}
	}
	update_option( 'nolasharon_branches_seeded', 1 );
}
add_action( 'after_switch_theme', 'nolasharon_seed_branches' );
add_action( 'admin_init', 'nolasharon_seed_branches' );

/**
 * Create the Store Locations page if missing, give it its template, and add it to the
 * primary menu when one is assigned.
 */
function nolasharon_locations_page() {
	$page = get_page_by_path( 'store-locations' );
	$id   = $page ? $page->ID : wp_insert_post(
		array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => __( 'Store Locations', 'nolasharon' ),
			'post_name'   => 'store-locations',
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}
	update_post_meta( $id, '_wp_page_template', 'page-templates/store-locations.php' );

	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		return; // The built-in menu (template-parts/nav.php) already links to the page.
	}
	foreach ( (array) wp_get_nav_menu_items( $locations['primary'] ) as $item ) {
		if ( (int) $item->object_id === (int) $id ) {
			return;
		}
	}
	wp_update_nav_menu_item(
		$locations['primary'],
		0,
		array(
			'menu-item-object-id' => $id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}
add_action( 'after_switch_theme', 'nolasharon_locations_page' );

/* Admin: meta box, list columns, notice ------------------------------------ */

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
	echo '<p class="description">' . esc_html__( 'Only fill in confirmed details. Anything left empty is simply hidden on the website.', 'nolasharon' ) . '</p>';
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( ns_branch_meta_fields() as $key => $field ) {
		$id    = 'ns-branch-' . $key;
		$value = get_post_meta( $post->ID, $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		switch ( $field['input'] ) {
			case 'textarea':
				printf( '<textarea class="large-text" rows="3" id="%1$s" name="ns_branch[%2$s]">%3$s</textarea>', esc_attr( $id ), esc_attr( $key ), esc_textarea( $value ) );
				break;
			case 'checkbox':
				printf( '<input type="checkbox" id="%1$s" name="ns_branch[%2$s]" value="1" %3$s>', esc_attr( $id ), esc_attr( $key ), checked( (bool) $value, true, false ) );
				break;
			case 'status':
				printf( '<select id="%1$s" name="ns_branch[%2$s]">', esc_attr( $id ), esc_attr( $key ) );
				foreach ( ns_branch_statuses() as $status => $label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $status ), selected( $value ? $value : 'open', $status, false ), esc_html( $label ) );
				}
				echo '</select>';
				break;
			case 'area':
				printf( '<input class="regular-text" type="text" id="%1$s" name="ns_branch[%2$s]" value="%3$s" list="ns-branch-areas">', esc_attr( $id ), esc_attr( $key ), esc_attr( $value ) );
				echo '<datalist id="ns-branch-areas">';
				foreach ( ns_branch_areas( true ) as $area ) {
					echo '<option value="' . esc_attr( $area ) . '"></option>';
				}
				echo '</datalist>';
				break;
			case 'hours':
				nolasharon_branch_hours_input( ns_branch_hours( $value ) );
				break;
			case 'number':
				printf( '<input class="regular-text" type="number" step="any" id="%1$s" name="ns_branch[%2$s]" value="%3$s">', esc_attr( $id ), esc_attr( $key ), esc_attr( $value ) );
				break;
			default:
				printf( '<input class="regular-text" type="%1$s" id="%2$s" name="ns_branch[%3$s]" value="%4$s">', esc_attr( $field['input'] ), esc_attr( $id ), esc_attr( $key ), esc_attr( $value ) );
		}
		if ( $field['help'] ) {
			echo '<p class="description">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

/**
 * Opening hours: one row per day with open and close times and a "Closed" box.
 *
 * @param array[] $hours Rows from ns_branch_hours().
 */
function nolasharon_branch_hours_input( $hours ) {
	echo '<table class="widefat striped" style="max-width:520px" id="ns-branch-ns_hours"><thead><tr><th>' . esc_html__( 'Day', 'nolasharon' ) . '</th><th>' . esc_html__( 'Opens', 'nolasharon' ) . '</th><th>' . esc_html__( 'Closes', 'nolasharon' ) . '</th><th>' . esc_html__( 'Closed', 'nolasharon' ) . '</th></tr></thead><tbody>';
	foreach ( $hours as $row ) {
		$d    = (int) $row['day'];
		$name = ns_branch_day_name( $d );
		printf(
			'<tr><th scope="row">%1$s</th><td><input type="time" name="ns_branch[ns_hours][%2$d][open]" value="%3$s" aria-label="%4$s"></td><td><input type="time" name="ns_branch[ns_hours][%2$d][close]" value="%5$s" aria-label="%6$s"></td><td><input type="checkbox" name="ns_branch[ns_hours][%2$d][closed]" value="1" %7$s aria-label="%8$s"></td></tr>',
			esc_html( $name ),
			$d, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- integer.
			esc_attr( $row['open'] ),
			/* translators: %s: day name. */
			esc_attr( sprintf( __( '%s opens', 'nolasharon' ), $name ) ),
			esc_attr( $row['close'] ),
			/* translators: %s: day name. */
			esc_attr( sprintf( __( '%s closes', 'nolasharon' ), $name ) ),
			checked( $row['closed'], true, false ),
			/* translators: %s: day name. */
			esc_attr( sprintf( __( 'Closed on %s', 'nolasharon' ), $name ) )
		);
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
	$posted = isset( $_POST['ns_branch'] ) && is_array( $_POST['ns_branch'] ) ? wp_unslash( $_POST['ns_branch'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised per field in ns_branch_sanitize().

	foreach ( array_keys( ns_branch_meta_fields() ) as $key ) {
		$raw = isset( $posted[ $key ] ) ? $posted[ $key ] : '';
		if ( 'ns_hours' === $key && is_array( $raw ) ) {
			foreach ( $raw as $day => $row ) {
				$raw[ $day ]['day'] = (int) $day;
			}
		}
		update_post_meta( $post_id, $key, ns_branch_sanitize( $key, $raw ) );
	}
}
add_action( 'save_post_ns_branch', 'nolasharon_branch_save' );

/**
 * Branch list columns: Area · Tag · Status · Pickup · Complete?
 *
 * @param array $columns Columns.
 * @return array
 */
function nolasharon_branch_columns( $columns ) {
	$date = isset( $columns['date'] ) ? $columns['date'] : null;
	unset( $columns['date'] );
	$columns['ns_area']     = __( 'Area', 'nolasharon' );
	$columns['ns_tag']      = __( 'Tag', 'nolasharon' );
	$columns['ns_status']   = __( 'Status', 'nolasharon' );
	$columns['ns_pickup']   = __( 'Pickup', 'nolasharon' );
	$columns['ns_complete'] = __( 'Complete?', 'nolasharon' );
	if ( null !== $date ) {
		$columns['date'] = $date;
	}
	return $columns;
}
add_filter( 'manage_ns_branch_posts_columns', 'nolasharon_branch_columns' );

/**
 * Branch list column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Branch ID.
 */
function nolasharon_branch_column( $column, $post_id ) {
	$b = ns_branch_data( get_post( $post_id ) );
	switch ( $column ) {
		case 'ns_area':
			echo esc_html( $b['area'] ? $b['area'] : '—' );
			break;
		case 'ns_tag':
			echo esc_html( $b['tag'] ? $b['tag'] : '—' );
			break;
		case 'ns_status':
			$statuses = ns_branch_statuses();
			echo esc_html( $statuses[ $b['status'] ] );
			break;
		case 'ns_pickup':
			echo $b['pickup_enabled'] ? esc_html__( 'Yes', 'nolasharon' ) : esc_html__( 'No', 'nolasharon' );
			break;
		case 'ns_complete':
			$missing = ns_branch_missing( $b );
			echo $missing
				/* translators: %s: list of missing details. */
				? esc_html( sprintf( __( 'Missing: %s', 'nolasharon' ), implode( ', ', $missing ) ) )
				: '<span aria-hidden="true">✓</span> ' . esc_html__( 'Complete', 'nolasharon' );
			break;
	}
}
add_action( 'manage_ns_branch_posts_custom_column', 'nolasharon_branch_column', 10, 2 );

/**
 * Details still missing for a branch to be complete.
 *
 * @param array $b Branch data.
 * @return string[]
 */
function ns_branch_missing( $b ) {
	$missing = array();
	if ( ! $b['address'] ) {
		$missing[] = __( 'address', 'nolasharon' );
	}
	if ( ! $b['phone'] ) {
		$missing[] = __( 'phone', 'nolasharon' );
	}
	if ( ! $b['has_hours'] ) {
		$missing[] = __( 'hours', 'nolasharon' );
	}
	if ( ! $b['has_coords'] && ! $b['map_url'] ) {
		$missing[] = __( 'map location', 'nolasharon' );
	}
	return $missing;
}

/**
 * Notice on the Dashboard and Branches screens while any branch is unpublished or incomplete.
 */
function nolasharon_branch_notice() {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'edit-ns_branch', 'ns_branch' ), true ) ) {
		return;
	}
	foreach ( ns_get_branches( array( 'include_drafts' => true ) ) as $b ) {
		if ( $b['is_draft'] || ns_branch_missing( $b ) ) {
			printf(
				'<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
				esc_html__( 'Some branch details are not confirmed yet. They are hidden on the website until filled in.', 'nolasharon' ),
				esc_url( admin_url( 'edit.php?post_type=ns_branch' ) ),
				esc_html__( 'Edit branches', 'nolasharon' )
			);
			return;
		}
	}
}
add_action( 'admin_notices', 'nolasharon_branch_notice' );

/* Helper API ----------------------------------------------------------------- */

/**
 * Branches as normalised arrays (ns_branch_data()), in menu order.
 *
 * @param array $args {
 *     @type string $area           Only branches in this area.
 *     @type bool   $pickup_only    Only branches that offer online order pickup.
 *     @type bool   $exclude_closed Leave out temporarily closed branches.
 *     @type bool   $include_drafts Also drafts, for editors only (flagged is_draft) so the layout
 *                                  can be checked before the client confirms details.
 * }
 * @return array[]
 */
function ns_get_branches( $args = array() ) {
	$args     = wp_parse_args(
		$args,
		array(
			'area'           => '',
			'pickup_only'    => false,
			'exclude_closed' => false,
			'include_drafts' => false,
		)
	);
	$statuses = array( 'publish' );
	if ( $args['include_drafts'] && current_user_can( 'edit_pages' ) ) {
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
		$b = ns_branch_data( $post );
		if ( ( $args['area'] && 0 !== strcasecmp( $args['area'], $b['area'] ) )
			|| ( $args['pickup_only'] && ! $b['pickup_enabled'] )
			|| ( $args['exclude_closed'] && 'temporarily_closed' === $b['status'] ) ) {
			continue;
		}
		$branches[] = $b;
	}
	return $branches;
}

/**
 * One branch as a normalised array. Empty or unconfirmed fields are ''.
 *
 * @param WP_Post|int $post Branch.
 * @return array
 */
function ns_branch_data( $post ) {
	$post = get_post( $post );
	$get  = function ( $key ) use ( $post ) {
		$value = get_post_meta( $post->ID, $key, true );
		return is_string( $value ) && nolasharon_unconfirmed( $value ) ? '' : $value;
	};
	$slug  = $post->post_name ? $post->post_name : sanitize_title( $post->post_title ) . '-' . $post->ID;
	$hours = ns_branch_hours( get_post_meta( $post->ID, 'ns_hours', true ) );
	$lat   = get_post_meta( $post->ID, 'ns_lat', true );
	$lng   = get_post_meta( $post->ID, 'ns_lng', true );
	$phone = (string) $get( 'ns_phone' );
	$b     = array(
		'id'                  => $post->ID,
		'slug'                => $slug,
		'anchor'              => 'branch-' . $slug,
		'name'                => get_the_title( $post ),
		'tag'                 => (string) $get( 'ns_tag' ),
		'area'                => (string) $get( 'ns_area' ),
		'address'             => (string) $get( 'ns_address' ),
		'landmark'            => (string) $get( 'ns_landmark' ),
		'phone'               => $phone ? ns_branch_format_phone( $phone ) : '',
		'tel'                 => nolasharon_tel_url( $phone ),
		'whatsapp'            => (string) $get( 'ns_whatsapp' ),
		'hours'               => $hours,
		'hours_note'          => (string) $get( 'ns_hours_note' ),
		'lat'                 => is_numeric( $lat ) ? (float) $lat : '',
		'lng'                 => is_numeric( $lng ) ? (float) $lng : '',
		'map_url'             => (string) $get( 'ns_map_url' ),
		'pickup_enabled'      => (bool) get_post_meta( $post->ID, 'ns_pickup_enabled', true ),
		'pickup_instructions' => (string) $get( 'ns_pickup_instructions' ),
		'status'              => array_key_exists( (string) get_post_meta( $post->ID, 'ns_status', true ), ns_branch_statuses() ) ? (string) get_post_meta( $post->ID, 'ns_status', true ) : 'open',
		'opening_date'        => (string) $get( 'ns_opening_date' ),
		'is_new'              => (bool) get_post_meta( $post->ID, 'ns_is_new', true ),
		'image_id'            => (int) get_post_thumbnail_id( $post ),
		'is_draft'            => 'publish' !== $post->post_status,
	);
	$b['has_hours']  = (bool) array_filter(
		$hours,
		function ( $row ) {
			return $row['closed'] || ( $row['open'] && $row['close'] );
		}
	);
	$b['has_coords'] = '' !== $b['lat'] && '' !== $b['lng'];
	$b['hours_text'] = implode(
		"\n",
		array_map(
			function ( $row ) {
				return $row['days'] . ': ' . $row['time'];
			},
			ns_branch_hours_rows( $b )
		)
	);
	return $b;
}

/**
 * Name with its tag, e.g. "NolaSharon Ikeja · Branch 1".
 *
 * @param array $b Branch data.
 * @return string
 */
function ns_branch_label( $b ) {
	return $b['tag'] ? $b['name'] . ' · ' . $b['tag'] : $b['name'];
}

/**
 * Format a +234 number for display: "+234 803 123 4567".
 *
 * @param string $phone Stored phone.
 * @return string
 */
function ns_branch_format_phone( $phone ) {
	if ( preg_match( '/^\+234(\d{3})(\d{3})(\d{4})$/', $phone, $m ) ) {
		return '+234 ' . $m[1] . ' ' . $m[2] . ' ' . $m[3];
	}
	return $phone;
}

/**
 * Directions link: the Maps place link, else directions to the coordinates, else ''.
 *
 * @param array $b Branch data.
 * @return string
 */
function ns_branch_directions_url( $b ) {
	if ( $b['map_url'] ) {
		return $b['map_url'];
	}
	if ( $b['has_coords'] ) {
		return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $b['lat'] . ',' . $b['lng'] );
	}
	return '';
}

/**
 * Day name, Monday = 1.
 *
 * @param int  $day   Day number.
 * @param bool $short Short form ("Mon").
 * @return string
 */
function ns_branch_day_name( $day, $short = false ) {
	global $wp_locale;
	$name = $wp_locale->get_weekday( $day % 7 ); // WP_Locale: 0 = Sunday.
	return $short ? $wp_locale->get_weekday_abbrev( $name ) : $name;
}

/**
 * A 24h "HH:MM" time as "9:00am".
 *
 * @param string $time Time.
 * @return string
 */
function ns_branch_time( $time ) {
	$dt = DateTime::createFromFormat( 'H:i', $time, wp_timezone() );
	return $dt ? wp_date( 'g:ia', $dt->getTimestamp(), wp_timezone() ) : '';
}

/**
 * Opening hours grouped into rows: consecutive days with the same hours share a row,
 * e.g. "Mon – Fri" "9:00am – 7:00pm". Days whose hours are not confirmed are left out.
 *
 * @param array $b Branch data.
 * @return array[] Each: days, time, closed.
 */
function ns_branch_hours_rows( $b ) {
	$rows  = array();
	$group = null;
	foreach ( $b['hours'] as $row ) {
		$known = $row['closed'] || ( $row['open'] && $row['close'] );
		$sig   = $known ? ( $row['closed'] ? 'closed' : $row['open'] . '-' . $row['close'] ) : '';
		if ( $group && $sig && $group['sig'] === $sig && $group['to'] === $row['day'] - 1 ) {
			$group['to'] = $row['day'];
			continue;
		}
		if ( $group ) {
			$rows[] = $group;
		}
		$group = $sig ? array(
			'sig'    => $sig,
			'from'   => $row['day'],
			'to'     => $row['day'],
			'closed' => $row['closed'],
			'time'   => $row['closed'] ? __( 'Closed', 'nolasharon' ) : ns_branch_time( $row['open'] ) . ' – ' . ns_branch_time( $row['close'] ),
		) : null;
	}
	if ( $group ) {
		$rows[] = $group;
	}
	return array_map(
		function ( $g ) {
			return array(
				'days'   => $g['from'] === $g['to'] ? ns_branch_day_name( $g['from'], true ) : ns_branch_day_name( $g['from'], true ) . ' – ' . ns_branch_day_name( $g['to'], true ),
				'time'   => $g['time'],
				'closed' => $g['closed'],
			);
		},
		$rows
	);
}

/**
 * Whether the branch is open right now in Lagos time (wp_timezone()).
 *
 * @param array $b Branch data.
 * @return bool|null Null when it has no hours.
 */
function ns_branch_is_open_now( $b ) {
	if ( ! $b['has_hours'] ) {
		return null;
	}
	$now = new DateTimeImmutable( 'now', wp_timezone() );
	$row = $b['hours'][ (int) $now->format( 'N' ) - 1 ];
	if ( $row['closed'] || ! $row['open'] || ! $row['close'] ) {
		return false;
	}
	$time = $now->format( 'H:i' );
	return $time >= $row['open'] && $time < $row['close'];
}

/**
 * The status line for a card: text, tone (open, closed, soon, shut) and data for the
 * browser to keep "Open now" current on cached pages.
 *
 * @param array $b Branch data.
 * @return array { label, tone }
 */
function ns_branch_status( $b ) {
	if ( 'temporarily_closed' === $b['status'] ) {
		return array(
			'label' => __( 'Temporarily closed', 'nolasharon' ),
			'tone'  => 'shut',
		);
	}
	if ( 'opening_soon' === $b['status'] ) {
		return array(
			'label' => $b['opening_date']
				/* translators: %s: opening date. */
				? sprintf( __( 'Opening %s', 'nolasharon' ), wp_date( get_option( 'date_format' ), strtotime( $b['opening_date'] . ' 12:00' ) ) )
				: __( 'Opening soon', 'nolasharon' ),
			'tone'  => 'soon',
		);
	}
	$open = ns_branch_is_open_now( $b );
	if ( null === $open ) {
		return array(
			'label' => __( 'Open', 'nolasharon' ),
			'tone'  => 'open',
		);
	}
	$now   = new DateTimeImmutable( 'now', wp_timezone() );
	$today = (int) $now->format( 'N' );
	if ( $open ) {
		return array(
			/* translators: %s: closing time. */
			'label' => sprintf( __( 'Open now · closes %s', 'nolasharon' ), ns_branch_time( $b['hours'][ $today - 1 ]['close'] ) ),
			'tone'  => 'open',
		);
	}
	// Next opening: later today, else the next day with hours.
	for ( $i = 0; $i < 7; $i++ ) {
		$row = $b['hours'][ ( $today - 1 + $i ) % 7 ];
		if ( $row['closed'] || ! $row['open'] ) {
			continue;
		}
		if ( 0 === $i && $now->format( 'H:i' ) >= $row['open'] ) {
			continue;
		}
		$when = ns_branch_time( $row['open'] );
		return array(
			'label' => 0 === $i
				/* translators: %s: opening time. */
				? sprintf( __( 'Closed · opens %s', 'nolasharon' ), $when )
				/* translators: 1: day, 2: opening time. */
				: sprintf( __( 'Closed · opens %1$s %2$s', 'nolasharon' ), ns_branch_day_name( $row['day'], true ), $when ),
			'tone'  => 'closed',
		);
	}
	return array(
		'label' => __( 'Closed', 'nolasharon' ),
		'tone'  => 'closed',
	);
}

/**
 * Distinct areas, in branch order.
 *
 * @param bool $include_drafts Include draft branches (admin datalist).
 * @return string[]
 */
function ns_branch_areas( $include_drafts = false ) {
	$areas = array();
	foreach ( ns_get_branches( array( 'include_drafts' => $include_drafts ) ) as $b ) {
		if ( $b['area'] && ! in_array( $b['area'], $areas, true ) ) {
			$areas[] = $b['area'];
		}
	}
	return $areas;
}

/**
 * The branch to spotlight: the first flagged "New" (menu order).
 *
 * @param array[] $branches Branches.
 * @return array|null
 */
function ns_branch_spotlight( $branches ) {
	foreach ( $branches as $b ) {
		if ( $b['is_new'] ) {
			return $b;
		}
	}
	return null;
}

/**
 * Branches offering pickup at checkout: pickup switched on site-wide, branch offers it,
 * not temporarily closed (CHECKOUT.md).
 *
 * @return array[]
 */
function nolasharon_pickup_branches() {
	if ( ! nolasharon_setting( 'nolasharon_pickup_enabled' ) ) {
		return array();
	}
	return ns_get_branches(
		array(
			'pickup_only'    => true,
			'exclude_closed' => true,
		)
	);
}

/**
 * Link to a branch on the Store Locations page.
 *
 * @param array $b Branch data.
 * @return string
 */
function ns_branch_url( $b ) {
	return nolasharon_page_url( 'store-locations' ) . '#' . $b['anchor'];
}

/* Store Locations page: assets, title, schema ------------------------------- */

/**
 * Whether this request is the Store Locations page.
 *
 * @return bool
 */
function nolasharon_is_locations_page() {
	return is_page_template( 'page-templates/store-locations.php' );
}

/**
 * Leaflet (only when a published branch has coordinates) and locations.js, on this page only.
 */
function nolasharon_locations_assets() {
	if ( ! nolasharon_is_locations_page() ) {
		return;
	}
	$deps = array();
	foreach ( ns_get_branches( array( 'include_drafts' => true ) ) as $b ) {
		if ( $b['has_coords'] ) {
			wp_enqueue_style( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css', array(), null );
			wp_enqueue_script( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js', array(), null, array( 'strategy' => 'defer' ) );
			$deps = array( 'leaflet' );
			break;
		}
	}
	wp_enqueue_script( 'nolasharon-locations', get_template_directory_uri() . '/assets/js/locations.js', $deps, NOLASHARON_VERSION, array( 'strategy' => 'defer' ) );
	wp_add_inline_script(
		'nolasharon-locations',
		'window.nsLocations = ' . wp_json_encode(
			array(
				// OpenStreetMap's own tiles: no key, fine for a small shop's traffic under
				// https://operations.osmfoundation.org/policies/tiles/ (attribution shown).
				// CARTO's light styles now need an API key; swap one in here if the client gets one.
				'tiles' => apply_filters(
					'nolasharon_map_tiles',
					array(
						'url'         => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
						'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
						'subdomains'  => 'abc',
						'maxZoom'     => 19,
					)
				),
				'i18n'  => array(
					'open'       => __( 'Open', 'nolasharon' ),
					/* translators: %s: closing time. */
					'openNow'    => __( 'Open now · closes %s', 'nolasharon' ),
					/* translators: %s: opening time. */
					'opensToday' => __( 'Closed · opens %s', 'nolasharon' ),
					/* translators: 1: day, 2: opening time. */
					'opensLater' => __( 'Closed · opens %1$s %2$s', 'nolasharon' ),
					'closed'     => __( 'Closed', 'nolasharon' ),
					'days'       => array_map(
						function ( $d ) {
							return ns_branch_day_name( $d, true );
						},
						range( 1, 7 )
					),
				),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'nolasharon_locations_assets' );

/**
 * Mark the page as script-enabled before first paint, so hours panels and tabs start in their
 * enhanced state. Without JavaScript the hours stay open and the tabs stay hidden.
 */
function nolasharon_locations_js_flag() {
	if ( nolasharon_is_locations_page() ) {
		echo "<script>document.documentElement.classList.add('ns-loc-js');</script>\n";
	}
}
add_action( 'wp_head', 'nolasharon_locations_js_flag', 2 );

/**
 * Subresource integrity for the pinned Leaflet files.
 *
 * @param string $tag    Tag HTML.
 * @param string $handle Handle.
 * @return string
 */
function nolasharon_leaflet_sri( $tag, $handle ) {
	if ( 'leaflet' !== $handle ) {
		return $tag;
	}
	$hash = 'style_loader_tag' === current_filter()
		? 'sha512-h9FcoyWjHcOcmEVkxOfTLnmZFWIH0iZhZT1H2TbOq55xssQGEJHEaIm+PgoUaZbRvQTNTluNOEfb1ZRy6D3BOw=='
		: 'sha512-puJW3E/qXDqYp9IfhAI54BJEaWIfloJ7JWs7OeD5i6ruC9JZL1gERT1wjtwXFlh7CjE7ZJ+/vcRZRkIYIb6p4g==';
	return preg_replace( '/ (src|href)=/', ' integrity="' . $hash . '" crossorigin="anonymous" referrerpolicy="no-referrer" $1=', $tag, 1 );
}
add_filter( 'script_loader_tag', 'nolasharon_leaflet_sri', 10, 2 );
add_filter( 'style_loader_tag', 'nolasharon_leaflet_sri', 10, 2 );

/**
 * Page title and meta description when no SEO plugin handles them (LOCATIONS.md §7).
 *
 * @return bool
 */
function nolasharon_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' );
}

/**
 * "Store Locations | NolaSharon Clothing".
 *
 * @param string $title Title.
 * @return string
 */
function nolasharon_locations_title( $title ) {
	if ( nolasharon_is_locations_page() && ! nolasharon_has_seo_plugin() ) {
		return get_the_title() . ' | ' . get_bloginfo( 'name' );
	}
	return $title;
}
add_filter( 'pre_get_document_title', 'nolasharon_locations_title' );

/**
 * Meta description (DRAFT: client to approve).
 */
function nolasharon_locations_meta() {
	if ( nolasharon_is_locations_page() && ! nolasharon_has_seo_plugin() ) {
		echo '<meta name="description" content="' . esc_attr__( 'Visit NolaSharon Clothing in Lagos. Find our Ikeja stores and our new Ogudu branch, with addresses, opening hours and directions.', 'nolasharon' ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'nolasharon_locations_meta', 1 );

/**
 * ClothingStore JSON-LD for each published branch with an address (LOCATIONS.md §7).
 *
 * @param array[] $branches Branches.
 */
function ns_branch_schema( $branches ) {
	$items = array();
	foreach ( $branches as $b ) {
		if ( $b['is_draft'] || ! $b['address'] ) {
			continue;
		}
		$item = array(
			'@type'              => 'ClothingStore',
			'name'               => $b['tag'] && ! ( $b['is_new'] && 'new' === strtolower( $b['tag'] ) ) ? $b['name'] . ' (' . $b['tag'] . ')' : $b['name'],
			'url'                => ns_branch_url( $b ),
			'address'            => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => preg_replace( '/\s*\n\s*/', ', ', $b['address'] ),
				'addressLocality' => $b['area'],
				'addressRegion'   => 'Lagos',
				'addressCountry'  => 'NG',
			),
			'parentOrganization' => array(
				'@type' => 'Organization',
				'name'  => 'NolaSharon Clothing',
				'url'   => home_url( '/' ),
			),
		);
		if ( $b['tel'] ) {
			$item['telephone'] = str_replace( 'tel:', '', $b['tel'] );
		}
		if ( $b['has_coords'] ) {
			$item['geo'] = array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => $b['lat'],
				'longitude' => $b['lng'],
			);
		}
		if ( $b['map_url'] ) {
			$item['hasMap'] = $b['map_url'];
		}
		if ( $b['image_id'] ) {
			$item['image'] = wp_get_attachment_image_url( $b['image_id'], 'large' );
		}
		$specs = array();
		foreach ( $b['hours'] as $row ) {
			if ( ! $row['closed'] && $row['open'] && $row['close'] ) {
				$specs[] = array(
					'@type'     => 'OpeningHoursSpecification',
					'dayOfWeek' => 'https://schema.org/' . array( 1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' )[ $row['day'] ],
					'opens'     => $row['open'],
					'closes'    => $row['close'],
				);
			}
		}
		if ( $specs ) {
			$item['openingHoursSpecification'] = $specs;
		}
		$items[] = $item;
	}
	if ( $items ) {
		echo '<script type="application/ld+json">' . wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $items,
			),
			JSON_UNESCAPED_SLASHES
		) . '</script>' . "\n";
	}
}

<?php

/* --------------------------------------------------------- */
/* !Duplicate the post - 2.31 */
/* --------------------------------------------------------- */

function mtphr_duplicate_post( $original_id, $args=array(), $do_action=true ) {
	
	// Get access to the database
	global $wpdb;
	
	// Get the post as an array
	$duplicate = get_post( $original_id, 'ARRAY_A' );
		
	$global_settings = get_mtphr_post_duplicator_settings();
	$settings = wp_parse_args( $args, $global_settings );
	
	// Modify some of the elements
	$appended = isset( $settings['title'] ) ? sanitize_text_field( $settings['title'] ) : esc_html__( 'Copy', 'post-duplicator' );
	$duplicate['post_title'] = wp_kses_post( $duplicate['post_title'] ) . ' ' . $appended;
	$duplicate['post_name'] = sanitize_title( $duplicate['post_name'] . '-' . $settings['slug'] );
	
	// Set the status
	if( $settings['status'] != 'same' ) {
		$duplicate['post_status'] = sanitize_text_field( $settings['status'] );
	}
	
	// Check if a user has publish get_post_type_capabilities. If not, make sure they can't _publish
	if ( ! current_user_can( 'publish_posts' ) ) {
		// Force the post status to pending
		if ( 'publish' == $duplicate['post_status'] ) {
			$duplicate['post_status'] = 'pending';
		}
	}
	
	// Set the type
	if( $settings['type'] != 'same' ) {
		$duplicate['post_type'] = sanitize_text_field( $settings['type'] );
	}
	
	// Set the post date
	$timestamp = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date']) : current_time('timestamp',0);
	$timestamp_gmt = ( $settings['timestamp'] == 'duplicate' ) ? strtotime($duplicate['post_date_gmt']) : current_time('timestamp',1);
	
	if( $settings['time_offset'] ) {
		$offset = intval($settings['time_offset_seconds']+$settings['time_offset_minutes']*60+$settings['time_offset_hours']*3600+$settings['time_offset_days']*86400);
		if( $settings['time_offset_direction'] == 'newer' ) {
			$timestamp = intval($timestamp+$offset);
			$timestamp_gmt = intval($timestamp_gmt+$offset);
		} else {
			$timestamp = intval($timestamp-$offset);
			$timestamp_gmt = intval($timestamp_gmt-$offset);
		}
	}
	$duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);
	$duplicate['post_date_gmt'] = date('Y-m-d H:i:s', $timestamp_gmt);
	$duplicate['post_modified'] = date('Y-m-d H:i:s', current_time('timestamp',0));
	$duplicate['post_modified_gmt'] = date('Y-m-d H:i:s', current_time('timestamp',1));
	if ( 'current_user' == $settings['post_author'] ) {
		$duplicate['post_author'] = get_current_user_id();
	}

	// Remove some of the keys
	unset( $duplicate['ID'] );
	unset( $duplicate['guid'] );
	unset( $duplicate['comment_count'] );

	//$duplicate['post_content'] = wp_slash( str_replace( array( '\r\n', '\r', '\n' ), '<br />', wp_kses_post( $duplicate['post_content'] ) ) ); 
	$duplicate['post_content'] = wp_slash( wp_kses_post( $duplicate['post_content'] ) ); 

	// Insert the post into the database
	$duplicate_id = wp_insert_post( $duplicate );
	
	// Duplicate all the taxonomies/terms
	$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
	$disabled_taxonomies = ['post_translations'];
	foreach( $taxonomies as $taxonomy ) {
		if ( in_array( $taxonomy, $disabled_taxonomies ) ) {
			continue;
		}
		$terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
		wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
	}
	
	// Duplicate all the custom fields
	$custom_fields = get_post_custom( $original_id );
	foreach ( $custom_fields as $key => $value ) {
		if( is_array($value) && count($value) > 0 ) {
			foreach( $value as $i=>$v ) {
				$data = array(
					'post_id' 		=> intval( $duplicate_id ),
					'meta_key' 		=> sanitize_text_field( $key ),
					'meta_value' 	=> $v,
				);
				$formats = array(
					'%d',
					'%s',
					'%s',
				);
				$result = $wpdb->insert( $wpdb->prefix.'postmeta', $data, $formats );
			}
		}
	}
	
	// Add an action for others to do custom stuff
	if( $do_action ) {
		do_action( 'mtphr_post_duplicator_created', $original_id, $duplicate_id, $settings );
	}

	return $duplicate_id;
}


/* --------------------------------------------------------- */
/* !Ajax duplicate post - 2.25 */
/* --------------------------------------------------------- */

function m4c_duplicate_post() {

	// Check the nonce
	check_ajax_referer( 'm4c_ajax_file_nonce', 'security' );
	
	// Get variables
	$original_id  = intval( $_POST['original_id'] );
	
	// Duplicate the post
	$duplicate_id = mtphr_duplicate_post( $original_id );
	
	$data = array(
		'duplicate_id' => esc_attr( $duplicate_id ),
	);
	wp_send_json( $data );
}
add_action( 'wp_ajax_m4c_duplicate_post', 'm4c_duplicate_post' );
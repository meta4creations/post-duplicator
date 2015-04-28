<?php
/*
Plugin Name: Post Duplicator
Description: Creates functionality to duplicate any and all post types, including taxonomies & custom fields
Version: 2.5
Author: Metaphor Creations
Author URI: http://www.metaphorcreations.com
License: GPL2
*/

/*  
Copyright 2012 Metaphor Creations  (email : joe@metaphorcreations.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/




/**Define Widget Constants */
define ( 'MTPHR_POST_DUPLICATOR_VERSION', '2.5' );
define ( 'MTPHR_POST_DUPLICATOR_DIR', plugin_dir_path(__FILE__) );
define ( 'MTPHR_POST_DUPLICATOR_URL', plugins_url().'/post-duplicator' );




add_action( 'plugins_loaded', 'mtphr_post_duplicator_localization' );
/**
 * Setup localization
 *
 * @since 2.4
 */
function mtphr_post_duplicator_localization() {
	load_plugin_textdomain( 'post-duplicator', false, 'post-duplicator/languages/' );
}




/**
 * Include files.
 *
 * @since 2.0
 */
if ( is_admin() ) {

	// Load Metaboxer
	require_once( MTPHR_POST_DUPLICATOR_DIR.'metaboxer/metaboxer.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'metaboxer/metaboxer-class.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/scripts.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/ajax.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/edit.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/functions.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/settings.php' );

	// Bulk duplicate
	add_action('admin_footer-edit.php', 'duplicate_bulk_admin_footer');
	add_action('load-edit.php', 'duplicate_bulk_action');
	add_action('admin_notices', 'duplicate_bulk_admin_notices');
}

function duplicate_bulk_admin_footer() {
	?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('duplicate').text('<?php _e('Duplicate', 'post-duplicator'); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('duplicate').text('<?php _e('Duplicate', 'post-duplicator'); ?>').appendTo("select[name='action2']");
			});
		</script>
	<?php
}

function duplicate_bulk_action() {
	global $typenow;

	$post_type = $typenow;
	
	if ($post_type == 'post') {

		// get the action
		$wp_list_table = _get_list_table('WP_Posts_List_Table');  // depending on your resource type this could be WP_Users_List_Table, WP_Comments_List_Table, etc
		$action = $wp_list_table->current_action();

		$allowed_actions = array("duplicate");
		if (!in_array($action, $allowed_actions)) return;
		
		// make sure ids are submitted. Depending on the resource type, this may be 'media' or 'ids'
		if (isset($_REQUEST['post'])) {
			$post_ids = array_map('intval', $_REQUEST['post']);
		}
		
		if (empty($post_ids)) return;
		
		// this is based on wp-admin/edit.php
		$sendback = remove_query_arg( array('duplicate', 'untrashed', 'deleted', 'ids'), wp_get_referer() );

		if (!$sendback) {
			$sendback = admin_url( "edit.php?post_type=$post_type" );
		}

		$pagenum = $wp_list_table->get_pagenum();
		$sendback = add_query_arg('paged', $pagenum, $sendback);
		
		switch ($action) {
			case 'duplicate':

				$duplicated = 0;
				foreach ($post_ids as $post_id) {

					if (!perform_duplicate($post_id)) {
						wp_die(__('Error duplicating post.', 'post-duplicator'));
					}
	
					$duplicated++;
				}

				$sendback = add_query_arg(array('duplicate' => $duplicated, 'ids' => join(',', $post_ids) ), $sendback);
			break;

			default: return;
		}
		
		$sendback = remove_query_arg(array('action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status',  'post', 'bulk_edit', 'post_view'), $sendback);

		wp_redirect($sendback);
		exit();
	}
}

function duplicate_bulk_admin_notices() {
	global $post_type, $pagenow;

	if ($pagenow == 'edit.php' && $post_type == 'post' && isset($_REQUEST['duplicate']) && (int)$_REQUEST['duplicate']) {
		$message = sprintf(_n( 'Post duplicated.', '%s posts duplicated.', $_REQUEST['duplicate']), number_format_i18n($_REQUEST['duplicate']));
		echo "<div class=\"updated\"><p>{$message}</p></div>";
	}
}

// same as ajax.php m4c_duplicate_post function just no nonce check and different return
function perform_duplicate($original_id) {
	// Get access to the database
	global $wpdb;

	// Get variables
	$original_id = $_POST['original_id'];

	// Get the post as an array
	$duplicate = get_post($original_id, 'ARRAY_A');

	$settings = get_mtphr_post_duplicator_settings();

	// Modify some of the elements
	$duplicate['post_title'] = $duplicate['post_title'].' Copy';
	
	// Set the status
	if ($settings['status'] != 'same') {
		$duplicate['post_status'] = $settings['status'];
	}

	// Set the post date
	$timestamp = ($settings['timestamp'] == 'duplicate') ? strtotime($duplicate['post_date']) : current_time('timestamp', 0);
	if ($settings['time_offset']) {
		$offset = intval($settings['time_offset_seconds']+$settings['time_offset_minutes']*60+$settings['time_offset_hours']*3600+$settings['time_offset_days']*86400);
		if ($settings['time_offset_direction'] == 'newer') {
			$timestamp = intval($timestamp+$offset);
		} else {
			$timestamp = intval($timestamp-$offset);
		}
	}

	$duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);

	// Remove some of the keys
	unset($duplicate['ID']);
	unset($duplicate['guid']);
	unset($duplicate['comment_count']);

	// Insert the post into the database
	$duplicate_id = wp_insert_post($duplicate);

	// Duplicate all the taxonomies/terms
	$taxonomies = get_object_taxonomies($duplicate['post_type']);

	foreach ($taxonomies as $taxonomy) {
		$terms = wp_get_post_terms($original_id, $taxonomy, array('fields' => 'names'));
		wp_set_object_terms($duplicate_id, $terms, $taxonomy);
	}

	// Duplicate all the custom fields
	$custom_fields = get_post_custom( $original_id );
	foreach ($custom_fields as $key => $value) {
		add_post_meta( $duplicate_id, $key, maybe_unserialize($value[0]) );
	}

	return true;
}
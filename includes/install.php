<?php

namespace Mtphr\PostDuplicator\Install;

/**
 * Install Function
 *
 * @since      1.0.0
 * @package    PostDuplicator
 * @subpackage PostDuplicator/includes
 * @author     Metaphor Creations <joe@metaphorcreations.com>
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 */

function install( $network_wide = false ) {
	global $wpdb;

	if( is_multisite() && $network_wide ) {

		foreach( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {

			switch_to_blog( $blog_id );
			run_install();
			restore_current_blog();
		}

	} else {

		run_install();
	}
}
register_activation_hook( MTPHR_POST_DUPLICATOR_FILE, __NAMESPACE__ . '\install' );


/**
 * Run the Install process
 *
 * @since  1.0
 * @return void
 */
function run_install() {

	// Add Upgraded From Option
	// $current_version = get_option( 'mtphr_postduplicator_version' );
	// if ( $current_version ) {
	// 	update_option( 'mtphr_postduplicator_version_upgraded_from', $current_version );
	// }

	// update_option( 'mtphr_postduplicator_version', MTPHR_POST_DUPLICATOR_VERSION );
}


/**
 * When a new Blog is created in multisite, see if the plugin is network activated, and run the installer
 *
 * @since  1.0
 * @param  int    $blog_id The Blog ID created
 * @param  int    $user_id The User ID set as the admin
 * @param  string $domain  The URL
 * @param  string $path    Site Path
 * @param  int    $site_id The Site ID
 * @param  array  $meta    Blog Meta
 * @return void
 */
function new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	if ( is_plugin_active_for_network( plugin_basename( MTPHR_POST_DUPLICATOR_FILE ) ) ) {
		switch_to_blog( $blog_id );
		run_install();
		restore_current_blog();
	}
}
add_action( 'wpmu_new_blog', __NAMESPACE__ . '\new_blog_created', 10, 6 );

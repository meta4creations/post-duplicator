<?php
/*
Plugin Name: Post Duplicator
Description: Creates functionality to duplicate any and all post types, including taxonomies & custom fields
Version: 2.47
Author: Metaphor Creations
Author URI: http://www.metaphorcreations.com
License:           GPL-2.0+
License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
Requires at least: 5.0
Requires PHP:      7.4
Tested up to:      6.7.2
Text Domain:       post-duplicator
Domain Path:       /languages
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



// Plugin version.
if ( ! defined( 'MTPHR_POST_DUPLICATOR_VERSION' ) ) {
	define( 'MTPHR_POST_DUPLICATOR_VERSION', '2.47' );
}

// Plugin Folder Path.
if ( ! defined( 'MTPHR_POST_DUPLICATOR_DIR' ) ) {
	define( 'MTPHR_POST_DUPLICATOR_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'MTPHR_POST_DUPLICATOR_URL' ) ) {
	define( 'MTPHR_POST_DUPLICATOR_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File.
if ( ! defined( 'MTPHR_POST_DUPLICATOR_FILE' ) ) {
	define( 'MTPHR_POST_DUPLICATOR_FILE', __FILE__ );
}

// Plugin Root File.
if ( ! defined( 'MTPHR_POST_DUPLICATOR_BASENAME' ) ) {
	define( 'MTPHR_POST_DUPLICATOR_BASENAME', plugin_basename( __FILE__ ) );
}

add_action( 'init', 'mtphr_post_duplicator_localization' );
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
 * @since 2.27
 */
require_once MTPHR_POST_DUPLICATOR_DIR . 'vendor/autoload.php';
require_once MTPHR_POST_DUPLICATOR_DIR . 'vendor/meta4creations/mtphr-settings/index.php';
//require_once MTPHR_POST_DUPLICATOR_DIR . 'includes/mtphr-settings/index.php';

require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/api.php' );
require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/hooks.php' );
require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/install.php' );
require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/settings.php' );
require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/helpers.php' );

if ( is_admin() ) { 
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/scripts.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/edit.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/notices.php' );
	require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/scripts.php' );
  require_once( MTPHR_POST_DUPLICATOR_DIR.'includes/upgrades.php' );
}




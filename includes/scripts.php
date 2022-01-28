<?php

/**
 * Load the metaboxer scripts
 *
 * @since 2.26
 */
function mtphr_post_duplicator_metaboxer_scripts( $hook ) {
	if( $hook == 'tools_page_mtphr_post_duplicator_settings_menu' ) {
    $version = WP_DEBUG ? time() : MTPHR_POST_DUPLICATOR_VERSION;
		wp_enqueue_style( 'mtphr-post-duplicator-metaboxer', MTPHR_POST_DUPLICATOR_URL . 'metaboxer/metaboxer.css', false, $version );
	}
}
add_action( 'admin_enqueue_scripts', 'mtphr_post_duplicator_metaboxer_scripts' );

/**
 * Add the necessary jquery.
 *
 * @since 2.26
 */
function m4c_duplicate_post_scripts( $hook_suffix ) {
  $version = WP_DEBUG ? time() : MTPHR_POST_DUPLICATOR_VERSION;
	wp_enqueue_script( 'mtphr-post-duplicator', MTPHR_POST_DUPLICATOR_URL . 'assets/js/pd-admin.js', array('jquery'), $version );
}
add_action( 'admin_enqueue_scripts', 'm4c_duplicate_post_scripts' );
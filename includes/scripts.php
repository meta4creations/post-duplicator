<?php

// Load Metaboxer
if( !function_exists('metaboxer_container') ) {

	add_action( 'admin_enqueue_scripts', 'mtphr_post_duplicator_metaboxer_scripts' );
	/**
	 * Load the metaboxer scripts
	 *
	 * @since 1.0
	 */
	function mtphr_post_duplicator_metaboxer_scripts() {
		
		// Load the style sheet
		wp_register_style( 'metaboxer', MTPHR_POST_DUPLICATOR_URL.'/metaboxer/metaboxer.css', array( 'colors', 'thickbox', 'farbtastic' ), MTPHR_POST_DUPLICATOR_VERSION );
		wp_enqueue_style( 'metaboxer' );
	
		// Load the jQuery
		wp_register_script( 'metaboxer', MTPHR_POST_DUPLICATOR_URL.'/metaboxer/metaboxer.js', array('jquery','media-upload','thickbox','jquery-ui-core','jquery-ui-sortable','jquery-ui-datepicker', 'jquery-ui-slider', 'farbtastic'), MTPHR_POST_DUPLICATOR_VERSION, true );
		wp_enqueue_script( 'metaboxer' );
	}
}




add_action( 'admin_enqueue_scripts', 'm4c_duplicate_post_scripts' );
/**
 * Add the necessary jquery.
 *
 * @since 1.0.0
 */
function m4c_duplicate_post_scripts( $hook_suffix ) {
	if( $hook_suffix == 'edit.php' ) {
		wp_enqueue_script( 'm4c-post-duplicator', MTPHR_POST_DUPLICATOR_URL.'/assets/js/pd-admin.js', array('jquery'), MTPHR_POST_DUPLICATOR_VERSION );
	}
}
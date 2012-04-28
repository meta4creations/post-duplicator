<?php
/*
Plugin Name: Post Duplicator
Description: Creates functionality to duplicate any and all post types, including taxonomies & custom fields
Version: 1.0
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




add_action( 'admin_enqueue_scripts', 'm4c_duplicate_post_scripts' );
/**
 * Add the necessary jquery.
 *
 * @since 1.0.0
 */
function m4c_duplicate_post_scripts( $hook_suffix ) {
	if( $hook_suffix == 'edit.php' ) {
		wp_enqueue_script( 'm4c-duplicate-post', plugins_url().'/m4c-postduplicator/m4c-postduplicator.js', array('jquery'), '1.0' );
	}
}




add_filter( 'post_row_actions', 'm4c_action_row', 10, 2 );
add_filter( 'page_row_actions', 'm4c_action_row', 10, 2 );
/**
 * Add a duplicate post link.
 *
 * @since 1.0.0
 */
function m4c_action_row( $actions, $post ){

	// Get the post type object
	$post_type = get_post_type_object( $post->post_type );
	
	// Create a nonce & add an action
	$nonce = wp_create_nonce( 'm4c_ajax_file_nonce' ); 
  $actions['duplicate_post'] = '<a class="m4c-duplicate-post" rel="'.$nonce.'" href="'.$post->ID.'">Duplicate '.$post_type->labels->singular_name.'</a>';

	return $actions;
}




add_action( 'wp_ajax_m4c_duplicate_post', 'm4c_duplicate_post' );
/**
 * Thehe jQuery ajax call to create a new post.
 * Duplicates all the data including custom meta.
 *
 * @since 1.0.0
 */
function m4c_duplicate_post() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'm4c_ajax_file_nonce', 'security' );
	
	// Get variables
	$original_id  = $_POST['original_id'];
	
	// Get the post as an array
	$duplicate = get_post( $original_id, 'ARRAY_A' );
	
	// Modify some of the elements
	$duplicate['post_title'] = $duplicate['post_title'].' Copy';

	// Remove some of the keys
	unset( $duplicate['ID'] );
	unset( $duplicate['guid'] );
	unset( $duplicate['comment_count'] );

	// Insert the post into the database
	$duplicate_id = wp_insert_post( $duplicate );
	
	// Duplicate all the taxonomies/terms
	$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
	foreach( $taxonomies as $taxonomy ) {
		$terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
		wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
	}

	// Duplicate all the custom fields
	$custom_fields = get_post_custom( $original_id );
  foreach ( $custom_fields as $key => $value ) {
		add_post_meta( $duplicate_id, $key, maybe_unserialize($value[0]) );
  }

	echo 'Duplicate Post Created!';

	die(); // this is required to return a proper result
}
<?php
namespace Mtphr\PostDuplicator;

add_filter( 'post_row_actions', __NAMESPACE__ . '\add_row_action', 10, 2 );
add_filter( 'page_row_actions', __NAMESPACE__ . '\add_row_action', 10, 2 );
add_filter( 'cuar/core/admin/content-list-table/row-actions', __NAMESPACE__ . '\add_row_action', 10, 2 );

/**
 * Add a duplicate post link.
 *
 * @since 2.27
 */
function add_row_action_link( $post ) {
	
	// Do not show on trash page
	$post_status = isset( $_GET['post_status'] ) ? sanitize_text_field( $_GET['post_status'] ) : false;
	if ( 'trash' == $post_status ) {
		return false;
	}

  // Make sure the user can duplicate
	if ( ! user_can_duplicate( $post ) ) {
		return false;
	}

	// Get the post type object
	$post_type = get_post_type_object( $post->post_type );
	
	// Set the button label
	$label = sprintf( __( 'Duplicate %s', 'post-duplicator' ), $post_type->labels->singular_name );
	
	// Modify the label if duplicating to new post type
	if( 'same' != get_option_value( 'type' ) ) {
		if ( $new_post_type = get_post_type_object( get_option_value( 'type' ) ) ) {
      if ( $post_type->name != $new_post_type->name ) {
        $label = sprintf( __( 'Duplicate %1$s to %2$s', 'post-duplicator' ), $post_type->labels->singular_name, $new_post_type->labels->singular_name );
      }
    }
	}

	// Return the link
	return '<a class="m4c-duplicate-post" href="#" data-postid="'.esc_attr( $post->ID ).'">'.wp_kses_post( $label ).'</a>';
}

// Add the duplicate link to post actions
function add_row_action( $actions, $post ){
	if ( $link = add_row_action_link( $post ) ) {
    $actions['duplicate_post'] = $link;
  }	
	return $actions;
}


<?php
namespace Mtphr\PostDuplicator;
	
/**
 * Return an array of post types
 */
function duplicator_post_types() {
	
	$post_types = array('same' => __('Same as original', 'post-duplicator'));
	$pts = get_post_types(array(), 'objects');
	
	// Remove framework post types
	unset( $pts['attachment'] );
	unset( $pts['revision'] );
	unset( $pts['nav_menu_item'] );
	unset( $pts['wooframework'] );

	if( is_array($pts) && count($pts) > 0 ) {
		foreach( $pts as $i=>$pt ) {
			$post_types[$i] = sanitize_text_field( $pt->labels->singular_name );
		}
	}
	
	return $post_types;	
}

/**
 * Check if a user can duplicate
 */
function user_can_duplicate( $post ) {

  if ( ! current_user_can( 'duplicate_posts' ) ) {
    return false;
  }
  
  if ( get_current_user_id() != $post->post_author ) {
    if ( ! current_user_can( 'duplicate_others_posts' ) ) {
      return false;
    }
    
    if ( 'draft' == $post->post_status && 'disabled' === get_option_value( 'duplicate_other_draft' ) ) {
      return false;
    }

    if ( 'pending' == $post->post_status && 'disabled' === get_option_value( 'duplicate_other_pending' ) ) {
      return false;
    }

    if ( 'private' == $post->post_status && 'disabled' === get_option_value( 'duplicate_other_private' ) ) {
      return false;
    }

    if ( '' != $post->post_password && 'disabled' === get_option_value( 'duplicate_other_password' ) ) {
      return false;
    }

    if ( 'future' == $post->post_status && 'disabled' === get_option_value( 'duplicate_other_future' ) ) {
      return false;
    }
  }
  
  return true;
}
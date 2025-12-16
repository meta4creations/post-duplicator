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

/**
 * Check if a post type supports authors
 */
function post_type_supports_author( $post_type ) {
	$post_type_obj = get_post_type_object( $post_type );
	if ( ! $post_type_obj ) {
		return false;
	}
	return post_type_supports( $post_type, 'author' );
}

/**
 * Get author support information for all post types
 */
function get_post_types_author_support() {
	$post_types = get_post_types( array(), 'objects' );
	$author_support = array();
	
	foreach ( $post_types as $post_type => $post_type_obj ) {
		// Skip system post types
		if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item', 'wooframework' ) ) ) {
			continue;
		}
		$author_support[ $post_type ] = post_type_supports( $post_type, 'author' );
	}
	
	return $author_support;
}

/**
 * Get hierarchical support information for all post types
 */
function get_post_types_hierarchical_support() {
	$post_types = get_post_types( array(), 'objects' );
	$hierarchical_support = array();
	
	foreach ( $post_types as $post_type => $post_type_obj ) {
		// Skip system post types
		if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item', 'wooframework' ) ) ) {
			continue;
		}
		$hierarchical_support[ $post_type ] = $post_type_obj->hierarchical;
	}
	
	return $hierarchical_support;
}

/**
 * Get meta keys that should never be cloned to duplicated posts
 * 
 * These meta keys will be excluded from:
 * - The duplicate post modal display
 * - The duplication process
 * 
 * @return array Array of meta keys to exclude
 */
function get_excluded_meta_keys() {
	/**
	 * Filter the list of meta keys that should never be cloned
	 * 
	 * @param array $excluded_keys Array of meta keys to exclude from duplication
	 */
	$excluded_keys = apply_filters( 'mtphr_post_duplicator_excluded_meta_keys', array(
		'_elementor_css',
		'_elementor_element_cache',
	) );
	
	return $excluded_keys;
}
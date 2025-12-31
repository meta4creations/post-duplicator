<?php
namespace Mtphr\PostDuplicator;
	
/**
 * Return an array of post types
 * 
 * @param string $original_post_type Optional. Original post type to always include in dropdown
 * @return array Array of post types
 */
function duplicator_post_types( $original_post_type = null ) {
	
	$post_types = array('same' => __('Same as original', 'post-duplicator'));
	$pts = get_post_types(array(), 'objects');
	
	// Remove framework post types
	unset( $pts['attachment'] );
	unset( $pts['revision'] );
	unset( $pts['nav_menu_item'] );
	unset( $pts['wooframework'] );

	// Get enabled post types for dropdown
	$enabled_for_dropdown = get_enabled_post_types_for_dropdown();
	
	// Always include original post type if provided, even if not enabled in dropdown
	if ( $original_post_type && isset( $pts[ $original_post_type ] ) ) {
		$post_types[ $original_post_type ] = sanitize_text_field( $pts[ $original_post_type ]->labels->singular_name );
	}
	
	if( is_array($pts) && count($pts) > 0 ) {
		foreach( $pts as $i=>$pt ) {
			// Only include if enabled in dropdown (and not already added as original post type)
			if ( in_array( $i, $enabled_for_dropdown ) && $i !== $original_post_type ) {
				$post_types[$i] = sanitize_text_field( $pt->labels->singular_name );
			}
		}
	}
	
	// Sort alphabetically by label (keep 'same' at the top)
	$same_option = array( 'same' => $post_types['same'] );
	unset( $post_types['same'] );
	uasort( $post_types, function( $a, $b ) {
		return strcasecmp( $a, $b );
	} );
	
	// Put 'same' back at the beginning
	return array_merge( $same_option, $post_types );
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
 * Get public status information for all post types
 */
function get_post_types_public_support() {
	$post_types = get_post_types( array(), 'objects' );
	$public_support = array();
	
	foreach ( $post_types as $post_type => $post_type_obj ) {
		// Skip system post types
		if ( in_array( $post_type, array( 'attachment', 'revision', 'nav_menu_item', 'wooframework' ) ) ) {
			continue;
		}
		// A post type is considered public if 'public' is true OR 'publicly_queryable' is true
		$public_support[ $post_type ] = ! empty( $post_type_obj->public ) || ! empty( $post_type_obj->publicly_queryable );
	}
	
	return $public_support;
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

/**
 * Get all post types for configuration
 * Returns array of post type objects with id and label
 * 
 * @return array Array of post type objects [{id: 'post', label: 'Post'}, ...]
 */
function get_all_post_types( $label_type = 'label' ) {
	$post_types = get_post_types( array(), 'objects' );
	$result = array();
	
	// System post types to exclude
	$excluded = array( 'attachment', 'revision', 'nav_menu_item', 'wooframework' );
	
	foreach ( $post_types as $post_type => $post_type_obj ) {
		if ( ! in_array( $post_type, $excluded ) ) {
			$label = $post_type_obj->labels->singular_name;
			if ( $label_type === 'label_slug' ) {
				$label = "{$label} ({$post_type})";
			} elseif ( $label_type === 'slug' ) {
				$label = $post_type;
			}
			$result[] = array(
				'id' => $post_type,
				'label' => $label,
			);
		}
	}
	
	// Sort alphabetically by label
	usort( $result, function( $a, $b ) {
		return strcasecmp( $a['label'], $b['label'] );
	} );
	
	return $result;
}

/**
 * Get enabled post types for duplication
 * Returns array of post type slugs where allow_duplication is true
 * 
 * @return array Array of post type slugs
 */
function get_enabled_post_types_for_duplication() {
	$config = get_option_value( 'post_types_config' );
	
	// If no config, enable all post types by default
	if ( empty( $config ) || ! is_array( $config ) ) {
		$all_types = get_all_post_types();
		return array_column( $all_types, 'id' );
	}
	
	$enabled = array();
	foreach ( $config as $post_type => $settings ) {
		if ( isset( $settings['allow_duplication'] ) && $settings['allow_duplication'] ) {
			$enabled[] = $post_type;
		}
	}
	
	return $enabled;
}

/**
 * Get enabled post types for dropdown
 * Returns array of post type slugs where allow_in_dropdown is true
 * 
 * @return array Array of post type slugs
 */
function get_enabled_post_types_for_dropdown() {
	$config = get_option_value( 'post_types_config' );
	
	// If no config, only enable post and page by default
	if ( empty( $config ) || ! is_array( $config ) ) {
		return array( 'post', 'page' );
	}
	
	$enabled = array();
	foreach ( $config as $post_type => $settings ) {
		if ( isset( $settings['allow_in_dropdown'] ) && $settings['allow_in_dropdown'] ) {
			$enabled[] = $post_type;
		}
	}
	
	return $enabled;
}

/**
 * Check if a post type is enabled for duplication
 * 
 * @param string $post_type Post type slug
 * @return bool True if enabled
 */
function is_post_type_duplication_enabled( $post_type ) {
	$enabled = get_enabled_post_types_for_duplication();
	return in_array( $post_type, $enabled );
}
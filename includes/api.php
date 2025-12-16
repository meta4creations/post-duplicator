<?php
namespace Mtphr\PostDuplicator;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_routes' );

/**
 * Register rest routes
 */
function register_routes() {
  register_rest_route( 'post-duplicator/v1', 'duplicate-post', array(
    'methods' 	=> 'POST',
    'permission_callback' => __NAMESPACE__ . '\duplicate_post_permissions',
    'callback' => __NAMESPACE__ . '\duplicate_post',
  ) );
  
  register_rest_route( 'post-duplicator/v1', 'post-data/(?P<id>\d+)', array(
    'methods' => 'GET',
    'permission_callback' => __NAMESPACE__ . '\get_post_data_permissions',
    'callback' => __NAMESPACE__ . '\get_post_data',
    'args' => array(
      'id' => array(
        'validate_callback' => function( $param ) {
          return is_numeric( $param );
        },
      ),
    ),
  ) );
  
  register_rest_route( 'post-duplicator/v1', 'parent-posts', array(
    'methods' => 'GET',
    'permission_callback' => '__return_true',
    'callback' => __NAMESPACE__ . '\get_parent_posts',
  ) );
}

/**
 * Permission check for getting post data
 */
function get_post_data_permissions( $request ) {
  $post_id = $request->get_param( 'id' );
  
  if ( ! $post_id ) {
    return new \WP_Error( 'no_post_id', esc_html__( 'No post ID provided.', 'post-duplicator' ), array( 'status' => 403 ) );
  }
  
  $post = get_post( $post_id );
  if ( ! $post ) {
    return new \WP_Error( 'post_not_found', esc_html__( 'Post not found.', 'post-duplicator' ), array( 'status' => 404 ) );
  }
  
  if ( ! user_can_duplicate( $post ) ) {
    return new \WP_Error( 'no_permission', esc_html__( 'User does not have permission to view this post.', 'post-duplicator' ), array( 'status' => 403 ) );
  }
  
  return true;
}

/**
 * Get taxonomy and custom meta data for a post
 */
function get_post_data( $request ) {
  $post_id = $request->get_param( 'id' );
  $post = get_post( $post_id );
  
  if ( ! $post ) {
    return new \WP_Error( 'post_not_found', esc_html__( 'Post not found.', 'post-duplicator' ), array( 'status' => 404 ) );
  }
  
  // Get taxonomies
  $taxonomies_data = array();
  $taxonomies = get_object_taxonomies( $post->post_type );
  $disabled_taxonomies = array( 'post_translations', 'post_format' );
  
  foreach ( $taxonomies as $taxonomy_slug ) {
    if ( in_array( $taxonomy_slug, $disabled_taxonomies ) ) {
      continue;
    }
    
    $taxonomy = get_taxonomy( $taxonomy_slug );
    if ( ! $taxonomy ) {
      continue;
    }
    
    // Get terms currently assigned to the post
    $assigned_term_ids = wp_get_post_terms( $post_id, $taxonomy_slug, array( 'fields' => 'ids' ) );
    
    // Get ALL available terms for this taxonomy
    $all_terms = get_terms( array(
      'taxonomy' => $taxonomy_slug,
      'hide_empty' => false,
    ) );
    
    $terms_data = array();
    if ( ! is_wp_error( $all_terms ) ) {
      foreach ( $all_terms as $term ) {
        $terms_data[] = array(
          'id' => $term->term_id,
          'name' => $term->name,
          'slug' => $term->slug,
        );
      }
    }
    
    $taxonomies_data[] = array(
      'slug' => $taxonomy_slug,
      'label' => $taxonomy->labels->name,
      'hierarchical' => $taxonomy->hierarchical,
      'terms' => $terms_data,
      'assignedTermIds' => $assigned_term_ids,
    );
  }
  
  // Get custom meta fields
  $custom_meta_data = array();
  $custom_fields = get_post_custom( $post_id );
  $excluded_meta_keys = get_excluded_meta_keys();
  
  foreach ( $custom_fields as $key => $values ) {
    // Skip excluded meta keys
    if ( in_array( $key, $excluded_meta_keys, true ) ) {
      continue;
    }
    
    // Check if meta is enabled via filter (defaults to true for all meta keys, including those starting with "_")
    if ( ! apply_filters( "mtphr_post_duplicator_meta_{$key}_enabled", true ) ) {
      continue;
    }
    
    foreach ( $values as $value ) {
      // Detect data type
      $type = 'string';
      $is_serialized = false;
      $original_value = $value;
      
      // Check if serialized
      if ( is_serialized( $value ) ) {
        $is_serialized = true;
        $unserialized = maybe_unserialize( $value );
        if ( is_array( $unserialized ) ) {
          $type = 'array';
          $value = wp_json_encode( $unserialized, JSON_PRETTY_PRINT );
        } elseif ( is_object( $unserialized ) ) {
          $type = 'object';
          $value = wp_json_encode( $unserialized, JSON_PRETTY_PRINT );
        } else {
          $type = 'string';
        }
      } elseif ( is_numeric( $value ) ) {
        // Check if it's a number (int or float)
        if ( strpos( $value, '.' ) !== false ) {
          $type = 'number';
        } else {
          $type = 'number';
        }
      } elseif ( $value === 'true' || $value === 'false' || $value === '1' || $value === '0' || $value === '' ) {
        // Could be boolean, but WordPress stores as string
        $type = 'string';
      }
      
      // Try to detect JSON
      if ( ! $is_serialized ) {
        $json_decoded = json_decode( $value, true );
        if ( json_last_error() === JSON_ERROR_NONE && ( is_array( $json_decoded ) || is_object( $json_decoded ) ) ) {
          $type = is_array( $json_decoded ) ? 'array' : 'object';
          $value = wp_json_encode( $json_decoded, JSON_PRETTY_PRINT );
        }
      }
      
      $custom_meta_data[] = array(
        'key' => $key,
        'value' => $value,
        'type' => $type,
        'isSerialized' => $is_serialized,
        'originalValue' => $original_value,
      );
    }
  }
  
  return rest_ensure_response( array(
    'taxonomies' => $taxonomies_data,
    'customMeta' => $custom_meta_data,
  ) );
}

/**
 * Get available parent posts for a post type (hierarchical)
 */
function get_parent_posts( $request ) {
  $post_type = $request->get_param( 'post_type' );
  $exclude_id = $request->get_param( 'exclude_id' );
  
  if ( ! $post_type ) {
    // Default to 'page' if no post type specified
    $post_type = 'page';
  }
  
  // Get posts that can be parents (same post type, published or draft)
  $args = array(
    'post_type' => $post_type,
    'post_status' => array( 'publish', 'draft', 'private' ),
    'posts_per_page' => -1,
    'orderby' => 'menu_order title',
    'order' => 'ASC',
  );
  
  // Exclude the current post (can't be its own parent)
  if ( $exclude_id ) {
    $args['post__not_in'] = array( intval( $exclude_id ) );
  }
  
  $posts = get_posts( $args );
  
  // Build a map of posts by ID
  $posts_map = array();
  foreach ( $posts as $post ) {
    $posts_map[ $post->ID ] = array(
      'id' => $post->ID,
      'title' => $post->post_title,
      'parent' => $post->post_parent,
      'children' => array(),
    );
  }
  
  // Build hierarchical structure and collect descendants of excluded post
  $excluded_descendants = array();
  if ( $exclude_id ) {
    $exclude_id_int = intval( $exclude_id );
    // Recursively find all descendants of the excluded post from the full post list
    // We need to query all posts to find descendants, not just the ones we're showing
    $all_posts_args = array(
      'post_type' => $post_type,
      'post_status' => array( 'publish', 'draft', 'private' ),
      'posts_per_page' => -1,
      'fields' => 'ids',
    );
    $all_post_ids = get_posts( $all_posts_args );
    
    $find_descendants = function( $parent_id ) use ( &$find_descendants, $all_post_ids, $post_type ) {
      $descendants = array();
      foreach ( $all_post_ids as $post_id ) {
        $post = get_post( $post_id );
        if ( $post && $post->post_parent == $parent_id ) {
          $descendants[] = $post_id;
          // Recursively get descendants of this child
          $descendants = array_merge( $descendants, $find_descendants( $post_id ) );
        }
      }
      return $descendants;
    };
    $excluded_descendants = $find_descendants( $exclude_id_int );
    $excluded_descendants[] = $exclude_id_int; // Include the post itself
  }
  
  $root_posts = array();
  foreach ( $posts_map as $post_id => $post_data ) {
    // Skip if this post is a descendant of the excluded post
    if ( in_array( $post_id, $excluded_descendants ) ) {
      continue;
    }
    
    if ( $post_data['parent'] == 0 ) {
      // Root level post
      $root_posts[] = $post_id;
    } else {
      // Child post - add to parent's children array only if parent is not excluded
      if ( isset( $posts_map[ $post_data['parent'] ] ) && ! in_array( $post_data['parent'], $excluded_descendants ) ) {
        $posts_map[ $post_data['parent'] ]['children'][] = $post_id;
      } else {
        // Parent not in our list (different post type or excluded), treat as root
        $root_posts[] = $post_id;
      }
    }
  }
  
  // Recursive function to build flat list with hierarchy info
  $hierarchical_list = array();
  $build_list = function( $post_ids, $level = 0 ) use ( &$build_list, &$hierarchical_list, &$posts_map ) {
    foreach ( $post_ids as $post_id ) {
      if ( ! isset( $posts_map[ $post_id ] ) ) {
        continue;
      }
      
      $post_data = $posts_map[ $post_id ];
      $hierarchical_list[] = array(
        'id' => $post_data['id'],
        'title' => $post_data['title'],
        'level' => $level,
        'parent' => $post_data['parent'],
      );
      
      // Recursively add children
      if ( ! empty( $post_data['children'] ) ) {
        $build_list( $post_data['children'], $level + 1 );
      }
    }
  };
  
  // Build the hierarchical list starting from root posts
  $build_list( $root_posts );
  
  return rest_ensure_response( $hierarchical_list );
}

/**
 * Duplicate a post
 */
function duplicate_post_permissions( $request ) {
  $args = $request->get_params();
  $data = $request->get_json_params();
  $original_id = isset( $data['original_id'] ) ? $data['original_id'] : false;

  if ( ! $original_id ) {
    return new \WP_Error( 'no_original_id', esc_html__( 'No original id passed.', 'post-duplicator' ), array( 'status' => 403 ) );
  }

  $post = get_post( $original_id );
  if ( ! user_can_duplicate( $post ) ) {
	  return new \WP_Error( 'no_permission', esc_html__( 'User does not have permission to duplicate post.', 'post-duplicator' ), array( 'status' => 403 ) );
	}

  return true;
}

/**
 * Duplicate a post
 */
function duplicate_post( $request ) {
  $args = $request->get_params();
  $data = $request->get_json_params();

  // Get access to the database
	global $wpdb;

  // Get the original id
  $original_id = $data['original_id'];
	
	// Get the original post object
	$orig = get_post( $original_id );
	
	if ( ! $orig ) {
		return new \WP_Error( 'post_not_found', esc_html__( 'Original post not found.', 'post-duplicator' ), array( 'status' => 404 ) );
	}
		
	// Get default settings
	$default_settings = get_option_value();
	
	// Merge with any override settings from the request
	// Remove original_id from data to get only settings
	$override_settings = $data;
	unset( $override_settings['original_id'] );
	
	// Merge: override settings take precedence
	$settings = array_merge( $default_settings, $override_settings );
	
	// Create an empty array and populate only the fields we want
	// This ensures we don't carry over any unwanted data
	$duplicate = array();
	
	// Copy basic post fields explicitly
	$duplicate['post_author'] = $orig->post_author;
	$duplicate['post_content'] = $orig->post_content;
	$duplicate['post_title'] = $orig->post_title;
	$duplicate['post_excerpt'] = $orig->post_excerpt;
	$duplicate['post_status'] = $orig->post_status;
	$duplicate['comment_status'] = $orig->comment_status;
	$duplicate['ping_status'] = $orig->ping_status;
	$duplicate['post_password'] = $orig->post_password;
	$duplicate['post_name'] = $orig->post_name;
	$duplicate['to_ping'] = $orig->to_ping;
	$duplicate['pinged'] = $orig->pinged;
	$duplicate['post_content_filtered'] = $orig->post_content_filtered;
	$duplicate['post_parent'] = $orig->post_parent;
	$duplicate['menu_order'] = $orig->menu_order;
	$duplicate['post_type'] = $orig->post_type;
	$duplicate['post_mime_type'] = $orig->post_mime_type;
	
	// Modify the title
	// If fullTitle is provided (user edited the full title), use it
	// Otherwise, append the suffix
	if ( isset( $settings['fullTitle'] ) && ! empty( $settings['fullTitle'] ) ) {
		$duplicate['post_title'] = sanitize_text_field( $settings['fullTitle'] );
	} else {
		$appended = isset( $settings['title'] ) ? sanitize_text_field( $settings['title'] ) : esc_html__( 'Copy', 'post-duplicator' );
		$duplicate['post_title'] = $duplicate['post_title'] . ' ' . $appended;
	}
	
	// Modify the slug
	// If fullSlug is provided (user edited the full slug), use it
	// Otherwise, append the suffix
	if ( isset( $settings['fullSlug'] ) && ! empty( $settings['fullSlug'] ) ) {
		$duplicate['post_name'] = sanitize_title( $settings['fullSlug'] );
	} else {
		$duplicate['post_name'] = sanitize_title( $duplicate['post_name'] . '-' . $settings['slug'] );
	}
	
	// Set the status
	if( $settings['status'] != 'same' ) {
		$duplicate['post_status'] = sanitize_text_field( $settings['status'] );
	}
	
	// Check if a user has publish get_post_type_capabilities. If not, make sure they can't _publish
	if ( ! current_user_can( 'publish_posts' ) ) {
		// Force the post status to pending
		if ( 'publish' == $duplicate['post_status'] ) {
			$duplicate['post_status'] = 'pending';
		}
	}
	
	// Set the type
	if( $settings['type'] != 'same' ) {
		$duplicate['post_type'] = sanitize_text_field( $settings['type'] );
	}
	
	// Set the parent - check for selectedParentId first, otherwise keep original parent
	if ( isset( $settings['selectedParentId'] ) ) {
		$duplicate['post_parent'] = intval( $settings['selectedParentId'] );
	}
	
	// Set the post date
	if ( $settings['timestamp'] == 'duplicate' ) {
		$timestamp = strtotime($orig->post_date);
		$timestamp_gmt = strtotime($orig->post_date_gmt);
	} elseif ( $settings['timestamp'] == 'custom' && isset( $settings['customDate'] ) && ! empty( $settings['customDate'] ) ) {
		// Use custom date if provided
		$custom_date = $settings['customDate'];
		try {
			// Parse the ISO date string (e.g., "2024-01-15T10:30:00.000Z")
			// JavaScript's toISOString() returns UTC time
			// Convert ISO format to WordPress date format (Y-m-d H:i:s)
			$date_obj = new \DateTime( $custom_date, new \DateTimeZone( 'UTC' ) );
			$gmt_date = $date_obj->format( 'Y-m-d H:i:s' );
			
			// Convert GMT date to local timezone using WordPress function
			$local_date = get_date_from_gmt( $gmt_date );
			
			$timestamp = strtotime( $local_date );
			$timestamp_gmt = strtotime( $gmt_date );
		} catch ( \Exception $e ) {
			// If date parsing fails, fall back to current time
			$timestamp = current_time('timestamp',0);
			$timestamp_gmt = current_time('timestamp',1);
		}
	} else {
		$timestamp = current_time('timestamp',0);
		$timestamp_gmt = current_time('timestamp',1);
	}
	
	if( isset( $settings['time_offset'] ) && $settings['time_offset'] ) {
		$offset = intval($settings['time_offset_seconds']+$settings['time_offset_minutes']*60+$settings['time_offset_hours']*3600+$settings['time_offset_days']*86400);
		if( $settings['time_offset_direction'] == 'newer' ) {
			$timestamp = intval($timestamp+$offset);
			$timestamp_gmt = intval($timestamp_gmt+$offset);
		} else {
			$timestamp = intval($timestamp-$offset);
			$timestamp_gmt = intval($timestamp_gmt-$offset);
		}
	}
	$duplicate['post_date'] = date('Y-m-d H:i:s', $timestamp);
	$duplicate['post_date_gmt'] = date('Y-m-d H:i:s', $timestamp_gmt);
	$duplicate['post_modified'] = date('Y-m-d H:i:s', current_time('timestamp',0));
	$duplicate['post_modified_gmt'] = date('Y-m-d H:i:s', current_time('timestamp',1));
	
	// Set author - check for selectedAuthorId first, then fall back to post_author setting
	// Handle "No Author" case (null or empty selectedAuthorId)
	if ( isset( $settings['selectedAuthorId'] ) ) {
		if ( $settings['selectedAuthorId'] === null || $settings['selectedAuthorId'] === '' || $settings['selectedAuthorId'] === 0 ) {
			// "No Author" - set to 0 for post types that don't support authors
			$duplicate['post_author'] = 0;
		} else {
			$duplicate['post_author'] = intval( $settings['selectedAuthorId'] );
		}
	} elseif ( 'current_user' == $settings['post_author'] ) {
		$duplicate['post_author'] = get_current_user_id();
	}

	// Sanitize post content
	add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\additional_kses', 10, 2 );
	$duplicate['post_content'] = wp_slash( wp_kses_post( $duplicate['post_content'] ) ); 
	remove_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\additional_kses', 10, 2 );

	// Insert the post into the database
	$duplicate_id = wp_insert_post( $duplicate );

	// Handle featured image
	if ( isset( $settings['featuredImageId'] ) ) {
		// If featuredImageId is null or 0, remove the featured image
		if ( empty( $settings['featuredImageId'] ) ) {
			delete_post_thumbnail( $duplicate_id );
		} else {
			// Set the featured image
			$thumbnail_id = intval( $settings['featuredImageId'] );
			// Verify the attachment exists and is an image
			$attachment = get_post( $thumbnail_id );
			if ( $attachment && wp_attachment_is_image( $thumbnail_id ) ) {
				set_post_thumbnail( $duplicate_id, $thumbnail_id );
			}
		}
	} else {
		// Default behavior: copy featured image from original post if it exists
		$original_thumbnail_id = get_post_thumbnail_id( $original_id );
		if ( $original_thumbnail_id ) {
			set_post_thumbnail( $duplicate_id, $original_thumbnail_id );
		}
	}

	// check which terms are connected to the duplicate post right here and now
	$duplicate_terms = wp_get_post_terms( $duplicate_id, get_object_taxonomies( $duplicate['post_type'] ) );
	
	// Handle taxonomies
	// Default to true for backward compatibility
	$include_taxonomies = false;
	if ( isset( $settings['includeTaxonomies'] ) && false !== $settings['includeTaxonomies'] ) {
		$tax_value = $settings['includeTaxonomies'];
		// Handle both boolean and string boolean values from JSON
		if ( is_bool( $tax_value ) ) {
			$include_taxonomies = $tax_value;
		} elseif ( is_string( $tax_value ) ) {
			// Handle string booleans - explicitly check for false strings
			$include_taxonomies = ! ( $tax_value === 'false' || $tax_value === '0' || $tax_value === '' );
		} elseif ( $tax_value === 0 || $tax_value === '0' ) {
			// Explicitly handle 0/false values
			$include_taxonomies = false;
		} else {
			// For any other value, cast to bool
			$include_taxonomies = (bool) $tax_value;
		}
	}
	
	// Only duplicate taxonomies if explicitly enabled
	// Use strict comparison to ensure false/0 values are respected
	if ( $include_taxonomies === true ) {
		// Use provided taxonomy data if available, otherwise fetch from original post
		if ( isset( $settings['taxonomyData'] ) && is_array( $settings['taxonomyData'] ) && ! empty( $settings['taxonomyData'] ) ) {
			// Use provided taxonomy data
			foreach ( $settings['taxonomyData'] as $taxonomy_slug => $term_ids ) {
				if ( ! is_array( $term_ids ) ) {
					continue;
				}
				// Convert term IDs to integers and filter out invalid values
				$term_ids = array_map( 'intval', $term_ids );
				$term_ids = array_filter( $term_ids );
				
				if ( ! empty( $term_ids ) ) {
					wp_set_object_terms( $duplicate_id, $term_ids, $taxonomy_slug );
				}
			}
		} elseif ( ! isset( $settings['taxonomyData'] ) ) {
			// Only fall back to original behavior if taxonomyData was not provided at all
			// This means the user didn't customize, so use default behavior
			$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
			$disabled_taxonomies = ['post_translations'];
			foreach( $taxonomies as $taxonomy ) {
				if ( in_array( $taxonomy, $disabled_taxonomies ) ) {
					continue;
				}
				$terms = wp_get_post_terms( $original_id, $taxonomy, array('fields' => 'names') );
				wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
			}
		}
		// If includeTaxonomies is false, do nothing - taxonomies are not duplicated
	}

	
	// Handle custom meta fields
	// Default to true for backward compatibility
	$include_custom_meta = false;
	if ( isset( $settings['includeCustomMeta'] ) && false !== $settings['includeCustomMeta'] ) {
		// Handle both boolean and string boolean values from JSON
		if ( is_bool( $settings['includeCustomMeta'] ) ) {
			$include_custom_meta = $settings['includeCustomMeta'];
		} elseif ( is_string( $settings['includeCustomMeta'] ) ) {
			// Handle string booleans (shouldn't happen with proper JSON, but be safe)
			$include_custom_meta = ( $settings['includeCustomMeta'] === 'true' || $settings['includeCustomMeta'] === '1' );
		} elseif ( $settings['includeCustomMeta'] === 0 || $settings['includeCustomMeta'] === '0' ) {
			// Explicitly handle 0/false values
			$include_custom_meta = false;
		} else {
			$include_custom_meta = (bool) $settings['includeCustomMeta'];
		}
	}
	
	// Only duplicate custom meta if explicitly enabled
	if ( $include_custom_meta === true ) {
		// Use provided custom meta data if available, otherwise fetch from original post
		if ( isset( $settings['customMetaData'] ) && is_array( $settings['customMetaData'] ) ) {
			// Use provided custom meta data
			$excluded_meta_keys = get_excluded_meta_keys();
			foreach ( $settings['customMetaData'] as $meta_item ) {
				if ( ! isset( $meta_item['key'] ) || ! isset( $meta_item['value'] ) ) {
					continue;
				}
				
				$meta_key = sanitize_text_field( $meta_item['key'] );
				
				// Skip excluded meta keys
				if ( in_array( $meta_key, $excluded_meta_keys, true ) ) {
					continue;
				}
				
				$meta_value = $meta_item['value'];
				
				// Handle data type preservation
				if ( isset( $meta_item['type'] ) && isset( $meta_item['isSerialized'] ) ) {
					if ( $meta_item['isSerialized'] ) {
						// It was originally serialized, so serialize it again
						// Try to decode JSON first if it's a JSON string (from the modal)
						$json_decoded = json_decode( $meta_value, true );
						if ( json_last_error() === JSON_ERROR_NONE ) {
							$meta_value = maybe_serialize( $json_decoded );
						} else {
							// Already a string, serialize it
							$meta_value = maybe_serialize( $meta_value );
						}
					} elseif ( in_array( $meta_item['type'], array( 'array', 'object' ) ) ) {
						// It was originally a JSON string (not serialized), so keep it as JSON
						// Validate it's valid JSON - if valid, keep as-is to preserve exact format
						// Only re-encode if necessary (e.g., if user modified it in the modal)
						$json_decoded = json_decode( $meta_value, true );
						if ( json_last_error() === JSON_ERROR_NONE ) {
							// Valid JSON - re-encode to ensure it's properly formatted
							// Use wp_json_encode which handles WordPress-specific encoding
							$meta_value = wp_json_encode( $json_decoded );
						} else {
							// Invalid JSON, sanitize as text
							$meta_value = sanitize_text_field( $meta_value );
						}
					} elseif ( $meta_item['type'] === 'number' ) {
						// Preserve as number (WordPress will store as string anyway, but we can validate)
						$meta_value = is_numeric( $meta_value ) ? $meta_value : sanitize_text_field( $meta_value );
					} else {
						// String type
						$meta_value = sanitize_text_field( $meta_value );
					}
				} else {
					// Fallback: sanitize as text
					$meta_value = sanitize_text_field( $meta_value );
				}
				
				// Apply filters
				if ( ! apply_filters( "mtphr_post_duplicator_meta_{$meta_key}_enabled", true ) ) {
					continue;
				}
				
				$meta_value = apply_filters( "mtphr_post_duplicator_meta_value", $meta_value, $meta_key, $duplicate_id, $duplicate['post_type'] );
				
				$data = array(
					'post_id' 		=> intval( $duplicate_id ),
					'meta_key' 		=> $meta_key,
					'meta_value' 	=> $meta_value,
				);
				$formats = array(
					'%d',
					'%s',
					'%s',
				);
				$result = $wpdb->insert( $wpdb->prefix.'postmeta', $data, $formats );
			}
		} else {
			// Fall back to original behavior: duplicate all custom fields
			$custom_fields = get_post_custom( $original_id );
			$excluded_meta_keys = get_excluded_meta_keys();
			foreach ( $custom_fields as $key => $value ) {
				// Skip excluded meta keys
				if ( in_array( $key, $excluded_meta_keys, true ) ) {
					continue;
				}
				
				if( is_array($value) && count($value) > 0 ) {
					foreach( $value as $i=>$v ) {
						if ( ! apply_filters( "mtphr_post_duplicator_meta_{$key}_enabled", true ) ) {
							continue;
						}
						$meta_value = apply_filters( "mtphr_post_duplicator_meta_value", $v, $key, $duplicate_id, $duplicate['post_type'] );
						$data = array(
							'post_id' 		=> intval( $duplicate_id ),
							'meta_key' 		=> sanitize_text_field( $key ),
							'meta_value' 	=> $meta_value,
						);
						$formats = array(
							'%d',
							'%s',
							'%s',
						);
						$result = $wpdb->insert( $wpdb->prefix.'postmeta', $data, $formats );
					}
				}
			}
		}
	}
	
	// Add an action for others to do custom stuff
	do_action( 'mtphr_post_duplicator_created', $original_id, $duplicate_id, $settings );

	$other_data = array(
		'duplicate_post' => $duplicate,
		'duplicate_terms' => $duplicate_terms,
	);
  return rest_ensure_response( [
		'duplicate_id' => $duplicate_id,
		'other_data' => $other_data,
	] , 200 );
}

/**
 * Add custom allowed kses
 */
function additional_kses( $allowed_tags ) {
	// Allow the center tag with its attributes
	$allowed_tags['center'] = array(
			'align' => true,
			'class' => true,
			'id' => true,
			'style' => true,
	);
	
	return $allowed_tags;
}
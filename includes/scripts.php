<?php
namespace Mtphr\PostDuplicator;

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Get users with edit capabilities
 */
function get_users_with_edit_caps() {
  $users = get_users( [
    'capability__in' => ['edit_posts'],
    'orderby' => 'display_name',
    'order' => 'ASC',
  ] );
  
  $user_options = [];
  foreach ( $users as $user ) {
    $user_options[] = [
      'value' => (string) $user->ID, // Convert to string for React select
      'label' => $user->display_name . ' (' . $user->user_login . ')',
    ];
  }
  
  return $user_options;
}

/**
 * Enqueue admin scripts
 */
function enqueue_scripts() {

  $current_screen = get_current_screen();
  if ( 'site-editor' == $current_screen->id ) {
    return;
  }

  $asset_file = include( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/postDuplicator.asset.php' );
  
  // Enqueue WordPress component styles
  wp_enqueue_style( 'wp-components' );
  
  // Enqueue WordPress media scripts for featured image functionality on post list screen
  wp_enqueue_media();
  
  wp_enqueue_style(
    'post-duplicator',
    MTPHR_POST_DUPLICATOR_URL . 'assets/build/postDuplicator.css',
    ['wp-components'],
    filemtime( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/postDuplicator.css' ),
  );
  wp_enqueue_script(
    'post-duplicator',
    MTPHR_POST_DUPLICATOR_URL . 'assets/build/postDuplicator.js',
    $asset_file['dependencies'],
    filemtime( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/postDuplicator.js' ),
    true
  ); 
  $settings = get_option_value();
  $current_user = wp_get_current_user();
  $users_list = get_users_with_edit_caps();
  
  wp_localize_script( 'post-duplicator', 'postDuplicatorVars', [
    'siteUrl'  => site_url(),
    'restUrl'  => esc_url_raw( rest_url( 'post-duplicator/v1/' ) ),
    'nonce'    => wp_create_nonce( 'wp_rest' ),
    'currentUser' => [
      'id' => $current_user->ID,
      'name' => $current_user->display_name,
    ],
    'users' => $users_list,
    'defaultSettings' => [
      'status' => isset( $settings['status'] ) ? $settings['status'] : 'draft',
      'type' => isset( $settings['type'] ) ? $settings['type'] : 'same',
      'post_author' => isset( $settings['post_author'] ) ? $settings['post_author'] : 'current_user',
      'timestamp' => isset( $settings['timestamp'] ) ? $settings['timestamp'] : 'current',
      'title' => isset( $settings['title'] ) ? $settings['title'] : esc_html__( 'Copy', 'post-duplicator' ),
      'slug' => isset( $settings['slug'] ) ? $settings['slug'] : esc_html__( 'copy', 'post-duplicator' ),
      'time_offset' => isset( $settings['time_offset'] ) ? $settings['time_offset'] : false,
      'time_offset_days' => isset( $settings['time_offset_days'] ) ? intval( $settings['time_offset_days'] ) : 0,
      'time_offset_hours' => isset( $settings['time_offset_hours'] ) ? intval( $settings['time_offset_hours'] ) : 0,
      'time_offset_minutes' => isset( $settings['time_offset_minutes'] ) ? intval( $settings['time_offset_minutes'] ) : 0,
      'time_offset_seconds' => isset( $settings['time_offset_seconds'] ) ? intval( $settings['time_offset_seconds'] ) : 0,
      'time_offset_direction' => isset( $settings['time_offset_direction'] ) ? $settings['time_offset_direction'] : 'newer',
    ],
    'postTypes' => array_filter( duplicator_post_types(), function( $key ) {
      return $key !== 'same';
    }, ARRAY_FILTER_USE_KEY ),
    'allPostTypes' => get_all_post_types(),
    'postTypesAuthorSupport' => get_post_types_author_support(),
    'postTypesHierarchicalSupport' => get_post_types_hierarchical_support(),
    'postTypesPublicSupport' => get_post_types_public_support(),
    'enabledPostTypesForDuplication' => get_enabled_post_types_for_duplication(),
    'enabledPostTypesForDropdown' => get_enabled_post_types_for_dropdown(),
    'statusChoices' => [
      'draft' => esc_html__( 'Draft', 'post-duplicator' ),
      'publish' => esc_html__( 'Published', 'post-duplicator' ),
      'pending' => esc_html__( 'Pending', 'post-duplicator' ),
      'private' => esc_html__( 'Private', 'post-duplicator' ),
    ],
    'mode' => isset( $settings['mode'] ) ? $settings['mode'] : 'advanced',
    'singleAfterDuplicationAction' => isset( $settings['single_after_duplication_action'] ) ? $settings['single_after_duplication_action'] : 'notice',
    'listSingleAfterDuplicationAction' => isset( $settings['list_single_after_duplication_action'] ) ? $settings['list_single_after_duplication_action'] : 'notice',
    'listMultipleAfterDuplicationAction' => isset( $settings['list_multiple_after_duplication_action'] ) ? $settings['list_multiple_after_duplication_action'] : 'notice',
  ] );

  // Enqueue Gutenberg button on block editor pages (but not in widgets editor)
  $screen = get_current_screen();
  if ( $screen && $screen->is_block_editor() ) {
    // Check if we're in widgets editor context (wp-edit-widgets or wp-customize-widgets)
    $is_widgets_editor = ( $screen->id === 'widgets' || $screen->id === 'customize' );
    
    // Skip Gutenberg button in widgets editor - it uses post editor APIs that don't exist there
    if ( $is_widgets_editor ) {
      return;
    }
    
    $gutenberg_asset_file = include( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/gutenbergButton.asset.php' );
    
    // Enqueue WordPress component styles
    wp_enqueue_style( 'wp-components' );
    
    wp_enqueue_style(
      'post-duplicator-gutenberg',
      MTPHR_POST_DUPLICATOR_URL . 'assets/build/gutenbergButton.css',
      ['wp-components', 'wp-edit-post'],
      filemtime( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/gutenbergButton.css' ),
    );
    wp_enqueue_script(
      'post-duplicator-gutenberg',
      MTPHR_POST_DUPLICATOR_URL . 'assets/build/gutenbergButton.js',
      $gutenberg_asset_file['dependencies'],
      filemtime( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/gutenbergButton.js' ),
      true
    );
    $settings = get_option_value();
    $current_user = wp_get_current_user();
    $users_list = get_users_with_edit_caps();
    
    wp_localize_script( 'post-duplicator-gutenberg', 'postDuplicatorVars', [
      'siteUrl'  => site_url(),
      'restUrl'  => esc_url_raw( rest_url( 'post-duplicator/v1/' ) ),
      'nonce'    => wp_create_nonce( 'wp_rest' ),
      'currentUser' => [
        'id' => $current_user->ID,
        'name' => $current_user->display_name,
      ],
      'users' => $users_list,
      'defaultSettings' => [
        'status' => isset( $settings['status'] ) ? $settings['status'] : 'draft',
        'type' => isset( $settings['type'] ) ? $settings['type'] : 'same',
        'post_author' => isset( $settings['post_author'] ) ? $settings['post_author'] : 'current_user',
        'timestamp' => isset( $settings['timestamp'] ) ? $settings['timestamp'] : 'current',
        'title' => isset( $settings['title'] ) ? $settings['title'] : esc_html__( 'Copy', 'post-duplicator' ),
        'slug' => isset( $settings['slug'] ) ? $settings['slug'] : esc_html__( 'copy', 'post-duplicator' ),
        'time_offset' => isset( $settings['time_offset'] ) ? $settings['time_offset'] : false,
        'time_offset_days' => isset( $settings['time_offset_days'] ) ? intval( $settings['time_offset_days'] ) : 0,
        'time_offset_hours' => isset( $settings['time_offset_hours'] ) ? intval( $settings['time_offset_hours'] ) : 0,
        'time_offset_minutes' => isset( $settings['time_offset_minutes'] ) ? intval( $settings['time_offset_minutes'] ) : 0,
        'time_offset_seconds' => isset( $settings['time_offset_seconds'] ) ? intval( $settings['time_offset_seconds'] ) : 0,
        'time_offset_direction' => isset( $settings['time_offset_direction'] ) ? $settings['time_offset_direction'] : 'newer',
      ],
      'postTypes' => array_filter( duplicator_post_types(), function( $key ) {
        return $key !== 'same';
      }, ARRAY_FILTER_USE_KEY ),
      'allPostTypes' => get_all_post_types(),
      'postTypesAuthorSupport' => get_post_types_author_support(),
      'postTypesHierarchicalSupport' => get_post_types_hierarchical_support(),
      'postTypesPublicSupport' => get_post_types_public_support(),
      'enabledPostTypesForDuplication' => get_enabled_post_types_for_duplication(),
      'enabledPostTypesForDropdown' => get_enabled_post_types_for_dropdown(),
      'statusChoices' => [
        'draft' => esc_html__( 'Draft', 'post-duplicator' ),
        'publish' => esc_html__( 'Published', 'post-duplicator' ),
        'pending' => esc_html__( 'Pending', 'post-duplicator' ),
        'private' => esc_html__( 'Private', 'post-duplicator' ),
      ],
      'mode' => isset( $settings['mode'] ) ? $settings['mode'] : 'advanced',
      'singleAfterDuplicationAction' => isset( $settings['single_after_duplication_action'] ) ? $settings['single_after_duplication_action'] : 'notice',
      'listSingleAfterDuplicationAction' => isset( $settings['list_single_after_duplication_action'] ) ? $settings['list_single_after_duplication_action'] : 'notice',
      'listMultipleAfterDuplicationAction' => isset( $settings['list_multiple_after_duplication_action'] ) ? $settings['list_multiple_after_duplication_action'] : 'notice',
    ] );
  }
}
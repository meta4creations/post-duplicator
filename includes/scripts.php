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

  $asset_file = include( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/postDuplicator.asset.php' );
  
  // Enqueue WordPress component styles
  wp_enqueue_style( 'wp-components' );
  
  wp_enqueue_style(
    'post-duplicator',
    MTPHR_POST_DUPLICATOR_URL . 'assets/build/postDuplicator.css',
    ['wp-components'],
    $asset_file['version']
  );
  wp_enqueue_script(
    'post-duplicator',
    MTPHR_POST_DUPLICATOR_URL . 'assets/build/postDuplicator.js',
    $asset_file['dependencies'],
    $asset_file['version'],
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
    'statusChoices' => [
      'draft' => esc_html__( 'Draft', 'post-duplicator' ),
      'publish' => esc_html__( 'Published', 'post-duplicator' ),
      'pending' => esc_html__( 'Pending', 'post-duplicator' ),
      'private' => esc_html__( 'Private', 'post-duplicator' ),
    ],
  ] );

  // Enqueue Gutenberg button on block editor pages
  $screen = get_current_screen();
  if ( $screen && $screen->is_block_editor() ) {
    $gutenberg_asset_file = include( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/gutenbergButton.asset.php' );
    
    // Enqueue WordPress component styles
    wp_enqueue_style( 'wp-components' );
    
    wp_enqueue_style(
      'post-duplicator-gutenberg',
      MTPHR_POST_DUPLICATOR_URL . 'assets/build/gutenbergButton.css',
      ['wp-components', 'wp-edit-post'],
      $gutenberg_asset_file['version']
    );
    wp_enqueue_script(
      'post-duplicator-gutenberg',
      MTPHR_POST_DUPLICATOR_URL . 'assets/build/gutenbergButton.js',
      $gutenberg_asset_file['dependencies'],
      $gutenberg_asset_file['version'],
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
      'statusChoices' => [
        'draft' => esc_html__( 'Draft', 'post-duplicator' ),
        'publish' => esc_html__( 'Published', 'post-duplicator' ),
        'pending' => esc_html__( 'Pending', 'post-duplicator' ),
        'private' => esc_html__( 'Private', 'post-duplicator' ),
      ],
    ] );
  }
}
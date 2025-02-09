<?php
namespace Mtphr\PostDuplicator;

// Update the namespace in the index.php after every update!!!
require_once __DIR__ . '/index.php';

/**
 * Get things started
 * Add a custom function name for your settings to differentiate from other settings
 */
function YOUR_CUSTOM_SETTINGS() {
	return Settings::instance();
}
YOUR_CUSTOM_SETTINGS();

/**
 * Initialize the settings
 */
function init_settings() {

  // Initialize the settings
  YOUR_CUSTOM_SETTINGS()->init( [
    'id' => 'postDuplicator',
    'textdomain' => 'post-duplicator',
    'settings_dir' => MTPHR_POST_DUPLICATOR_DIR . 'mtphr-settings',
    'settings_url' => MTPHR_POST_DUPLICATOR_URL . 'mtphr-settings',
  ] );
  
  // Add an admin page for your settings page
  YOUR_CUSTOM_SETTINGS()->add_admin_page( [
    'page_title' => esc_html__( 'Post Duplicator Settings', 'post-duplicator' ),
    'menu_title' => esc_html__( 'Post Duplicator', 'post-duplicator' ),
    'capability' => 'manage_options',
    'menu_slug'  => 'mtphr_post_duplicator_settings_menu', 
    'parent_slug' => 'tools.php',
    'position' => 25,
  ] );

  // Add setting sections.
  YOUR_CUSTOM_SETTINGS()->add_section( [
    'id' => 'defaults',
    'slug' => 'defaults',
    'label' => __( 'Defaults', 'post-duplicator' ),
    'option' => 'mtphr_post_duplicator_settings',
    'menu_slug' => 'mtphr_post_duplicator_settings_menu',
    'parent_slug' => 'tools.php',
  ] );

  YOUR_CUSTOM_SETTINGS()->add_section( [
    'id' => 'security',
    'slug' => 'security',
    'label' => __( 'Security', 'post-duplicator' ),
    'option' => 'mtphr_post_duplicator_settings',
    'menu_slug' => 'mtphr_post_duplicator_settings_menu',
    'parent_slug' => 'tools.php',
  ] );

  // Add some settings
  YOUR_CUSTOM_SETTINGS()->add_settings( [
    'section' => 'defaults',
    'fields' => [
      [
        'type'    => 'select',
        'id'      => 'status',
        'label'   => __( 'Post Status', 'post-duplicator' ),
        'options' => [
          'same' => esc_html__( 'Same as original', 'post-duplicator' ),
			    'draft' => esc_html__( 'Draft', 'post-duplicator' ),
			    'publish' => esc_html__( 'Published', 'post-duplicator' ),
			    'pending' => esc_html__( 'Pending', 'post-duplicator' )	
        ],
        'default' => 'draft',
      ],
      [
        'type'    => 'select',
        'id'      => 'type',
        'label'   => __( 'Post Type', 'post-duplicator' ),
        'options' => duplicator_post_types(),
        'default' => 'same',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'post_author',
        'label'   => __( 'Post Author', 'post-duplicator' ),
        'options' => [
          'current_user' => esc_html__( 'Current User', 'post-duplicator' ),
			    'original_user' => esc_html__( 'Original Post Author', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'current_user',
        'show' => [
          'id' => 'post_duplication',
          'value' => 'all_users',
          'compare' => '='
        ]
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'timestamp',
        'label'   => __( 'Post Date', 'post-duplicator' ),
        'options' => [
          'duplicate' => esc_html__( 'Duplicate Timestamp', 'post-duplicator' ),
			    'current' => esc_html__( 'Current Time', 'post-duplicator' )
        ],
        'inline' => true,
        'default' => 'current',
      ],
      [
        'type'    => 'text',
        'id'      => 'title',
        'label'   => __( 'Duplicate Title', 'post-duplicator' ),
        'help'    => esc_html__( "String that should be appended to the duplicate post's title", 'post-duplicator' ),
        'default' => esc_html__( 'Copy', 'post-duplicator' ),
      ],
      [
        'type'    => 'text',
        'id'      => 'slug',
        'label'   => __( 'Duplicate Slug', 'post-duplicator' ),
        'help'    => esc_html__( "String that should be appended to the duplicate post's slug", 'post-duplicator' ),
        'default' => esc_html__( 'copy', 'post-duplicator' ),
      ],
      [
        'type'    => 'checkbox',
        'id'      => 'time_offset',
        'label'   => __( 'Offset Date', 'post-duplicator' ),
        'sanitize' => 'boolval',
      ],
      [
        'type'    => 'group',
        'wrap'    => true,
        'fields'  => [
          [
            'type'    => 'number',
            'id'      => 'time_offset_days',
            'label'   => __( 'Days', 'post-duplicator' ),
            'min'     => (int) 0,
            'default' => (int) 0,
            'sanitize' => 'intval',
          ],
          [
            'type'    => 'number',
            'id'      => 'time_offset_hours',
            'label'   => __( 'Hours', 'post-duplicator' ),
            'min'     => (int) 0,
            'default' => (int) 0,
            'sanitize' => 'intval',
          ],
          [
            'type'    => 'number',
            'id'      => 'time_offset_minutes',
            'label'   => __( 'Minutes', 'post-duplicator' ),
            'min'     => (int) 0,
            'default' => (int) 0,
            'sanitize' => 'intval',
          ],
          [
            'type'    => 'number',
            'id'      => 'time_offset_seconds',
            'label'   => __( 'Seconds', 'post-duplicator' ),
            'min'     => (int) 0,
            'default' => (int) 0,
            'sanitize' => 'intval',
          ],
        ],
        'show'    => [
          'id' => 'time_offset',
          'value' => true,
          'compare' => '='
        ],
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'time_offset_direction',
        'label'   => __( 'Offset direction', 'post-duplicator' ),
        'options' => [
          'newer' => esc_html__( 'Newer', 'post-duplicator' ),
          'older' => esc_html__( 'Older', 'post-duplicator' )
        ],
        'default' => 'newer',
        'inline' => true,
        'show'    => [
          'id' => 'time_offset',
          'value' => true,
          'compare' => '='
        ],
      ],
    ],
  ] );

  YOUR_CUSTOM_SETTINGS()->add_settings( [
    'section' => 'security',
    'fields' => [
      [
        'type'    => 'radio_buttons',
        'id'      => 'post_duplication',
        'label'   => __( 'Post Duplication', 'post-duplicator' ),
        'options' => [
          'current_user' => esc_html__( 'Limit to Current User', 'post-duplicator' ),
          'all_users' => esc_html__( 'Allow Duplication of All Users', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'current_user',
      ],
    ],
  ] );
}
add_action( 'init', __NAMESPACE__ . '\init_settings' );
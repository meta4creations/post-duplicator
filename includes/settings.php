<?php
namespace Mtphr\PostDuplicator;

// Update the namespace in the index.php after every update!!!
require_once __DIR__ . '/mtphr-settings/index.php';

/**
 * Get things started
 * Add a custom function name for your settings to differentiate from other settings
 */
function MTPHR_POSTDUPLICATOR_SETTINGS() {
	return Settings::instance();
}
MTPHR_POSTDUPLICATOR_SETTINGS();

/**
 * Initialize the settings
 */
function init_settings() {

  //delete_option( 'disabled' );

  // Initialize the settings
  MTPHR_POSTDUPLICATOR_SETTINGS()->init( [
    'id' => 'postDuplicator',
    'textdomain' => 'post-duplicator',
    'settings_dir' => MTPHR_POST_DUPLICATOR_DIR . 'includes/mtphr-settings',
    'settings_url' => MTPHR_POST_DUPLICATOR_URL . 'includes/mtphr-settings',
  ] );
  
  // Add an admin page for your settings page
  MTPHR_POSTDUPLICATOR_SETTINGS()->add_admin_page( [
    'page_title' => esc_html__( 'Post Duplicator Settings', 'post-duplicator' ),
    'menu_title' => esc_html__( 'Post Duplicator', 'post-duplicator' ),
    'capability' => 'manage_options',
    'menu_slug'  => 'mtphr_post_duplicator_settings_menu', 
    'parent_slug' => 'tools.php',
    'position' => 25,
  ] );

  // Add setting sections.
  MTPHR_POSTDUPLICATOR_SETTINGS()->add_section( [
    'id' => 'defaults',
    'slug' => 'defaults',
    'label' => __( 'Defaults', 'post-duplicator' ),
    'option' => 'mtphr_post_duplicator_settings',
    'menu_slug' => 'mtphr_post_duplicator_settings_menu',
    'parent_slug' => 'tools.php',
  ] );

  MTPHR_POSTDUPLICATOR_SETTINGS()->add_section( [
    'id' => 'permissions',
    'slug' => 'permissions',
    'label' => __( 'Permissions', 'post-duplicator' ),
    'option' => 'disabled',
    'menu_slug' => 'mtphr_post_duplicator_settings_menu',
    'parent_slug' => 'tools.php',
  ] );

  MTPHR_POSTDUPLICATOR_SETTINGS()->add_section( [
    'id' => 'advanced',
    'slug' => 'advanced',
    'label' => __( 'Advanced', 'post-duplicator' ),
    'option' => 'mtphr_post_duplicator_settings',
    'menu_slug' => 'mtphr_post_duplicator_settings_menu',
    'parent_slug' => 'tools.php',
  ] );

  // Add some settings
  MTPHR_POSTDUPLICATOR_SETTINGS()->add_settings( [
    'section' => 'defaults',
    'fields' => [
      [
        'type'    => 'heading',
        'label'   => __( 'Defaults', 'post-duplicator' ),
      ],
      [
        'type'    => 'select',
        'id'      => 'status',
        'label'   => __( 'Post Status', 'post-duplicator' ),
        'choices' => [
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
        'choices' => duplicator_post_types(),
        'default' => 'same',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'post_author',
        'label'   => __( 'Post Author', 'post-duplicator' ),
        'choices' => [
          'current_user' => esc_html__( 'Current User', 'post-duplicator' ),
			    'original_user' => esc_html__( 'Original Post Author', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'current_user',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'timestamp',
        'label'   => __( 'Post Date', 'post-duplicator' ),
        'choices' => [
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
        'choices' => [
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

  MTPHR_POSTDUPLICATOR_SETTINGS()->add_settings( [
    'section' => 'advanced',
    'fields' => [
      [
        'type'    => 'heading',
        'label'   => __( 'Advanced Duplication Settings', 'post-duplicator' ),
        'help'    => __( "Advanced settings related to duplication of other user posts.", 'post-duplicator' ),
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'duplicate_other_draft',
        'label'   => __( 'Draft Posts', 'post-duplicator' ),
        'help'    => __( "Should users be able to duplicate other's draft posts?", 'post-duplicator' ),
        'choices' => [
          'disabled' => esc_html__( 'Disabled', 'post-duplicator' ),
          'enabled' => esc_html__( 'Enabled', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'enabled',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'duplicate_other_pending',
        'label'   => __( 'Pending Posts', 'post-duplicator' ),
        'help'    => __( "Should users be able to duplicate other's pending posts?", 'post-duplicator' ),
        'choices' => [
          'disabled' => esc_html__( 'Disabled', 'post-duplicator' ),
          'enabled' => esc_html__( 'Enabled', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'enabled',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'duplicate_other_private',
        'label'   => __( 'Private Posts', 'post-duplicator' ),
        'help'    => __( "Should users be able to duplicate other's private posts?", 'post-duplicator' ),
        'choices' => [
          'disabled' => esc_html__( 'Disabled', 'post-duplicator' ),
          'enabled' => esc_html__( 'Enabled', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'enabled',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'duplicate_other_password',
        'label'   => __( 'Password Protected Posts', 'post-duplicator' ),
        'help'    => __( "Should users be able to duplicate other's password protected posts?", 'post-duplicator' ),
        'choices' => [
          'disabled' => esc_html__( 'Disabled', 'post-duplicator' ),
          'enabled' => esc_html__( 'Enabled', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'enabled',
      ],
      [
        'type'    => 'radio_buttons',
        'id'      => 'duplicate_other_future',
        'label'   => __( 'Future Posts', 'post-duplicator' ),
        'help'    => __( "Should users be able to duplicate other's future posts?", 'post-duplicator' ),
        'choices' => [
          'disabled' => esc_html__( 'Disabled', 'post-duplicator' ),
          'enabled' => esc_html__( 'Enabled', 'post-duplicator' ),
        ],
        'inline' => true,
        'default' => 'enabled',
      ],
    ],
  ] );

  MTPHR_POSTDUPLICATOR_SETTINGS()->add_settings( [
    'section' => 'permissions',
    'fields'  => [
      [
        'id' 		    => 'permissions',
        'label'     => esc_html__( 'Permissions', 'post-duplicator' ),
        'type'      => 'group',
        'direction' => 'column',
        'fields'    => user_roles_and_capabilities(),
      ]
    ],
  ] );

  $get_values = MTPHR_POSTDUPLICATOR_SETTINGS()->get_default_values();
  //echo '<pre>';print_r($get_values);echo '</pre>';
}
add_action( 'init', __NAMESPACE__ . '\init_settings' );

/**
 * Return settings
 */
function get_option_value( $setting = false, $option = 'mtphr_post_duplicator_settings' ) {
  
  if ( $values = MTPHR_POSTDUPLICATOR_SETTINGS()->get_option_values( $option ) ) {
    if ( $setting ) {
      if ( isset( $values[$setting] ) ) {
        return $values[$setting];
      }
    } else {
      return $values;
    }
  }
}

/**
 * Return user roles and capabilities
 */
function user_roles_and_capabilities() {
  $wp_roles_instance = wp_roles();
  $all_roles = $wp_roles_instance->roles;
  $fields = [];

  $capabilities = get_capabilities();
  $active_capabilities = get_active_capabilities();

  foreach ($all_roles as $role_key => $role) {
    $role_capabilities = $capabilities;
    $role_group = [
      'id' 		    => $role_key,
      'label'     => sprintf( esc_html__( '%s Permissions', 'post-duplicator' ), $role['name'] ),
      'type'      => 'checkboxes',
      'inline'    => false,
      'choices'   => $role_capabilities,
      'default'   => $active_capabilities[$role_key],
      'noupdate'  => true,
      'sanitize'  => __NAMESPACE__ . '\update_capabilities',
    ];
    $fields[] = $role_group;
  }

  return $fields;
}

/**
 * Get capabilities
 */
function get_capabilities() {
  $capabilities = [
    'duplicate_posts',
    'duplicate_others_posts',
  ];
  return array_combine( $capabilities, $capabilities );
}

/**
 * Get active capabilities
 */
function get_active_capabilities( $role = false) {
  $duplicator_capabilities = get_capabilities();
  $active_capabilities = [];
  if ( $role ) {
    foreach ( $role->capabilities as $capability => $enabled ) {
      if ( $enabled && in_array( $capability, $duplicator_capabilities ) ) {
        $active_capabilities[] = $capability;
      }
    }
  } else {
    $wp_roles_instance = wp_roles();
    $all_roles = $wp_roles_instance->roles;
    foreach ($all_roles as $role_key => $role) {
      $capabilities = [];
      foreach ( $role['capabilities'] as $capability => $enabled ) {
        if ( $enabled && in_array( $capability, $duplicator_capabilities ) ) {
          $capabilities[] = $capability;
        }
      }
      $active_capabilities[$role_key] = $capabilities;
    }
  }
  return $active_capabilities;
}

/**
 * Update a role capability
 */
function update_capabilities( $value, $key, $option, $type ) {
  if ( 'update' == $type ) {
    $role = get_role( $key );
    $active_capabilities = get_active_capabilities( $role );
    $capability_value = is_array( $value ) ? $value : [];;

    $added_capabilities = array_diff( $capability_value, $active_capabilities );
    $removed_capabilities = array_diff( $active_capabilities, $capability_value );
    if ( is_array( $added_capabilities ) && count( $added_capabilities ) > 0 ) {
      foreach ( $added_capabilities as $added_capability ) {
        $role->add_cap( esc_attr( $added_capability ) );
      }
    }
    if ( is_array( $removed_capabilities ) && count( $removed_capabilities ) > 0 ) {
      foreach ( $removed_capabilities as $removed_capability ) {
        $role->remove_cap( esc_attr( $removed_capability ) );
      }
    }
  }
  return $value;
}
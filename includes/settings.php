<?php
namespace Mtphr\PostDuplicator;

// Update the namespace in the index.php after every update!!!
require_once __DIR__ . '/mtphr-settings/index.php';

add_action( 'init', __NAMESPACE__ . '\init_fields' );

/**
 * Get things started
 * Add a custom function name for your settings to differentiate from other settings
 */
function SETTINGS() {
	return Settings::instance();
}
SETTINGS();

// Initialize the settings
SETTINGS()->init( [
  'id' => 'postDuplicator',
  'textdomain' => 'post-duplicator',
  'settings_dir' => MTPHR_POST_DUPLICATOR_DIR . 'includes/mtphr-settings',
  'settings_url' => MTPHR_POST_DUPLICATOR_URL . 'includes/mtphr-settings',
] );

// Add an admin page for your settings page
SETTINGS()->add_admin_page( [
  'page_title' => esc_html__( 'Post Duplicator Settings', 'post-duplicator' ),
  'menu_title' => esc_html__( 'Post Duplicator', 'post-duplicator' ),
  'capability' => 'manage_options',
  'menu_slug'  => 'mtphr_post_duplicator', 
  'parent_slug' => 'options-general.php',
  'position' => 25,
] );

// Add setting sections.
SETTINGS()->add_section( [
  'id' => 'defaults',
  'slug' => 'defaults',
  'label' => __( 'Defaults', 'post-duplicator' ),
  'option' => 'mtphr_post_duplicator_settings',
  'menu_slug' => 'mtphr_post_duplicator',
  'parent_slug' => 'options-general.php',
] );

SETTINGS()->add_section( [
  'id' => 'permissions',
  'slug' => 'permissions',
  'label' => __( 'Permissions', 'post-duplicator' ),
  'option' => 'disabled',
  'menu_slug' => 'mtphr_post_duplicator',
  'parent_slug' => 'options-general.php',
] );

SETTINGS()->add_section( [
  'id' => 'advanced',
  'slug' => 'advanced',
  'label' => __( 'Advanced', 'post-duplicator' ),
  'option' => 'mtphr_post_duplicator_settings',
  'menu_slug' => 'mtphr_post_duplicator',
  'parent_slug' => 'options-general.php',
] );

// Add default values
SETTINGS()->add_default_values( 'mtphr_post_duplicator_settings', [
  'status' => 'draft',
  'type' => 'same',
  'post_author' => 'current_user',
  'timestamp' => 'current',
  'title' => esc_html__( 'Copy', 'post-duplicator' ),
  'slug' => esc_html__( 'copy', 'post-duplicator' ),
  'time_offset_days' => (int) 0,
  'time_offset_hours' => (int) 0,
  'time_offset_minutes' => (int) 0,
  'time_offset_seconds' => (int) 0,
  'time_offset_direction' => 'newer',
  'duplicate_other_draft' => 'enabled',
  'duplicate_other_pending' => 'enabled',
  'duplicate_other_private' => 'enabled',
  'duplicate_other_password' => 'enabled',
  'duplicate_other_future' => 'enabled',
] );
SETTINGS()->add_default_values( 'disabled',
  user_roles_and_capabilities( 'defaults' )
);

// Add sanitize settings
SETTINGS()->add_sanitize_settings( 'mtphr_post_duplicator_settings', [
  'time_offset' => 'boolval',
  'time_offset_days' => 'intval',
  'time_offset_hours' => 'intval',
  'time_offset_minutes' => 'intval',
  'time_offset_seconds' => 'intval',
] );
SETTINGS()->add_sanitize_settings( 'disabled',
  user_roles_and_capabilities( 'sanitizers' )
);

/**
 * Initialize the settings
 */
function init_fields() {

  // Add some settings
  SETTINGS()->add_fields( [
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
      ],
      [
        'type'    => 'select',
        'id'      => 'type',
        'label'   => __( 'Post Type', 'post-duplicator' ),
        'choices' => duplicator_post_types(),
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
      ],
      [
        'type'    => 'text',
        'id'      => 'title',
        'label'   => __( 'Duplicate Title', 'post-duplicator' ),
        'help'    => esc_html__( "String that should be appended to the duplicate post's title", 'post-duplicator' ),
      ],
      [
        'type'    => 'text',
        'id'      => 'slug',
        'label'   => __( 'Duplicate Slug', 'post-duplicator' ),
        'help'    => esc_html__( "String that should be appended to the duplicate post's slug", 'post-duplicator' ),
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
          ],
          [
            'type'    => 'number',
            'id'      => 'time_offset_hours',
            'label'   => __( 'Hours', 'post-duplicator' ),
            'min'     => (int) 0,
          ],
          [
            'type'    => 'number',
            'id'      => 'time_offset_minutes',
            'label'   => __( 'Minutes', 'post-duplicator' ),
            'min'     => (int) 0,
          ],
          [
            'type'    => 'number',
            'id'      => 'time_offset_seconds',
            'label'   => __( 'Seconds', 'post-duplicator' ),
            'min'     => (int) 0,
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
        'inline' => true,
        'show'    => [
          'id' => 'time_offset',
          'value' => true,
          'compare' => '='
        ],
      ],
    ],
  ] );

  SETTINGS()->add_fields( [
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
      ],
    ],
  ] );

  SETTINGS()->add_fields( [
    'section' => 'permissions',
    'fields'  => [
      [
        'label'     => esc_html__( 'Permissions', 'post-duplicator' ),
        'type'      => 'group',
        'direction' => 'column',
        'fields'    => user_roles_and_capabilities(),
      ]
    ],
  ] );

  $values = SETTINGS()->get_values();
}

/**
 * Return settings
 */
function get_option_value( $setting = false, $option = 'mtphr_post_duplicator_settings' ) {
  
  if ( $values = SETTINGS()->get_option_values( $option ) ) {
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
function user_roles_and_capabilities( $type = '' ) {
  $wp_roles_instance = wp_roles();
  $all_roles = $wp_roles_instance->roles;
  $fields = [];
  $defaults = [];
  $sanitize = [];

  $capabilities = get_capabilities();
  $active_capabilities = get_active_capabilities();

  foreach ($all_roles as $role_key => $role) {
    $role_capabilities = $capabilities;
    $checkboxes = [
      'id' 		    => $role_key,
      'label'     => sprintf( esc_html__( '%s Permissions', 'post-duplicator' ), $role['name'] ),
      'type'      => 'checkboxes',
      'inline'    => false,
      'choices'   => $role_capabilities,
      'noupdate'  => true,
    ];
    $fields[] = $checkboxes;
    $defaults[$role_key] = $active_capabilities[$role_key];
    $sanitize[$role_key] = __NAMESPACE__ . '\update_capabilities';
  }

  if ( 'defaults' == $type ) {
    return $defaults;
  } elseif( 'sanitizers' == $type ) {
    return $sanitize;
  } else {
    return $fields;
  }
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
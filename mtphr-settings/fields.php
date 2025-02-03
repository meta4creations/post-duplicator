<?php
namespace Mtphr\Settings;

use function Mtphr\EmailCustomizer\get_integrations;
use function Mtphr\EmailCustomizer\is_integration_active;

add_action( 'admin_menu', __NAMESPACE__ . '\admin_pages' );
add_action( 'admin_menu', __NAMESPACE__ . '\additional_pages' );

/**
 * Add setting pages
 */
function admin_pages() {

  // Add a submenu page under the custom post type "mtphr_email_template" called "Settings"
  add_submenu_page(
      'edit.php?post_type=mtphr_email_template',  // Parent slug for the custom post type
      __('Settings', 'mtphr-emailcustomizer'),  // Page title
      __('Settings', 'mtphr-emailcustomizer'),  // Menu title
      'manage_options',                         // Capability required
      'settings',                               // Menu slug
      __NAMESPACE__ . '\render_settings' // Callback function to render the page
  );
}

/**
 * Add additional pages
 */
function additional_pages() {
  global $submenu;

  $active_integrations = get_integrations( 'active' );
  $sections = setting_field_sections();

  $filtered_sections = [];
  if ( is_array( $sections ) && count( $sections ) > 0 ) {
    foreach ( $sections as $i => $section ) {
      if ( ! isset( $section['is_integration'] ) ) {
        $filtered_sections[] = $section;
        continue;
      }
      if ( ! is_integration_active( $section['id'] ) ) {
        continue;
      }
      if ( ! isset( $active_integrations[$section['id']] ) ) {
        continue;
      }
      $filtered_sections[] = $section;
    }
  }

  if ( is_array( $filtered_sections ) && count( $filtered_sections ) > 0 ) {

    // Sort sections by 'order' key, defaulting to 10 if 'order' not set
    usort( $filtered_sections, function( $a, $b ) {
      $order_a = isset( $a['order'] ) ? $a['order'] : 10;
      $order_b = isset( $b['order'] ) ? $b['order'] : 10;
      return $order_a - $order_b;
    });

    $post_type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : false;
    $page = isset( $_GET['page'] ) ? $_GET['page'] : false;
    $sect = isset( $_GET['section'] ) ? $_GET['section'] : false;

    foreach ( $filtered_sections  as $section ) {
      if ( ! isset( $section['id'] ) || 'general' == $section['id'] ) {
        continue;
      }
      if ( ! isset( $section['show_in_menu'] ) || ! $section['show_in_menu'] ) {
        continue;
      }

      $class = '';
      if ( 'mtphr_email_template' == $post_type && 'settings' == $page ) {
        if ( $section['id'] == $sect ) {
          $class = 'active';
        }
      }

      $label = isset( $section['label'] ) ? $section['label'] : $section['id'];
      $permalink = admin_url( "edit.php?post_type=mtphr_email_template&page=settings&section={$section['id']}" );
      $submenu['edit.php?post_type=mtphr_email_template'][] = ["- {$label}", 'manage_options', $permalink, $class];
    }
  }
}

/**
 * Callback function to display content on the Settings admin page
 */
function render_settings() {
  echo '<div class="wrap">';
    echo '<div id="mtphr-settings-app" namespace="mtphrEmailCustomizer"></div>'; // React App will be injected here
  echo '</div>';
}

/**
 * Return the setting field sections
 */
function setting_field_sections() {
  $sections = [
    [
      'id' => 'general',
      'label' => __( 'General', 'mtphr-emailcustomizer' ),
      'order' => 1,
    ],
    [
      'id' => 'advanced',
      'label' => __( 'Advanced', 'mtphr-emailcustomizer' ),
      'order' => 99
    ],
  ];
  return apply_filters( 'mtphrSettings/setting_field_sections', $sections );
}

/**
 * Return the setting fields
 */
function setting_fields() {
  $template_options = [[
    'label' => __( 'Select a Template', 'mtphr-emailcustomizer' ),
    'value' => '',
  ]];
  $templates = get_posts( [
    'numberposts' => -1,
    'post_type' => 'mtphr_email_template',
    'orderby' => 'title',
    'order' => 'ASC',
  ] );
  if ( is_array( $templates ) && count( $templates ) > 0 ) {
    foreach ( $templates as $template ) {
      $template_options[] = [
        'label' => "({$template->ID}) {$template->post_title}",
        'value' => $template->ID,
      ];
    }
  }

  $fields = [
    [
      'type'    => 'heading',
      'label'   => esc_html__( 'General Settings', 'mtphr-emailcustomizer' ),
      'section' => 'general',
    ],
    [
      'type'    => 'color',
      'id'      => 'colors',
      'label'   => __( 'Custom Colors', 'mtphr-emailcustomizer' ),
      'help'    => __( 'Add custom colors to use in your email templates.', 'mtphr-emailcustomizer' ),
      'min'     => 5,
      'max'     => 10,
      'section' => 'general',
    ],
    [
      'type'    => 'textarea',
      'id'      => 'mapped_fields',
      'rows'    => 8,
      'label'   => __( 'Mapped Fields', 'mtphr-emailcustomizer' ),
      'help'    => __( 'Set up custom placeholders to use in your email templates.', 'mtphr-emailcustomizer' ),
      'section' => 'general',
    ],
    [
      'type'    => 'heading',
      'label'   => esc_html__( 'Advanced', 'mtphr-emailcustomizer' ),
      'section' => 'advanced',
    ],
    [
      'type'            => 'edd_license',
      'id'              => 'edd_license',
      'label'           => __( 'License', 'mtphr-emailcustomizer' ),
      'help'            => __( 'Add your license for Email Customizer.', 'mtphr-emailcustomizer' ),
      'license_data'    => get_settings( 'edd_license_data' ),
      'activate_url'    => esc_url_raw( rest_url( 'mtphrSettings/v1/license_activate' ) ),
      'deactivate_url'  => esc_url_raw( rest_url( 'mtphrSettings/v1/license_deactivate' ) ),
      'refresh_url'     => esc_url_raw( rest_url( 'mtphrSettings/v1/license_refresh' ) ),
      'section'         => 'advanced',
    ],
    [
      'type'    => 'checkboxes',
      'id'      => 'integrations',
      'label'   => __( 'Integrations', 'mtphr-emailcustomizer' ),
      'help'    => __( 'Enable integrations to use with your email templates.', 'mtphr-emailcustomizer' ),
      'section' => 'advanced',
      'choices' => get_integrations(),
    ],
  ];
  
  if ( is_plugin_active( 'mtphr-gravity-emailcustomizer/mtphr-gravity-emailcustomizer.php' ) ) {
    $fields[] = [
      'type'      => 'buttons',
      'id'        => 'migrate_buttons',
      'label'     => __( 'Migrate Gravity Forms Email Customizer Data', 'mtphr-emailcustomizer' ),
      'direction' => 'horizontal',
      'justify'   => 'flex-start',
      'help' => __( 'Duplicate and convert existing Gravity Forms Email Customizer templates to the Email Customizer post type and blocks and update. Update Gravity Forms notification settings to the new posts.', 'mtphr-emailcustomizer' ),
      'section' => 'advanced',
      'buttons'    => [
        [
          'id'      => 'migrate_templates',
          'text'    => __( 'Migrate Templates', 'mtphr-emailcustomizer' ),
          'icon' => 'update',
          'iconPosition' => 'right',
          'section' => 'advanced',
          'action'  => [
            'type'      => 'api',
            'url'       => esc_url_raw( rest_url( 'mtphrSettings/v1/migrate_templates' ) ),
            'response'  => true,
            'confirm'   => __( 'This action will clone and modify Gravity Forms Email Customizer template posts and create new Email Customizer template posts. Are you sure you want to proceed?', 'mtphr-emailcustomizer' ),
          ],
        ],
        [
          'id'      => 'update_notifications',
          'text'    => __( 'Update Notifications', 'mtphr-emailcustomizer' ),
          'icon' => 'update',
          'iconPosition' => 'right',
          'section' => 'advanced',
          'action'  => [
            'type'      => 'api',
            'url'       => esc_url_raw( rest_url( 'mtphrSettings/v1/update_notifications' ) ),
            'response'  => true,
            'confirm'   => __( 'This action will attempt to update Gravity Forms notifications with updated post IDs. Make sure you have Migrated your templates first! Are you sure you want to proceed?', 'mtphr-emailcustomizer' ),
          ],
        ],
      ],
    ];
  }

  return apply_filters( 'mtphrSettings/setting_fields', $fields, $template_options );
}
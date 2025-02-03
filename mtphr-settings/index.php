<?php
namespace Mtphr\PostDuplicator;

require_once __DIR__ . '/settings-class.php';
// require_once __DIR__ . '/api.php';
// require_once __DIR__ . '/fields.php';
// require_once __DIR__ . '/scripts.php';

add_action( 'init', __NAMESPACE__ . '\init_settings' );


function init_settings() {

  // Initialize the settings
  MTPHR_POST_DUPLICATOR_SETTINGS()->init( [
    'id' => 'postDuplicator',
    'textdomain' => 'post-duplicator',
    'settings_dir' => MTPHR_POST_DUPLICATOR_DIR . 'mtphr-settings',
    'settings_url' => MTPHR_POST_DUPLICATOR_URL . 'mtphr-settings',
  ] );

  MTPHR_POST_DUPLICATOR_SETTINGS()->add_admin_page( [
    'page_title' => __('Super Pooper', 'post-duplicator'),
    'menu_title' => __('Super Pooper', 'post-duplicator'),
    'capability' => 'manage_options',
    'menu_slug'  => 'post_duplicator_settings', 
    'icon' => 'dashicons-chart-pie',
    'position' => 25,
  ] );

  MTPHR_POST_DUPLICATOR_SETTINGS()->add_admin_page( [
    'page_title' => __('Puper Sooper', 'post-duplicator'),
    'menu_title' => __('Puper Sooper', 'post-duplicator'),
    'capability' => 'manage_options',
    'menu_slug'  => 'post_duplicator_settings_x', 
    'icon' => 'dashicons-chart-pie',
    'position' => 25,
  ] );

  MTPHR_POST_DUPLICATOR_SETTINGS()->add_section( [
    'id' => 'general_1',
    'slug' => 'general',
    'label' => __( 'General', 'post-duplicator' ),
    'option' => 'mtphr_post_duplicator_settings',
    'menu_slug' => 'post_duplicator_settings',
  ] );

  MTPHR_POST_DUPLICATOR_SETTINGS()->add_section( [
    'id' => 'general_2',
    'slug' => 'general',
    'label' => __( 'Super General', 'post-duplicator' ),
    'option' => 'mtphr_post_duplicator_settings',
    'menu_slug' => 'post_duplicator_settings_x',
  ] );

  // Add some settings
  MTPHR_POST_DUPLICATOR_SETTINGS()->add_settings( [
    'section' => 'general_1',
    'fields' => [
      'testing' => [
        'type'    => 'text',
        'id'      => 'testing',
        'label'   => __( 'Testing', 'post-duplicator' ),
        'default' => '',
      ],
    ],
  ] );

  // Add some settings
  MTPHR_POST_DUPLICATOR_SETTINGS()->add_settings( [
    'section' => 'general_2',
    'fields' => [
      'testing' => [
        'type'    => 'text',
        'id'      => 'testing',
        'label'   => __( 'Testing Boogs', 'post-duplicator' ),
        'default' => '',
      ],
    ],
  ] );

  // $settings = MTPHR_POST_DUPLICATOR_SETTINGS()->get_settings();

  // echo '<pre>';print_r($settings);echo '</pre>';
}





<?php
namespace Mtphr\Settings;

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Enqueue general admin scripts
 */
function enqueue_scripts( $hook_suffix ) {

  $current_screen = get_current_screen();

  // Load react settings pages
  if ( 'mtphr_email_template' == $current_screen->post_type && isset( $_GET['page'] ) ) {

    $settings_asset_file = include( MTPHR_EMAILCUSTOMIZER_DIR . 'assets/build/mtphrEmailCustomizerSettings.asset.php' );
    wp_enqueue_style(
      'mtphr-emailcustomizer-settings',
      MTPHR_EMAILCUSTOMIZER_URL . 'assets/build/mtphrEmailCustomizerSettings.css',
      ['wp-components'],
      $settings_asset_file['version']
    );
    wp_enqueue_script(
      'mtphr-emailcustomizer-settings',
      MTPHR_EMAILCUSTOMIZER_URL . 'assets/build/mtphrEmailCustomizerSettings.js',
      array_unique( array_merge( $settings_asset_file['dependencies'], ['wp-element'] ) ),
      $settings_asset_file['version'],
      true
    ); 
    wp_localize_script( 'mtphr-emailcustomizer-settings', 'mtphrEmailCustomizerSettings', array(
      'siteUrl'        => site_url(),
      'restUrl'        => esc_url_raw( rest_url( 'mtphrSettings/v1/' ) ),
      'settings'       => get_settings(),
      'fields'         => setting_fields(),
      'field_sections' => setting_field_sections(),
      'nonce'          => wp_create_nonce( 'wp_rest' )
    ));

    $custom_asset_file = include( MTPHR_EMAILCUSTOMIZER_DIR . 'assets/build/mtphrEmailCustomizerCustomFields.asset.php' );
    wp_enqueue_script(
      'mtphr-emailcustomizer-custom-fields',
      MTPHR_EMAILCUSTOMIZER_URL . 'assets/build/mtphrEmailCustomizerCustomFields.js',
      array_unique( array_merge( $custom_asset_file['dependencies'], ['mtphr-emailcustomizer-settings'] ) ),
      $custom_asset_file['version'],
      true
    );
  } 
}
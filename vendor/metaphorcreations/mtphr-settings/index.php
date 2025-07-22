<?php
use Mtphr\Settings;

if ( ! function_exists( 'MTPHR_SETTINGS' ) ) {
  require_once __DIR__ . '/settings-class.php';

  add_action( 'rest_api_init', 'mtphr_settings_initialize_settings', 1 );
  add_action( 'init', 'mtphr_settings_initialize_settings', 1 );
  add_action( 'init', 'mtphr_settings_initialize_fields' );

  /**
   * Get things started
   * Add a custom function name for your settings to differentiate from other settings
   */
  function MTPHR_SETTINGS() {
    return Settings::instance();
  }
  MTPHR_SETTINGS();

  /**
   * Let other scripts know the settings are ready
   */
  function mtphr_settings_initialize_settings() {
    if ( ! MTPHR_SETTINGS()->get_settings_ready() ) { 
      do_action( 'mtphrSettings/init_settings' );
      MTPHR_SETTINGS()->set_settings_ready( true );
    }
  }

  /**
   * Let other scripts know they can initialize fields
   */
  function mtphr_settings_initialize_fields() {
    if ( ! MTPHR_SETTINGS()->get_fields_ready() ) { 
      do_action( 'mtphrSettings/init_fields' );
      MTPHR_SETTINGS()->set_fields_ready( true );
    }
  }

  /**
   * Let other scripts know they can initialize fields
   */
  function mtphr_settings_ready() {
    return MTPHR_SETTINGS()->get_settings_ready();
  }

  /**
   * Add an admin page
   */
  function mtphr_settings_add_admin_page( $data ) {
    MTPHR_SETTINGS()->add_admin_page( $data );
  }

  /**
   * Add a section
   */
  function mtphr_settings_add_section( $data ) {
    MTPHR_SETTINGS()->add_section( $data );
  }

  /**
   * Add default values
   */
  function mtphr_settings_add_default_values( $option, $data ) {
    MTPHR_SETTINGS()->add_default_values( $option, $data );
  }

  /**
   * Add sanitize settings
   */
  function mtphr_settings_add_sanitize_settings( $option, $data ) {
    MTPHR_SETTINGS()->add_sanitize_settings( $option, $data );
  }

  /**
   * Add encryption settings
   */
  function mtphr_settings_add_encryption_settings( $option, $data ) {
    MTPHR_SETTINGS()->add_encryption_settings( $option, $data );
  }

  /**
   * Add fields
   */
  function mtphr_settings_add_fields( $data ) {
    MTPHR_SETTINGS()->add_fields( $data );
  }

  /**
   * Get an option value
   */
  function mtphr_settings_get_option_value( $option, $key = false ) {
    if ( $values = MTPHR_SETTINGS()->get_option_values( $option ) ) {
      if ( $key ) {
        if ( isset( $values[$key] ) ) {
          return $values[$key];
        }
      } else {
        return $values;
      }
    }
  }

  /**
   * Set an option value
   */
  function mtphr_settings_set_option_value( $option, $key, $value = false ) {
    if ( is_array( $key ) ) {
      $updated_values = $key;
    } else {
      $updated_values = [
        $key => $value,
      ];
    };

    $values = MTPHR_SETTINGS()->update_values( $option, $updated_values );
    return $values;
  }
}
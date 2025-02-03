<?php
namespace Mtphr\Settings;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_routes' );

/**
 * Add rest routes
 *
 * @access public
 * @since  1.0.0
 */
function register_routes() {
  
  register_rest_route( "mtphrSettings/v1", 'settings', array(
    'methods' 	=> 'GET',
    'callback' 	=> __NAMESPACE__ . '\api_get_settings',
    'permission_callback' => function () {
      return current_user_can('manage_options');
    }
  ) );

  register_rest_route( "mtphrSettings/v1", 'settings', array(
    'methods' 	=> 'POST',
    'callback' 	=> __NAMESPACE__ . '\api_save_settings',
    'permission_callback' => function () {
      return current_user_can('manage_options');
    }
  ) );
}

/**
 * Return the settings
 *
 * @access public
 * @since  1.0.0
 */
function api_get_settings( $request ) {
  $args = $request->get_params();
  $data = [
    'settings' => get_settings(),
    'fields' => setting_fields(),
  ];
  return rest_ensure_response( $data );
}

/**
 * Save the settings
 *
 * @access public
 * @since  1.0.0
 */
function api_save_settings( $request ) {
  $args = $request->get_params();
  $settings = $request->get_json_params();
  update_settings( $settings );
  return rest_ensure_response( $settings , 200);
}
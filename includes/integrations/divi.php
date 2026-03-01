<?php
/**
 * Integration with Divi Theme
 *
 * Divi conditionally loads its builder framework and `et_pb_layout` post type
 * on selected admin screens. This can cause `et_pb_layout` to be unavailable
 * on the Post Duplicator settings page, which prevents it from appearing in the
 * Post Types configuration.
 *
 * @package Mtphr\PostDuplicator\Integrations
 */

namespace Mtphr\PostDuplicator\Integrations;

add_filter( 'et_builder_should_load_framework', __NAMESPACE__ . '\maybe_load_divi_framework_on_post_duplicator_settings', 10, 1 );
add_filter( 'mtphr_post_duplicator_excluded_post_types', __NAMESPACE__ . '\exclude_divi_post_types' );
add_filter( 'mtphr_post_duplicator_should_enqueue_list_scripts', __NAMESPACE__ . '\maybe_enqueue_on_divi_library_screen' );
add_filter( 'mtphr_post_duplicator_mode', __NAMESPACE__ . '\force_basic_mode_on_divi_screens' );
add_filter( 'mtphr_post_duplicator_list_single_after_duplication_action', __NAMESPACE__ . '\force_refresh_on_divi_screens' );
add_filter( 'mtphr_post_duplicator_list_multiple_after_duplication_action', __NAMESPACE__ . '\force_refresh_on_divi_screens' );
add_filter( 'mtphr_post_duplicator_general_notices', __NAMESPACE__ . '\add_divi_settings_notice' );

/**
 * Detect whether Divi (parent or child) is active.
 *
 * @return bool
 */
function is_divi_active() {
	$theme = wp_get_theme();
	if ( ! $theme ) {
		return false;
	}

	$template = $theme->get_template();
	$parent   = $theme->parent();

	return (
		( is_string( $template ) && strtolower( $template ) === 'divi' ) ||
		( $parent && is_string( $parent->get_template() ) && strtolower( $parent->get_template() ) === 'divi' )
	);
}

/**
 * Force Divi framework load on Post Duplicator settings page.
 *
 * Ensures Divi post types (e.g. et_pb_layout) are registered when this settings
 * screen renders the Post Types matrix.
 *
 * @param bool $should_load Current Divi should-load value.
 * @return bool
 */
function maybe_load_divi_framework_on_post_duplicator_settings( $should_load ) {
	if ( ! is_divi_active() ) {
		return $should_load;
	}

	global $pagenow;
	$is_post_duplicator_settings = (
		'options-general.php' === $pagenow &&
		isset( $_GET['page'] ) &&
		'mtphr_post_duplicator' === sanitize_key( wp_unslash( $_GET['page'] ) )
	);

	if ( $is_post_duplicator_settings ) {
		return true;
	}

	return $should_load;
}

/**
 * Ensure Post Duplicator scripts load on the Divi Library list screen.
 *
 * @param bool $should_enqueue Current filter value.
 * @return bool
 */
function maybe_enqueue_on_divi_library_screen( $should_enqueue ) {
	if ( is_divi_library_screen() ) {
		return true;
	}
	return $should_enqueue;
}

/**
 * Check if the current screen is a Divi Library screen.
 *
 * @return bool
 */
function is_divi_library_screen() {
	$screen = get_current_screen();
	return $screen && isset( $screen->post_type ) && 'et_pb_layout' === $screen->post_type;
}

/**
 * Force Basic mode on Divi Library screens.
 *
 * Divi loads React 16 which is incompatible with the Advanced mode modal
 * (requires React 18 APIs).
 *
 * @param string $mode Current mode.
 * @return string
 */
function force_basic_mode_on_divi_screens( $mode ) {
	if ( is_divi_library_screen() ) {
		return 'basic';
	}
	return $mode;
}

/**
 * Force refresh after duplication on Divi Library screens.
 *
 * The 'notice' action opens a React modal which is incompatible with
 * Divi's React 16 environment.
 *
 * @param string $action Current action.
 * @return string
 */
function force_refresh_on_divi_screens( $action ) {
	if ( is_divi_library_screen() ) {
		return 'refresh';
	}
	return $action;
}

/**
 * Add a notice to the General settings tab about Divi Library limitations.
 *
 * @param array $notices Existing notice fields.
 * @return array
 */
function add_divi_settings_notice( $notices ) {
	if ( ! is_divi_active() ) {
		return $notices;
	}

  $notices[] = [
    'type'             => 'notification',
    'notificationType' => 'warning',
    'label'            => __( 'Divi Compatibility', 'post-duplicator' ),
    'message'          => __( 'Divi Library Layouts will always use Basic mode due to limitations with Divi\'s codebase. The Advanced modal is not available when duplicating Divi Layouts.', 'post-duplicator' ),
    'isDismissible'    => true,
  ];

	return $notices;
}

/**
 * Exclude internal Divi post types from duplication.
 *
 * @param array $excluded_post_types Excluded post types.
 * @return array
 */
function exclude_divi_post_types( $excluded_post_types ) {
	return array_merge( $excluded_post_types, array(
		'et_body_layout',
		'et_code_snippet',
		'et_footer_layout',
		'et_header_layout',
		'et_tb_item',
		'et_template',
		'et_theme_builder',
		'et_theme_options',
	) );
}
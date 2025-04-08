<?php
namespace Mtphr\PostDuplicator\Hooks;
use function Mtphr\PostDuplicator\user_can_duplicate;

// Disable WC product review count
add_filter( 'mtphr_post_duplicator_meta__wc_review_count_enabled', '__return_false' );
add_filter( 'plugin_action_links_' . MTPHR_POST_DUPLICATOR_BASENAME, __NAMESPACE__ . '\add_plugin_settings_link' );
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\duplicator_submitbox' );
add_action( 'mtphr_post_duplicator_created', __NAMESPACE__ . '\wpml_duplication', 10, 2 );


/**
 * Add settings link to the plugin screen
 */
function add_plugin_settings_link( $links ) {
  
  // Define the settings page URL
  $settings_link = '<a href="' . admin_url( 'options-general.php?page=mtphr_post_duplicator' ). '">'. __( 'Settings', 'post-duplicator' ) . '</a>';
  
  // Add the settings link to the beginning of the array
  array_unshift($links, $settings_link);
  
  return $links;
}

/**
 * Add duplicate post option on the legacy post edit screen
 */
function duplicator_submitbox( $post ) {
	if ( 'publish' != $post->post_status || ! user_can_duplicate( $post ) ) {
    return false;
  }
  if ( $post_type = get_post_type_object( $post->post_type ) ) {
    ?>
    <div class="misc-pub-section misc-pub-duplicator" id="duplicator">
      <a class="m4c-duplicate-post button button-small" href="#" data-postid="<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( sprintf( __( 'Duplicate %s', 'post-duplicator' ), $post_type->labels->singular_name ) ); ?></a><span class="spinner" style="float:none;margin-top:2px;margin-left:4px;"></span>
    </div>
    <?php
  }
}

/**
 * Make sure WPML is set up on the duplicated post
 */
function wpml_duplication( $original_id, $duplicate_id ) {
  global $sitepress;

  // Get the WPML translation group ID (trid) and language code of the original post
  $language_info = apply_filters( 'wpml_post_language_details', null, $original_id );

  if ( $language_info ) {
    $original_trid = isset( $language_info['trid'] ) ? $language_info['trid'] : false;
    $language_code = $language_info['language_code'];

    // Set the WPML language info for the duplicated post
    do_action( 'wpml_set_element_language_details', array(
        'element_id'    => $duplicate_id,
        'element_type'  => 'post_' . get_post_type( $original_id ),
        'trid'          => $original_trid,
        'language_code' => $language_code,
        'source_language_code' => null,
    ) );
  }
}
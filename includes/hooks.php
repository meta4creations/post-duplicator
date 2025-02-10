<?php
namespace Mtphr\PostDuplicator\Hooks;
use function Mtphr\PostDuplicator\user_can_duplicate;

// Disable WC product review count
add_filter( 'mtphr_post_duplicator_meta__wc_review_count_enabled', '__return_false' );
add_action( 'post_submitbox_misc_actions', __NAMESPACE__ . '\duplicator_submitbox' );

/**
 * Add duplicate post option on the legacy post edit screen
 */
function duplicator_submitbox( $post ) {
	if ( 'publish' != $post->post_status || ! user_can_duplicate( $post ) ) {
    return false;
  }
  $post_type = get_post_type_object( $post->post_type );
  ?>
  <div class="misc-pub-section misc-pub-duplicator" id="duplicator">
    <a class="m4c-duplicate-post button button-small" href="#" data-postid="<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( sprintf( __( 'Duplicate %s', 'post-duplicator' ), $post_type->labels->singular_name ) ); ?></a><span class="spinner" style="float:none;margin-top:2px;margin-left:4px;"></span>
  </div>
  <?php
}
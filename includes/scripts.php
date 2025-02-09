<?php
namespace Mtphr\PostDuplicator;

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts' );

/**
 * Enqueue admin scripts
 */
function enqueue_scripts() {

  $asset_file = include( MTPHR_POST_DUPLICATOR_DIR . 'assets/build/postDuplicator.asset.php' );
  wp_enqueue_style(
    'post-duplicator',
    MTPHR_POST_DUPLICATOR_URL . 'assets/build/postDuplicator.css',
    ['wp-components'],
    $asset_file['version']
  );
  wp_enqueue_script(
    'post-duplicator',
    MTPHR_POST_DUPLICATOR_URL . 'assets/build/postDuplicator.js',
    $asset_file['dependencies'],
    $asset_file['version'],
    true
  ); 
  wp_localize_script( 'post-duplicator', 'postDuplicatorVars', [
    'siteUrl'  => site_url(),
    'restUrl'  => esc_url_raw( rest_url( 'post-duplicator/v1/' ) ),
    'nonce'    => wp_create_nonce( 'wp_rest' )
  ] );
}
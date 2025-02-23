<?php
namespace Mtphr\PostDuplicator\Upgrages;
use function Mtphr\PostDuplicator\get_option_value;

add_action( 'admin_init', __NAMESPACE__ . '\run_updates' );

/**
 * Run updates
 *
 * @since  1.0.0
 * @return void
 */
function run_updates() {
  $current_version = get_option( 'mtphr_postduplicator_version', '0' );
  if ( ! $current_version ) {
    $current_version = '0';
  }
  
	if ( version_compare( $current_version, '2.41', '<' ) ) {
    update_v2_41();
	}

	if ( MTPHR_POST_DUPLICATOR_VERSION != $current_version ) {
		update_option( 'mtphr_postduplicator_version_upgraded_from', $current_version );
		update_option( 'mtphr_postduplicator_version', MTPHR_POST_DUPLICATOR_VERSION );
	}
}

/**
 * Version 2.41 updates
 */
function update_v2_41() {
  $duplicate_roles = [
    'administrator',
    'editor',
    'author',
    'contributor',
  ];
  $duplicate_others_roles = [
    'administrator',
  ];
  if ( 'all_users' === get_option_value( 'post_duplication' ) ) {
    $duplicate_others_roles[] = 'editor';
  }

  if ( is_array( $duplicate_roles ) && ! empty( $duplicate_roles ) ) {
    foreach ( $duplicate_roles as $slug ) {
      if ( $role = get_role( $slug ) ) {
        $role->add_cap( 'duplicate_posts' );
        if ( in_array( $slug, $duplicate_others_roles ) ) {
          $role->add_cap( 'duplicate_others_posts' );
        }
      }
    }
  }
}
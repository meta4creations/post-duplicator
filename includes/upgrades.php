<?php
namespace Mtphr\PostDuplicator\Upgrages;
use function Mtphr\PostDuplicator;

add_action( 'admin_init', __NAMESPACE__ . '\run_updates' );

/**
 * Run updates
 *
 * @since  1.0.0
 * @return void
 */
function run_updates() {
	$current_version = get_option( 'mtphr_postduplicator_version' );
  if ( ! $current_version ) {
		update_option( 'mtphr_postduplicator_version', MTPHR_POST_DUPLICATOR_VERSION );
    return false;
  }

	if ( version_compare( $current_version, '2.38', '<' ) ) {
    update_v2_38();
	}

	if ( MTPHR_POST_DUPLICATOR_VERSION != $current_version ) {
		update_option( 'mtphr_postduplicator_version_upgraded_from', $current_version );
		update_option( 'mtphr_postduplicator_version', MTPHR_POST_DUPLICATOR_VERSION );
	}
}

/**
 * Version 2.38 updates
 */
function update_v2_38() {
}
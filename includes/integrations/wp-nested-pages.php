<?php
/**
 * Integration with WP Nested Pages plugin
 *
 * WP Nested Pages replaces the default post list screen with a custom interface.
 * The duplicate link appears in .np-assigned-pt-actions but the Post Duplicator
 * scripts only load on the standard edit.php screen. This integration ensures
 * the scripts load on Nested Pages screens so the duplicate functionality works.
 *
 * @package Mtphr\PostDuplicator\Integrations
 */

namespace Mtphr\PostDuplicator\Integrations;

// Only load when WP Nested Pages is active
if ( ! class_exists( 'NestedPages' ) ) {
	return;
}

add_filter( 'mtphr_post_duplicator_should_enqueue_list_scripts', __NAMESPACE__ . '\maybe_enqueue_on_nested_pages_screen', 10, 1 );

/**
 * Tell Post Duplicator to enqueue list scripts when on a WP Nested Pages screen.
 *
 * Nested Pages uses custom admin pages (e.g. admin.php?page=nestedpages) instead
 * of the standard edit.php, so the default screen check doesn't match.
 *
 * @param bool $should_enqueue Current filter value
 * @return bool True if we're on a Nested Pages screen and scripts should load
 */
function maybe_enqueue_on_nested_pages_screen( $should_enqueue ) {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return $should_enqueue;
	}

	// Nested Pages screens: toplevel_page_nestedpages, toplevel_page_nestedpages-{post_type}, etc.
	if ( strpos( $screen->id, 'nestedpages' ) !== false ) {
		return true;
	}

	return $should_enqueue;
}

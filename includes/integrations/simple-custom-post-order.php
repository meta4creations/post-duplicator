<?php
/**
 * Integration with Simple Custom Post Order (SCPO) plugin
 *
 * SCPO manages post order via the menu_order field and runs a "refresh" on every
 * admin page load that reassigns sequential menu_order values when it detects
 * gaps or duplicates. Post Duplicator copies menu_order from the original,
 * creating duplicate values that trigger SCPO's refresh and cause unpredictable
 * reordering of all posts.
 *
 * This integration assigns the duplicate a unique menu_order (max+1) when SCPO
 * is active, preventing the refresh from running and preserving the user's order.
 *
 * @package Mtphr\PostDuplicator\Integrations
 */

namespace Mtphr\PostDuplicator\Integrations;

// Only load when Simple Custom Post Order is active
if ( ! class_exists( 'SCPO_Engine' ) ) {
	return;
}

add_action( 'mtphr_post_duplicator_created', __NAMESPACE__ . '\set_duplicate_menu_order_for_scpo', 10, 3 );

/**
 * Set the duplicate's menu_order to a unique value when SCPO is managing this post type.
 *
 * SCPO's refresh() runs on admin_init and reassigns menu_order when cnt !== max.
 * By assigning max+1, we keep the sequence intact so SCPO skips the refresh.
 *
 * @param int   $original_id Original post ID
 * @param int   $duplicate_id Duplicated post ID
 * @param array $settings Duplication settings
 */
function set_duplicate_menu_order_for_scpo( $original_id, $duplicate_id, $settings ) {
	$duplicate_id = absint( $duplicate_id );
	if ( ! $duplicate_id ) {
		return;
	}

	$post_type = get_post_type( $duplicate_id );
	if ( ! $post_type ) {
		return;
	}
	$post_type = sanitize_key( $post_type );

	$scpo_options = get_option( 'scporder_options', [] );
	$objects      = isset( $scpo_options['objects'] ) && is_array( $scpo_options['objects'] )
		? $scpo_options['objects']
		: [];

	if ( ! in_array( $post_type, $objects, true ) ) {
		return;
	}

	global $wpdb;

	$max = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT MAX(menu_order) FROM $wpdb->posts
			WHERE post_type = %s AND post_status IN ('publish', 'pending', 'draft', 'private', 'future')",
			$post_type
		)
	);

	$new_menu_order = (int) $max + 1;

	$wpdb->update(
		$wpdb->posts,
		[ 'menu_order' => $new_menu_order ],
		[ 'ID' => $duplicate_id ],
		[ '%d' ],
		[ '%d' ]
	);

	clean_post_cache( $duplicate_id );
}

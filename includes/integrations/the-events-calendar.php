<?php
namespace Mtphr\PostDuplicator\Integrations;

add_action( 'mtphr_post_duplicator_created', __NAMESPACE__ . '\handle_tribe_events_duplication', 20, 3 );

/**
 * Handle The Events Calendar event duplication
 * 
 * The Events Calendar (TEC) stores event data in custom database tables.
 * When duplicating an event, we need to ensure that the custom table data
 * is properly synced after the post meta is copied.
 * 
 * @param int $original_id Original post ID
 * @param int $duplicate_id Duplicated post ID
 * @param array $settings Duplication settings
 */
function handle_tribe_events_duplication( $original_id, $duplicate_id, $settings ) {
	if ( get_post_type( $duplicate_id ) !== 'tribe_events' ) {
		return;
	}

	// Update custom tables
	if ( class_exists( 'TEC\Events\Custom_Tables\V1\Updates\Events' ) ) {
		$events_updater = tribe( \TEC\Events\Custom_Tables\V1\Updates\Events::class );
		$result = $events_updater->update( $duplicate_id );
	}
}
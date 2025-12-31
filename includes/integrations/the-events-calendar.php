<?php
/**
 * The Events Calendar Integration
 * 
 * Handles proper duplication of tribe_events custom post types
 * ensuring that The Events Calendar's custom tables are properly populated.
 */

namespace Mtphr\PostDuplicator\Integrations;

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
	// Check if this is a tribe_events post type
	$duplicate_post = get_post( $duplicate_id );
	if ( ! $duplicate_post || $duplicate_post->post_type !== 'tribe_events' ) {
		return;
	}
	
	// Check if The Events Calendar is active
	if ( ! class_exists( 'Tribe__Events__Main' ) ) {
		return;
	}
	
	// Get the original event data
	$original_post = get_post( $original_id );
	if ( ! $original_post ) {
		return;
	}
	
	// Check if TEC's Custom Tables are active
	$using_custom_tables = class_exists( 'TEC\Events\Custom_Tables\V1\Models\Event' );
	
	if ( $using_custom_tables ) {
		// TEC Custom Tables v1 implementation
		// We need to ensure the event is properly inserted into the custom tables
		
		// Get all meta from the original event
		$original_meta = get_post_meta( $original_id );
		
		// Essential event meta keys for TEC
		$event_meta_keys = array(
			'_EventStartDate',
			'_EventEndDate',
			'_EventStartDateUTC',
			'_EventEndDateUTC',
			'_EventTimezone',
			'_EventAllDay',
			'_EventDuration',
			'_EventShowMap',
			'_EventShowMapLink',
			'_EventURL',
			'_EventCost',
			'_EventCurrencySymbol',
			'_EventCurrencyPosition',
			'_EventVenueID',
			'_EventOrganizerID',
			'_EventOrigin',
		);
		
		// Ensure all event-specific meta is present on the duplicate
		$meta_copied_count = 0;
		foreach ( $event_meta_keys as $meta_key ) {
			if ( isset( $original_meta[ $meta_key ] ) && ! empty( $original_meta[ $meta_key ][0] ) ) {
				$meta_value = $original_meta[ $meta_key ][0];
				// Delete first to ensure clean state
				delete_post_meta( $duplicate_id, $meta_key );
				// Add the meta
				add_post_meta( $duplicate_id, $meta_key, $meta_value, true );
				$meta_copied_count++;
			}
		}
		
		// Now we need to trigger TEC's custom tables sync
		// The best approach is to use their "save_post" handler which syncs meta to custom tables
		
		$sync_success = false;
		
		// Check if the custom tables updater class exists
		if ( class_exists( 'TEC\Events\Custom_Tables\V1\Updates\Updater' ) ) {
			try {
				// Get the updater instance
				$updater = tribe( 'TEC\Events\Custom_Tables\V1\Updates\Updater' );
				
				// Sync this specific event
				if ( method_exists( $updater, 'update' ) ) {
					$result = $updater->update( $duplicate_id );
					$sync_success = true;
				}
			} catch ( \Exception $e ) {
				// Silently fail and try next method
			}
		}
		
		// Alternative approach: Use TEC's event upsert functionality
		if ( ! $sync_success && class_exists( 'TEC\Events\Custom_Tables\V1\Events\Converter\Event_Converter_From_Post' ) ) {
			try {
				// Convert post to event and upsert into custom tables
				$converter = tribe( 'TEC\Events\Custom_Tables\V1\Events\Converter\Event_Converter_From_Post' );
				
				if ( method_exists( $converter, 'convert_to_event' ) ) {
					$event = $converter->convert_to_event( $duplicate_id );
					
					if ( $event && method_exists( $event, 'save' ) ) {
						$event->save();
						$sync_success = true;
					}
				}
			} catch ( \Exception $e ) {
				// Silently fail and try next method
			}
		}
		
		// Final fallback: Trigger the save_post hook that TEC uses
		if ( ! $sync_success ) {
			// Remove our hook to prevent recursion
			remove_action( 'mtphr_post_duplicator_created', __NAMESPACE__ . '\handle_tribe_events_duplication', 20 );
			
			// Trigger TEC's save_post handler
			// TEC hooks into 'save_post' with priority 15 and 20
			do_action( 'save_post', $duplicate_id, $duplicate_post, false );
			do_action( 'save_post_tribe_events', $duplicate_id, $duplicate_post, false );
			
			// Re-add our hook
			add_action( 'mtphr_post_duplicator_created', __NAMESPACE__ . '\handle_tribe_events_duplication', 20, 3 );
		}
		
	} else {
		// Legacy TEC (not using custom tables) - meta fields should be sufficient
		// Just ensure critical meta fields are present
		$critical_meta = array(
			'_EventStartDate',
			'_EventEndDate',
			'_EventAllDay',
			'_EventTimezone',
		);
		
		$original_meta = get_post_meta( $original_id );
		foreach ( $critical_meta as $meta_key ) {
			if ( isset( $original_meta[ $meta_key ] ) && ! empty( $original_meta[ $meta_key ][0] ) ) {
				$existing = get_post_meta( $duplicate_id, $meta_key, true );
				if ( empty( $existing ) ) {
					add_post_meta( $duplicate_id, $meta_key, $original_meta[ $meta_key ][0], true );
				}
			}
		}
	}
	
	// Handle event recurrence if the original event is recurring
	$original_meta = get_post_meta( $original_id );
	if ( isset( $original_meta['_EventRecurrence'] ) && ! empty( $original_meta['_EventRecurrence'][0] ) ) {
		// Copy recurrence data
		$recurrence_data = maybe_unserialize( $original_meta['_EventRecurrence'][0] );
		delete_post_meta( $duplicate_id, '_EventRecurrence' );
		add_post_meta( $duplicate_id, '_EventRecurrence', $recurrence_data, true );
	}
	
	// Clear TEC's event cache for this post
	if ( function_exists( 'tribe_get_event' ) ) {
		wp_cache_delete( $duplicate_id, 'tribe_events' );
		wp_cache_delete( $duplicate_id, 'posts' );
	}
}

// Hook into the post duplicator created action with priority 20
// This ensures it runs after the meta has been copied
add_action( 'mtphr_post_duplicator_created', __NAMESPACE__ . '\handle_tribe_events_duplication', 20, 3 );


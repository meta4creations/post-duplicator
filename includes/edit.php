<?php
namespace Mtphr\PostDuplicator;

add_filter( 'post_row_actions', __NAMESPACE__ . '\add_row_action', 10, 2 );
add_filter( 'page_row_actions', __NAMESPACE__ . '\add_row_action', 10, 2 );
add_filter( 'cuar/core/admin/content-list-table/row-actions', __NAMESPACE__ . '\add_row_action', 10, 2 );

// PHP fallback: handles the duplicate link when JS scripts are not loaded on the current screen
add_action( 'admin_post_mtphr_quick_duplicate', __NAMESPACE__ . '\handle_quick_duplicate' );

// Add bulk actions for all post types dynamically
add_action( 'admin_init', __NAMESPACE__ . '\register_bulk_actions' );
add_action( 'admin_footer', __NAMESPACE__ . '\add_bulk_action_script' );

/**
 * Register bulk actions for all post types
 */
function register_bulk_actions() {
	$post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
	
	// Remove post types that shouldn't have duplicate actions
	$excluded = array( 'attachment', 'revision', 'nav_menu_item', 'wooframework' );
	
	foreach ( $post_types as $post_type ) {
		if ( ! in_array( $post_type->name, $excluded ) ) {
			add_filter( "bulk_actions-edit-{$post_type->name}", __NAMESPACE__ . '\add_bulk_action' );
		}
	}
}

/**
 * Add a duplicate post link.
 *
 * @since 2.27
 */
function add_row_action_link( $post ) {
	
	// Do not show on trash page
	$post_status = isset( $_GET['post_status'] ) ? sanitize_text_field( $_GET['post_status'] ) : false;
	if ( 'trash' == $post_status ) {
		return false;
	}

	// Check if post type is enabled for duplication
	if ( ! is_post_type_duplication_enabled( $post->post_type ) ) {
		return false;
	}

  // Make sure the user can duplicate
	if ( ! user_can_duplicate( $post ) ) {
		return false;
	}

	// Get the post type object
	$post_type = get_post_type_object( $post->post_type );
	
	// Set the button label
	$label = sprintf( __( 'Duplicate %s', 'post-duplicator' ), $post_type->labels->singular_name );
	
	// Modify the label if duplicating to new post type
	if( 'same' != get_option_value( 'type' ) ) {
		if ( $new_post_type = get_post_type_object( get_option_value( 'type' ) ) ) {
      if ( $post_type->name != $new_post_type->name ) {
        $label = sprintf( __( 'Duplicate %1$s to %2$s', 'post-duplicator' ), $post_type->labels->singular_name, $new_post_type->labels->singular_name );
      }
    }
	}

	// Build a PHP fallback URL for screens where scripts may not be loaded.
	// When JS is present it intercepts the click and opens the modal instead.
	$fallback_url = admin_url(
		'admin-post.php?action=mtphr_quick_duplicate&post_id=' . $post->ID
		. '&_wpnonce=' . wp_create_nonce( 'mtphr_quick_duplicate_' . $post->ID )
	);

	// Return the link
	return '<a class="m4c-duplicate-post" href="' . esc_url( $fallback_url ) . '" data-postid="' . esc_attr( $post->ID ) . '" data-posttype="' . esc_attr( $post->post_type ) . '">' . wp_kses_post( $label ) . '</a>';
}

// Add the duplicate link to post actions
function add_row_action( $actions, $post ){
	if ( $link = add_row_action_link( $post ) ) {
    $actions['duplicate_post'] = $link;
  }	
	return $actions;
}

/**
 * Add bulk duplicate action to bulk actions dropdown
 *
 * @param array $actions Existing bulk actions
 * @return array Modified bulk actions
 */
function add_bulk_action( $actions ) {
	// Get current post type from screen
	$screen = get_current_screen();
	if ( ! $screen || ! isset( $screen->post_type ) ) {
		return $actions;
	}

	$post_type = $screen->post_type;
	
	// Check if post type is enabled for duplication
	if ( ! is_post_type_duplication_enabled( $post_type ) ) {
		return $actions;
	}
	
	$post_type_obj = get_post_type_object( $post_type );
	
	if ( ! $post_type_obj ) {
		return $actions;
	}

	// Add bulk duplicate action
	$actions['m4c_bulk_duplicate'] = sprintf( 
		__( 'Duplicate %s', 'post-duplicator' ), 
		$post_type_obj->labels->name 
	);

	return $actions;
}

/**
 * PHP fallback handler for the duplicate row-action link.
 *
 * Fires when the duplicate link is clicked on a screen where the Post Duplicator
 * JavaScript is not enqueued. Performs duplication using global default settings
 * (equivalent to Basic mode) then redirects back to the referring page.
 *
 * All security checks mirror those in the REST API permission callback so that
 * the same rules apply regardless of which code path triggers the duplication.
 */
function handle_quick_duplicate() {
	$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

	if ( ! $post_id ) {
		wp_die( esc_html__( 'Invalid post ID.', 'post-duplicator' ) );
	}

	// Verify nonce — tied to the specific post ID so one nonce cannot be replayed
	// against a different post. Also implicitly confirms the user is logged in.
	check_admin_referer( 'mtphr_quick_duplicate_' . $post_id );

	$post = get_post( $post_id );
	if ( ! $post ) {
		wp_die( esc_html__( 'Post not found.', 'post-duplicator' ) );
	}

	// Enforce the same permission rules as the REST API
	if ( ! user_can_duplicate( $post ) ) {
		wp_die( esc_html__( 'You do not have permission to duplicate this post.', 'post-duplicator' ) );
	}

	if ( ! is_post_type_duplication_enabled( $post->post_type ) ) {
		wp_die( esc_html__( 'Duplication is disabled for this post type.', 'post-duplicator' ) );
	}

	// Duplicate using global default settings (no modal overrides)
	$result = perform_duplication( $post, get_option_value() );

	$redirect = wp_get_referer() ?: admin_url();

	if ( is_wp_error( $result ) ) {
		wp_safe_redirect( add_query_arg( 'duplicated', '0', $redirect ) );
	} else {
		wp_safe_redirect( add_query_arg( 'duplicated', '1', $redirect ) );
	}
	exit;
}

/**
 * Add JavaScript to handle bulk duplicate action
 * Instead of submitting the form, we intercept and open the modal
 */
function add_bulk_action_script() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->base !== 'edit' || ! isset( $screen->post_type ) ) {
		return;
	}

	?>
	<script type="text/javascript">
	(function($) {
		// Intercept bulk action form submission
		$(document).on('submit', '#posts-filter', function(e) {
			var $form = $(this);
			var $action = $form.find('select[name="action"]');
			var $action2 = $form.find('select[name="action2"]');
			var selectedAction = $action.val() || $action2.val();
			
			if (selectedAction === 'm4c_bulk_duplicate') {
				e.preventDefault();
				
				// Get selected post IDs
				var postIds = [];
				$form.find('tbody input[type="checkbox"][name="post[]"]:checked').each(function() {
					postIds.push($(this).val());
				});
				
				if (postIds.length === 0) {
					alert('<?php echo esc_js( __( 'Please select at least one post to duplicate.', 'post-duplicator' ) ); ?>');
					return false;
				}
				
			// Store post IDs in a data attribute on the form for JavaScript to pick up
			$form.attr('data-bulk-duplicate-ids', postIds.join(','));
			
			// Dispatch a native CustomEvent that our React JavaScript will listen for
			var event = new CustomEvent('m4c:bulk-duplicate', {
				detail: {
					postIds: postIds,
					postType: '<?php echo esc_js( $screen->post_type ); ?>'
				}
			});
			document.dispatchEvent(event);
			
			return false;
			}
		});
	})(jQuery);
	</script>
	<?php
}


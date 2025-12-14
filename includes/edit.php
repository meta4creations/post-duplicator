<?php
namespace Mtphr\PostDuplicator;

add_filter( 'post_row_actions', __NAMESPACE__ . '\add_row_action', 10, 2 );
add_filter( 'page_row_actions', __NAMESPACE__ . '\add_row_action', 10, 2 );
add_filter( 'cuar/core/admin/content-list-table/row-actions', __NAMESPACE__ . '\add_row_action', 10, 2 );

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

	// Return the link
	return '<a class="m4c-duplicate-post" href="#" data-postid="'.esc_attr( $post->ID ).'" data-posttype="'.esc_attr( $post->post_type ).'">'.wp_kses_post( $label ).'</a>';
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


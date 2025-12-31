<?php
namespace Mtphr\PostDuplicator;

add_action('admin_notices', __NAMESPACE__ . '\notice');
add_action('admin_init', __NAMESPACE__ . '\register_upgrade_notices', 5); // Register early
add_action('admin_notices', __NAMESPACE__ . '\display_registered_upgrade_notices');
add_action('wp_ajax_mtphr_post_duplicator_dismiss_notice', __NAMESPACE__ . '\dismiss_notice');
add_action('admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_notice_scripts');

/* --------------------------------------------------------- */
/* !Create an admin notice that a post has been duplicated - 2.27 */
/* --------------------------------------------------------- */

function notice() {
	
	$duplicated_id = isset( $_GET['post-duplicated'] ) ? intval( $_GET['post-duplicated'] ) : '';
	if( $duplicated_id != '' ) {
	
		// Get the post type object
		$duplicated_post = get_post( $duplicated_id );
		if ( $post_type = get_post_type_object( $duplicated_post->post_type ) ) {
      
      // Set the button label
      $pt = sanitize_text_field( $post_type->labels->singular_name );
      $link = wp_kses_post( '<a href="'.get_edit_post_link( $duplicated_id ).'">'.esc_html__( 'here', 'post-duplicator' ).'</a>' );
      $label = sprintf( __( 'Successfully Duplicated! You can edit your new %1$s %2$s.', 'post-duplicator' ), $pt, $link );
      
      ?>
      <div class="updated">
        <p><?php echo wp_kses_post( $label ); ?></p>
      </div>
      <?php
    }
	}
}

/* --------------------------------------------------------- */
/* !Reusable upgrade notice system - 3.0.4 */
/* --------------------------------------------------------- */

/**
 * Store registered notices
 * 
 * @var array
 */
$GLOBALS['mtphr_post_duplicator_registered_notices'] = array();

/**
 * Register upgrade notices
 * This function registers all upgrade notices and should be called early
 * 
 * @since 3.0.4
 */
function register_upgrade_notices() {
	// Prevent duplicate registration
	static $registered = false;
	if ( $registered ) {
		return;
	}
	$registered = true;

	// Initialize the notices array
	if ( ! isset( $GLOBALS['mtphr_post_duplicator_registered_notices'] ) ) {
		$GLOBALS['mtphr_post_duplicator_registered_notices'] = array();
	}

	// Register upgrade notices here
	register_upgrade_notice( 'post_types_3_0_4', '3.0.4', __NAMESPACE__ . '\post_types_setting_notice_content' );
	
	// Add more notices here as needed:
	// register_upgrade_notice( 'feature_xyz_3_0_5', '3.0.5', __NAMESPACE__ . '\feature_xyz_notice_content' );
}

/**
 * Register an upgrade notice
 * 
 * @param string $notice_id Unique identifier for the notice
 * @param string $min_version Minimum version that triggers this notice (user must upgrade TO this version or later)
 * @param callable $content_callback Function that outputs the notice content
 * 
 * @since 3.0.4
 */
function register_upgrade_notice( $notice_id, $min_version, $content_callback ) {
	// Store the notice registration
	if ( ! isset( $GLOBALS['mtphr_post_duplicator_registered_notices'] ) ) {
		$GLOBALS['mtphr_post_duplicator_registered_notices'] = array();
	}
	
	$GLOBALS['mtphr_post_duplicator_registered_notices'][] = array(
		'id' => $notice_id,
		'version' => $min_version,
		'callback' => $content_callback,
	);
}

/**
 * Display all registered upgrade notices that should be shown
 * 
 * @since 3.0.4
 */
function display_registered_upgrade_notices() {
	// Only show to users who can manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$registered_notices = get_registered_upgrade_notices();
	if ( empty( $registered_notices ) ) {
		return;
	}

	// Check if user upgraded from a version before the minimum version
	$upgraded_from = get_option( 'mtphr_postduplicator_version_upgraded_from', '0' );
	$current_version = get_option( 'mtphr_postduplicator_version', '0' );
	
	// If no upgrade record exists, don't show notices
	if ( '0' === $upgraded_from || empty( $upgraded_from ) ) {
		// For fresh installs or if upgrade tracking isn't set, don't show notice
		// Only show if we know they upgraded from an older version
		return;
	}

	foreach ( $registered_notices as $notice_data ) {
		$notice_id = $notice_data['id'];
		$min_version = $notice_data['version'];
		$content_callback = $notice_data['callback'];

		// Check if notice has been dismissed
		$dismissed = get_user_meta( get_current_user_id(), 'mtphr_post_duplicator_notice_dismissed_' . $notice_id, true );
		if ( $dismissed ) {
			continue;
		}

		// Check if they upgraded from a version before min_version to min_version or later
		if ( version_compare( $upgraded_from, $min_version, '<' ) && version_compare( $current_version, $min_version, '>=' ) ) {
			// User upgraded from before min_version to min_version or later - show notice
			display_upgrade_notice( $notice_id, $content_callback );
		}
	}
}

/**
 * Display an upgrade notice
 * 
 * @param string $notice_id Unique identifier for the notice
 * @param callable $content_callback Function that outputs the notice content
 * 
 * @since 3.0.4
 */
function display_upgrade_notice( $notice_id, $content_callback ) {
	// Don't show on the settings page itself
	$screen = get_current_screen();
	if ( $screen && $screen->id === 'settings_page_mtphr_post_duplicator' ) {
		return;
	}

	$icon_url = MTPHR_POST_DUPLICATOR_URL . 'assets/img/icon-128x128.png';

	?>
	<div class="notice notice-info is-dismissible mtphr-post-duplicator-upgrade-notice" data-notice="<?php echo esc_attr( $notice_id ); ?>" style="display: flex; align-items: center; padding-left: 10px;">
		<div style="margin-right: 10px; flex-shrink: 0;">
			<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php esc_attr_e( 'Post Duplicator', 'post-duplicator' ); ?>" style="width: 48px; height: 48px; display: block;">
		</div>
		<div style="flex: 1;">
			<?php call_user_func( $content_callback ); ?>
		</div>
	</div>
	<?php
}

/**
 * Post Types setting notice content (for version 3.0.4 upgrade)
 * 
 * @since 3.0.4
 */
function post_types_setting_notice_content() {
	$settings_url = admin_url( 'options-general.php?page=mtphr_post_duplicator&section=post_types' );
	$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Post Types settings', 'post-duplicator' ) . '</a>';
	?>
	<p>
		<strong><?php esc_html_e( 'New Post Types Setting', 'post-duplicator' ); ?></strong><br>
		<?php 
		printf(
			/* translators: %1$s: Link to Post Types settings */
			esc_html__( 'Post Duplicator has added a new Post Types setting section. Configure which post types can be duplicated and which appear in the "Post Type" dropdown menu. %1$s', 'post-duplicator' ),
			$settings_link
		);
		?>
	</p>
	<?php
}

/* --------------------------------------------------------- */
/* !Handle notice dismissal via AJAX */
/* --------------------------------------------------------- */

/**
 * Handle notice dismissal via AJAX
 * 
 * @since 3.0.4
 */
function dismiss_notice() {
	// Verify nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'mtphr_post_duplicator_dismiss_notice' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce.', 'post-duplicator' ) ) );
	}

	// Check user capability
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'post-duplicator' ) ) );
	}

	// Get notice ID
	$notice_id = isset( $_POST['notice'] ) ? sanitize_text_field( $_POST['notice'] ) : '';
	if ( empty( $notice_id ) ) {
		wp_send_json_error( array( 'message' => esc_html__( 'Invalid notice ID.', 'post-duplicator' ) ) );
	}

	// Mark notice as dismissed
	update_user_meta( get_current_user_id(), 'mtphr_post_duplicator_notice_dismissed_' . $notice_id, true );

	wp_send_json_success( array( 'message' => esc_html__( 'Notice dismissed.', 'post-duplicator' ) ) );
}

/* --------------------------------------------------------- */
/* !Enqueue scripts for dismissable notices */
/* --------------------------------------------------------- */

/**
 * Enqueue scripts for dismissable upgrade notices
 * 
 * @since 3.0.4
 */
function enqueue_notice_scripts( $hook ) {
	// Only enqueue on admin pages
	if ( ! is_admin() ) {
		return;
	}

	// Check if user can manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Check if any upgrade notices should be shown
	$upgraded_from = get_option( 'mtphr_postduplicator_version_upgraded_from', '0' );
	$current_version = get_option( 'mtphr_postduplicator_version', '0' );
	
	// If no upgrade record, don't enqueue
	if ( '0' === $upgraded_from || empty( $upgraded_from ) ) {
		return;
	}

	// Get all registered notices and check if any should be shown
	$registered_notices = get_registered_upgrade_notices();
	$has_active_notice = false;
	
	foreach ( $registered_notices as $notice_data ) {
		$notice_id = $notice_data['id'];
		$min_version = $notice_data['version'];
		
		$dismissed = get_user_meta( get_current_user_id(), 'mtphr_post_duplicator_notice_dismissed_' . $notice_id, true );
		if ( ! $dismissed ) {
			// Check if this notice should be shown based on version
			if ( version_compare( $upgraded_from, $min_version, '<' ) && version_compare( $current_version, $min_version, '>=' ) ) {
				$has_active_notice = true;
				break;
			}
		}
	}

	if ( ! $has_active_notice ) {
		return;
	}

	// Ensure jQuery is enqueued
	wp_enqueue_script( 'jquery' );

	// Add inline script to handle dismissal
	$nonce = wp_create_nonce( 'mtphr_post_duplicator_dismiss_notice' );
	$script = "
	(function($) {
		$(document).ready(function() {
			$(document).on('click', '.mtphr-post-duplicator-upgrade-notice .notice-dismiss', function(e) {
				var notice = $(this).closest('.mtphr-post-duplicator-upgrade-notice');
				var noticeId = notice.data('notice');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'mtphr_post_duplicator_dismiss_notice',
						nonce: '" . esc_js( $nonce ) . "',
						notice: noticeId
					}
				});
			});
		});
	})(jQuery);
	";

	wp_add_inline_script( 'jquery', $script );
}

/**
 * Get registered upgrade notices
 * 
 * @return array Array of registered notices with id and version
 * 
 * @since 3.0.4
 */
function get_registered_upgrade_notices() {
	// Ensure notices are registered
	if ( ! isset( $GLOBALS['mtphr_post_duplicator_registered_notices'] ) || empty( $GLOBALS['mtphr_post_duplicator_registered_notices'] ) ) {
		// Register notices if not already done
		register_upgrade_notices();
	}
	
	if ( ! isset( $GLOBALS['mtphr_post_duplicator_registered_notices'] ) ) {
		return array();
	}
	
	return $GLOBALS['mtphr_post_duplicator_registered_notices'];
}
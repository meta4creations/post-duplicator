<?php

add_action( 'admin_menu', 'mtphr_post_duplicator_settings_page' );
/**
 * Add a menu page to display options
 *
 * @since 2.0
 */
function mtphr_post_duplicator_settings_page() {

	add_management_page(
		esc_html__('Post Duplicator', 'post-duplicator'),	// The value used to populate the browser's title bar when the menu page is active
		esc_html__('Post Duplicator', 'post-duplicator'),	// The label of this submenu item displayed in the menu
		'administrator',																	// What roles are able to access this submenu item
		'mtphr_post_duplicator_settings_menu',						// The ID used to represent this submenu item
		'mtphr_post_duplicator_settings_display'					// The callback function used to render the options for this submenu item
	);
}




add_action( 'admin_init', 'mtphr_post_duplicator_initialize_settings' );
/**
 * Initializes the options page.
 *
 * @since 2.17
 */ 
function mtphr_post_duplicator_initialize_settings() {
	
	$options = get_option( 'mtphr_post_duplicator_settings', array() );
	
	$settings['post_duplication'] = array(
		'title' => esc_html__( 'Post Duplication', 'post-duplicator' ),
		'type' => 'radio',
		'options' => array(
			'all_users' => esc_html__('Allow Duplication of All Users', 'post-duplicator'),
			'current_user' => esc_html__('Limit to Current User', 'post-duplicator')
		),
		'display' => 'inline',
		'default' => isset( $options['post_duplication'] ) ? sanitize_text_field( $options['post_duplication'] ) : 'all_users'
	);
	
	$settings['post_author'] = array(
		'title' => esc_html__( 'Post Author', 'post-duplicator' ),
		'type' => 'radio',
		'options' => array(
			'current_user' => esc_html__('Current User', 'post-duplicator'),
			'original_user' => esc_html__('Original Post Author', 'post-duplicator'),
		),
		'display' => 'inline',
		'default' => isset( $options['post_author'] ) ? sanitize_text_field( $options['post_author'] ) : 'current_user'
	);

	$settings['status'] = array(
		'title' => esc_html__( 'Post Status', 'post-duplicator' ),
		'type' => 'select',
		'options' => array(
			'same' => esc_html__('Same as original', 'post-duplicator'),
			'draft' => esc_html__('Draft', 'post-duplicator'),
			'publish' => esc_html__('Published', 'post-duplicator'),
			'pending' => esc_html__('Pending', 'post-duplicator')	
		),
		'default' => isset( $options['status'] ) ? sanitize_text_field( $options['status'] ) : 'draft'
	);
	
	$settings['type'] = array(
		'title' => esc_html__( 'Post Type', 'post-duplicator' ),
		'type' => 'select',
		'options' => mtphr_post_duplicator_post_types(),
		'default' => isset( $options['type'] ) ? sanitize_text_field( $options['type'] ) : 'same'
	);
	
	$settings['timestamp'] = array(
		'title' => esc_html__( 'Post Date', 'post-duplicator' ),
		'type' => 'radio',
		'options' => array(
			'duplicate' => esc_html__('Duplicate Timestamp', 'post-duplicator'),
			'current' => esc_html__('Current Time', 'post-duplicator')
		),
		'display' => 'inline',
		'default' => isset( $options['timestamp'] ) ? sanitize_text_field( $options['timestamp'] ) : 'current'
	);
	
	$settings['title'] = array(
		'title' => esc_html__( 'Duplicate Title', 'post-duplicator' ),
		'description' => esc_html__('String that should be appended to the duplicate post\'s title', 'post-duplicator'),
		'type' => 'text',
		'display' => 'inline',
		'default' => isset( $options['title'] ) ? sanitize_text_field( $options['title'] ) : esc_html__('Copy', 'post-duplicator')
	);
	
	$settings['slug'] = array(
		'title' => esc_html__( 'Duplicate Slug', 'post-duplicator' ),
		'description' => esc_html__('String that should be appended to the duplicate post\'s slug', 'post-duplicator'),
		'type' => 'text',
		'display' => 'inline',
		'default' => isset( $options['slug'] ) ? sanitize_text_field( $options['slug'] ) : 'copy'
	);
	
	$settings['time_offset'] = array(
		'title' => esc_html__( 'Offset Date', 'post-duplicator' ),
		'type' => 'checkbox',
		'default' => isset( $options['time_offset'] ) ? sanitize_text_field( $options['time_offset'] ) : 0,
		'append' => array(
			'time_offset_days' => array(
				'type' => 'text',
				'size' => 2,
				'after' => esc_html__(' days', 'post-duplicator'),
				'text_align' => 'right',
				'default' => isset( $options['time_offset_days'] ) ? sanitize_text_field( $options['time_offset_days'] ) : 0
			),
			'time_offset_hours' => array(
				'type' => 'text',
				'size' => 2,
				'after' => esc_html__(' hours', 'post-duplicator'),
				'text_align' => 'right',
				'default' => isset( $options['time_offset_hours'] ) ? sanitize_text_field( $options['time_offset_hours'] ) : 0
			),
			'time_offset_minutes' => array(
				'type' => 'text',
				'size' => 2,
				'after' => esc_html__(' minutes', 'post-duplicator'),
				'text_align' => 'right',
				'default' => isset( $options['time_offset_minutes'] ) ? sanitize_text_field( $options['time_offset_minutes'] ) : 0
			),
			'time_offset_seconds' => array(
				'type' => 'text',
				'size' => 2,
				'after' => esc_html__(' seconds', 'post-duplicator'),
				'text_align' => 'right',
				'default' => isset( $options['time_offset_seconds'] ) ? sanitize_text_field( $options['time_offset_seconds'] ) : 0
			),
			'time_offset_direction' => array(
				'type' => 'select',
				'options' => array(
					'newer' => esc_html__('newer', 'post-duplicator'),
					'older' => esc_html__('older', 'post-duplicator')
				),
				'default' => isset( $options['time_offset_direction'] ) ? sanitize_text_field( $options['time_offset_direction'] ) : 'newer'
			)
		)
	);

	if( false == get_option('mtphr_post_duplicator_settings') ) {	
		add_option( 'mtphr_post_duplicator_settings' );
	}
	
	/* Register the style options */
	add_settings_section(
		'mtphr_post_duplicator_settings_section',						// ID used to identify this section and with which to register options
		'',																									// Title to be displayed on the administration page
		'mtphr_post_duplicator_settings_callback',					// Callback used to render the description of the section
		'mtphr_post_duplicator_settings'										// Page on which to add this section of options
	);
	
	$settings = apply_filters( 'mtphr_post_duplicator_settings', $settings );

	if( is_array($settings) ) {
		foreach( $settings as $id => $setting ) {	
			$setting['option_id'] = $id;
			$setting['id'] = 'mtphr_post_duplicator_settings['.$id.']';
			add_settings_field( $setting['id'], $setting['title'], 'mtphr_post_duplicator_field_display', 'mtphr_post_duplicator_settings', 'mtphr_post_duplicator_settings_section', $setting);
		}
	}
	
	// Register the fields with WordPress
	register_setting( 'mtphr_post_duplicator_settings', 'mtphr_post_duplicator_settings', 'mtphr_post_duplicator_settings_sanitize' );
}


/**
 * Sanitize the settings
 *
 * @since 2.27
 */
function mtphr_post_duplicator_settings_sanitize( $fields ) {
	$sanitized_fields = array(
		'post_duplication' 			=> isset( $fields['post_duplication'] ) 			? sanitize_text_field( $fields['post_duplication'] ) 			: 'all_users',
		'post_author' 					=> isset( $fields['post_author'] ) 						? sanitize_text_field( $fields['post_author'] ) 					: 'current_user',
		'status' 								=> isset( $fields['status'] ) 								? sanitize_text_field( $fields['status'] ) 								: 'draft',
		'type' 									=> isset( $fields['type'] ) 									? sanitize_text_field( $fields['type'] ) 									: 'same',
		'timestamp' 						=> isset( $fields['timestamp'] ) 							? sanitize_text_field( $fields['timestamp'] ) 						: 'current',
		'title' 								=> isset( $fields['title'] ) 									? sanitize_text_field( $fields['title'] ) 								: '',
		'slug' 									=> isset( $fields['slug'] ) 									? sanitize_title_with_dashes( $fields['slug'] ) 					: '',
		'time_offset' 					=> isset( $fields['time_offset'] ) 						? sanitize_text_field( $fields['time_offset'] ) 					: false,
		'time_offset_days' 			=> isset( $fields['time_offset_days'] ) 			? intval( $fields['time_offset_days'] ) 									: 0,
		'time_offset_hours' 		=> isset( $fields['time_offset_hours'] ) 			? intval( $fields['time_offset_hours'] ) 									: 0,
		'time_offset_minutes' 	=> isset( $fields['time_offset_minutes'] ) 		? intval( $fields['time_offset_minutes'] ) 								: 0,
		'time_offset_seconds' 	=> isset( $fields['time_offset_seconds'] ) 		? intval( $fields['time_offset_seconds'] ) 								: 0,
		'time_offset_direction' => isset( $fields['time_offset_direction'] ) 	? sanitize_text_field( $fields['time_offset_direction'] )	: 'newer',
	);
	return $sanitized_fields;
}




/**
 * Renders a simple page to display for the theme menu defined above.
 *
 * @since 2.0
 */
function mtphr_post_duplicator_settings_display() {
	?>
	<div class="wrap">
	
		<h2><?php _e( 'Post Duplicator Settings', 'post-duplicator' ); ?></h2>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php
			settings_fields( 'mtphr_post_duplicator_settings' );
			do_settings_sections( 'mtphr_post_duplicator_settings' );
			submit_button();
			?>
		</form>

	</div><!-- /.wrap -->
	<?php
}




/**
 * The callback function for the settings sections.
 *
 * @since 2.0
 */ 
function mtphr_post_duplicator_settings_callback() {
	echo '<h4>' . esc_html__( 'Customize the settings for duplicated posts.', 'post-duplicator' ) . '</h4>';
}




/**
 * The custom field callback.
 *
 * @since 2.27
 */ 
function mtphr_post_duplicator_field_display( $args ) {
	$value = '';
	if( isset( $args['default'] ) ) {
		$value = sanitize_text_field( $args['default'] );
	}
	if( isset($args['type']) ) {
	
		echo '<div class="mtphr-post-duplicator-metaboxer-field mtphr-post-duplicator-metaboxer-' . esc_attr( $args['type'] ) . '">';
			
			// Call the function to display the field
			if ( function_exists('mtphr_post_duplicator_metaboxer_'. esc_attr( $args['type'] ) ) ) {
				call_user_func( 'mtphr_post_duplicator_metaboxer_'. esc_attr( $args['type'] ), $args, $value );
			}
		
		echo '<div>';
	}
	
	// Add a descriptions
	if( isset( $args['description'] ) ) {
		echo '<span class="description"><small>' . wp_kses_post( $args['description'] ) . '</small></span>';
	}
}

 

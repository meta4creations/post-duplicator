<?php
/**
 * Put all the Metaboxer admin function here fields here
 *
 * @package Ditty News Ticker
 * @author   Metaphor Creations
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.metaphorcreations.com/plugins/metatools
 * @version	 1.0.0
 */



/**
 * Create a field container and switch.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_container( $field, $context ) {

	global $post;

	$default = isset( $field['default'] ) ? $field['default'] : '';
	$value = ( get_post_meta( $post->ID, $field['id'], true ) != '' ) ? get_post_meta( $post->ID, $field['id'], true ) : $default;
	$display = isset( $field['display'] ) ? $field['display'] : '';
	?>
	<tr class="mtphr-post-duplicator-metaboxer-field mtphr-post-duplicator-metaboxer-field-<?php echo $field['type']; ?> mtphr-post-duplicator-metaboxer<?php echo $field['id']; ?><?php if( isset($field['class']) ) { echo ' '.$field['class']; } ?> clearfix">	
		
		<?php
		$content_class = 'mtphr-post-duplicator-metaboxer-field-content mtphr-post-duplicator-metaboxer-field-content-full mtphr-post-duplicator-metaboxer-'.$field['type'].' clearfix';
		$content_span = ' colspan="2"';
		$label = false;
		
		if ( isset($field['name']) || isset($field['description']) ) {
		
			$content_class = 'mtphr-post-duplicator-metaboxer-field-content mtphr-post-duplicator-metaboxer-'.$field['type'].' clearfix';
			$content_span = '';
			$label = true;
			?>

			<?php if( $context == 'side' || $display == 'vertical' ) { ?><td><table><tr><?php } ?>
			
			<td class="mtphr-post-duplicator-metaboxer-label">
				<?php if( isset($field['name']) ) { ?><label for="<?php echo $field['id']; ?>"><?php echo $field['name']; ?></label><?php } ?>
				<?php if( isset($field['description']) ) { ?><small><?php echo $field['description']; ?></small><?php } ?>
			</td>
			
			<?php if( $context == 'side' || $display == 'vertical' ) { echo '</tr>'; } ?>

			<?php
		}
		?>
		
		<?php if( $label ) { if( $context == 'side' || $display == 'vertical' ) { echo '<tr>'; } } ?>
		
		<td<?php echo $content_span; ?> class="<?php echo $content_class; ?>" id="<?php echo $post->ID; ?>">
			<?php
			// Call the function to display the field
			if ( function_exists('mtphr_post_duplicator_metaboxer_'.$field['type']) ) {
				call_user_func( 'mtphr_post_duplicator_metaboxer_'.$field['type'], $field, $value );
			}
			?>
		</td>
		
		<?php if( $label ) { if( $context == 'side' || $display == 'vertical' ) { echo '</tr></table></td>'; } } ?>
		
	</tr>
	<?php
}



/**
 * Add custom image sizes.
 *
 * @since 1.0.0
 */
if ( function_exists( 'add_image_size' ) ) { 

	// Create custom image sizes
	add_image_size( 'metaboxer-gallery-thumb', 120, 120, true );
}




add_action( 'wp_ajax_mtphr_post_duplicator_metaboxer_insert_attachment', 'mtphr_post_duplicator_metaboxer_insert_attachment' );
/**
 * Ajax function used to insert a single attachment
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_insert_attachment() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'mtphr_post_duplicator_metaboxer_ajax_file_nonce', 'security' );
	
	// Get variables
	$file_id  = $_POST['file_ids'];
	$file_type  = isset( $_POST['file_type'] ) ? $_POST['file_type'] : '';
	$file_size  = isset( $_POST['file_size'] ) ? $_POST['file_size'] : '';
	
	$output = '<li class="mtphr-post-duplicator-metaboxer-single-attachment" id="'.$file_id.'">'.wp_get_attachment_image( $file_id, ( $file_size != '' ) ? $file_size : 'thumbnail' );;
	$output .= '<div class="mtphr-post-duplicator-metaboxer-attachment-links">';
	//$output .= '<a href="'.$file_id.'" class="mtphr-post-duplicator-metaboxer-attachment-preview"></a>';
	$output .= '<a href="'.$file_id.'" class="mtphr-post-duplicator-metaboxer-attachment-settings"></a>';
	$output .= '<a href="'.$file_id.'" class="mtphr-post-duplicator-metaboxer-attachment-delete"></a>';
	$output .= '</div><li>';
	
	echo $output;

	die(); // this is required to return a proper result
}




add_action( 'wp_ajax_mtphr_post_duplicator_metaboxer_insert_attachments', 'mtphr_post_duplicator_metaboxer_insert_attachments' );
/**
 * Ajax function used to insert multiple attachments
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_insert_attachments() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'mtphr_post_duplicator_metaboxer_ajax_file_nonce', 'security' );
	
	// Get variables
	$file_ids  = $_POST['file_ids'];
	
	// Create an array of ids
	$files = explode( ',', $file_ids );
	
	// Create the new files
	foreach ( $files as $id ) {
		echo mtphr_post_duplicator_metaboxer_thumb( $id, false );
	}

	die(); // this is required to return a proper result
}




add_action( 'wp_ajax_mtphr_post_duplicator_metaboxer_delete_attachments', 'mtphr_post_duplicator_metaboxer_delete_attachments' );
/**
 * Ajax function used to delete attachments
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_delete_attachments() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'mtphr_post_duplicator_metaboxer_ajax_file_nonce', 'security' );
	
	// Get variables
	$file_ids  = $_POST['file_ids'];
	
	// Create an array of ids
	$files = explode( ',', $file_ids );
	
	// Delete the attachments
	foreach ( $files as $id ) {
		wp_delete_attachment( $id );
	}

	die(); // this is required to return a proper result
}


/**
 * Append fields
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_append_field( $field ) {

	// Add appended fields
	if( isset($field['append']) ) {
		
		$fields = $field['append'];
		$settings = ( isset($field['option'] ) ) ? $field['option'] : false;

		if( is_array($fields) ) {
		
			foreach( $fields as $id => $field ) {
				
				// Get the value
				if( $settings) {
					$options = get_option( $settings );
					$value = isset( $options[$id] ) ? $options[$id] : get_option( $id );	
				} else {
					global $post;
					$value = get_post_meta( $post->ID, $id, true );
				}
				
				// Set the default if no value
				if( $value == '' && isset($field['default']) ) {
					$value = $field['default'];
				}
	
				if( isset($field['type']) ) {
		
					if( $settings ) {
						$field['id'] = $settings.'['.$id.']';
						$field['option'] = $settings;
					} else {
						$field['id'] = $id;
					}
	
					// Call the function to display the field
					if ( function_exists('mtphr_post_duplicator_metaboxer_'.$field['type']) ) {
						echo '<div class="mtphr-post-duplicator-metaboxer-appended mtphr-post-duplicator-metaboxer'.$field['id'].'">';
						call_user_func( 'mtphr_post_duplicator_metaboxer_'.$field['type'], $field, $value );
						echo '</div>';
					}
				}
			}
		}
	}
}


 
/* Table of Contents

* text
* number
* textarea
* wysiwyg
* checkbox
* radio
* farbtastic
* image
* select
* image_select
* list
* sort
* html
* metabox toggle
* file
* gallery
* code

*/

/**
 * Renders an text field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_text( $field, $value='' ) {
	$size = ( isset($field['size']) ) ? $field['size'] : 40;
	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';
	$text_align = ( isset($field['text_align']) ) ? ' style="text-align:'.$field['text_align'].'"' : '' ;
	$output = $before.'<input name="'.$field['id'].'" id="'.$field['id'].'" type="text" value="'.$value.'" size="'.$size.'"'.$text_align.'>'.$after;
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders an number field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_number( $field, $value='' ) {
	$style = ( isset($field['style']) ) ? ' style="'.$field['style'].'"' : '';
	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';
	$output = $before.'<input name="'.$field['id'].'" id="'.$field['id'].'" type="number" value="'.$value.'" class="small-text"'.$style.'>'.$after;
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a textarea custom field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_textarea( $field, $value='' ) {
	$rows = ( isset($field['rows']) ) ? $field['rows'] : 5;
	$cols = ( isset($field['cols']) ) ? $field['cols'] : 40;
	$output = '<textarea name="'.$field['id'].'" id="'.$field['id'].'" rows="'.$rows.'" cols="'.$cols.'">'.$value.'</textarea>';
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a wysiwyg field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_wysiwyg( $field, $value='' ) {
	$settings = array();
	$settings['media_buttons'] = true;
	$settings['textarea_rows'] = ( isset($field['rows']) ) ? $field['rows'] : 12;
	wp_editor( $value, $field['id'], $settings );
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a checkbox custom field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_checkbox( $field, $value='' ) {

	$output = '';
	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';

	if( isset($field['options']) ) {
	
		$break = '<br/>';
		if ( isset($field['display']) ) {
			if( $field['display'] == 'inline' ) {
				$break = '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}
		foreach( $field['options'] as $i => $option ) {
			$checked = ( isset($value[$i]) ) ? 'checked="checked"' : '';
			$output .= '<label><input name="'.$field['id'].'['.$i.']" id="'.$field['id'].'['.$i.']" type="checkbox" value="1" '.$checked.' /> '.$option.'</label>'.$break;
		}
		
	} else {
		
		$checked = ( $value == 1 ) ? 'checked="checked"' : '';
		$output .= '<label><input name="'.$field['id'].'" id="'.$field['id'].'" type="checkbox" value="1" '.$checked.' />';
		if( isset($field['label']) ) {
			$output .= ' '.$field['label'];
		}	
		$output .= '</label>';
	}
	
	echo $before.$output.$after;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a radio custom field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_radio( $field, $value='' ) {
	
	if( isset($field['options']) ) {

		$output = '';
		$break = '<br/>';
		if ( isset($field['display']) ) {
			if( $field['display'] == 'inline' ) {
				$break = '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}
		foreach( $field['options'] as $i => $option ) {
			$checked = ( $value == $i ) ? 'checked="checked"' : '';
			$output .= '<label><input name="'.$field['id'].'" id="'.$field['id'].'" type="radio" value="'.$i.'" '.$checked.' /> '.$option.'</label>'.$break;
		}	
	}
	
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a farbtastic custom field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_farbtastic( $field, $value='' ) {
	$output = '<div class="farbtastic_cp" id="'.$field['id'].'_cp"></div>';
	$output .= '<input name="'.$field['id'].'" id="'.$field['id'].'" type="text" value="'.$value.'" />';
	$output .= '<a href="#" class="farbtastic-pick button">Pick Color</a>';
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}
 
/**
 * Renders an image field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_image( $field, $value=false ) {
	$button = isset( $field['button'] ) ? $field['button'] : 'Upload Image';
	$size = isset( $field['size'] ) ? $field['size'] : 'thumbnail';
	
	$value = ( $value ) ? $value : false;

	$output = '<div class="mtphr-post-duplicator-metaboxer-attachment-buttons"><a href="#" class="button mtphr-post-duplicator-metaboxer-upload-button">'.$button.'</a></div>';
	$output .= '<input class="mtphr-post-duplicator-metaboxer-attachment-limit" type="hidden" value="1" />';
	$output .= '<input class="mtphr-post-duplicator-metaboxer-attachment-size" type="hidden" value="'.$size.'" />';
	$output .= '<div class="mtphr-post-duplicator-metaboxer-input-container" id="'.$field['id'].'"></div>';
	$output .= '<ul id="'.wp_create_nonce( 'mtphr_post_duplicator_metaboxer_ajax_file_nonce' ).'" class="mtphr-post-duplicator-metaboxer-attachment-container clearfix">';
	if( $value ) {
		if( get_post($value[0]) ) {
			$output .= '<li id="'.$value[0].'" class="mtphr-post-duplicator-metaboxer-single-attachment">'.wp_get_attachment_image( $value[0], isset( $field['size'] ) ? $field['size'] : 'thumbnail' );
			$output .= '<div class="mtphr-post-duplicator-metaboxer-attachment-links">';
			//$output .= '<a href="'.$value[0].'" class="mtphr-post-duplicator-metaboxer-attachment-preview"></a>';
			$output .= '<a href="'.$value[0].'" class="mtphr-post-duplicator-metaboxer-attachment-settings"></a>';
			$output .= '<a href="'.$value[0].'" class="mtphr-post-duplicator-metaboxer-attachment-delete"></a>';
			$output .= '</div>';
			$output .= '</li>';
		}
	}
	$output .= '</ul>';
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a select field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_select( $field, $value='' ) {

	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';
	
	$output = $before.'<select name="'.$field['id'].'" id="'.$field['id'].'">';
	
  if( $field['options'] ) {
  
  	$key_val = isset( $field['key_val'] ) ? true : false;
  	
	  foreach ( $field['options'] as $key => $option ) {
	  	if( is_numeric($key) && !$key_val ) {
				$name = ( is_array( $option ) ) ? $option['name'] : $option;
				$val = ( is_array( $option ) ) ? $option['value'] : $option;
			} else {
				$name = $option;
				$val = $key;
			}
			$selected = ( $val == $value ) ? 'selected="selected"' : '';
			$output .= '<option value="'.$val.'" '.$selected.'>'.stripslashes( $name ).'</option>';
		}
	}
  $output .= '</select>'.$after;

	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders an image select
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_image_select( $field, $value='' ) {
	$output = '<input type="hidden" id="'.$field['id'].'" name="'.$field['id'].'" value="'.$value.'" />';
	foreach ( $field['options'] as $option ) {
		$selected = ( $value == $option['value'] ) ? 'selected' : '';
		$output .= '<a class="mtphr-post-duplicator-metaboxer-image-select-link '.$selected.'" href="'.$option['value'].'"><img src="'.$option['path'].'" /><small>'.$option['label'].'</small></a>';
	}
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a list.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_list( $field, $value='' ) {
		
	$output = '<table>';	
	
	$headers = false;
	$header_str = '';
	foreach( $field['structure'] as $id => $str ) {
	
		$header_str .= '<th>';
		if( isset($str['header']) ) {
			$headers = true;
			$header_str .= $str['header'];
		}
		$header_str .= '</th>';
	}
	if( $headers ) {
		$output .= '<tr><td class="mtphr-post-duplicator-metaboxer-list-item-handle"></td>'.$header_str.'</tr>';
	}
	
	$buttons = '<td class="mtphr-post-duplicator-metaboxer-list-item-delete"><a href="#">Delete</a></td><td class="mtphr-post-duplicator-metaboxer-list-item-add"><a href="#">Add</a></td>';
	if( is_array($value) ) {
		foreach( $value as $i=>$v ) {
			$structure = mtphr_post_duplicator_metaboxer_list_structure( $i, $field, $v );
			$output .= '<tr class="mtphr-post-duplicator-metaboxer-list-item"><td class="mtphr-post-duplicator-metaboxer-list-item-handle"><span></span></td>'.$structure.$buttons.'</tr>';
		}
	}
	
	// If nothing is being output make sure one field is showing
	if( $value == '' ) {
		$structure = mtphr_post_duplicator_metaboxer_list_structure( 0, $field );
		$output .= '<tr class="mtphr-post-duplicator-metaboxer-list-item"><td class="mtphr-post-duplicator-metaboxer-list-item-handle"><span></span></td>'.$structure.$buttons.'</tr>';
	}
	
	$output .= '</table>';
	
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Add the list structure
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_list_structure( $pos, $fields, $m_value='' ) {

	$main_id = $fields['id'];
	
	// Add appended fields
	if( isset($fields['structure']) ) {
		
		$fields = $fields['structure'];
		$settings = ( isset($fields['option'] ) ) ? $fields['option'] : false;

		if( is_array($fields) ) {
		
			ob_start();
			
			foreach( $fields as $id => $field ) {
				
				// Get the value
				$value = isset($m_value[$id]) ? $m_value[$id] : '';
				
				// Get the width
				$width = isset($field['width']) ? ' style="width:'.$field['width'].'"' : '';
	
				if( isset($field['type']) ) {
		
					$field['id'] = $main_id.'['.$pos.']['.$id.']';
	
					// Call the function to display the field
					if ( function_exists('mtphr_post_duplicator_metaboxer_'.$field['type']) ) {

						echo '<td'.$width.' class="mtphr-post-duplicator-metaboxer-list-structure-item mtphr-post-duplicator-metaboxer'.$main_id.'-'.$id.'" base="'.$main_id.'" field="'.$id.'">';
						call_user_func( 'mtphr_post_duplicator_metaboxer_'.$field['type'], $field, $value );
						echo '</td>';
					}
				}
			}
			
			return ob_get_clean();
		}
	}
}

/**
 * Renders a sort.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_sort( $field, $value='' ) {

	global $post;
	
	$rows = array();
	if( is_array($value) ) {
		foreach( $value as $id ) {
			$rows[$id] = $field['rows'][$id];
		}
	} else {
		$rows = $field['rows'];
	}
	
	$output = '<table>';	

	foreach( $rows as $id => $data ) {
	
		$output .= '<tr class="mtphr-post-duplicator-metaboxer-sort-item"><td class="mtphr-post-duplicator-metaboxer-sort-item-handle"><span></span></td>';
		if( isset($data['name']) ) {
			$output .= '<td class="mtphr-post-duplicator-metaboxer-sort-name">'.$data['name'].'</td>';
		}
		$output .= '<td><input name="'.$field['id'].'[]" id="'.$field['id'].'[]" type="hidden" value="'.$id.'">';
		
		// Find the value
		$data_value = get_post_meta( $post->ID, $data['id'], true );
		if( $data_value == '' && isset($data['default']) ) {
			$data_value = $data['default'];
		}
		
		ob_start();
		// Call the function to display the field
		if ( function_exists('mtphr_post_duplicator_metaboxer_'.$data['type']) ) {
			call_user_func( 'mtphr_post_duplicator_metaboxer_'.$data['type'], $data, $data_value );
		}
		$output .= ob_get_clean();
		
		$output .= '</td>';
		
		$output .= '</tr>';
	}
	
	$output .= '</table>';
	
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders an html field.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_html( $field, $value='' ) {
	
	// Echo the html
	echo $value;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Renders a metabox toggle.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_metabox_toggle( $field, $value='' ) {

	if( isset($field['options']) ) {
		
		$output = '';
		$output .= '<input type="hidden" id="'.$field['id'].'" name="'.$field['id'].'" value="'.$value.'" />';
		
		foreach( $field['options'] as $i => $option ) {
			
			$button = $option['button'];
			$metaboxes = $option['metaboxes'];
			$metabox_list = join( ',', $metaboxes );
			
			// Create a button
			$selected = ( $value == $i ) ? ' button-primary' : '';
			$output .= '<a href="'.$i.'" metaboxes="'.$metabox_list.'" class="mtphr-post-duplicator-metaboxer-metabox-toggle button'.$selected.'">'.$button.'</a>&nbsp;';
		}	
		
		echo $output;
	}
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}




/**
 * Redners a file attachment
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_file( $field, $value='' ) {
	
	// Check if there's actually a file
	$file = false;
	if( $value != '' ) {
		$file = get_post( $value );
	}
	
	// If there isn't a file reset the value
	if( !$file ) {
		$value = '';
	}
	?>
	
	<input class="mtphr-post-duplicator-metaboxer-file-value" type="hidden" id="<?php echo $field['id']; ?>" name="<?php echo $field['id']; ?>" value="<?php echo $value; ?>" />
	
	<?php
	echo isset( $field['button'] ) ? '<a href="#" class="button mtphr-post-duplicator-metaboxer-file-upload">'.$field['button'].'</a>' : '<a href="#" class="button custom-media-upload">Insert File</a>';
	
	if( $file ) {
	
		$type = explode( '/', $file->post_mime_type );

		// Display the file
		echo mtphr_post_duplicator_metaboxer_file_display( $file->ID, $type[0], $file->guid, $file->post_title, $file -> post_excerpt, $file->post_content );
	}

	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

add_action( 'wp_ajax_mtphr_post_duplicator_metaboxer_ajax_file_display', 'mtphr_post_duplicator_metaboxer_ajax_file_display' );
/**
 * Ajax function used to delete attachments
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_ajax_file_display() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'mtphr_dnt', 'security' );
	
	// Get variables
	$id  = $_POST['id'];
	$type  = $_POST['type'];
	$url  = $_POST['url'];
	$title  = $_POST['title'];
	$caption  = $_POST['caption'];
	$description  = $_POST['description'];
	
	// Display the file
	mtphr_post_duplicator_metaboxer_file_display( $id, $type, $url, $title, $caption, $description );
	
	die(); // this is required to return a proper result
}

// Display the file
function mtphr_post_duplicator_metaboxer_file_display( $id, $type, $url, $title, $caption, $description ) { 

	$src = '';
	switch( $type ) {
		
		case 'image':
			$att = wp_get_attachment_image_src( $id, 'thumbnail' );
			$src = $att[0];
			break;
			
		case 'application':
			$att = wp_get_attachment_image_src( $id, 'thumbnail', true );
			$src = $att[0];
			break;
	}
	?>
	<table class="mtphr-post-duplicator-metaboxer-file-table">
		<tr>
			<td class="mtphr-post-duplicator-metaboxer-file-display">
				<a href="<?php echo $url; ?>" target="_blank" class="clearfix">
					<img class="custom_media_image" src="<?php echo $src; ?>" />
					<span class="mtphr-post-duplicator-metaboxer-file-title"><strong>Title:</strong> <?php echo $title; ?></span><br/>
					<?php if( $caption != '' ) { ?>
					<span class="mtphr-post-duplicator-metaboxer-file-caption"><strong>Caption:</strong> <?php echo $caption; ?></span><br/>
					<?php }
					if( $description != '' ) { ?>
					<span class="mtphr-post-duplicator-metaboxer-file-description"><strong>Description:</strong> <?php echo $description; ?></span>
					<?php } ?>
				</a>
			</td>
			<td class="mtphr-post-duplicator-metaboxer-file-delete">
				<a href="#"></a>
			</td>
		</tr>
	</table>
	<?php
}




/**
 * Render a gallery attachment
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_gallery( $field, $value='' ) {
	
	/* Create the buttons */
	$button = isset( $field['button'] ) ? $field['button'] : 'Insert Media';
	
	$output = '<div class="mtphr-post-duplicator-metaboxer-attachment-buttons"><a href="#" class="button mtphr-post-duplicator-metaboxer-upload-button">'.$button.'</a></div>';
	
	/* If there is a file limit */
	if ( isset( $field['limit'] ) ) {
		$output .= '<input class="mtphr-post-duplicator-metaboxer-attachment-limit" type="hidden" value="'.$field['limit'].'" />';
	}
	$output .= '<div class="mtphr-post-duplicator-metaboxer-input-container" id="'.$field['id'].'"></div>';
	$output .= '<ul id="'.wp_create_nonce( 'mtphr_post_duplicator_metaboxer_ajax_file_nonce' ).'" class="mtphr-post-duplicator-metaboxer-gallery-attachment-container mtphr-post-duplicator-metaboxer-attachment-container clearfix">';
	
	/* Loop through the existing attachments */
	if( $value != '' ){
		foreach( $value as $id ) {
			$output .= mtphr_post_duplicator_metaboxer_thumb( $id );
		}
	}
	$output .= '</ul>';
	echo $output;
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}

/**
 * Create the gallery thumbnail containers.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_thumb( $id, $preview=true ) {

	$html = '';
	$attachment = get_post( $id );
	$nonce = wp_create_nonce( 'mtphr_post_duplicator_metaboxer_ajax_file_nonce' );

	if ( $attachment ) {
	
		$mime = $attachment->post_mime_type;

		$html = '<li id="'.$id.'" class="mtphr-post-duplicator-metaboxer-gallery-attachment mtphr-post-duplicator-metaboxer-sort-container clearfix">';

		switch ( $mime ) {
			
			case 'image/jpeg':
				$thumb = wp_get_attachment_image_src( $id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-image" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-image"></div>';
				break;
				
			case 'image/png':
				$thumb = wp_get_attachment_image_src( $id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-image" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-image"></div>';
				break;
				
			case 'application/pdf':
				$thumb = wp_get_attachment_image_src( $id, false, true );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-pdf" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-pdf"></div>';
				break;
				
			case 'application/zip':
				$thumb = wp_get_attachment_image_src( $id, false, true );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-zip" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-zip"></div>';
				break;
			
			case 'audio/mpeg':
				$image_id = get_post_meta( $id, '_attachment_poster_image', true );
				$default = ( !$image_id || $image_id=='none' ) ? true : false;
				$thumb = ( $default ) ? wp_get_attachment_image_src( $id, false, true ) : wp_get_attachment_image_src( $image_id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-audio" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-audio"></div>';
				break;
	
			case 'video/mp4':
				$image_id = get_post_meta( $id, '_attachment_poster_image', true );
				$default = ( !$image_id || $image_id=='none' ) ? true : false;
				$thumb = ( $default ) ? wp_get_attachment_image_src( $id, false, true ) : wp_get_attachment_image_src( $image_id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-video" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-video"></div>';
				break;
				
			case 'video/m4v':
				$image_id = get_post_meta( $id, '_attachment_poster_image', true );
				$default = ( !$image_id || $image_id=='none' ) ? true : false;
				$thumb = ( $default ) ? wp_get_attachment_image_src( $id, false, true ) : wp_get_attachment_image_src( $image_id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-video" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-video"></div>';
				break;
			
			case 'vimeo':
				$thumb = get_post_meta( $id, '_video_thumb_large', true );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-vimeo" style="background-image:url('.$thumb.');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-vimeo"></div>';
				break;
			
			case 'youtube':
				$thumb = get_post_meta( $id, '_video_thumb_large', true );
				$html .= '<div class="mtphr-post-duplicator-metaboxer-gallery-attachment-bg mtphr-post-duplicator-metaboxer-gallery-attachment-bg-youtube" style="background-image:url('.$thumb.');"></div>';
				$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-mime-type mtphr-post-duplicator-metaboxer-attachment-mime-type-youtube"></div>';
				break;
							
			default:
				// Call the function to display the custom field
				call_user_func( 'mtphr_post_duplicator_metaboxer_'.str_replace('%/%','_',$mime).'_thumb', $id, $nonce );
				$html .= $mime;
				break;
		}
		
		$html .= '<div class="mtphr-post-duplicator-metaboxer-attachment-links">';
		//$html .= '<a href="'.$id.'" rel="attachment_preview" class="mtphr-post-duplicator-metaboxer-attachment-preview"></a>';
		$html .= '<a href="'.$id.'" class="mtphr-post-duplicator-metaboxer-attachment-settings"></a>';
		$html .= '<a href="'.$id.'" class="mtphr-post-duplicator-metaboxer-attachment-delete"></a>';
		$html .= '</div>';
		
		$html .= '</li>';
	}

	return $html;
}




/**
 * Renders the code fields.
 *
 * @since 1.0.0
 */
function mtphr_post_duplicator_metaboxer_code( $field, $value='' ) {
	
	global $post;
	
	// Display the shortcode code
	if( $field['id'] == '_mtphr_post_duplicator_shortcode' ) {
	
		echo '<pre><p>[ditty_news_ticker id="'.$post->ID.'"]</p></pre>';
	
	// Display the function code
	} elseif( $field['id'] == '_mtphr_post_duplicator_function' ) {
		
		echo '<pre><p>ditty_news_ticker('.$post->ID.');</p></pre>';
	}
	
	// Display a "Select All" button
	$button = isset($field['button']) ? $field['button'] : __('Select Code', 'post-duplicator');
	echo '<a href="#" class="button mtphr-post-duplicator-metaboxer-code-select">'.$button.'</a>';
}


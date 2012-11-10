<?php
/**
 * Put all the Metaboxer admin function here fields here
 *
 * @package  Metaboxer
 * @author   Metaphor Creations
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.metaphorcreations.com/plugins/metatools
 * @version	 1.2
 */



/**
 * Create a field container and switch.
 *
 * @since 1.0
 */
function metaboxer_container( $field, $type='' ) {

	global $post;

	$default = isset( $field['default'] ) ? $field['default'] : '';
	$value = ( get_post_meta( $post->ID, $field['id'], true ) != '' ) ? get_post_meta( $post->ID, $field['id'], true ) : $default;
	?>
	<div class="metaboxer-field metaboxer-<?php echo $field['type']; ?><?php if( isset($field['layout']) ) { echo ' '.$field['layout']; } ?><?php if( isset($field['class']) ) { echo ' '.$field['class']; } ?> clearfix">	
		<div class="metaboxer-label">
			<label for="<?php echo $field['id']; ?>"><?php echo ( isset( $field['name'] ) ) ? $field['name'] : 'Please add a label!'; ?></label>
		<?php if( isset($field['description']) ) { ?><small><?php echo $field['description']; ?></small><?php } ?>
		</div>
		<div class="metaboxer-field-content metaboxer-<?php echo $field['type']; ?> clearfix" id="<?php echo $post->ID; ?>">
		<?php
		// Call the function to display the field
		if ( function_exists('metaboxer_'.$field['type']) ) {
			call_user_func( 'metaboxer_'.$field['type'], $field, $value );
		}
		?>
		</div>
	</div>
	<?php
}

/**
 * Add custom image sizes.
 *
 * @since 1.0
 */
if ( function_exists( 'add_image_size' ) ) { 

	// Create custom image sizes
	add_image_size( 'metaboxer-gallery-thumb', 120, 120, true );
}
 
/**
 * Ajax function used to insert a single attachment
 *
 * @since 1.0
 */
function metaboxer_insert_attachment() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'metaboxer_ajax_file_nonce', 'security' );
	
	// Get variables
	$file_id  = $_POST['file_ids'];
	$file_type  = isset( $_POST['file_type'] ) ? $_POST['file_type'] : '';
	$file_size  = isset( $_POST['file_size'] ) ? $_POST['file_size'] : '';
	
	$output = '<li class="metaboxer-single-attachment" id="'.$file_id.'">'.wp_get_attachment_image( $file_id, ( $file_size != '' ) ? $file_size : 'thumbnail' );;
	$output .= '<div class="metaboxer-attachment-links">';
	//$output .= '<a href="'.$file_id.'" class="metaboxer-attachment-preview"></a>';
	$output .= '<a href="'.$file_id.'" class="metaboxer-attachment-settings"></a>';
	$output .= '<a href="'.$file_id.'" class="metaboxer-attachment-delete"></a>';
	$output .= '</div><li>';
	
	echo $output;

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_metaboxer_insert_attachment', 'metaboxer_insert_attachment' );

/**
 * Ajax function used to insert multiple attachments
 *
 * @since 1.0
 */
function metaboxer_insert_attachments() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'metaboxer_ajax_file_nonce', 'security' );
	
	// Get variables
	$file_ids  = $_POST['file_ids'];
	
	// Create an array of ids
	$files = explode( ',', $file_ids );
	
	// Create the new files
	foreach ( $files as $id ) {
		echo metaboxer_thumb( $id, false );
	}

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_metaboxer_insert_attachments', 'metaboxer_insert_attachments' );

/**
 * Ajax function used to delete attachments
 *
 * @since 1.0
 */
function metaboxer_delete_attachments() {
	
	// Get access to the database
	global $wpdb;
	
	// Check the nonce
	check_ajax_referer( 'metaboxer_ajax_file_nonce', 'security' );
	
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
add_action( 'wp_ajax_metaboxer_delete_attachments', 'metaboxer_delete_attachments' );

/**
 * Append fields
 *
 * @since 1.2
 */
function metaboxer_append_field( $field ) {

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
					if ( function_exists('metaboxer_'.$field['type']) ) {
						echo '<div class="metaboxer-appended">';
						call_user_func( 'metaboxer_'.$field['type'], $field, $value );
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
* slider
* gallery
* matrix

*/

/**
 * Renders an text field.
 *
 * @since 1.2
 */
function metaboxer_text( $field, $value='' ) {
	$size = ( isset($field['size']) ) ? $field['size'] : 40;
	$before = ( isset($field['before']) ) ? $field['before'] : '';
	$after = ( isset($field['after']) ) ? $field['after'] : '';
	$text_align = ( isset($field['text_align']) ) ? ' style="text-align:'.$field['text_align'].'"' : '' ;
	$output = $before.'<input name="'.$field['id'].'" id="'.$field['id'].'" type="text" value="'.$value.'" size="'.$size.'"'.$text_align.'>'.$after;
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders an number field.
 *
 * @since 1.2
 */
function metaboxer_number( $field, $value='' ) {
	$size = ( isset($field['size']) ) ? $field['size'] : 10;
	$before = ( isset($field['before']) ) ? $field['before'] : '';
	$after = ( isset($field['after']) ) ? $field['after'] : '';
	$output = $before.'<input name="'.$field['id'].'" id="'.$field['id'].'" type="number" value="'.$value.'" class="small-text">'.$after;
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders a textarea custom field.
 *
 * @since 1.2
 */
function metaboxer_textarea( $field, $value='' ) {
	$rows = ( isset($field['rows']) ) ? $field['rows'] : 5;
	$cols = ( isset($field['cols']) ) ? $field['cols'] : 40;
	$output = '<textarea name="'.$field['id'].'" id="'.$field['id'].'" rows="'.$rows.'" cols="'.$cols.'">'.$value.'</textarea>';
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders a wysiwyg field.
 *
 * @since 1.2
 */
function metaboxer_wysiwyg( $field, $value='' ) {
	$settings = array();
	$settings['media_buttons'] = true;
	$settings['textarea_rows'] = ( isset($field['rows']) ) ? $field['rows'] : 12;
	wp_editor( $value, $field['id'], $settings );
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders a checkbox custom field.
 *
 * @since 1.2
 */
function metaboxer_checkbox( $field, $value='' ) {
	
	if( isset($field['options']) ) {
	
		$output = '';
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
		$output = '<label><input name="'.$field['id'].'" id="'.$field['id'].'" type="checkbox" value="1" '.$checked.' />';
		if( isset($field['label']) ) {
			$output .= ' '.$field['label'];
		}	
		$output .= '</label>';
	}
	
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders a radio custom field.
 *
 * @since 1.2
 */
function metaboxer_radio( $field, $value='' ) {
	
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
	metaboxer_append_field($field);
}

/**
 * Renders a farbtastic custom field.
 *
 * @since 1.2
 */
function metaboxer_farbtastic( $field, $value='' ) {
	$output = '<div class="farbtastic_cp" id="'.$field['id'].'_cp"></div>';
	$output .= '<input name="'.$field['id'].'" id="'.$field['id'].'" type="text" value="'.$value.'" />';
	$output .= '<a href="#" class="farbtastic-pick button">Pick Color</a>';
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}
 
/**
 * Renders an image field.
 *
 * @since 1.2
 */
function metaboxer_image( $field, $value=false ) {
	$button = isset( $field['button'] ) ? $field['button'] : 'Upload Image';
	$size = isset( $field['size'] ) ? $field['size'] : 'thumbnail';
	
	$value = ( $value ) ? $value : false;

	$output = '<div class="metaboxer-attachment-buttons"><a href="#" class="button metaboxer-upload-button">'.$button.'</a></div>';
	$output .= '<input class="metaboxer-attachment-limit" type="hidden" value="1" />';
	$output .= '<input class="metaboxer-attachment-size" type="hidden" value="'.$size.'" />';
	$output .= '<div class="metaboxer-input-container" id="'.$field['id'].'"></div>';
	$output .= '<ul id="'.wp_create_nonce( 'metaboxer_ajax_file_nonce' ).'" class="metaboxer-attachment-container clearfix">';
	if( $value ) {
		if( get_post($value[0]) ) {
			$output .= '<li id="'.$value[0].'" class="metaboxer-single-attachment">'.wp_get_attachment_image( $value[0], isset( $field['size'] ) ? $field['size'] : 'thumbnail' );
			$output .= '<div class="metaboxer-attachment-links">';
			//$output .= '<a href="'.$value[0].'" class="metaboxer-attachment-preview"></a>';
			$output .= '<a href="'.$value[0].'" class="metaboxer-attachment-settings"></a>';
			$output .= '<a href="'.$value[0].'" class="metaboxer-attachment-delete"></a>';
			$output .= '</div>';
			$output .= '</li>';
		}
	}
	$output .= '</ul>';
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders a select field.
 *
 * @since 1.2
 */
function metaboxer_select( $field, $value='' ) {
	$output = '<select name="'.$field['id'].'" id="'.$field['id'].'">';
	
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
  $output .= '</select>';
  if( isset($field['link']) ) {
  	$output .= ' '.$field['link'];
  }
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Renders an image select
 *
 * @since 1.2
 */
function metaboxer_image_select( $field, $value='' ) {
	$output = '<input type="hidden" id="'.$field['id'].'" name="'.$field['id'].'" value="'.$value.'" />';
	foreach ( $field['options'] as $option ) {
		$selected = ( $value == $option['value'] ) ? 'selected' : '';
		$output .= '<a class="metaboxer-image-select-link '.$selected.'" href="'.$option['value'].'"><img src="'.$option['path'].'" /><small>'.$option['label'].'</small></a>';
	}
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Render a slider
 *
 * @since 1.2
 */
function metaboxer_slider( $field, $value='' ) {
	
	extract( $field['options'] );
	$min = ( isset($min) ) ? $min : 0;
	$max = ( isset($max) ) ? $max : 100;
	$step = ( isset($step) ) ? $step : 5;
	$width = ( isset($width) ) ? $width : '500px';
	if( $value == '' ) {
		$value = ( isset($default) ) ? $default : 0;
	}
	
	$output = '';
	if( isset($label) ) {
		$output .= '<label for="'.$field['id'].'">'.$label.':</label>';
	}
	$output .= '<input type="text" id="'.$field['id'].'" name="'.$field['id'].'" class="slider-value" value="'.$value.'" />';

	$output .= '<div class="slider-settings">';
	$output .= '<input type="hidden" class="min" value="'.$min.'" />';
	$output .= '<input type="hidden" class="max" value="'.$max.'" />';
	$output .= '<input type="hidden" class="step" value="'.$step.'" />';
	$output .= '</div>';
	$output .= '<div style="width:'.$width.';" class="slider"></div>';

  echo $output;
  
  // Add appended fields
	metaboxer_append_field($field);
}

/**
 * Render a gallery attachment
 *
 * @since 1.2
 */
function metaboxer_gallery( $field, $value='' ) {
	
	/* Create the buttons */
	$button = isset( $field['button'] ) ? $field['button'] : 'Insert Media';
	
	$output = '<div class="metaboxer-attachment-buttons"><a href="#" class="button metaboxer-upload-button">'.$button.'</a></div>';
	
	/* If there is a file limit */
	if ( isset( $field['limit'] ) ) {
		$output .= '<input class="metaboxer-attachment-limit" type="hidden" value="'.$field['limit'].'" />';
	}
	$output .= '<div class="metaboxer-input-container" id="'.$field['id'].'"></div>';
	$output .= '<ul id="'.wp_create_nonce( 'metaboxer_ajax_file_nonce' ).'" class="metaboxer-gallery-attachment-container metaboxer-attachment-container clearfix">';
	
	/* Loop through the existing attachments */
	if( $value != '' ){
		foreach( $value as $id ) {
			$output .= metaboxer_thumb( $id );
		}
	}
	$output .= '</ul>';
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}

/**
 * Create the gallery thumbnail containers.
 *
 * @since 1.0
 */
function metaboxer_thumb( $id, $preview=true ) {

	$html = '';
	$attachment = get_post( $id );
	$nonce = wp_create_nonce( 'metaboxer_ajax_file_nonce' );

	if ( $attachment ) {
	
		$mime = $attachment->post_mime_type;

		$html = '<li id="'.$id.'" class="metaboxer-gallery-attachment metaboxer-sort-container clearfix">';

		switch ( $mime ) {
			
			case 'image/jpeg':
				$thumb = wp_get_attachment_image_src( $id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-image" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-image"></div>';
				break;
				
			case 'image/png':
				$thumb = wp_get_attachment_image_src( $id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-image" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-image"></div>';
				break;
				
			case 'application/pdf':
				$thumb = wp_get_attachment_image_src( $id, false, true );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-pdf" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-pdf"></div>';
				break;
				
			case 'application/zip':
				$thumb = wp_get_attachment_image_src( $id, false, true );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-zip" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-zip"></div>';
				break;
			
			case 'audio/mpeg':
				$image_id = get_post_meta( $id, '_attachment_poster_image', true );
				$default = ( !$image_id || $image_id=='none' ) ? true : false;
				$thumb = ( $default ) ? wp_get_attachment_image_src( $id, false, true ) : wp_get_attachment_image_src( $image_id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-audio" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-audio"></div>';
				break;
	
			case 'video/mp4':
				$image_id = get_post_meta( $id, '_attachment_poster_image', true );
				$default = ( !$image_id || $image_id=='none' ) ? true : false;
				$thumb = ( $default ) ? wp_get_attachment_image_src( $id, false, true ) : wp_get_attachment_image_src( $image_id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-video" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-video"></div>';
				break;
				
			case 'video/m4v':
				$image_id = get_post_meta( $id, '_attachment_poster_image', true );
				$default = ( !$image_id || $image_id=='none' ) ? true : false;
				$thumb = ( $default ) ? wp_get_attachment_image_src( $id, false, true ) : wp_get_attachment_image_src( $image_id, 'metaboxer-gallery-thumb' );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-video" style="background-image:url('.$thumb[0].');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-video"></div>';
				break;
			
			case 'vimeo':
				$thumb = get_post_meta( $id, '_video_thumb_large', true );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-vimeo" style="background-image:url('.$thumb.');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-vimeo"></div>';
				break;
			
			case 'youtube':
				$thumb = get_post_meta( $id, '_video_thumb_large', true );
				$html .= '<div class="metaboxer-gallery-attachment-bg metaboxer-gallery-attachment-bg-youtube" style="background-image:url('.$thumb.');"></div>';
				$html .= '<div class="metaboxer-attachment-mime-type metaboxer-attachment-mime-type-youtube"></div>';
				break;
							
			default:
				// Call the function to display the custom field
				call_user_func( 'metaboxer_'.str_replace('%/%','_',$mime).'_thumb', $id, $nonce );
				$html .= $mime;
				break;
		}
		
		$html .= '<div class="metaboxer-attachment-links">';
		//$html .= '<a href="'.$id.'" rel="attachment_preview" class="metaboxer-attachment-preview"></a>';
		$html .= '<a href="'.$id.'" class="metaboxer-attachment-settings"></a>';
		$html .= '<a href="'.$id.'" class="metaboxer-attachment-delete"></a>';
		$html .= '</div>';
		
		$html .= '</li>';
	}

	return $html;
}

/**
 * Renders a matrix
 *
 * @since 1.2
 */
function metaboxer_matrix( $field, $value='' ) {

	// Store the widths
	$widths = $field['widths'];
		
	// Set the button text
	$button = isset( $field['button'] ) ? $field['button'] : 'Add Row';
	
	// Create the headers
	$headers = '';
	if( isset($field['headers']) ) {
		$headers .= '<tr>';
		foreach ( $field['headers'] as $key => $header ) {
			$headers .= '<th width="'.$widths[$key].'">'.$header.'</th>';
		}
		$headers .= '</tr>';
	}
	
	// Set the widths as hidden inputs
	$output = '';
	foreach ( $widths as $i => $w ) {
		$output .= '<input type="hidden" class="matrix-width" value="'.$widths[$i].'">';
	}
	$max_width = isset( $field['table_width'] ) ? 'style="width:'.$field['table_width'].';"' : ''; 
	$output .= '<table cellpadding="0" cellspacing="0" '.$max_width.' id="'.$field['id'].'" class="metaboxer-matrix-table">';
	$output .= $headers;
	
	if( is_array($value) ){
		
		foreach( $value as $i => $row ) {
			$output .= '<tr class="metaboxer-sort-container">';
	
			// If there are multiple columns
			if ( is_array( $row ) ) {
			
				// Loop through the rows
				if( count( $widths ) > count( $row ) ) {
					$add = count( $widths ) - count( $row );
					for( $v=0; $v<$add; $v++ ) {
						$row[]='';
					}
				}
				foreach( $row as $e => $val ) {
					$output .= '<td width="'.$widths[$e].'%">';
					if( $e == 0 ) {
						$output .= '<a class="metaboxer-delete-row" href="#"></a>';
					}
					$output .= '<input name="'.$field['id'].'['.$i.'][]" type="text" value="'.stripslashes( $val ).'" />';
					$output .= '</td>';
				}
			
			// If there is only one column
			} else {
				$val = $row;
				$output .= '<td width="'.$widths[0].'%" class="clearfix">';
				$output .= '<a class="metaboxer-delete-row" href="#"></a>';
				$output .= '<input name="'.$field['id'].'['.$i.']" type="text" value="'.stripslashes( $val ).'" />';
				$output .= '</td>';
			}
			$output .= '</tr>';
		}
		$output .= '</table>';
		$output .= '<a href="#" class="button metaboxer-add-row-button" style="display:inline-block;margin-top:10px;">'.$button.'</a>';
	} else {
		/*
		$output .= '<tr class="metaboxer-sort-container">';
		foreach ( $widths as $i => $w ) {
			$output .= '<td width="'.$widths[$i].'%">';
			$output .= '<input name="'.$field['id'].'[0][]" type="text" value="" />';
			if( $i == count($widths)-1 ) {
				$output .= '<a class="metaboxer-delete-row" href="#">Delete</a>';
			} else {
				$output .= '<a class="metaboxer-spacer">&nbsp;</a>';
			}
			$output .= '</td>';
		}
		$output .= '</tr>';
		*/
		$output .= '</table>';
		$output .= '<a href="#" class="button metaboxer-add-row-button" style="display:inline-block;margin-top:10px;">'.$button.'</a>';
	}
	echo $output;
	
	// Add appended fields
	metaboxer_append_field($field);
}
<?php
/**
 * Put all the Metaboxer admin function here fields here
 *
 * @package  Post Duplicator
 * @author   Metaphor Creations
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 */



/**
 * Create a field container and switch.
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_container( $field, $context ) {

	global $post;

	$default = isset( $field['default'] ) ? sanitize_text_field( $field['default'] ) : '';
	$value = ( get_post_meta( $post->ID, $field['id'], true ) != '' ) ? sanitize_text_field( get_post_meta( $post->ID, $field['id'], true ) ) : $default;
	$display = isset( $field['display'] ) ? sanitize_text_field( $field['display'] ) : '';
	?>
	<tr class="mtphr-post-duplicator-metaboxer-field mtphr-post-duplicator-metaboxer-field-<?php esc_attr_e( $field['type'] ); ?> mtphr-post-duplicator-metaboxer<?php esc_attr_e( $field['id'] ); ?><?php if( isset($field['class']) ) { esc_attr_e( ' ' . $field['class'] ); } ?> clearfix">	
		
		<?php
		$content_class = 'mtphr-post-duplicator-metaboxer-field-content mtphr-post-duplicator-metaboxer-field-content-full mtphr-post-duplicator-metaboxer-'.esc_attr( $field['type'] ).' clearfix';
		$content_span = ' colspan="2"';
		$label = false;
		
		if ( isset($field['name']) || isset($field['description']) ) {
		
			$content_class = 'mtphr-post-duplicator-metaboxer-field-content mtphr-post-duplicator-metaboxer-'.esc_attr( $field['type'] ).' clearfix';
			$content_span = '';
			$label = true;
			?>

			<?php if( $context == 'side' || $display == 'vertical' ) { ?><td><table><tr><?php } ?>
			
			<td class="mtphr-post-duplicator-metaboxer-label">
				<?php if( isset($field['name']) ) { ?><label for="<?php esc_attr_e( $field['id'] ); ?>"><?php esc_html_e( $field['name'] ); ?></label><?php } ?>
				<?php if( isset($field['description']) ) { ?><small><?php esc_html_e( $field['description'] ); ?></small><?php } ?>
			</td>
			
			<?php if( $context == 'side' || $display == 'vertical' ) { echo '</tr>'; } ?>

			<?php
		}
		?>
		
		<?php if( $label ) { if( $context == 'side' || $display == 'vertical' ) { echo '<tr>'; } } ?>
		
		<td<?php esc_html_e( $content_span ); ?> class="<?php esc_attr_e( $content_class ); ?>" id="<?php esc_attr_e( $post->ID ); ?>">
			<?php
			// Call the function to display the field
			if ( function_exists('mtphr_post_duplicator_metaboxer_'.esc_attr( $field['type'] )) ) {
				call_user_func( 'mtphr_post_duplicator_metaboxer_'.esc_attr( $field['type'] ), $field, $value );
			}
			?>
		</td>
		
		<?php if( $label ) { if( $context == 'side' || $display == 'vertical' ) { echo '</tr></table></td>'; } } ?>
		
	</tr>
	<?php
}




/**
 * Append fields
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_append_field( $field ) {

	// Add appended fields
	if( isset($field['append']) ) {
		
		$fields = $field['append'];

		if( is_array($fields) ) {
		
			foreach( $fields as $id => $field ) {
	
				if( isset($field['type']) ) {
					
					// Set the default if no value
					$value = '';
					if( isset($field['default']) ) {
						$value = sanitize_text_field( $field['default'] );
					}
					
					$field['option_id'] = $id;
					$field['id'] = 'mtphr_post_duplicator_settings['.sanitize_text_field( $id ).']';
	
					// Call the function to display the field
					$function_name  = 'mtphr_post_duplicator_metaboxer_' . esc_attr( $field['type'] );
					if ( function_exists( $function_name ) ) {
						echo '<div class="mtphr-post-duplicator-metaboxer-appended mtphr-post-duplicator-metaboxer' . esc_attr( $field['id'] ) . '">';
						call_user_func( $function_name, $field, $value );
						echo '</div>';
					}
				}
			}
		}
	}
}



/**
 * Renders a select field.
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_select( $field, $value='' ) {

	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';
	
	echo wp_kses_post( $before ) . '<select name="'.esc_attr( $field['id'] ).'" id="'.esc_attr( $field['id'] ).'">';
	
  if( $field['options'] ) {
  
  	$key_val = isset( $field['key_val'] ) ? true : false;
  	
	  foreach ( $field['options'] as $key => $option ) {
	  	if( is_numeric($key) && !$key_val ) {
				$name = ( is_array( $option ) ) ? sanitize_text_field( $option['name'] ) : sanitize_text_field( $option );
				$val = ( is_array( $option ) ) ? sanitize_text_field( $option['value'] ) : sanitize_text_field( $option );
			} else {
				$name = sanitize_text_field( $option );
				$val = sanitize_text_field( $key );
			}
			echo '<option value="'.esc_attr( $val ).'" '.selected( $val, $value, false ).'>'.stripslashes( wp_kses_post( $name ) ).'</option>';
		}
	}
  echo '</select>' .  wp_kses_post( $after );
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}



/**
 * Renders a radio custom field.
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_radio( $field, $value='' ) {
	
	if( isset($field['options']) ) {

		$break = '<br/>';
		if ( isset($field['display']) ) {
			if( $field['display'] == 'inline' ) {
				$break = '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}
		foreach( $field['options'] as $i => $option ) {
			echo '<label><input name="'.esc_attr( $field['id'] ).'" id="'.esc_attr( $field['id'] ).'" type="radio" value="'.esc_attr( $i ).'" '.checked( $value, $i, false ).' /> '.wp_kses_post( $option ).'</label>'.wp_kses_post( $break );
		}	
	}
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}



/**
 * Renders a checkbox.
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_checkbox( $field, $value='' ) {

	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';

	if( isset($field['options']) ) {
		
		echo wp_kses_post( $before );
	
		$break = '<br/>';
		if ( isset($field['display']) ) {
			if( $field['display'] == 'inline' ) {
				$break = '&nbsp;&nbsp;&nbsp;&nbsp;';
			}
		}
		foreach( $field['options'] as $i => $option ) {
			
			echo '<label><input name="'.esc_attr( $field['id'] ).'['.esc_attr( $i ).']" id="'.esc_attr( $field['id'] ).'['.esc_attr( $i ).']" type="checkbox" value="1" '.checked( $value[$i], '1', false ).' /> '.wp_kses_post( $option ).'</label>'.wp_kses_post( $break );
		}
		
		echo wp_kses_post( $after );
		
	} else {
		
		echo wp_kses_post( $before );

		echo '<label><input name="'.esc_attr( $field['id'] ).'" id="'.esc_attr( $field['id'] ).'" type="checkbox" value="1" '.checked( $value, '1', false ).' />';
		if( isset($field['label']) ) {
			echo ' '.wp_kses_post( $field['label'] );
		}	
		echo '</label>';
		
		echo wp_kses_post( $after );
	}
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}



/**
 * Renders an text field.
 *
 * @since 2.27
 */
function mtphr_post_duplicator_metaboxer_text( $field, $value='' ) {
	$size = ( isset( $field['size'] ) ) ? intval( $field['size'] ) : 40;
	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';
	$text_align = ( isset($field['text_align']) ) ? ' style="text-align:'.$field['text_align'].'"' : '' ;
	echo wp_kses_post( $before ).'<input name="'.esc_attr( $field['id'] ).'" id="'.esc_attr( $field['id'] ).'" type="text" value="'.esc_attr( $value ).'" size="'.esc_attr( $size ).'"'.wp_kses_post( $text_align ).'>'.wp_kses_post( $after );

	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}



/**
 * Renders a textarea.
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_textarea( $field, $value='' ) {
	$rows = ( isset($field['rows']) ) ? intval( $field['rows'] ) : 5;
	$cols = ( isset($field['cols']) ) ? intval( $field['cols'] ) : 40;
	echo '<textarea name="'.esc_attr( $field['id'] ).'" id="'.esc_attr( $field['id'] ).'" rows="'.esc_attr( $rows ).'" cols="'.esc_attr( $cols ).'">'.wp_kses_post( $value ).'</textarea>';
	
	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}



/**
 * Renders an number field.
 *
 * @since 2.25
 */
function mtphr_post_duplicator_metaboxer_number( $field, $value='' ) {
	$style = ( isset($field['style']) ) ? ' style="'.$field['style'].'"' : '';
	$before = ( isset($field['before']) ) ? '<span>'.$field['before'].' </span>' : '';
	$after = ( isset($field['after']) ) ? '<span> '.$field['after'].'</span>' : '';
	echo wp_kses_post( $before ).'<input name="'.esc_attr( $field['id'] ).'" id="'.esc_attr( $field['id'] ).'" type="number" value="'.esc_attr( $value ).'" class="small-text"'.wp_kses_post( $style ).'>'.wp_kses_post( $after );

	// Add appended fields
	mtphr_post_duplicator_metaboxer_append_field($field);
}




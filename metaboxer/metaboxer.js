/**
 * Setup variables.
 *
 * @since 1.0
 */
var $metaboxer_admin_attachment_container;
var $metaboxer_admin_input_container;
var $temp_container = [];
var $temp_attachment = [];
var metaboxer_checked_array;
var metaboxer_files = false;




/**
 * Initialize the script.
 *
 * @since 1.0
 */
function metaboxer_admin_initialize_script() {

	var $iframe = jQuery( '#TB_iframeContent', parent.document.body );
	
	if ( $iframe.length > 0 ) {	
		var $body = $iframe.parent().parent();
		$temp_container = $body.find( '.metaboxer-temp-file-container' );
		$temp_attachment = $body.find( '.metaboxer-attachment-settings-current-edit' );
	}
	
	// If adding files to custom fields
	if ( $temp_container.length > 0 ) {
		metaboxer_multiple_file_selection();
	}
	
	// If editing an attachment
	if ( $temp_attachment.length > 0 ) {
		metaboxer_edit_attachment( $temp_attachment.attr('href') );
	}

	// Loop through the attachment fields
	jQuery( '.metaboxer-gallery, .metaboxer-attachments, .metaboxer-image' ).each( function( index ) {
		// Set the inputs
		metaboxer_admin_set_attachment_inputs( jQuery( this ).find( '.metaboxer-attachment-container' ), jQuery( this ).find( '.metaboxer-input-container' ) );
	});
	
	// Add jQuery sorting
	metaboxer_add_sortables();
	
	// Add jQuery sliders
	metaboxer_add_sliders();
	
	// Add Farbtastic colorpicker
	metaboxer_add_farbtastic();

	// Create the checked array
	metaboxer_checked_array = [];
}




/**
 * Add multiple file selectiont to the uploader.
 *
 * @since 1.0
 */
function metaboxer_multiple_file_selection() {
	
	// Loop through the media items and add a checkbox
	jQuery( '.media-item .new' ).each( function( index ) {
	
		// Add a checkbox
		jQuery( this ).prepend( '<input style="margin-right: 10px;" type="checkbox" class="metaboxer-attachment-checkbox" />' );
	} );
	
	// Add an 'insert selected' button
	if ( jQuery( 'form#image-form' ).length == 0 ) {
		jQuery( '.ml-submit:first' ).append( '<a class="button" id="metaboxer-attachment-select-all">Select All</a> <a class="button" id="metaboxer-attachment-deselect-all">De-Select All</a><br/><br/><a class="button" id="metaboxer-insert-selected">Insert Selected</a> <a style="text-decoration:underline;padding-left:5px;" id="metaboxer-delete-selected">Delete Selected</a>' );
	}
}




/**
 * Check file limits for attachment import.
 *
 * @since 1.0
 */
function metaboxer_check_file_limit( $limit ) {

	var file_limit = $limit.val();
	var $button_container = $limit.siblings( '.metaboxer-attachment-buttons' );
	var $attachment_container = $limit.siblings( '.metaboxer-attachment-container' );
	var total_files = $attachment_container.children( '.metaboxer-gallery-attachment, .metaboxer-single-attachment' ).length;
	
	if ( $attachment_container.parent().hasClass( 'metaboxer-image' ) ) {
		file_limit = 1;
	}
	
	if ( total_files < file_limit ) {
		$button_container.show();
	} else {
		$button_container.hide();
	}
}




/**
 * Add sortable functionality.
 *
 * @since 1.0
 */
function metaboxer_add_sortables() {

	// Loop through the gallery containers and enable sorting
	jQuery( '.metaboxer-gallery-attachment-container' ).each( function(index) {
		jQuery( this).sortable( {
			handle: '.metaboxer-gallery-attachment-bg',
			items: '.metaboxer-gallery-attachment',
			update: function( event, ui ) {
				// Set the order of the files
				var $input_container = jQuery( this ).siblings( '.metaboxer-input-container' );
				metaboxer_admin_set_attachment_inputs( jQuery( this ), $input_container );
			}
		});
	});
	
	// Loop through the attachment containers and enable sorting
	jQuery( '.metaboxer-matrix-table' ).each( function(index) {

		jQuery( this).children('tbody').sortable( {
			axis: 'y',
			helper: function(e, tr) {
		    var $originals = tr.children();
		    var $helper = tr.clone();
		    $helper.children().each(function(index) {
		      // Set helper cell sizes to match the original sizes
		      jQuery(this).width($originals.eq(index).width())
		    });
		    return $helper;
		  },
		  activate: function(event, ui) {
		  }
		});
	});
}




/**
 * Add slider functionality.
 *
 * @since 1.0
 */
function metaboxer_add_sliders() {

	// Loop through the gallery containers and enable sorting
	jQuery( '.metaboxer-slider' ).each( function(index) {
	
		// Get the variables
		var $slider = jQuery( this ).children( '.slider' );
		var $input = jQuery( this ).children( '.slider-value' );
		var $settings = jQuery( this ).children( '.slider-settings' );
		var min = parseInt( $settings.children('.min').val() );
		var max = parseInt( $settings.children('.max').val() );
		var step = parseInt( $settings.children('.step').val() );

		$slider.slider({
			value: $input.val(),
			min: min,
			max: max,
			step: step,
			value: $input.val(),
			slide: function( event, ui ) {
				$input.val( ui.value );
			}
		});	
	});
}




/**
 * Add farbtastic.
 *
 * @since 1.0
 */
function metaboxer_add_farbtastic() {
	
	// Loop through the colorpicker inputs
	jQuery( '.farbtastic_cp' ).each( function( index ) {
		jQuery( this ).farbtastic( jQuery( this ).next() );
	} );
}




/**
 * Re-order and set the attachment inputs.
 *
 * @since 1.0
 */
function metaboxer_admin_set_attachment_inputs( $attachment_container, $input_container, tb_remove ) {

	var $iframe = jQuery( '#TB_iframeContent', parent.document.body );
	var $body = $iframe.parent().parent();
	
	// Create an input template
	var id = $input_container.attr( 'id' );
	var input_template = '<input class="metaboxer-attachment-id" type="hidden" name="'+id+'[]" id="'+id+'" value="" />';
	
	// Remove all the hidden inputs
	$input_container.empty();
	
	// Remove any empty li
	jQuery( $attachment_container.children('li:empty') ).remove();
	
	// Loop through the attachments
	jQuery( $attachment_container.children( 'li' ) ).each( function( index ) {

		// If there isn't an error, save the input
		if ( jQuery( this ).find( '.metaboxer-attachment-error' ).length == 0 ) {
			// Get the attachment id
			var id = jQuery( this ).attr( 'id' );
			
			// Set & add the input
			var $input = jQuery( input_template );
			$input.val( id );
			$input_container.append( $input );
		}
	} );

	// Check for file limits
	if ( $attachment_container.siblings( '.metaboxer-attachment-limit' ).length > 0 || $attachment_container.parent().hasClass( 'metaboxer-image' ) ) {
		metaboxer_check_file_limit( $attachment_container.siblings( '.metaboxer-attachment-limit' ) );
	}
	
	if ( tb_remove ) {
		var $overlay = $body.find( '#TB_overlay' );
		var $window = $body.find( '#TB_window' );
		// Remove the thickbox
		$iframe.remove();
		$overlay.remove();
		$window.remove();
	}
}



/**
 * Go directly to a specific attachment to edit.
 *
 * @since 1.0
 */
function metaboxer_edit_attachment( id ) {
	$att = jQuery( '#media-item-'+id );
	if( $att.length > 0 ) {
		$att.children('.describe-toggle-on').trigger('click');
	}
}








/**
 * Reset all the temp classes.
 *
 * @since 1.0
 */
function metaboxer_reset_temp_classes() {
	jQuery( '.metaboxer-temp-file-container' ).removeClass( 'metaboxer-temp-file-container' );
	jQuery( '.metaboxer-attachment-settings-current-edit' ).removeClass( 'metaboxer-attachment-settings-current-edit' );
}




/**
 * Add event listeners.
 *
 * @since 1.0
 */
function metaboxer_admin_addEventListeners() {
	
	
	/**
	 * Attachment checkbox listener
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-attachment-checkbox' ).live( 'click', function(e) {
	
		// Reset the array
		metaboxer_checked_array = [];

		// Loop through the selected checkboxes
		jQuery( '.metaboxer-attachment-checkbox' ).each( function(index) {

			// If the checkbox is checked
			if ( jQuery(this).attr('checked') ) {
				
				// Get the attachment id
				var id = jQuery( this ).parent().parent().attr( 'id' ).substr( 11 );
				
				// Add the id to the array
				metaboxer_checked_array.push( id );	
			}	
		});
	});
	
	
	
	/**
	 * Select all attachment checkboxes.
	 *
	 * @since 1.0
	 */
	jQuery( '#metaboxer-attachment-select-all' ).live( 'click', function(e) {
		
		// Prevent the default action
		e.preventDefault();
		
		// Select all checkboxes
		jQuery( '.metaboxer-attachment-checkbox' ).attr( 'checked', true );
		
		// Reset the array
		metaboxer_checked_array = [];
		
		// Loop through the selected checkboxes
		jQuery( '.metaboxer-attachment-checkbox' ).each( function(index) {

			// If the checkbox is checked
			if ( jQuery(this).attr('checked') ) {
				
				// Get the attachment id
				var id = jQuery( this ).parent().parent().attr( 'id' ).substr( 11 );
				
				// Add the id to the array
				metaboxer_checked_array.push( id );	
			}	
		});
	});
	
	
	
	/**
	 * De-select all attachment checkboxes.
	 *
	 * @since 1.0
	 */
	jQuery( '#metaboxer-attachment-deselect-all' ).live( 'click', function(e) {
		
		// Prevent the default action
		e.preventDefault();
		
		// De-Select all checkboxes
		jQuery( '.metaboxer-attachment-checkbox' ).attr( 'checked', false );
		
		// Reset the array
		metaboxer_checked_array = [];	
	});
	
	
	
	/**
	 * Insert the attachments with selected checkboxes.
	 *
	 * @since 1.0
	 */
	jQuery( '#metaboxer-insert-selected' ).live( 'click', function(e) {
		
		// Prevent the default action
		e.preventDefault();
		
		var $iframe = jQuery( '#TB_iframeContent', parent.document.body );
		var $body = $iframe.parent().parent();
		var $attachment_container = $body.find( '.metaboxer-temp-file-container' );
		var $input_container = $attachment_container.siblings( '.metaboxer-input-container' );
		var nonce = $attachment_container.attr( 'id' );
		
		// Check for file limits
		if ( $attachment_container.siblings( '.metaboxer-attachment-limit' ).length > 0 ) {
			var file_limit = $attachment_container.siblings( '.metaboxer-attachment-limit' ).val();
			var curr_files = $attachment_container.children( '.metaboxer-admin-attachment' ).length;
			var files_left = file_limit - curr_files;
			if ( metaboxer_checked_array.length > files_left ) metaboxer_checked_array.length = files_left;	
		}

		// Save a file_id string
		var file_ids = '';
		for ( var i=0; i<metaboxer_checked_array.length; i++ ) {
			file_ids += metaboxer_checked_array[i] + ',';
		}
		file_ids = file_ids.substring( 0, file_ids.length - 1 );
		
		if( metaboxer_checked_array.length != 0 ) {
	
			var action = 'metaboxer_insert_attachments';
			var type = '';
			var size = '';
			
			if ( $attachment_container.parent().hasClass( 'metaboxer-image' ) ) {
				action = 'metaboxer_insert_attachment';
				type = 'image';
				size = $input_container.prev().val();
			}

			// Create the data to pass
			var data = {
				action: action,
				file_ids: file_ids,
				file_type: type,
				file_size: size,
				security: nonce
			};
	
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function( response ) {
						
				// Append the new data
				$attachment_container.prepend( response );
	
				// Save the file inputs
				metaboxer_admin_set_attachment_inputs( $attachment_container, $input_container, true );
			});
		}
	});
	
	
	
	
	/**
	 * Bypass the Insert Into Post default behavior.
	 * Insert the selected attachment into the post.
	 *
	 * @since 1.0
	 */
	jQuery( 'input[value="Insert into Post"]' ).live('click', function(e) {
		
		if ( $temp_container.length > 0 ) {
			
			e.preventDefault();
			
			// Get the attachment id
			var val = jQuery( this ).attr( 'id' ).substr( 5 );
			file_ids = val.slice( 0,-1 );
			
			var $iframe = jQuery( '#TB_iframeContent', parent.document.body );
			var $body = $iframe.parent().parent();
			var $attachment_container = $body.find( '.metaboxer-temp-file-container' );
			var $input_container = $attachment_container.siblings( '.metaboxer-input-container' );
			var nonce = $attachment_container.attr( 'id' );
			
			// Check for file limits
			if ( $attachment_container.siblings( '.metaboxer-attachment-limit' ).length > 0 ) {
				var file_limit = $attachment_container.siblings( '.metaboxer-attachment-limit' ).val();
				var curr_files = $attachment_container.children( '.metaboxer-admin-attachment' ).length;
				var files_left = file_limit - curr_files;
				if ( 1 > files_left ) metaboxer_checked_array.length = files_left;	
			}

			var action = 'metaboxer_insert_attachments';
			var type = '';
			var size = '';
			
			if ( $attachment_container.parent().hasClass( 'metaboxer-image' ) ) {
				action = 'metaboxer_insert_attachment';
				type = 'image';
				size = $input_container.prev().val();
			}

			// Create the data to pass
			var data = {
				action: action,
				file_ids: file_ids,
				file_type: type,
				file_size: size,
				security: nonce
			};
	
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			jQuery.post( ajaxurl, data, function( response ) {
				
				// Append the new data
				$attachment_container.prepend( response );
	
				// Save the file inputs
				metaboxer_admin_set_attachment_inputs( $attachment_container, $input_container, true );
			});
		}
	});
	
		
	
	
	/**
	 * Delete the attachments with selected checkboxes.
	 *
	 * @since 1.0
	 */
	jQuery( '#metaboxer-delete-selected' ).live( 'click', function(e) {
		
		// Prevent the default action
		e.preventDefault();
		
		var $iframe = jQuery( '#TB_iframeContent', parent.document.body );
		var $body = $iframe.parent().parent();
		var $attachment_container = $body.find( '.metaboxer-temp-file-container' );
		var $input_container = $attachment_container.siblings( '.metaboxer-input-container' );
		var nonce = $attachment_container.attr( 'id' );

		// Save a file_id string
		var file_ids = '';
		for ( var i=0; i<metaboxer_checked_array.length; i++ ) {
			file_ids += metaboxer_checked_array[i] + ',';
		}
		file_ids = file_ids.substring( 0, file_ids.length - 1 );
		
		if( metaboxer_checked_array.length != 0 ) {
		
			// Confirm deletions
			delete_image = confirm( 'Are you sure you want to delete these attachments?' );
			if( delete_image ) {
				
				// Remove the attachments from the page & reset the inputs
				for ( var i=0; i<metaboxer_checked_array.length; i++ ) {
					$attachment_container.find('#metaboxer_'+metaboxer_checked_array[i]).remove();
				}
				metaboxer_admin_set_attachment_inputs( $attachment_container, $input_container );

				var data = {
					action: 'metaboxer_delete_attachments',
					file_ids: file_ids,
					security: nonce
				};
				jQuery.post( ajaxurl, data, function( response ) {
					// Refresh the page	
					location.reload();
				});
			}
		}
	});	
	
	
	
	
	/**
	 * Delete a single attachment.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-attachment-delete' ).live('click', function( e ) {
	
		// Prevent the default
		e.preventDefault();
		
		// Save the containers
		var $parent = jQuery( this ).parent().parent();
		var $attachment_container = $parent.parent();
		var $input_container = $attachment_container.siblings( '.metaboxer-input-container' );
		
		// Get the attachment id & nonce
		var id = jQuery(this).attr('href');
		var nonce = $attachment_container.attr( 'id' );
		var delete_image = false;

		// Check if the user wants to completely delete the image
		delete_image = confirm( 'Do you want to permanently delete this image from the Media Library as well?' );

		if ( delete_image ) {
		
			// Set the opacity
			$parent.css( 'opacity', '.3' );
		
			// Create the data to pass via jQuery
			var data = {
				action: 'metaboxer_delete_attachments',
				file_ids: id,
				security: nonce
			};
			jQuery.post( ajaxurl, data, function( response ) {
				// Delete the attachment & reset the inputs
				$parent.slideUp( 'fast', function() {
					$parent.remove();
					metaboxer_admin_set_attachment_inputs( $attachment_container, $input_container );
				} );		 
			});
		} else {
			// Remove the attachment & reset the inputs
			$parent.slideUp( 'fast', function() {
				$parent.remove();
				metaboxer_admin_set_attachment_inputs( $attachment_container, $input_container );
			});
		}
	});
	
	
	
	
	/**
	 * Add a new row to the matrix.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-add-row-button' ).click( function( e ) {
	
		// Prevent the default
		e.preventDefault();
		
		// Save the containers
		var $table = jQuery( this ).siblings( 'table' );
		
		// Get the option id
		var id = $table.attr( 'id' );
		
		// Save the widths
		var widths = $table.siblings( '.matrix-width' );
		
		// Get the number of values
		var num_rows = $table.children( 'tbody' ).children( 'tr' ).length;
		var count = widths.length;
	
		// Add a new row
		var html = '<tr class="metaboxer-sort-container">';
		for ( var i=0; i<count; i++ ) {
			html += '<td width="'+jQuery(widths[i]).val()+'">';
			html += '<a class="metaboxer-delete-row" href="#"></a>';
			if ( count > 1 ) {
				html += '<input name="'+id+'['+num_rows+'][]" type="text" value="" />';
			} else {
				html += '<input name="'+id+'['+num_rows+']" type="text" value="" />';
			}
			html += '</td>';
		}
		html += '</tr>';
		
		if( $table.children( 'tbody' ).length == 0 ) {
			$table.append( jQuery('<tbody>'+html+'</tbody>') );
		} else {
			$table.children( 'tbody' ).append( html );
		}
	});

	
	
	
	/**
	 * Delete a row from the matrix.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-delete-row' ).live( 'click', function(e) {
	
		// Prevent the default
		e.preventDefault();
		
		// Save the containers
		var $parent = jQuery( this ).parent().parent();
		var $table = $parent.parent().parent();
		
		// Get the option id
		var id = $table.attr( 'id' );

		// Remove the attachment
		$parent.fadeOut( 'fast', function(){
			
			$parent.remove();
			
			// Reset the input names
			var i = 0;
			$table.children( 'tbody' ).children( 'tr' ).each( function(index) {
				
				if ( jQuery( this ).children( 'td' ).length > 0 ) {
					if ( jQuery( this ).children( 'td' ).length > 1 ) {
						jQuery( this ).children( 'td' ).each( function( index ) {
							jQuery( this ).children( 'input' ).attr( 'name', id+'['+i+'][]' );
						} );
					} else {
						jQuery( this ).children( 'td' ).each( function( index ) {
							jQuery( this ).children( 'input' ).attr( 'name', id+'['+i+']' );
						} );
					}
					i++;
				}
			});
		});
	});


	
	
	/**
	 * Open the media uploader.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-upload-button' ).click( function() {
		
		// Reset the temp holders
		metaboxer_reset_temp_classes();
		
		// Save the input & attachment containers & variables
		$metaboxer_admin_input_container = jQuery( this ).parent().siblings( '.metaboxer-input-container' );
		$metaboxer_admin_attachment_container = $metaboxer_admin_input_container.siblings( '.metaboxer-attachment-container' );
		var nonce = $metaboxer_admin_attachment_container.attr( 'id' );
		var post_id = jQuery( '#post_ID' ).val();
		var reset_send_to_editor = window.send_to_editor;
		
		// Add a temp class to the container
		jQuery( this ).parent().addClass( 'metaboxer-temp-button-container' );
		$metaboxer_admin_attachment_container.addClass( 'metaboxer-temp-file-container' );
	
		// Show the thickbox
		if ( !post_id ) {
			tb_show( '', 'media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true' );
		} else {
			tb_show( '', 'media-upload.php?post_id='+post_id+'&amp;type=image&amp;TB_iframe=true' );
		}
		
		window.send_to_editor = function( html ) {
	
			// Add the new image area
			$metaboxer_admin_attachment_container.append( html );
	
			// Set & re-order inputs
			metaboxer_admin_set_attachment_inputs( $metaboxer_admin_attachment_container, $metaboxer_admin_input_container );
	
			// Remove the thickbox
			tb_remove();
			
			// Reset the function
			window.send_to_editor = reset_send_to_editor;
		}
		
		return false;
	});
	
	
	
	
	/**
	 * Update the selected image select.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-image-select-link' ).click( function(e) {
	
		e.preventDefault();
		
		// Get the value
		var val = jQuery( this ).attr( 'href' );
		
		// Save the value to the input
		jQuery( this ).siblings( 'input[type="hidden"]' ).val( val );
		
		// Set & remove selected
		jQuery( this ).siblings( '.metaboxer-image-select-link' ).removeClass( 'selected' );
		jQuery( this ).addClass( 'selected' );
	});
		
	
	
	
	/**
	 * Select or de-select all sibling checkboxes.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-field-content' ).children( 'label' ).children( 'input[type="checkbox"]' ).click( function() {
		
		// Save objects
		$parent = jQuery( this ).parent();
		$g_parent = $parent.parent();
		$checkboxes = $g_parent.children( 'label' ).children( 'input[type="checkbox"]' );

		// If this is a check all button
		if ( $parent.children( '.metaboxer-small' ).text() == ' * Check All *' ) {
			if ( jQuery( this ).attr( 'checked' ) == 'checked' ) {
				$checkboxes.each( function( index ) {
					jQuery( this ).attr( 'checked', 'checked' );
				});
			} else {
				$checkboxes.each( function( index ) {
					jQuery( this ).removeAttr('checked');
				});
			}
		} else {
			if ( jQuery( this ).attr( 'checked' ) != 'checked' ) {
				$checkboxes.each( function( index ) {	
					// Uncheck the check all
					if ( jQuery( this ).parent().children( '.metaboxer-small' ).text() == ' * Check All *' ) {
						jQuery( this ).removeAttr('checked');
					}
				});
			}
		}
	});
	
	
	/**
	 * Open thickbox to the attachment to edit.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-attachment-settings' ).live( 'click', function( e ) {
	
		e.preventDefault();
	
		// Reset the temp holders
		metaboxer_reset_temp_classes();
		
		// Save the input & attachment containers & variables
		$metaboxer_admin_attachment_container = jQuery( this ).parent().parent().parent();
		$metaboxer_admin_input_container = $metaboxer_admin_attachment_container.siblings( '.metaboxer-input-container' );
		
		var nonce = $metaboxer_admin_attachment_container.attr( 'id' );
		var post_id = jQuery( '#post_ID' ).val();
		var reset_send_to_editor = window.send_to_editor;
		
		// Add a temp class to the container
		$metaboxer_admin_attachment_container.addClass( 'metaboxer-temp-file-container' );
		jQuery(this).addClass( 'metaboxer-attachment-settings-current-edit' );
	
		// Show the thickbox
		if ( !post_id ) {
			tb_show( '', 'media-upload.php?post_id=0&amp;type=image&amp;tab=library&amp;post_mime_type=all&amp;TB_iframe=true' );
		} else {
			tb_show( '', 'media-upload.php?post_id='+post_id+'&amp;type=image&amp;TB_iframe=true' );
		}
	});
	
	
	
	
	/**
	 * Show or hide the farbtastic colorpicker.
	 *
	 * @since 1.0
	 */
	jQuery( '.farbtastic-pick' ).click( function( e ) {
		e.preventDefault();
		if( jQuery(this).hasClass('selected') ) {
			jQuery(this).prev().prev().hide();
			jQuery(this).text('Pick Color');
			jQuery(this).removeClass('selected');
		} else {
			jQuery(this).prev().prev().show();
			jQuery(this).text('Hide Color');
			jQuery(this).addClass('selected');
		}
	});
	
	
	
	
	/**
	 * Show the selected pattern.
	 *
	 * @since 1.0
	 */
	jQuery( '.metaboxer-pattern_select' ).find( 'select' ).change( function( e ) {
		var $selection = jQuery(this).next();
		var url = jQuery(this).val();
		if( url == 'none' ) {
			$selection.slideUp( function() {
				$selection.css( 'background-image', 'none' );
			});
		} else {
			$selection.css( 'background-image', 'url('+jQuery(this).val()+')' );
			$selection.slideDown();
		}
	});




  /**
	 * Clear the temp classes when the default WordPress
	 * buttons are clicked.
	 *
	 * @since 1.0
	 */	
	jQuery( '#media-buttons' ).children( 'a' ).click( function() {
		metaboxer_reset_temp_classes();
	});
	jQuery( '#set-post-thumbnail' ).click( function() {
		metaboxer_reset_temp_classes();
	});
	jQuery( '#content-add_media' ).click( function( e ) {
		metaboxer_reset_temp_classes();
	});
}



/**
 * Document ready listener.
 *
 * @since 1.0
 */
jQuery( document ).ready( function() {
	
	// Initialize the packages
	metaboxer_admin_initialize_script();
	
	// Add event listeners
	metaboxer_admin_addEventListeners();	
});
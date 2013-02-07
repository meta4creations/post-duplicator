/* Table of Contents

* File
* Lists
* Code
* Metabox toggle

*/




jQuery(document).ready( function($) {



	
	/**
	 * Add file functionality.
	 *
	 * @since 1.0.0
	 */
	if( $('.mtphr-post-duplicator-metaboxer-file').length > 0 ) {
		mtphr_post_duplicator_metaboxer_files();
	}
	function mtphr_post_duplicator_metaboxer_files() {
	
		// Loop through all files to initialize
		$('.mtphr-post-duplicator-metaboxer-file').each( function(index) {
			
			// If there currently isn't a value, show the upload button
			if( $(this).find('.mtphr-post-duplicator-metaboxer-file-value').val() == '' ) {
				$(this).find('.mtphr-post-duplicator-metaboxer-file-upload').css('display','inline-block');
			}
		});
		
		// Custom media upload functionality
		$('.mtphr-post-duplicator-metaboxer-file-upload').click(function() {
			
			// Save the container
			var $container = $(this).parent('.mtphr-post-duplicator-metaboxer-field-content');
			
		  var send_attachment_bkp = wp.media.editor.send.attachment;
		
		  wp.media.editor.send.attachment = function( props, attachment ) {

		  	// Set the field value
		  	$container.find('.mtphr-post-duplicator-metaboxer-file-value').val(attachment.id);
		  	
		  	// Create the display
				var data = {
					action: 'mtphr_post_duplicator_metaboxer_ajax_file_display',
					id: attachment.id,
					type: attachment.type,
					url: attachment.url,
					title: attachment.title,
					caption: attachment.caption,
					description: attachment.description,
					security: mtphr_post_duplicator_metaboxer_vars.security
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post( ajaxurl, data, function( response ) {
							
					// Append the new data
					$container.append( response );
					
					// Hide the upload button
					$container.find('.mtphr-post-duplicator-metaboxer-file-upload').hide();
				});
	
	      wp.media.editor.send.attachment = send_attachment_bkp;
		  }
		  
		  wp.media.editor.open();

		  return false;       
		});
		
		$('.mtphr-post-duplicator-metaboxer-file-delete').live('click',function() {
			
			// Save the container
			var $container = $(this).parents('.mtphr-post-duplicator-metaboxer-field-content');
			
			// Remove the field value
		  $container.find('.mtphr-post-duplicator-metaboxer-file-value').val('');
			
			// Remove the current display
			$container.find('.mtphr-post-duplicator-metaboxer-file-table').remove();
			
			// Disply the upload button
			$container.find('.mtphr-post-duplicator-metaboxer-file-upload').css('display','inline-block');
		});
	}


	

	/**
	 * Add list functionality.
	 *
	 * @since 1.0.0
	 */
	if( $('.mtphr-post-duplicator-metaboxer-list').length > 0 ) {
		mtphr_post_duplicator_metaboxer_lists();
	}
	function mtphr_post_duplicator_metaboxer_lists() {
	
		// Loop through all lists to initialize
		$('.mtphr-post-duplicator-metaboxer-list').each( function(index) {

			// Set the field order
			mtphr_post_duplicator_metaboxer_lists_set_order( $(this) );
			
			// Add sorting to the items
			$(this).sortable( {
				handle: '.mtphr-post-duplicator-metaboxer-list-item-handle',
				items: '.mtphr-post-duplicator-metaboxer-list-item',
				axis: 'y',
				helper: function(e, tr) {
			    var $originals = tr.children();
			    var $helper = tr.clone();
			    $helper.children().each(function(index) {
			      // Set helper cell sizes to match the original sizes
			      $(this).width($originals.eq(index).width())
			    });
			    return $helper;
			  },
			  update: function( event, ui ) {
					
					// Set the field order
					mtphr_post_duplicator_metaboxer_lists_set_order( $(this) );
				}
			});
		});
		
		// Set the list item order
		function mtphr_post_duplicator_metaboxer_lists_set_order( $list ) {
	
			// Set the order of the items
			$list.find('.mtphr-post-duplicator-metaboxer-list-item').each( function(i) {
				
				$(this).find('.mtphr-post-duplicator-metaboxer-list-structure-item').each( function(e) {
					
					var base = $(this).attr('base');
					var field = $(this).attr('field');
					$(this).find('input,textarea,select').attr('name', base+'['+i+']['+field+']');
				});
			});
			
			// Hide the delete if only one element
			if( $list.find('.mtphr-post-duplicator-metaboxer-list-item').length == 1 ) {
				
				$list.find('.mtphr-post-duplicator-metaboxer-list-item-handle,.mtphr-post-duplicator-metaboxer-list-item-delete').hide();
			}
		}
		
		// Add item click
		$('.mtphr-post-duplicator-metaboxer-list-item-add').children('a').click( function(e) {
			e.preventDefault();
			
			// Create a new item with blank content
			var $parent = $(this).parents('.mtphr-post-duplicator-metaboxer-list-item');
			var $new = $parent.clone(true).hide();
			$new.find('input,textarea,select').removeAttr('value').removeAttr('checked').removeAttr('selected');
			$parent.after($new);
			$new.fadeIn();
			
			// Set the field order
			mtphr_post_duplicator_metaboxer_lists_set_order( $(this).parents('.mtphr-post-duplicator-metaboxer-list') );
			
			// Show the handles
			$(this).parents('.mtphr-post-duplicator-metaboxer-list').find('.mtphr-post-duplicator-metaboxer-list-item-handle,.mtphr-post-duplicator-metaboxer-list-item-delete').show();
			
			// Set the focus to the new input
			var inputs = $new.find('input,textarea,select');
			$(inputs[0]).focus();
		});
		
		// Delete item click
		$('.mtphr-post-duplicator-metaboxer-list-item-delete').children('a').click( function(e) {
			e.preventDefault();
			
			// Fade out the item
			$(this).parents('.mtphr-post-duplicator-metaboxer-list-item').fadeOut( function() {
				
				// Get the list
				var $list = $(this).parents('.mtphr-post-duplicator-metaboxer-list');
				
				// Remove the item
				$(this).remove();
				
				// Set the field order
				mtphr_post_duplicator_metaboxer_lists_set_order( $list );
			});
		});
	}
	
	
	
	
	/**
	 * Add list functionality.
	 *
	 * @since 1.0.0
	 */
	if( $('.mtphr-post-duplicator-metaboxer-sort').length > 0 ) {
		mtphr_post_duplicator_metaboxer_sorts();
	}
	function mtphr_post_duplicator_metaboxer_sorts() {
	
		// Loop through all sorts to initialize
		$('.mtphr-post-duplicator-metaboxer-sort').each( function(index) {

			// Set the field order
			//mtphr_post_duplicator_metaboxer_sorts_set_order( $(this) );
			
			// Add sorting to the items
			$(this).sortable( {
				handle: '.mtphr-post-duplicator-metaboxer-sort-item-handle',
				items: '.mtphr-post-duplicator-metaboxer-sort-item',
				axis: 'y',
				helper: function(e, tr) {
			    var $originals = tr.children();
			    var $helper = tr.clone();
			    $helper.children().each(function(index) {
			      // Set helper cell sizes to match the original sizes
			      $(this).width($originals.eq(index).width())
			    });
			    return $helper;
			  },
			  update: function( event, ui ) {
					
					// Set the field order
					//mtphr_post_duplicator_metaboxer_sorts_set_order( $(this) );
				}
			});
		});
		
		// Set the list item order
		/*
function mtphr_post_duplicator_metaboxer_sorts_set_order( $sort ) {
	
			// Set the order of the items
			$list.find('.mtphr-post-duplicator-metaboxer-list-item').each( function(i) {
				
				$(this).find('.mtphr-post-duplicator-metaboxer-list-structure-item').each( function(e) {
					
					var base = $(this).attr('base');
					var field = $(this).attr('field');
					$(this).find('input,textarea,select').attr('name', base+'['+i+']['+field+']');
				});
			});
			
			// Hide the delete if only one element
			if( $list.find('.mtphr-post-duplicator-metaboxer-list-item').length == 1 ) {
				
				$list.find('.mtphr-post-duplicator-metaboxer-list-item-handle,.mtphr-post-duplicator-metaboxer-list-item-delete').hide();
			}
		}
*/
	}
	
	
	
	
	/**
	 * Add code functionality.
	 *
	 * @since 1.0.0
	 */
	if( $('.mtphr-post-duplicator-metaboxer-code').length > 0 ) {
		mtphr_post_duplicator_metaboxer_codes();
	}
	function mtphr_post_duplicator_metaboxer_codes() {
		
		// Select the code on button click
		$('.mtphr-post-duplicator-metaboxer-code-select').click( function(e) {
			e.preventDefault();
			
			var $pre = $(this).parents('.mtphr-post-duplicator-metaboxer-code').find('pre');
			var refNode = $pre[0];
			if ( jQuery.browser.msie ) {
				var range = document.body.createTextRange();
				range.moveToElementText( refNode );
				range.select();
			} else if ( jQuery.browser.mozilla || jQuery.browser.opera ) {
				var selection = window.getSelection();
				var range = document.createRange();
				range.selectNodeContents( refNode );
				selection.removeAllRanges();
				selection.addRange( range );
			} else if ( jQuery.browser.safari || jQuery.browser.chrome ) {
				var selection = window.getSelection();
				selection.setBaseAndExtent( refNode, 0, refNode, 1 );
			}
		});
	}
	
	
	
	
	/**
	 * Add metabox toggle functionality.
	 *
	 * @since 1.0.0
	 */
	if( $('.mtphr-post-duplicator-metaboxer-field-metabox_toggle').length > 0 ) {
		mtphr_post_duplicator_metaboxer_metabox_toggles();
	}
	function mtphr_post_duplicator_metaboxer_metabox_toggles() {
	
		$('.mtphr-post-duplicator-metaboxer-field-metabox_toggle').each( function(index) {

			// Create an array to store all the toggled metaboxes
			var metaboxes = Array();
			$(this).find('.mtphr-post-duplicator-metaboxer-metabox-toggle').each( function(index) {
				
				// Get the metaboxes and merge into the main array
				var m = $(this).attr('metaboxes').split(',');
				$.merge( metaboxes, m );
			});
			var total_metaboxes = metaboxes.length;
			
			// Hide the toggled metaboxes
			mtphr_post_duplicator_metaboxer_metabox_hide();
			
			// Display the current metaboxes
			if( $(this).find('.mtphr-post-duplicator-metaboxer-metabox-toggle.button-primary').length > 0 ) {
				$init_button = $(this).find('.mtphr-post-duplicator-metaboxer-metabox-toggle.button-primary');
			} else {
				$init_button = $(this).find('.mtphr-post-duplicator-metaboxer-metabox-toggle:first');
				$init_button.addClass('button-primary');
			}
			mtphr_post_duplicator_metaboxer_metabox_show( $init_button );
			
			// Hide the toggled metaboxes
			function mtphr_post_duplicator_metaboxer_metabox_hide() {
				for( var i=0; i<total_metaboxes; i++ ) {
					$('#'+metaboxes[i]).hide();
					$('input[name="'+metaboxes[i]+'-hide"]').removeAttr('checked');
				}
			}
			
			// Show the selected metaboxes
			function mtphr_post_duplicator_metaboxer_metabox_show( $button ) {
				
				// Get and display the selected metaboxes
				var m = $button.attr('metaboxes').split(',');
				var t = m.length;
				
				// Show all the toggled metaboxes
				for( var i=0; i<t; i++ ) {
					$('#'+m[i]).show();
					$('input[name="'+m[i]+'-hide"]').attr('checked', 'checked');
				}
				
				// Store the new value
				$button.siblings('input').val($button.attr('href'));
			}
			
			// Select the code on button click
			$(this).find('.mtphr-post-duplicator-metaboxer-metabox-toggle').click( function(e) {
				e.preventDefault();
	
				// Hide all the toggled metaboxes
				mtphr_post_duplicator_metaboxer_metabox_hide();
				
				// Show the selected metaboxes
				mtphr_post_duplicator_metaboxer_metabox_show( $(this) );
	
				// Set the button classes
				$(this).siblings('.mtphr-post-duplicator-metaboxer-metabox-toggle').removeClass('button-primary');
				$(this).addClass('button-primary');
			});
		});
	}





});
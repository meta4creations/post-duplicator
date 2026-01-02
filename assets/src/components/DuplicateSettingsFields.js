import { useState, useEffect, useRef } from '@wordpress/element';
import {
	SelectControl,
	TextControl,
	CheckboxControl,
	DateTimePicker,
	Dropdown,
	Popover,
	Button,
	Flex,
	FlexBlock,
	FlexItem,
	__experimentalNumberControl as NumberControl,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
	DropZone,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { calendar, trash } from '@wordpress/icons';

// Try to import MediaUpload components, but they may not be available on post list screen
// Use a function to check availability at runtime
const getMediaUploadComponents = () => {
	try {
		// Try to dynamically import - this will work if the module is available
		const blockEditor = require( '@wordpress/block-editor' );
		
		// Verify the components are actually functions/components, not just truthy
		if ( 
			blockEditor && 
			blockEditor.MediaUpload && 
			blockEditor.MediaUploadCheck &&
			typeof blockEditor.MediaUpload === 'function' &&
			typeof blockEditor.MediaUploadCheck === 'function'
		) {
			return {
				MediaUpload: blockEditor.MediaUpload,
				MediaUploadCheck: blockEditor.MediaUploadCheck,
			};
		}
	} catch ( e ) {
		// MediaUpload not available - will use wp.media fallback
	}
	// Always return an object, even if components are null
	return { MediaUpload: null, MediaUploadCheck: null };
};

const DuplicateSettingsFields = ( {
	settings,
	onSettingsChange,
	postTypes,
	statusChoices,
	originalPost,
	featuredImage,
	onFeaturedImageChange,
} ) => {
	// Initialize settings with proper type and status values
	// If default is "same", use original post's value, otherwise use default
	const getInitialSettings = () => {
		const initial = { ...settings };

		// Handle type: if "same", use original post type
		if ( settings.type === 'same' && originalPost?.type ) {
			initial.type = originalPost.type;
		}

		// Handle status: if "same", use original post status
		if ( settings.status === 'same' && originalPost?.status ) {
			initial.status = originalPost.status;
		}

		return initial;
	};

	const [ localSettings, setLocalSettings ] = useState(
		getInitialSettings()
	);
	const [ slugManuallyEdited, setSlugManuallyEdited ] = useState( false );
	const [ fullTitle, setFullTitle ] = useState( '' );
	const [ fullSlug, setFullSlug ] = useState( '' );
	const [ initialized, setInitialized ] = useState( false );

	// Get users list and current user from localized data
	const usersList = window.postDuplicatorVars?.users || [];
	const currentUser = window.postDuplicatorVars?.currentUser;
	const postTypesAuthorSupport = window.postDuplicatorVars?.postTypesAuthorSupport || {};
	const postTypesHierarchicalSupport = window.postDuplicatorVars?.postTypesHierarchicalSupport || {};

	// Ensure original post type is always in the dropdown options, even if not enabled
	const getPostTypesForDropdown = () => {
		const types = { ...postTypes };
		
		// Always include the original post's type if it exists and isn't already in the list
		if ( originalPost?.type && ! types[ originalPost.type ] ) {
			// Get the post type object to get its label
			const allPostTypes = window.postDuplicatorVars?.allPostTypes || [];
			const postTypeObj = allPostTypes.find( 
				pt => pt.id === originalPost.type 
			);
			
			if ( postTypeObj ) {
				types[ originalPost.type ] = postTypeObj.label;
			} else {
				// Fallback: capitalize the post type slug
				const fallbackLabel = originalPost.type
					.split( '_' )
					.map( word => word.charAt( 0 ).toUpperCase() + word.slice( 1 ) )
					.join( ' ' );
				types[ originalPost.type ] = fallbackLabel;
			}
		}
		
		// Sort alphabetically by label (keep 'same' at the top if it exists)
		const sortedTypes = {};
		if ( types.same ) {
			sortedTypes.same = types.same;
			delete types.same;
		}
		
		// Sort remaining types by label
		const entries = Object.entries( types ).sort( ( a, b ) => {
			return a[1].localeCompare( b[1], undefined, { sensitivity: 'base' } );
		} );
		
		// Rebuild object with sorted entries
		entries.forEach( ( [ key, value ] ) => {
			sortedTypes[ key ] = value;
		} );
		
		return sortedTypes;
	};

	// Check if current post type supports authors
	const getCurrentPostType = () => {
		return localSettings.type === 'same' ? originalPost?.type : localSettings.type;
	};

	const currentPostTypeSupportsAuthor = () => {
		const postType = getCurrentPostType();
		return postTypesAuthorSupport[ postType ] !== false; // Default to true if not specified
	};

	const currentPostTypeIsHierarchical = () => {
		const postType = getCurrentPostType();
		return postTypesHierarchicalSupport[ postType ] === true; // Default to false if not specified
	};

	// Initialize selectedAuthorId with a valid default
	// If post type doesn't support authors, default to "No Author" (empty string)
	const getDefaultAuthorId = () => {
		const postType = originalPost?.type;
		const supportsAuthor = postTypesAuthorSupport[ postType ] !== false;
		
		if ( ! supportsAuthor ) {
			return ''; // "No Author"
		}
		
		return currentUser?.id
			? String( currentUser.id )
			: usersList.length > 0
			? usersList[ 0 ].value
			: '';
	};

	const [ selectedAuthorId, setSelectedAuthorId ] =
		useState( getDefaultAuthorId() );
	const [ selectedParentId, setSelectedParentId ] = useState( '' );
	const [ parentPosts, setParentPosts ] = useState( [] );
	const [ postDate, setPostDate ] = useState( new Date().toISOString() );
	const [ dateInputValue, setDateInputValue ] = useState( '' );
	const [ isDatePickerOpen, setIsDatePickerOpen ] = useState( false );
	const dateInputRef = useRef( null );
	const dateButtonRef = useRef( null );
	
	// Check if MediaUpload is available at runtime
	const { MediaUpload, MediaUploadCheck } = getMediaUploadComponents();
	
	// Detect if we're on the post list screen (not in block editor)
	// MediaUpload components may not work properly outside the block editor context
	// Check if the editor store is available (only in block editor)
	let isPostListScreen = true;
	try {
		if ( typeof window.wp !== 'undefined' && 
			 window.wp.data && 
			 typeof window.wp.data.select === 'function' ) {
			const editorStore = window.wp.data.select( 'core/editor' );
			isPostListScreen = ! editorStore || typeof editorStore.getCurrentPost !== 'function';
		}
	} catch ( e ) {
		// If we can't access the editor store, assume we're on post list screen
		isPostListScreen = true;
	}
	

	// Sanitize slug using WordPress-style sanitization
	const sanitizeSlug = ( text ) => {
		return text
			.toLowerCase()
			.trim()
			.replace( /\s+/g, '-' ) // Replace spaces with hyphens
			.replace( /[^\w\-]/g, '' ) // Remove non-word chars except hyphens
			.replace( /\-\-+/g, '-' ) // Replace multiple hyphens with single hyphen
			.replace( /^-+/, '' ) // Trim hyphens from start
			.replace( /-+$/, '' ); // Trim hyphens from end
	};

	// Generate slug from title
	const generateSlugFromTitle = ( title ) => {
		return sanitizeSlug( title );
	};

	const handleChange = ( field, value ) => {
		const newSettings = { ...localSettings, [ field ]: value };

		// If changing timestamp to not-custom, clear customDate
		if ( field === 'timestamp' && value !== 'custom' ) {
			newSettings.customDate = null;
		}

		setLocalSettings( newSettings );
		onSettingsChange( newSettings );
	};

	// Initialize full title and slug ONLY on mount or when originalPost changes
	useEffect( () => {
		if ( ! initialized && originalPost?.title && originalPost?.slug ) {
			// Set initial full title
			const initialTitle =
				settings.fullTitle ||
				`${ originalPost.title } ${ settings.title || '' }`;
			setFullTitle( initialTitle.trim() );

			// Set initial full slug
			const initialSlug =
				settings.fullSlug ||
				`${ originalPost.slug }-${ settings.slug || 'copy' }`;
			setFullSlug( initialSlug );

			// Batch update: pass both initial title and slug to parent together
			const newSettings = {
				...settings,
				fullTitle: initialTitle.trim(),
				fullSlug: initialSlug,
			};
			setLocalSettings( newSettings );
			onSettingsChange( newSettings );

			// Set initial author based on default settings and post type author support
			const postType = originalPost?.type;
			const supportsAuthor = postTypesAuthorSupport[ postType ] !== false;
			
			let initialAuthorId = '';
			if ( supportsAuthor ) {
				initialAuthorId =
					settings.post_author === 'current_user'
						? String( currentUser?.id || '' )
						: String( originalPost.authorId || currentUser?.id || '' );
			}
			// If post type doesn't support authors, initialAuthorId remains '' (No Author)
			setSelectedAuthorId( initialAuthorId );

			// Set initial date based on timestamp setting
			let initialDate;
			let shouldApplyOffset = false;
			
			if ( settings.customDate ) {
				// Use custom date if provided (offset already applied in multiple clone mode)
				initialDate = settings.customDate;
				// Don't apply offset again if customDate is provided
				shouldApplyOffset = false;
			} else if ( settings.timestamp === 'duplicate' && originalPost?.date ) {
				// Use original post's date if timestamp is set to 'duplicate'
				initialDate = originalPost.date;
				shouldApplyOffset = true;
			} else {
				// Default to current time
				initialDate = new Date().toISOString();
				shouldApplyOffset = true;
			}
			
			// Apply time offset if enabled (only if we're not using a pre-calculated customDate)
			if ( settings.time_offset && shouldApplyOffset ) {
				const date = new Date( initialDate );
				const offsetMilliseconds = 
					( settings.time_offset_days || 0 ) * 86400000 +
					( settings.time_offset_hours || 0 ) * 3600000 +
					( settings.time_offset_minutes || 0 ) * 60000 +
					( settings.time_offset_seconds || 0 ) * 1000;
				
				if ( settings.time_offset_direction === 'newer' ) {
					date.setTime( date.getTime() + offsetMilliseconds );
				} else {
					date.setTime( date.getTime() - offsetMilliseconds );
				}
				
				initialDate = date.toISOString();
			}
			
			setPostDate( initialDate );
			setDateInputValue( formatDateDisplay( initialDate ) );

			// Set initial parent
			const initialParentId = originalPost?.parent || 0;
			setSelectedParentId( initialParentId > 0 ? String( initialParentId ) : '' );
			handleChange( 'selectedParentId', initialParentId > 0 ? initialParentId : null );

			setInitialized( true );
		}
	}, [ originalPost, initialized, settings, currentUser ] );

	// Fetch parent posts when post type changes
	useEffect( () => {
		const fetchParentPosts = async () => {
			if ( ! originalPost?.type ) {
				setParentPosts( [] );
				return;
			}

			const postType = localSettings.type === 'same' ? originalPost.type : localSettings.type;
			
			try {
				const response = await fetch(
					`${ window.postDuplicatorVars.restUrl }parent-posts?post_type=${ postType }&exclude_id=${ originalPost.id }`,
					{
						headers: {
							'X-WP-Nonce': window.postDuplicatorVars.nonce,
						},
					}
				);

				if ( response.ok ) {
					const posts = await response.json();
					
					// Format as options for SelectControl with hierarchical indentation
					const formatHierarchicalLabel = ( post ) => {
						const level = post.level || 0;
						if ( level === 0 ) {
							return post.title;
						}
						// Use dashes to show hierarchy level
						// Each level gets 1 dash for visual indentation
						const indent = '—'.repeat( level ) + ' ';
						return `${ indent }${ post.title }`;
					};
					
					const options = [
						{ label: __( '— No Parent —', 'post-duplicator' ), value: '' },
						...posts.map( ( post ) => ( {
							label: formatHierarchicalLabel( post ),
							value: String( post.id ),
							level: post.level || 0,
						} ) ),
					];
					setParentPosts( options );
					
					// If current parent is not in the list (e.g., different post type), add it
					if ( originalPost?.parentPost && originalPost.parent > 0 ) {
						const parentExists = posts.some( ( p ) => p.id === originalPost.parent );
						if ( ! parentExists ) {
							options.splice( 1, 0, {
								label: originalPost.parentPost.title,
								value: String( originalPost.parent ),
								level: 0,
							} );
							setParentPosts( options );
						}
					}
				}
			} catch ( error ) {
				console.error( 'Error fetching parent posts:', error );
				setParentPosts( [
					{ label: __( '— No Parent —', 'post-duplicator' ), value: '' },
				] );
			}
		};

		fetchParentPosts();
	}, [ originalPost?.type, localSettings.type, originalPost?.id, originalPost?.parent, originalPost?.parentPost ] );

	// Update localSettings when settings prop changes (but not title/slug)
	useEffect( () => {
		const updated = { ...settings };

		// Handle type: if "same", use original post type
		if ( settings.type === 'same' && originalPost?.type ) {
			updated.type = originalPost.type;
		}

		// Handle status: if "same", use original post status
		if ( settings.status === 'same' && originalPost?.status ) {
			updated.status = originalPost.status;
		}

		setLocalSettings( updated );
	}, [ settings, originalPost ] );

	// Update author selection when post type changes
	useEffect( () => {
		if ( ! initialized ) {
			return; // Don't update during initialization
		}
		
		const postType = getCurrentPostType();
		const supportsAuthor = postTypesAuthorSupport[ postType ] !== false;
		
		// If post type doesn't support authors, set to "No Author"
		if ( ! supportsAuthor && selectedAuthorId !== '' ) {
			setSelectedAuthorId( '' );
			handleChange( 'selectedAuthorId', null );
		}
		// If post type supports authors and current selection is "No Author", set to current user
		else if ( supportsAuthor && selectedAuthorId === '' && currentUser?.id ) {
			const newAuthorId = String( currentUser.id );
			setSelectedAuthorId( newAuthorId );
			handleChange( 'selectedAuthorId', parseInt( newAuthorId ) );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ localSettings.type, initialized ] );

	// Update parent selection when post type changes to non-hierarchical
	useEffect( () => {
		if ( ! initialized ) {
			return; // Don't update during initialization
		}
		
		const isHierarchical = currentPostTypeIsHierarchical();
		
		// If post type is not hierarchical, set parent to "No Parent"
		if ( ! isHierarchical && selectedParentId !== '' ) {
			setSelectedParentId( '' );
			handleChange( 'selectedParentId', null );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ localSettings.type, initialized ] );

	// Format date for display
	const formatDateDisplay = ( dateString ) => {
		const date = new Date( dateString );
		return date.toLocaleString( 'en-US', {
			year: 'numeric',
			month: 'short',
			day: 'numeric',
			hour: '2-digit',
			minute: '2-digit',
		} );
	};

	// Handle manual date input and format on blur
	const handleDateBlur = () => {
		try {
			const parsedDate = new Date( dateInputValue );
			if ( ! isNaN( parsedDate.getTime() ) ) {
				// Valid date - format and save
				const formattedDate = parsedDate.toISOString();
				setPostDate( formattedDate );
				setDateInputValue( formatDateDisplay( formattedDate ) );
				// Set timestamp to 'custom' and update customDate
				const newSettings = {
					...localSettings,
					timestamp: 'custom',
					customDate: formattedDate,
				};
				setLocalSettings( newSettings );
				onSettingsChange( newSettings );
			} else {
				// Invalid date - revert to previous valid date display
				setDateInputValue( formatDateDisplay( postDate ) );
			}
		} catch ( error ) {
			// Error parsing - revert to previous valid date display
			setDateInputValue( formatDateDisplay( postDate ) );
		}
	};

	// Handle date input changes (just update display value)
	const handleDateInputChange = ( value ) => {
		setDateInputValue( value );
	};

	// Handle date picker changes
	const handleDatePickerChange = ( value ) => {
		// Ensure value is in ISO string format
		const isoDate = value instanceof Date ? value.toISOString() : new Date( value ).toISOString();
		setPostDate( isoDate );
		setDateInputValue( formatDateDisplay( isoDate ) );
		// Set timestamp to 'custom' and update customDate
		const newSettings = {
			...localSettings,
			timestamp: 'custom',
			customDate: isoDate,
		};
		setLocalSettings( newSettings );
		onSettingsChange( newSettings );
	};

	const handleTitleChange = ( value ) => {
		setFullTitle( value );

		// Auto-generate slug from title if slug hasn't been manually edited
		if ( ! slugManuallyEdited && originalPost?.title ) {
			const newSlug = generateSlugFromTitle( value );
			setFullSlug( newSlug );
			// Batch update: pass both title and slug to parent together
			const newSettings = {
				...localSettings,
				fullTitle: value,
				fullSlug: newSlug,
			};
			setLocalSettings( newSettings );
			onSettingsChange( newSettings );
		} else {
			// Pass full title to parent
			handleChange( 'fullTitle', value );
		}
	};

	const handleSlugChange = ( value ) => {
		setFullSlug( value );
		setSlugManuallyEdited( true );

		// Pass full slug to parent
		handleChange( 'fullSlug', value );
	};

	const handleSlugBlur = () => {
		// If slug is empty, regenerate from title
		if ( ! fullSlug || fullSlug.trim() === '' ) {
			const regeneratedSlug = generateSlugFromTitle( fullTitle );
			setFullSlug( regeneratedSlug );
			handleChange( 'fullSlug', regeneratedSlug );
			// Reset manual edit flag since we're regenerating
			setSlugManuallyEdited( false );
		} else {
			// Sanitize slug on blur
			const sanitized = sanitizeSlug( fullSlug );
			setFullSlug( sanitized );
			handleChange( 'fullSlug', sanitized );
		}
	};

	const formatChoices = ( choices ) => {
		if ( Array.isArray( choices ) ) {
			return choices;
		}
		return Object.entries( choices ).map( ( [ value, label ] ) => ( {
			value,
			label,
		} ) );
	};

	// Open WordPress media library using wp.media API (fallback for post list screen)
	const openWpMediaLibrary = () => {
		// Ensure wp.media is available
		if ( typeof window.wp === 'undefined' || ! window.wp.media ) {
			console.error( 'WordPress media library is not available. Make sure wp_enqueue_media() is called.' );
			alert( __( 'Media library is not available. Please refresh the page.', 'post-duplicator' ) );
			return;
		}

		const mediaFrame = window.wp.media( {
			title: __( 'Select or Upload Featured Image', 'post-duplicator' ),
			button: {
				text: __( 'Use this image', 'post-duplicator' ),
			},
			multiple: false,
			library: {
				type: 'image',
			},
		} );

		mediaFrame.on( 'select', () => {
			const attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
			const imageData = {
				id: attachment.id,
				url: attachment.url,
				thumbnail:
					attachment.sizes?.thumbnail?.url || attachment.url,
				alt: attachment.alt || '',
			};
			if ( onFeaturedImageChange ) {
				onFeaturedImageChange( imageData );
			}
		} );

		mediaFrame.open();
	};

	// Render the featured image UI (shared between MediaUpload and wp.media)
	const renderFeaturedImageUI = ( openMediaLibrary ) => {
		// Generate alt text description
		const altText = featuredImage?.alt || '';
		const fileName =
			featuredImage?.url?.split( '/' ).pop() || '';
		const describedBy = `editor-post-featured-image-${
			featuredImage?.id || 'new'
		}-describedby`;
		const imageDescription = altText
			? altText
			: __(
					'The current image has no alternative text.',
					'post-duplicator'
			  ) +
			  ( fileName
					? ` ${ __( 'The file name is:', 'post-duplicator' ) } ${ fileName }`
					: '' );

		return (
			<div className="editor-post-featured-image__container">
				{ /* Screen reader description */ }
				{ featuredImage && (
					<div id={ describedBy } className="hidden">
						{ imageDescription }
					</div>
				) }

				{ featuredImage ? (
					<>
						<Button
							type="button"
							className="components-button editor-post-featured-image__preview is-next-40px-default-size"
							onClick={ openMediaLibrary }
							aria-label={ __(
								'Edit or replace the featured image',
								'post-duplicator'
							) }
							aria-describedby={ describedBy }
							aria-haspopup="dialog"
						>
							<img
								className="editor-post-featured-image__preview-image"
								src={ featuredImage.url }
								alt={ imageDescription }
							/>
						</Button>
						<HStack className="editor-post-featured-image__actions">
							<Button
								type="button"
								className="components-button editor-post-featured-image__action is-next-40px-default-size"
								onClick={ openMediaLibrary }
								aria-haspopup="dialog"
							>
								{ __( 'Replace', 'post-duplicator' ) }
							</Button>
							<Button
								type="button"
								className="components-button editor-post-featured-image__action is-next-40px-default-size"
								onClick={ () => {
									if ( onFeaturedImageChange ) {
										onFeaturedImageChange( null );
									}
								} }
							>
								{ __( 'Remove', 'post-duplicator' ) }
							</Button>
						</HStack>
					</>
				) : (
					<Button
						variant="secondary"
						onClick={ openMediaLibrary }
						__next40pxDefaultSize
					>
						{ __( 'Set featured image', 'post-duplicator' ) }
					</Button>
				) }

				{ /* Drop Zone - only show if DropZone is available */ }
				{ DropZone && (
					<DropZone
						onFilesDrop={ ( files ) => {
							// Handle file drop - open media library
							if ( files.length > 0 ) {
								openMediaLibrary();
							}
						} }
						className="components-drop-zone"
					>
						<div className="components-drop-zone__content">
							<div className="components-drop-zone__content-inner">
								<svg
									xmlns="http://www.w3.org/2000/svg"
									viewBox="0 0 24 24"
									width="24"
									height="24"
									className="components-drop-zone__content-icon"
									aria-hidden="true"
									focusable="false"
								>
									<path d="M18.5 15v3.5H13V6.7l4.5 4.1 1-1.1-6.2-5.8-5.8 5.8 1 1.1 4-4v11.7h-6V15H4v5h16v-5z" />
								</svg>
								<span className="components-drop-zone__content-text">
									{ __( 'Drop files to upload', 'post-duplicator' ) }
								</span>
							</div>
						</div>
					</DropZone>
				) }
			</div>
		);
	};

	return (
		<VStack className="duplicate-settings-fields" spacing="20px">
			{ /* Row 1: Title (left) | Featured Image (right) */ }
			<HStack spacing="16px" alignment="stretch" className="duplicate-settings-fields__top">
				<VStack className="duplicate-settings-fields__title">
					<TextControl
						label={ __( 'Title', 'post-duplicator' ) }
						value={ fullTitle }
						onChange={ handleTitleChange }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
						style={ { flex: 1 } }
					/>
					<TextControl
						label={ __( 'Slug', 'post-duplicator' ) }
						value={ fullSlug }
						onChange={ handleSlugChange }
						onBlur={ handleSlugBlur }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</VStack>
				{ /* Featured Image Control */ }
				<div
					className="duplicate-settings-fields__featured-image"
					style={ { minWidth: '200px', width: '35%',
            flex: '0 0 auto' } }
				>
					{ ( () => {
						// On post list screen, always use wp.media fallback since MediaUploadCheck may block rendering
						// In block editor, use MediaUpload if available
						const shouldUseMediaUpload = ! isPostListScreen && MediaUpload && MediaUploadCheck;
						
						if ( shouldUseMediaUpload ) {
							return (
								<MediaUploadCheck>
									<MediaUpload
										onSelect={ ( media ) => {
											const imageData = {
												id: media.id,
												url: media.url,
												thumbnail:
													media.sizes?.thumbnail?.url ||
													media.url,
												alt: media.alt || '',
											};
											if ( onFeaturedImageChange ) {
												onFeaturedImageChange( imageData );
											}
										} }
										allowedTypes={ [ 'image' ] }
										value={ featuredImage?.id }
										render={ ( { open } ) => {
											return renderFeaturedImageUI( open );
										} }
									/>
								</MediaUploadCheck>
							);
						} else {
							try {
								return renderFeaturedImageUI( () => openWpMediaLibrary() );
							} catch ( error ) {
								console.error( 'Error rendering featured image UI:', error );
								return (
									<div>
										<p>Error rendering featured image UI: { error.message }</p>
										<Button
											variant="secondary"
											onClick={ () => openWpMediaLibrary() }
											__next40pxDefaultSize
										>
											{ __( 'Set featured image', 'post-duplicator' ) }
										</Button>
									</div>
								);
							}
						}
					} )() }
				</div>
			</HStack>

			{ /* Row 4: Post Type | Post Author | Post Date */ }
			<HStack spacing="16px" alignment="stretch">
				<SelectControl
					label={ __( 'Post Type', 'post-duplicator' ) }
					value={ localSettings.type }
					options={ formatChoices( getPostTypesForDropdown() ) }
					onChange={ ( value ) => handleChange( 'type', value ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
				<SelectControl
					label={ __( 'Post Status', 'post-duplicator' ) }
					value={ localSettings.status }
					options={ formatChoices( statusChoices ) }
					onChange={ ( value ) => handleChange( 'status', value ) }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
			<SelectControl
				label={ __( 'Post Parent', 'post-duplicator' ) }
				value={ selectedParentId || '' }
				options={ parentPosts }
				onChange={ ( value ) => {
					setSelectedParentId( value );
					handleChange( 'selectedParentId', value ? parseInt( value ) : null );
				} }
				disabled={ ! currentPostTypeIsHierarchical() }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
      </HStack>
      <HStack spacing="16px" alignment="stretch">
				<SelectControl
					label={ __( 'Post Author', 'post-duplicator' ) }
					value={ selectedAuthorId || '' }
					options={ [
						// Always include "No Author" option at the top
						{
							label: __( 'No Author', 'post-duplicator' ),
							value: '',
						},
						// Add user list options
						...usersList,
					] }
					onChange={ ( value ) => {
						setSelectedAuthorId( value );
						// If "No Author" is selected (empty string), pass null or 0
						handleChange( 'selectedAuthorId', value ? parseInt( value ) : null );
					} }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
        <div ref={ dateInputRef } style={ { flex: 1, position: 'relative' } }>
					<TextControl
						label={ __( 'Post Date', 'post-duplicator' ) }
						value={ dateInputValue }
						onChange={ handleDateInputChange }
						onBlur={ handleDateBlur }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<Button
						ref={ dateButtonRef }
						onClick={ () => setIsDatePickerOpen( ! isDatePickerOpen ) }
						aria-expanded={ isDatePickerOpen }
						icon={ calendar }
						style={ {
							position: 'absolute',
							right: '2px',
							top: '26px',
							minWidth: '36px',
							height: '36px',
							padding: '0',
						} }
						variant="tertiary"
					/>
					{ isDatePickerOpen && (
						<Popover
							anchorRef={ dateInputRef }
							placement="bottom-start"
							offset={ 8 }
							onClose={ () => setIsDatePickerOpen( false ) }
						>
							<div style={ { padding: '16px' } }>
								<DateTimePicker
									currentDate={ postDate }
									onChange={ ( value ) => {
										handleDatePickerChange( value );
										setIsDatePickerOpen( false );
									} }
									is12Hour={ false }
								/>
							</div>
						</Popover>
					) }
				</div>
			</HStack>
		</VStack>
	);
};

export default DuplicateSettingsFields;

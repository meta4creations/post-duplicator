import { useState, useEffect, useRef } from '@wordpress/element';
import {
	SelectControl,
	TextControl,
	CheckboxControl,
	DateTimePicker,
	Dropdown,
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

	// Initialize selectedAuthorId with a valid default (current user ID or first user in list)
	const defaultAuthorId = currentUser?.id
		? String( currentUser.id )
		: usersList.length > 0
		? usersList[ 0 ].value
		: '';
	const [ selectedAuthorId, setSelectedAuthorId ] =
		useState( defaultAuthorId );
	const [ postDate, setPostDate ] = useState( new Date().toISOString() );
	const [ dateInputValue, setDateInputValue ] = useState( '' );
	
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

			// Set initial author based on default settings
			const initialAuthorId =
				settings.post_author === 'current_user'
					? String( currentUser?.id || '' )
					: String( originalPost.authorId || currentUser?.id || '' );
			setSelectedAuthorId( initialAuthorId );

			// Set initial date
			const initialDate = settings.customDate || new Date().toISOString();
			setPostDate( initialDate );
			setDateInputValue( formatDateDisplay( initialDate ) );

			setInitialized( true );
		}
	}, [ originalPost, initialized, settings, currentUser ] );

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
				handleChange( 'customDate', formattedDate );
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
		setPostDate( value );
		setDateInputValue( formatDateDisplay( value ) );
		handleChange( 'customDate', value );
	};

	const handleTitleChange = ( value ) => {
		setFullTitle( value );

		// Auto-generate slug from title if slug hasn't been manually edited
		if ( ! slugManuallyEdited && originalPost?.title ) {
			const newSlug = generateSlugFromTitle( value );
			setFullSlug( newSlug );
		}

		// Pass full title to parent
		handleChange( 'fullTitle', value );
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
					options={ formatChoices( postTypes ) }
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
      </HStack>
      <HStack spacing="16px" alignment="stretch">
				<SelectControl
					label={ __( 'Post Author', 'post-duplicator' ) }
					value={ selectedAuthorId || '' }
					options={ usersList }
					onChange={ ( value ) => {
						setSelectedAuthorId( value );
						handleChange( 'selectedAuthorId', parseInt( value ) );
					} }
					__nextHasNoMarginBottom
					__next40pxDefaultSize
				/>
        <div style={ { flex: 1, position: 'relative' } }>
					<TextControl
						label={ __( 'Post Date', 'post-duplicator' ) }
						value={ dateInputValue }
						onChange={ handleDateInputChange }
						onBlur={ handleDateBlur }
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
					<Dropdown
						popoverProps={ {
							placement: 'bottom-end',
							offset: 8,
						} }
						renderToggle={ ( { isOpen, onToggle } ) => (
							<Button
								onClick={ onToggle }
								aria-expanded={ isOpen }
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
						) }
						renderContent={ ( { onClose } ) => (
							<div style={ { padding: '16px' } }>
								<DateTimePicker
									currentDate={ postDate }
									onChange={ handleDatePickerChange }
									is12Hour={ false }
								/>
							</div>
						) }
					/>
				</div>
			</HStack>
		</VStack>
	);
};

export default DuplicateSettingsFields;

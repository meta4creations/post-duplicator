import { useState, useEffect, useRef } from '@wordpress/element';
import {
	Modal,
	Button,
	Spinner,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import DuplicateSettingsFields from './DuplicateSettingsFields';
import TaxonomySection from './TaxonomySection';
import CustomMetaSection from './CustomMetaSection';

const DuplicateModal = ( {
	isOpen,
	onClose,
	onDuplicate,
	originalPost,
	defaultSettings,
	postTypes,
	statusChoices,
	siteUrl,
	currentUser,
} ) => {
	const [ settings, setSettings ] = useState( defaultSettings );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ includeTaxonomies, setIncludeTaxonomies ] = useState( true );
	const [ includeCustomMeta, setIncludeCustomMeta ] = useState( true );
	const [ taxonomyData, setTaxonomyData ] = useState( {} );
	const [ customMetaData, setCustomMetaData ] = useState( [] );
	const [ featuredImage, setFeaturedImage ] = useState( null );
	const [ duplicateStatus, setDuplicateStatus ] = useState( 'idle' );
	const [ duplicatedPostId, setDuplicatedPostId ] = useState( null );
	const [ duplicatedPostTitle, setDuplicatedPostTitle ] = useState( '' );
	const [ resetKey, setResetKey ] = useState( 0 );
	const prevIsOpenRef = useRef( false );

	// Only reset state when modal is first opened (isOpen changes from false to true)
	useEffect( () => {
		if ( isOpen && ! prevIsOpenRef.current ) {
			setSettings( defaultSettings );
			setIncludeTaxonomies( true );
			setIncludeCustomMeta( true );
			setDuplicateStatus( 'idle' );
			setDuplicatedPostId( null );
			setDuplicatedPostTitle( '' );
		}
		// Reset component and state when modal closes
		if ( ! isOpen && prevIsOpenRef.current ) {
			setResetKey( ( prev ) => prev + 1 );
			setSettings( defaultSettings );
			setIncludeTaxonomies( true );
			setIncludeCustomMeta( true );
			setTaxonomyData( {} );
			setCustomMetaData( [] );
			setFeaturedImage( null );
		}
		prevIsOpenRef.current = isOpen;
	}, [ isOpen, defaultSettings ] );

	// Update data when originalPost changes, but only if modal is open and in idle state
	useEffect( () => {
		if ( ! isOpen || duplicateStatus !== 'idle' ) {
			return;
		}

		// Initialize taxonomy data from original post - only use assigned terms
		if ( originalPost?.taxonomies ) {
			const initialTaxonomyData = {};
			originalPost.taxonomies.forEach( ( taxonomy ) => {
				// Use assignedTermIds (terms currently on the post), not all terms
				initialTaxonomyData[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
			} );
			setTaxonomyData( initialTaxonomyData );
		} else {
			setTaxonomyData( {} );
		}

		// Initialize custom meta data from original post
		if ( originalPost?.customMeta ) {
			setCustomMetaData(
				originalPost.customMeta.map( ( meta ) => ( {
					key: meta.key,
					value: meta.value,
					type: meta.type || 'string',
					isSerialized: meta.isSerialized || false,
				} ) )
			);
		} else {
			setCustomMetaData( [] );
		}

		// Initialize featured image from original post
		if ( originalPost?.featuredImage ) {
			setFeaturedImage( originalPost.featuredImage );
		} else {
			setFeaturedImage( null );
		}
	}, [ isOpen, originalPost, duplicateStatus ] );

	const getModalTitle = () => {
		if ( ! originalPost?.title ) return '';
		// Use fullTitle if available, otherwise use original + suffix
		if ( settings.fullTitle ) {
			return settings.fullTitle;
		}
		return `${ originalPost.title } ${ settings.title }`;
	};

	const getDuplicateButtonLabel = () => {
		const targetType =
			settings.type === 'same' ? originalPost.type : settings.type;
		const postTypeLabel = postTypes[ targetType ] || targetType;
		return __( `Duplicate ${ postTypeLabel }`, 'post-duplicator' );
	};

	const handleDuplicate = async () => {
		setIsLoading( true );
		setDuplicateStatus( 'duplicating' );
		
		// Calculate the final post title
		const finalTitle = settings.fullTitle || 
			`${ originalPost.title } ${ settings.title }`;
		setDuplicatedPostTitle( finalTitle );
		
		try {
			const duplicateSettings = {
				...settings,
				includeTaxonomies,
				includeCustomMeta,
				// Only send taxonomyData if taxonomies are enabled
				...( includeTaxonomies ? { taxonomyData } : {} ),
				customMetaData,
				featuredImageId: featuredImage?.id || null,
			};
			
			// Create a promise wrapper to handle the callback-based onDuplicate
			await new Promise( ( resolve, reject ) => {
				onDuplicate( duplicateSettings, {
					onSuccess: ( result ) => {
						setDuplicatedPostId( result.duplicate_id );
						setDuplicateStatus( 'complete' );
						setIsLoading( false );
						resolve( result );
					},
					onError: ( error ) => {
						console.error( 'Error duplicating:', error );
						setDuplicateStatus( 'idle' );
						setIsLoading( false );
						reject( error );
					},
				} );
			} );
		} catch ( error ) {
			// Error already handled in callback
		}
	};

	const handleTaxonomyChange = ( selectedTerms ) => {
		setTaxonomyData( selectedTerms );
	};

	const handleCustomMetaChange = ( metaFields ) => {
		setCustomMetaData( metaFields );
	};

	// Set CSS variable on modal frame when featured image changes
	useEffect( () => {
		if ( featuredImage && isOpen ) {
			// Use setTimeout to ensure modal is rendered
			const timer = setTimeout( () => {
				const modalFrame = document.querySelector(
					'.duplicate-post-modal--has-featured-image.components-modal__frame'
				);
				if ( modalFrame ) {
					modalFrame.style.setProperty(
						'--featured-image-url',
						`url(${ featuredImage.thumbnail || featuredImage.url })`
					);
				}
			}, 0 );
			return () => clearTimeout( timer );
		}
	}, [ featuredImage, isOpen ] );

	if ( ! isOpen ) return null;

	const handleClose = () => {
		// Prevent closing while duplicating
		if ( duplicateStatus === 'duplicating' ) {
			return;
		}
		// Reset state when closing from complete state
		if ( duplicateStatus === 'complete' ) {
			setDuplicateStatus( 'idle' );
			setDuplicatedPostId( null );
			setDuplicatedPostTitle( '' );
		}
		onClose();
	};

	return (
		<Modal
			title={ getModalTitle() }
			onRequestClose={ handleClose }
			className={ `duplicate-post-modal ${
				featuredImage ? 'duplicate-post-modal--has-featured-image' : ''
			}` }
			size="large"
			style={ { borderRadius: 0 } }
		>
			<div
				className="duplicate-post-modal__content"
				style={ { paddingBottom: duplicateStatus === 'idle' ? '77px' : '0' } }
			>
				{ duplicateStatus === 'idle' ? (
					<VStack className="duplicate-post-modal__settings">
					<DuplicateSettingsFields
						key={ `duplicate-settings-${ originalPost?.id || 0 }-${ resetKey }` }
						settings={ settings }
						onSettingsChange={ setSettings }
						postTypes={ postTypes }
						statusChoices={ statusChoices }
						originalPost={ originalPost }
						featuredImage={ featuredImage }
						onFeaturedImageChange={ setFeaturedImage }
					/>

					{ originalPost?.taxonomies &&
						originalPost.taxonomies.length > 0 && (
							<TaxonomySection
								taxonomies={ originalPost.taxonomies }
								onChange={ handleTaxonomyChange }
								enabled={ includeTaxonomies }
								onToggle={ setIncludeTaxonomies }
							/>
						) }

					{ originalPost?.customMeta &&
						originalPost.customMeta.length > 0 && (
							<CustomMetaSection
								customMeta={ originalPost.customMeta }
								onChange={ handleCustomMetaChange }
								enabled={ includeCustomMeta }
								onToggle={ setIncludeCustomMeta }
							/>
						) }
					</VStack>
				) : (
					<div className="duplicate-post-modal__status">
						<div className="duplicate-post-modal__status-item">
							{ featuredImage && (
								<img
									src={ featuredImage.thumbnail || featuredImage.url }
									alt=""
									className="duplicate-post-modal__status-thumbnail"
								/>
							) }
							<h3 className="duplicate-post-modal__status-title">
								{ duplicatedPostTitle }
							</h3>
							<div className="duplicate-post-modal__status-actions">
								{ duplicateStatus === 'duplicating' ? (
									<Spinner />
								) : (
									<>
										<Button
											variant="secondary"
											onClick={ () =>
												window.open(
													`${ siteUrl }/?p=${ duplicatedPostId }`,
													'_blank'
												)
											}
										>
											{ __( 'View Post', 'post-duplicator' ) }
										</Button>
										<Button
											variant="primary"
											onClick={ () =>
												window.open(
													`${ siteUrl }/wp-admin/post.php?post=${ duplicatedPostId }&action=edit`,
													'_blank'
												)
											}
										>
											{ __( 'Edit Post', 'post-duplicator' ) }
										</Button>
									</>
								) }
							</div>
						</div>
					</div>
				) }
			</div>
			{ duplicateStatus === 'idle' && (
				<HStack
				alignment="right"
				className="duplicate-post-modal__footer"
				style={ {
					position: 'absolute',
					bottom: '0px',
					left: '0',
					padding: '20px',
					borderTop: '1px solid rgba(0, 0, 0, 0.1)',
					background: '#FFF',
				} }
			>
				<Button
					variant="tertiary"
					onClick={ handleClose }
					disabled={ isLoading }
				>
					{ __( 'Cancel', 'post-duplicator' ) }
				</Button>
				<Button
					variant="primary"
					onClick={ handleDuplicate }
					disabled={ isLoading }
					isBusy={ isLoading }
				>
					{ isLoading
						? __( 'Duplicating...', 'post-duplicator' )
						: getDuplicateButtonLabel() }
				</Button>
			</HStack>
			) }
		</Modal>
	);
};

export default DuplicateModal;

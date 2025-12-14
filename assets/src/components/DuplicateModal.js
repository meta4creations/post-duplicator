import { useState, useEffect, useRef } from '@wordpress/element';
import {
	Modal,
	Button,
	Spinner,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { copy } from '@wordpress/icons';
import DuplicateSettingsFields from './DuplicateSettingsFields';
import TaxonomySection from './TaxonomySection';
import CustomMetaSection from './CustomMetaSection';
import PostList from './PostList';
import MarketingBanner from './MarketingBanner';

const DuplicateModal = ( {
	isOpen,
	onClose,
	onDuplicate,
	originalPost,
	postsToDuplicate,
	mode: initialMode = 'single',
	defaultSettings,
	postTypes,
	statusChoices,
	siteUrl,
	currentUser,
} ) => {
	// Determine if we're in bulk mode (separate from single/multiple toggle)
	const isBulkMode = initialMode === 'bulk' || ( postsToDuplicate && postsToDuplicate.length > 0 );
	
	// Use boolean for single/multiple toggle (only applies when not in bulk mode)
	const [ isMultiple, setIsMultiple ] = useState( initialMode === 'multiple-clones' );
	const [ cloneCount, setCloneCount ] = useState( 2 );
	
	// Convert originalPost to postsToDuplicate format for backward compatibility
	const getInitialPosts = () => {
		if ( postsToDuplicate && postsToDuplicate.length > 0 ) {
			return postsToDuplicate;
		}
		if ( originalPost ) {
			return [ {
				id: `post-${ originalPost.id }`,
				originalPost,
				settings: { ...defaultSettings },
				isDuplicate: false,
			} ];
		}
		return [];
	};

	const [ posts, setPosts ] = useState( getInitialPosts() );
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
	const [ duplicationResults, setDuplicationResults ] = useState( [] );
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
			setDuplicationResults( [] );
			// Reset posts array when opening
			const initialPosts = getInitialPosts();
			setPosts( initialPosts );
			setIsMultiple( initialMode === 'multiple-clones' );
			// Reset clone count to default (2) when opening
			setCloneCount( 2 );
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
			setDuplicationResults( [] );
		}
		prevIsOpenRef.current = isOpen;
	}, [ isOpen, defaultSettings, initialMode ] );

	// Update posts when originalPost or postsToDuplicate changes
	useEffect( () => {
		if ( isOpen && duplicateStatus === 'idle' ) {
			const newPosts = getInitialPosts();
			setPosts( newPosts );
		}
	}, [ originalPost, postsToDuplicate, isOpen, duplicateStatus ] );

	// Calculate actual clone count (excluding original post)
	const actualCloneCount = posts.filter( ( p ) => p.isDuplicate ).length;
	const cloneCountRef = useRef( cloneCount );
	const isSyncingFromDeletionRef = useRef( false );

	// Update ref when cloneCount changes (from user input)
	useEffect( () => {
		cloneCountRef.current = cloneCount;
	}, [ cloneCount ] );

	// Sync cloneCount with actual clone count when posts are deleted (but don't regenerate)
	useEffect( () => {
		if ( isMultiple && ! isBulkMode && duplicateStatus === 'idle' && ! isSyncingFromDeletionRef.current ) {
			const currentCloneCount = posts.filter( ( p ) => p.isDuplicate ).length;
			// Only sync if count decreased (deletion happened), not if user is typing or regenerating
			if ( currentCloneCount < cloneCountRef.current && currentCloneCount >= 1 ) {
				isSyncingFromDeletionRef.current = true;
				setCloneCount( currentCloneCount );
				// Reset flag after state update
				setTimeout( () => {
					isSyncingFromDeletionRef.current = false;
				}, 0 );
			}
		}
	}, [ posts, isMultiple, isBulkMode, duplicateStatus ] );

	// Handle mode change and clone count changes - generate/update clones
	useEffect( () => {
		// Skip regeneration if we're syncing from deletion
		if ( isSyncingFromDeletionRef.current ) {
			return;
		}

		if ( isMultiple && ! isBulkMode && duplicateStatus === 'idle' ) {
			// Find the original post (non-duplicate)
			const firstPost = posts.find( ( p ) => ! p.isDuplicate );
			if ( ! firstPost ) {
				return; // Need original post to generate clones
			}
			
			// Generate clones based on cloneCount
			const existingClones = posts.filter( ( p ) => p.isDuplicate );
			const targetCloneCount = Math.max( 1, cloneCount );
			
			// Only regenerate if count doesn't match (user changed input or mode switched)
			if ( existingClones.length !== targetCloneCount ) {
				// Set flag to prevent sync effect from interfering
				isSyncingFromDeletionRef.current = true;
				
				const clones = [];
				// Preserve existing clone settings if possible
				for ( let i = 0; i < targetCloneCount; i++ ) {
					const existingClone = existingClones[i];
					if ( existingClone ) {
						// Keep existing clone with same ID and settings
						clones.push( existingClone );
					} else {
						// Create new clone
						clones.push( {
							id: `clone-${ firstPost.originalPost.id }-${ i }`,
							originalPost: firstPost.originalPost,
							settings: { ...defaultSettings },
							isDuplicate: true,
						} );
					}
				}
				setPosts( [ firstPost, ...clones ] );
				
				// Update ref to match the new count
				cloneCountRef.current = targetCloneCount;
				
				// Reset flag after regeneration
				setTimeout( () => {
					isSyncingFromDeletionRef.current = false;
				}, 0 );
			}
		} else if ( ! isMultiple && ! isBulkMode && posts.length > 1 && duplicateStatus === 'idle' ) {
			// Switch back to single - keep only the first post
			const firstPost = posts.find( ( p ) => ! p.isDuplicate ) || posts[0];
			if ( firstPost ) {
				setPosts( [ {
					...firstPost,
					isDuplicate: false,
				} ] );
			}
		}
	}, [ isMultiple, isBulkMode, cloneCount, duplicateStatus ] );

	// Update data when originalPost changes, but only if modal is open and in idle state (single mode)
	useEffect( () => {
		if ( ! isOpen || duplicateStatus !== 'idle' || isMultiple || isBulkMode ) {
			return;
		}

		const currentPost = posts[0]?.originalPost || originalPost;
		if ( ! currentPost ) return;

		// Initialize taxonomy data from original post - only use assigned terms
		if ( currentPost?.taxonomies ) {
			const initialTaxonomyData = {};
			currentPost.taxonomies.forEach( ( taxonomy ) => {
				initialTaxonomyData[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
			} );
			setTaxonomyData( initialTaxonomyData );
		} else {
			setTaxonomyData( {} );
		}

		// Initialize custom meta data from original post
		if ( currentPost?.customMeta ) {
			setCustomMetaData(
				currentPost.customMeta.map( ( meta ) => ( {
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
		if ( currentPost?.featuredImage ) {
			setFeaturedImage( currentPost.featuredImage );
		} else {
			setFeaturedImage( null );
		}
	}, [ isOpen, originalPost, duplicateStatus, isMultiple, isBulkMode, posts ] );

	const getModalTitle = () => {
		if ( isBulkMode ) {
			return __( 'Duplicate Posts', 'post-duplicator' );
		}
		if ( isMultiple ) {
			const firstPost = posts[0]?.originalPost || originalPost;
			const thumbnail = firstPost?.featuredImage?.thumbnail || firstPost?.featuredImage?.url;
			
			if ( firstPost?.title ) {
				// Return JSX with thumbnail if available
				if ( thumbnail ) {
					return (
						<div style={ { display: 'flex', alignItems: 'center', gap: '12px' } }>
							<img 
								src={ thumbnail } 
								alt=""
								style={ { 
									width: '40px', 
									height: '40px', 
									objectFit: 'cover',
									borderRadius: '4px',
									flexShrink: 0
								} }
							/>
							<span>{ __( `Duplicate: ${ firstPost.title }`, 'post-duplicator' ) }</span>
						</div>
					);
				}
				return __( `Duplicate: ${ firstPost.title }`, 'post-duplicator' );
			}
			return __( 'Duplicate Post', 'post-duplicator' );
		}
		// Single mode
		const currentPost = posts[0]?.originalPost || originalPost;
		if ( ! currentPost?.title ) return '';
		// Use fullTitle if available, otherwise use original + suffix
		if ( settings.fullTitle ) {
			return settings.fullTitle;
		}
		return `${ currentPost.title } ${ settings.title }`;
	};

	const getDuplicateButtonLabel = () => {
		if ( isBulkMode || isMultiple ) {
			// In multiple-clones mode, show only clone count; in bulk mode, show all posts count
			const count = isMultiple && ! isBulkMode ? cloneCount : posts.length;
			return __( `Duplicate ${ count } ${ count === 1 ? 'Post' : 'Posts' }`, 'post-duplicator' );
		}
		// Single mode
		const currentPost = posts[0]?.originalPost || originalPost;
		const targetType =
			settings.type === 'same' ? currentPost?.type : settings.type;
		const postTypeLabel = postTypes[ targetType ] || targetType;
		return __( `Duplicate ${ postTypeLabel }`, 'post-duplicator' );
	};

	const handleDuplicate = async () => {
		if ( posts.length === 0 ) {
			return;
		}

		setIsLoading( true );
		setDuplicateStatus( 'duplicating' );
		setDuplicationResults( [] );

		const results = [];

		// Handle single mode (backward compatible)
		if ( ! isMultiple && ! isBulkMode ) {
			const currentPost = posts[0];
			const finalTitle = settings.fullTitle || 
				`${ currentPost.originalPost.title } ${ settings.title }`;
			setDuplicatedPostTitle( finalTitle );
			
			try {
				const duplicateSettings = {
					...settings,
					includeTaxonomies,
					includeCustomMeta,
					...( includeTaxonomies ? { taxonomyData } : {} ),
					customMetaData,
					featuredImageId: featuredImage?.id || null,
				};
				
				await new Promise( ( resolve, reject ) => {
					onDuplicate( currentPost.originalPost.id, duplicateSettings, {
						onSuccess: ( result ) => {
							results.push( {
								success: true,
								postId: result.duplicate_id,
								title: finalTitle,
								originalPost: currentPost.originalPost,
								featuredImage,
							} );
							setDuplicationResults( results );
							setDuplicatedPostId( result.duplicate_id );
							setDuplicateStatus( 'complete' );
							setIsLoading( false );
							resolve( result );
						},
						onError: ( error ) => {
							console.error( 'Error duplicating:', error );
							results.push( {
								success: false,
								error: error.message || error.data?.message || 'Unknown error',
								originalPost: currentPost.originalPost,
							} );
							setDuplicationResults( results );
							setDuplicateStatus( 'complete' );
							setIsLoading( false );
							reject( error );
						},
					} );
				} );
			} catch ( error ) {
				// Error already handled in callback
			}
		} else {
			// Handle multiple posts/clones mode - duplicate sequentially
			// In multiple-clones mode, filter out the original post (only duplicate clones)
			const postsToDup = isMultiple && ! isBulkMode ? posts.filter( ( p ) => p.isDuplicate ) : posts;
			for ( let i = 0; i < postsToDup.length; i++ ) {
				const post = postsToDup[i];
				const postSettings = post.settings || defaultSettings;
				const postTaxonomyData = post.taxonomyData || {};
				const postCustomMetaData = post.customMetaData || [];
				const postFeaturedImage = post.featuredImage || null;
				const postIncludeTaxonomies = post.includeTaxonomies !== undefined ? post.includeTaxonomies : true;
				const postIncludeCustomMeta = post.includeCustomMeta !== undefined ? post.includeCustomMeta : true;

				const finalTitle = postSettings.fullTitle || 
					`${ post.originalPost.title } ${ postSettings.title || __( 'Copy', 'post-duplicator' ) }`;

				try {
					const duplicateSettings = {
						...postSettings,
						includeTaxonomies: postIncludeTaxonomies,
						includeCustomMeta: postIncludeCustomMeta,
						...( postIncludeTaxonomies ? { taxonomyData: postTaxonomyData } : {} ),
						customMetaData: postCustomMetaData,
						featuredImageId: postFeaturedImage?.id || null,
					};

					await new Promise( ( resolve, reject ) => {
						onDuplicate( post.originalPost.id, duplicateSettings, {
							onSuccess: ( result ) => {
								results.push( {
									success: true,
									postId: result.duplicate_id,
									title: finalTitle,
									originalPost: post.originalPost,
									featuredImage: postFeaturedImage,
								} );
								setDuplicationResults( [ ...results ] );
								resolve( result );
							},
							onError: ( error ) => {
								console.error( `Error duplicating post ${ post.originalPost.id }:`, error );
								results.push( {
									success: false,
									error: error.message || error.data?.message || 'Unknown error',
									title: finalTitle,
									originalPost: post.originalPost,
								} );
								setDuplicationResults( [ ...results ] );
								// Continue with next post even on error
								resolve( null );
							},
						} );
					} );
				} catch ( error ) {
					// Continue with next post
					results.push( {
						success: false,
						error: error.message || 'Unknown error',
						title: finalTitle,
						originalPost: post.originalPost,
					} );
					setDuplicationResults( [ ...results ] );
				}
			}

			setDuplicateStatus( 'complete' );
			setIsLoading( false );
		}
	};

	const handleTaxonomyChange = ( selectedTerms ) => {
		setTaxonomyData( selectedTerms );
	};

	const handleCustomMetaChange = ( metaFields ) => {
		setCustomMetaData( metaFields );
	};

	const handlePostsChange = ( updatedPosts ) => {
		// Prevent deletion if it's the last clone in multiple-clones mode
		if ( isMultiple && ! isBulkMode ) {
			const newCloneCount = updatedPosts.filter( ( p ) => p.isDuplicate ).length;
			// Don't allow deletion if it would result in less than 1 clone
			if ( newCloneCount < 1 ) {
				return; // Don't update posts
			}
			// In multiple-clones mode, PostList only receives clones, so we need to preserve the original post
			const originalPost = posts.find( ( p ) => ! p.isDuplicate );
			if ( originalPost ) {
				setPosts( [ originalPost, ...updatedPosts ] );
			} else {
				setPosts( updatedPosts );
			}
		} else {
			setPosts( updatedPosts );
		}
		// Note: cloneCount will be synced by useEffect when posts change
	};

	const handleToggleMultiple = () => {
		const newIsMultiple = ! isMultiple;
		setIsMultiple( newIsMultiple );
		if ( newIsMultiple && cloneCount < 2 ) {
			setCloneCount( 2 );
		}
	};

	// Set CSS variable on modal frame when featured image changes
	useEffect( () => {
		const imageToUse = ! isMultiple && ! isBulkMode ? featuredImage : ( posts[0]?.featuredImage || null );
		if ( imageToUse && isOpen ) {
			// Use setTimeout to ensure modal is rendered
			const timer = setTimeout( () => {
				const modalFrame = document.querySelector(
					'.duplicate-post-modal--has-featured-image.components-modal__frame'
				);
				if ( modalFrame ) {
					modalFrame.style.setProperty(
						'--featured-image-url',
						`url(${ imageToUse.thumbnail || imageToUse.url })`
					);
				}
			}, 0 );
			return () => clearTimeout( timer );
		}
	}, [ featuredImage, posts, isMultiple, isBulkMode, isOpen ] );

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

	const currentPost = posts[0]?.originalPost || originalPost;
	const currentFeaturedImage = ! isMultiple && ! isBulkMode ? featuredImage : ( posts[0]?.featuredImage || null );

	// Header actions for mode toggle (only show for single post mode, not bulk)
	const headerActions = ( ! postsToDuplicate || postsToDuplicate.length === 0 ) && originalPost ? (
		<HStack spacing="4px" style={ { flex: 0 } }>
			<Button
				icon={ copy }
				size="compact"
				variant={ isMultiple ? 'primary' : 'secondary' }
				onClick={ handleToggleMultiple }
				label={ isMultiple ? __( 'Multiple Clone', 'post-duplicator' ) : __( 'Single Clone', 'post-duplicator' ) }
				isPressed={ isMultiple }
			/>
		</HStack>
	) : null;

	return (
		<Modal
			title={ getModalTitle() }
			onRequestClose={ handleClose }
			headerActions={ headerActions }
			className={ `duplicate-post-modal ${
				currentFeaturedImage ? 'duplicate-post-modal--has-featured-image' : ''
			}` }
			size="large"
			style={ { borderRadius: 0 } }
		>
			<div
				className="duplicate-post-modal__content"
				style={ { paddingBottom: duplicateStatus === 'idle' ? '77px' : '0' } }
			>
				{ duplicateStatus === 'idle' ? (
					<>
					<VStack className="duplicate-post-modal__settings" spacing="20px">
						{/* Clone Count Input - only show for multiple-clones mode */}
						{ isMultiple && ! isBulkMode && ( ! postsToDuplicate || postsToDuplicate.length === 0 ) && originalPost && (
							<div className="duplicate-post-modal__clone-count">
								<NumberControl
									label={ __( 'Number of Clones', 'post-duplicator' ) }
									value={ cloneCount }
									onChange={ ( value ) => {
										const newCount = Math.max( 1, Math.min( 50, parseInt( value ) || 1 ) );
										setCloneCount( newCount );
									} }
									min={ 1 }
									max={ 50 }
									__nextHasNoMarginBottom
									__next40pxDefaultSize
								/>
							</div>
						) }

						{/* Single Mode - show original UI */}
						{ ! isMultiple && ! isBulkMode && (
							<>
								<DuplicateSettingsFields
									key={ `duplicate-settings-${ currentPost?.id || 0 }-${ resetKey }` }
									settings={ settings }
									onSettingsChange={ setSettings }
									postTypes={ postTypes }
									statusChoices={ statusChoices }
									originalPost={ currentPost }
									featuredImage={ featuredImage }
									onFeaturedImageChange={ setFeaturedImage }
								/>

								{ currentPost?.taxonomies &&
									currentPost.taxonomies.length > 0 && (
										<TaxonomySection
											taxonomies={ currentPost.taxonomies }
											onChange={ handleTaxonomyChange }
											enabled={ includeTaxonomies }
											onToggle={ setIncludeTaxonomies }
										/>
									) }

								{ currentPost?.customMeta &&
									currentPost.customMeta.length > 0 && (
										<CustomMetaSection
											customMeta={ currentPost.customMeta }
											onChange={ handleCustomMetaChange }
											enabled={ includeCustomMeta }
											onToggle={ setIncludeCustomMeta }
										/>
									) }
							</>
						) }

					{/* Multiple Clones or Bulk Mode - show PostList */}
					{ ( isMultiple || isBulkMode ) && (
						<PostList
							posts={ isMultiple && ! isBulkMode ? posts.filter( ( p ) => p.isDuplicate ) : posts }
							onPostsChange={ handlePostsChange }
							postTypes={ postTypes }
							statusChoices={ statusChoices }
							siteUrl={ siteUrl }
							currentUser={ currentUser }
							defaultSettings={ defaultSettings }
							isMultipleClonesMode={ isMultiple && ! isBulkMode }
						/>
					) }
					</VStack>

					{/* Marketing Banner */}
					<MarketingBanner />
					</>
				) : (
					<div className="duplicate-post-modal__status">
						{/* Single mode success */}
						{ ! isMultiple && ! isBulkMode && (
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
						) }

						{/* Multiple posts/clones success - stacked messages */}
						{ ( isMultiple || isBulkMode ) && (
							<VStack spacing="12px" className="duplicate-post-modal__success-list">
								{ duplicateStatus === 'duplicating' && (
									<div style={ { textAlign: 'center', padding: '20px' } }>
										<Spinner />
										<p style={ { marginTop: '12px' } }>
											{ __( 'Duplicating posts...', 'post-duplicator' ) }
										</p>
									</div>
								) }
								{ duplicationResults.map( ( result, index ) => (
									<div
										key={ index }
										className={ `duplicate-post-modal__success-item ${
											result.success ? '' : 'duplicate-post-modal__success-item--error'
										}` }
									>
										{ result.success ? (
											<>
												{ result.featuredImage && (
													<img
														src={ result.featuredImage.thumbnail || result.featuredImage.url }
														alt=""
														className="duplicate-post-modal__status-thumbnail"
													/>
												) }
												<h3 className="duplicate-post-modal__status-title">
													{ result.title }
												</h3>
												<div className="duplicate-post-modal__status-actions">
													<Button
														variant="secondary"
														onClick={ () =>
															window.open(
																`${ siteUrl }/?p=${ result.postId }`,
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
																`${ siteUrl }/wp-admin/post.php?post=${ result.postId }&action=edit`,
																'_blank'
															)
														}
													>
														{ __( 'Edit Post', 'post-duplicator' ) }
													</Button>
												</div>
											</>
										) : (
											<>
												<h3 className="duplicate-post-modal__status-title">
													{ result.title || result.originalPost?.title || __( 'Unknown Post', 'post-duplicator' ) }
												</h3>
												<div className="duplicate-post-modal__status-error">
													{ __( 'Error:', 'post-duplicator' ) } { result.error }
												</div>
											</>
										) }
									</div>
								) ) }
							</VStack>
						) }
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
						disabled={ isLoading || posts.length === 0 }
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

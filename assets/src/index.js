import './css/index.scss';
import { render, useState, useEffect, createRoot } from '@wordpress/element';
import DuplicateModal from './components/DuplicateModal';
import { duplicatePost } from './utils/duplicatePost';

function showSnackbar( message, type = 'error' ) {
	// Create the container
	const snackbar = document.createElement( 'div' );
	snackbar.classList.add( 'my-snackbar', `my-snackbar--${ type }` );
	snackbar.textContent = message;

	// Append to body
	document.body.appendChild( snackbar );

	// Auto-remove after 3 seconds
	setTimeout( () => {
		snackbar.classList.add( 'my-snackbar--hide' );
		snackbar.addEventListener( 'transitionend', () => snackbar.remove() );
	}, 3000 );
}

// Helper function to fetch post data
// Uses plugin's own REST endpoint which works for all post types (including non-REST)
const fetchPostData = async ( postId, postType = 'post' ) => {
	// Fetch full post data from plugin endpoint (works for all post types)
	const fullDataResponse = await fetch(
		`${ postDuplicatorVars.restUrl }post-full-data/${ postId }`,
		{
			headers: {
				'X-WP-Nonce': postDuplicatorVars.nonce,
			},
		}
	);

	if ( ! fullDataResponse.ok ) {
		throw new Error( 'Failed to fetch post data' );
	}

	const post = await fullDataResponse.json();

	// Fetch taxonomy and custom meta data
	let taxonomies = [];
	let customMeta = [];

	try {
		const postDataResponse = await fetch(
			`${ postDuplicatorVars.restUrl }post-data/${ postId }`,
			{
				headers: {
					'X-WP-Nonce': postDuplicatorVars.nonce,
				},
			}
		);

		if ( postDataResponse.ok ) {
			const postData = await postDataResponse.json();
			taxonomies = postData.taxonomies || [];
			customMeta = postData.customMeta || [];
		}
	} catch ( error ) {
		console.error( 'Error fetching post data:', error );
	}

	return {
		id: post.id,
		title: post.title,
		type: post.type,
		status: post.status,
		slug: post.slug,
		date: post.date,
		author: post.author,
		authorId: post.authorId,
		parent: post.parent || 0,
		parentPost: post.parentPost,
		taxonomies: taxonomies,
		customMeta: customMeta,
		featuredImage: post.featuredImage,
	};
};

// React component to handle modal state
const DuplicatePostHandler = () => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ currentPost, setCurrentPost ] = useState( null );
	const [ postsToDuplicate, setPostsToDuplicate ] = useState( null );
	const [ modalMode, setModalMode ] = useState( 'single' );
	const [ wasDuplicated, setWasDuplicated ] = useState( false );
	const [ isLoadingPostData, setIsLoadingPostData ] = useState( false );
	const [ initialDuplicationResults, setInitialDuplicationResults ] = useState( null );
	
	// Check if we're on the posts list screen
	const isPostsListScreen = () => {
		// Check if we're on an edit screen (wp-admin/edit.php)
		return window.location.href.includes( '/wp-admin/edit.php' ) || 
		       window.location.href.includes( '/wp-admin/post.php' ) === false;
	};

	// Check if we're on a classic edit post screen
	const isClassicEditScreen = () => {
		return window.location.href.includes( '/wp-admin/post.php' ) && 
		       window.location.href.includes( 'action=edit' );
	};

	const handleDuplicate = async ( postId, settings, callbacks ) => {
		await duplicatePost( postId, settings, {
			onSuccess: ( result ) => {
				setWasDuplicated( true );
				if ( callbacks?.onSuccess ) {
					callbacks.onSuccess( result );
				}
			},
			onError: ( error ) => {
				showSnackbar(
					`Error duplicating post: ${
						error.message || error.data?.message || 'Unknown error'
					}`,
					'error'
				);
				if ( callbacks?.onError ) {
					callbacks.onError( error );
				}
			},
		} );
	};

	// Set up click handler for single post duplication
	useEffect( () => {
		const handleClick = async ( e ) => {
			if ( e.target.classList.contains( 'm4c-duplicate-post' ) ) {
				e.preventDefault();

				const postId = e.target.getAttribute( 'data-postid' );
				const postType =
					e.target.getAttribute( 'data-posttype' ) || 'post';

				const mode = postDuplicatorVars.mode || 'advanced';

				// Check if basic mode - auto-duplicate
				if ( mode === 'basic' ) {
					setIsLoadingPostData( true );
					try {
						// Fetch post data
						const postData = await fetchPostData( postId, postType );

						// Prepare taxonomy and custom meta data
						let taxonomyData = {};
						if ( postData.taxonomies ) {
							postData.taxonomies.forEach( ( taxonomy ) => {
								taxonomyData[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
							} );
						}
						const customMetaData = ( postData.customMeta || [] ).map( ( meta ) => ( {
							key: meta.key,
							value: meta.value,
							type: meta.type || 'string',
							isSerialized: meta.isSerialized || false,
						} ) );

						// Prepare settings with defaults
						const duplicateSettings = {
							...postDuplicatorVars.defaultSettings,
							includeTaxonomies: true,
							includeCustomMeta: true,
							taxonomyData: taxonomyData,
							customMetaData: customMetaData,
							featuredImageId: postData.featuredImage?.id || null,
						};

						// Duplicate the post
						await duplicatePost( postId, duplicateSettings, {
							onSuccess: async ( result ) => {
								setIsLoadingPostData( false );
								// Determine which action setting to use based on screen type
								const isClassicEdit = isClassicEditScreen();
								const action = isClassicEdit 
									? ( postDuplicatorVars.singleAfterDuplicationAction || 'notice' )
									: ( postDuplicatorVars.listSingleAfterDuplicationAction || 'notice' );
								const finalPostType = duplicateSettings.type === 'same' ? postType : duplicateSettings.type;
								const finalTitle = `${ postData.title } ${ postDuplicatorVars.defaultSettings.title }`;

								// Fetch featured image from duplicated post for notice action
								let duplicatedFeaturedImage = null;
								if ( action === 'notice' ) {
									try {
										const duplicatedPostDataResponse = await fetch(
											`${ postDuplicatorVars.restUrl }post-full-data/${ result.duplicate_id }`,
											{
												headers: {
													'X-WP-Nonce': postDuplicatorVars.nonce,
												},
											}
										);
										if ( duplicatedPostDataResponse.ok ) {
											const duplicatedPostData = await duplicatedPostDataResponse.json();
											duplicatedFeaturedImage = duplicatedPostData.featuredImage || null;
										}
									} catch ( error ) {
										console.error( 'Error fetching duplicated post featured image:', error );
									}
								}

								if ( action === 'notice' ) {
									// Show success modal
									setWasDuplicated( true );
									setCurrentPost( postData );
									setInitialDuplicationResults( {
										postId: result.duplicate_id,
										title: finalTitle,
										featuredImage: duplicatedFeaturedImage || postData.featuredImage,
										postType: finalPostType,
									} );
									setModalMode( 'single' );
									setIsModalOpen( true );
								} else if ( action === 'new_tab' ) {
									// Open in new tab - use post type in URL for custom post types
									const editUrl = finalPostType !== 'post' 
										? `${ postDuplicatorVars.siteUrl }/wp-admin/post.php?post=${ result.duplicate_id }&action=edit&post_type=${ finalPostType }`
										: `${ postDuplicatorVars.siteUrl }/wp-admin/post.php?post=${ result.duplicate_id }&action=edit`;
									window.open( editUrl, '_blank' );
								} else if ( action === 'same_tab' ) {
									// Navigate in same tab - use post type in URL for custom post types
									const editUrl = finalPostType !== 'post' 
										? `${ postDuplicatorVars.siteUrl }/wp-admin/post.php?post=${ result.duplicate_id }&action=edit&post_type=${ finalPostType }`
										: `${ postDuplicatorVars.siteUrl }/wp-admin/post.php?post=${ result.duplicate_id }&action=edit`;
									window.location.href = editUrl;
								} else if ( action === 'refresh' ) {
									// Refresh the page
									setWasDuplicated( true );
									window.location.reload();
								}
							},
							onError: ( error ) => {
								setIsLoadingPostData( false );
								showSnackbar(
									`Error duplicating post: ${
										error.message || error.data?.message || 'Unknown error'
									}`,
									'error'
								);
							},
						} );
					} catch ( error ) {
						console.error( 'Error in basic mode duplication:', error );
						setIsLoadingPostData( false );
						showSnackbar(
							'Failed to duplicate post. Please try again.',
							'error'
						);
					}
				} else {
					// Advanced mode - show modal
					setCurrentPost( null );
					setPostsToDuplicate( null );
					setModalMode( 'single' );
					setIsLoadingPostData( true );
					setIsModalOpen( true );

					// Fetch data asynchronously after opening modal
					try {
						const postData = await fetchPostData( postId, postType );
						setCurrentPost( postData );
						setIsLoadingPostData( false );
					} catch ( error ) {
						console.error( 'Error fetching post:', error );
						setIsLoadingPostData( false );
						setIsModalOpen( false );
						showSnackbar(
							'Error loading post data. Please try again.',
							'error'
						);
					}
				}
			}
		};

		document.body.addEventListener( 'click', handleClick );
		return () => {
			document.body.removeEventListener( 'click', handleClick );
		};
	}, [] );

	// Set up bulk action handler
	useEffect( () => {
		const handleBulkDuplicate = async ( event ) => {
			const { postIds, postType } = event.detail;

			if ( ! postIds || postIds.length === 0 ) {
				return;
			}

			const mode = postDuplicatorVars.mode || 'advanced';

			// Check if basic mode - auto-duplicate all posts
			if ( mode === 'basic' ) {
				setIsLoadingPostData( true );
				const action = postDuplicatorVars.listMultipleAfterDuplicationAction || 'notice';
				// Only open modal if action is 'notice', otherwise we'll refresh
				if ( action === 'notice' ) {
					setModalMode( 'bulk' );
					setIsModalOpen( true );
				}

				try {
					// Fetch all posts in parallel
					const postDataPromises = postIds.map( ( postId ) =>
						fetchPostData( postId, postType ).catch( ( error ) => {
							console.error( `Error fetching post ${ postId }:`, error );
							return null;
						} )
					);

					const postDataArray = await Promise.all( postDataPromises );
					const validPosts = postDataArray.filter( ( post ) => post !== null );

					if ( validPosts.length === 0 ) {
						setIsLoadingPostData( false );
						setIsModalOpen( false );
						showSnackbar(
							'Error loading post data. Please try again.',
							'error'
						);
						return;
					}

					// Duplicate all posts sequentially
					const duplicationResults = [];
					for ( const post of validPosts ) {
						try {
							// Prepare taxonomy and custom meta data
							let taxonomyData = {};
							if ( post.taxonomies ) {
								post.taxonomies.forEach( ( taxonomy ) => {
									taxonomyData[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
								} );
							}
							const customMetaData = ( post.customMeta || [] ).map( ( meta ) => ( {
								key: meta.key,
								value: meta.value,
								type: meta.type || 'string',
								isSerialized: meta.isSerialized || false,
							} ) );

							// Prepare settings with defaults
							const duplicateSettings = {
								...postDuplicatorVars.defaultSettings,
								includeTaxonomies: true,
								includeCustomMeta: true,
								taxonomyData: taxonomyData,
								customMetaData: customMetaData,
								featuredImageId: post.featuredImage?.id || null,
							};

							const finalPostType = duplicateSettings.type === 'same' ? post.type : duplicateSettings.type;
							const finalTitle = `${ post.title } ${ postDuplicatorVars.defaultSettings.title }`;

							await duplicatePost( post.id, duplicateSettings, {
								onSuccess: ( result ) => {
									duplicationResults.push( {
										success: true,
										postId: result.duplicate_id,
										title: finalTitle,
										originalPost: post,
										featuredImage: post.featuredImage,
										postType: finalPostType,
									} );
								},
								onError: ( error ) => {
									duplicationResults.push( {
										success: false,
										error: error.message || error.data?.message || 'Unknown error',
										title: finalTitle,
										originalPost: post,
									} );
								},
							} );
						} catch ( error ) {
							console.error( `Error duplicating post ${ post.id }:`, error );
							duplicationResults.push( {
								success: false,
								error: error.message || 'Unknown error',
								title: `${ post.title } ${ postDuplicatorVars.defaultSettings.title }`,
								originalPost: post,
							} );
						}
					}

					setIsLoadingPostData( false );
					const action = postDuplicatorVars.listMultipleAfterDuplicationAction || 'notice';
					
					if ( action === 'notice' ) {
						// Show results modal
						setWasDuplicated( true );
						setInitialDuplicationResults( duplicationResults );
					} else if ( action === 'refresh' ) {
						// Refresh the page
						setWasDuplicated( true );
						window.location.reload();
					}
				} catch ( error ) {
					console.error( 'Error in bulk duplication:', error );
					setIsLoadingPostData( false );
					setIsModalOpen( false );
					showSnackbar(
						'Error duplicating posts. Please try again.',
						'error'
					);
				}
			} else {
				// Advanced mode - show configuration modal
				setPostsToDuplicate( null );
				setCurrentPost( null );
				setModalMode( 'bulk' );
				setIsLoadingPostData( true );
				setIsModalOpen( true );

				// Fetch all posts in parallel after opening modal
				try {
					const postDataPromises = postIds.map( ( postId ) =>
						fetchPostData( postId, postType ).catch( ( error ) => {
							console.error( `Error fetching post ${ postId }:`, error );
							return null; // Return null for failed fetches
						} )
					);

					const postDataArray = await Promise.all( postDataPromises );
					
					// Filter out null values (failed fetches)
					const validPosts = postDataArray.filter( ( post ) => post !== null );

					if ( validPosts.length === 0 ) {
						setIsLoadingPostData( false );
						setIsModalOpen( false );
						showSnackbar(
							'Error loading post data. Please try again.',
							'error'
						);
						return;
					}

					// Convert to postsToDuplicate format
					const postsForModal = validPosts.map( ( post, index ) => ( {
						id: `bulk-${ post.id }-${ index }`,
						originalPost: post,
						settings: { ...postDuplicatorVars.defaultSettings },
						isDuplicate: false,
					} ) );

					setPostsToDuplicate( postsForModal );
					setIsLoadingPostData( false );
				} catch ( error ) {
					console.error( 'Error fetching posts:', error );
					setIsLoadingPostData( false );
					setIsModalOpen( false );
					showSnackbar(
						'Error loading post data. Please try again.',
						'error'
					);
				}
			}
		};

		// Listen for custom bulk duplicate event
		document.addEventListener( 'm4c:bulk-duplicate', handleBulkDuplicate );

		return () => {
			document.removeEventListener( 'm4c:bulk-duplicate', handleBulkDuplicate );
		};
	}, [] );

	const handleClose = () => {
		const shouldRefresh = wasDuplicated && isPostsListScreen();
		setIsModalOpen( false );
		setCurrentPost( null );
		setPostsToDuplicate( null );
		setWasDuplicated( false );
		setIsLoadingPostData( false );
		setInitialDuplicationResults( null );
		
		// Refresh page if on posts list screen and posts were duplicated
		if ( shouldRefresh ) {
			window.location.reload();
		}
	};

	return (
		<DuplicateModal
			isOpen={ isModalOpen }
			onClose={ handleClose }
			onDuplicate={ handleDuplicate }
			originalPost={ currentPost }
			postsToDuplicate={ postsToDuplicate }
			mode={ modalMode }
			defaultSettings={ postDuplicatorVars.defaultSettings }
			postTypes={ postDuplicatorVars.postTypes }
			statusChoices={ postDuplicatorVars.statusChoices }
			siteUrl={ postDuplicatorVars.siteUrl }
			currentUser={ postDuplicatorVars.currentUser }
			isLoadingPostData={ isLoadingPostData }
			initialDuplicationResult={ initialDuplicationResults }
		/>
	);
};

// Mount the React component
document.addEventListener( 'DOMContentLoaded', function () {
	const modalRoot = document.createElement( 'div' );
	modalRoot.id = 'duplicate-post-modal-root';
	document.body.appendChild( modalRoot );

	// Use createRoot for React 18
	const root = createRoot( modalRoot );
	root.render( <DuplicatePostHandler /> );
} );

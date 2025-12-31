import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/editor';
import { Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import DuplicateModal from './components/DuplicateModal';
import { duplicatePost } from './utils/duplicatePost';
import './css/gutenberg-button.scss';

const DuplicatePostButton = () => {
	const [ isLoading, setIsLoading ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ taxonomies, setTaxonomies ] = useState( [] );
	const [ customMeta, setCustomMeta ] = useState( [] );
	const [ featuredImage, setFeaturedImage ] = useState( null );
	const [ parentPost, setParentPost ] = useState( null );
	const [ duplicationResult, setDuplicationResult ] = useState( null );
	const [ showSuccessModal, setShowSuccessModal ] = useState( false );

	// Check if current post type is enabled for duplication
	const enabledPostTypes = window.postDuplicatorVars?.enabledPostTypesForDuplication || [];

	const {
		postId,
		postType,
		postStatus,
		postTypeLabel,
		postTitle,
		postSlug,
		postDate,
		postAuthor,
		postParent,
		featuredMediaId,
	} = useSelect( ( select ) => {
		const editor = select( 'core/editor' );
		const currentPost = editor.getCurrentPost();
		const currentPostType = editor.getCurrentPostType();
		const postTypeObj = select( 'core' ).getPostType( currentPostType );
		const authorId = editor.getEditedPostAttribute( 'author' );

		// Get author name
		const author = select( 'core' ).getUser( authorId );
		const authorName = author ? author.name : 'Unknown Author';

		// Get parent post ID
		const parentId = editor.getEditedPostAttribute( 'parent' ) || 0;

		// Get featured media ID
		const featuredMediaId =
			editor.getEditedPostAttribute( 'featured_media' );

		return {
			postId: currentPost.id,
			postType: currentPostType,
			postStatus: editor.getEditedPostAttribute( 'status' ),
			postTypeLabel: postTypeObj
				? postTypeObj.labels.singular_name
				: 'Post',
			postTitle: editor.getEditedPostAttribute( 'title' ),
			postSlug: editor.getEditedPostAttribute( 'slug' ),
			postDate: editor.getEditedPostAttribute( 'date' ),
			postAuthor: authorName,
			postParent: parentId,
			featuredMediaId: featuredMediaId,
		};
	}, [] );

	// Don't render button if post type is not enabled for duplication
	if ( ! enabledPostTypes.includes( postType ) ) {
		return null;
	}

	// Fetch taxonomy, custom meta, and featured image data when modal opens
	useEffect( () => {
		if ( isModalOpen && postId ) {
			const fetchPostData = async () => {
				try {
					// Fetch taxonomy and custom meta data
					const response = await fetch(
						`${ postDuplicatorVars.restUrl }post-data/${ postId }`,
						{
							headers: {
								'X-WP-Nonce': postDuplicatorVars.nonce,
							},
						}
					);

					if ( response.ok ) {
						const postData = await response.json();
						setTaxonomies( postData.taxonomies || [] );
						setCustomMeta( postData.customMeta || [] );
					}

					// Fetch full post data (includes featured image and parent post info)
					const fullDataResponse = await fetch(
						`${ postDuplicatorVars.restUrl }post-full-data/${ postId }`,
						{
							headers: {
								'X-WP-Nonce': postDuplicatorVars.nonce,
							},
						}
					);

					if ( fullDataResponse.ok ) {
						const fullPostData = await fullDataResponse.json();
						
						// Set featured image from full post data
						setFeaturedImage( fullPostData.featuredImage || null );
						
						// Set parent post from full post data
						setParentPost( fullPostData.parentPost || null );
					} else {
						// Fallback: try to fetch parent post separately if full data fetch fails
						if ( postParent && postParent > 0 ) {
							try {
								const parentResponse = await fetch(
									`${ postDuplicatorVars.restUrl }post-full-data/${ postParent }`,
									{
										headers: {
											'X-WP-Nonce': postDuplicatorVars.nonce,
										},
									}
								);

								if ( parentResponse.ok ) {
									const parentData = await parentResponse.json();
									setParentPost( {
										id: parentData.id,
										title: parentData.title,
									} );
								} else {
									setParentPost( null );
								}
							} catch ( error ) {
								console.error( 'Error fetching parent post:', error );
								setParentPost( null );
							}
						} else {
							setParentPost( null );
						}
						
						// Fallback for featured image if full data fetch fails
						setFeaturedImage( null );
					}
				} catch ( error ) {
					console.error( 'Error fetching post data:', error );
					// Continue without taxonomy/meta data
					setFeaturedImage( null );
					setParentPost( null );
				}
			};

			fetchPostData();
		}
	}, [ isModalOpen, postId, postParent, postType, featuredMediaId ] );

	// Only show for published posts
	if ( postStatus !== 'publish' || ! postId ) {
		return null;
	}

	const handleDuplicate = async ( duplicatePostId, settings, callbacks ) => {
		setIsLoading( true );
		setError( null );

		// Use the postId passed or fall back to current postId
		const targetPostId = duplicatePostId || postId;

		await duplicatePost( targetPostId, settings, {
			onSuccess: ( result ) => {
				setIsLoading( false );
				if ( callbacks?.onSuccess ) {
					callbacks.onSuccess( result );
				}
			},
			onError: ( error ) => {
				setError(
					error.message ||
						error.data?.message ||
						'Failed to duplicate post'
				);
				setIsLoading( false );
				if ( callbacks?.onError ) {
					callbacks.onError( error );
				}
			},
		} );
	};

	const handleBasicModeDuplicate = async () => {
		setIsLoading( true );
		setError( null );

		// Fetch post data first to get taxonomies and custom meta
		try {
			// Fetch taxonomy and custom meta data
			const response = await fetch(
				`${ postDuplicatorVars.restUrl }post-data/${ postId }`,
				{
					headers: {
						'X-WP-Nonce': postDuplicatorVars.nonce,
					},
				}
			);

			let taxonomyData = {};
			let customMetaData = [];

			if ( response.ok ) {
				const postData = await response.json();
				const taxonomiesData = postData.taxonomies || [];
				// Initialize taxonomy data from assigned terms
				taxonomiesData.forEach( ( taxonomy ) => {
					taxonomyData[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
				} );
				customMetaData = ( postData.customMeta || [] ).map( ( meta ) => ( {
					key: meta.key,
					value: meta.value,
					type: meta.type || 'string',
					isSerialized: meta.isSerialized || false,
				} ) );
			}

			// Fetch featured image
			const fullDataResponse = await fetch(
				`${ postDuplicatorVars.restUrl }post-full-data/${ postId }`,
				{
					headers: {
						'X-WP-Nonce': postDuplicatorVars.nonce,
					},
				}
			);

			let featuredImageId = null;
			let featuredImageData = null;
			if ( fullDataResponse.ok ) {
				const fullPostData = await fullDataResponse.json();
				featuredImageId = fullPostData.featuredImage?.id || null;
				featuredImageData = fullPostData.featuredImage || null;
			}

			// Prepare settings with defaults and include taxonomies/custom meta
			const duplicateSettings = {
				...postDuplicatorVars.defaultSettings,
				includeTaxonomies: true,
				includeCustomMeta: true,
				taxonomyData: taxonomyData,
				customMetaData: customMetaData,
				featuredImageId: featuredImageId,
			};

			// Duplicate the post
			await duplicatePost( postId, duplicateSettings, {
				onSuccess: async ( result ) => {
					setIsLoading( false );
					const action = postDuplicatorVars.singleAfterDuplicationAction || 'notice';
					const finalPostType = duplicateSettings.type === 'same' ? postType : duplicateSettings.type;
					const finalTitle = `${ postTitle } ${ postDuplicatorVars.defaultSettings.title }`;
					
					// Fetch featured image from duplicated post
					let duplicatedFeaturedImage = null;
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
					
					if ( action === 'notice' ) {
						// Show success modal
						setDuplicationResult( {
							postId: result.duplicate_id,
							title: finalTitle,
							featuredImage: duplicatedFeaturedImage,
							postType: finalPostType,
						} );
						setShowSuccessModal( true );
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
						window.location.reload();
					}
				},
				onError: ( error ) => {
					setError(
						error.message ||
							error.data?.message ||
							'Failed to duplicate post'
					);
					setIsLoading( false );
				},
			} );
		} catch ( error ) {
			console.error( 'Error in basic mode duplication:', error );
			setError( 'Failed to duplicate post. Please try again.' );
			setIsLoading( false );
		}
	};

	const originalPost = {
		id: postId,
		title: postTitle,
		type: postType,
		status: postStatus,
		slug: postSlug,
		date: postDate,
		author: postAuthor,
		parent: postParent || 0,
		parentPost: parentPost || null,
		taxonomies: taxonomies,
		customMeta: customMeta,
		featuredImage: featuredImage,
	};

	return (
		<>
			<PluginPostStatusInfo className="m4c-duplicate-post-status-info">
				<div
					className="m4c-duplicate-post-wrapper"
					style={ { paddingTop: '16px' } }
				>
					<Button
						variant="secondary"
						className="m4c-duplicate-post-gutenberg"
						onClick={ () => {
							const mode = postDuplicatorVars.mode || 'advanced';
							if ( mode === 'basic' ) {
								handleBasicModeDuplicate();
							} else {
								setIsModalOpen( true );
							}
						} }
						disabled={ isLoading }
					>
						{ __(
							`Duplicate ${ postTypeLabel }`,
							'post-duplicator'
						) }
					</Button>
					{ error && (
						<div className="m4c-duplicate-error">{ error }</div>
					) }
				</div>
			</PluginPostStatusInfo>
			<DuplicateModal
				isOpen={ isModalOpen || showSuccessModal }
				onClose={ () => {
					setIsModalOpen( false );
					setShowSuccessModal( false );
					setDuplicationResult( null );
				} }
				onDuplicate={ handleDuplicate }
				originalPost={ originalPost }
				defaultSettings={ postDuplicatorVars.defaultSettings }
				postTypes={ postDuplicatorVars.postTypes }
				statusChoices={ postDuplicatorVars.statusChoices }
				siteUrl={ postDuplicatorVars.siteUrl }
				currentUser={ postDuplicatorVars.currentUser }
				initialDuplicationResult={ showSuccessModal && duplicationResult ? {
					postId: duplicationResult.postId,
					title: duplicationResult.title,
					featuredImage: duplicationResult.featuredImage,
					postType: duplicationResult.postType,
				} : null }
			/>
		</>
	);
};

registerPlugin( 'post-duplicator-button', {
	render: DuplicatePostButton,
} );

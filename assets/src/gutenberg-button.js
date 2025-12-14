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

	// Fetch taxonomy, custom meta, and featured image data when modal opens
	useEffect( () => {
		if ( isModalOpen && postId ) {
			const fetchPostData = async () => {
				try {
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

					// Fetch parent post if available
					if ( postParent && postParent > 0 ) {
						try {
							const baseUrl = postDuplicatorVars.restUrl.replace(
								'post-duplicator/v1/',
								'wp/v2/'
							);
							const parentEndpointType =
								postType === 'page'
									? 'pages'
									: postType === 'post'
									? 'posts'
									: postType;
							const parentResponse = await fetch(
								`${ baseUrl }${ parentEndpointType }/${ postParent }`,
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
									title: parentData.title.rendered,
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

					// Fetch featured image if available
					if ( featuredMediaId && featuredMediaId > 0 ) {
						try {
							const baseUrl = postDuplicatorVars.restUrl.replace(
								'post-duplicator/v1/',
								'wp/v2/'
							);
							const mediaResponse = await fetch(
								`${ baseUrl }media/${ featuredMediaId }`,
								{
									headers: {
										'X-WP-Nonce': postDuplicatorVars.nonce,
									},
								}
							);

							if ( mediaResponse.ok ) {
								const mediaData = await mediaResponse.json();
								setFeaturedImage( {
									id: mediaData.id,
									url: mediaData.source_url,
									thumbnail:
										mediaData.media_details?.sizes
											?.thumbnail?.source_url ||
										mediaData.source_url,
									alt: mediaData.alt_text || '',
								} );
							} else {
								setFeaturedImage( null );
							}
						} catch ( error ) {
							console.error(
								'Error fetching featured image:',
								error
							);
							setFeaturedImage( null );
						}
					} else {
						setFeaturedImage( null );
					}
				} catch ( error ) {
					console.error( 'Error fetching post data:', error );
					// Continue without taxonomy/meta data
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
						onClick={ () => setIsModalOpen( true ) }
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
				isOpen={ isModalOpen }
				onClose={ () => setIsModalOpen( false ) }
				onDuplicate={ handleDuplicate }
				originalPost={ originalPost }
				defaultSettings={ postDuplicatorVars.defaultSettings }
				postTypes={ postDuplicatorVars.postTypes }
				statusChoices={ postDuplicatorVars.statusChoices }
				siteUrl={ postDuplicatorVars.siteUrl }
				currentUser={ postDuplicatorVars.currentUser }
			/>
		</>
	);
};

registerPlugin( 'post-duplicator-button', {
	render: DuplicatePostButton,
} );

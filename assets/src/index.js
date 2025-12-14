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
const fetchPostData = async ( postId, postType = 'post' ) => {
	const baseUrl = postDuplicatorVars.restUrl.replace(
		'post-duplicator/v1/',
		'wp/v2/'
	);
	const endpointType =
		postType === 'page'
			? 'pages'
			: postType === 'post'
			? 'posts'
			: postType;
	const endpoint = `${ baseUrl }${ endpointType }/${ postId }`;

	const response = await fetch( endpoint, {
		headers: {
			'X-WP-Nonce': postDuplicatorVars.nonce,
		},
	} );

	if ( ! response.ok ) {
		throw new Error( 'Failed to fetch post data' );
	}

	const post = await response.json();

	// Fetch author data
	let authorName = 'Unknown Author';
	if ( post.author && post.author > 0 ) {
		try {
			const authorResponse = await fetch(
				`${ postDuplicatorVars.restUrl.replace(
					'post-duplicator/v1/',
					'wp/v2/'
				) }users/${ post.author }`,
				{
					headers: {
						'X-WP-Nonce': postDuplicatorVars.nonce,
					},
				}
			);

			if ( authorResponse.ok ) {
				const authorData = await authorResponse.json();
				authorName = authorData.name;
			}
		} catch ( error ) {
			console.error( 'Error fetching author:', error );
		}
	}

	// Fetch featured image data
	let featuredImage = null;
	if ( post.featured_media && post.featured_media > 0 ) {
		try {
			const mediaResponse = await fetch(
				`${ postDuplicatorVars.restUrl.replace(
					'post-duplicator/v1/',
					'wp/v2/'
				) }media/${ post.featured_media }`,
				{
					headers: {
						'X-WP-Nonce': postDuplicatorVars.nonce,
					},
				}
			);

			if ( mediaResponse.ok ) {
				const mediaData = await mediaResponse.json();
				featuredImage = {
					id: mediaData.id,
					url: mediaData.source_url,
					thumbnail:
						mediaData.media_details?.sizes?.thumbnail?.source_url ||
						mediaData.source_url,
					alt: mediaData.alt_text || '',
				};
			}
		} catch ( error ) {
			console.error( 'Error fetching featured image:', error );
		}
	}

	// Fetch parent post data
	let parentPost = null;
	if ( post.parent && post.parent > 0 ) {
		try {
			const parentEndpointType =
				postType === 'page'
					? 'pages'
					: postType === 'post'
					? 'posts'
					: postType;
			const parentResponse = await fetch(
				`${ baseUrl }${ parentEndpointType }/${ post.parent }`,
				{
					headers: {
						'X-WP-Nonce': postDuplicatorVars.nonce,
					},
				}
			);

			if ( parentResponse.ok ) {
				const parentData = await parentResponse.json();
				parentPost = {
					id: parentData.id,
					title: parentData.title.rendered,
				};
			}
		} catch ( error ) {
			console.error( 'Error fetching parent post:', error );
		}
	}

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
		title: post.title.rendered,
		type: post.type,
		status: post.status,
		slug: post.slug,
		date: post.date,
		author: authorName,
		authorId: post.author,
		parent: post.parent || 0,
		parentPost: parentPost,
		taxonomies: taxonomies,
		customMeta: customMeta,
		featuredImage: featuredImage,
	};
};

// React component to handle modal state
const DuplicatePostHandler = () => {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ currentPost, setCurrentPost ] = useState( null );
	const [ postsToDuplicate, setPostsToDuplicate ] = useState( null );
	const [ modalMode, setModalMode ] = useState( 'single' );
	const [ wasDuplicated, setWasDuplicated ] = useState( false );
	
	// Check if we're on the posts list screen
	const isPostsListScreen = () => {
		// Check if we're on an edit screen (wp-admin/edit.php)
		return window.location.href.includes( '/wp-admin/edit.php' ) || 
		       window.location.href.includes( '/wp-admin/post.php' ) === false;
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

				try {
					const postData = await fetchPostData( postId, postType );
					setCurrentPost( postData );
					setPostsToDuplicate( null );
					setModalMode( 'single' );
					setIsModalOpen( true );
				} catch ( error ) {
					console.error( 'Error fetching post:', error );
					showSnackbar(
						'Error loading post data. Please try again.',
						'error'
					);
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

			try {
				// Fetch all posts in parallel
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
				setCurrentPost( null );
				setModalMode( 'bulk' );
				setIsModalOpen( true );
			} catch ( error ) {
				console.error( 'Error fetching posts:', error );
				showSnackbar(
					'Error loading post data. Please try again.',
					'error'
				);
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

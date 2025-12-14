import { useState } from '@wordpress/element';
import { __experimentalVStack as VStack } from '@wordpress/components';
import PostItem from './PostItem';

const PostList = ( {
	posts,
	onPostsChange,
	postTypes,
	statusChoices,
	siteUrl,
	currentUser,
	defaultSettings,
	isMultipleClonesMode = false,
} ) => {
	const [ expandedPosts, setExpandedPosts ] = useState( new Set() );

	const handlePostUpdate = ( updatedPost ) => {
		const updatedPosts = posts.map( ( post ) =>
			post.id === updatedPost.id ? updatedPost : post
		);
		if ( onPostsChange ) {
			onPostsChange( updatedPosts );
		}
	};

	const handlePostDelete = ( postId ) => {
		// Check if this is the last clone in multiple-clones mode
		if ( isMultipleClonesMode ) {
			const clonePosts = posts.filter( ( p ) => p.isDuplicate );
			// Don't allow deletion if it's the last clone (must have at least 1 clone remaining)
			if ( clonePosts.length <= 1 && clonePosts.some( ( p ) => p.id === postId ) ) {
				return; // Prevent deletion
			}
		}
		
		const updatedPosts = posts.filter( ( post ) => post.id !== postId );
		if ( onPostsChange ) {
			onPostsChange( updatedPosts );
		}
		// Remove from expanded set if it was expanded
		const newExpanded = new Set( expandedPosts );
		newExpanded.delete( postId );
		setExpandedPosts( newExpanded );
	};

	const handleToggleExpand = ( postId ) => {
		const newExpanded = new Set( expandedPosts );
		if ( newExpanded.has( postId ) ) {
			newExpanded.delete( postId );
		} else {
			newExpanded.add( postId );
		}
		setExpandedPosts( newExpanded );
	};

	if ( ! posts || posts.length === 0 ) {
		return null;
	}

	// Calculate if delete button should be shown for each post
	const getShowDelete = ( post ) => {
		if ( ! isMultipleClonesMode ) {
			return true; // Always show delete in bulk mode
		}
		// In multiple-clones mode, only show delete for clones, and hide if only 1 clone remains
		if ( post.isDuplicate ) {
			const cloneCount = posts.filter( ( p ) => p.isDuplicate ).length;
			return cloneCount > 1; // Hide delete button when only 1 clone remains
		}
		return false; // Never show delete for the original post in multiple-clones mode
	};

	return (
		<VStack spacing="12px" className="duplicate-post-modal__post-list">
			{ posts.map( ( post, index ) => (
				<PostItem
					key={ post.id }
					post={ post }
					index={ index }
					onUpdate={ handlePostUpdate }
					onDelete={ handlePostDelete }
					onToggleExpand={ handleToggleExpand }
					isExpanded={ expandedPosts.has( post.id ) }
					showDelete={ getShowDelete( post ) }
					postTypes={ postTypes }
					statusChoices={ statusChoices }
					siteUrl={ siteUrl }
					currentUser={ currentUser }
					defaultSettings={ defaultSettings }
				/>
			) ) }
		</VStack>
	);
};

export default PostList;


/**
 * Shared utility function to duplicate a post
 *
 * @param {number} postId - The ID of the post to duplicate
 * @param {Object} settings - The duplication settings
 * @param {Object} callbacks - Callback functions for different scenarios
 * @param {Function} callbacks.onSuccess - Called when duplication succeeds, receives { duplicate_id }
 * @param {Function} callbacks.onError - Called when duplication fails, receives error object
 * @returns {Promise} Promise that resolves when the duplication is complete
 */
export const duplicatePost = async ( postId, settings, callbacks = {} ) => {
	const { onSuccess, onError } = callbacks;
	const parsedParentId = Number.parseInt( settings?.selectedParentId, 10 );
	const normalizedParentId =
		Number.isFinite( parsedParentId ) && parsedParentId > 0
			? parsedParentId
			: null;

	try {
		const response = await fetch(
			`${ postDuplicatorVars.restUrl }duplicate-post`,
			{
				method: 'POST',
				headers: {
					'X-WP-Nonce': postDuplicatorVars.nonce,
					'Content-Type': 'application/json',
				},
				body: JSON.stringify( {
					original_id: postId,
					...settings,
					// Always send a normalized parent id (or null for "No Parent")
					selectedParentId: normalizedParentId,
				} ),
			}
		);

		if ( ! response.ok ) {
			const errorData = await response.json();
			const error = new Error(
				errorData.message || 'Failed to duplicate post'
			);
			error.data = errorData;
			throw error;
		}

		const result = await response.json();

		if ( result.duplicate_id && onSuccess ) {
			onSuccess( result );
		}

		return result;
	} catch ( error ) {
		console.error( 'Error duplicating post:', error );
		if ( onError ) {
			onError( error );
		}
		throw error;
	}
};

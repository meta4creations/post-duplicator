import { useState, useEffect } from '@wordpress/element';
import {
	Button,
	__experimentalVStack as VStack,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { chevronDown, chevronUp, trash } from '@wordpress/icons';
import DuplicateSettingsFields from './DuplicateSettingsFields';
import TaxonomySection from './TaxonomySection';
import CustomMetaSection from './CustomMetaSection';

const PostItem = ( {
	post,
	index,
	onUpdate,
	onDelete,
	onToggleExpand,
	isExpanded: controlledExpanded,
	showDelete = true,
	postTypes,
	statusChoices,
	siteUrl,
	currentUser,
	defaultSettings,
} ) => {
	const [ settings, setSettings ] = useState( post.settings || defaultSettings );
	const [ includeTaxonomies, setIncludeTaxonomies ] = useState( true );
	const [ includeCustomMeta, setIncludeCustomMeta ] = useState( true );
	const [ taxonomyData, setTaxonomyData ] = useState( {} );
	const [ customMetaData, setCustomMetaData ] = useState( [] );
	const [ featuredImage, setFeaturedImage ] = useState( post.originalPost?.featuredImage || null );

	// Use controlled expanded state if provided, otherwise use internal state
	const [ internalExpanded, setInternalExpanded ] = useState( false );
	const isExpanded = controlledExpanded !== undefined ? controlledExpanded : internalExpanded;

	// Initialize taxonomy data from original post
	useEffect( () => {
		if ( post.originalPost?.taxonomies ) {
			const initialTaxonomyData = {};
			post.originalPost.taxonomies.forEach( ( taxonomy ) => {
				initialTaxonomyData[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
			} );
			setTaxonomyData( initialTaxonomyData );
		} else {
			setTaxonomyData( {} );
		}
	}, [ post.originalPost ] );

	// Initialize custom meta data from original post
	useEffect( () => {
		if ( post.originalPost?.customMeta ) {
			setCustomMetaData(
				post.originalPost.customMeta.map( ( meta ) => ( {
					key: meta.key,
					value: meta.value,
					type: meta.type || 'string',
					isSerialized: meta.isSerialized || false,
				} ) )
			);
		} else {
			setCustomMetaData( [] );
		}
	}, [ post.originalPost ] );

	// Initialize featured image from original post
	useEffect( () => {
		if ( post.originalPost?.featuredImage ) {
			setFeaturedImage( post.originalPost.featuredImage );
		} else {
			setFeaturedImage( null );
		}
	}, [ post.originalPost ] );

	// Update parent when settings change
	useEffect( () => {
		if ( onUpdate ) {
			onUpdate( {
				...post,
				settings,
				taxonomyData,
				customMetaData,
				featuredImage,
				includeTaxonomies,
				includeCustomMeta,
			} );
		}
	}, [ settings, taxonomyData, customMetaData, featuredImage, includeTaxonomies, includeCustomMeta ] );

	const handleToggleExpand = () => {
		if ( onToggleExpand ) {
			onToggleExpand( post.id );
		} else {
			setInternalExpanded( ! internalExpanded );
		}
	};

	const handleDelete = ( e ) => {
		e.stopPropagation(); // Prevent triggering expand/collapse
		if ( onDelete ) {
			onDelete( post.id );
		}
	};

	const handleHeaderClick = ( e ) => {
		// Don't toggle if clicking on delete button or its parent actions container
		if ( e.target.closest( '.duplicate-post-modal__post-item-actions' ) ) {
			return;
		}
		handleToggleExpand();
	};

	const handleTaxonomyChange = ( selectedTerms ) => {
		setTaxonomyData( selectedTerms );
	};

	const handleCustomMetaChange = ( metaFields ) => {
		setCustomMetaData( metaFields );
	};

	// Get the display title
	const getDisplayTitle = () => {
		if ( settings?.fullTitle ) {
			return settings.fullTitle;
		}
		const titleSuffix = settings?.title || __( 'Copy', 'post-duplicator' );
		return `${ post.originalPost?.title || '' } ${ titleSuffix }`;
	};

	const displayTitle = getDisplayTitle();
	const thumbnail = featuredImage?.thumbnail || featuredImage?.url || null;

	return (
		<div
			className={ `duplicate-post-modal__post-item ${
				isExpanded
					? 'duplicate-post-modal__post-item--expanded'
					: 'duplicate-post-modal__post-item--collapsed'
			}` }
		>
			<div 
				className="duplicate-post-modal__post-item-header"
				onClick={ handleHeaderClick }
			>
				<HStack spacing="12px" alignment="center">
					{ thumbnail && (
						<img
							src={ thumbnail }
							alt=""
							className="duplicate-post-modal__post-item-thumbnail"
						/>
					) }
					<h3 className="duplicate-post-modal__post-item-title">
						{ displayTitle }
					</h3>
					<div className="duplicate-post-modal__post-item-actions" onClick={ ( e ) => e.stopPropagation() }>
						<Button
							icon={ isExpanded ? chevronUp : chevronDown }
							variant="tertiary"
							onClick={ ( e ) => {
								e.stopPropagation();
								handleToggleExpand();
							} }
							label={
								isExpanded
									? __( 'Collapse', 'post-duplicator' )
									: __( 'Expand', 'post-duplicator' )
							}
						/>
						{ showDelete && (
							<Button
								icon={ trash }
								variant="tertiary"
								onClick={ handleDelete }
								label={ __( 'Remove', 'post-duplicator' ) }
							/>
						) }
					</div>
				</HStack>
			</div>

			{ isExpanded && (
				<div className="duplicate-post-modal__post-item-content">
					<VStack spacing="20px">
						<DuplicateSettingsFields
							settings={ settings }
							onSettingsChange={ setSettings }
							postTypes={ postTypes }
							statusChoices={ statusChoices }
							originalPost={ post.originalPost }
							featuredImage={ featuredImage }
							onFeaturedImageChange={ setFeaturedImage }
						/>

						{ post.originalPost?.taxonomies &&
							post.originalPost.taxonomies.length > 0 && (
								<TaxonomySection
									taxonomies={ post.originalPost.taxonomies }
									onChange={ handleTaxonomyChange }
									enabled={ includeTaxonomies }
									onToggle={ setIncludeTaxonomies }
								/>
							) }

						{ post.originalPost?.customMeta &&
							post.originalPost.customMeta.length > 0 && (
								<CustomMetaSection
									customMeta={ post.originalPost.customMeta }
									onChange={ handleCustomMetaChange }
									enabled={ includeCustomMeta }
									onToggle={ setIncludeCustomMeta }
								/>
							) }
					</VStack>
				</div>
			) }
		</div>
	);
};

export default PostItem;


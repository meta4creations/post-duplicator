import { useState, useEffect } from '@wordpress/element';
import {
	ToggleControl,
	FormTokenField,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const TaxonomySection = ( { taxonomies, onChange, enabled, onToggle } ) => {
	const [ isExpanded, setIsExpanded ] = useState( false );
	const [ selectedTerms, setSelectedTerms ] = useState( {} );

	// Initialize selected terms from taxonomies
	useEffect( () => {
		if ( taxonomies && taxonomies.length > 0 ) {
			const initialTerms = {};
			taxonomies.forEach( ( taxonomy ) => {
				// Use assignedTermIds if available (terms currently on the post)
				initialTerms[ taxonomy.slug ] = taxonomy.assignedTermIds || [];
			} );
			setSelectedTerms( initialTerms );
		}
	}, [ taxonomies ] );

	// Calculate counts
	const taxonomyCount = taxonomies ? taxonomies.length : 0;
	const totalTermsCount = taxonomies
		? taxonomies.reduce(
				( sum, taxonomy ) => sum + taxonomy.terms.length,
				0
		  )
		: 0;
	const selectedTermsCount = Object.values( selectedTerms ).reduce(
		( sum, termIds ) => sum + termIds.length,
		0
	);

	const handleTermChange = ( taxonomySlug, termIds ) => {
		const newSelectedTerms = {
			...selectedTerms,
			[ taxonomySlug ]: termIds,
		};
		setSelectedTerms( newSelectedTerms );

		// Notify parent of change
		if ( onChange ) {
			onChange( newSelectedTerms );
		}
	};

	const handleSectionClick = ( e ) => {
		// Don't expand if clicking the toggle
		if ( e.target.closest( '.components-toggle-control' ) ) {
			return;
		}
		setIsExpanded( ! isExpanded );
	};

	if ( ! taxonomies || taxonomies.length === 0 ) {
		return null;
	}

	return (
		<div 
			className="duplicate-post-modal__taxonomy-section"
			style={ {
				opacity: enabled ? 1 : 0.5,
			} }
		>
			<div
				className="duplicate-post-modal__section-header"
				onClick={ handleSectionClick }
				style={ {
					display: 'flex',
					alignItems: 'center',
					gap: '12px',
					padding: '12px',
					cursor: 'pointer',
					borderBottom: isExpanded
						? '1px solid rgba(0, 0, 0, 0.1)'
						: 'none',
				} }
			>
				<ToggleControl
					checked={ enabled }
					onChange={ onToggle }
					__nextHasNoMarginBottom
				/>
				<div style={ { flex: 1 } }>
					<span className="duplicate-post-modal__section-label">
						{ __( 'Taxonomies', 'post-duplicator' ) }
					</span>
					{ enabled && (
						<span className="duplicate-post-modal__section-count">
							{ ' ' }
							({ taxonomyCount }{ ' ' }
							{ __( 'taxonomies', 'post-duplicator' ) },{ ' ' }
							{ selectedTermsCount || totalTermsCount }{ ' ' }
							{ __( 'terms', 'post-duplicator' ) })
						</span>
					) }
				</div>
			</div>

			{ isExpanded && (
				<div
					className="duplicate-post-modal__section-content"
					style={ { padding: '16px' } }
				>
					<VStack spacing="20px">
						{ taxonomies.map( ( taxonomy ) => {
							const currentTermIds =
								selectedTerms[ taxonomy.slug ] || [];

							// Get ALL term names for suggestions
							const termSuggestions = taxonomy.terms.map(
								( term ) => term.name
							);
							
							// Get selected term names
							const selectedTermNames = taxonomy.terms
								.filter( ( term ) =>
									currentTermIds.includes( term.id )
								)
								.map( ( term ) => term.name );

							return (
								<div key={ taxonomy.slug }>
									<label
										style={ {
											display: 'block',
											marginBottom: '8px',
											fontWeight: 500,
										} }
									>
										{ taxonomy.label }
									</label>
									<FormTokenField
										value={ selectedTermNames }
										suggestions={ termSuggestions }
										onChange={ ( tokens ) => {
											// Convert term names back to IDs
											const newTermIds = tokens
												.map( ( tokenName ) => {
													const term =
														taxonomy.terms.find(
															( t ) =>
																t.name ===
																tokenName
														);
													return term
														? term.id
														: null;
												} )
												.filter(
													( id ) => id !== null
												);

											handleTermChange(
												taxonomy.slug,
												newTermIds
											);
										} }
										__experimentalExpandOnFocus
										__experimentalShowHowTo={ false }
										__nextHasNoMarginBottom
										__next40pxDefaultSize
									/>
								</div>
							);
						} ) }
					</VStack>
				</div>
			) }
		</div>
	);
};

export default TaxonomySection;

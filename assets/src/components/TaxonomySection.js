import { useState, useEffect, useRef, useCallback } from '@wordpress/element';
import {
	ToggleControl,
	FormTokenField,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const SEARCH_DEBOUNCE_MS = 300;
const SEARCH_LIMIT = 20;

const buildTermMaps = ( terms ) => {
	const byId = {};
	const byName = {};
	( terms || [] ).forEach( ( term ) => {
		byId[ term.id ] = term;
		byName[ term.name ] = term;
	} );
	return { byId, byName };
};

const TaxonomyTermField = ( { taxonomy, selectedTermIds, onChange } ) => {
	const [ suggestions, setSuggestions ] = useState( () =>
		( taxonomy.terms || [] ).map( ( term ) => term.name )
	);
	const termMapsRef = useRef( buildTermMaps( taxonomy.terms ) );
	const debounceRef = useRef( null );
	const abortRef = useRef( null );
	const requestIdRef = useRef( 0 );
	const hasFetchedInitialRef = useRef( false );

	const mergeTerms = ( terms ) => {
		const maps = termMapsRef.current;
		( terms || [] ).forEach( ( term ) => {
			maps.byId[ term.id ] = term;
			maps.byName[ term.name ] = term;
		} );
	};

	useEffect( () => {
		mergeTerms( taxonomy.terms );
	}, [ taxonomy.terms ] );

	const searchTerms = useCallback(
		async ( query ) => {
			const restUrl = window.postDuplicatorVars?.restUrl || '';
			const nonce = window.postDuplicatorVars?.nonce || '';
			if ( ! restUrl || ! nonce ) {
				return;
			}

			if ( abortRef.current ) {
				abortRef.current.abort();
			}

			const controller = new AbortController();
			abortRef.current = controller;
			const requestId = ++requestIdRef.current;

			const params = new URLSearchParams( {
				taxonomy: taxonomy.slug,
				number: String( SEARCH_LIMIT ),
			} );
			if ( query ) {
				params.set( 'search', query );
			}

			try {
				const response = await fetch(
					`${ restUrl }search-terms?${ params.toString() }`,
					{
						method: 'GET',
						headers: {
							'X-WP-Nonce': nonce,
						},
						signal: controller.signal,
					}
				);

				if ( ! response.ok || requestId !== requestIdRef.current ) {
					return;
				}

				const terms = await response.json();
				if ( ! Array.isArray( terms ) ) {
					return;
				}

				mergeTerms( terms );
				setSuggestions( terms.map( ( term ) => term.name ) );
			} catch ( error ) {
				if ( error.name !== 'AbortError' ) {
					console.error( 'Error searching terms:', error );
				}
			}
		},
		[ taxonomy.slug ]
	);

	useEffect( () => {
		return () => {
			if ( debounceRef.current ) {
				clearTimeout( debounceRef.current );
			}
			if ( abortRef.current ) {
				abortRef.current.abort();
			}
		};
	}, [] );

	const handleFocus = () => {
		if ( hasFetchedInitialRef.current ) {
			return;
		}
		hasFetchedInitialRef.current = true;
		searchTerms( '' );
	};

	const handleInputChange = ( value ) => {
		if ( debounceRef.current ) {
			clearTimeout( debounceRef.current );
		}
		debounceRef.current = setTimeout( () => {
			hasFetchedInitialRef.current = true;
			searchTerms( value.trim() );
		}, SEARCH_DEBOUNCE_MS );
	};

	const selectedTermNames = ( selectedTermIds || [] )
		.map( ( id ) => termMapsRef.current.byId[ id ]?.name )
		.filter( Boolean );

	return (
		<div onFocus={ handleFocus }>
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
				suggestions={ suggestions }
				placeholder={ __( 'Type to search terms', 'post-duplicator' ) }
				onInputChange={ handleInputChange }
				onChange={ ( tokens ) => {
					const newTermIds = tokens
						.map( ( tokenName ) => {
							const term =
								termMapsRef.current.byName[ tokenName ];
							return term ? term.id : null;
						} )
						.filter( ( id ) => id !== null );

					onChange( newTermIds );
				} }
				__experimentalValidateInput={ ( token ) =>
					Boolean( termMapsRef.current.byName[ token ] )
				}
				__experimentalExpandOnFocus
				__experimentalShowHowTo={ false }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				maxSuggestions={ SEARCH_LIMIT }
			/>
		</div>
	);
};

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

	const taxonomyCount = taxonomies.length;
	const selectedTermsCount = Object.values( selectedTerms ).reduce(
		( sum, termIds ) => sum + termIds.length,
		0
	);

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
							{ selectedTermsCount }{ ' ' }
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
						{ taxonomies.map( ( taxonomy ) => (
							<TaxonomyTermField
								key={ taxonomy.slug }
								taxonomy={ taxonomy }
								selectedTermIds={
									selectedTerms[ taxonomy.slug ] || []
								}
								onChange={ ( termIds ) =>
									handleTermChange( taxonomy.slug, termIds )
								}
							/>
						) ) }
					</VStack>
				</div>
			) }
		</div>
	);
};

export default TaxonomySection;

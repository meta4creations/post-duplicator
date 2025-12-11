import { __ } from '@wordpress/i18n';

const SettingsSummary = ( { settings, postTypes, statusChoices } ) => {
	const getStatusLabel = ( status ) => {
		return statusChoices[ status ] || status;
	};

	const getPostTypeLabel = ( type ) => {
		return postTypes[ type ] || type;
	};

	const getAuthorLabel = ( author ) => {
		return author === 'current_user'
			? __( 'Current User', 'post-duplicator' )
			: __( 'Original Post Author', 'post-duplicator' );
	};

	const getTimestampLabel = ( timestamp ) => {
		return timestamp === 'duplicate'
			? __( 'Duplicate Timestamp', 'post-duplicator' )
			: __( 'Current Time', 'post-duplicator' );
	};

	const getTimeOffsetSummary = () => {
		if ( ! settings.time_offset ) {
			return __( 'No offset', 'post-duplicator' );
		}

		const parts = [];
		if ( settings.time_offset_days > 0 )
			parts.push(
				`${ settings.time_offset_days } ${ __(
					'days',
					'post-duplicator'
				) }`
			);
		if ( settings.time_offset_hours > 0 )
			parts.push(
				`${ settings.time_offset_hours } ${ __(
					'hours',
					'post-duplicator'
				) }`
			);
		if ( settings.time_offset_minutes > 0 )
			parts.push(
				`${ settings.time_offset_minutes } ${ __(
					'minutes',
					'post-duplicator'
				) }`
			);
		if ( settings.time_offset_seconds > 0 )
			parts.push(
				`${ settings.time_offset_seconds } ${ __(
					'seconds',
					'post-duplicator'
				) }`
			);

		if ( parts.length === 0 ) {
			return __( 'No offset', 'post-duplicator' );
		}

		const direction =
			settings.time_offset_direction === 'newer'
				? __( 'newer', 'post-duplicator' )
				: __( 'older', 'post-duplicator' );
		return `${ parts.join( ', ' ) } ${ direction }`;
	};

	return (
		<div className="duplicate-settings-summary">
			<div className="duplicate-settings-summary__grid">
				<div className="duplicate-settings-summary__item">
					<span className="duplicate-settings-summary__label">
						{ __( 'Post Status:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						{ getStatusLabel( settings.status ) }
					</span>
				</div>
				<div className="duplicate-settings-summary__item">
					<span className="duplicate-settings-summary__label">
						{ __( 'Post Type:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						{ getPostTypeLabel( settings.type ) }
					</span>
				</div>
				<div className="duplicate-settings-summary__item">
					<span className="duplicate-settings-summary__label">
						{ __( 'Post Author:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						{ getAuthorLabel( settings.post_author ) }
					</span>
				</div>
				<div className="duplicate-settings-summary__item">
					<span className="duplicate-settings-summary__label">
						{ __( 'Post Date:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						{ getTimestampLabel( settings.timestamp ) }
					</span>
				</div>
				<div className="duplicate-settings-summary__item">
					<span className="duplicate-settings-summary__label">
						{ __( 'Title Suffix:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						"{ settings.title }"
					</span>
				</div>
				<div className="duplicate-settings-summary__item">
					<span className="duplicate-settings-summary__label">
						{ __( 'Slug Suffix:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						"{ settings.slug }"
					</span>
				</div>
				<div className="duplicate-settings-summary__item duplicate-settings-summary__item--full">
					<span className="duplicate-settings-summary__label">
						{ __( 'Time Offset:', 'post-duplicator' ) }
					</span>
					<span className="duplicate-settings-summary__value">
						{ getTimeOffsetSummary() }
					</span>
				</div>
			</div>
		</div>
	);
};

export default SettingsSummary;

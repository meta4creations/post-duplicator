import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const MarketingBanner = () => {
	const products = [
		{
			id: 'email-customizer',
			name: 'Email Customizer',
			tagline: __( 'Transform standard Gravity Forms notifications into stunning, on-brand emails using the familiar WordPress editor to build your templates.', 'post-duplicator' ),
			cta: __( 'Learn More →', 'post-duplicator' ),
			image: '/wp-content/plugins/post-duplicator/assets/img/marketing/email-customizer-bordered.svg',
			url: 'https://www.metaphorcreations.com/wordpress-plugins/email-customizer/?campaign=post-duplicator&ref=266',
		},
		// {
		// 	id: 'ditty',
		// 	name: 'Ditty',
		// 	tagline: __( 'Get access to all Ditty extensions with a single license.', 'post-duplicator' ),
		// 	cta: __( 'Learn More →', 'post-duplicator' ),
		// 	image: '/wp-content/plugins/post-duplicator/assets/img/marketing/ditty-everything.png',
		// 	url: 'https://www.metaphorcreations.com/ditty/pricing/',
		// },
	];

	// Randomly select a product on component mount
	const [ selectedProduct ] = useState( () => {
		const randomIndex = Math.floor( Math.random() * products.length );
		return products[ randomIndex ];
	} );

	return (
		<a
			href={ selectedProduct.url }
			target="_blank"
			rel="noopener noreferrer"
			className="duplicate-post-modal__marketing-banner"
		>
			<div className="duplicate-post-modal__marketing-banner-image">
				<img
					src={ selectedProduct.image }
					alt={ selectedProduct.name }
				/>
			</div>
			<div className="duplicate-post-modal__marketing-banner-content">
				<div className="duplicate-post-modal__marketing-banner-title">
					{ selectedProduct.name }
				</div>
				<div className="duplicate-post-modal__marketing-banner-tagline">
					{ selectedProduct.tagline }
				</div>
			</div>
			<div className="duplicate-post-modal__marketing-banner-cta">
				{ selectedProduct.cta }
			</div>
		</a>
	);
};

export default MarketingBanner;


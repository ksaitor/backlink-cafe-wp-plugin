import _apiFetch from '@wordpress/api-fetch';
import { useState, useEffect } from '@wordpress/element';
import { Offer } from './Offer';
import { Logo } from './Logo';
import { getMe, getOffers } from '../../wp-api';
import Preferences from './Preferences';

export function Settings() {
	const [ offers, setOffers ] = useState( wpBacklinkCafeBuild?.offers ?? [] );
	const [ tab, setTab ] = useState( 'offers' );
	const [ websiteInfo, setWebsiteInfo ] = useState(
		( wpBacklinkCafeBuild ?? { website_info: {} } )?.website_info
	);
	const [ formState, setFormState ] = useState( {
		domain: websiteInfo?.domain,
		pricePerLink: websiteInfo?.price,
		email: websiteInfo?.owner?.email,
		bitcoinAddress: websiteInfo?.bitcoinAddress,
		paypalEmail: websiteInfo?.paypalEmail,
	} );

	useEffect( () => {
		offersCallback();
		settingsCallback();
	}, [] );

	const offersCallback = async () => {
		await getOffers().then( setOffers );
	};

	const settingsCallback = async () => {
		getMe().then( ( website_info ) => {
			setWebsiteInfo( website_info );
			setFormState( {
				domain: website_info?.domain,
				pricePerLink: ( website_info?.price ?? 0 ) / 100,
				email: website_info?.email,
				bitcoinAddress: website_info?.bitcoinAddress,
				paypalEmail: website_info?.paypalEmail,
			} );
		} );
	};

	return (
		<>
			<link
				href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&display=swap"
				rel="stylesheet"
				type="text/css"
			/>
			<section className="w-full min-h-full p-2 md:p-10">
				<Logo />
				<div className="flex justify-between">
					<button
						className={ `mb-4 text-3xl no-underline [font-family:Dela_Gothic_One] ${
							tab === 'offers'
								? ''
								: 'opacity-50 hover:opacity-100 cursor-pointer'
						}` }
						disabled={ tab === 'offers' }
						onClick={ () => setTab( 'offers' ) }
					>
						Offers
					</button>
					<button
						className={ `mb-4 text-3xl no-underline [font-family:Dela_Gothic_One] ${
							tab === 'settings'
								? ''
								: 'opacity-50 hover:opacity-100 cursor-pointer'
						}` }
						disabled={ tab === 'settings' }
						onClick={ () => setTab( 'settings' ) }
					>
						Settings
					</button>
				</div>

				{ tab === 'settings' && (
					<Preferences
						callback={ settingsCallback }
						formState={ formState }
						setFormState={ setFormState }
						websiteInfo={ websiteInfo }
					/>
				) }

				{ tab === 'offers' && (
					<>
						{ websiteInfo?.approved === true && (
							<ul className="space-y-4">
								{ /* <RecommendedOrder /> */ }
								{ offers?.length > 0 ? (
									offers.map( ( offer ) => (
										<Offer
											key={ offer.id }
											{ ...offer }
											callback={ offersCallback }
										/>
									) )
								) : (
									<p className="text-lg animate-pulse">
										Waiting for new offers...
									</p>
								) }
							</ul>
						) }

						{ websiteInfo?.approved === false && (
							<p className="text-lg">
								Thank you for your application. Your website is
								pending approval.
								<br />
								In the meantime, feel free to check out{ ' ' }
								<a
									href="https://backlink.cafe/blog"
									target="_blank"
								>
									resources
								</a>
								,{ ' ' }
								<a
									href="https://backlink.cafe/faq"
									target="_blank"
								>
									FAQ
								</a>{ ' ' }
								and tweet at us{ ' ' }
								<a
									href="https://twitter.com/backlink_cafe"
									target="_blank"
								>
									@backlink_cafe
								</a>
								.
							</p>
						) }
					</>
				) }
			</section>
		</>
	);
}

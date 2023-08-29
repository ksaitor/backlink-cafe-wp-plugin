import React, { useState } from 'react';
import { isEmail } from '@wordpress/url';
import { Input } from './Input';
import { disconnectStripe, upsertWebsite } from '../../wp-api';

function validateForm( data ) {
	const errors = {};
	let isValid = true;

	if ( ! data.pricePerLink || isNaN( data.pricePerLink ) ) {
		isValid = false;
		errors.pricePerLink = 'Price per link is required.';
	}

	if ( data.pricePerLink < 10 ) {
		isValid = false;
		errors.pricePerLink = 'Price should be at least $10';
	}

	if ( data.pricePerGuestPost < 10 ) {
		isValid = false;
		errors.pricePerGuestPost = 'Price should be at least $10';
	}

	data.autoApproveLinks = !! data.autoApproveLinks;

	if ( ! data.email || ! isEmail( data.email ) ) {
		isValid = false;
		errors.email = 'Valid email is required.';
	}

	if ( data.paypalEmail && ! isEmail( data.paypalEmail ) ) {
		isValid = false;
		errors.paypalEmail = 'Paypal email is not a valid email';
	}

	if (
		data.bitcoinAddress &&
		! data.bitcoinAddress.match( /^(bc1|[13])[a-zA-HJ-NP-Z0-9]{25,39}$/i )
	) {
		isValid = false;
		errors.bitcoinAddress = 'Valid Bitcoin address is required.';
	}

	return {
		isValid,
		errors,
	};
}

export default function Preferences( {
	formState,
	setFormState,
	callback,
	websiteInfo,
} ) {
	const [ formErrors, setFormErrors ] = useState( {} );
	const [ formMessage, setFormMessage ] = useState( null );

	const onStripeDisconnect = async () => {
		try {
			const confirmed = confirm(
				"You won't be able to receive new bank payouts. Are you sure you want to disconnect your Stripe account?"
			);
			if ( ! confirmed ) return;
			setFormErrors( {} );
			setFormMessage( null );
			await disconnectStripe();
			setFormMessage( 'Successfully disconnected stripe account!' );
			await callback();
		} catch ( e ) {
			setFormErrors( {
				stripe: `Failed to disconnect stripe: ${ e?.message ?? e }`,
			} );
		}
	};

	const handleSubmit = async ( e ) => {
		e.preventDefault();
		setFormErrors( {} );
		setFormMessage( null );
		const { isValid, errors } = validateForm( formState );

		if ( ! isValid ) return setFormErrors( errors );

		try {
			const result = await upsertWebsite( {
				data: {
					price: Number( formState.pricePerLink ) * 100,
					postPrice: Number( formState.pricePerGuestPost ) * 100,
					domain: formState.domain,
					bitcoinAddress: formState.bitcoinAddress,
					email: formState.email,
					paypalEmail: formState.paypalEmail,
				},
			} );

			if ( 'error' in result ) {
				throw new Error( result.error );
			}

			setFormMessage( 'Successfully updated your profile.' );
			await callback();
		} catch ( e ) {
			setFormErrors( {
				// server: ( e?.message ?? e ?? '' ).substring( 0, 200 ),
				server: e?.message ?? e ?? '',
			} );
		}
	};

	const onChange = ( key, e ) => {
		const value =
			key === 'autoApproveLinks' ? e.target.checked : e.target.value;
		setFormState( ( c ) => ( {
			...c,
			[ key ]: value,
		} ) );
	};

	return (
		<>
			{ /* <h1 className="mb-4 text-3xl no-underline [font-family:Dela_Gothic_One]">
				Settings
			</h1> */ }

			<form
				onSubmit={ handleSubmit }
				className="grid grid-cols-3 gap-4 mb-10 w-[min(700px,100%)]"
			>
				<Input
					label="Link insertion price"
					placeholder="20"
					prefix="$"
					type="number"
					value={ formState.pricePerLink }
					onChange={ ( e ) => onChange( 'pricePerLink', e ) }
				/>
				<Input
					label="Guest post price"
					// placeholder="40"
					disabled
					placeholder="Coming soon…"
					prefix="$"
					type="number"
					value={ formState.pricePerGuestPost }
					onChange={ ( e ) => onChange( 'pricePerGuestPost', e ) }
				/>
				<Input
					label="Guest post link limit"
					disabled
					type="number"
					// placeholder="20"
					placeholder="Coming soon…"
					value={ formState.linksPerPostLimit }
					onChange={ ( e ) => onChange( 'linksPerPostLimit', e ) }
				/>

				{ /*
				<Input
					label="Excluded keywords"
					placeholder="casino"
					disabled
					value={ formState.excludedKeywords }
					onChange={ ( e ) => onChange( 'excludedKeywords', e ) }
				/>
				<Input
					label="Excluded domain regex"
					placeholder="casino"
					disabled
					value={ formState.excludedDomainRegex }
					onChange={ ( e ) => onChange( 'excludedDomainRegex', e ) }
				/>
				*/ }
				{ /* <div className="col-span-2">
					<Checkbox
						label="Auto-approve links"
						checked={ formState.autoApproveLinks }
						onChange={ ( e ) => onChange( 'autoApproveLinks', e ) }
					/>
				</div> */ }

				<Input
					label="Email"
					type="email"
					placeholder="me@mywebsite.com"
					value={ formState.email }
					onChange={ ( e ) => onChange( 'email', e ) }
				/>
				<Input
					label="Domain"
					placeholder="⚠ Domain not detected"
					type="url"
					value={ formState.domain }
					title="You can't change your domain"
					disabled
					onChange={ ( e ) => onChange( 'domain', e ) }
				/>
				<br />
				<Input
					label="Paypal Email"
					type="email"
					placeholder="me@mywebsite.com"
					value={ formState.paypalEmail }
					onChange={ ( e ) => onChange( 'paypalEmail', e ) }
				/>
				<Input
					label="Payout to Bitcoin Address"
					type="text"
					placeholder="bc1..."
					value={ formState.bitcoinAddress }
					onChange={ ( e ) => onChange( 'bitcoinAddress', e ) }
				/>

				<div className="flex flex-col gap-1 text-lg">
					<label className="font-bold">Bank payouts</label>
					{ websiteInfo?.stripeConnectId ? (
						<button
							type="button"
							className="px-6 py-1 text-lg font-bold text-black duration-200 border border-gray-500 rounded hover:text-black hover:bg-red-500 hover:opacity-100 opacity-60"
							onClick={ onStripeDisconnect }
						>
							Disconnect Stripe
						</button>
					) : (
						<a
							className="px-6 py-1 text-lg font-bold text-center duration-200 border border-[#8c8f94] rounded hvoer:border-black hover:text-black hover:bg-white hover:opacity-70"
							href={ `https://connect.stripe.com/oauth/authorize?${ new URLSearchParams(
								{
									response_type: 'code',
									client_id:
										wpBacklinkCafeBuild.stripe_client_id,
									scope: 'read_write',
									state: JSON.stringify( {
										type: 'website',
										access_token:
											wpBacklinkCafeBuild.access_token,
									} ),
								}
							) }` }
						>
							Connect to Stripe
						</a>
					) }
				</div>

				<div className="col-span-2">
					<button className="flex-none block w-1/2 px-6 py-2 text-lg font-bold text-white duration-200 bg-black rounded hover:opacity-70">
						Save
					</button>
				</div>
			</form>

			{ !! formMessage && (
				<div className="bg-black text-white rounded-md w-[min(500px,100%)] p-2 px-3 text-lg mb-4">
					{ formMessage }
				</div>
			) }

			{ Object.keys( formErrors ).length > 0 && (
				<div className="bg-[red] text-white rounded-md w-[min(500px,100%)] p-2 px-4 text-lg mb-4">
					Error: { formErrors[ Object.keys( formErrors )[ 0 ] ] }
				</div>
			) }
		</>
	);
}

/**
 * @typedef {'PENDING' | 'PLACED' | 'PAID'} OfferStatus
 * @typedef {Object} Website
 * @property {string} domain
 *
 * @typedef {Object} Order
 * @property {string} url
 * @property {string} anchor
 *
 * @typedef {Object} OfferProps
 * @property {number} id
 * @property {string?} anchor
 * @property {Website} website
 * @property {Order} order
 * @property {boolean} buyerApproved
 * @property {boolean} sellerApproved
 * @property {OfferStatus} status
 * @property {Function} callback
 *
 */

import cn from 'classnames';
import { useState } from 'react';
import { approveOffer, rejectOffer } from '../../wp-api';

/**
 * @param {OfferProps} offer
 */
export function Offer( offer ) {
	const { buyerApproved, id, order, sellerApproved, website, callback } =
		offer;

	const [ loading, setLoading ] = useState( false );
	const [ status, setStatus ] = useState( offer.status );
	const [ error, setError ] = useState( null );

	const accept = async () => {
		if ( loading ) return;
		setLoading( true );

		try {
			await approveOffer( {
				data: {
					offerId: id,
					websiteDomain: website.domain,
				},
			} );
			setStatus( 'PLACED' );
			await callback?.();
		} catch ( e ) {
			setError( e );
		} finally {
			setLoading( false );
		}
	};

	const reject = async () => {
		if ( loading ) return;
		setLoading( true );
		const rejectionReason = prompt( 'Why are you rejecting this offer?' );
		if ( rejectionReason === null || rejectionReason?.trim().length < 2 ) {
			setLoading( false );
			return alert( 'Please provide a reason for rejecting this offer.' );
		}

		try {
			await rejectOffer( {
				data: {
					offerId: id,
					websiteDomain: website.domain,
					rejectionReason,
				},
			} );
			setStatus( 'REJECTED' );
			await callback?.();
		} catch ( e ) {
			setError( e );
		} finally {
			setLoading( false );
		}
	};

	return (
		<li className="relative flex flex-col gap-2 p-2 px-4 pb-4 -ml-4 hover:bg-gray-200 rounded-xl group">
			<p className="text-lg">
				<span className="opacity-80">Link to</span>{ ' ' }
				<a
					href={ order.url }
					target="_blank"
					className="font-bold underline"
					rel="noopener"
					title="Requested backlink"
				>
					{ order.url }
				</a>
				<br />
				<span className="opacity-80">with anchor text</span> "
				<b title="Requested anchor text">{ offer.anchor }</b>"<br />
				{ order.priceBTC && (
					<>
						<span className="opacity-80">price in BTC</span>{ ' ' }
						<b title="Price in BTC">{ order.priceBTC }</b>
						<br />
					</>
				) }
				{ /* TODO we need to show the exact anchor that actually got a match. Not the whole anchor value (often it's comma separated) */ }
				<span className="opacity-80">will be placed on your page</span>{ ' ' }
				<a
					href={ offer.url }
					target="_blank"
					className="underline"
					title="Page URL on your site"
				>
					{ offer.url }
				</a>
			</p>
			{ status === 'PENDING' && (
				<div className="flex gap-2 text-lg">
					<button
						className={ cn(
							'p-1 px-4 text-white bg-black rounded hover:opacity-80',
							{
								'cursor-wait opacity-40 hover:opacity-40':
									loading,
							}
						) }
						onClick={ accept }
						disabled={ loading }
					>
						Accept for ${ offer.price / 100 }
					</button>
					<button
						className={ cn(
							'p-1 px-4 rounded opacity-80 hover:bg-gray-300 hover:opacity-100',
							{
								'cursor-wait opacity-40 hover:opacity-40':
									loading,
							}
						) }
						title="Skip this offer"
						onClick={ reject }
						disabled={ loading }
					>
						Reject
					</button>
				</div>
			) }
			{ status === 'PLACED' && (
				<div className="flex items-center gap-2">
					<div
						className="p-1 px-4 text-lg font-bold text-green-600 duration-200 bg-transparent border-2 border-green-600 rounded"
						disabled
						title="Link is now placed. Waiting for it to get indexed by Google."
					>
						Link placed. Indexing...
					</div>

					<p className="text-md text-black/40">
						Next: Funds Released (15 days)
					</p>
				</div>
			) }
			{ status === 'PAID' && (
				<div className="flex items-center gap-2">
					<div className="p-1 px-4 text-lg font-bold text-green-600 duration-200 bg-transparent border-2 border-green-600 rounded">
						Paid
					</div>
				</div>
			) }
			{ status === 'REJECTED' && (
				<div className="flex gap-2 text-lg">
					<span className="p-1 px-4 border border-gray-500 border-solid rounded opacity-80">
						You rejected this offer
					</span>
				</div>
			) }
			{ error && (
				<div className="p-1 font-red-500">
					{ error.message ??
						'Something went wrong. Please refresh the page.' }
				</div>
			) }
			<div className="absolute hidden bottom-1 right-2 opacity-60 group-hover:block">
				Offer #{ offer.id }
			</div>
		</li>
	);
}

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
 * @property {Website} website
 * @property {Order} order
 * @property {boolean} buyerApproved
 * @property {boolean} sellerApproved
 * @property {OfferStatus} status
 * @property {Function} callback
 *
 */

import { approveOffer, rejectOffer } from '../../wp-api';

/**
 * @param {OfferProps} offer
 */
export function Offer( offer ) {
	const {
		status,
		buyerApproved,
		id,
		order,
		sellerApproved,
		website,
		callback,
	} = offer;

	const accept = async () => {
		await approveOffer( {
			data: {
				offerId: id,
				websiteDomain: website.domain,
			},
		} );

		await callback?.();
	};

	const reject = async () => {
		await rejectOffer( {
			data: {
				offerId: id,
				websiteDomain: website.domain,
			},
		} );

		await callback?.();
	};

	return (
		<li>
			<div className="flex flex-col gap-2 p-2 px-4 pb-4 -ml-4 hover:bg-gray-200 rounded-xl">
				<div>
					<h2 title="Target URL" className="text-lg">
						<span className="opacity-50">Link to</span>{ ' ' }
						<b>{ order.url }</b>
					</h2>
					<p title="Anchor text" className="text-lg">
						<span className="opacity-50">with anchor</span>{ ' ' }
						<b>{ order.anchor }</b>
					</p>
					{ order.priceBTC && (
						<p title="Price in BTC" className="text-lg">
							<span className="opacity-50">price in BTC</span>{ ' ' }
							<b>{ order.priceBTC }</b>
						</p>
					) }

					{ /* TODO we need to show the exact anchor that actually got a match. Not the whole anchor value (often it's comma separated) */ }
					<p title="Page URL on your site" className="text-lg">
						<span className="opacity-50">to be placed on</span>{ ' ' }
						<b>{ offer.url }</b>
					</p>
					{ /* <p className="text-lg"><code>&lt;a href="{ order.url }"&gt;{ order.anchor }&lt;/a&gt; </code></p> */ }
				</div>

				{ status === 'PENDING' && (
					<div className="flex gap-2">
						<button
							className="px-6 py-2 text-lg font-bold text-white duration-200 bg-black rounded hover:shadow-lg hover:scale-105 "
							onClick={ accept }
						>
							Accept
						</button>
						<button
							className="px-6 py-2 text-lg font-bold text-red-400 duration-200 rounded hover:bg-red-400 hover:text-white"
							title="Skip this offer"
							onClick={ reject }
						>
							Skip
						</button>
					</div>
				) }

				{ status === 'PLACED' && (
					<div className="flex items-center gap-2">
						<div
							className="px-6 py-2 text-lg font-bold text-green-600 duration-200 bg-transparent border-2 border-green-600 rounded"
							disabled
							title="Link is now placed. Waiting for it to get indexed by Google."
						>
							Placed. Indexing...
						</div>

						<p className="text-md text-black/40">
							Next: Funds Released (15 days)
						</p>
					</div>
				) }

				{ status === 'PAID' && (
					<div className="flex items-center gap-2">
						<div className="px-6 py-2 text-lg font-bold text-green-600 duration-200 bg-transparent border-2 border-green-600 rounded">
							Paid
						</div>
					</div>
				) }
			</div>
		</li>
	);
}

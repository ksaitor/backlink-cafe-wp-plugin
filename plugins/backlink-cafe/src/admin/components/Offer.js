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
		const confirmed = confirm( 'Are you sure you want to reject this offer?' );
		if (!confirmed) return
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
			<div className="flex flex-col gap-2 p-2 px-4 pb-4 -ml-4 hover:bg-gray-200 rounded-xl text-[10px]">
				<div>
					<p title="Target URL" className="text-lg">
						<span className="opacity-80">Link to</span>{ ' ' }
						<a href={ order.url } target="_blank" className="font-bold underline" rel='noopener' >{ order.url }</a>
					</p>
					<p title="Anchor text" className="text-lg">
						<span className="opacity-80">with anchor text</span>{ ' ' }
						"<b>{ order.anchor }</b>"
					</p>
					{ order.priceBTC && (
						<p title="Price in BTC" className="text-lg">
							<span className="opacity-80">price in BTC</span>{ ' ' }
							<b>{ order.priceBTC }</b>
						</p>
					) }

					{ /* TODO we need to show the exact anchor that actually got a match. Not the whole anchor value (often it's comma separated) */ }
					<p title="Page URL on your site" className="text-lg">
						<span className="opacity-80">will be placed on your page</span>{ ' ' }
						<a href={ offer.url } target="_blank" className="underline" >{ offer.url }</a>
					</p>
					{ /* <p className=""><code>&lt;a href="{ order.url }"&gt;{ order.anchor }&lt;/a&gt; </code></p> */ }
				</div>

				{ status === 'PENDING' && (
					<div className="flex gap-2 text-lg">
						<button
							className="p-1 px-4 text-white bg-black rounded hover:opacity-80"
							onClick={ accept }
						>
							Accept for ${offer.price/100}
						</button>
						<button
							className="p-1 px-4 rounded opacity-80 hover:bg-gray-300 hover:opacity-100"
							title="Skip this offer"
							onClick={ reject }
						>
							Reject
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

				{ status === 'REJECTED' && (
					<div className="flex gap-2 text-lg">
						<span className="p-1 px-4 border border-gray-500 border-solid rounded opacity-80">
							You rejected this offer
						</span>
				</div>
				) }
			</div>
		</li>
	);
}

export function RecommendedOrder() {
	return (
		<li>
			<h2 className="text-xl font-bold">
				barber shop nyc, barber shop in nyc, nyc barber shop
			</h2>
			<h3 className="text-xl font-bold">https://mywebsite.com/barber</h3>
			<h3 className="text-xl text-green-600">Order Funded</h3>

			<div className="flex flex-col gap-4">
				<div className="flex flex-col gap-2">
					{ Array( 2 )
						.fill( null )
						.map( ( _, i ) => (
							<div className="flex items-center gap-2" key={ i }>
								<input type="radio" />
								<div>
									<p className="text-lg font-bold">
										Best Barber Shops in NYC
									</p>
									<p className="text-lg">
										https://yoursite.com/blog/best-barber-shops-nyc
									</p>
								</div>
							</div>
						) ) }
					<div className="flex gap-2">
						<button className="px-6 py-2 text-lg font-bold text-white duration-200 bg-black rounded-lg hover:opacity-70">
							Make an Offer
						</button>
						<button className="px-6 py-2 text-lg font-bold text-red-400 duration-200 bg-transparent border-2 border-red-400 rounded-lg hover:opacity-70">
							Skip
						</button>
					</div>
				</div>
			</div>
		</li>
	);
}

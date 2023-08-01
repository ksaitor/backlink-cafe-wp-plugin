import _apiFetch from '@wordpress/api-fetch';
export const BASE_API_PATH = `${ wpBacklinkCafeBuild.plugin_name }/v1`;

export const apiFetch = async ( path, config = {} ) =>
	_apiFetch( {
		path: BASE_API_PATH + path,
		headers: {
			'Content-Type': 'application/json',
		},
		...config,
	} );

export const getOffers = async ( config = {} ) =>
	apiFetch( '/get-offers', config );

export const getMe = async ( config = {} ) => apiFetch( '/get-me', config );

export const approveOffer = async ( config = {} ) =>
	apiFetch( '/approve-offer', {
		...config,
		method: 'POST',
	} );

export const rejectOffer = async ( config = {} ) =>
	apiFetch( '/reject-offer', {
		...config,
		method: 'POST',
	} );

export const disconnectStripe = async ( config = {} ) =>
	apiFetch( '/disconnect-stripe', {
		...config,
		method: 'POST',
	} );

export const upsertWebsite = async ( config = {} ) =>
	apiFetch( '/upsert-website', {
		...config,
		method: 'POST',
	} );

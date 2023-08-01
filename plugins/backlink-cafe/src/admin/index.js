import { render } from '@wordpress/element';
import { Settings } from './components/Settings';

( function ( $ ) {
	'use strict';
	$( window ).load( function () {
		if (
			'undefined' !==
				typeof document.getElementById( wpBacklinkCafeBuild.root_id ) &&
			null !== document.getElementById( wpBacklinkCafeBuild.root_id )
		) {
			render(
				<Settings />,
				document.getElementById( wpBacklinkCafeBuild.root_id )
			);
		}
	} );
} )( jQuery );

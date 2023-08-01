import React from 'react';

export function Checkbox( { label, ...props } ) {
	return (
		<div className="space-x-1 text-lg">
			<input type="checkbox" { ...props } />
			<label className="font-bold">{ label }</label>
		</div>
	);
}

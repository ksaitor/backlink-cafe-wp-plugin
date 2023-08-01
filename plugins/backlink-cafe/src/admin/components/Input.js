import React from 'react';
import cn from 'classnames';

export function Input( { label, ...props } ) {
	return (
		<div
			className={ cn( 'flex flex-col gap-1 text-lg', {
				'!cursor-not-allowed opacity-70': props.disabled,
			} ) }
		>
			<label className="font-bold">{ label }</label>
			<input className="p-2" { ...props } />
		</div>
	);
}

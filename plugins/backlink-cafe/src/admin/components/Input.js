import React from 'react';
import cn from 'classnames';

export function Input( { label, ...props } ) {
	return (
		<div
			className={ cn( 'flex flex-col gap-1 text-lg relative', {
				'!cursor-not-allowed opacity-70': props.disabled,
			} ) }
		>
			<label className="font-bold">{ label }</label>
			{ props.prefix ? (
				<span className="absolute bottom-[5.1px] left-2">
					{ props.prefix }
				</span>
			) : null }
			<input
				className={ cn( 'p-2', { '!pl-5': props.prefix } ) }
				{ ...props }
			/>
		</div>
	);
}

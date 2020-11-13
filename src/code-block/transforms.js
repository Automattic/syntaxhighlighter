import { createBlock } from '@wordpress/blocks';

export default {
	from: [
		{
			type: 'enter',
			regExp: /^```$/,
			transform: () => createBlock( 'syntaxhighlighter/code' ),
		},
		{
			type: 'raw',
			isMatch: ( node ) => (
				node.nodeName === 'PRE' &&
				node.children.length === 1 &&
				node.firstChild.nodeName === 'CODE'
			),
			schema: {
				pre: {
					children: {
						code: {
							children: {
								'#text': {},
							},
						},
					},
				},
			},
		},
		{
			type: 'block',
			blocks: [ 'core/code' ],
			transform: ( { content } ) => {
				return createBlock( 'syntaxhighlighter/code', { content } );
			},
		},
	],
	to: [
		{
			type: 'block',
			blocks: [ 'core/code' ],
			transform: ( { content } ) => {
				return createBlock( 'core/code', { content } );
			},
		},
	],
};
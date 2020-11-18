import { createBlock } from '@wordpress/blocks';

export default {
	from: [
		{
			type: 'enter',
			regExp: /^```\w*$/,
			transform: ( { content = '' } ) => {
				const [ , language ] = content.match( /^```(\w+)/ ) || [ null, null ];

				const attributes = language ? { language } : undefined;
				return createBlock( 'syntaxhighlighter/code', attributes );
			},
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

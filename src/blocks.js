/**
 * BLOCK: SyntaxHighlighter Evolved (syntaxhighlighter/code)
 */

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerBlockType, createBlock } from '@wordpress/blocks';
import { Path, PanelBody, PanelRow } from '@wordpress/components';
import { PlainText, InspectorControls } from '@wordpress/editor';

registerBlockType( 'syntaxhighlighter/code', {
	title: __('SyntaxHighlighter Code', 'syntaxhighlighter'),

	description: __('Adds syntax highlighting to source code (front end only).', 'syntaxhighlighter'),

	icon: 'editor-code',

	category: 'formatting',

	keywords: [
		// translators: Keyword that user might search for when trying to locate block.
		__('Source', 'syntaxhighlighter'),
		// translators: Keyword that user might search for when trying to locate block.
		__('Program', 'syntaxhighlighter'),
		// translators: Keyword that user might search for when trying to locate block.
		__('Develop', 'syntaxhighlighter'),
	],

	attributes: {
		content: {
			type: 'string',
				source: 'text',
				selector: 'pre',
		},

		language: {
			type: 'string',
			default: 'plain',
		}
	},

	supports: {
		html: false,
	},

	transforms: {
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
		],
	},

	edit( { attributes, setAttributes, className } ) {
		const options = [];
		for ( let brush in syntaxHighlighterData.brushes ) {
			options.push (
				<option key={ brush } value={ brush }>
					{ syntaxHighlighterData.brushes[ brush ] }
				</option>
			);
		}

		const blockSettings = (
			<InspectorControls key="syntaxHighlighterInspectorControls">
				<PanelBody title="Settingsaaaa">
					<PanelRow>
						<label htmlFor="syntaxhighlighter-language">
							{ __( 'Code Language', 'syntaxhighlighter' ) }
						</label>

						<select
							id       = 'syntaxhighlighter-language'
							value    = { attributes.language }
							onChange = { ( language ) => { setAttributes( { language: language.target.value } ) } }
						>
							{ options }
						</select>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
	);

	const editView = (
			<div className={ className + ' wp-block-code' }>
				<PlainText
					value={ attributes.content }
					onChange={ ( content ) => setAttributes( { content } ) }
					placeholder={ __( 'Tip: To the right, choose a code language from the block settings.', 'syntaxhighlighter' ) }
					aria-label={ __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ) }
				/>
			</div>
		);

		return [ blockSettings, editView ];
	},

	save( { attributes } ) {
		return <pre>{ attributes.content }</pre>;
	},
} );

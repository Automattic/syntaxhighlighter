/**
 * BLOCK: SyntaxHighlighter Evolved (syntaxhighlighter/code)
 */

import { registerBlockType } from '@wordpress/blocks';
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import deprecated from './deprecated';
import edit from './edit';
import save from './save';
import transforms from './transforms';

registerBlockType( 'syntaxhighlighter/code', {
	title: __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ),

	description: __( 'Adds syntax highlighting to source code (front end only).', 'syntaxhighlighter' ),

	icon: 'editor-code',

	category: 'formatting',

	keywords: [
		// translators: Keyword that user might search for when trying to locate block.
		__( 'Source', 'syntaxhighlighter' ),
		// translators: Keyword that user might search for when trying to locate block.
		__( 'Program', 'syntaxhighlighter' ),
		// translators: Keyword that user might search for when trying to locate block.
		__( 'Develop', 'syntaxhighlighter' ),
	],

	attributes: {
		content: {
			type: 'string',
			source: 'text',
			selector: 'code',
		},

		language: {
			type: 'string',
			default: syntaxHighlighterData.settings.language.default,
		},

		lineNumbers: {
			type: 'boolean',
			default: syntaxHighlighterData.settings.lineNumbers.default,
		},

		firstLineNumber: {
			type: 'string',
			default: syntaxHighlighterData.settings.firstLineNumber.default,
		},

		highlightLines: {
			type: 'string',
		},

		wrapLines: {
			type: 'boolean',
			default: syntaxHighlighterData.settings.wrapLines.default,
		},

		makeURLsClickable: {
			type: 'boolean',
			default: syntaxHighlighterData.settings.makeURLsClickable.default,
		},

		quickCode: {
			type: 'boolean',
			default: syntaxHighlighterData.settings.quickCode.default,
		},
	},

	supports: {
		html: false,
	},

	transforms,
	edit,
	save,
	deprecated,
} );

import { __ } from '@wordpress/i18n';
import edit from './edit';
import save from './save';
import transforms from './transforms';

const { settings } = window.syntaxHighlighterData;

export default {
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
			selector: 'pre',
		},
		language: {
			type: 'string',
			default: settings.language.default,
		},
		lineNumbers: {
			type: 'boolean',
			default: settings.lineNumbers.default,
		},
		firstLineNumber: {
			type: 'string',
			default: settings.firstLineNumber.default,
		},
		highlightLines: {
			type: 'string',
		},
		wrapLines: {
			type: 'boolean',
			default: settings.wrapLines.default,
		},
		makeURLsClickable: {
			type: 'boolean',
			default: settings.makeURLsClickable.default,
		},
		quickCode: {
			type: 'boolean',
			default: settings.quickCode.default,
		},
	},
	supports: {
		html: false,
		align: true,
	},
	transforms,
	edit,
	save,
};

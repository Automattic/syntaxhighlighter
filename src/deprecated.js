export default [
	{
		attributes: {
			content: {
				type: 'string',
					source: 'text',
					selector: 'pre',
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

		save( { attributes } ) {
			return( <pre>{ attributes.content }</pre> );
		},
	},
];

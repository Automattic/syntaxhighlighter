const { __, setLocaleData }            = wp.i18n;
const { registerBlockType }            = wp.blocks;
const { InspectorControls, PlainText } = wp.editor;
const { PanelBody, PanelRow }          = wp.components;

setLocaleData( syntaxHighlighterData.localeData, 'syntaxhighlighter' );

/*
 * syntaxhighlighter/code block
 *
 * This only applies highlighting to the front end. See `edit()` for details.
 *
 * The reason why we're creating a new block here, instead of extending or replacing the existing `code` block
 * built in to Core, is because it's more future-proof for the user's content. If we were to extend the existing
 * `core` block, and then the user disabled this plugin, and then saved the post, the `language` attribute (and
 * any others) would be lost. If the user re-enabled the plugin, they would have to re-apply the settings to each
 * block.
 *
 * @todo
 *      * Add tests
 *      * Add support for other shortcode parameters
 *      * Add a shortcode transform
 */
 export const syntaxHighlighterCode = {
	title: __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ),
	description: __( 'Adds syntax highlighting to source code (front end only)', 'syntaxhighlighter' ),
	icon: 'editor-code',
	category: 'formatting',

	keywords: [
		// translators: Keyword that user might search for when trying to locate block.
		__( 'Source',  'syntaxhighlighter' ),
		// translators: Keyword that user might search for when trying to locate block.
		__( 'Program', 'syntaxhighlighter' ),
		// translators: Keyword that user might search for when trying to locate block.
		__( 'Develop', 'syntaxhighlighter' ),
	],

	/*
	 * The `[sourcecode]` shortcode has many additional parameters, but the initial version of this block only
	 * supports the most basic ones. The remaining ones can be added in a future iteration.
	 */
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
		html: false
	},

	transforms: {
		from: [
			{
				type: 'pattern',
				trigger: 'enter',
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

	/**
	 * Render the dynamic version of the block for the editor.
	 *
	 * Ideally, this version should have syntax highlighting too, because that would be consistent with Gutenberg's
	 * design principal of making the front and back ends as close as possible. Unfortunately, that's not practical
	 * with SyntaxHighlighter, because it is only intended to _display_ code, not provide an editable interface.
	 *
	 * @link https://github.com/syntaxhighlighter/syntaxhighlighter/issues/337
	 *
	 * @param {object} props
	 * @returns {array}
	 */
	edit: props => {
		const {
			className, setAttributes,
			attributes: { content, language }
		} = props;

		const options = [];

		for ( let brush in syntaxHighlighterData.brushes ) {
			options.push (
				<option key={ brush } value={ brush }>
					{ syntaxHighlighterData.brushes[ brush ] }
				</option>
			);
		}

		const inspectorControls = (
			<InspectorControls key="syntaxHighlighterInspectorControls">
				<PanelBody title="Settings">
					<PanelRow>
						<label htmlFor="syntaxhighlighter-language">
							{ __( 'Language', 'syntaxhighlighter' ) }
						</label>

						<select
							id       = "syntaxhighlighter-language"
							value    = { language }
							onChange = { ( language ) => { setAttributes( { language: language.target.value } ) } }
						>
							{ options }
						</select>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
		);

		const editView = (
			// Add CSS class to inherit styles from the bundled `code` block.
			<div className={ className + ' wp-block-code' } key="syntaxHighlighterEditView">
				<PlainText
					// translators: Sample code used as a placeholder.
					placeholder = { __( "$foo = new Foo( 'bar' );", 'syntaxhighlighter' ) }
					className   = "editor-plain-text"
					value       = { content }
					aria-label  = { __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ) }
					onChange    = { ( content ) => setAttributes( { content } ) }
				/>
			</div>
		);

		return [ inspectorControls, editView ];
	},

	/**
	 * Render the saved version of the block for the front end.
	 *
	 * @param {object} props
	 * @returns {Element}
	 */
	save: props => {
		const { content, language } = props.attributes;

		return (
			<pre className={ 'brush: ' + language + '; notranslate' }>{ content }</pre>
		);
	}
};

registerBlockType( 'syntaxhighlighter/code', syntaxHighlighterCode );

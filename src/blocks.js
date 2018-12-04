/**
 * BLOCK: SyntaxHighlighter Evolved (syntaxhighlighter/code)
 */

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InspectorControls, PlainText } = wp.editor;
const { PanelBody, PanelRow } = wp.components;

/**
 * Register syntaxhighlighter/code block.
 *
 * This only applies highlighting to the front end. See `edit()` for details.
 *
 * The reason why we're creating a new block here, instead of extending or replacing the existing `code` block
 * built in to Core, is because it's more future-proof for the user's content. If we were to extend the existing
 * `core` block, and then the user disabled this plugin, and then saved the post, the `language` attribute (and
 * any others) would be lost. If the user re-enabled the plugin, they would have to re-apply the settings to each
 * block.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
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
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
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
							{ __( 'Code Language', 'syntaxhighlighter' ) }
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
					placeholder = { __( 'Tip: Choose a code language from the block settings to the right.', 'syntaxhighlighter' ) }
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
} );

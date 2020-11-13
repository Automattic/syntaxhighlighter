import {
	PanelBody,
	PanelRow,
	SelectControl,
	ToggleControl,
	TextControl,
} from '@wordpress/components';
import { PlainText, InspectorControls } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';

export default function editSyntaxHighlighterBlock( { attributes, setAttributes, className } ) {
	const {
		      content,
		      language,
		      lineNumbers,
		      firstLineNumber,
		      highlightLines,
		      wrapLines,
		      makeURLsClickable,
		      quickCode,
	      } = attributes;

	let blockSettingRows = [];

	// Language
	if ( syntaxHighlighterData.settings.language.supported ) {
		let options = [];
		for ( let brush in syntaxHighlighterData.brushes ) {
			options.push( {
				label: syntaxHighlighterData.brushes[ brush ],
				value: brush,
			} );
		}

		blockSettingRows.push(
			wp.element.createElement(
				PanelRow,
				null,
				wp.element.createElement(
					SelectControl,
					{
						label: __( 'Code Language', 'syntaxhighlighter' ),
						value: language,
						options: options,
						onChange: ( language ) => setAttributes( { language } ),
					},
				),
			),
		);
	}

	// Line numbers
	if ( syntaxHighlighterData.settings.lineNumbers.supported ) {
		blockSettingRows.push(
			wp.element.createElement(
				PanelRow,
				null,
				wp.element.createElement(
					ToggleControl,
					{
						label: __( 'Show Line Numbers', 'syntaxhighlighter' ),
						checked: lineNumbers,
						onChange: ( lineNumbers ) => setAttributes( { lineNumbers } ),
					},
				),
			),
		);
	}

	// First line number
	if ( lineNumbers && syntaxHighlighterData.settings.firstLineNumber.supported ) {
		blockSettingRows.push(
			wp.element.createElement(
				PanelRow,
				null,
				wp.element.createElement(
					TextControl,
					{
						label: __( 'First Line Number', 'syntaxhighlighter' ),
						type: 'number',
						value: firstLineNumber,
						onChange: ( firstLineNumber ) => setAttributes( { firstLineNumber } ),
						min: 1,
						max: 100000,
					},
				),
			),
		);
	}

	// Highlight line(s)
	if ( syntaxHighlighterData.settings.highlightLines.supported ) {
		blockSettingRows.push(
			wp.element.createElement(
				TextControl,
				{
					label: __( 'Highlight Lines', 'syntaxhighlighter' ),
					value: highlightLines,
					help: __( 'A comma-separated list of line numbers to highlight. Can also be a range. Example: 1,5,10-20', 'syntaxhighlighter' ),
					onChange: ( highlightLines ) => setAttributes( { highlightLines } ),
				},
			),
		);
	}

	// Wrap long lines
	if ( syntaxHighlighterData.settings.wrapLines.supported ) {
		blockSettingRows.push(
			wp.element.createElement(
				PanelRow,
				null,
				wp.element.createElement(
					ToggleControl,
					{
						label: __( 'Wrap Long Lines', 'syntaxhighlighter' ),
						checked: wrapLines,
						onChange: ( wrapLines ) => setAttributes( { wrapLines } ),
					},
				),
			),
		);
	}

	// Make URLs clickable
	if ( syntaxHighlighterData.settings.makeURLsClickable.supported ) {
		blockSettingRows.push(
			wp.element.createElement(
				PanelRow,
				null,
				wp.element.createElement(
					ToggleControl,
					{
						label: __( 'Make URLs Clickable', 'syntaxhighlighter' ),
						checked: makeURLsClickable,
						onChange: ( makeURLsClickable ) => setAttributes( { makeURLsClickable } ),
					},
				),
			),
		);
	}

	// Quick code
	if ( syntaxHighlighterData.settings.quickCode.supported ) {
		blockSettingRows.push(
			wp.element.createElement(
				PanelRow,
				null,
				wp.element.createElement(
					ToggleControl,
					{
						label: __( 'Enable edit mode on double click', 'syntaxhighlighter' ),
						checked: quickCode,
						onChange: ( quickCode ) => setAttributes( { quickCode } ),
					},
				),
			),
		);
	}

	const blockSettings = (
		<InspectorControls key="syntaxHighlighterInspectorControls">
			<PanelBody title={ __( 'Settings', 'syntaxhighlighter' ) }>
				{ blockSettingRows }
			</PanelBody>
		</InspectorControls>
	)

	const editView = (
		<div className={ className + ' wp-block-code' }>
			<PlainText
				value={ content }
				onChange={ ( content ) => setAttributes( { content } ) }
				placeholder={ __( 'Tip: you can choose a code language from the block settings.', 'syntaxhighlighter' ) }
				aria-label={ __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ) }
			/>
		</div>
	)

	return [ blockSettings, editView ];
}
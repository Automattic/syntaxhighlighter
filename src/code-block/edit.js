
import { PlainText } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import settings from './settings';

export default function editSyntaxHighlighterBlock( { attributes, setAttributes, className } ) {
	const {
		content,
	} = attributes;

	const { settings: { tabSize } } = window.syntaxHighlighterData;

	const editView = <div className={ className + ' wp-block-code' }>
		<PlainText
			className="wp-block-syntaxhighlighter__textarea"
			style={ { tabSize, '-moz-tab-size': '' + tabSize } }
			value={ content }
			onChange={ ( nextContent ) => setAttributes( { content: nextContent } ) }
			placeholder={ __( 'Tip: you can choose a code language from the block settings.', 'syntaxhighlighter' ) }
			aria-label={ __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ) }
		/>
	</div>;

	return (
		[
			settings( { attributes, setAttributes } ),
			editView,
		]
	);
}


import { PlainText } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import { HighlightLines } from './highlight';
import settings from './settings';
import { Fragment } from '@wordpress/element';

export default function editSyntaxHighlighterBlock( props ) {
	const { attributes: { content }, setAttributes, className } = props;

	const { settings: { tabSize } } = window.syntaxHighlighterData;

	const editView = <div className={ className + ' wp-block-code' }>
		<HighlightLines { ... props } />
		<PlainText
			className="wp-block-syntaxhighlighter__textarea"
			style={ { tabSize, '-moz-tab-size': '' + tabSize } }
			value={ content }
			onChange={ ( nextContent ) => setAttributes( { content: nextContent } ) }
			placeholder={ __( 'Tip: you can choose a code language from the block settings.', 'syntaxhighlighter' ) }
			aria-label={ __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ) }
		/>
	</div>;

	return <Fragment>
		{ settings( props ) }
		{ editView }
	</Fragment>
	;
}

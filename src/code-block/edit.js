
import { PlainText } from '@wordpress/editor';
import { __ } from '@wordpress/i18n';
import settings from './settings';
import { Fragment } from '@wordpress/element';

export default function editSyntaxHighlighterBlock( { attributes, setAttributes, className } ) {
	const {
		content,
	} = attributes;

	const editView = <div className={ className + ' wp-block-code' }>
		<PlainText
			value={ content }
			onChange={ ( nextContent ) => setAttributes( { content: nextContent } ) }
			placeholder={ __( 'Tip: you can choose a code language from the block settings.', 'syntaxhighlighter' ) }
			aria-label={ __( 'SyntaxHighlighter Code', 'syntaxhighlighter' ) }
		/>
	</div>;

	return <Fragment>
		{ settings( { attributes, setAttributes } ) }
		{ editView }
	</Fragment>
	;
}

import {
	ToolbarGroup,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default ( { attributes, setAttributes, options } ) => {
	const { language } = attributes;

	const languageControl = ( { label, value } ) => ( {
		title: label,
		onClick: () => setAttributes( { language: value } ),
		isActive: value === language,
	} );

	const selectedLanguage = options.find( o => o.value === language );

	return <ToolbarGroup
		isCollapsed={ true }
		noIcons={ true }
		label={ __( 'Code Language', 'syntaxhighlighter' ) }
		icon={ null }
		menuProps={ { className: 'wp-block-syntaxhighlighter__language_toolbar' } }
		toggleProps={ { children: <b> { selectedLanguage.label } </b> } }
		controls={ options.map( languageControl ) }
	>
	</ToolbarGroup>;
};

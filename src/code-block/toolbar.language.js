import { __ } from '@wordpress/i18n';

import ToolbarDropdown from '../toolbar-dropdown';

export default ( { attributes, setAttributes, options } ) => {
	const { language } = attributes;

	return (
		<ToolbarDropdown
			key="code-language"
			options={ options }
			optionsLabel={ __( 'Code Language', 'syntaxhighlighter' ) }
			value={ language }
			onChange={ ( value ) => setAttributes( { language: value } ) }
		/>
	);
};

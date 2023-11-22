import {
	Button,
	Dropdown,
	MenuGroup,
	MenuItem,
	NavigableMenu,
} from '@wordpress/components';

/**
 * @typedef {Object} DropdownOption
 *
 * @property {string} label Option label.
 * @property {string} value Option value.
 */
/**
 * Dropdown for the editor toolbar.
 *
 * @param {Object}           props                Component props.
 * @param {DropdownOption[]} props.options        Dropdown options.
 * @param {string}           [props.optionsLabel] Options label.
 * @param {Object}           [props.icon]         Icon for the toolbar.
 * @param {string}           props.value          Current dropdown value.
 * @param {Function}         props.onChange       Dropdown change callback, which receive the new value as argument.
 *
 * @return {Object} React component.
 */
const ToolbarDropdown = ( {
	options,
	optionsLabel,
	icon,
	value,
	onChange,
	...props
} ) => {
	const selectedOption = options.find( ( option ) => value === option.value );

	return (
		<Dropdown
			className="syntaxhighlighter-toolbar-dropdown"
			popoverProps={ {
				isAlternate: true,
				position: 'bottom right left',
				focusOnMount: true,
				className: 'syntaxhighlighter-toolbar-dropdown__popover',
			} }
			renderToggle={ ( { isOpen, onToggle } ) => (
				<Button
					onClick={ onToggle }
					icon={ icon }
					aria-expanded={ isOpen }
					aria-haspopup="true"
					children={
						selectedOption ? selectedOption.label : ''
					}
				/>
			) }
			renderContent={ ( { onClose } ) => (
				<NavigableMenu role="menu" stopNavigationEvents>
					<MenuGroup label={ optionsLabel }>
						{ options.map( ( option ) => {
							const isSelected =
								option.value === selectedOption.value;
							let itemClassNames = 'syntaxhighlighter-toolbar-dropdown__option';
							if ( isSelected ) {
								itemClassNames += ' is-selected';
							}
							return (
								<MenuItem
									key={ option.value }
									role="menuitemradio"
									isSelected={ isSelected }
									className={ itemClassNames }
									onClick={ () => {
										onChange( option.value );
										onClose();
									} }
									children={ option.label }
								/>
							);
						} ) }
					</MenuGroup>
				</NavigableMenu>
			) }
			{ ...props }
		/>
	);
};

export default ToolbarDropdown;

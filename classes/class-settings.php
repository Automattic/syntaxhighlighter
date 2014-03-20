<?php

class SyntaxHighlighter_Settings {
	public $version      = '4.0.0-alpha';
	public $setting_name = 'syntaxhighlighter_settings';

	public function __get( $name ) {
		$settings = $this->get_settings_with_defaults();

		// Deprecated settings
		switch ( $name ) {
			case 'shversion':
				return ( isset( $settings['renderer'] ) && 'sh2' == $settings['renderer'] ) ? 2 : 3;
		}

		if ( array_key_exists( $name, $settings ) ) {
			return $settings[ $name ];
		} else {
			return null;
		}
	}

	public function __set( $name, $value ) {
		$settings = $this->get_settings_without_defaults();

		$settings[ $name ] = $value;

		update_option( $this->setting_name, $settings );
	}

	public function __isset( $name ) {
		$var = $this->__get( $name );

		return isset( $var );
	}

	public function get_settings_without_defaults() {
		return (array) get_option( $this->setting_name, array() );
	}

	public function get_settings_with_defaults( $defaults = array() ) {
		return wp_parse_args( $this->get_settings_without_defaults(), $this->get_defaults( $defaults ) );
	}

	public function get_defaults( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array(
				'theme'          => 'default',
				'loadallbrushes' => 0,
				'renderer'       => 'sh3',
				'title'          => '',
				'autolinks'      => 1,
				'classname'      => '',
				'collapse'       => 0,
				'firstline'      => 1,
				'gutter'         => 1,
				'htmlscript'     => 0,
				'light'          => 0,
				'padlinenumbers' => 'false',
				'smarttabs'      => 1,
				'tabsize'        => 4,
				'toolbar'        => 0,
				'wraplines'      => 1, // 2.x only
			) );

		return (array) apply_filters( 'syntaxhighlighter_defaultsettings', $defaults );
	}
}
<?php

class SyntaxHighlighter_Settings {
	public $setting_name = 'syntaxhighlighter_settings';
	public $version = '4';

	public $settings = array();

	public function __construct() {
		$this->maybe_upgrade();

		$this->settings = $this->get_settings_with_defaults();
	}

	public function __get( $name ) {
		if ( array_key_exists( $name, $this->settings ) ) {
			return $this->settings[ $name ];
		} else {
			return null;
		}
	}

	public function __set( $name, $value ) {
		$this->settings[ $name ] = $value;

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
			'wraplines'      => 1, // SH 2.x only
		) );

		return (array) apply_filters( 'syntaxhighlighter_defaultsettings', $defaults );
	}

	public function reset_all() {
		update_option( $this->setting_name, array() );
		$this->settings = $this->get_settings_with_defaults();
	}

	public function maybe_upgrade() {
		$settings = $this->get_settings_without_defaults();

		if ( empty( $settings['version'] ) ) {
			$settings['version'] = 0;
		}

		$modified = false;

		if ( $settings['version'] < 4 ) {
			if ( ! empty( $settings['shversion'] ) ) {
				switch ( $settings['shversion'] ) {
					case 2:
						$settings['renderer'] = 'sh2';
						break;

					case 3:
						$settings['renderer'] = 'sh3';
						break;

				}

				unset( $settings['shversion'] );

				$modified = true;
			}
		}

		if ( $modified ) {
			update_option( $this->setting_name, $settings );
		}

		return $modified;
	}
}
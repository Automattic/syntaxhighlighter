<?php

class SyntaxHighlighter_Settings {
	public $core;

	public $setting_name = 'syntaxhighlighter_settings';
	public $version = '4';

	public $settings = array();

	public function __construct( $core, $overrides = array() ) {
		$this->core = $core;

		$this->settings = $this->get_settings();

		$this->maybe_upgrade();

		if ( ! empty( $overrides ) ) {
			$this->settings = array_merge( $this->settings, $overrides );
		}
	}

	public function __get( $name ) {
		if ( array_key_exists( $name, $this->settings ) ) {
			return $this->settings[ $name ];
		} else {
			$defaults = $this->get_defaults();

			if ( array_key_exists( $name, $defaults ) ) {
				return $defaults[ $name ];
			}
		}

		return null;
	}

	public function __set( $name, $value ) {
		if ( is_null( $value ) ) {
			unset( $this->settings[ $name ] );
		} else {
			$this->settings[ $name ] = $value;
		}
	}

	public function __isset( $name ) {
		$var = $this->__get( $name );

		return isset( $var );
	}

	public function get_settings() {
		return (array) get_option( $this->setting_name, array() );
	}

	public function save() {
		update_option( $this->setting_name, $this->settings );
	}

	public function get_defaults( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array(
			'renderer'       => 'sh3',
			'theme'          => 'default',
			'title'          => '',
			'autolinks'      => 1,
			'classname'      => '',
			'collapse'       => 0,
			'firstline'      => 1,
			'gutter'         => 1,
			'light'          => 0,
			'padlinenumbers' => 'false',
			'smarttabs'      => 1,
			'tabsize'        => 4,
			'toolbar'        => 0,

			// SH 2.x only
			// @TODO: Move these to the renderer class?
			'loadallbrushes' => 0,
			'wraplines'      => 1,
		) );

		return (array) apply_filters( 'syntaxhighlighter_defaultsettings', $defaults, $this->core );
	}

	public function reset_to_default( $name ) {
		unset( $this->settings[ $name ] );
	}

	public function reset_all() {
		$this->settings = array();
		$this->save();
	}

	public function maybe_upgrade() {
		if ( empty( $this->settings['version'] ) ) {
			$this->settings['version'] = 0;
		}

		$modified = false;

		if ( $this->settings['version'] < 4 ) {
			// Retire "shversion" in favor of "renderer"
			if ( ! empty( $this->settings['shversion'] ) ) {
				switch ( $this->settings['shversion'] ) {
					case 2:
						$this->settings['renderer'] = 'sh2';
						break;

					case 3:
						$this->settings['renderer'] = 'sh3';
						break;
				}

				unset( $this->settings['shversion'] );

				$modified = true;
			}
		}

		if ( $modified ) {
			update_option( $this->setting_name, $this->settings );
		}

		return $modified;
	}

	public function validate_settings( $raw_settings ) {
		$settings = array();

		// It's fine if this is invalid -- it'll be defaulted on runtime
		if ( ! empty( $raw_settings['renderer'] ) ) {
			$settings['renderer'] = $raw_settings['renderer'];
		}

		if ( ! empty( $raw_settings['theme'] ) && array_key_exists( $raw_settings['theme'], $this->core->renderer->themes ) ) {
			$settings['theme'] = $raw_settings['theme'];
		}

		if ( ! empty( $raw_settings['title'] ) ) {
			$settings['title'] = strip_tags( $raw_settings['title'] );
		}

		if ( ! empty( $raw_settings['classname'] ) ) {
			$settings['classname'] = preg_replace( '/[^ A-Za-z0-9_-]*/', '', $raw_settings['classname'] );
		}

		if ( ! empty( $raw_settings['firstline'] ) && $raw_settings['firstline'] > 0 ) {
			$settings['firstline'] = (int) $raw_settings['firstline'];
		}

		if ( ! empty( $raw_settings['padlinenumbers'] ) ) {
			// None/off
			if ( 'false' == $raw_settings['padlinenumbers'] ) {
				$settings['padlinenumbers'] = 'false';
			} // Automatic
			elseif ( 'true' == $raw_settings['padlinenumbers'] ) {
				$settings['padlinenumbers'] = 'true';
			} // How much to pad
			elseif ( $raw_settings['padlinenumbers'] > 0 ) {
				$settings['padlinenumbers'] = (int) $raw_settings['padlinenumbers'];
			}
		}

		if ( ! empty( $raw_settings['tabsize'] ) && $raw_settings['tabsize'] > 0 ) {
			$settings['tabsize'] = (int) $raw_settings['tabsize'];
		}

		// Checkboxes
		$settings['autolinks'] = ( ! empty( $raw_settings['autolinks'] ) ) ? 1 : 0;
		$settings['collapse']  = ( ! empty( $raw_settings['collapse'] ) ) ? 1 : 0;
		$settings['gutter']    = ( ! empty( $raw_settings['gutter'] ) ) ? 1 : 0;
		$settings['gutter']    = ( ! empty( $raw_settings['gutter'] ) ) ? 1 : 0;
		$settings['light']     = ( ! empty( $raw_settings['light'] ) ) ? 1 : 0;
		$settings['smarttabs'] = ( ! empty( $raw_settings['smarttabs'] ) ) ? 1 : 0;
		$settings['toolbar']   = ( ! empty( $raw_settings['toolbar'] ) ) ? 1 : 0; // May be overridden below

		// SH 2.x only
		// @TODO: Move these to the renderer class?
		$settings['loadallbrushes'] = ( ! empty( $raw_settings['loadallbrushes'] ) ) ? 1 : 0;
		$settings['wraplines']      = ( ! empty( $raw_settings['wraplines'] ) ) ? 1 : 0; // 2.x only for now

		// If the renderer changed, reset the toolbar preference
		if ( ! empty( $settings['renderer'] ) && $settings['renderer'] != $this->__get( 'renderer' ) ) {
			switch ( $settings['renderer'] ) {
				case 'sh2':
					$settings['toolbar'] = 1;
					break;

				case 'sh3':
					$settings['toolbar'] = 0;
					break;
			}
		}

		return $settings;
	}
}
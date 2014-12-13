<?php

class SyntaxHighlighter_Admin {
	public $core;

	public function __construct( $core ) {
		$this->core = $core;

		$this->register_hooks();
	}

	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
	}

	public function register_setting() {
		register_setting( 'syntaxhighlighter_settings', 'syntaxhighlighter_settings', array( $this, 'validate_settings' ) );
	}

	public function validate_settings( $settings ) {
		if ( ! empty( $_POST['syntaxhighlighter-defaults'] ) ) {
			$settings = array();
			$_REQUEST['_wp_http_referer'] = add_query_arg( 'defaults', 'true', $_REQUEST['_wp_http_referer'] );
		} else {
			$settings = $this->core->settings->validate_settings( $settings );
		}

		return $settings;
	}

	public function register_settings_page() {
		add_options_page( __( 'SyntaxHighlighter Settings', 'syntaxhighlighter' ), __( 'SyntaxHighlighter', 'syntaxhighlighter' ), 'manage_options', 'syntaxhighlighter', array( $this, 'settings_page' ) );
	}
}
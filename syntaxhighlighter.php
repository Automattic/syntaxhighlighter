<?php /*

**************************************************************************

Plugin Name:  SyntaxHighlighter Evolved
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/syntaxhighlighter/
Version:      4.0.0-alpha
Description:  Easily post syntax-highlighted code to your site without having to modify the code at all. Uses Alex Gorbatchev's <a href="http://alexgorbatchev.com/wiki/SyntaxHighlighter">SyntaxHighlighter</a>. <strong>TIP:</strong> Don't use the Visual Editor if you don't want your code mangled. It will "clean up" your HTML.
Author:       Alex Mills (Viper007Bond)
Author URI:   http://www.viper007bond.com/

**************************************************************************

Thanks to:

* Alex Gorbatchev for writing the Javascript-powered syntax-highlighter scripts
* Andrew Ozz for writing the TinyMCE plugin

**************************************************************************/

class SyntaxHighlighter {
	public $pluginver = '4.0.0-alpha';

	public $supported_renderers = array();
	public $settings_overrides = array();

	public $helpers;
	public $settings;
	public $admin;
	public $renderer;

	public $themes = array();
	public $languages = array();
	public $shortcodes = array();

	function __construct( $settings_overrides = array() ) {
		$this->settings_overrides = $settings_overrides;

		// Load localization file
		load_plugin_textdomain( 'syntaxhighlighter', false, dirname( plugin_basename( __FILE__ ) ) . '/localization/' );

		// Queue further initialization for later
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$this->load_helpers();
		$this->load_user_settings();
		$this->load_admin();
		$this->load_renderer();
	}

	public function load_helpers() {
		$this->helpers = new stdClass();

		require_once( __DIR__ . '/classes/class-formatting.php' );
		$this->helpers->formatting = new SyntaxHighlighter_Formatting( $this );
	}

	public function load_user_settings() {
		require_once( __DIR__ . '/classes/class-settings.php' );
		$this->settings = new SyntaxHighlighter_Settings( $this, $this->settings_overrides );
	}

	public function load_admin() {
		require_once( __DIR__ . '/classes/class-admin.php' );
		$this->admin = new SyntaxHighlighter_Admin( $this );
	}

	public function load_renderer() {
		$this->supported_renderers = apply_filters( 'syntaxhighlighter_supported_renderers', array(
			'sh2' => array(
				'file' => __DIR__ . '/classes/class-renderer-syntaxhighlighter2.php',
				'class' => 'SyntaxHighlighter_Renderer_SH2',
			),
			'sh3' => array(
				'file' => __DIR__ . '/classes/class-renderer-syntaxhighlighter3.php',
				'class' => 'SyntaxHighlighter_Renderer_SH3',
			),
		) );

		if ( empty( $this->settings->renderer ) || empty( $this->supported_renderers[ $this->settings->renderer ] ) ) {
			$this->settings->renderer = 'sh3';
		}

		// Just for simplicity
		$renderer = $this->supported_renderers[ $this->settings->renderer ];

		if ( ! empty( $renderer['file'] ) ) {
			require_once( $renderer['file'] );
		}

		$this->renderer = new $renderer['class']( $this );
	}
}


/**
 * Returns the single instance of SyntaxHighlighter Evolved, creating a new instance if needed.
 *
 * Use this function rather than the global variable if you need to interact with or
 * call one of SyntaxHighlighter Evolved's methods.
 *
 * For example if you wanted to unhook something that SyntaxHighlighter Evolved hooked, then
 * you would simple need to do this:
 *
 * remove_filter( 'the_filter_name', array( SyntaxHighlighter(), 'the_callback_name' ) );
 *
 * While this function existed before 4.0.0, it did not return the class instance.
 *
 * @since 4.0.0
 *
 * @return SyntaxHighlighter The single instance of SyntaxHighlighter.
 */
function SyntaxHighlighter() {
	global $SyntaxHighlighter;

	if ( ! isset( $SyntaxHighlighter ) ) {
		$SyntaxHighlighter = new SyntaxHighlighter();
	}

	return $SyntaxHighlighter;
}

/**
 * Start up SyntaxHighlighter Evolved once all other plugins have loaded.
 */
add_action( 'plugins_loaded', 'SyntaxHighlighter' );
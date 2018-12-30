<?php /*

**************************************************************************

Plugin Name:  SyntaxHighlighter Evolved
Plugin URI:   https://alex.blog/wordpress-plugins/syntaxhighlighter/
Version:      3.5.0
Description:  Easily post syntax-highlighted code to your site without having to modify the code at all. Uses Alex Gorbatchev's <a href="http://alexgorbatchev.com/SyntaxHighlighter/">SyntaxHighlighter</a>. Includes a new editor block.
Author:       Alex Mills (Viper007Bond)
Author URI:   https://alex.blog/
Text Domain:  syntaxhighlighter
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html

**************************************************************************/

class SyntaxHighlighter {
	// All of these variables are private. Filters are provided for things that can be modified.
	var $pluginver            = '3.5.0';  // Plugin version
	var $agshver              = false;    // Alex Gorbatchev's SyntaxHighlighter version (dynamically set below due to v2 vs v3)
	var $shfolder             = false;    // Controls what subfolder to load SyntaxHighlighter from (v2 or v3)
	var $settings             = array();  // Contains the user's settings
	var $defaultsettings      = array();  // Contains the default settings
	var $brushes              = array();  // Array of aliases => brushes
	var $shortcodes           = array();  // Array of shortcodes to use
	var $themes               = array();  // Array of themes
	var $usedbrushes          = array();  // Stores used brushes so we know what to output
	var $encoded              = false;    // Used to mark that a character encode took place
	var $codeformat           = false;    // If set, SyntaxHighlighter::get_code_format() will return this value
	var $content_save_pre_ran = false;    // It's possible for the "content_save_pre" filter to run multiple times, so keep track

	// Initalize the plugin by registering the hooks
	function __construct() {
		if ( ! function_exists( 'do_shortcodes_in_html_tags' ) )
			return;

		// Load localization domain
		load_plugin_textdomain( 'syntaxhighlighter' );

		// Display hooks
		add_filter( 'the_content', array( $this, 'parse_shortcodes' ), 7 ); // Posts
		add_filter( 'comment_text', array( $this, 'parse_shortcodes_comment' ), 7 ); // Comments
		add_filter( 'bp_get_the_topic_post_content', array( $this, 'parse_shortcodes' ), 7 ); // BuddyPress

		// Into the database
		add_filter( 'content_save_pre', array( $this, 'encode_shortcode_contents_slashed_noquickedit' ), 1 ); // Posts
		add_filter( 'pre_comment_content', array( $this, 'encode_shortcode_contents_slashed' ), 1 ); // Comments
		add_filter( 'group_forum_post_text_before_save', array( $this, 'encode_shortcode_contents_slashed' ), 1 ); // BuddyPress
		add_filter( 'group_forum_topic_text_before_save', array( $this, 'encode_shortcode_contents_slashed' ), 1 ); // BuddyPress

		// Out of the database for editing
		add_filter( 'the_editor_content', array( $this, 'the_editor_content' ), 1 ); // Posts
		add_filter( 'comment_edit_pre', array( $this, 'decode_shortcode_contents' ), 1 ); // Comments
		add_filter( 'bp_get_the_topic_text', array( $this, 'decode_shortcode_contents' ), 1 ); // BuddyPress
		add_filter( 'bp_get_the_topic_post_edit_text', array( $this, 'decode_shortcode_contents' ), 1 ); // BuddyPress

		// Outputting SyntaxHighlighter's JS and CSS
		add_action( 'wp_head', array( $this, 'output_header_placeholder' ), 15 );
		add_action( 'admin_head', array( $this, 'output_header_placeholder' ), 15 ); // For comments
		add_action( 'wp_footer', array( $this, 'maybe_output_scripts' ), 15 );
		add_action( 'admin_footer', array( $this, 'maybe_output_scripts' ), 15 ); // For comments

		// Admin hooks
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_filter( 'mce_external_plugins', array( $this, 'add_tinymce_plugin' ) );
		add_filter( 'save_post', array( $this, 'mark_as_encoded' ), 10, 2 );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );

		// Editor Blocks
		if (
			function_exists( 'parse_blocks' ) // WordPress 5.0+
			|| function_exists( 'the_gutenberg_project' ) // Gutenberg plugin for older WordPress
		) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
			add_action( 'the_content', array( $this, 'enable_brushes_used_in_blocks' ), 0 );
			register_block_type(
				'syntaxhighlighter/code',
				array(
					'render_callback' => array( $this, 'render_block' ),
				)
			);
		}

		// Register widget hooks
		add_filter( 'widget_text', array( $this, 'widget_text_output' ), 7, 2 );
		add_filter( 'widget_update_callback', array( $this, 'widget_text_save' ), 1, 4 );
		add_filter( 'widget_form_callback', array( $this, 'widget_text_form' ), 1, 2 );


		// Create array of default settings (you can use the filter to modify these)
		$this->defaultsettings = (array) apply_filters( 'syntaxhighlighter_defaultsettings', array(
			'theme'          => 'default',
			'loadallbrushes' => 0,
			'shversion'      => 3,
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

		// Create the settings array by merging the user's settings and the defaults
		$usersettings = (array) get_option('syntaxhighlighter_settings');
		$this->settings = wp_parse_args( $usersettings, $this->defaultsettings );

		// Dynamically set folder and version names for SynaxHighlighter
		if ( 2 == $this->settings['shversion'] ) {
			$this->shfolder = 'syntaxhighlighter2';
			$this->agshver  = '2.1.364';
		} else {
			$this->shfolder = 'syntaxhighlighter3';
			$this->agshver  = '3.0.9b';
		}

		// Register brush scripts
		wp_register_script( 'syntaxhighlighter-core',             plugins_url( $this->shfolder . '/scripts/shCore.js',            __FILE__ ), array(),                         $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-as3',        plugins_url( $this->shfolder . '/scripts/shBrushAS3.js',        __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-bash',       plugins_url( $this->shfolder . '/scripts/shBrushBash.js',       __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-coldfusion', plugins_url( $this->shfolder . '/scripts/shBrushColdFusion.js', __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-cpp',        plugins_url( $this->shfolder . '/scripts/shBrushCpp.js',        __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-csharp',     plugins_url( $this->shfolder . '/scripts/shBrushCSharp.js',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-css',        plugins_url( $this->shfolder . '/scripts/shBrushCss.js',        __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-delphi',     plugins_url( $this->shfolder . '/scripts/shBrushDelphi.js',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-diff',       plugins_url( $this->shfolder . '/scripts/shBrushDiff.js',       __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-erlang',     plugins_url( $this->shfolder . '/scripts/shBrushErlang.js',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-groovy',     plugins_url( $this->shfolder . '/scripts/shBrushGroovy.js',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-java',       plugins_url( $this->shfolder . '/scripts/shBrushJava.js',       __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-javafx',     plugins_url( $this->shfolder . '/scripts/shBrushJavaFX.js',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-jscript',    plugins_url( $this->shfolder . '/scripts/shBrushJScript.js',    __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-perl',       plugins_url( $this->shfolder . '/scripts/shBrushPerl.js',       __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-php',        plugins_url( $this->shfolder . '/scripts/shBrushPhp.js',        __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-plain',      plugins_url( $this->shfolder . '/scripts/shBrushPlain.js',      __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-powershell', plugins_url( $this->shfolder . '/scripts/shBrushPowerShell.js', __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-python',     plugins_url( $this->shfolder . '/scripts/shBrushPython.js',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-ruby',       plugins_url( $this->shfolder . '/scripts/shBrushRuby.js',       __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-scala',      plugins_url( $this->shfolder . '/scripts/shBrushScala.js',      __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-sql',        plugins_url( $this->shfolder . '/scripts/shBrushSql.js',        __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-vb',         plugins_url( $this->shfolder . '/scripts/shBrushVb.js',         __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-xml',        plugins_url( $this->shfolder . '/scripts/shBrushXml.js',        __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );

		// Register some popular third-party brushes
		wp_register_script( 'syntaxhighlighter-brush-clojure',    plugins_url( 'third-party-brushes/shBrushClojure.js',           __FILE__ ), array('syntaxhighlighter-core'), '20090602'     );
		wp_register_script( 'syntaxhighlighter-brush-fsharp',     plugins_url( 'third-party-brushes/shBrushFSharp.js',            __FILE__ ), array('syntaxhighlighter-core'), '20091003'     );
		wp_register_script( 'syntaxhighlighter-brush-latex',      plugins_url( 'third-party-brushes/shBrushLatex.js',             __FILE__ ), array('syntaxhighlighter-core'), '20090613'     );
		wp_register_script( 'syntaxhighlighter-brush-matlabkey',  plugins_url( 'third-party-brushes/shBrushMatlabKey.js',         __FILE__ ), array('syntaxhighlighter-core'), '20091209'     );
		wp_register_script( 'syntaxhighlighter-brush-objc',       plugins_url( 'third-party-brushes/shBrushObjC.js',              __FILE__ ), array('syntaxhighlighter-core'), '20091207'     );
		wp_register_script( 'syntaxhighlighter-brush-r',          plugins_url( 'third-party-brushes/shBrushR.js',                 __FILE__ ), array('syntaxhighlighter-core'), '20100919'     );

		// Register theme stylesheets
		wp_register_style(  'syntaxhighlighter-core',             plugins_url( $this->shfolder . '/styles/shCore.css',            __FILE__ ), array(),                         $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-default',    plugins_url( $this->shfolder . '/styles/shThemeDefault.css',    __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-django',     plugins_url( $this->shfolder . '/styles/shThemeDjango.css',     __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-eclipse',    plugins_url( $this->shfolder . '/styles/shThemeEclipse.css',    __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-emacs',      plugins_url( $this->shfolder . '/styles/shThemeEmacs.css',      __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-fadetogrey', plugins_url( $this->shfolder . '/styles/shThemeFadeToGrey.css', __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-midnight',   plugins_url( $this->shfolder . '/styles/shThemeMidnight.css',   __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-rdark',      plugins_url( $this->shfolder . '/styles/shThemeRDark.css',      __FILE__ ), array('syntaxhighlighter-core'), $this->agshver );


		// Create list of brush aliases and map them to their real brushes
		// The key is the language alias
		// The value is the script handle suffix: syntaxhighlighter-brush-ThisBitHere  (your plugin needs to register the script itself)
		$this->brushes = (array) apply_filters( 'syntaxhighlighter_brushes', array(
			'as3'           => 'as3',
			'actionscript3' => 'as3',
			'bash'          => 'bash',
			'shell'         => 'bash',
			'coldfusion'    => 'coldfusion',
			'cf'            => 'coldfusion',
			'clojure'       => 'clojure',
			'clj'           => 'clojure',
			'cpp'           => 'cpp',
			'c'             => 'cpp',
			'c-sharp'       => 'csharp',
			'csharp'        => 'csharp',
			'css'           => 'css',
			'delphi'        => 'delphi',
			'pas'           => 'delphi',
			'pascal'        => 'delphi',
			'diff'          => 'diff',
			'patch'         => 'diff',
			'erl'           => 'erlang',
			'erlang'        => 'erlang',
			'fsharp'        => 'fsharp',
			'groovy'        => 'groovy',
			'java'          => 'java',
			'jfx'           => 'javafx',
			'javafx'        => 'javafx',
			'js'            => 'jscript',
			'jscript'       => 'jscript',
			'javascript'    => 'jscript',
			'latex'         => 'latex', // Not used as a shortcode
			'tex'           => 'latex',
			'matlab'        => 'matlabkey',
			'objc'          => 'objc',
			'obj-c'         => 'objc',
			'perl'          => 'perl',
			'pl'            => 'perl',
			'php'           => 'php',
			'plain'         => 'plain',
			'text'          => 'plain',
			'ps'            => 'powershell',
			'powershell'    => 'powershell',
			'py'            => 'python',
			'python'        => 'python',
			'r'             => 'r', // Not used as a shortcode
			'splus'         => 'r',
			'rails'         => 'ruby',
			'rb'            => 'ruby',
			'ror'           => 'ruby',
			'ruby'          => 'ruby',
			'scala'         => 'scala',
			'sql'           => 'sql',
			'vb'            => 'vb',
			'vbnet'         => 'vb',
			'xml'           => 'xml',
			'xhtml'         => 'xml',
			'xslt'          => 'xml',
			'html'          => 'xml',
		) );

		$this->brush_names = (array) apply_filters( 'syntaxhighlighter_brush_names', array(
			'as3'        => __( 'ActionScript',              'syntaxhighlighter' ),
			'bash'       => __( 'BASH / Shell',              'syntaxhighlighter' ),
			'coldfusion' => __( 'ColdFusion',                'syntaxhighlighter' ),
			'clojure'    => __( 'Clojure',                   'syntaxhighlighter' ),
			'cpp'        => __( 'C / C++',                   'syntaxhighlighter' ),
			'csharp'     => __( 'C#',                        'syntaxhighlighter' ),
			'css'        => __( 'CSS',                       'syntaxhighlighter' ),
			'delphi'     => __( 'Delphi / Pascal',           'syntaxhighlighter' ),
			'diff'       => __( 'diff / patch',              'syntaxhighlighter' ),
			'erlang'     => __( 'Erlang',                    'syntaxhighlighter' ),
			'fsharp'     => __( 'F#',                        'syntaxhighlighter' ),
			'groovy'     => __( 'Groovy',                    'syntaxhighlighter' ),
			'java'       => __( 'Java',                      'syntaxhighlighter' ),
			'javafx'     => __( 'JavaFX',                    'syntaxhighlighter' ),
			'jscript'    => __( 'JavaScript',                'syntaxhighlighter' ),
			'latex'      => __( 'LaTeX',                     'syntaxhighlighter' ),
			'matlabkey'  => __( 'MATLAB',                    'syntaxhighlighter' ),
			'objc'       => __( 'Objective-C',               'syntaxhighlighter' ),
			'perl'       => __( 'Perl',                      'syntaxhighlighter' ),
			'php'        => __( 'PHP',                       'syntaxhighlighter' ),
			'plain'      => __( 'Plain Text',                'syntaxhighlighter' ),
			'powershell' => __( 'PowerShell',                'syntaxhighlighter' ),
			'python'     => __( 'Python',                    'syntaxhighlighter' ),
			'r'          => __( 'R',                         'syntaxhighlighter' ),
			'ruby'       => __( 'Ruby / Ruby on Rails',      'syntaxhighlighter' ),
			'scala'      => __( 'Scala',                     'syntaxhighlighter' ),
			'sql'        => __( 'SQL',                       'syntaxhighlighter' ),
			'vb'         => __( 'Visual Basic',              'syntaxhighlighter' ),
			'xml'        => __( 'HTML / XHTML / XML / XSLT', 'syntaxhighlighter' ),
		) );

		// Add any custom brushes that aren't making use of the newer "syntaxhighlighter_brush_names" filter.
		foreach ( $this->brushes as $slug => $language ) {
			if ( ! isset( $this->brush_names[ $language ] ) ) {
				$this->brush_names[ $language ] = $slug;
			}
		}

		// Create a list of shortcodes to use. You can use the filter to add/remove ones.
		// If the language/lang parameter is left out, it's assumed the shortcode name is the language.
		// If that's invalid, then "plain" is used.
		$this->shortcodes = array( 'sourcecode', 'source', 'code' );
		$this->shortcodes = array_merge( $this->shortcodes, array_keys( $this->brushes ) );

		// Remove some shortcodes we don't want while still supporting them as language values
		unset( $this->shortcodes[array_search( 'latex', $this->shortcodes )] ); // Remove "latex" shortcode (it'll collide)
		unset( $this->shortcodes[array_search( 'r', $this->shortcodes )] ); // Remove "r" shortcode (too short)

		$this->shortcodes = (array) apply_filters( 'syntaxhighlighter_shortcodes', $this->shortcodes );


		// Register each shortcode with a placeholder callback so that strip_shortcodes() will work
		// The proper callback and such is done in SyntaxHighlighter::shortcode_hack()
		foreach ( $this->shortcodes as $shortcode )
			add_shortcode( $shortcode, '__return_empty_string' );


		// Create list of themes and their human readable names
		// Plugins can add to this list: http://www.viper007bond.com/wordpress-plugins/syntaxhighlighter/adding-a-new-theme/
		$this->themes = (array) apply_filters( 'syntaxhighlighter_themes', array(
			'default'    => __( 'Default',      'syntaxhighlighter' ),
			'django'     => __( 'Django',       'syntaxhighlighter' ),
			'eclipse'    => __( 'Eclipse',      'syntaxhighlighter' ),
			'emacs'      => __( 'Emacs',        'syntaxhighlighter' ),
			'fadetogrey' => __( 'Fade to Grey', 'syntaxhighlighter' ),
			'midnight'   => __( 'Midnight',     'syntaxhighlighter' ),
			'rdark'      => __( 'RDark',        'syntaxhighlighter' ),
			'none'       => __( '[None]',       'syntaxhighlighter' ),
		) );

		// Other special characters that need to be encoded before going into the database (namely to work around kses)
		$this->specialchars = (array) apply_filters( 'syntaxhighlighter_specialchars', array(
			'\0' => '&#92;&#48;',
		) );
	}


	// Register the settings page
	function register_settings_page() {
		add_options_page( __( 'SyntaxHighlighter Settings', 'syntaxhighlighter' ), __( 'SyntaxHighlighter', 'syntaxhighlighter' ), 'manage_options', 'syntaxhighlighter', array( $this, 'settings_page' ) );
	}


	// Register the plugin's setting
	function register_setting() {
		register_setting( 'syntaxhighlighter_settings', 'syntaxhighlighter_settings', array( $this, 'validate_settings' ) );
	}

	// Enqueue block assets for the Editor
	function enqueue_block_editor_assets() {
		wp_enqueue_script(
			'syntaxhighlighter-blocks',
			plugins_url( 'dist/blocks.build.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			( defined( 'WP_DEBUG' ) && WP_DEBUG )
				? filemtime( plugin_dir_path( __FILE__ ) . 'dist/blocks.build.js' )
				: $this->pluginver
		);

		// WordPress 5.0+ only, no Gutenberg plugin support
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'syntaxhighlighter-blocks', 'syntaxhighlighter' );
		}

		natcasesort( $this->brush_names );

		$settings = (object) array(
			'language' => (object) array(
				'supported' => true,
				'default' => 'plain'
			),
			'lineNumbers' => (object) array(
				'supported' => true,
				'default' => (bool) $this->settings['gutter'],
			),
			'firstLineNumber' => (object) array(
				'supported' => true,
				'default' => $this->settings['firstline'],
			),
			'highlightLines' => (object) array(
				'supported' => true,
				'default' => '',
			),
			'wrapLines' => (object) array(
				'supported' => ( '2' == $this->settings['shversion'] ),
				'default' => (bool) $this->settings['wraplines'],
			),
			'makeURLsClickable' => (object) array(
				'supported' => true,
				'default' => (bool) $this->settings['autolinks'],
			),
		);

		wp_add_inline_script(
			'syntaxhighlighter-blocks',
			sprintf( '
				var syntaxHighlighterData = {
					brushes: %s,
					settings: %s,
				};',
				json_encode( $this->brush_names ),
				json_encode( $settings )
			),
			'before'
		);
	}

	/**
	 * Enable the brushes that are used in blocks.
	 *
	 * For the shortcode, brushes are activated by `shortcode_callback()`, but that won't detect brushes that were
	 * used in blocks. For that, we need to extract the data from the block's attributes.
	 *
	 * @since 3.3.0
	 *
	* @param string $content The post content.
	  *
	 * @return string Unmodified $content.
	 */
	function enable_brushes_used_in_blocks( $content ) {
		/*
		 * Return early on the backend because SyntaxHighlighting is only active on the front end, see
		 * `syntaxHighlighterCode::edit()` block for details.
		 */
		if ( is_admin() ) {
			return $content;
		}

		// Lower overhead than a full parse.
		if (
			! has_block( 'syntaxhighlighter/code', $content )
			&& ! has_block( 'core/block', $content ) // Reusable
		) {
			return $content;
		}

		if ( function_exists( 'parse_blocks' ) ) { // WP 5.0+
			$blocks = parse_blocks( $content );
		} elseif ( Function_exists( 'gutenberg_parse_blocks' ) ) { // Gutenberg plugin
			$blocks = gutenberg_parse_blocks( $content );
		} else {
			return $content;
		}

		foreach ( $blocks as $block ) {
			if ( empty( $block['blockName'] ) ) {
				continue;
			}

			switch ( $block['blockName'] ) {
				// Normal block usage
				case 'syntaxhighlighter/code':
					$language = ( ! empty( $block['attrs']['language'] ) ) ? $block['attrs']['language'] : 'plain';

					if ( in_array( $language, $this->brushes, true ) ) {
						$this->usedbrushes[ $this->brushes[ $language ] ] = true;
					}

					break;

				// But the block could also be used inside of a reusable block
				case 'core/block':
					/**
					 * Rather than going down the block parsing rabbit hole of dynamic blocks,
					 * let's just hook in post-render and look for our HTML.
 					 */
					add_filter( 'the_content', array( $this, 'enable_brushes_via_raw_html_parsing' ), 10 );

					break;
			}
		}

		return $content;
	}

	/**
	 * Parses raw HTML looking for SyntaxHighlighter's <pre> tags and queues brushes
	 * for the languages used.
	 *
	 * This filter only runs when a reusable block is used in a post.
	 * It was easier to implement than loading the block, which then could
	 * have child blocks and so forth. Technically this means that we're
	 * parsing any regular SyntaxHighlighter blocks again, but oh well. :)
	 *
	 * @since 3.4.2
	 *
	* @param string $content The post content.
	 *
	 * @return string Unmodified $content.
	 */
	public function enable_brushes_via_raw_html_parsing( $content ) {
		if ( false === strpos( $content,'<pre class="wp-block-syntaxhighlighter-code' ) ) {
			return $content;
		}

		preg_match_all(
			'/<pre class="wp-block-syntaxhighlighter-code brush: ([^;]+);/',
			$content,
			$languages,
			PREG_PATTERN_ORDER
		);

		if ( is_array( $languages ) ) {
			foreach ( $languages[1] as $language ) {
				if ( in_array( $language, $this->brushes, true ) ) {
					$this->usedbrushes[ $this->brushes[ $language ] ] = true;
				}
			}
		}

		return $content;
	}

	/**
	 * Renders the content of the Gutenberg block on the front end
	 * using the shortcode callback. This ensures one source of truth
	 * and allows for forward compatibility.
	 *
	* @param string $content The block's content.
	 *
	 * @return string The rendered content.
	 */
	public function render_block( $attributes, $content ) {
		$remaps = array(
			'lineNumbers'       => 'gutter',
			'firstLineNumber'   => 'firstline',
			'highlightLines'    => 'highlight',
			'wrapLines'         => 'wraplines',
			'makeURLsClickable' => 'autolinks',
		);

		foreach ( $remaps as $from => $to ) {
			if ( isset( $attributes[ $from ] ) ) {
				if ( is_bool( $attributes[ $from ] ) ) {
					$attributes[ $to ] = ( $attributes[ $from ] ) ? '1' : '0';
				} else {
					$attributes[ $to ] = $attributes[ $from ];
				}

				unset( $attributes[ $from ] );
			}
		}

		$code = preg_replace( '#<pre [^>]+>([^<]+)?</pre>#', '$1', $content );

		// Undo escaping done by WordPress
		$code = str_replace( '&lt;', '<', $code );

		return $this->shortcode_callback( $attributes, $code, 'code' );
	}

	// Add the custom TinyMCE plugin which wraps plugin shortcodes in <pre> in TinyMCE
	function add_tinymce_plugin( $plugins ) {
		global $tinymce_version;

		add_action( 'admin_print_footer_scripts', array( $this, 'output_shortcodes_for_tinymce' ), 9 );

		if ( substr( $tinymce_version, 0, 1 ) < 4 ) {
			$plugins['syntaxhighlighter'] = plugins_url( 'syntaxhighlighter_mce.js', __FILE__ );
		} else {
			$plugins['syntaxhighlighter'] = add_query_arg( 'ver', $this->pluginver, plugins_url( 'syntaxhighlighter_mce-4.js', __FILE__ ) );
			wp_enqueue_script( 'syntaxhighlighter', plugins_url( 'syntaxhighlighter.js', __FILE__ ), array(), false, true );
		}

		return $plugins;
	}


	// Break the TinyMCE cache
	function break_tinymce_cache( $version ) {
		return $version . '-sh' . $this->pluginver;
	}


	// Add a "Settings" link to the plugins page
	function settings_link( $links, $file ) {
		static $this_plugin;

		if( empty($this_plugin) )
			$this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin )
			$links[] = '<a href="' . admin_url( 'options-general.php?page=syntaxhighlighter' ) . '">' . __( 'Settings', 'syntaxhighlighter' ) . '</a>';

		return $links;
	}


	// Output list of shortcode tags for the TinyMCE plugin
	function output_shortcodes_for_tinymce() {
		$shortcodes = array();

		foreach ( $this->shortcodes as $shortcode )
			$shortcodes[] = preg_quote( $shortcode );

		echo "<script type='text/javascript'>\n";
		echo "	var syntaxHLcodes = '" . implode( '|', $shortcodes ) . "';\n";
		echo "</script>\n";
	}


	/**
	 * Process only this plugin's shortcodes.
	 *
	 * If we waited for the normal do_shortcode() call at priority 11,
	 * then wpautop() and maybe others would mangle all of the code.
	 *
	 * So instead we hook in earlier with this function and process
	 * just this plugins's shortcodes. To do this requires some trickery.
	 *
	 * First we need to clear out all existing shortcodes, then register
	 * just this plugin's ones, process them, and then restore the original
	 * list of shortcodes.
	 *
	 * To make matters more complicated, if someone has done [[code]foo[/code]]
	 * in order to display the shortcode (not render it), then do_shortcode()
	 * will strip the outside brackets and when do_shortcode() runs a second
	 * time later on, it will render it.
	 *
	 * So instead before do_shortcode() runs for the first time, we add
	 * even more brackets escaped shortcodes in order to result in
	 * the shortcodes actually being displayed instead rendered.
	 *
	 * We only need to do this for this plugin's shortcodes however
	 * as all other shortcodes such as [[gallery]] will be untouched
	 * by this pass of do_shortcode.
	 *
	 * Phew!
	 *
	 * @param string $content     The post content.
	 * @param array  $callback    The callback function that should be used for add_shortcode()
	 * @param bool   $ignore_html When true, shortcodes inside HTML elements will be skipped.
	 *
	 * @return string The filtered content, with this plugin's shortcodes parsed.
	 */
	function shortcode_hack( $content, $callback, $ignore_html = true ) {
		global $shortcode_tags;

		// Regex is slow. Let's do some strpos() checks first.
		if ( ! $this->string_has_shortcodes( $content, $this->shortcodes ) ) {
			return $content;
		}

		// Backup current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		// Register all of this plugin's shortcodes
		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode( $shortcode, $callback );
		}

		$regex = '/' . get_shortcode_regex( $this->shortcodes ) . '/';

		// Parse the shortcodes (only this plugins's are registered)
		if ( $ignore_html ) {
			// Extra escape escaped shortcodes because do_shortcode_tag() called by do_shortcode() is going to strip a pair of square brackets when it runs
			$content = preg_replace_callback(
				$regex,
				array( $this, 'shortcode_hack_extra_escape_escaped_shortcodes' ),
				$content
			);

			// Normal, safe parsing
			$content = do_shortcode( $content, true );
		} else {
			// Extra escape escaped shortcodes because do_shortcode_tag() called by do_shortcode() is going to strip a pair of square brackets when it runs.
			// Then call do_shortcode_tag(). This is basically do_shortcode() without calling do_shortcodes_in_html_tags() which breaks things.
			// For context, see https://wordpress.org/support/topic/php-opening-closing-tags-break-code-blocks
			$content = preg_replace_callback(
				$regex,
				array( $this, 'shortcode_hack_extra_escape_escaped_shortcodes_and_parse' ),
				$content
			);
		}

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}


	/**
	 * A quick checker to see if any of this plugin's shortcodes are in use in a string.
	 * Since all of the tags can't be self-closing, we look for the closing tag.
	 *
	 * @param string $string     The string to look through. This is a post's contents usually.
	 * @param array  $shortcodes The array of shortcodes to look for.
	 *
	 * @return bool Whether any shortcode usage was found.
	 */
	function string_has_shortcodes( $string, $shortcodes ) {
		foreach ( $shortcodes as $shortcode ) {
			if ( false !== strpos( $string, "[/{$shortcode}]" ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Add extra square brackets around escaped shortcodes.
	 * This is to counteract the beginning of the do_shortcode_tag() function.
	 *
	 * @param array $match The array of matches generated by get_shortcode_regex()
	 *
	 * @return string What should be placed into the post content. In this case it's the raw match, or an extra-wrapped raw match.
	 */
	function shortcode_hack_extra_escape_escaped_shortcodes( $match ) {
		if ( $match[1] == '[' && $match[6] == ']' ) {
			return '[' . $match[0] . ']';
		}

		return $match[0];
	}

	/**
	 * This is a combination of this class's shortcode_hack_extra_escape_escaped_shortcodes()
  	 * and do_shortcode_tag() for performance reasons so that we don't have to run some regex twice.
 	 *
	 * @param array $match Regular expression match array.
	 *
	 * @return string|false False on failure, otherwise a parse shortcode tag.
	 */
	function shortcode_hack_extra_escape_escaped_shortcodes_and_parse( $match ) {
		$match[0] = $this->shortcode_hack_extra_escape_escaped_shortcodes( $match );

		return do_shortcode_tag( $match );
	}


	// The main filter for the post contents. The regular shortcode filter can't be used as it's post-wpautop().
	function parse_shortcodes( $content ) {
		return $this->shortcode_hack( $content, array( $this, 'shortcode_callback' ) );
	}


	// HTML entity encode the contents of shortcodes
	function encode_shortcode_contents( $content ) {
		return $this->shortcode_hack( $content, array( $this, 'encode_shortcode_contents_callback' ), false );
	}


	// HTML entity encode the contents of shortcodes. Expects slashed content.
	function encode_shortcode_contents_slashed( $content ) {
		return addslashes( $this->encode_shortcode_contents( stripslashes( $content ) ) );
	}


	// HTML entity encode the contents of shortcodes. Expects slashed content. Aborts if AJAX.
	function encode_shortcode_contents_slashed_noquickedit( $content ) {

		// In certain weird circumstances, the content gets run through "content_save_pre" twice
		// Keep track and don't allow this filter to be run twice
		// I couldn't easily figure out why this happens and didn't bother looking into it further as this works fine
		if ( true == $this->content_save_pre_ran ) {
			return $content;
		}
		$this->content_save_pre_ran = true;

		// Post quick edits aren't decoded for display, so we don't need to encode them (again)
		// This also aborts for (un)trashing to avoid extra encoding.
		if ( empty( $_POST ) || ( ! empty( $_POST['action'] ) && 'inline-save' == $_POST['action'] ) ) {
			return $content;
		}

		return $this->encode_shortcode_contents_slashed( $content );
	}


	// HTML entity decode the contents of shortcodes
	function decode_shortcode_contents( $content ) {
		return $this->shortcode_hack( $content, array( $this, 'decode_shortcode_contents_callback' ), false );
	}


	// The callback function for SyntaxHighlighter::encode_shortcode_contents()
	function encode_shortcode_contents_callback( $atts, $code = '', $tag = false ) {
		$this->encoded = true;
		$code = str_replace( array_keys($this->specialchars), array_values($this->specialchars), htmlspecialchars( $code ) );
		return '[' . $tag . $this->atts2string( $atts ) . "]{$code}[/$tag]";
	}


	// The callback function for SyntaxHighlighter::decode_shortcode_contents()
	// Shortcode attribute values need to not be quoted with TinyMCE disabled for some reason (weird bug)
	function decode_shortcode_contents_callback( $atts, $code = '', $tag = false ) {
		$quotes = ( user_can_richedit() ) ? true : false;
		$code = str_replace(  array_values($this->specialchars), array_keys($this->specialchars), htmlspecialchars_decode( $code ) );
		return '[' . $tag . $this->atts2string( $atts, $quotes ) . "]{$code}[/$tag]";
	}


	// Dynamically format the post content for the edit form
	function the_editor_content( $content ) {
		global $post;

		// New code format (stored encoded in database)
		if ( 2 == $this->get_code_format( $post ) ) {
			// If TinyMCE is disabled or the HTML tab is set to be displayed first, we need to decode the HTML
			if ( !user_can_richedit() || 'html' == wp_default_editor() )
				$content = $this->decode_shortcode_contents( $content );
		}

		// Old code format (stored raw in database)
		else {
			// If TinyMCE is enabled and is set to be displayed first, we need to encode the HTML
			if ( user_can_richedit() && 'html' != wp_default_editor() )
				$content = $this->encode_shortcode_contents( $content );
		}

		return $content;
	}


	// Run SyntaxHighlighter::encode_shortcode_contents() on the contents of the text widget
	function widget_text_save( $instance, $new_instance, $old_instance, $widgetclass ) {
		if ( 'text' == $widgetclass->id_base ) {
			// Re-save the widget settings but this time with the shortcode contents encoded
			$new_instance['text'] = $this->encode_shortcode_contents( $new_instance['text'] );
			$instance = $widgetclass->update( $new_instance, $old_instance );

			// And flag it as encoded
			$instance['syntaxhighlighter_encoded'] = true;
		}

		return $instance;
	}


	// Run SyntaxHighlighter::decode_shortcode_contents_callback() on the contents of the text widget form
	function widget_text_form( $instance, $widgetclass ) {
		if ( 'text' == $widgetclass->id_base && !empty($instance['syntaxhighlighter_encoded']) ) {
			$instance['text'] = $this->shortcode_hack( $instance['text'], array( $this, 'decode_shortcode_contents_callback' ) );
		}

		return $instance;
	}


	// Run SyntaxHighlighter::parse_shortcodes() on the contents of a text widget
	function widget_text_output( $content, $instance = false ) {
		$this->codeformat = ( false === $instance || empty($instance['syntaxhighlighter_encoded']) ) ? 1 : 2;
		$content = $this->parse_shortcodes( $content );
		$this->codeformat = false;

		return $content;
	}


	// Run SyntaxHighlighter::parse_shortcodes() on the contents of a comment
	function parse_shortcodes_comment( $content ) {
		$this->codeformat = 2;
		$content = $this->parse_shortcodes( $content );
		$this->codeformat = false;

		return $content;
	}


	// This function determines what version of SyntaxHighlighter was used when the post was written
	// This is because the code was stored differently for different versions of SyntaxHighlighter
	function get_code_format( $post ) {
		if ( false !== $this->codeformat )
			return $this->codeformat;

		if ( empty($post) )
			$post = new stdClass();

		if ( null !== $version = apply_filters( 'syntaxhighlighter_pre_getcodeformat', null, $post ) )
			return $version;

		$version = ( empty($post->ID) || get_post_meta( $post->ID, '_syntaxhighlighter_encoded', true ) || get_post_meta( $post->ID, 'syntaxhighlighter_encoded', true ) ) ? 2 : 1;

		return apply_filters( 'syntaxhighlighter_getcodeformat', $version, $post );
	}


	// Adds a post meta saying that HTML entities are encoded (for backwards compatibility)
	function mark_as_encoded( $post_ID, $post ) {
		if ( false == $this->encoded || 'revision' == $post->post_type )
			return;

		delete_post_meta( $post_ID, 'syntaxhighlighter_encoded' ); // Previously used
		add_post_meta( $post_ID, '_syntaxhighlighter_encoded', true, true );
	}


	// Transforms an attributes array into a 'key="value"' format (i.e. reverses the process)
	function atts2string( $atts, $quotes = true ) {
		if ( empty($atts) )
			return '';

		$atts = $this->attributefix( $atts );

		// Re-map [code="php"] style tags
		if ( isset($atts[0]) ) {
			if ( empty($atts['language']) )
				$atts['language'] = $atts[0];

			unset($atts[0]);
		}

		$strings = array();
		foreach ( $atts as $key => $value )
			$strings[] = ( $quotes ) ? $key . '="' . esc_attr( $value ) . '"' : $key . '=' . esc_attr( $value );

		return ' ' . implode( ' ', $strings );
	}


	// Simple function for escaping just single quotes (the original js_escape() escapes more than we need)
	function js_escape_singlequotes( $string ) {
		return str_replace( "'", "\'", $string );
	}


	// Output an anchor in the header for the Javascript to use.
	// In the <head>, we don't know if we'll need this plugin's CSS and JavaScript yet but we will in the footer.
	function output_header_placeholder() {
		echo '<style type="text/css" id="syntaxhighlighteranchor"></style>' . "\n";
	}


	// Output any needed scripts. This is meant for the footer.
	function maybe_output_scripts() {
		global $wp_styles;

		if ( 1 == $this->settings['loadallbrushes'] )
			$this->usedbrushes = array_flip( array_values( $this->brushes ) );

		if ( empty($this->usedbrushes) )
			return;

		$scripts = array();
		foreach ( $this->usedbrushes as $brush => $unused )
			$scripts[] = 'syntaxhighlighter-brush-' . strtolower( $brush );

		wp_print_scripts( $scripts );

		// Stylesheets can't be in the footer, so inject them via Javascript
		echo "<script type='text/javascript'>\n";
		echo "	(function(){\n";
		echo "		var corecss = document.createElement('link');\n";
		echo "		var themecss = document.createElement('link');\n";

		if ( !is_a($wp_styles, 'WP_Styles') )
			$wp_styles = new WP_Styles();

		$needcore = false;
		if ( 'none' == $this->settings['theme'] ) {
			$needcore = true;
		} else {
			$theme = ( !empty($this->themes[$this->settings['theme']]) ) ? strtolower($this->settings['theme']) : $this->defaultsettings['theme'];
			$theme = 'syntaxhighlighter-theme-' . $theme;

			// See if the requested theme has been registered
			if ( !empty($wp_styles) && !empty($wp_styles->registered) && !empty($wp_styles->registered[$theme]) && !empty($wp_styles->registered[$theme]->src) ) {

				// Users can register their own stylesheet and may opt to not load the core stylesheet if they wish for some reason
				if ( is_array($wp_styles->registered[$theme]->deps) && in_array( 'syntaxhighlighter-core', $wp_styles->registered[$theme]->deps ) )
					$needcore = true;
			}

			// Otherwise use the default theme
			else {
				$theme = 'syntaxhighlighter-theme-' . $this->defaultsettings['theme'];
				$needcore = true;
			}
		}

		if ( $needcore && !empty($wp_styles) && !empty($wp_styles->registered) && !empty($wp_styles->registered['syntaxhighlighter-core']) && !empty($wp_styles->registered['syntaxhighlighter-core']->src) ) :
			$corecssurl = add_query_arg( 'ver', $this->agshver, $wp_styles->registered['syntaxhighlighter-core']->src );
			$corecssurl = apply_filters( 'syntaxhighlighter_csscoreurl', $corecssurl );
?>
		var corecssurl = "<?php echo esc_js( $corecssurl ); ?>";
		if ( corecss.setAttribute ) {
				corecss.setAttribute( "rel", "stylesheet" );
				corecss.setAttribute( "type", "text/css" );
				corecss.setAttribute( "href", corecssurl );
		} else {
				corecss.rel = "stylesheet";
				corecss.href = corecssurl;
		}
		document.getElementsByTagName("head")[0].insertBefore( corecss, document.getElementById("syntaxhighlighteranchor") );
<?php
		endif; // Endif $needcore

		if ( 'none' != $this->settings['theme'] ) : ?>
		var themecssurl = "<?php echo esc_js( apply_filters( 'syntaxhighlighter_cssthemeurl', add_query_arg( 'ver', $this->agshver, $wp_styles->registered[$theme]->src ) ) ); ?>";
		if ( themecss.setAttribute ) {
				themecss.setAttribute( "rel", "stylesheet" );
				themecss.setAttribute( "type", "text/css" );
				themecss.setAttribute( "href", themecssurl );
		} else {
				themecss.rel = "stylesheet";
				themecss.href = themecssurl;
		}
		//document.getElementById("syntaxhighlighteranchor").appendChild(themecss);
		document.getElementsByTagName("head")[0].insertBefore( themecss, document.getElementById("syntaxhighlighteranchor") );
<?php
		endif; // Endif none != theme

		echo "	})();\n";

		switch ( $this->settings['shversion'] ) {
			case 2:
				echo "	SyntaxHighlighter.config.clipboardSwf = '" . esc_js( apply_filters( 'syntaxhighlighter_clipboardurl', plugins_url( 'syntaxhighlighter2/scripts/clipboard.swf', __FILE__ ) ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.expandSource = '" . $this->js_escape_singlequotes( __( 'show source', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.viewSource = '" . $this->js_escape_singlequotes( __( 'view source', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.copyToClipboard = '" . $this->js_escape_singlequotes( __( 'copy to clipboard', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.copyToClipboardConfirmation = '" . $this->js_escape_singlequotes( __( 'The code is in your clipboard now', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.print = '" . $this->js_escape_singlequotes( __( 'print', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.help = '" . $this->js_escape_singlequotes( __( '?', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.alert = '" . $this->js_escape_singlequotes( __( 'SyntaxHighlighter\n\n', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.noBrush = '" . $this->js_escape_singlequotes( __( "Can't find brush for: ", 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.brushNotHtmlScript = '" . $this->js_escape_singlequotes( __( "Brush wasn't configured for html-script option: ", 'syntaxhighlighter' ) ) . "';\n";
				break;
			case 3:
				echo "	SyntaxHighlighter.config.strings.expandSource = '" . $this->js_escape_singlequotes( __( '+ expand source', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.help = '" . $this->js_escape_singlequotes( __( '?', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.alert = '" . $this->js_escape_singlequotes( __( 'SyntaxHighlighter\n\n', 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.noBrush = '" . $this->js_escape_singlequotes( __( "Can't find brush for: ", 'syntaxhighlighter' ) ) . "';\n";
				echo "	SyntaxHighlighter.config.strings.brushNotHtmlScript = '" . $this->js_escape_singlequotes( __( "Brush wasn't configured for html-script option: ", 'syntaxhighlighter' ) ) . "';\n";
				break;
		}

		if ( 1 != $this->settings['autolinks'] )
			echo "	SyntaxHighlighter.defaults['auto-links'] = false;\n";

		if ( !empty($this->settings['classname']) )
			echo "	SyntaxHighlighter.defaults['class-name'] = '" . $this->js_escape_singlequotes( $this->settings['classname'] ) . "';\n";

		if ( 1 == $this->settings['collapse'] )
			echo "	SyntaxHighlighter.defaults['collapse'] = true;\n";

		if ( 1 != $this->settings['firstline'] )
			echo "	SyntaxHighlighter.defaults['first-line'] = " . $this->settings['firstline'] . ";\n";

		if ( 1 != $this->settings['gutter'] )
			echo "	SyntaxHighlighter.defaults['gutter'] = false;\n";

		/*
		if ( 1 == $this->settings['htmlscript'] )
			echo "	SyntaxHighlighter.defaults['html-script'] = true;\n";
		*/

		if ( 1 == $this->settings['light'] )
			echo "	SyntaxHighlighter.defaults['light'] = true;\n";

		echo "	SyntaxHighlighter.defaults['pad-line-numbers'] = ";
		switch ( $this->settings['padlinenumbers'] ) {
			case 'true':
				echo 'true';
				break;
			case 'false';
				echo 'false';
				break;
			default;
				echo (int) $this->settings['padlinenumbers'];
		}
		echo ";\n";

		if ( 1 != $this->settings['smarttabs'] )
			echo "	SyntaxHighlighter.defaults['smart-tabs'] = false;\n";

		if ( 4 != $this->settings['tabsize'] )
			echo "	SyntaxHighlighter.defaults['tab-size'] = " . $this->settings['tabsize'] . ";\n";

		if ( 1 != $this->settings['toolbar'] )
			echo "	SyntaxHighlighter.defaults['toolbar'] = false;\n";

		// 2.x only for now
		if ( 1 != $this->settings['wraplines'] )
			echo "	SyntaxHighlighter.defaults['wrap-lines'] = false;\n";

?>	SyntaxHighlighter.all();

	// Infinite scroll support
	if ( typeof( jQuery ) !== 'undefined' ) {
		jQuery( function( $ ) {
			$( document.body ).on( 'post-load', function() {
				SyntaxHighlighter.highlight();
			} );
		} );
	}
</script>
<?php

		do_action( 'syntaxhighlighter_after_script_output' );

	}


	// No-name attribute fixing
	function attributefix( $atts = array() ) {
		if ( empty($atts[0]) )
			return $atts;

		// Quoted value
		if ( 0 !== preg_match( '#=("|\')(.*?)\1#', $atts[0], $match ) )
			$atts[0] = $match[2];

		// Unquoted value
		elseif ( '=' == substr( $atts[0], 0, 1 ) )
			$atts[0] = substr( $atts[0], 1 );

		return $atts;
	}


	// Shortcode handler for transforming the shortcodes to their final <pre>'s
	function shortcode_callback( $atts, $code = '', $tag = false ) {
		global $post;

		if ( false === $tag || empty($code) )
			return $code;

		// Avoid PHP notices
		if ( !isset($post) )
			$post = null;

		$code = apply_filters( 'syntaxhighlighter_precode', $code, $atts, $tag );

		// Error fixing for [tag="language"]
		if ( isset($atts[0]) ) {
			$atts = $this->attributefix( $atts );
			$atts['language'] = $atts[0];
			unset($atts[0]);
		}

		// Default out all of the available parameters to "false" (easy way to check if they're set or not)
		// Note this isn't the same as if the user passes the string "false" to the shortcode
		$atts = (array) apply_filters( 'syntaxhighlighter_shortcodeatts', shortcode_atts( array(
			'language'       => false,
			'lang'           => false,
			'type'           => false, // language alias
			'autolinks'      => false,
			'classname'      => false,
			'collapse'       => false,
			'firstline'      => false,
			'fontsize'       => false,
			'gutter'         => false,
			'highlight'      => false,
			'htmlscript'     => false,
			'light'          => false,
			'padlinenumbers' => false,
			'smarttabs'      => false,
			'tabsize'        => false,
			'title'          => $this->settings['title'],
			'toolbar'        => false,
			'wraplines'      => false,
		), $atts ) );

		// Check for language shortcode tag such as [php]code[/php]
		if ( isset($this->brushes[$tag]) ) {
			$lang = $tag;
		}

		// If a valid tag is not used, it must be sourcecode/source/code
		else {
			$atts = $this->attributefix( $atts );

			// Check for the "language" attribute
			if ( false !== $atts['language'] )
				$lang = $atts['language'];

			// Check for the "lang" attribute
			elseif ( false !== $atts['lang'] )
				$lang = $atts['lang'];

			// Default to plain text
			else
				$lang = 'text';

			// All language aliases are lowercase
			$lang = strtolower( $lang );

			// Validate passed attribute
			if ( !isset($this->brushes[$lang]) )
				return $code;
		}

		// Switch from the alias to the real brush name (so custom aliases can be used)
		$lang = $this->brushes[$lang];

		// Register this brush as used so it's script will be outputted
		$this->usedbrushes[$lang] = true;

		$params = array();
		$params[] = "brush: $lang;";

		// Fix bug that prevents collapse from working if the toolbar is off or light mode is on
		if ( 'true' == $atts['collapse'] || '1' === $atts['collapse'] || 1 == $this->settings['collapse'] ) {
			$atts['toolbar'] = 'true';
			$atts['light'] = 'false';
		}

		// Parameter renaming (the shortcode API doesn't like parameter names with dashes)
		$rename_map = array(
			'autolinks'      => 'auto-links',
			'classname'      => 'class-name',
			'firstline'      => 'first-line',
			'fontsize'       => 'font-size',
			'htmlscript'     => 'html-script',
			'padlinenumbers' => 'pad-line-numbers',
			'smarttabs'      => 'smart-tabs',
			'tabsize'        => 'tab-size',
			'wraplines'      => 'wrap-lines',
		);

		// Allowed configuration parameters and their type
		// Use the proper names (see above)
		$allowed_atts = (array) apply_filters( 'syntaxhighlighter_allowedatts', array(
			'auto-links'       => 'boolean',
			'class-name'       => 'other',
			'collapse'         => 'boolean',
			'first-line'       => 'integer',
			'font-size'        => 'integer',
			'gutter'           => 'boolean',
			'highlight'        => 'other',
			'html-script'      => 'boolean',
			'light'            => 'boolean',
			'pad-line-numbers' => 'other',
			'smart-tabs'       => 'boolean',
			'tab-size'         => 'integer',
			'title'            => 'other',
			'toolbar'          => 'boolean',
			'wrap-lines'       => 'boolean',
		) );

		$title = '';

		// Sanitize configuration parameters and such
		foreach ( $atts as $key => $value ) {
			$key = strtolower( $key );

			// Put back parameter names that have been renamed for shortcode use
			if ( !empty($rename_map[$key]) )
				$key = $rename_map[$key];

			// This this parameter if it's unknown, not set, or the language which was already handled
			if ( empty($allowed_atts[$key]) || false === $value || in_array( $key, array( 'language', 'lang' ) ) )
				continue;

			// Sanitize values
			switch ( $allowed_atts[$key] ) {
				case 'boolean':
					$value = strtolower( $value );
					if ( 'true' === $value || '1' === $value || 'on' == $value )
						$value = 'true';
					elseif ( 'false' === $value || '0' === $value || 'off' == $value )
						$value = 'false';
					else
						continue 2; // Invalid value, ditch parameter
					break;

				// integer
				case 'integer':
					$value = (int) $value;
					break;
			}

			// Sanitize the "classname" parameter
			if ( 'class-name' == $key )
				$value = trim( preg_replace( '/[^a-zA-Z0-9 _-]/i', '', $value ) );

			// Special sanitization for "pad-line-numbers"
			if ( 'pad-line-numbers' == $key ) {
				$value = strtolower( $value );
				if ( 'true' === $value || '1' === $value )
					$value = 'true';
				elseif ( 'false' === $value || '0' === $value )
					$value = 'false';
				else
					$value = (int) $value;
			}

			// Add % sign to "font-size"
			if ( 'font-size' == $key )
				$value = $value . '%';

			// If "html-script", then include the XML brush as it's needed
			if ( 'html-script' == $key && 'true' == $value )
				$this->usedbrushes['xml'] = true;

			// Sanitize row highlights
			if ( 'highlight' == $key ) {
				if ( false === strpos( $value, ',' ) && false === strpos( $value, '-' ) ) {
					$value = (int) $value;
				} else {
					$lines = explode( ',', $value );
					$highlights = array();

					foreach ( $lines as $line ) {
						// Line range
						if ( false !== strpos( $line, '-' ) ) {
							list( $range_start, $range_end ) = array_map( 'intval', explode( '-', $line ) );
							if ( ! $range_start || ! $range_end || $range_end <= $range_start )
								continue;

							for ( $i = $range_start; $i <= $range_end; $i++ )
								$highlights[] = $i;
						} else {
							$highlights[] = (int) $line;
						}
					}

					natsort( $highlights );

					$value = implode( ',', $highlights );
				}

				if ( empty( $value ) )
					continue;

				// Wrap highlight in [ ]
				$params[] = "$key: [$value];";
				continue;
			}

			// Don't allow HTML in the title parameter
			if ( 'title' == $key ) {
				$value = strip_tags( html_entity_decode( strip_tags( $value ) ) );
			}

			$params[] = "$key: $value;";

			// Set the title variable if the title parameter is set (but not for feeds)
			if ( 'title' == $key && ! is_feed() )
				$title = ' title="' . esc_attr( $value ) . '"';
		}

		$code = ( false === strpos( $code, '<' ) && false === strpos( $code, '>' ) && 2 == $this->get_code_format($post) ) ? strip_tags( $code ) : htmlspecialchars( $code );

		$params[] = 'notranslate'; // For Google, see http://otto42.com/9k

		$params = apply_filters( 'syntaxhighlighter_cssclasses', $params ); // Use this to add additional CSS classes / SH parameters

		return apply_filters( 'syntaxhighlighter_htmlresult', '<pre class="' . esc_attr( implode( ' ', $params ) ) . '"' . $title . '>' . $code . '</pre>' );;
	}


	// Settings page
	function settings_page() { ?>

<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function($) {
		// Confirm pressing of the "Reset to Defaults" button
		$("#syntaxhighlighter-defaults").click(function(){
			var areyousure = confirm("<?php echo esc_js( __( 'Are you sure you want to reset your settings to the defaults?', 'syntaxhighlighter' ) ); ?>");
			if ( true != areyousure ) return false;
		});
<?php if ( !empty( $_GET['defaults'] ) ) : ?>
		$("#message p strong").text("<?php echo esc_js( __( 'Settings reset to defaults.', 'syntaxhighlighter' ) ); ?>");
<?php endif; ?>
	});
// ]]>
</script>

<div class="wrap">
<?php if ( function_exists('screen_icon') ) screen_icon(); ?>
	<h2><?php _e( 'SyntaxHighlighter Settings', 'syntaxhighlighter' ); ?></h2>

	<form method="post" action="options.php">

	<?php settings_fields('syntaxhighlighter_settings'); ?>


	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-shversion"><?php _e( 'Highlighter Version', 'syntaxhighlighter' ); ?></label></th>
			<td>
				<select name="syntaxhighlighter_settings[shversion]" id="syntaxhighlighter-shversion" class="postform">
<?php
					$versions = array(
						3 => __( 'Version 3.x', 'syntaxhighlighter' ),
						2 => __( 'Version 2.x', 'syntaxhighlighter' ),
					);

					foreach ( $versions as $version => $name ) {
						echo '					<option value="' . esc_attr( $version ) . '"' . selected( $this->settings['shversion'], $version, false ) . '>' . esc_html( $name ) . "&nbsp;</option>\n";
					}
?>
				</select><br />
				<?php _e( 'Version 3 allows visitors to easily highlight portions of your code with their mouse (either by dragging or double-clicking) and copy it to their clipboard. No toolbar containing a Flash-based button is required.', 'syntaxhighlighter' ); ?><br />
				<?php _e( 'Version 2 allows for line wrapping, something that version 3 does not do at this time.', 'syntaxhighlighter' ); ?>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-theme"><?php _e( 'Color Theme', 'syntaxhighlighter' ); ?></label></th>
			<td>
				<select name="syntaxhighlighter_settings[theme]" id="syntaxhighlighter-theme" class="postform">
<?php
					foreach ( $this->themes as $theme => $name ) {
						echo '					<option value="' . esc_attr( $theme ) . '"' . selected( $this->settings['theme'], $theme, false ) . '>' . esc_html( $name ) . "&nbsp;</option>\n";
					}
?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Load All Brushes', 'syntaxhighlighter' ); ?></th>
			<td>
				<fieldset>
					<legend class="hidden"><?php _e( 'Load All Brushes', 'syntaxhighlighter' ); ?></legend>
					<label for="syntaxhighlighter-loadallbrushes"><input name="syntaxhighlighter_settings[loadallbrushes]" type="checkbox" id="syntaxhighlighter-loadallbrushes" value="1" <?php checked( $this->settings['loadallbrushes'], 1 ); ?> /> <?php _e( 'Always load all language files (for directly using <code>&lt;pre&gt;</code> tags rather than shortcodes)<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;If left unchecked (default), then language files will only be loaded when needed<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;If unsure, leave this box unchecked', 'syntaxhighlighter' ); ?></label>
				</fieldset>
			</td>
		</tr>
	</table>

	<h3><?php _e( 'Defaults', 'syntaxhighlighter' ); ?></h3>

	<p><?php _e( 'All of the settings below can be configured on a per-code block basis, but you can control the defaults of all code blocks here.', 'syntaxhighlighter' ); ?></p>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e( 'Miscellaneous', 'syntaxhighlighter' ); ?></th>
			<td>
				<fieldset>
					<legend class="hidden"><?php _e( 'Miscellaneous', 'syntaxhighlighter' ); ?></legend>

					<label for="syntaxhighlighter-gutter"><input name="syntaxhighlighter_settings[gutter]" type="checkbox" id="syntaxhighlighter-gutter" value="1" <?php checked( $this->settings['gutter'], 1 ); ?> /> <?php _e( 'Display line numbers', 'syntaxhighlighter' ); ?></label><br />
					<label for="syntaxhighlighter-toolbar"><input name="syntaxhighlighter_settings[toolbar]" type="checkbox" id="syntaxhighlighter-toolbar" value="1" <?php checked( $this->settings['toolbar'], 1 ); ?> /> <?php _e( 'Display the toolbar', 'syntaxhighlighter' ); ?></label><br />
					<label for="syntaxhighlighter-autolinks"><input name="syntaxhighlighter_settings[autolinks]" type="checkbox" id="syntaxhighlighter-autolinks" value="1" <?php checked( $this->settings['autolinks'], 1 ); ?> /> <?php _e( 'Automatically make URLs clickable', 'syntaxhighlighter' ); ?></label><br />
					<label for="syntaxhighlighter-collapse"><input name="syntaxhighlighter_settings[collapse]" type="checkbox" id="syntaxhighlighter-collapse" value="1" <?php checked( $this->settings['collapse'], 1 ); ?> /> <?php _e( 'Collapse code boxes', 'syntaxhighlighter' ); ?></label><br />
					<label for="syntaxhighlighter-light"><input name="syntaxhighlighter_settings[light]" type="checkbox" id="syntaxhighlighter-light" value="1" <?php checked( $this->settings['light'], 1 ); ?> /> <?php _e( 'Use the light display mode, best for single lines of code', 'syntaxhighlighter' ); ?></label><br />
					<label for="syntaxhighlighter-smarttabs"><input name="syntaxhighlighter_settings[smarttabs]" type="checkbox" id="syntaxhighlighter-smarttabs" value="1" <?php checked( $this->settings['smarttabs'], 1 ); ?> /> <?php _e( 'Use smart tabs allowing tabs being used for alignment', 'syntaxhighlighter' ); ?></label><br />
					<label for="syntaxhighlighter-wraplines"><input name="syntaxhighlighter_settings[wraplines]" type="checkbox" id="syntaxhighlighter-wraplines" value="1" <?php checked( $this->settings['wraplines'], 1 ); ?> /> <?php _e( 'Wrap long lines (v2.x only, disabling this will make a scrollbar show instead)', 'syntaxhighlighter' ); ?></label><br />
					<!--<label for="syntaxhighlighter-htmlscript"><input name="syntaxhighlighter_settings[htmlscript]" type="checkbox" id="syntaxhighlighter-htmlscript" value="1" <?php checked( $this->settings['htmlscript'], 1 ); ?> /> <?php _e( 'Enable &quot;HTML script&quot; mode by default (see the bottom of this page for details). Checking this box is not recommended as this mode only works with certain languages.', 'syntaxhighlighter' ); ?></label>-->
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-classname"><?php _e( 'Additional CSS Class(es)', 'syntaxhighlighter' ); ?></label></th>
			<td><input name="syntaxhighlighter_settings[classname]" type="text" id="syntaxhighlighter-classname" value="<?php echo esc_attr( $this->settings['classname'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-firstline"><?php _e( 'Starting Line Number', 'syntaxhighlighter' ); ?></label></th>
			<td><input name="syntaxhighlighter_settings[firstline]" type="text" id="syntaxhighlighter-firstline" value="<?php echo esc_attr( $this->settings['firstline'] ); ?>" class="small-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-padlinenumbers"><?php _e( 'Line Number Padding', 'syntaxhighlighter' ); ?></label></th>
			<td>
				<select name="syntaxhighlighter_settings[padlinenumbers]" id="syntaxhighlighter-padlinenumbers" class="postform">
<?php
					$linepaddings = array(
						'false' => __( 'Off', 'syntaxhighlighter' ),
						'true'  => __( 'Automatic', 'syntaxhighlighter' ),
						1       => 1,
						2       => 2,
						3       => 3,
						4       => 4,
						5       => 5,
					);

					foreach ( $linepaddings as $value => $name ) {
						echo '					<option value="' . esc_attr( $value ) . '"' . selected( $this->settings['padlinenumbers'], $value, false ) . '>' . esc_html( $name ) . "&nbsp;</option>\n";
					}
?>
				</select>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-tabsize"><?php _e( 'Tab Size', 'syntaxhighlighter' ); ?></label></th>
			<td><input name="syntaxhighlighter_settings[tabsize]" type="text" id="syntaxhighlighter-tabsize" value="<?php echo esc_attr( $this->settings['tabsize'] ); ?>" class="small-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-title"><?php _e( 'Title', 'syntaxhighlighter' ); ?></label></th>
			<td>
				<input name="syntaxhighlighter_settings[title]" type="text" id="syntaxhighlighter-title" value="<?php echo esc_attr( $this->settings['title'] ); ?>" class="regular-text" /><br />
				<?php _e( 'Some optional default text to display above each code block or as the clickable text for collapsed code blocks.', 'syntaxhighlighter' ); ?>
			</td>
		</tr>
	</table>

	<p class="submit">
<?php
		if ( function_exists( 'submit_button' ) ) {
			submit_button( null, 'primary', 'syntaxhighlighter-submit', false );
			echo ' ';
			submit_button( __( 'Reset to Defaults', 'syntaxhighlighter' ), 'primary', 'syntaxhighlighter-defaults', false );
		} else {
			echo '<input type="submit" name="syntaxhighlighter-submit" class="button-primary" value="' . __( 'Save Changes') . '" />' . "\n";
			echo '<input type="submit" name="syntaxhighlighter-defaults" id="syntaxhighlighter-defaults" class="button-primary" value="' . __( 'Reset to Defaults', 'syntaxhighlighter' ) . '" />' . "\n";
		}
?>
	</p>

	</form>

	<h3><?php _e( 'Preview', 'syntaxhighlighter' ); ?></h3>

	<p><?php _e( 'Click &quot;Save Changes&quot; to update this preview.', 'syntaxhighlighter' ); ?>

	<?php

		echo '<div';
		if ( ! empty( $GLOBALS['content_width'] ) )
			echo ' style="max-width:' . intval( $GLOBALS['content_width'] ) . 'px"';
		echo '>';

		$title = ( empty( $this->settings['title'] ) && 1 != $this->settings['collapse'] ) ? ' title="Code example: (this example was added using the title parameter)"' : '';

		// Site owners may opt to disable the short tags, i.e. [php]
		$democode = apply_filters( 'syntaxhighlighter_democode', '[sourcecode language="php" htmlscript="true" highlight="12"' . $title . ']<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>PHP Code Example</title>
</head>
<body>
	<h1>' . __( 'PHP Code Example', 'syntaxhighlighter' ) . '</h1>

	<p><?php echo \'' . __( 'Hello World!', 'syntaxhighlighter' ) . '\'; ?></p>

	<p>' . __( 'This line is highlighted.', 'syntaxhighlighter' ) . '</p>

	<div class="foobar">
' . __( '		This	is	an
		example	of	smart
		tabs.', 'syntaxhighlighter' ) . '
	</div>

	<p><a href="http://wordpress.org/">' . __( 'WordPress' ) . '</a></p>
</body>
</html>[/sourcecode]' );

		$this->codeformat = 1;
		echo $this->parse_shortcodes( $democode );
		$this->codeformat = false;

		echo '</div>';
?>

	<h3 style="margin-top:30px"><?php _e( 'Shortcode Parameters', 'syntaxhighlighter' ); ?></h3>

	<p><?php printf( __( 'These are the parameters you can pass to the shortcode and what they do. For the booleans (i.e. on/off), pass %1$s/%2$s or %3$s/%4$s.', 'syntaxhighlighter' ), '<code>true</code>', '<code>1</code>', '<code>false</code>', '<code>0</code>' ); ?></p>

	<ul class="ul-disc">
		<li><?php printf( _x( '%1$s or %2$s &#8212; The language syntax to highlight with. You can alternately just use that as the tag, such as <code>[php]code[/php]</code>. <a href="%3$s">Click here</a> for a list of valid tags (under &quot;aliases&quot;).', 'language parameter', 'syntaxhighlighter' ), '<code>lang</code>', '<code>language</code>', 'http://alexgorbatchev.com/wiki/SyntaxHighlighter:Brushes' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Toggle automatic URL linking.', 'autolinks parameter', 'syntaxhighlighter' ), '<code>autolinks</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Add an additional CSS class to the code box.', 'classname parameter', 'syntaxhighlighter' ), '<code>classname</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Toggle collapsing the code box by default, requiring a click to expand it. Good for large code posts.', 'collapse parameter', 'syntaxhighlighter' ), '<code>collapse</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; An interger specifying what number the first line should be (for the line numbering).', 'firstline parameter', 'syntaxhighlighter' ), '<code>firstline</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Toggle the left-side line numbering.', 'gutter parameter', 'syntaxhighlighter' ), '<code>gutter</code>' ); ?></li>
		<li><?php printf( _x( '%1$s &#8212; A comma-separated list of line numbers to highlight. You can also specify a range. Example: %2$s', 'highlight parameter', 'syntaxhighlighter' ), '<code>highlight</code>', '<code>2,5-10,12</code>' ); ?></li>
		<li><?php printf( _x( "%s &#8212; Toggle highlighting any extra HTML/XML. Good for when you're mixing HTML/XML with another language, such as having PHP inside an HTML web page. The above preview has it enabled for example. This only works with certain languages.", 'htmlscript parameter', 'syntaxhighlighter' ), '<code>htmlscript</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Toggle light mode which disables the gutter and toolbar all at once.', 'light parameter', 'syntaxhighlighter' ), '<code>light</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Controls line number padding. Valid values are <code>false</code> (no padding), <code>true</code> (automatic padding), or an integer (forced padding).', 'padlinenumbers parameter', 'syntaxhighlighter' ), '<code>padlinenumbers</code>' ); ?></li>
		<li><?php printf( _x( '%1$s (v3 only) &#8212; Sets some text to show up before the code. Very useful when combined with the %2$s parameter.', 'title parameter', 'syntaxhighlighter' ), '<code>title</code>', '<code>collapse</code>' ); ?></li>
		<li><?php printf( _x( '%s &#8212; Toggle the toolbar (buttons in v2, the about question mark in v3)', 'toolbar parameter', 'syntaxhighlighter' ), '<code>toolbar</code>' ); ?></li>
		<li><?php printf( _x( '%s (v2 only) &#8212; Toggle line wrapping.', 'wraplines parameter', 'syntaxhighlighter'), '<code>wraplines</code>' ); ?></li>
	</ul>

	<p><?php _e( 'Some example shortcodes:', 'syntaxhighlighter' ); ?></p>

	<ul class="ul-disc">
		<li><code>[php]<?php _e( 'your code here', 'syntaxhighlighter' ); ?>[/php]</code></li>
		<li><code>[css autolinks=&quot;false&quot; classname=&quot;myclass&quot; collapse=&quot;false&quot; firstline=&quot;1&quot; gutter=&quot;true&quot; highlight=&quot;1-3,6,9&quot; htmlscript=&quot;false&quot; light=&quot;false&quot; padlinenumbers=&quot;false&quot; smarttabs=&quot;true&quot; tabsize=&quot;4&quot; toolbar=&quot;true&quot; title=&quot;<?php _e( 'example-filename.php', 'syntaxhighlighter' ); ?>&quot;]<?php _e( 'your code here', 'syntaxhighlighter' ); ?>[/css]</code></li>
		<li><code>[code lang=&quot;js&quot;]<?php _e( 'your code here', 'syntaxhighlighter' ); ?>[/code]</code></li>
		<li><code>[sourcecode language=&quot;plain&quot;]<?php _e( 'your code here', 'syntaxhighlighter' ); ?>[/sourcecode]</code></li>
	</ul>

<?php $this->maybe_output_scripts(); ?>

</div>

<?php
	}


	// Validate the settings sent from the settings page
	function validate_settings( $settings ) {
		if ( !empty($_POST['syntaxhighlighter-defaults']) ) {
			$settings = $this->defaultsettings;
			$_REQUEST['_wp_http_referer'] = add_query_arg( 'defaults', 'true', $_REQUEST['_wp_http_referer'] );
		} else {
			$settings['shversion']      = ( ! empty($settings['shversion']) && 2 == $settings['shversion'] ) ? 2 : 3;

			$settings['theme']          = ( ! empty($settings['theme']) && isset($this->themes[$settings['theme']]) ) ? strtolower($settings['theme']) : $this->defaultsettings['theme'];

			$settings['loadallbrushes'] = ( ! empty($settings['loadallbrushes']) ) ? 1 : 0;
			$settings['autolinks']      = ( ! empty($settings['autolinks']) )      ? 1 : 0;
			$settings['collapse']       = ( ! empty($settings['collapse']) )       ? 1 : 0;
			$settings['gutter']         = ( ! empty($settings['gutter']) )         ? 1 : 0;
			$settings['light']          = ( ! empty($settings['light']) )          ? 1 : 0;
			$settings['smarttabs']      = ( ! empty($settings['smarttabs']) )      ? 1 : 0;
			$settings['toolbar']        = ( ! empty($settings['toolbar']) )        ? 1 : 0; // May be overridden below
			$settings['wraplines']      = ( ! empty($settings['wraplines']) )      ? 1 : 0; // 2.x only for now

			// If the version changed, then force change the toolbar version setting
			if ( $settings['shversion'] != $this->settings['shversion'] ) {
				$settings['toolbar'] = ( 2 == $settings['shversion'] ) ? 1 : 0;
			}

			if ( 'true' != $settings['padlinenumbers'] && 'false' != $settings['padlinenumbers'] )
				$settings['padlinenumbers'] = (int) $settings['padlinenumbers'];

			$settings['classname']      = ( !empty($settings['classname']) )       ? preg_replace( '/[^ A-Za-z0-9_-]*/', '', $settings['classname'] ) : '';
			$settings['firstline']      = (int) ( ( !empty($settings['firstline']) ) ? $settings['firstline'] : $this->defaultsettings['firstline'] );
			$settings['tabsize']        = (int) ( ( !empty($settings['tabsize']) )   ? $settings['tabsize']   : $this->defaultsettings['tabsize'] );
		}

		return $settings;
	}
}


// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'SyntaxHighlighter', 5 );
function SyntaxHighlighter() {
	global $SyntaxHighlighter;
	$SyntaxHighlighter = new SyntaxHighlighter();
}
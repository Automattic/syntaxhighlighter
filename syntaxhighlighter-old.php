<?php

/**
 * This contains a development version of the plugin where I was working to
 * update and refresh the plugin before I decided to completely rewrite it
 * from scratch. This version is super hacky and not fully functioning.
 *
 * I want to keep it in this tree so that if/when I move code from it
 * to the new file, the revision history tracks it.
 */

class SyntaxHighlighter_Old {

	// Please don't directly modify these, use the provided filters instead.
	public $pluginver            = '4.0.0';  // Plugin version
	public $agshver              = false;    // Alex Gorbatchev's SyntaxHighlighter version (dynamically set below due to v2 vs v3)
	public $agsh_folder          = 'syntaxhighlighter3'; // Toggled based on $agshver

	public $settings             = array();  // Contains the user's settings
	public $defaultsettings      = array();  // Contains the default settings

	public $plugin_brushes       = array();  // These brushes come with my plugin, array of aliases => brushes
	public $brushes              = array();  // All registered brushes, array of aliases => brushes
	public $shortcodes           = array();  // Array of shortcodes to use
	public $themes               = array();  // Array of themes

	public $core_theme_url       = false;    // Either false if core CSS is not needed, or the URL to the file
	public $user_theme_url       = false;    // The URL to the theme that the user has chosen

	public $usedbrushes          = array();  // Stores used brushes so we know what to output
	public $encoded              = false;    // Used to mark that a character encode took place
	public $codeformat           = false;    // If set, SyntaxHighlighter::get_code_format() will return this value
	public $content_save_pre_ran = false;    // It's possible for the "content_save_pre" filter to run multiple times, so keep track

	// Initalize the plugin by registering the hooks
	function __construct() {
		global $wp_version;

		// Requires WordPress 3.3+
		if ( ! version_compare( $wp_version, '3.3', '>=' ) ) {
			return;
		}

		// Outputting SyntaxHighlighter's JS and CSS
		add_action( 'wp_head',                            array( $this, 'output_header_placeholder' ),                     15 );
		add_action( 'admin_head',                         array( $this, 'output_header_placeholder' ),                     15 ); // For comments
		add_action( 'wp_footer',                          array( $this, 'maybe_output_scripts' ),                          15 );
		add_action( 'admin_footer',                       array( $this, 'maybe_output_scripts' ),                          15 ); // For comments

		// Admin hooks
		add_filter( 'mce_external_plugins',               array( $this, 'add_tinymce_plugin' ) );
		add_filter( 'tiny_mce_version',                   array( $this, 'break_tinymce_cache' ) );
		add_filter( 'save_post',                          array( $this, 'mark_as_encoded' ),                               10, 2 );
		add_filter( 'plugin_action_links',                array( $this, 'settings_link' ),                                 10, 2 );


		// Create list of brush aliases and map them to their real brushes
		$this->plugin_brushes = array(
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
		);

		// Let people register additional brushes
		// See http://www.viper007bond.com/wordpress-plugins/syntaxhighlighter/adding-a-new-brush-language/
		$this->brushes = (array) apply_filters( 'syntaxhighlighter_brushes', $this->plugin_brushes );

		// Todo -- make this on-demand
		$this->additional_setup();
	}


	public function additional_setup() {
		// Register theme stylesheets
		wp_register_style(  'syntaxhighlighter-core',             plugins_url( $this->agsh_folder . '/styles/shCore.css',            __FILE__ ), array(),                           $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-default',    plugins_url( $this->agsh_folder . '/styles/shThemeDefault.css',    __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-django',     plugins_url( $this->agsh_folder . '/styles/shThemeDjango.css',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-eclipse',    plugins_url( $this->agsh_folder . '/styles/shThemeEclipse.css',    __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-emacs',      plugins_url( $this->agsh_folder . '/styles/shThemeEmacs.css',      __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-fadetogrey', plugins_url( $this->agsh_folder . '/styles/shThemeFadeToGrey.css', __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-midnight',   plugins_url( $this->agsh_folder . '/styles/shThemeMidnight.css',   __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-rdark',      plugins_url( $this->agsh_folder . '/styles/shThemeRDark.css',      __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );

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


		## We need to do different things based on what version of the highlighting script the user wants

		// Legacy v2 of the highlighting script
		if ( 2 == $this->settings['shversion'] ) {
			$this->agshver = '2.1.364';

			// Register brush scripts
			wp_register_script( 'syntaxhighlighter-core',             plugins_url( 'syntaxhighlighter2/scripts/shCore.js',            __FILE__ ), array(),                           $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-as3',        plugins_url( 'syntaxhighlighter2/scripts/shBrushAS3.js',        __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-bash',       plugins_url( 'syntaxhighlighter2/scripts/shBrushBash.js',       __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-coldfusion', plugins_url( 'syntaxhighlighter2/scripts/shBrushColdFusion.js', __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-cpp',        plugins_url( 'syntaxhighlighter2/scripts/shBrushCpp.js',        __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-csharp',     plugins_url( 'syntaxhighlighter2/scripts/shBrushCSharp.js',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-css',        plugins_url( 'syntaxhighlighter2/scripts/shBrushCss.js',        __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-delphi',     plugins_url( 'syntaxhighlighter2/scripts/shBrushDelphi.js',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-diff',       plugins_url( 'syntaxhighlighter2/scripts/shBrushDiff.js',       __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-erlang',     plugins_url( 'syntaxhighlighter2/scripts/shBrushErlang.js',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-groovy',     plugins_url( 'syntaxhighlighter2/scripts/shBrushGroovy.js',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-java',       plugins_url( 'syntaxhighlighter2/scripts/shBrushJava.js',       __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-javafx',     plugins_url( 'syntaxhighlighter2/scripts/shBrushJavaFX.js',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-jscript',    plugins_url( 'syntaxhighlighter2/scripts/shBrushJScript.js',    __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-perl',       plugins_url( 'syntaxhighlighter2/scripts/shBrushPerl.js',       __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-php',        plugins_url( 'syntaxhighlighter2/scripts/shBrushPhp.js',        __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-plain',      plugins_url( 'syntaxhighlighter2/scripts/shBrushPlain.js',      __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-powershell', plugins_url( 'syntaxhighlighter2/scripts/shBrushPowerShell.js', __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-python',     plugins_url( 'syntaxhighlighter2/scripts/shBrushPython.js',     __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-ruby',       plugins_url( 'syntaxhighlighter2/scripts/shBrushRuby.js',       __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-scala',      plugins_url( 'syntaxhighlighter2/scripts/shBrushScala.js',      __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-sql',        plugins_url( 'syntaxhighlighter2/scripts/shBrushSql.js',        __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-vb',         plugins_url( 'syntaxhighlighter2/scripts/shBrushVb.js',         __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );
			wp_register_script( 'syntaxhighlighter-brush-xml',        plugins_url( 'syntaxhighlighter2/scripts/shBrushXml.js',        __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );

			// Register some popular and bundled third-party brushes
			wp_register_script( 'syntaxhighlighter-brush-clojure',    plugins_url( 'third-party-brushes/shBrushClojure.js',           __FILE__ ), array( 'syntaxhighlighter-core' ), '20090602'     );
			wp_register_script( 'syntaxhighlighter-brush-fsharp',     plugins_url( 'third-party-brushes/shBrushFSharp.js',            __FILE__ ), array( 'syntaxhighlighter-core' ), '20091003'     );
			wp_register_script( 'syntaxhighlighter-brush-latex',      plugins_url( 'third-party-brushes/shBrushLatex.js',             __FILE__ ), array( 'syntaxhighlighter-core' ), '20090613'     );
			wp_register_script( 'syntaxhighlighter-brush-matlabkey',  plugins_url( 'third-party-brushes/shBrushMatlabKey.js',         __FILE__ ), array( 'syntaxhighlighter-core' ), '20091209'     );
			wp_register_script( 'syntaxhighlighter-brush-objc',       plugins_url( 'third-party-brushes/shBrushObjC.js',              __FILE__ ), array( 'syntaxhighlighter-core' ), '20091207'     );
			wp_register_script( 'syntaxhighlighter-brush-r',          plugins_url( 'third-party-brushes/shBrushR.js',                 __FILE__ ), array( 'syntaxhighlighter-core' ), '20100919'     );
		}

		// The newer v3 of the highlighting script
		else {
			$this->agshver = '3.0.83c';

			// This theme is SH3 only
			wp_register_style(  'syntaxhighlighter-theme-mdultra',    plugins_url( $this->agsh_folder . '/styles/shThemeMDUltra.css', __FILE__ ), array( 'syntaxhighlighter-core' ), $this->agshver );

			// Add MDUltra after RDark
			$theme_keys = array_keys( $this->themes );
			$rdark_pos = (int) array_search( 'rdark', $theme_keys ) + 1;
			$this->themes = array_merge(
				array_slice( $this->themes, 0, $rdark_pos, true ),
				array( 'mdultra' => __( 'MDUltra', 'syntaxhighlighter' ) ),
				array_slice( $this->themes, $rdark_pos, null, true )
			);


			# Translation strings
			$strings = (object) array(); // Faster than "new stdClass();" actually

			if ( 'expand source' !== $string = __( '+ expand source', 'syntaxhighlighter' ) ) {
				$strings->expandsource = $string;
			}

			if ( '?' !== $string = __( '?', 'syntaxhighlighter' ) ) {
				$strings->help = $string;
			}

			if ( 'SyntaxHighlighter\n\n' !== $string = __( 'SyntaxHighlighter\n\n', 'syntaxhighlighter' ) ) {
				$strings->alert = $string;
			}

			if ( "Can't find brush for: " !== $string = __( "Can't find brush for: ", 'syntaxhighlighter' ) ) {
				$strings->nobrush = $string;
			}

			if ( "Brush wasn't configured for html-script option: " !== $string = __( "Brush wasn't configured for html-script option: ", 'syntaxhighlighter' ) ) {
				$strings->brushnothtmlscript = $string;
			}

			# SyntaxHighlighter default settings
			$defaults = (object) array();

			if ( 1 != $this->settings['autolinks'] ) {
				$defaults->autolinks = false;
			}

			if ( ! empty( $this->settings['classname'] ) ) {
				$defaults->classname = $this->settings['classname'];
			}

			if ( 1 == $this->settings['collapse'] ) {
				$defaults->collapse = true;
			}

			if ( 1 != $this->settings['firstline'] ) {
				$defaults->firstline = (int) $this->settings['firstline'];
			}

			if ( 1 != $this->settings['gutter'] ) {
				$defaults->gutter = false;
			}

			if ( 1 == $this->settings['light'] ) {
				$defaults->light = true;
			}

			switch ( $this->settings['padlinenumbers'] ) {
				case 'true':
					$defaults->padlinenumbers = true;
					break;
				case 'false';
					break;
				default;
					$defaults->padlinenumbers = (int) $this->settings['padlinenumbers'];
			}

			if ( 1 != $this->settings['smarttabs'] ) {
				$defaults->smarttabs = false;
			}

			if ( 4 != $this->settings['tabsize'] ) {
				$defaults->tabsize = (int) $this->settings['tabsize'];
			}

			if ( 1 != $this->settings['toolbar'] ) {
				$defaults->toolbar = false;
			}


			// This is an all-in-one loader for SyntaxHighlighter
			wp_register_script( 'syntaxhighlighter-autoloader', plugins_url( 'sh3-loader.js', __FILE__ ), array(), $this->pluginver, true );


			// Are there any brushes added by other plugins that we need to tell the autoloader about?
			$autoloader_extra_brushes = array();
			if ( $this->brushes !== $this->plugin_brushes ) {
				$extra_aliases = array_diff_assoc( $this->brushes, $this->plugin_brushes );

				// First we need to get it into "brush" => array( "alias1", "alias2" )
				$extra_brushes = array();
				foreach ( $extra_aliases as $alias => $brush ) {
					$extra_brushes[ $brush ][] = $alias;
				}

				// Then we need to transform it to the format that the autoloader wants, and grab the brush script  URL
				foreach ( $extra_brushes as $brush => $aliases ) {
					$brush_script_url = $this->get_script_url( 'syntaxhighlighter-brush-' . $brush );

					if ( $brush_script_url ) {
						$autoloader_extra_brushes[] = array_merge( $aliases, array( $brush_script_url ) );
					}
				}
			}

			$this->set_theme_urls();

			// Pass some dynamic values to the above JavaScript file
			wp_localize_script(
				'syntaxhighlighter-autoloader',
				'SyntaxHighlighterEvolved',
				array(
					'sh_version'         => $this->agshver,
					'plugin_url'         => plugins_url( '', __FILE__ ),
					'core_theme_url'     => $this->core_theme_url,
					'user_theme_url'     => $this->user_theme_url,
					'strings'            => $strings,
					'defaults'           => $defaults,
					'additional_brushes' => $autoloader_extra_brushes,
				)
			);
		}
	}


	// Figure out the URLs are to the themes and store them in class variables
	public function set_theme_urls() {
		global $wp_styles;

		if ( 'none' == $this->settings['theme'] ) {
			return;
		}

		$theme = ( ! empty( $this->themes[ $this->settings['theme'] ] ) ) ? strtolower( $this->settings['theme'] ) : $this->defaultsettings['theme'];

		// Is it registered with WordPress?
		$theme = 'syntaxhighlighter-theme-' . $theme;
		if ( ! empty( $wp_styles ) && ! empty( $wp_styles->registered ) && ! empty( $wp_styles->registered[ $theme ] ) && ! empty( $wp_styles->registered[ $theme ]->src ) ) {
			$this->user_theme_url = add_query_arg( 'ver', $this->agshver, $wp_styles->registered[ $theme ]->src );

			// Does the user's theme require the core CSS?
			if ( is_array( $wp_styles->registered[ $theme ]->deps ) && in_array( 'syntaxhighlighter-core', $wp_styles->registered[ $theme ]->deps ) ) {

				if ( ! empty( $wp_styles ) && ! empty( $wp_styles->registered ) && ! empty( $wp_styles->registered['syntaxhighlighter-core'] ) && ! empty( $wp_styles->registered['syntaxhighlighter-core']->src ) ) {
					$this->core_theme_url = add_query_arg( 'ver', $this->agshver, $wp_styles->registered['syntaxhighlighter-core']->src );
				}
			}
		}
	}

	// Given a script slug, return the script's URL
	public function get_script_url( $script ) {
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && ! empty( $wp_scripts->registered ) && ! empty( $wp_scripts->registered[ $script ] ) && ! empty( $wp_scripts->registered[ $script ]->src ) ) {
			return $wp_scripts->registered[ $script ]->src;
		}

		return false;
	}

	// Add the custom TinyMCE plugin which wraps plugin shortcodes in <pre> in TinyMCE
	public function add_tinymce_plugin( $plugins ) {
		global $tinymce_version;

		// Pass an array of this plugin's shortcodes to the TinyMCE plugin
		add_action( 'admin_print_footer_scripts', array( $this, 'output_shortcodes_for_tinymce' ), 9 );

		if ( substr( $tinymce_version, 0, 1 ) < 4 ) {
			$plugins['syntaxhighlighter'] = add_query_arg( 'ver', $this->pluginver, plugins_url( 'editor/syntaxhighlighter_mce-3.js', __FILE__ ) );
		} else {
			$plugins['syntaxhighlighter'] = add_query_arg( 'ver', $this->pluginver, plugins_url( 'editor/syntaxhighlighter_mce-4.js', __FILE__ ) );
			wp_enqueue_script( 'syntaxhighlighter-editor', plugins_url( 'editor/editor.js', __FILE__ ), array( 'jquery' ), $this->pluginver, true );
		}

		return $plugins;
	}


	// Add a "Settings" link to the plugins page
	public function settings_link( $links, $file ) {
		static $this_plugin;

		if( empty($this_plugin) )
			$this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin )
			$links[] = '<a href="' . admin_url( 'options-general.php?page=syntaxhighlighter' ) . '">' . __( 'Settings', 'syntaxhighlighter' ) . '</a>';

		return $links;
	}


	// Output list of shortcode tags for the TinyMCE plugin
	public function output_shortcodes_for_tinymce() {
		$shortcodes = array();

		foreach ( $this->shortcodes as $shortcode )
			$shortcodes[] = preg_quote( $shortcode );

		echo "<script type='text/javascript'>\n";
		echo "	var syntaxHLcodes = '" . implode( '|', $shortcodes ) . "';\n";
		echo "</script>\n";
	}


	// Adds this plugin's shortcodes to the list of shortcodes that wptexturize() shouldn't modify
	public function no_texturize_shortcodes( $exempted_shortcodes = array() ) {
		foreach ( $this->shortcodes as $shortcode ) {
			$exempted_shortcodes[] = $shortcode;
		}

		return $exempted_shortcodes;
	}

	// The main filter for the post contents. The regular shortcode filter can't be used as it's post-wpautop().
	public function parse_shortcodes( $content ) {
		return $this->shortcode_hack( $content, array( $this, 'shortcode_callback' ) );
	}


	// HTML entity encode the contents of shortcodes
	public function encode_shortcode_contents( $content ) {
		return $this->shortcode_hack( $content, array( $this, 'encode_shortcode_contents_callback' ) );
	}


	// HTML entity encode the contents of shortcodes. Expects slashed content.
	public function encode_shortcode_contents_slashed( $content ) {
		return addslashes( $this->encode_shortcode_contents( stripslashes( $content ) ) );
	}


	// HTML entity encode the contents of shortcodes. Expects slashed content. Aborts if AJAX.
	public function encode_shortcode_contents_slashed_noquickedit( $content ) {

		// In certain weird circumstances, the content gets run through "content_save_pre" twice
		// Keep track and don't allow this filter to be run twice
		// I couldn't easily figure out why this happens and didn't bother looking into it further as this works fine
		if ( true == $this->content_save_pre_ran )
			return $content;
		$this->content_save_pre_ran = true;

		// Post quick edits aren't decoded for display, so we don't need to encode them (again)
		if ( ! empty( $_POST ) && !empty( $_POST['action'] ) && 'inline-save' == $_POST['action'] )
			return $content;

		return $this->encode_shortcode_contents_slashed( $content );
	}


	// HTML entity decode the contents of shortcodes
	public function decode_shortcode_contents( $content ) {
		return $this->shortcode_hack( $content, array( $this, 'decode_shortcode_contents_callback' ) );
	}


	// The callback function for SyntaxHighlighter::encode_shortcode_contents()
	public function encode_shortcode_contents_callback( $atts, $code = '', $tag = false ) {
		$this->encoded = true;
		$code = str_replace( array_keys($this->specialchars), array_values($this->specialchars), htmlspecialchars( $code ) );
		return '[' . $tag . $this->atts2string( $atts ) . "]{$code}[/$tag]";
	}


	// The callback function for SyntaxHighlighter::decode_shortcode_contents()
	// Shortcode attribute values need to not be quoted with TinyMCE disabled for some reason (weird bug)
	public function decode_shortcode_contents_callback( $atts, $code = '', $tag = false ) {
		$quotes = ( user_can_richedit() ) ? true : false;
		$code = str_replace(  array_values($this->specialchars), array_keys($this->specialchars), htmlspecialchars_decode( $code ) );
		return '[' . $tag . $this->atts2string( $atts, $quotes ) . "]{$code}[/$tag]";
	}


	// Dynamically format the post content for the edit form
	public function the_editor_content( $content ) {
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
	public function widget_text_save( $instance, $new_instance, $old_instance, $widgetclass ) {
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
	public function widget_text_form( $instance, $widgetclass ) {
		if ( 'text' == $widgetclass->id_base && !empty($instance['syntaxhighlighter_encoded']) ) {
			$instance['text'] = $this->shortcode_hack( $instance['text'], array( $this, 'decode_shortcode_contents_callback' ) );
		}

		return $instance;
	}


	// Run SyntaxHighlighter::parse_shortcodes() on the contents of a text widget
	public function widget_text_output( $content, $instance = false ) {
		$this->codeformat = ( false === $instance || empty($instance['syntaxhighlighter_encoded']) ) ? 1 : 2;
		$content = $this->parse_shortcodes( $content );
		$this->codeformat = false;

		return $content;
	}


	// Run SyntaxHighlighter::parse_shortcodes() on the contents of a comment
	public function parse_shortcodes_comment( $content ) {
		$this->codeformat = 2;
		$content = $this->parse_shortcodes( $content );
		$this->codeformat = false;

		return $content;
	}


	// This function determines what version of SyntaxHighlighter was used when the post was written
	// This is because the code was stored differently for different versions of SyntaxHighlighter
	public function get_code_format( $post ) {
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
	public function mark_as_encoded( $post_ID, $post ) {
		if ( false == $this->encoded || 'revision' == $post->post_type )
			return;

		delete_post_meta( $post_ID, 'syntaxhighlighter_encoded' ); // Previously used
		add_post_meta( $post_ID, '_syntaxhighlighter_encoded', true, true );
	}


	// Transforms an attributes array into a 'key="value"' format (i.e. reverses the process)
	public function atts2string( $atts, $quotes = true ) {
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
	public function js_escape_singlequotes( $string ) {
		return str_replace( "'", "\'", $string );
	}


	// Output an anchor in the header for the Javascript to use.
	// In the <head>, we don't know if we'll need this plugin's CSS and JavaScript yet but we will in the footer.
	public function output_header_placeholder() {
		echo '<style id="syntaxhighlighteranchor"></style>' . "\n";
	}


	// Output any needed scripts. This is meant for the footer.
	public function maybe_output_scripts() {

		wp_print_scripts( array( 'syntaxhighlighter-autoloader' ) );


		return;


		global $wp_styles;

		/*
		if ( 1 == $this->settings['loadallbrushes'] )
			$this->usedbrushes = array_flip( array_values( $this->brushes ) );

		if ( empty($this->usedbrushes) )
			return;

		$scripts = array();
		foreach ( $this->usedbrushes as $brush => $unused )
			$scripts[] = 'syntaxhighlighter-brush-' . strtolower( $brush );

		wp_print_scripts( $scripts );
		*/

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
		</script>
	<?php
	}

	public function settings_page() { ?>

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
}

?>
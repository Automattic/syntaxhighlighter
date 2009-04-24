<?php /*

**************************************************************************

Plugin Name:  SyntaxHighlighter Evolved
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/syntaxhighlighter/
Version:      2.0.1
Description:  Easily post syntax-highlighted code to your site without having to modify the code at all. Uses Alex Gorbatchev's <a href="http://alexgorbatchev.com/wiki/SyntaxHighlighter">SyntaxHighlighter</a> and code by <a href="http://wordpress.com/">Andrew Ozz of Automattic</a>.
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Thanks to:

* Alex Gorbatchev for writing such an awesome Javascript-powered synatax
  highlighter script

* Andrew Ozz of Automattic for writing the TinyMCE plugin

**************************************************************************/

class SyntaxHighlighter {
	// All of these variables are private. Filters are provided for things that can be modified.
	var $pluginver       = '2.0.1';   // Plugin version
	var $agshver         = '2.0.296'; // Alex Gorbatchev's SyntaxHighlighter version
	var $settings        = array();   // Contains the user's settings
	var $defaultsettings = array();   // Contains the default settings
	var $brushes         = array();   // Array of aliases => brushes
	var $themes          = array();   // Array of themes
	var $usedbrushes     = array();   // Stores used brushes so we know what to output
	var $encoded         = false;     // Used to mark that a character encode took place

	// Initalize the plugin by registering the hooks
	function __construct() {
		// Check WordPress version
		if ( !function_exists( 'plugins_url' ) ) return;

		// Load localization domain
		load_plugin_textdomain( 'syntaxhighlighter', FALSE, '/syntaxhighlighter/localization' );

		// Register brush scripts
		wp_register_script( 'syntaxhighlighter-core',             plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shCore.js'),            array(),                         $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-bash',       plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushBash.js'),       array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-csharp',     plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushCSharp.js'),     array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-cpp',        plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushCpp.js'),        array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-css',        plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushCss.js'),        array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-delphi',     plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushDelphi.js'),     array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-diff',       plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushDiff.js'),       array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-groovy',     plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushGroovy.js'),     array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-jscript',    plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushJScript.js'),    array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-java',       plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushJava.js'),       array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-perl',       plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushPerl.js'),       array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-php',        plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushPhp.js'),        array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-plain',      plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushPlain.js'),      array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-python',     plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushPython.js'),     array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-ruby',       plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushRuby.js'),       array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-scala',      plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushScala.js'),      array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-sql',        plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushSql.js'),        array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-vb',         plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushVb.js'),         array('syntaxhighlighter-core'), $this->agshver );
		wp_register_script( 'syntaxhighlighter-brush-xml',        plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/shBrushXml.js'),        array('syntaxhighlighter-core'), $this->agshver );

		// Register theme stylesheets
		wp_register_style(  'syntaxhighlighter-core',             plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shCore.css'),            array(),                         $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-default',    plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shThemeDefault.css'),    array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-django',     plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shThemeDjango.css'),     array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-emacs',      plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shThemeEmacs.css'),      array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-fadetogrey', plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shThemeFadeToGrey.css'), array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-midnight',   plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shThemeMidnight.css'),   array('syntaxhighlighter-core'), $this->agshver );
		wp_register_style(  'syntaxhighlighter-theme-rdark',      plugins_url('syntaxhighlighter/syntaxhighlighter/styles/shThemeRDark.css'),      array('syntaxhighlighter-core'), $this->agshver );

		// Register hooks
		add_action( 'admin_menu',                   array(&$this, 'register_settings_page') );
		add_action( 'admin_post_syntaxhighlighter', array(&$this, 'save_settings') );
		add_action( 'admin_head',                   array(&$this, 'admin_head') );
		add_filter( 'the_content',                  array(&$this, 'parse_shortcodes'),          9 );
		add_filter( 'widget_text',                  array(&$this, 'parse_shortcodes'),          9 );
		add_action( 'wp_footer',                    array(&$this, 'maybe_output_scripts'),      15 );
		add_filter( 'mce_external_plugins',         array(&$this, 'add_tinymce_plugin') );
		add_filter( 'tiny_mce_version',             array(&$this, 'break_tinymce_cache') );
		add_filter( 'the_editor_content',           array(&$this, 'decode_shortcode_contents'), 1 );
		add_filter( 'content_save_pre',             array(&$this, 'encode_shortcode_contents'), 1 );
		add_filter( 'save_post',                    array(&$this, 'mark_as_encoded'),           10, 2 );
		add_filter( 'plugin_action_links',          array(&$this, 'plugin_action_links'),       10, 2 );

		// Create list of brush aliases and map them to their real brushes
		$this->brushes = apply_filters( 'syntaxhighlighter_brushes', array(
			'bash'       => 'bash',
			'shell'      => 'bash',
			'c-sharp'    => 'csharp',
			'csharp'     => 'csharp',
			'cpp'        => 'cpp',
			'c'          => 'cpp',
			'css'        => 'css',
			'delphi'     => 'delphi',
			'pas'        => 'delphi',
			'pascal'     => 'delphi',
			'diff'       => 'diff',
			'patch'      => 'diff',
			'groovy'     => 'groovy',
			'js'         => 'jscript',
			'jscript'    => 'jscript',
			'javascript' => 'jscript',
			'java'       => 'java',
			'perl'       => 'perl',
			'pl'         => 'perl',
			'php'        => 'php',
			'plain'      => 'plain',
			'text'       => 'plain',
			'py'         => 'python',
			'python'     => 'python',
			'rails'      => 'ruby',
			'ror'        => 'ruby',
			'ruby'       => 'ruby',
			'scala'      => 'scala',
			'sql'        => 'sql',
			'vb'         => 'vb',
			'vbnet'      => 'vb',
			'xml'        => 'xml',
			'xhtml'      => 'xml',
			'xslt'       => 'xml',
			'html'       => 'xml',
			'xhtml'      => 'xml',
		) );

		// Create list of themes and their human readable names
		// Plugins can add to this list as long as they also register a style with the handle "syntaxhighlighter-theme-THEMENAMEHERE"
		$this->themes = apply_filters( 'syntaxhighlighter_themes', array(
			'default'    => __( 'Default',      'syntaxhighlighter' ),
			'django'     => __( 'Django',       'syntaxhighlighter' ),
			'emacs'      => __( 'Emacs',        'syntaxhighlighter' ),
			'fadetogrey' => __( 'Fade to Grey', 'syntaxhighlighter' ),
			'midnight'   => __( 'Midnight',     'syntaxhighlighter' ),
			'rdark'      => __( 'RDark',        'syntaxhighlighter' ),
			'none'       => __( '[None]',       'syntaxhighlighter' ),
		) );

		// Create array of default settings (you can use the filter to modify these)
		$this->defaultsettings = apply_filters( 'syntaxhighlighter_defaultsettings', array(
			'theme'      => 'default',
			'autolinks'  => 1,
			'classname'  => '',
			'collapse'   => 0,
			'firstline'  => 1,
			'fontsize'   => 100,
			'gutter'     => 1,
			'light'      => 0,
			'ruler'      => 0,
			'smarttabs'  => 1,
			'tabsize'    => 4,
			'toolbar'    => 1,
		) );

		// Setup the settings by using the default as a base and then adding in any changed values
		// This allows settings arrays from old versions to be used even though they are missing values
		$usersettings = (array) get_option('syntaxhighlighter_settings');
		$this->settings = $this->defaultsettings;
		if ( $usersettings !== $this->defaultsettings ) {
			foreach ( (array) $usersettings as $key1 => $value1 ) {
				if ( is_array($value1) ) {
					foreach ( $value1 as $key2 => $value2 ) {
						$this->settings[$key1][$key2] = $value2;
					}
				} else {
					$this->settings[$key1] = $value1;
				}
			}
		}

		// Load the user's selected theme
		if ( 'none' == $this->settings['theme'] )
			wp_enqueue_style( 'syntaxhighlighter-core' );
		else {
			$theme = ( !empty($this->themes[$this->settings['theme']]) ) ? strtolower($this->settings['theme']) : $this->defaultsettings['theme'];
			wp_enqueue_style( 'syntaxhighlighter-theme-' . $theme );
		}
	}


	// Register the settings page
	function register_settings_page() {
		add_options_page( __('SyntaxHighlighter Settings', 'syntaxhighlighter'), __('SyntaxHighlighter', 'syntaxhighlighter'), 'manage_options', 'syntaxhighlighter', array(&$this, 'settings_page') );
	}


	// Add the custom TinyMCE plugin which wraps plugin shortcodes in <pre> in TinyMCE
	function add_tinymce_plugin( $plugins ) {
		$plugins['syntaxhighlighter'] = plugins_url('syntaxhighlighter/syntaxhighlighter_mce.js');
		return $plugins;
	}


	// Break the TinyMCE cache
	function break_tinymce_cache( $version ) {
		return $version . '-syntaxhighlighter' . $this->pluginver;
	}


	// Add a "Settings" link to the plugins page
	function plugin_action_links( $links, $file ) {
		static $this_plugin;
		
		if( empty($this_plugin) )
			$this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin )
			$links[] = '<a href="' . admin_url( 'options-general.php?page=syntaxhighlighter' ) . '">' . __('Settings', 'syntaxhighlighter') . '</a>';

		return $links;
	}


	// Output list of shortcode tags for the TinyMCE plugin as well as some CSS for the settings page
	function admin_head() {
		$tags = array();
		$tags[] = 'sourcecode';
		$tags[] = 'source';
		$tags[] = 'code';

		foreach ( $this->brushes as $tag => $brush )
			$tags[] = preg_quote( $tag );

		echo "<script type='text/javascript'>\n";
		echo "	var syntaxHLcodes = '" . implode( '|', $tags ) . "';\n";
		echo "</script>\n";

		echo "<style type='text/css'>.bulletlist li { list-style-type: disc; margin-left: 25px; }</style>\n";
	}


	// A filter function that runs do_shortcode() but only with this plugin's shortcodes
	function shortcode_hack( $content, $callback ) {
		global $shortcode_tags;

		// Backup current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		// Register all of this plugin's shortcodes
		add_shortcode( 'sourcecode', $callback );
		add_shortcode( 'source', $callback );
		add_shortcode( 'code', $callback );
		foreach ( $this->brushes as $shortcode => $brush )
			add_shortcode( $shortcode, $callback );

		// Do the shortcodes (only this plugins's are registered)
		$content = do_shortcode( $content );

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}


	// The main filter for the post contents. The regular shortcode filter can't be used as it's post-wpautop().
	function parse_shortcodes( $content ) {
		return $this->shortcode_hack( $content, array(&$this, 'shortcode_callback') );
	}


	// HTML entity encode the contents of shortcodes. Note this handles $_POST-sourced data, so it has to deal with slashes
	function encode_shortcode_contents( $content ) {
		$this->encoded = true;
		return addslashes( $this->shortcode_hack( stripslashes( $content ), array(&$this, 'encode_shortcode_contents_callback') ) );
	}


	// HTML entity decode the contents of shortcodes
	function decode_shortcode_contents( $content ) {
		// If TinyMCE is enabled and set to be displayed first, leave it encoded
		if ( user_can_richedit() && 'html' != wp_default_editor() )
			return $content;

		return $this->shortcode_hack( $content, array(&$this, 'decode_shortcode_contents_callback') );
	}


	// The callback function for SyntaxHighlighter::encode_shortcode_contents()
	function encode_shortcode_contents_callback( $atts, $code = '', $tag = false ) {
		return '[' . $tag . $this->atts2string( $atts ) . ']' . htmlspecialchars( $code ) . "[/$tag]";
	}


	// The callback function for SyntaxHighlighter::decode_shortcode_contents()
	// Shortcode attribute values need to not be quoted with TinyMCE disabled for some reason (weird bug)
	function decode_shortcode_contents_callback( $atts, $code = '', $tag = false ) {
		$quotes = ( user_can_richedit() ) ? true : false;
		return '[' . $tag . $this->atts2string( $atts, $quotes ) . ']' . htmlspecialchars_decode( $code ) . "[/$tag]";
	}


	// Adds a post meta saying that HTML entities are encoded (for backwards compatibility)
	function mark_as_encoded( $post_ID, $post ) {
		if ( false == $this->encoded || 'revision' == $post->post_type )
			return;

		add_post_meta( $post_ID, 'syntaxhighlighter_encoded', true, true );
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
			$strings[] = ( $quotes ) ? $key . '="' . attribute_escape( $value ) . '"' : $key . '=' . attribute_escape( $value );

		return ' ' . implode( ' ', $strings );
	}


	// Simple function for escaping just single quotes (the original js_escape() escapes more than we need)
	function js_escape_singlequotes( $string ) {
		return str_replace( "'", "\'", $string );
	}


	// Output any needed scripts. This is meant for the footer.
	function maybe_output_scripts() {
		if ( empty($this->usedbrushes) )
			return;

		$scripts = array();
		foreach ( $this->usedbrushes as $brush => $unused )
			$scripts[] = 'syntaxhighlighter-brush-' . strtolower( $brush );

		wp_print_scripts( $scripts );

		echo "<script type='text/javascript'>\n";
		echo "	SyntaxHighlighter.config.clipboardSwf = '" . js_escape( plugins_url('syntaxhighlighter/syntaxhighlighter/scripts/clipboard.swf') ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.expandSource = '" . $this->js_escape_singlequotes( __( 'expand source', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.viewSource = '" . $this->js_escape_singlequotes( __( 'view source', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.copyToClipboard = '" . $this->js_escape_singlequotes( __( 'copy to clipboard', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.copyToClipboardConfirmation = '" . $this->js_escape_singlequotes( __( 'The code is in your clipboard now', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.print = '" . $this->js_escape_singlequotes( __( 'print', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.help = '" . $this->js_escape_singlequotes( __( '?', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.alert = '" . $this->js_escape_singlequotes( __( 'SyntaxHighlighter\n\n', 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.noBrush = '" . $this->js_escape_singlequotes( __( "Can't find brush for: ", 'syntaxhighlighter' ) ) . "';\n";
		echo "	SyntaxHighlighter.config.strings.brushNotHtmlScript = '" . $this->js_escape_singlequotes( __( "Brush wasn't configured for html-script option: ", 'syntaxhighlighter' ) ) . "';\n";

		if ( 1 != $this->settings['autolinks'] )
			echo "	SyntaxHighlighter.defaults['auto-links'] = false;\n";

		if ( !empty($this->settings['classname']) )
			echo "	SyntaxHighlighter.defaults['class-name'] = '" . $this->js_escape_singlequotes( $this->settings['classname'] ) . "';\n";

		if ( 1 == $this->settings['collapse'] )
			echo "	SyntaxHighlighter.defaults['collapse'] = true;\n";

		if ( 1 != $this->settings['firstline'] )
			echo "	SyntaxHighlighter.defaults['first-line'] = " . $this->settings['firstline'] . ";\n";

		if ( 100 != $this->settings['fontsize'] )
			echo "	SyntaxHighlighter.defaults['font-size'] = '" . $this->settings['fontsize'] . "%';\n";

		if ( 1 != $this->settings['gutter'] )
			echo "	SyntaxHighlighter.defaults['gutter'] = false;\n";

		if ( 1 == $this->settings['light'] )
			echo "	SyntaxHighlighter.defaults['light'] = true;\n";

		if ( 1 == $this->settings['ruler'] )
			echo "	SyntaxHighlighter.defaults['ruler'] = true;\n";

		if ( 1 != $this->settings['smarttabs'] )
			echo "	SyntaxHighlighter.defaults['smart-tabs'] = false;\n";

		if ( 4 != $this->settings['tabsize'] )
			echo "	SyntaxHighlighter.defaults['tab-size'] = " . $this->settings['tabsize'] . ";\n";

		if ( 1 != $this->settings['toolbar'] )
			echo "	SyntaxHighlighter.defaults['toolbar'] = false;\n";

		echo "	SyntaxHighlighter.all();\n";
		echo "</script>\n";
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

		// Error fixing for [tag="language"]
		if ( isset($atts[0]) ) {
			$atts = $this->attributefix( $atts );
			$atts['language'] = $atts[0];
			unset($atts[0]);
		}

		// Default out all of the available parameters to "false" (easy way to check if they're set or not)
		// Note this isn't the same as if the user passes the string "false" to the shortcode
		$atts = apply_filters( 'syntaxhighlighter_shortcodeatts', shortcode_atts( array(
			'language'   => false,
			'lang'       => false,
			'autolinks'  => false,
			'classname'  => false,
			'collapse'   => false,
			'firstline'  => false,
			'gutter'     => false,
			'highlight'  => false,
			'htmlscript' => false,
			'light'      => false,
			'ruler'      => false,
			'smarttabs'  => false,
			'tabsize'    => false,
			'toolbar'    => false,
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

			// Validate passed attribute
			if ( !isset($this->brushes[$lang]) )
				return $code;
		}

		// Ensure lowercase
		$lang = strtolower( $lang );

		// Register this brush as used so it's script will be outputted
		$this->usedbrushes[$this->brushes[$lang]] = true;

		$params = array();
		$params[] = "brush: $lang;";

		// Fix bug that prevents collapse from working if the toolbar is off
		if ( 1 == $atts['collapse'] || 'true' == $atts['collapse'] ) {
			$atts['toolbar'] = 'true';
			$atts['light'] = 'false';
		}

		// Enable "htmlscript" for certain brushes
		//if ( false === $atts['html-script'] && in_array( $lang, apply_filters( 'syntaxhighlighter_htmlscriptbrushes', array( 'php' ) ) ) )
		//	$atts['html-script'] = 'true';

		foreach ( $atts as $key => $value ) {
			if ( false === $value || in_array( $key, array( 'language', 'lang' ) ) )
				continue;

			// Parameter renaming (the shortcode API doesn't like parameter names with dashes)
			$rename_map = array(
				'autolinks'  => 'auto-links',
				'classname'  => 'class-name',
				'firstline'  => 'first-line',
				'htmlscript' => 'html-script',
				'smarttabs'  => 'smart-tabs',
				'tabsize'    => 'tab-size',
			);
			if ( !empty($rename_map[$key]) )
				$key = $rename_map[$key];

			// If "html-script", then include the XML brush as it's needed
			if ( $key == 'html-script' && ( 'true' == $value || 1 == $value ) )
				$this->usedbrushes['xml'] = true;

			if ( 'highlight' == $key )
				$params[] = "$key: [$value];";
			else
				$params[] = "$key: $value;";
		}

		$content  = '<pre class="' . attribute_escape( implode( ' ', $params ) ) . '">';
		$content .= ( get_post_meta( $post->ID, 'syntaxhighlighter_encoded', true ) ) ? $code : htmlspecialchars( $code );
		$content .= '</pre>';

		return $content;
	}


	// Settings page
	function settings_page() {
		if ( !empty($_GET['defaults']) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Settings reset to defaults.', 'syntaxhighlighter'); ?></strong></p></div>
<?php endif; ?>

<script type="text/javascript">
// <![CDATA[
	jQuery(document).ready(function() {
		// Confirm pressing of the "Reset to Defaults" button
		jQuery("#syntaxhighlighter-defaults").click(function(){
			var areyousure = confirm("<?php echo js_escape( __('Are you sure you want to reset your settings to the defaults?', 'syntaxhighlighter') ); ?>");
			if ( true != areyousure ) return false;
		});
	});
// ]]>
</script>

<div class="wrap">
<?php if ( function_exists('screen_icon') ) screen_icon(); ?>
	<h2><?php _e( 'SyntaxHighlighter Settings', 'syntaxhighlighter' ); ?></h2>

	<form method="post" action="admin-post.php">

	<?php wp_nonce_field('syntaxhighlighter'); ?>

	<input type="hidden" name="action" value="syntaxhighlighter" />

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-theme"><?php _e('Color Theme', 'syntaxhighlighter'); ?></label></th>
			<td>
				<select name="syntaxhighlighter-theme" id="syntaxhighlighter-theme" class="postform">
<?php
					foreach ( $this->themes as $theme => $name ) {
						echo '					<option value="' . attribute_escape($theme) . '"';
						selected( $this->settings['theme'], $theme );
						echo '>' . htmlspecialchars($name) . "</option>\n";
					}
?>
				</select>
			</td>
		</tr>
	</table>

	<h3><?php _e('Defaults', 'syntaxhighlighter'); ?></h3>

	<p><?php _e('All of the settings below can also be configured on a per-code box basis.', 'syntaxhighlighter'); ?></p>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Miscellaneous', 'syntaxhighlighter'); ?></th>
			<td>
				<fieldset>
					<legend class="hidden"><?php _e('Miscellaneous', 'syntaxhighlighter'); ?></legend>

					<label for="syntaxhighlighter-gutter"><input name="syntaxhighlighter-gutter" type="checkbox" id="syntaxhighlighter-gutter" value="1" <?php checked( $this->settings['gutter'], 1 ); ?> /> <?php _e('Display line numbers', 'syntaxhighlighter'); ?></label><br />
					<label for="syntaxhighlighter-toolbar"><input name="syntaxhighlighter-toolbar" type="checkbox" id="syntaxhighlighter-toolbar" value="1" <?php checked( $this->settings['toolbar'], 1 ); ?> /> <?php _e('Display the toolbar', 'syntaxhighlighter'); ?></label><br />
					<label for="syntaxhighlighter-autolinks"><input name="syntaxhighlighter-autolinks" type="checkbox" id="syntaxhighlighter-autolinks" value="1" <?php checked( $this->settings['autolinks'], 1 ); ?> /> <?php _e('Automatically make URLs clickable', 'syntaxhighlighter'); ?></label><br />
					<label for="syntaxhighlighter-collapse"><input name="syntaxhighlighter-collapse" type="checkbox" id="syntaxhighlighter-collapse" value="1" <?php checked( $this->settings['collapse'], 1 ); ?> /> <?php _e('Collapse code boxes', 'syntaxhighlighter'); ?></label><br />
					<label for="syntaxhighlighter-ruler"><input name="syntaxhighlighter-ruler" type="checkbox" id="syntaxhighlighter-ruler" value="1" <?php checked( $this->settings['ruler'], 1 ); ?> /> <?php _e('Show a ruler column along the top of the code box', 'syntaxhighlighter'); ?></label><br />
					<label for="syntaxhighlighter-light"><input name="syntaxhighlighter-light" type="checkbox" id="syntaxhighlighter-light" value="1" <?php checked( $this->settings['light'], 1 ); ?> /> <?php _e('Use the light display mode, best for single lines of code', 'syntaxhighlighter'); ?></label><br />
					<label for="syntaxhighlighter-smarttabs"><input name="syntaxhighlighter-smarttabs" type="checkbox" id="syntaxhighlighter-smarttabs" value="1" <?php checked( $this->settings['smarttabs'], 1 ); ?> /> <?php _e('Use smart tabs allowing tabs being used for alignment', 'syntaxhighlighter'); ?></label>
				</fieldset>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-classname"><?php _e('Additional CSS Class(es)', 'syntaxhighlighter'); ?></label></th>
			<td><input name="syntaxhighlighter-classname" type="text" id="syntaxhighlighter-classname" value="<?php echo attribute_escape( $this->settings['classname'] ); ?>" class="regular-text" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-firstline"><?php _e('Starting Line Number', 'syntaxhighlighter'); ?></label></th>
			<td><input name="syntaxhighlighter-firstline" type="text" id="syntaxhighlighter-firstline" value="<?php echo attribute_escape( $this->settings['firstline'] ); ?>" class="small-text" /></td>
		</tr>
		<tr valign="top" style="display:none">
			<th scope="row">
				<label for="syntaxhighlighter-fontsize"><?php _e('Font Size (Percentage)', 'syntaxhighlighter'); ?></label>
				<br /><strong>THIS CURRENTLY DOESN'T WORK</strong>
			</th>
			<td><input name="syntaxhighlighter-fontsize" type="text" id="syntaxhighlighter-fontsize" value="<?php echo attribute_escape( $this->settings['fontsize'] ); ?>" class="small-text" />%</td>
		</tr>
		<tr valign="top">
			<th scope="row"><label for="syntaxhighlighter-tabsize"><?php _e('Tab Size', 'syntaxhighlighter'); ?></label></th>
			<td><input name="syntaxhighlighter-tabsize" type="text" id="syntaxhighlighter-tabsize" value="<?php echo attribute_escape( $this->settings['tabsize'] ); ?>" class="small-text" /></td>
		</tr>
	</table>

	<p class="submit">
		<input type="submit" name="syntaxhighlighter-submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		<input type="submit" name="syntaxhighlighter-defaults" id="syntaxhighlighter-defaults" class="button-primary" value="<?php _e('Reset to Defaults', 'syntaxhighlighter') ?>" />
	</p>

	</form>

	<h3><?php _e('Preview', 'syntaxhighlighter'); ?></h3>

	<p><?php _e('Click &quot;Save Changes&quot; to update this preview.', 'syntaxhighlighter'); ?>

	<?php

		global $post;

		$post->ID = 0;

		$democode = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>PHP Code Example</title>
</head>
<body>
	<h1>PHP Code Example</h1>

	<p><?php echo \'Hello World!\'; ?></p>

	<p>This line is highlighted.</p>

	<div class="foobar">
		This	is	an
		example	of	smart
		tabs.
	</div>

	<p><a href="http://wordpress.org/">WordPress</a></p>
</body>
</html>';

		echo $this->parse_shortcodes( '[php htmlscript="true" highlight="12"]' . $democode . '[/php]' );

?>

	<h3 style="margin-top:30px"><?php _e('Shortcode Parameters', 'syntaxhighlighter'); ?></h3>

	<p><?php printf( __('These are the parameters you can pass to the shortcode and what they do. For the booleans (i.e. on/off), pass %1$s/%2$s or %3$s/%4$s.', 'syntaxhighlighter'), '<code>true</code>', '<code>1</code>', '<code>false</code>', '<code>0</code>' ); ?></p>

	<ul class="bulletlist">
		<li><?php printf( __('%1$s or %2$s &#8212; The language syntax to highlight with. You can alternately just use that as the tag, such as <code>[php]code[/php]</code>. <a href="%3$s">Click here</a> for a list of valid tags (under &quot;aliases&quot;).', 'syntaxhighlighter'), '<code>lang</code>', '<code>language</code>', 'http://alexgorbatchev.com/wiki/SyntaxHighlighter:Brushes' ); ?></li>
		<li><?php printf( __('%s &#8212; Toggle automatic URL linking.', 'syntaxhighlighter'), '<code>autolinks</code>' ); ?></li>
		<li><?php printf( __('%s &#8212; Add an additional CSS class to the code box.', 'syntaxhighlighter'), '<code>classname</code>' ); ?></li>
		<li><?php printf( __('%s &#8212; Toggle collapsing the code box by default, requiring a click to expand it. Good for large code posts.', 'syntaxhighlighter'), '<code>collapse</code>' ); ?></li>
		<li><?php printf( __('%s &#8212; An interger specifying what number the first line should be (for the line numbering).', 'syntaxhighlighter'), '<code>firstline</code>' ); ?></li>
		<li><?php printf( __('%s &#8212; Toggle the left-side line numbering.', 'syntaxhighlighter'), '<code>gutter</code>' ); ?></li>
		<li><?php printf( __('%s &#8212; A comma-sperated list of line numbers to highlight.', 'syntaxhighlighter'), '<code>highlight</code>' ); ?></li>
		<li><?php printf( __("%s &#8212; Toggle highlighting any extra HTML/XML. Good for when you're mixing HTML/XML with another language, such as having PHP inside an HTML web page. The above preview has it enabled for example.", 'syntaxhighlighter'), '<code>htmlscript</code>' ); ?></li>
		<li><?php printf( __("%s &#8212; Toggle light mode which disables the gutter, toolbar, and ruler all at once.", 'syntaxhighlighter'), '<code>light</code>' ); ?></li>
		<li><?php printf( __("%s &#8212; Toggle the column ruler at the top of the code box.", 'syntaxhighlighter'), '<code>ruler</code>' ); ?></li>
		<li><?php printf( __("%s &#8212; Toggle the toolbar containing the helpful buttons.", 'syntaxhighlighter'), '<code>toolbar</code>' ); ?></li>
	</ul>

	<p><?php _e('Some example shortcodes:', 'syntaxhighlighter'); ?></p>

	<ul class="bulletlist">
		<li><code>[php]<?php _e('your code here', 'syntaxhighlighter'); ?>[/php]</code></li>
		<li><code>[css autolinks=&quot;false&quot; classname=&quot;myclass&quot; collapse=&quot;false&quot; firstline=&quot;1&quot; gutter=&quot;true&quot; highlight=&quot;1,2,3&quot; htmlscript=&quot;false&quot; light=&quot;false&quot; ruler=&quot;false&quot; smarttabs=&quot;true&quot; tabsize=&quot;4&quot; toolbar=&quot;true&quot;]<?php _e('your code here', 'syntaxhighlighter'); ?>[/css]</code></li>
		<li><code>[code lang=&quot;js&quot;]<?php _e('your code here', 'syntaxhighlighter'); ?>[/code]</code></li>
		<li><code>[sourcecode language=&quot;plain&quot;]<?php _e('your code here', 'syntaxhighlighter'); ?>[/sourcecode]</code></li>
	</ul>

<?php $this->maybe_output_scripts(); ?>

</div>

<?php
	}


	// Handle the results of the settings page
	function save_settings() {
		// Capability check
		if ( !current_user_can('manage_options') )
			wp_die( __('Cheatin&#8217; uh?') );

		// Form nonce check
		check_admin_referer('syntaxhighlighter');

		$defaults = false;

		if ( !empty($_POST['syntaxhighlighter-defaults']) ) {
			$settings = $this->defaultsettings;
			$defaults = true;
		} else {
			$settings = array();

			$settings['theme']      = ( !empty($_POST['syntaxhighlighter-theme']) && isset($this->themes[$_POST['syntaxhighlighter-theme']]) ) ? strtolower($_POST['syntaxhighlighter-theme']) : $this->defaultsettings['theme'];

			$settings['autolinks']  = ( !empty($_POST['syntaxhighlighter-autolinks']) )  ? 1 : 0;
			$settings['collapse']   = ( !empty($_POST['syntaxhighlighter-collapse']) )   ? 1 : 0;
			$settings['gutter']     = ( !empty($_POST['syntaxhighlighter-gutter']) )     ? 1 : 0;
			$settings['light']      = ( !empty($_POST['syntaxhighlighter-light']) )      ? 1 : 0;
			$settings['ruler']      = ( !empty($_POST['syntaxhighlighter-ruler']) )      ? 1 : 0;
			$settings['smarttabs']  = ( !empty($_POST['syntaxhighlighter-smarttabs']) )  ? 1 : 0;
			$settings['toolbar']    = ( !empty($_POST['syntaxhighlighter-toolbar']) )    ? 1 : 0;

			$settings['classname']  = ( !empty($_POST['syntaxhighlighter-classname']) )       ? stripslashes( trim( $_POST['syntaxhighlighter-classname'] ) ) : $this->defaultsettings['classname'];
			$settings['firstline']  = (int) ( !empty($_POST['syntaxhighlighter-firstline']) ) ? stripslashes( $_POST['syntaxhighlighter-firstline'] )         : $this->defaultsettings['firstline'];
			$settings['fontsize']   = (int) ( !empty($_POST['syntaxhighlighter-fontsize']) )  ? stripslashes( $_POST['syntaxhighlighter-fontsize'] )          : $this->defaultsettings['fontsize'];
			$settings['tabsize']    = (int) ( !empty($_POST['syntaxhighlighter-tabsize']) )   ? stripslashes( $_POST['syntaxhighlighter-tabsize'] )           : $this->defaultsettings['tabsize'];
		}

		update_option( 'syntaxhighlighter_settings', $settings );

		// Redirect back to where we came from (the settings page)
		$redirectto = remove_query_arg( 'defaults', remove_query_arg( 'updated', wp_get_referer() ) );
		$redirectto = ( $defaults ) ? add_query_arg( 'defaults', 'true', $redirectto ) : add_query_arg( 'updated', 'true', $redirectto );
		wp_safe_redirect( $redirectto );
	}


	// PHP4 compatibility
	function SyntaxHighlighter() {
		$this->__construct();
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', 'SyntaxHighlighter' ); function SyntaxHighlighter() { global $SyntaxHighlighter; $SyntaxHighlighter = new SyntaxHighlighter(); }

?>
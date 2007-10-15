<?php /*

**************************************************************************

Plugin Name:  SyntaxHighlighter
Plugin URI:   http://wordpress.org/extend/plugins/syntaxhighlighter/
Version:      1.0.1
Description:  An advanced upload-and-activate WordPress implementation of Alex Gorbatchev's <a href="http://code.google.com/p/syntaxhighlighter/">SyntaxHighlighter</a> JavaScript code highlighting package. See WordPress.com's "<a href="http://faq.wordpress.com/2007/09/03/how-do-i-post-source-code/">How do I post source code?</a>" for details.
Author:       <a href="http://photomatt.net/">Matt</a> & <a href="http://www.viper007bond.com/">Viper007Bond</a>

**************************************************************************

Credits:

* Matt ( photomatt.net ) -- original concept and code on WP.com
* Viper007Bond ( viper007bond.com ) -- current plugin version

Simply put, Matt deserves the majority of the credit for this plugin.
I (Viper007Bond) just took the plugin I had already written (it looked a
lot like this current one) after seeing his code operate on WP.com and
incorporated his ingenius TinyMCE handling and some other misc. code.

**************************************************************************/

class AGSyntaxHighlighter {
	var $languages = array();
	var $languagesregex;
	var $jsfiles2load = array();
	var $pluginurl;

	// WordPress hooks
	function AGSyntaxHighlighter() {
		add_action( 'init', array(&$this, 'SetVariables'), 1000 );
		add_action( 'wp_head', array(&$this, 'AddStylesheet'), 1000 );
		add_action( 'admin_head', array(&$this, 'AddStylesheet'), 1000 );
		add_action( 'wp_footer', array(&$this, 'FileLoader'), 1000 );
		add_action( 'admin_footer', array(&$this, 'FileLoader'), 1000 ); // For viewing comments in admin area

		// Find and replace the BBCode
		add_filter( 'the_content', array(&$this, 'BBCodeToHTML'), 8 );
		add_filter( 'widget_text', array(&$this, 'BBCodeToHTML'), 8 );

		// Account for TinyMCE
		add_filter( 'content_save_pre', array(&$this, 'TinyMCEDecode'), 8 );
		add_filter( 'the_editor_content', array(&$this, 'TinyMCEEncode'), 8 );

		// Uncomment this next line to allow commenters to post code
		//add_filter( 'comment_text', array(&$this, 'BBCodeToHTML'), 8 );
	}


	// Set some variables now that we've given all other plugins a chance to load
	function SetVariables() {
		$this->pluginurl = apply_filters( 'agsyntaxhighlighter_url', get_bloginfo( 'wpurl' ) . '/wp-content/plugins/syntaxhighlighter/files/' );

		// Define all allowed languages and allow plugins to modify this
		$this->languages = apply_filters( 'agsyntaxhighlighter_languages', array(
			'cpp' => 'shBrushCpp.js',
			'c' => 'shBrushCpp.js',
			'c++' => 'shBrushCpp.js',
			'c#' => 'shBrushCSharp.js',
			'c-sharp' => 'shBrushCSharp.js',
			'csharp' => 'shBrushCSharp.js',
			'css' => 'shBrushCss.js',
			'delphi' => 'shBrushDelphi.js',
			'pascal' => 'shBrushDelphi.js',
			'java' => 'shBrushJava.js',
			'js' => 'shBrushJScript.js',
			'jscript' => 'shBrushJScript.js',
			'javascript' => 'shBrushJScript.js',
			'php' => 'shBrushPhp.js',
			'py' => 'shBrushPython.js',
			'python' => 'shBrushPython.js',
			'rb' => 'shBrushRuby.js',
			'ruby' => 'shBrushRuby.js',
			'rails' => 'shBrushRuby.js',
			'ror' => 'shBrushRuby.js',
			'sql' => 'shBrushSql.js',
			'vb' => 'shBrushVb.js',
			'vb.net' => 'shBrushVb.js',
			'xml' => 'shBrushXml.js',
			'html' => 'shBrushXml.js',
			'xhtml' => 'shBrushXml.js',
			'xslt' => 'shBrushXml.js',
		) );

		// Quote them to make them regex safe
		$languages = array();
		foreach ( $this->languages as $language => $filename ) $languages[] = preg_quote( $language );

		// Generate the regex for them
		$this->languagesregex = '(' . implode( '|', $languages ) . ')';
	}


	// We need to stick the stylesheet in the header for best results
	function AddStylesheet() {
		echo '	<link type="text/css" rel="stylesheet" href="' . $this->pluginurl . 'SyntaxHighlighter.css"></link>' . "\n";
	}


	// This function checks for the BBCode cheaply so we don't waste CPU cycles on regex if it's not needed
	// It's in a seperate function since it's used in mulitple places (makes it easier to edit)
	function CheckForBBCode( $content ) {
		if ( stristr( $content, '[sourcecode' ) && stristr( $content, '[/sourcecode]' ) ) return TRUE;
		if ( stristr( $content, '[source' ) && stristr( $content, '[/source]' ) ) return TRUE;
		if ( stristr( $content, '[code' ) && stristr( $content, '[/code]' ) ) return TRUE;

		return FALSE;
	}


	// This function is a wrapper for preg_match_all() that grabs all BBCode calls
	// It's in a seperate function since it's used in mulitple places (makes it easier to edit)
	function GetBBCode( $content, $addslashes = FALSE ) {
		$regex = '/\[(sourcecod|sourc|cod)(e language=|e lang=|e=)';
		if ( $addslashes ) $regex .= '\\\\';
		$regex .= '([\'"])' . $this->languagesregex;
		if ( $addslashes ) $regex .= '\\\\';
		$regex .= '\3\](.*?)\[\/\1e\]/si';

		preg_match_all( $regex, $content, $matches, PREG_SET_ORDER );

		return $matches;
	}


	// Reverse changes TinyMCE made to the entered code
	function TinyMCEDecode( $content ) {
		if ( !user_can_richedit() || !$this->CheckForBBCode( $content ) ) return $content;

		// Find all BBCode (remember, it's all slash escaped!)
		$matches = $this->GetBBCode( $content, TRUE );

		if ( empty($matches) ) return $content; // No BBCode found, we can stop here

		// Loop through each match and decode the code
		foreach ( (array) $matches as $match ) {
			$content = str_replace( $match[5], htmlspecialchars_decode( $match[5] ), $content );
		}

		return $content;
	}


	// (Re)Encode the code so TinyMCE will display it correctly
	function TinyMCEEncode( $content ) {
		if ( !user_can_richedit() || !$this->CheckForBBCode( $content ) ) return $content;

		$matches = $this->GetBBCode( $content );

		if ( empty($matches) ) return $content; // No BBCode found, we can stop here

		// Loop through each match and encode the code
		foreach ( (array) $matches as $match ) {
			$code = htmlspecialchars( $match[5] );
			$code = str_replace( '&amp;', '&amp;amp;', $code );
			$code = str_replace( '&amp;lt;', '&amp;amp;lt;', $code );
			$code = str_replace( '&amp;gt;', '&amp;amp;gt;', $code );

			$content = str_replace( $match[5], $code, $content );
		}

		return $content;
	}


	// The meat of the plugin. Find all valid BBCode calls and replace them with HTML for the Javascript to handle.
	function BBCodeToHTML( $content ) {
		if ( !$this->CheckForBBCode( $content ) ) return $content;

		$matches = $this->GetBBCode( $content );

		if ( empty($matches) ) return $content; // No BBCode found, we can stop here

		// Loop through each match and replace the BBCode with HTML
		foreach ( (array) $matches as $match ) {
			$language = strtolower( $match[4] );
			$content = str_replace( $match[0], '<pre name="code" class="' . $language . "\">\n" . htmlspecialchars( $match[5] ) . "\n</pre>", $content );
			$this->jsfiles2load[$this->languages[$language]] = TRUE;
		}

		return $content;
	}


	// Output the HTML to load all of SyntaxHighlighter's Javascript, CSS, and SWF files
	function FileLoader() {
		?>

<!-- SyntaxHighlighter Stuff -->
<script type="text/javascript" src="<?php echo $this->pluginurl; ?>shCore.js"></script>
<?php foreach ( $this->jsfiles2load as $filename => $foobar ) : ?>
<script type="text/javascript" src="<?php echo $this->pluginurl . $filename; ?>"></script>
<?php endforeach; ?>
<script type="text/javascript">
	dp.SyntaxHighlighter.ClipboardSwf = '<?php echo $this->pluginurl; ?>clipboard.swf';
	dp.SyntaxHighlighter.HighlightAll('code');
</script>

<?php
	}
}

// Initiate the plugin class
$AGSyntaxHighlighter = new AGSyntaxHighlighter();

// For those poor souls stuck on PHP4
if ( !function_exists( 'htmlspecialchars_decode' ) ) {
	function htmlspecialchars_decode( $string, $quote_style = ENT_COMPAT ) {
		return strtr( $string, array_flip( get_html_translation_table( HTML_SPECIALCHARS, $quote_style) ) );
	}
}
?>
<?php
/**
 * Class SyntaxHighlighter_Evolved_Tests_Shortcode_Parsing
 *
 * @package    SyntaxHighlighter_Evolved
 */

/**
 * Tests for how the plugin's shortcodes coexist with other shortcodes, and with text that only looks like a shortcode.
 *
 * Most of the examples are taken from bug reports. The issue numbers are from https://github.com/Automattic/syntaxhighlighter
 * and the topics are from https://wordpress.org/support/plugin/syntaxhighlighter/
 */
class SyntaxHighlighter_Evolved_Tests_Shortcode_Parsing extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();

		// Start every test with the shortcodes registered the way they are at the start of a request
		foreach ( $GLOBALS['SyntaxHighlighter']->shortcodes as $shortcode ) {
			add_shortcode( $shortcode, '__return_empty_string' );
		}

		add_shortcode( 'current_datetime', array( $this, 'shortcode_current_datetime' ) );
		add_shortcode( 'yell', array( $this, 'shortcode_yell' ) );
		add_shortcode( 'su_spoiler', array( $this, 'shortcode_wrapper' ) );
	}

	public function tear_down() {
		remove_shortcode( 'current_datetime' );
		remove_shortcode( 'yell' );
		remove_shortcode( 'su_spoiler' );

		// Put back any placeholder that a test replaced
		foreach ( $GLOBALS['SyntaxHighlighter']->shortcodes as $shortcode ) {
			add_shortcode( $shortcode, '__return_empty_string' );
		}

		parent::tear_down();
	}

	public function shortcode_current_datetime() {
		return 'DATETIME';
	}

	public function shortcode_yell( $atts, $content = '' ) {
		return strtoupper( $content );
	}

	/**
	 * A shortcode that wraps its content, the way spoilers, tabs and columns do.
	 */
	public function shortcode_wrapper( $atts, $content = '' ) {
		return '<div class="wrapper">' . do_shortcode( $content ) . '</div>';
	}

	private function render( $content ) {
		return apply_filters( 'the_content', $content );
	}

	/*
	 * Code is displayed as it was written
	 */

	/**
	 * Issue #26: "data[c]" in C code was displayed as "data".
	 */
	public function test_language_tag_inside_code_of_the_same_language() {
		$rendered = $this->render( "[c]\nint data[100];\nint c;\n\nfor (c=0;c&lt;=100;c++)\n    data[c]=c;\n[/c]" );

		$this->assertStringContainsString( '<pre class="brush: cpp;', $rendered );
		$this->assertStringContainsString( 'int data&#x5B;100];', $rendered );
		$this->assertStringContainsString( 'data&#x5B;c]=c;', $rendered );
	}

	/**
	 * Issue #26, as reported: the same code inside [code lang="c"].
	 */
	public function test_language_tag_inside_code() {
		$rendered = $this->render( "[code lang=\"c\"]\nchar data[100];\nint cpp;\n\nfor (c=0;c&lt;100;c++)\ndata[c]=\"a\";\n[/code]" );

		$this->assertStringContainsString( '<pre class="brush: cpp;', $rendered );
		$this->assertStringContainsString( 'data&#x5B;c]="a";', $rendered );
	}

	/**
	 * Issue #32: [xml] on a line of its own inside [code] was removed.
	 */
	public function test_bare_language_tag_inside_code() {
		$rendered = $this->render( "[code]\n\n[xml]\n\n[/code]" );

		$this->assertStringContainsString( '&#x5B;xml]', $rendered );
	}

	/**
	 * Issue #63: [diff "csharp"] in a Git config file was removed. "diff" is one of this plugin's shortcodes.
	 */
	public function test_language_tag_with_attributes_inside_code() {
		$rendered = $this->render( "[code]\n[diff \"csharp\"]\n  xfuncname = ^[ \\t]*static\n[/code]" );

		$this->assertStringContainsString( '&#x5B;diff "csharp"]', $rendered );
		$this->assertStringContainsString( 'xfuncname = ^&#x5B; \\t]*static', $rendered );
	}

	/**
	 * Other plugins' shortcodes inside of code are displayed, not run.
	 *
	 * @dataProvider data_other_shortcodes_in_code
	 */
	public function test_other_shortcodes_inside_code_are_not_run( $content, $expected ) {
		$rendered = $this->render( $content );

		$this->assertStringContainsString( $expected, $rendered );
		$this->assertStringNotContainsString( 'DATETIME', $rendered );
	}

	public static function data_other_shortcodes_in_code() {
		return array(
			// Topic "Latest version doesn't parse shortcodes inside the highlighted area": the intended behaviour since 3.7.0
			'shortcode on its own line'     => array( "[text]\n[current_datetime]\n[/text]", '&#x5B;current_datetime]' ),
			// Topic "Unable to prevent of running the shortcodes"
			'shortcode in a language tag'   => array( "[php]\n[current_datetime]\n[/php]", '&#x5B;current_datetime]' ),
			// Topic "SyntaxHighlighter Evolved and shortcodes"
			'shortcode with attributes'     => array( '[php][current_datetime id=http://www.youtube.com/watch?v=kC9Ju63Bchk width=460][/php]', '&#x5B;current_datetime id=http://www.youtube.com/watch?v=kC9Ju63Bchk width=460]' ),
			// Topic "Plugin executing shortcode in code sample"
			'shortcode in a PHP string'     => array( "[php]\necho do_shortcode( '[current_datetime]' );\n[/php]", "echo do_shortcode( '&#x5B;current_datetime]' );" ),
			// PR #260
			'shortcode in a comment'        => array( "[c]\nint c = 0;\nprintf(data[c]);\n// A [current_datetime]\n[/c]", '// A &#x5B;current_datetime]' ),
			'enclosing shortcode in code'   => array( "[code lang=\"c\"]\n// D [yell]is this yelling?[/yell]\n[/code]", '// D &#x5B;yell]is this yelling?&#x5B;/yell]' ),
			'escaped shortcode in code'     => array( "[code lang=\"c\"]\n// B [[current_datetime]]\n// C [[c]]\n[/code]", '// B &#x5B;&#x5B;current_datetime]]' ),
		);
	}

	/**
	 * PR #260: the fix for shortcodes inside of code stopped the shortcodes in the rest of the post from working.
	 */
	public function test_other_shortcodes_outside_code_still_run() {
		$rendered = $this->render( "Real shortcode: [current_datetime]\n\n[php light=\"true\"]datetime: [current_datetime][/php]\n\n[yell]quiet[/yell]" );

		$this->assertStringContainsString( 'Real shortcode: DATETIME', $rendered );
		$this->assertStringContainsString( 'datetime: &#x5B;current_datetime]', $rendered );
		$this->assertStringContainsString( 'QUIET', $rendered );
	}

	public function test_escaped_code_shortcode_is_displayed_not_rendered() {
		$rendered = $this->render( 'Use [[code]your code[/code]] to post code.' );

		$this->assertStringContainsString( '[code]your code[/code]', $rendered );
		$this->assertStringNotContainsString( '<pre', $rendered );
	}

	/**
	 * Topic "Illegal filtering of generic java code tokens".
	 */
	public function test_angle_brackets_in_code() {
		$rendered = $this->render( '[sourcecode language="java"]public class Aggregator <T> {}[/sourcecode]' );

		$this->assertStringContainsString( 'public class Aggregator &lt;T&gt; {}', $rendered );
	}

	/**
	 * Topic "Line breaks missing from my formatted code".
	 */
	public function test_line_breaks_and_indentation_in_code() {
		$rendered = $this->render( "[code lang=\"java\"]\nwhile (a &lt; 10) {\n    a += 1;\n}\n[/code]" );

		$this->assertStringContainsString( "while (a &lt; 10) {\n    a += 1;\n}", $rendered );
	}

	/*
	 * Text that only looks like one of this plugin's shortcodes is left alone
	 */

	/**
	 * Issues #71 and #72: [c] was removed from an analytics snippet, in a post and in a text widget.
	 *
	 * @dataProvider data_bare_language_tags
	 */
	public function test_bare_language_tag_in_a_post_is_left_alone( $text ) {
		$this->assertStringContainsString( $text, $this->render( $text ) );
	}

	/**
	 * @dataProvider data_bare_language_tags
	 */
	public function test_bare_language_tag_in_a_post_with_code_is_left_alone( $text ) {
		// The code uses a tag that isn't in the text: WordPress pairs a bare tag with a later closing tag of the same name
		$rendered = $this->render( $text . "\n\n[bash]echo 1;[/bash]" );

		$this->assertStringContainsString( $text, $rendered );
		$this->assertStringContainsString( '<pre class="brush: bash;', $rendered );
	}

	/**
	 * @dataProvider data_bare_language_tags
	 */
	public function test_bare_language_tag_after_code_was_rendered_is_left_alone( $text ) {
		$this->render( '[php]echo 1;[/php]' );

		$this->assertStringContainsString( $text, $this->render( $text ) );
	}

	/**
	 * @dataProvider data_bare_language_tags
	 */
	public function test_bare_language_tag_in_a_text_widget_is_left_alone( $text ) {
		$rendered = apply_filters( 'widget_text', $text, array( 'text' => $text ) );
		$rendered = do_shortcode( $rendered ); // What the text widget does when shortcodes are enabled in it

		$this->assertStringContainsString( $text, $rendered );
	}

	/**
	 * @dataProvider data_bare_language_tags
	 */
	public function test_bare_language_tag_inside_another_shortcode_is_left_alone( $text ) {
		$this->assertStringContainsString( strtoupper( $text ), $this->render( '[yell]' . $text . '[/yell]' ) );
	}

	public static function data_bare_language_tags() {
		return array(
			'analytics snippet (#71)' => array( '(w[c] = w[c] || []).push(function() {' ),
			'array index'             => array( 'data[c] and list[r] and map[js]' ),
			'bracketed word'          => array( 'Languages: [php] [css] [sql]' ),
			'generic tag'             => array( 'See [code] and [sourcecode]' ),
		);
	}

	/**
	 * Topics "Seems do_shortcode not working with [php] shortcode!" and "do_shortcode returns/echos 1":
	 * the plugin's shortcodes are only rendered by its own filters, so do_shortcode() should leave them alone
	 * instead of removing the code.
	 */
	public function test_do_shortcode_does_not_remove_code() {
		$content = 'Hello, here is [php]Php code goes here[/php] code!';

		$this->assertSame( $content, do_shortcode( $content ) );
	}

	public function test_do_shortcode_after_code_was_rendered_does_not_remove_code() {
		$content = 'Hello, here is [php]Php code goes here[/php] code!';

		$this->render( '[php]echo 1;[/php]' );

		$this->assertSame( $content, do_shortcode( $content ) );
	}

	/**
	 * Another plugin can register a shortcode with the same name as one of this plugin's, which replaces the placeholder.
	 */
	public function test_shortcode_of_the_same_name_from_another_plugin_still_works() {
		add_shortcode( 'text', array( $this, 'shortcode_current_datetime' ) );
		add_shortcode( 'ps', array( $this, 'shortcode_yell' ) );

		$this->assertSame( 'Now: DATETIME', do_shortcode( 'Now: [text]' ) );
		$this->assertSame( 'LOUD', do_shortcode( '[ps]loud[/ps]' ) );

		// A tag without a closing tag isn't code, so it's still the other plugin's in a post
		$rendered = $this->render( "Now: [text]\n\n[code]echo 1;[/code]" );

		$this->assertStringContainsString( 'Now: DATETIME', $rendered );
		$this->assertStringContainsString( '<pre class="brush: plain;', $rendered );
	}

	/*
	 * The plugin's shortcodes stay registered, so WordPress knows what they are
	 */

	public function test_shortcodes_are_still_registered_after_rendering() {
		$this->render( '[code]echo 1;[/code]' );

		$this->assertTrue( shortcode_exists( 'code' ) );
		$this->assertTrue( shortcode_exists( 'php' ) );
		$this->assertTrue( shortcode_exists( 'yell' ) );
	}

	public function test_strip_shortcodes_removes_code() {
		$this->assertSame( 'Before  after', strip_shortcodes( 'Before [php]echo 1;[/php] after' ) );
	}

	public function test_strip_shortcodes_removes_code_after_rendering() {
		$this->render( '[code]echo 1;[/code]' );

		$this->assertSame( 'Before  after', strip_shortcodes( 'Before [php]echo 1;[/php] after' ) );
		$this->assertSame( 'Before  after', strip_shortcodes( 'Before [code lang="php"]echo 1;[/code] after' ) );
	}

	/**
	 * On an archive page, the excerpt of the second post is made after the first post has been rendered.
	 */
	public function test_automatic_excerpt_has_no_code_after_another_post_was_rendered() {
		$post_id = self::factory()->post->create(
			array(
				'post_content' => "The introduction.\n\n[php]echo 'SECRET_CODE';[/php]\n\nThe conclusion.",
				'post_excerpt' => '',
			)
		);

		$this->render( '[code]echo 1;[/code]' );

		$excerpt = get_the_excerpt( $post_id );

		$this->assertStringContainsString( 'The introduction.', $excerpt );
		$this->assertStringContainsString( 'The conclusion.', $excerpt );
		$this->assertStringNotContainsString( 'SECRET_CODE', $excerpt );
		$this->assertStringNotContainsString( '[php]', $excerpt );
	}

	/*
	 * Code inside of another plugin's shortcode
	 */

	/**
	 * Topic "Short codes not workable with Shortcodes Ultimate": worked until 3.7.0.
	 */
	public function test_code_inside_another_shortcode_is_highlighted() {
		$rendered = $this->render( "[su_spoiler title=\"EXAMPLE TITLE\" style=\"fancy\" icon=\"arrow\" open=\"yes\"]\n[shell]\nEXAMPLE SHELL CODE\n[/shell]\n[/su_spoiler]" );

		$this->assertStringContainsString( '<div class="wrapper">', $rendered );
		$this->assertStringContainsString( '<pre class="brush: bash;', $rendered );
		$this->assertStringContainsString( 'EXAMPLE SHELL CODE', $rendered );
		$this->assertStringNotContainsString( '[shell]', $rendered );
	}

	public function test_code_inside_another_shortcode_is_escaped() {
		$rendered = $this->render( '[su_spoiler][code lang="html"]<b>bold</b> & [current_datetime][/code][/su_spoiler]' );

		$this->assertStringContainsString( '&lt;b&gt;bold&lt;/b&gt; &amp; &#x5B;current_datetime]', $rendered );
		$this->assertStringNotContainsString( '<b>', $rendered );
		$this->assertStringNotContainsString( 'DATETIME', $rendered );
	}

	/**
	 * Code has to be encoded when a post is saved, or KSES removes the HTML in it for users who can't post HTML.
	 */
	public function test_code_inside_another_shortcode_is_encoded_when_saved() {
		$encoded = $GLOBALS['SyntaxHighlighter']->encode_shortcode_contents( '[su_spoiler][php]<b>bold</b>[/php][/su_spoiler]' );

		$this->assertSame( '[su_spoiler][php]&lt;b&gt;bold&lt;/b&gt;[/php][/su_spoiler]', $encoded );
	}

	public function test_code_inside_another_shortcode_is_decoded_for_editing() {
		$decoded = $GLOBALS['SyntaxHighlighter']->decode_shortcode_contents( '[su_spoiler][php]&lt;b&gt;bold&lt;/b&gt;[/php][/su_spoiler]' );

		$this->assertSame( '[su_spoiler][php]<b>bold</b>[/php][/su_spoiler]', $decoded );
	}

	/*
	 * Saving and editing
	 */

	/**
	 * Saves a post the way the editor does, as a user who isn't allowed to post unfiltered HTML.
	 *
	 * @param string $content The post content, as it was typed.
	 *
	 * @return WP_Post The saved post.
	 */
	private function save_post_as_an_author( $content ) {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'author' ) ) );

		// The code is only encoded when a form was posted, and only once per request
		$_POST = array( 'action' => 'editpost' );
		$GLOBALS['SyntaxHighlighter']->content_save_pre_ran = false;

		$post_id = wp_insert_post(
			wp_slash(
				array(
					'post_title'   => 'A post with code',
					'post_content' => $content,
					'post_status'  => 'publish',
				)
			)
		);

		$_POST = array();
		wp_set_current_user( 0 );

		return get_post( $post_id );
	}

	/**
	 * WordPress removes the HTML that authors aren't allowed to post, such as scripts. Code has to be encoded
	 * before that happens, or the HTML in it is lost.
	 *
	 * @dataProvider data_saved_code
	 */
	public function test_html_in_code_survives_being_saved_by_an_author( $before, $after ) {
		$code = '<script>alert( 1 );</script> <b>bold</b> <?php echo $a && $b; ?>';
		$post = $this->save_post_as_an_author( $before . $code . $after );

		$this->assertStringContainsString( htmlspecialchars( $code ), $post->post_content );

		$GLOBALS['post'] = $post;
		$rendered        = $this->render( $post->post_content );
		unset( $GLOBALS['post'] );

		$this->assertStringContainsString( '&lt;script&gt;alert( 1 );&lt;/script&gt; &lt;b&gt;bold&lt;/b&gt; &lt;?php echo $a &amp;&amp; $b; ?&gt;', $rendered );
		$this->assertStringNotContainsString( '<script>', $rendered );
		$this->assertStringContainsString( '<pre class="brush: php;', $rendered );

		// And what the author gets back in the editor is what they typed
		$this->assertStringContainsString( $code, $GLOBALS['SyntaxHighlighter']->decode_shortcode_contents( $post->post_content ) );
	}

	public static function data_saved_code() {
		return array(
			'code'                        => array( '[php]', '[/php]' ),
			'code with a language'        => array( '[code lang="php"]', '[/code]' ),
			'code inside a shortcode'     => array( '[su_spoiler title="Example"][php]', '[/php][/su_spoiler]' ),
			'code inside two shortcodes'  => array( "[su_spoiler]\n[yell]loud[/yell]\n[su_spoiler][sourcecode language=\"php\"]", "[/sourcecode][/su_spoiler]\n[/su_spoiler]" ),
		);
	}

	public function test_code_is_encoded_when_saved_and_decoded_for_editing() {
		$sh      = $GLOBALS['SyntaxHighlighter'];
		$content = "Intro [current_datetime]\n\n[php]if ( \$a < \$b && \$c ) { echo '<b>'; } // [current_datetime] data[c][/php]\n\n[yell]outro[/yell]";

		$encoded = $sh->encode_shortcode_contents( $content );

		$this->assertStringContainsString( '[php]if ( $a &lt; $b &amp;&amp; $c ) { echo &#039;&lt;b&gt;&#039;; } // [current_datetime] data[c][/php]', $encoded );
		$this->assertStringContainsString( 'Intro [current_datetime]', $encoded );
		$this->assertStringContainsString( '[yell]outro[/yell]', $encoded );
		$this->assertSame( $content, $sh->decode_shortcode_contents( $encoded ) );
	}

	/**
	 * Sites can limit the shortcodes with the `syntaxhighlighter_shortcodes` filter. WordPress.com only has these three.
	 */
	public function test_site_with_only_the_generic_shortcodes() {
		$sh             = $GLOBALS['SyntaxHighlighter'];
		$all_shortcodes = $sh->shortcodes;

		foreach ( array_diff( $all_shortcodes, array( 'sourcecode', 'source', 'code' ) ) as $shortcode ) {
			remove_shortcode( $shortcode );
		}
		$sh->shortcodes = array( 'sourcecode', 'source', 'code' );

		try {
			$rendered = $this->render( "Not code: [php]echo 1;[/php] and data[c] and [code].\n\n[su_spoiler][sourcecode language=\"php\"]echo \$data[c]; // [current_datetime][/sourcecode][/su_spoiler]" );
			$stripped = strip_shortcodes( 'Before [code]echo 1;[/code] after' );
		} finally {
			$sh->shortcodes = $all_shortcodes;
		}

		$this->assertStringContainsString( 'Not code: [php]echo 1;[/php] and data[c] and [code].', $rendered );
		$this->assertStringContainsString( '<pre class="brush: php;', $rendered );
		$this->assertStringContainsString( 'echo $data&#x5B;c]; // &#x5B;current_datetime]', $rendered );
		$this->assertSame( 'Before  after', $stripped );
	}

	public function test_shortcodes_in_a_comment() {
		$rendered = apply_filters( 'comment_text', "Try this:\n\n[code lang=\"php\"]echo \$data[c]; // [current_datetime][/code]" );

		$this->assertStringContainsString( '<pre class="brush: php;', $rendered );
		$this->assertStringContainsString( 'echo $data&#x5B;c]; // &#x5B;current_datetime]', $rendered );
	}
}

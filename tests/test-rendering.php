<?php
/**
 * Class SyntaxHighlighter_Evolved_Tests_Rendering
 *
 * @package    SyntaxHighlighter_Evolved
 */

/**
 * Tests for how blocks and shortcodes are rendered on the front end.
 */
class SyntaxHighlighter_Evolved_Tests_Rendering extends WP_UnitTestCase {
	const ENCODED_PAYLOAD = '&lt;img src=x onerror=alert(1)&gt;';

	/**
	 * Renders a SyntaxHighlighter Code block through the full `the_content` pipeline.
	 *
	 * @param string $attributes The block's attributes, as JSON.
	 * @param string $code       The block's (entity encoded) code.
	 *
	 * @return string The rendered content.
	 */
	private function render_block( $attributes, $code = self::ENCODED_PAYLOAD ) {
		return apply_filters(
			'the_content',
			'<!-- wp:syntaxhighlighter/code ' . $attributes . ' --><pre class="wp-block-syntaxhighlighter-code">' . $code . '</pre><!-- /wp:syntaxhighlighter/code -->'
		);
	}

	/**
	 * A positional `0` attribute must not swap a known language for an unknown one
	 * after `render_block()` has already decoded the code for the known language.
	 *
	 * @dataProvider data_block_positional_language_attributes
	 */
	public function test_block_positional_language_does_not_print_decoded_code( $attributes ) {
		$rendered = $this->render_block( $attributes );

		$this->assertStringNotContainsString( '<img', $rendered );
		$this->assertStringContainsString( self::ENCODED_PAYLOAD, $rendered );
	}

	public static function data_block_positional_language_attributes() {
		return array(
			'unknown language' => array( '{"language":"php","0":"zzz"}' ),
			'quoted unknown'   => array( '{"language":"php","0":"=\"zzz\""}' ),
			'empty language'   => array( '{"language":"php","0":""}' ),
			'known language'   => array( '{"language":"zzz","0":"php"}' ),
		);
	}

	/**
	 * Whatever combination of language attributes a block has, its code is never printed as markup.
	 *
	 * @dataProvider data_block_language_attributes
	 */
	public function test_block_never_prints_code_as_markup( $attributes ) {
		$this->assertStringNotContainsString( '<img', $this->render_block( $attributes ) );
	}

	public static function data_block_language_attributes() {
		return array(
			'no attributes'           => array( '' ),
			'known language'          => array( '{"language":"php"}' ),
			'unknown language'        => array( '{"language":"zzz"}' ),
			'uppercase language'      => array( '{"language":"PHP"}' ),
			'lang alias, unknown'     => array( '{"language":"php","lang":"zzz"}' ),
			'lang alias, known'       => array( '{"language":"zzz","lang":"php"}' ),
			'type alias, unknown'     => array( '{"language":"php","type":"zzz"}' ),
			'false language'          => array( '{"language":false,"lang":"php"}' ),
			'empty language'          => array( '{"language":""}' ),
		);
	}

	public function test_unknown_language_escapes_code() {
		$rendered = $GLOBALS['SyntaxHighlighter']->shortcode_callback( array( 'language' => 'zzz' ), '<img src=x onerror=alert(1)>', 'code' );

		$this->assertSame( '&lt;img src=x onerror=alert(1)&gt;', $rendered );
	}

	public function test_unknown_language_does_not_double_encode_code() {
		$rendered = $GLOBALS['SyntaxHighlighter']->shortcode_callback( array( 'language' => 'zzz' ), 'if ( $a &lt; $b &amp;&amp; $c )', 'code' );

		$this->assertSame( 'if ( $a &lt; $b &amp;&amp; $c )', $rendered );
	}

	/**
	 * The editor saves `<` and `&` as entities. They have to be displayed as the characters that were typed,
	 * whatever the language, and whether or not the post is marked as having encoded shortcodes.
	 *
	 * @dataProvider data_block_escaping
	 */
	public function test_block_code_is_escaped_exactly_once( $attributes, $encoded_shortcodes ) {
		$post_id = self::factory()->post->create();
		if ( $encoded_shortcodes ) {
			add_post_meta( $post_id, '_syntaxhighlighter_encoded', true );
		}
		$GLOBALS['post'] = get_post( $post_id );

		$rendered = $this->render_block( $attributes, "&lt;foo>\nbar &amp; baz\n&amp;lt;" );

		// Other tests shouldn't be affected by this post
		unset( $GLOBALS['post'] );

		$this->assertStringContainsString( "&lt;foo&gt;\nbar &amp; baz\n&amp;lt;</pre>", $rendered );
	}

	public static function data_block_escaping() {
		return array(
			'default language'                    => array( '', false ),
			'default language, encoded post'      => array( '', true ),
			'plain text'                          => array( '{"language":"plain"}', false ),
			'php'                                 => array( '{"language":"php"}', false ),
			'php, encoded post'                   => array( '{"language":"php"}', true ),
			'uppercase language'                  => array( '{"language":"PHP"}', false ),
			'default language with other options' => array( '{"lineNumbers":false}', false ),
		);
	}

	public function test_block_without_a_language_is_plain_text() {
		$rendered = $this->render_block( '{"lang":"zzz","type":"php"}', 'echo 1;' );

		$this->assertStringContainsString( '<pre class="brush: plain;', $rendered );
	}

	public function test_block_with_an_empty_language_is_plain_text() {
		$rendered = $this->render_block( '{"language":""}', "&lt;foo>\nbar" );

		$this->assertStringContainsString( '<pre class="brush: plain;', $rendered );
		$this->assertStringContainsString( "&lt;foo&gt;\nbar</pre>", $rendered );
	}

	public function test_code_format_is_restored_when_rendering_a_block_fails() {
		$throw = function () {
			throw new Exception( 'Failed' );
		};
		add_filter( 'syntaxhighlighter_htmlresult', $throw );

		$thrown = false;
		try {
			$GLOBALS['SyntaxHighlighter']->render_block( array( 'language' => 'php' ), '<pre class="wp-block-syntaxhighlighter-code">echo 1;</pre>' );
		} catch ( Exception $e ) {
			$thrown = true;
		}
		remove_filter( 'syntaxhighlighter_htmlresult', $throw );

		$this->assertTrue( $thrown );
		$this->assertFalse( $GLOBALS['SyntaxHighlighter']->codeformat );
	}

	public function test_known_language_block_is_highlighted() {
		$rendered = $this->render_block( '{"language":"php"}', '&lt;?php echo 1; ?&gt;' );

		$this->assertStringContainsString( 'brush: php;', $rendered );
		$this->assertStringContainsString( '&lt;?php echo 1; ?&gt;', $rendered );
	}

	public function test_block_settings_are_passed_to_the_highlighter() {
		$rendered = $this->render_block( '{"language":"php","lineNumbers":false,"firstLineNumber":"5","highlightLines":"5,7-8","className":"my-class"}', "echo 1;\necho 2;\necho 3;\necho 4;" );

		$this->assertStringContainsString( 'brush: php; first-line: 5; gutter: false; highlight: [5,7,8];', $rendered );
		$this->assertStringContainsString( 'wp-block-syntaxhighlighter-code my-class', $rendered );
	}

	/**
	 * Block attributes come straight from the post content, so they can be of any type.
	 *
	 * @dataProvider data_block_non_scalar_attributes
	 */
	public function test_block_ignores_non_scalar_attributes( $attributes ) {
		$rendered = $this->render_block( $attributes, 'echo 1;' );

		$this->assertStringContainsString( 'echo 1;', $rendered );
		$this->assertStringNotContainsString( 'Array', $rendered );
	}

	public static function data_block_non_scalar_attributes() {
		return array(
			'language'       => array( '{"language":["php"]}' ),
			'title'          => array( '{"language":"php","title":["x"]}' ),
			'lineNumbers'    => array( '{"language":"php","lineNumbers":{"a":1}}' ),
			'highlightLines' => array( '{"language":"php","highlightLines":["1"]}' ),
			'align'          => array( '{"language":"php","align":["x"]}' ),
			'null language'  => array( '{"language":null}' ),
			'number'         => array( '{"language":1.5,"title":2}' ),
		);
	}

	/**
	 * @dataProvider data_highlights
	 */
	public function test_highlight( $attributes, $expected ) {
		$rendered = apply_filters( 'the_content', '[code ' . $attributes . "]one\ntwo\nthree\nfour\nfive[/code]" );

		if ( null === $expected ) {
			$this->assertStringNotContainsString( 'highlight:', $rendered );
		} else {
			$this->assertStringContainsString( ' highlight: [' . $expected . '];', $rendered );
		}
	}

	public static function data_highlights() {
		return array(
			'single line'              => array( 'highlight="3"', '3' ),
			'list'                     => array( 'highlight="4,2"', '2,4' ),
			'range'                    => array( 'highlight="2-4"', '2,3,4' ),
			'list and ranges'          => array( 'highlight="5,1-2,4"', '1,2,4,5' ),
			'overlapping ranges'       => array( 'highlight="2-4,1-3,3-5"', '1,2,3,4,5' ),
			'range past the last line' => array( 'highlight="4-99999999999"', '4,5' ),
			'range outside the code'   => array( 'highlight="2,100-99999999999"', '2' ),
			'only out of range'        => array( 'highlight="100-99999999999"', null ),
			'backwards range'          => array( 'highlight="4-2,3"', '3' ),
			'different first line'     => array( 'firstline="10" highlight="1-12,14"', '10,11,12,14' ),
		);
	}

	/**
	 * A huge range used to build a list of every number in it, however short the code was.
	 * Shortcodes are parsed in comments, so anyone able to comment could use up all the memory.
	 */
	public function test_huge_highlight_range_in_a_comment_is_limited_to_the_code() {
		$rendered = apply_filters( 'comment_text', '[code highlight="1-99999999999,' . str_repeat( '1-99999999999,', 1000 ) . "2-88888888888\"]one\ntwo[/code]" );

		$this->assertStringContainsString( ' highlight: [1,2];', $rendered );
		$this->assertLessThan( 1000, strlen( $rendered ) );
	}

	public function test_huge_first_line_does_not_overflow() {
		$rendered = apply_filters( 'the_content', '[code firstline="9999999999999999999999" highlight="1-99999999999999999999999"]' . "one\ntwo[/code]" );

		$this->assertStringContainsString( '<pre class="brush: plain;', $rendered );
		$this->assertLessThan( 1000, strlen( $rendered ) );
	}

	/**
	 * The highlighter scripts loop as many times as these numbers say, in the browser of everyone viewing the page.
	 *
	 * @dataProvider data_integer_limits
	 */
	public function test_numbers_are_limited( $attributes, $expected ) {
		$rendered = apply_filters( 'comment_text', '[code ' . $attributes . ']echo 1;[/code]' );

		$this->assertStringContainsString( ' ' . $expected . ';', $rendered );
	}

	public static function data_integer_limits() {
		return array(
			'tab size'                 => array( 'tabsize="8"', 'tab-size: 8' ),
			'huge tab size'            => array( 'tabsize="2000000000"', 'tab-size: 32' ),
			'negative tab size'        => array( 'tabsize="-5"', 'tab-size: 0' ),
			'padding'                  => array( 'padlinenumbers="4"', 'pad-line-numbers: 4' ),
			'automatic padding'        => array( 'padlinenumbers="true"', 'pad-line-numbers: true' ),
			'no padding'               => array( 'padlinenumbers="false"', 'pad-line-numbers: false' ),
			'huge padding'             => array( 'padlinenumbers="999999999"', 'pad-line-numbers: 20' ),
			'negative padding'         => array( 'padlinenumbers="-3"', 'pad-line-numbers: 0' ),
			'font size'                => array( 'fontsize="120"', 'font-size: 120%' ),
			'huge font size'           => array( 'fontsize="99999999"', 'font-size: 1000%' ),
			'first line isn\'t limited' => array( 'firstline="5000000"', 'first-line: 5000000' ),
		);
	}

	/**
	 * Reads the parameters out of a rendered <pre>'s class the way the highlighter scripts do.
	 *
	 * This is the regular expression from parseParams() in shCore.js. It doesn't need parameters to be separated
	 * by anything, so any "name: value" in the class is a parameter.
	 *
	 * @param string $rendered The rendered shortcode.
	 *
	 * @return array {
	 *     @type array  $parameters The parameters, as name => value.
	 *     @type string $leftover   What's left of the class without them.
	 * }
	 */
	private function parse_highlighter_parameters( $rendered ) {
		$this->assertSame( 1, preg_match( '#^<pre class="([^"]*)"( title="[^"]*")?>#', $rendered, $pre ), $rendered );

		$class = html_entity_decode( $pre[1], ENT_QUOTES );
		$regex = '/([\w-]+)\s*:\s*([\w%#-]+|\[.*?\]|".*?"|\'.*?\')\s*;?/';

		preg_match_all( $regex, $class, $matches, PREG_SET_ORDER );

		$parameters = array();
		foreach ( $matches as $match ) {
			$parameters[ $match[1] ] = $match[2];
		}

		return array(
			'parameters' => $parameters,
			'leftover'   => trim( preg_replace( $regex, '', $class ) ),
		);
	}

	/**
	 * Whatever is put in a shortcode's attribute, it can only ever set that attribute's own parameter,
	 * and only to a value of the right kind.
	 *
	 * @dataProvider data_hostile_attributes
	 */
	public function test_attribute_can_only_set_its_own_parameter( $attribute, $value ) {
		$formats = array(
			'brush'            => '/^plain$/',
			'auto-links'       => '/^(true|false)$/',
			'class-name'       => '/^\'[a-zA-Z0-9 _-]*\'$/',
			'collapse'         => '/^(true|false)$/',
			'first-line'       => '/^-?\d+$/',
			'font-size'        => '/^\d+%$/',
			'gutter'           => '/^(true|false)$/',
			'highlight'        => '/^\[\d+(,\d+)*\]$/',
			'html-script'      => '/^(true|false)$/',
			'light'            => '/^(true|false)$/',
			'pad-line-numbers' => '/^(true|false|\d+)$/',
			'smart-tabs'       => '/^(true|false)$/',
			'tab-size'         => '/^\d+$/',
			'toolbar'          => '/^(true|false)$/',
			'wrap-lines'       => '/^(true|false)$/',
			'quick-code'       => '/^(true|false)$/',
		);
		$names   = array(
			'autolinks'      => 'auto-links',
			'classname'      => 'class-name',
			'firstline'      => 'first-line',
			'fontsize'       => 'font-size',
			'htmlscript'     => 'html-script',
			'padlinenumbers' => 'pad-line-numbers',
			'smarttabs'      => 'smart-tabs',
			'tabsize'        => 'tab-size',
			'wraplines'      => 'wrap-lines',
			'quickcode'      => 'quick-code',
		);
		$own     = isset( $names[ $attribute ] ) ? $names[ $attribute ] : $attribute;

		$rendered = $GLOBALS['SyntaxHighlighter']->shortcode_callback( array( $attribute => $value ), "one\ntwo\nthree", 'code' );
		$parsed   = $this->parse_highlighter_parameters( $rendered );

		$this->assertSame( 'notranslate', $parsed['leftover'], $rendered );

		foreach ( $parsed['parameters'] as $name => $parameter ) {
			$this->assertContains( $name, array( 'brush', $own ), $rendered );
			$this->assertArrayHasKey( $name, $formats, $rendered );
			$this->assertMatchesRegularExpression( $formats[ $name ], $parameter, $rendered );
		}
	}

	public static function data_hostile_attributes() {
		$attributes = array( 'autolinks', 'classname', 'collapse', 'firstline', 'fontsize', 'gutter', 'highlight', 'htmlscript', 'light', 'padlinenumbers', 'smarttabs', 'tabsize', 'title', 'toolbar', 'wraplines', 'quickcode' );
		$values     = array(
			'another parameter'   => 'see pad-line-numbers: 999999999',
			'after a semicolon'   => 'true; tab-size: 2000000000; smart-tabs: false',
			'without spaces'      => '1;tab-size:2000000000',
			'in single quotes'    => "x' tab-size: 2000000000 '",
			'in double quotes'    => 'x" tab-size: 2000000000 "',
			'a quoted value'      => "see class-name: 'a\" onmouseover=\"alert(1)'",
			'a list'              => '1,2 highlight: [1,2,3] collapse: true',
			'on another line'     => "4\ntab-size: 2000000000",
			'as entities'         => 'see tab-size&#58; 2000000000 pad-line-numbers&colon; 999999999',
			'a different brush'   => 'x brush: php; gutter: false',
		);

		$data = array();
		foreach ( $attributes as $attribute ) {
			foreach ( $values as $name => $value ) {
				$data[ $attribute . ', ' . $name ] = array( $attribute, $value );
			}
		}

		return $data;
	}

	/**
	 * Sites can add parameters of their own with filters. Their values can't add other parameters either.
	 */
	public function test_custom_parameter_can_only_have_a_single_word_as_its_value( $value = "see tab-size: 2000000000 'x' [1]", $expected = array( 'brush' => 'plain' ) ) {
		$add_attribute = function ( $atts ) use ( $value ) {
			$atts['custom'] = $value;

			return $atts;
		};
		$allow         = function ( $allowed ) {
			$allowed['custom'] = 'other';

			return $allowed;
		};
		add_filter( 'syntaxhighlighter_shortcodeatts', $add_attribute );
		add_filter( 'syntaxhighlighter_allowedatts', $allow );

		$rendered = $GLOBALS['SyntaxHighlighter']->shortcode_callback( array(), 'one', 'code' );

		remove_filter( 'syntaxhighlighter_shortcodeatts', $add_attribute );
		remove_filter( 'syntaxhighlighter_allowedatts', $allow );

		$parsed = $this->parse_highlighter_parameters( $rendered );

		$this->assertSame( $expected, $parsed['parameters'] );
		$this->assertSame( 'notranslate', $parsed['leftover'] );
	}

	public function test_custom_parameter_with_a_single_word_is_kept() {
		$this->test_custom_parameter_can_only_have_a_single_word_as_its_value(
			'dark-mode_2',
			array(
				'brush'  => 'plain',
				'custom' => 'dark-mode_2',
			)
		);
	}

	public function test_title_is_only_in_the_title_attribute() {
		$rendered = apply_filters( 'the_content', '[code title="My <b>example</b> &amp; more" collapse="true"]echo 1;[/code]' );

		$this->assertStringContainsString( '<pre class="brush: plain; collapse: true; light: false; toolbar: true; notranslate" title="My example &amp; more">', $rendered );
	}

	public function test_code_without_a_title_has_no_title_attribute() {
		$rendered = apply_filters( 'the_content', '[code]echo 1;[/code]' );

		$this->assertStringContainsString( '<pre class="brush: plain; notranslate">', $rendered );
	}

	public function test_type_is_a_language_alias() {
		$rendered = apply_filters( 'the_content', '[code type="php"]echo 1;[/code]' );

		$this->assertStringContainsString( '<pre class="brush: php;', $rendered );
	}

	public function test_unknown_type_is_still_highlighted_as_plain_text() {
		$rendered = apply_filters( 'the_content', '[code type="zzz"]echo 1;[/code]' );

		$this->assertStringContainsString( '<pre class="brush: plain;', $rendered );
	}

	public function test_language_wins_over_type() {
		$rendered = apply_filters( 'the_content', '[code lang="js" type="php"]echo 1;[/code]' );

		$this->assertStringContainsString( '<pre class="brush: jscript;', $rendered );
	}

	/**
	 * @dataProvider data_shortcodes
	 */
	public function test_shortcode_language_forms( $shortcode, $expected_brush ) {
		$rendered = apply_filters( 'the_content', $shortcode );

		$this->assertStringContainsString( '<pre class="brush: ' . $expected_brush . ';', $rendered );
		$this->assertStringContainsString( 'echo 1;', $rendered );
	}

	public static function data_shortcodes() {
		return array(
			'language attribute' => array( '[code language="php"]echo 1;[/code]', 'php' ),
			'lang attribute'     => array( '[code lang="js"]echo 1;[/code]', 'jscript' ),
			'quoted positional'  => array( '[code="php"]echo 1;[/code]', 'php' ),
			'bare positional'    => array( '[sourcecode php]echo 1;[/sourcecode]', 'php' ),
			'language tag'       => array( '[php]echo 1;[/php]', 'php' ),
			'no language'        => array( '[code]echo 1;[/code]', 'plain' ),
		);
	}
}

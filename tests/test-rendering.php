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

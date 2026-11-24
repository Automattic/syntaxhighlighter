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

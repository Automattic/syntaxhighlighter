<?php

require_once( __DIR__ . '/helper.php' );

class SyntaxHighlighter_Tests_Render extends WP_UnitTestCase {
	public function test_shortcode_basic() {
		$content = apply_filters( 'the_content', '[code]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_language_as_tag() {
		$content = apply_filters( 'the_content', '[php]Hello World[/php]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: php; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: php; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_language_as_attribute_lang() {
		$content = apply_filters( 'the_content', '[code lang="php"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: php; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: php; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_language_as_attribute_language() {
		$content = apply_filters( 'the_content', '[code language="php"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: php; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: php; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_true() {
		$content = apply_filters( 'the_content', '[code light="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_1() {
		$content = apply_filters( 'the_content', '[code light="1"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_on() {
		$content = apply_filters( 'the_content', '[code light="on"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_false() {
		$content = apply_filters( 'the_content', '[code light="false"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_0() {
		$content = apply_filters( 'the_content', '[code light="0"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_off() {
		$content = apply_filters( 'the_content', '[code light="off"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_boolean_invalid() {
		$content = apply_filters( 'the_content', '[code light="hello"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; notranslate">Hello World</pre>' . "\n", $content );
		}
	}
}
<?php

require_once( __DIR__ . '/helper.php' );

class SyntaxHighlighter_Tests_Render extends WP_UnitTestCase {
	public $instances = array();

	function __construct() {
		//remove_action( 'plugins_loaded', 'SyntaxHighlighter' );

		$this->instances = array(
			'sh2' => new SyntaxHighlighter( array( 'renderer' => 'sh2' ) ),
			'sh3' => new SyntaxHighlighter( array( 'renderer' => 'sh3' ) ),
		);
	}

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

	public function test_shortcode_language_invalid() {
		$content = apply_filters( 'the_content', '[code lang="helloworld"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<p>Hello World</p>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; notranslate">Hello World</pre>' . "\n", $content );
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
		$content = apply_filters( 'the_content', '[code light="helloworld"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_integer_number() {
		$content = apply_filters( 'the_content', '[code firstline="10"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; first-line: 10; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; first-line: 10; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_type_integer_invalid() {
		$content = apply_filters( 'the_content', '[code firstline="helloworld"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; first-line: 0; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; first-line: 0; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_autolinks() {
		$content = apply_filters( 'the_content', '[code autolinks="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; auto-links: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; auto-links: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_classname() {
		$content = apply_filters( 'the_content', '[code classname="test-class"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; class-name: test-class; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; class-name: test-class; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_classname() {
		$content = apply_filters( 'the_content', '[code classname=\'Image: <img src="404" onerror="alert(1);" style="display: none;">\']Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; class-name: Image img src404 onerroralert1 styledisplay none; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; class-name: Image img src404 onerroralert1 styledisplay none; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_classname_valid() {
		$content = apply_filters( 'the_content', '[code classname="test-class"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; class-name: test-class; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; class-name: test-class; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_collapse() {
		$content = apply_filters( 'the_content', '[code collapse="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; collapse: true; light: false; title: ; toolbar: true; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; collapse: true; light: false; toolbar: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_firstline() {
		$content = apply_filters( 'the_content', '[code firstline="10"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; first-line: 10; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; first-line: 10; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_fontsize() {
		$content = apply_filters( 'the_content', '[code fontsize="10"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; font-size: 10%; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; font-size: 10%; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_gutter() {
		$content = apply_filters( 'the_content', '[code gutter="false"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; gutter: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; gutter: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_highlight_single() {
		$content = apply_filters( 'the_content', '[code highlight="10"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; highlight: [10]; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; highlight: [10]; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_highlight_series() {
		$content = apply_filters( 'the_content', '[code highlight="10,20,30"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; highlight: [10,20,30]; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; highlight: [10,20,30]; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_highlight_range() {
		$content = apply_filters( 'the_content', '[code highlight="10-15"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; highlight: [10,11,12,13,14,15]; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; highlight: [10,11,12,13,14,15]; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_highlight_mixed() {
		$content = apply_filters( 'the_content', '[code highlight="1,10-15,20"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; highlight: [1,10,11,12,13,14,15,20]; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; highlight: [1,10,11,12,13,14,15,20]; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_htmlscript() {
		$content = apply_filters( 'the_content', '[code htmlscript="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; html-script: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; html-script: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_light() {
		$content = apply_filters( 'the_content', '[code light="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; light: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; light: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_padlinenumbers_true() {
		$content = apply_filters( 'the_content', '[code padlinenumbers="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_padlinenumbers_1() {
		$content = apply_filters( 'the_content', '[code padlinenumbers="1"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: true; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_padlinenumbers_false() {
		$content = apply_filters( 'the_content', '[code padlinenumbers="false"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_padlinenumbers_0() {
		$content = apply_filters( 'the_content', '[code padlinenumbers="0"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_padlinenumbers_number() {
		$content = apply_filters( 'the_content', '[code padlinenumbers="3"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: 3; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; pad-line-numbers: 3; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_smarttabs() {
		$content = apply_filters( 'the_content', '[code smarttabs="false"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; smart-tabs: false; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; smart-tabs: false; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_tabsize() {
		$content = apply_filters( 'the_content', '[code tabsize="10"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; tab-size: 10; title: ; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; tab-size: 10; notranslate">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_title() {
		$content = apply_filters( 'the_content', '[code title="Title Test"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; title: Title Test; notranslate" title="Title Test">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; title: Title Test; notranslate" title="Title Test">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_sanitization_title() {
		$content = apply_filters( 'the_content', '[code title=\'Image: <img src="404" onerror="alert(1);" style="display: none;">\']Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; title: Image: ; notranslate" title="Image: ">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; title: Unknown Yet; notranslate" title="Unknown Yet">Hello World</pre>' . "\n", $content );
		}
	}

	public function test_shortcode_attribute_wraplines() {
		$content = apply_filters( 'the_content', '[code wraplines="true"]Hello World[/code]' );

		if ( SyntaxHighlighter_Tests_Helper::is_3x() ) {
			$this->assertEquals( '<pre class="brush: plain; title: ; wrap-lines: true; notranslate" title="">Hello World</pre>' . "\n", $content );
		} else {
			$this->assertEquals( '<pre class="brush: plain; wrap-lines: true; notranslate">Hello World</pre>' . "\n", $content );
		}
	}
}
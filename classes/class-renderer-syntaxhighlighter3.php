<?php

require_once( __DIR__ . '/abstract-renderer.php' );

class SyntaxHighlighter_Renderer_SH3 extends SyntaxHighlighter_Renderer {

	public function set_languages() {
		$this->languages = array(
			'foo'   => 'foo',
			'bar'   => 'foo',
			'latex' => 'test',
		);
	}

	public function set_shortcodes() {
		parent::set_shortcodes();

		// Remove some shortcodes we don't want while still supporting them as language values
		unset( $this->shortcodes[ array_search( 'latex', $this->shortcodes ) ] ); // Remove "latex" shortcode (it'll collide with rendering shortcodes)
		unset( $this->shortcodes[ array_search( 'r', $this->shortcodes ) ] );     // Remove "r" shortcode (too short)
	}

	public function shortcode_callback( $atts, $code = '', $tag = false ) {
		return 'Hello world!';
	}
}
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

		# Remove some shortcodes we don't want while still supporting them as language values

		// "latex" will often collide with LaTeX rendering shortcodes
		if ( in_array( 'latex', $this->shortcodes ) ) {
			unset( $this->shortcodes[ array_search( 'latex', $this->shortcodes ) ] );
		}

		// "r" is just too short to be useful
		if ( in_array( 'r', $this->shortcodes ) ) {
			unset( $this->shortcodes[ array_search( 'r', $this->shortcodes ) ] );
		}
	}

	public function set_defaults( $atts ) {
		$defaults = array(
			'language'       => null,
			'lang'           => null,
			'type'           => null, // deprecated language alias

			'autolinks'      => null,
			'classname'      => null,
			'collapse'       => null,
			'firstline'      => null,
			'gutter'         => null,
			'highlight'      => null,
			'htmlscript'     => null,
			'light'          => null,
			'padlinenumbers' => null,
			'quickcode'      => null,
			'smarttabs'      => null,
			'tabsize'        => null,
			'title'          => null,
			'toolbar'        => null,
			'unindent'       => null,
		);

		$atts = apply_filters( 'syntaxhighlighter_shortcodeatts', shortcode_atts( $defaults, $atts ) );

		return $atts;
	}
}
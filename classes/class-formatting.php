<?php

class SyntaxHighlighter_Formatting {
	public $core;

	public function __construct( $core ) {
		$this->core = $core;
	}

	public function parse_specific_shortcodes( $content, $callback, $shortcodes = array() ) {
		global $shortcode_tags;

		if ( empty( $shortcodes ) ) {
			$shortcodes = $this->core->shortcodes;
		}

		// Backup current registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		// Re-register each of the shortcodes we want to parse
		foreach ( $shortcodes as $shortcode ) {
			add_shortcode( $shortcode, $callback );
		}

		// Process all registered shortcodes but keep any escaped shortcodes like [[foobar]]
		$content = $this->do_shortcode_keep_escaped_shortcodes( $content );

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}

	/**
	 * Processes all registered shortcodes just like do_shortcode() except
	 * that escaped shortcodes like [[foobar]] are not touched.
	 * Normally one set of brackets is stripped off.
	 *
	 * This is an exact copy of do_shortcode() as of r31245 (4.2-alpha)
	 * but just with a different callback function.
	 *
	 * @global array $shortcode_tags List of shortcode tags and their callback hooks.
	 *
	 * @param string $content Content to search for shortcodes.
	 * @return string Content with shortcodes filtered out.
	 */
	public function do_shortcode_keep_escaped_shortcodes( $content ) {
		global $shortcode_tags;

		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}

		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;

		$pattern = get_shortcode_regex();
		return preg_replace_callback( "/$pattern/s", array( $this, 'do_shortcode_tag_keep_escaped_shortcodes' ), $content );
	}

	/**
	 * Callback for do_shortcode_keep_escaped_shortcodes().
	 *
	 * This is an exact copy of do_shortcode_tag() as of r31245 (4.2-alpha)
	 * but one of the returns has been modified. See below.
	 *
	 * @uses $shortcode_tags
	 *
	 * @param array $m Regular expression match array
	 * @return mixed False on failure.
	 */
	public function do_shortcode_tag_keep_escaped_shortcodes( $m ) {
		global $shortcode_tags;

		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			/**
			 * This is the modified line compared to do_shortcode_tag().
			 * The substr() call has been removed.
			 */
			return $m[0];
		}

		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		if ( isset( $m[5] ) ) {
			// enclosing tag - extra parameter
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
		} else {
			// self-closing tag
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, null,  $tag ) . $m[6];
		}
	}

	public function convert_atts_to_string( $atts ) {
		if ( empty( $atts ) ) {
			return '';
		}

		$strings = array();

		foreach ( $atts as $key => $value ) {
			$strings[] = $key . '="' . esc_attr( $value ) . '"';
		}

		return ' ' . implode( ' ', $strings );
	}

	public function escape_shortcode_contents( $text, $shortcodes = array() ) {
		return $this->parse_specific_shortcodes( $text, array( $this, 'escape_shortcode_contents_callback' ), $shortcodes );
	}

	public function escape_shortcode_contents_callback( $atts, $content, $tag ) {
		return '[' . $tag . $this->convert_atts_to_string( $atts ) . ']' . esc_html( $content ) . '[/' . $tag . ']';
	}
}
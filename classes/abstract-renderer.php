<?php

abstract class SyntaxHighlighter_Renderer {
	public $core;

	public $themes = array();
	public $languages = array();
	public $shortcodes = array();

	function __construct( $core ) {
		$this->core = $core;

		$this->init();
	}

	public function init() {
		$this->set_languages();

		$this->set_shortcodes();
		/**
		 * Use this filter to remove any shortcodes you don't want to use.
		 * Do not use this filter to add any additional shortcodes as it won't work.
		 * Instead you'll need to register the new language or alias with this plugin.
		 */
		$this->shortcodes = (array) apply_filters( 'syntaxhighlighter_shortcodes', $this->shortcodes, $this->core );

		$this->register_hooks();
		$this->register_placeholder_shortcodes();
	}

	public function set_languages() {
		$this->languages = array();
	}

	public function set_shortcodes() {
		$this->shortcodes = array_merge( array( 'code', 'sourcecode', 'source' ), array_keys( $this->languages ) );
	}

	public function register_hooks() {
		// Display hooks
		add_filter( 'the_content',                        array( $this, 'parse_shortcodes' ),                              7 ); // Posts
//		add_filter( 'comment_text',                       array( $this, 'parse_shortcodes_comment' ),                      7 ); // Comments
		add_filter( 'bp_get_the_topic_post_content',      array( $this, 'parse_shortcodes' ),                              7 ); // BuddyPress

/*
		// Into the database
		add_filter( 'content_save_pre',                   array( $this, 'encode_shortcode_contents_slashed_noquickedit' ), 1 ); // Posts
		add_filter( 'pre_comment_content',                array( $this, 'encode_shortcode_contents_slashed' ),             1 ); // Comments
		add_filter( 'group_forum_post_text_before_save',  array( $this, 'encode_shortcode_contents_slashed' ),             1 ); // BuddyPress
		add_filter( 'group_forum_topic_text_before_save', array( $this, 'encode_shortcode_contents_slashed' ),             1 ); // BuddyPress

		// Out of the database for editing
		add_filter( 'the_editor_content',                 array( $this, 'the_editor_content' ),                            1 ); // Posts
		add_filter( 'comment_edit_pre',                   array( $this, 'decode_shortcode_contents' ),                     1 ); // Comments
		add_filter( 'bp_get_the_topic_text',              array( $this, 'decode_shortcode_contents' ),                     1 ); // BuddyPress
		add_filter( 'bp_get_the_topic_post_edit_text',    array( $this, 'decode_shortcode_contents' ),                     1 ); // BuddyPress

		// Register widget hooks
		add_filter( 'widget_text',                        array( $this, 'widget_text_output' ),                            7, 2 );
		add_filter( 'widget_update_callback',             array( $this, 'widget_text_save' ),                              1, 4 );
		add_filter( 'widget_form_callback',               array( $this, 'widget_text_form' ),                              1, 2 );

		// Exempt shortcodes from wptexturize()
		add_filter( 'no_texturize_shortcodes',            array( $this, 'no_texturize_shortcodes' ) );
*/
	}

	public function register_placeholder_shortcodes() {
		foreach ( $this->shortcodes as $shortcode ) {
			add_shortcode( $shortcode, '__return_true' );
		}
	}

	public function parse_shortcodes( $content ) {
		return $this->parse_specific_shortcodes( $content, $this->shortcodes, array( $this, 'shortcode_callback' ) );
	}

	public function parse_specific_shortcodes( $content, $shortcodes, $callback ) {
		global $shortcode_tags;

		// Backup currently registered shortcodes and clear them all out
		$orig_shortcode_tags = $shortcode_tags;
		remove_all_shortcodes();

		// Register just the shortcodes we want to parse
		foreach ( $shortcodes as $shortcode ) {
			add_shortcode( $shortcode, $callback );
		}

		// Parse the shortcodes -- only the ones registered above will be processed
		$content = $this->do_shortcode_keep_escaped_tags( $content );

		// Put the original shortcodes back
		$shortcode_tags = $orig_shortcode_tags;

		return $content;
	}

	/**
	 * This function is identical to core's do_shortcode() but uses a different
	 * callback function that won't touch escaped shortcodes, like [[shortcode]].
	 *
	 * This copy was taken from revision 27394.
	 *
	 * @see do_shortcode()
	 *
	 * @uses $shortcode_tags
	 * @uses get_shortcode_regex() Gets the search pattern for searching shortcodes.
	 *
	 * @param string $content Content to search for shortcodes.
	 * @return string Content with shortcodes filtered out.
	 */
	public function do_shortcode_keep_escaped_tags( $content ) {
		global $shortcode_tags;

		if ( false === strpos( $content, '[' ) ) {
			return $content;
		}

		if (empty($shortcode_tags) || !is_array($shortcode_tags))
			return $content;

		$pattern = get_shortcode_regex();
		return preg_replace_callback( "/$pattern/s", array( $this, 'do_shortcode_tag_keep_escaped_tags' ), $content );
	}

	/**
	 * Regular Expression callable for SyntaxHighlighter_Renderer::do_shortcode_keep_escaped_tags()
	 * for calling shortcode hook. This is a copy of core's do_shortcode_tag() but differs in that
	 * it won't touch escaped shortcodes, like [[shortcode]].
	 *
	 * This copy was taken from revision 27394.
	 *
	 * @see do_shortcode_tag()
	 *
	 * @uses $shortcode_tags
	 *
	 * @param array $m Regular expression match array.
	 * @return mixed False on failure.
	 */
	public function do_shortcode_tag_keep_escaped_tags( $m ) {
		global $shortcode_tags;

		// allow [[foo]] syntax for escaping a tag
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return $m[0]; // This is the line that was modified and differs from core's do_shortcode_tag()
		}

		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		if ( isset( $m[5] ) ) {
			// enclosing tag - extra parameter
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, $m[5], $tag ) . $m[6];
		} else {
			// self-closing tag
			return $m[1] . call_user_func( $shortcode_tags[$tag], $attr, NULL,  $tag ) . $m[6];
		}
	}

	public function shortcode_callback( $atts, $code = '', $tag = false ) {
		$code = apply_filters( 'syntaxhighlighter_precode', $code, $atts, $tag );

		if ( ! $code ) {
			return $code;
		}

		if ( ! $tag ) {
			return '<pre>' . esc_html( $code ) . '</pre>';
		}

		// Error fixing for [shortcode="language"]
		if ( isset( $atts[0] ) ) {
			$atts = $this->fix_no_name_attribute( $atts );
			$atts['language'] = $atts[0];
			unset( $atts[0] );
		}

		$atts = $this->set_any_missing_attributes( $atts );

		return '<pre>' . $code . '</pre>';
	}

	/**
	 * No-name attribute fixing, i.e. [shortcode="foobar"].
	 * When a user does that, you get $atts[0] set to '="foo"'.
	 *
	 * @see https://core.trac.wordpress.org/ticket/7045
	 *
	 * @param array $atts Array of shortcode attributes.
	 *
	 * @return array
	 */
	public function fix_no_name_attribute( $atts = array() ) {
		if ( empty( $atts[0] ) ) {
			return $atts;
		}

		// Quoted value
		if ( 0 !== preg_match( '#=("|\')(.*?)\1#', $atts[0], $match ) ) {
			$atts[0] = $match[2];
		}

		// Unquoted value
		elseif ( '=' == substr( $atts[0], 0, 1 ) ) {
			$atts[0] = substr( $atts[0], 1 );
		}

		return $atts;
	}

	public function set_any_missing_attributes( $atts ) {
		return apply_filters( 'syntaxhighlighter_shortcodeatts', $atts );
	}
}
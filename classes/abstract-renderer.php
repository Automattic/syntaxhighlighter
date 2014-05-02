<?php

abstract class SyntaxHighlighter_Renderer {
	public $core;

	function __construct( $core ) {
		$this->core = $core;
	}

	public function register_hooks() {
		// Display hooks
		add_filter( 'the_content',                        array( $this, 'parse_shortcodes' ),                              7 ); // Posts
		add_filter( 'comment_text',                       array( $this, 'parse_shortcodes_comment' ),                      7 ); // Comments
		add_filter( 'bp_get_the_topic_post_content',      array( $this, 'parse_shortcodes' ),                              7 ); // BuddyPress

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
	}
}
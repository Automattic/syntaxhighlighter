<?php

class SyntaxHighlighter_Tests_Helper {
	public static function is_3x() {
		global $SyntaxHighlighter;

		return version_compare( $SyntaxHighlighter->pluginver, '4.0.0', '<' );
	}
}
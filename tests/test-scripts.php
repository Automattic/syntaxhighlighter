<?php
/**
 * Class SyntaxHighlighter_Evolved_Tests_Scripts
 *
 * @package    SyntaxHighlighter_Evolved
 */

/**
 * Tests for the scripts printed in the footer.
 */
class SyntaxHighlighter_Evolved_Tests_Scripts extends WP_UnitTestCase {
	public function test_footer_script_rehighlights_after_infinite_scroll() {
		apply_filters( 'the_content', '[php]echo 1;[/php]' );

		ob_start();
		$GLOBALS['SyntaxHighlighter']->maybe_output_scripts();
		$output = ob_get_clean();

		// The event Jetpack's Infinite Scroll dispatches, and the jQuery event it and some themes trigger.
		$this->assertStringContainsString( "document.addEventListener( 'is.post-load'", $output );
		$this->assertStringContainsString( "$( document.body ).on( 'post-load'", $output );
	}
}

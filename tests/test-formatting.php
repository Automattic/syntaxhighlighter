<?php

/**
 * @group formatting
 */
class SyntaxHighlighter_Tests_Formatting extends WP_UnitTestCase {

	/**
	 * @dataProvider provider_convert_atts_to_string
	 */
	public function test_convert_atts_to_string( $atts, $expected ) {
		$this->assertEquals( $expected, SyntaxHighlighter()->helpers->formatting->convert_atts_to_string( $atts ) );
	}

	public function provider_convert_atts_to_string() {
		return array(
			array(
				array(),
				'',
			),
			array(
				array( 'foo' => 'bar' ),
				' foo="bar"',
			),
			array(
				array( 'foo' => 'bar', 'fruit' => 'apple' ),
				' foo="bar" fruit="apple"',
			),
		);
	}
}
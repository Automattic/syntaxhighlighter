<?php

class SyntaxHighlighter_Evolved_Tests_Helper {

	public static function delete_upload_dir_contents() {
		$upload_dir = wp_get_upload_dir();
		$upload_dir = $upload_dir['path'];

		if ( is_dir( $upload_dir ) ) {
			$filesystem = new WP_Filesystem_Direct( array() );
			$filesystem->rmdir( trailingslashit( $upload_dir ), true );
		}
	}

	/**
	 * Anonymous functions aren't supported in PHP 5.2.x which WordPress does still support,
	 * so here's a bunch of helper functions for making filters return certain numbers. Ugh.
	 */

	public static function return_int_1() {
		return 1;
	}

	public static function return_int_100() {
		return 100;
	}

	public static function return_int_150() {
		return 150;
	}

	public static function return_int_300() {
		return 300;
	}

	public static function return_int_350() {
		return 350;
	}

	public static function return_int_500() {
		return 500;
	}

	public static function return_int_768() {
		return 768;
	}

	public static function return_int_1024() {
		return 1024;
	}

	public static function return_int_1500() {
		return 1500;
	}
}

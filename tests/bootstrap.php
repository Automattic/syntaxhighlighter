<?php

if ( file_exists( __DIR__ . '/config.php' ) ) {
	include_once( __DIR__ . '/config.php' );
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once( $_tests_dir . '/includes/functions.php' );

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
function _manually_load_plugin() {
	require_once( dirname( __DIR__ ) . '/syntaxhighlighter.php' );
}

require_once( $_tests_dir . '/includes/bootstrap.php' );
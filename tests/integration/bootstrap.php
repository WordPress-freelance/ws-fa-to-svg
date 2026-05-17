<?php
/**
 * Bootstrap des tests d'intégration BDD réels.
 *
 * Requiert que install-wp-tests.sh ait setup une WP de test dans /tmp/wordpress-tests-lib.
 *
 * @package WS_FA_To_SVG
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

if ( ! file_exists( "$_tests_dir/includes/functions.php" ) ) {
	echo "Test suite WordPress non installée. Lance bin/install-wp-tests.sh d'abord." . PHP_EOL;
	exit( 1 );
}

// Requis depuis WP 6.1 : yoast/phpunit-polyfills.
if ( ! defined( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' ) ) {
	$polyfills = dirname( __DIR__, 2 ) . '/vendor/yoast/phpunit-polyfills';
	if ( is_dir( $polyfills ) ) {
		define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $polyfills );
	}
}

require_once "$_tests_dir/includes/functions.php";

/**
 * Charge le plugin avant les hooks WP.
 */
tests_add_filter( 'muplugins_loaded', function () {
	require_once dirname( __DIR__, 2 ) . '/ws-fa-to-svg.php';
} );

require "$_tests_dir/includes/bootstrap.php";

<?php
// disable xdebug backtrace
if ( function_exists( 'xdebug_disable' ) ) {
  xdebug_disable();
}

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
  define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
}

if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
  require_once getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/functions.php';
  tests_add_filter( 'muplugins_loaded', function() {
	// load CMB2
    require_once __DIR__ . '/../vendor/webdevstudios/cmb2/init.php';
  });
  require_once getenv( 'WP_DEVELOP_DIR' ) . 'tests/phpunit/includes/bootstrap.php';
  require_once __DIR__ . '/includes/functions.php';
}

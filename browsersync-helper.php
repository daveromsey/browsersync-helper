<?php
/*
Plugin Name: Browsersync Helper
Plugin URI: 
Description: Adds Browsersync's JavaScript snippet to the site's front/back end.
Version: 0.1.0
Author: Dave Romsey
Author URI: 
Text Domain: browsersync-helper
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Specify the plugin version.
define( 'BROWSERSYNC_HELPER_VERSION', '0.1.0' );

// Load up the Browsersync Helper Manager class which handles all functionality for the plugin.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-manager.php';
$browsersync_helper = new \BrowsersyncHelper\Manager();

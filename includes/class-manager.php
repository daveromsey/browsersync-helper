<?php
/**
 * Top-level class to handle all of Browsersync Helper's functionality.
 *
 * @since 0.1.0
 *
 * @package Browsersync Helper
 * @subpackage Browsersync Helper/Manager
 */

namespace BrowsersyncHelper;

/**
 * Top-level class to handle all of Browsersync Helper's functionality.
 *
 * @since 0.1.0
 *
 * @package Browsersync Helper
 * @subpackage Browsersync Helper/Manager
 */
class Manager {
	/**
	 * Holds the current version of the plugin.
	 *
	 * @var string
	 */	
	private $version;

	public function __construct() {
		$this->version = BROWSERSYNC_HELPER_VERSION;
		$this->dependencies_load();
		$this->locale_set();
		$this->snippet_init();
	}

	/**
	 * Loads all dependencies.
	 * 
	 * @since 0.1.0
	 */	
	private function dependencies_load() {
		require_once plugin_dir_path( __FILE__ ) . 'class-i18n.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-snippet.php';
	}

	/**
	 * Sets up plugin for translation.
	 * 
	 * @since 0.1.0
	 */		
	private function locale_set() {
		add_action( 'plugins_loaded', [ new \BrowsersyncHelper\I18n(), 'textdomain_load' ] );
	}

	/**
	 * Sets up snippet generation.
	 * 
	 * @since 0.1.0
	 */		
	private function snippet_init() {
		add_action( 'plugins_loaded', '\BrowsersyncHelper\Snippet::instance_get' );
	}	
}
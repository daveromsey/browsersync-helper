<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 1.0.0
 *
 * @package Browsersync Helper
 * @subpackage Browsersync Helper/i18n
 */

namespace BrowsersyncHelper;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 1.0.0
 * 
 * @package Browsersync Helper
 * @subpackage Browsersync Helper/I18n
 */
class I18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function textdomain_load() {
		load_plugin_textdomain(
			'browsersync-helper',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
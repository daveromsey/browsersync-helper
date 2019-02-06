# Browsersync Helper

Browsersync Helper is a WordPress plugin for developers who are using Browsersync in their project's build process. The plugin automatically inserts Browsersync's JavaScript snippet into the HTML of a WordPress site.

The JavaScrtipt snippet allows Browsersync's proxy feature to work without adding the port to the URL, which can cause various issues on WordPress sites.

Browsersync Helper can be configured to work for a theme, child theme, plugin, or mu-plugin. By default, it's configured to work for a theme (or child theme, if in use), but this can be changed by customizing the options.

## Prerequisites

You'll need to have a WordPress website installed and have Browsersync installed and configured as part of your theme's or plugin's build process. Browsersync Helper has been tested with Gulp and Webpack based projects.

## Installation

1. Clone this repository inside your WordPress plugins directory or download and extract the zip.

2. Activate the plugin.

3. Navigate to your project's directory and start the watch process so that Browsersync is watching for changes.

4. Visit the front end of your WordPress site. You should see the following message in the browser's JS console:

```
Browsersync Helper Notification: Snippet JS successfully loaded!`
```

You should see Browsersync's JS snippet in the DOM. E.g.:

```js
<script id="__bs_script__">//<![CDATA[
	document.write(
		"<script async src='http://HOST:3000/browser-sync/browser-sync-client.js?v=2.26.3'><\/script>".replace( "HOST", location.hostname ));
//]]></script>
```

5. When you save changes to a file that is being watched (`.css`, `.scss`, `.js`, `.php`, etc. ), the site should reload automatically.

Note that by default, the snippet will only be output on the front end, and the user needs to have the `administrator` capability. This behavior can be changed by [modifying the plugin's options]('modifying-default-plugin-options').

## Browsersync Configuration Example

Example of a Browsersync configuration object used in Webpack, Gulp, etc.:

```js
/**
 *
 */
{
	logSnippet: true,
	open: false,
	port: 3000,
	notify: false,
	ghostMode: false,
	files: ["**/*.php"]
}
```

## Modifying Default Plugin Options

This plugin has no UI, but the default options can be configured using the `browsersync_helper_options` filter as demonstrated below:

```php
/**
 * Options customization example.
 *
 * @param array $options plugin settings
 * @return array
 */
add_filter( 'browsersync_helper_options', 'prefix_browsersync_helper_options' );
function prefix_browsersync_helper_options( $options ) {
	/**
	 * Use Browsersync in a plugin's directory.
	 */
	$options['project_abs_path'] = plugin_dir_path(__FILE__);

	/**
	 * Use Browsersync in the active theme's directory (default behavior).
	 * This will apply to a child theme if it is active.
	 */
	//$options['project_abs_path'] = get_stylesheet_directory();

	/**
	 * Use Browsersync in the parent theme's directory.
	 */
	//$options['project_abs_path'] = get_template_directory();

	/**
	 * Manually set the version by disabling the auto version feature
	 * and specify the desired version number.
	 */
	$options['browsersync_auto_version'] = false; // Default: true. When true, overrides any manual version set.
	$options['browsersync_version'] = '2.0.0'; // Default: false

	/**
	 * Where to display snippet. Possible values: array( 'frontend', 'admin' )
	 * Default: array( 'frontend' )
	 */
	$options['snippet_locations'] = array( 'frontend', 'admin' );

	/**
	 * Capability required for snippet to be displayed. Use false to require no capability.
	 * Default: administrator
	 */
	$options['required_cap'] = 'editor';

	/**
	 * Browsersync port. This should be the same port configured for
	 * Browsersync in the project's Webpack/Gulp config.
	 * Default: 3000
	 */
	$options['port'] = '8080';

	/**
	 * Show debug messages in JS console.
	 * Default: true
	 */
	$options['debug'] = false;

	return $options;
}
```

## Modifying the JavaScript Snippet

If you'd like to modify the output of the snippet, the `browsersync_helper_snippet_output` can be used:

```php
/**
 * Filter Browsersync's snippet output
 *
 * @param string $snippet script tag output
 * @param array $options plugin settings
 * @return string
 */
add_filter( 'browsersync_helper_snippet_output', 'prefix_browsersync_helper_snippet_output', 10, 2 );
function prefix_browsersync_helper_snippet_output( $snippet, $options ) {
	// Add code to modify generated snippet or rebuild manually...

	return $snippet;
}
```

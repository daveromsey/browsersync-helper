<?php
/**
 * Browsersync Snippet
 * 
 * Adds the Browsersync JavaScript snippet to the site's HTML.
 * 
 * @package Browsersync Helper
 * @subpackage Browsersync Helper/Snippet
 */

namespace BrowsersyncHelper;

/**
 * Adds the Browsersync JavaScript snippet to the site's HTML.
 * 
 * @package Browsersync Helper
 * @subpackage Browsersync Helper/Snippet
 */
class Snippet {
	/**
	 * Holds array of plugin options.
	 * 
	 * @var array
	 */	
	private $options;

	/**
	 * Holds array messages.
   *
	 * @var array $messages
	 */
	private $messages;

	/**
	 * Single instance of the Snippet class.
	 *
	 * @var Snippet
	 */
	private static $instance;

	/**
	 * Constructor
	 * 
	 * Initializes properties and wires up snippet and messages for display.
	 * 
	 * @since  1.0.0
	 * @param array $options User-provided options.
	 * @return void
	 */
	private function __construct() {
		// Set up the messages.
		$this->messages_init();

		// Absolute directory for project using Browsersync (theme, child theme, plugin, or mu-plugin).
		// This is the top-level directory that contains the node_modules directory.	
		$this->options['project_abs_path'] = get_stylesheet_directory(); 

		// Node modules directory, relative to project base directory.
		$this->options['node_modules_directory'] = 'node_modules';

		// Flag that determines if auto version features should be used.
		$this->options['browsersync_auto_version'] = true;

		// Browsersync version. This must match the version of Browsersync in use. By default,
		// this will be determined programmatically.
		$this->options['browsersync_version'] = false;

		// Where to display snippet: array( 'frontend', 'admin' )
		$this->options['snippet_locations'] = array( 'frontend' );

		// Capability required for snippet to be displayed. Use false to require no capability.
		$this->options['required_cap'] = 'administrator';

		// Browsersync port. This should be the same port configured for Browsersync in
		//  the project's Webpack/Gulp config.
		$this->options['port'] = '3000';

		// Use async attribute for Browsersync snippet's script tag?
		$this->options['async'] = true;

		// Protocol for URL to Browsersync client. http|https
		$this->options['protocol'] = $this->snippet_protocol();

		// Show debug messages in JS console.
		$this->options['debug'] = true;

		// Filter to allow options to be customized.
		$this->options = apply_filters( 'browsersync_helper_options', $this->options );

		// Sets the Browsersync auto version.
		$this->browsersync_version_set();

		// Wire up the plugin.
		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Creates and returns the single instance of the Snippet class.
	 * @link https://carlalexander.ca/singletons-in-wordpress/
	 * 
	 * @since  1.0.0
	 * @return Snippet
	 */
	public static function instance_get() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * Wires up the snippet to be output.
	 * 
	 * @since  1.0.0
	 * @return void
	 */
	public function init() {
		// Output the snippet and/or messages to the front end and/or admin.
		if ( in_array( 'frontend', $this->options['snippet_locations'] ) ) {
			add_action( 'wp_footer', [$this, 'snippet_output'] );
			add_action( 'wp_footer', [$this, 'messages_output'], 15 ); // Messages need to fire after snippet.
		}
		
		if ( in_array( 'admin', $this->options['snippet_locations'] ) ) {
			add_action( 'admin_footer', [$this, 'snippet_output'] );
			add_action( 'admin_footer', [$this, 'messages_output'], 15 );
		}
	}

	/**
	 * Initialize array that stores messages.
	 * 
	 * @return array
	 */
	private function messages_init() {
		return $this->messages = [
			'error' => [
				'prefix' => __( 'Browsersync Helper Error: ', 'browsersync-helper' ),
				'entries' => [],
			],
			'notification' => [
				'prefix' => __( 'Browsersync Helper Notification: ', 'browsersync-helper' ),
				'entries' => [],
			],
		];
	}

	/**
	 * Returns messages as an array for the type specified.
	 * 
	 * @param string $type type of message to get. Use 'all' to return all messages.
	 * @return array
	 */
	private function messages_get( $type = 'all' ) {
		$_messages = array();

		foreach ( $this->messages as $message_type_name => $message_type ) {
			if ( 'all' === $type || $type === $message_type_name ) {
				foreach ( $message_type['entries'] as $message ) {
					$_messages[] = $message_type['prefix'] . $message;
				}
			}
		}

		return $_messages;
	}

	/**
	 * Output any messages to the JS console.
	 * 
	 * @return void
	 */
	public function messages_output() {
		// Bail if debug mode is off.
		if ( false == $this->options['debug'] ) {
			return;
		}

		// Get all messages and bail if there are no messages to output.
		$messages = $this->messages_get();
		if ( empty( $messages ) ) {
			return;
		}
		
		// Output messages to JS console.
		$output = '<script id="browsersync-helper-debug">//<![CDATA[' . PHP_EOL;
		foreach ( $messages as $message ) {
			$output .= 'console.log("' . addslashes( $message ) . '");';
		}
		$output .= '//]]></script>' . PHP_EOL;

		echo $output;
	}	

	/**
	 * Add a message.
	 * 
	 * @param string $type type of message to add
	 * @param string $message text for message.
	 * @return bool
	 */
	private function message_add( $type, $message ) {	
		// Bail if invalid message was given.
		if ( ! $message ) {
			return false;
		}		
		
		// Add the message.
		$this->messages[ $type ]['entries'][] = $message;
		return true;
	}

	/**
	 * Sets the Browsersync version
	 * 
	 * @since 1.0.0
	 * @return string|false
	 */
	private function browsersync_version_set() {
		// Automatically get version from Browsersync's package.json
		if ( true == $this->options['browsersync_auto_version'] ) {
			$this->options['browsersync_version'] = $this->browsersync_version_get_from_json();
			return $this->options['browsersync_version'];
		}
		
		// Configuration error.
		if ( ! $this->options['browsersync_auto_version'] && ! $this->options['browsersync_version'] ) {
			$this->message_add( 'error',
				__( "Set \$options['browsersync_auto_version'] to true (recommended) or manually set \$options['browsersync_version'].", 'browsersync-helper' )
			);

			return false;
		}

		// Manually set version.
		return $this->options['browsersync_version'];
	}
	
	/**
	 * Helper function used to detect the Browsersync version being used by
	 * extracting it from the project's Browsersync's package.json file.
	 * 
	 * @since 1.0.0
	 * @return string|false on error
	 */	
	private function browsersync_version_get_from_json() {
		// Set path to package.json
		// E.g.: {project abs path}/{node_modules path}/browser-sync/package.json
		$package_json_file = trailingslashit( $this->options['project_abs_path'] ) .
												 trailingslashit( $this->options['node_modules_directory'] . '/browser-sync' ) .
												 'package.json';

		if ( ! file_exists( $package_json_file ) ) {
			$this->message_add( 'error',
				sprintf( '%1$s %2$s',
					__( 'Cannot find Browsersync\'s package.json file:', 'browsersync-helper' ),
					esc_html( $package_json_file )
				)
			);
				
			return false;
		}

		// Read the contents of package.json http://stackoverflow.com/a/4343664/3059883	
		$package_json_contents = file_get_contents( $package_json_file );
		if ( ! $package_json_contents ) {
			$this->message_add( 'error',
				sprintf( '%1$s %2$s',
					__( 'Unable to read Browsersync\'s package.json file:', 'browsersync-helper' ),
					esc_html( $package_json_file )
				)
			);

			return false;
		}
		
		// Extract the version number.
		$package_json_decoded = json_decode( $package_json_contents, true );
		if ( ! $package_json_decoded || ! $package_json_decoded[ 'version' ] ) {
			$this->message_add( 'error',
				sprintf( '%1$s %2$s',
					__( 'Problem decoding Browsersync\'s package.json:', 'browsersync-helper' ),
					esc_html( $package_json_file )
				)
			);

			return false;
		}

		// We have got the version; return it.
		return $package_json_decoded[ 'version' ];
	}

	/**
	 * Gets the protocol to use for snippet output.
	 * 
	 * @since 1.0.0
	 * @return string
	 */
	private function snippet_protocol() {
		return is_ssl() ? 'https' : 'http';
	}

	/**
	 * Gets the async attribute for the snippet output.
	 * 
	 * @since 1.0.0
	 * @return string|false
	 */	
	private function snippet_attrib_async() {
		if ( true == $this->options['async'] ) {
			return ' async';
		} else {
			return false;
		}
	}

	/**
	 * Checks if the user has sufficient capabilities to output the snippet.
	 * 
	 * @since 1.0.0
	 * @return bool
	 */	
	private function snippet_capability_check() {
		// No cap required.
		if ( false == $this->options['required_cap'] ) {
			return true;
		}

		// Check if the user has the required capability, when one was specified.
		if ( $this->options['required_cap'] && current_user_can( $this->options['required_cap'] ) ) {
			return true;
		} else {
			// Bail if a specific capability is required and the current user does not have it.
			$this->message_add( 'notification',
				__( 'Snippet disabled. User does not have required capability.', 'browsersync-helper' )
			);

			return false;
		}
	}
	
	/**
	 * Outputs the Browsersync snippet.
	 * 
	 * Example snippet output:
	 *   <script id="__bs_script__">//<![CDATA[
	 * 	  	document.write("<script async src='http://HOST:3000/browser-sync/browser-sync-client.js?v=2.26.3'><\/script>".replace("HOST", location.hostname));
	 * 	 //]]></script>
	 * 
	 * @since 1.0.0
	 * @return void
	 */
	public function snippet_output() {
		// Bail if the user doesn't have the required capability.
		if ( ! $this->snippet_capability_check() ) {
			return;
		}

		// Bail if there are any errors.
		$error_messages = $this->messages_get( 'error' );
		if ( ! empty( $error_messages ) ) {
			return;
		}

		// Build the snippet.
		$output  = '<script id="__bs_script__">//<![CDATA[' . PHP_EOL;
		$output .= "\t" . 'document.write(' . PHP_EOL;
		$output .= "\t\t" . '"<script';
		$output .= esc_attr( $this->snippet_attrib_async() );
		$output .= " src='" . 
									esc_attr( $this->options['protocol'] ). "://HOST:" . esc_attr( $this->options['port'] ) . 
							 		"/browser-sync/browser-sync-client.js?v=" . esc_attr( $this->options['browsersync_version'] );
		$output .= "'>";
		$output .= '<\/script>".replace( "HOST", location.hostname ));' . PHP_EOL;
		$output .= '//]]></script>' . PHP_EOL;

		// Output the snippet.
		echo apply_filters( 'browsersync_helper_snippet_output', $output, $this->options );

		// Add a message stating that the snippet has been output.
		$this->message_add( 'notification', __( 'Snippet JS successfully loaded!', 'browsersync-helper' ) );
	}
}

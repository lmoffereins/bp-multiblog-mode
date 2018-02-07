<?php

/**
 * The BuddyPress Multiblog Mode Plugin
 *
 * @package BP Multiblog Mode
 * @subpackage Main
 */

/**
 * Plugin Name:       BP Multiblog Mode
 * Description:       Enable and customize BuddyPress on other sites than the root blog.
 * Plugin URI:        https://github.com/lmoffereins/bp-multiblog-mode/
 * Version:           1.0.0
 * Author:            Laurens Offereins
 * Author URI:        https://github.com/lmoffereins/
 * Network:           true
 * Text Domain:       bp-multiblog-mode
 * Domain Path:       /languages/
 * GitHub Plugin URI: lmoffereins/bp-multiblog-mode
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Multiblog_Mode' ) ) :
/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class BP_Multiblog_Mode {

	/**
	 * Setup and return the singleton pattern
	 *
	 * @since 1.0.0
	 *
	 * @uses BP_Multiblog_Mode::setup_globals()
	 * @uses BP_Multiblog_Mode::setup_actions()
	 * @return The single BP Multiblog Mode class
	 */
	public static function instance() {

		// Store instance locally
		static $instance = null;

		if ( null === $instance ) {
			$instance = new BP_Multiblog_Mode;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Prevent the plugin class from being loaded more than once
	 */
	private function __construct() { /* Nothing to do */ }

	/** Private methods *************************************************/

	/**
	 * Setup default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Versions **********************************************************/

		$this->version      = '1.0.0';

		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file         = __FILE__;
		$this->basename     = plugin_basename( $this->file );
		$this->plugin_dir   = plugin_dir_path( $this->file );
		$this->plugin_url   = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes' );

		// Languages
		$this->lang_dir     = trailingslashit( $this->plugin_dir . 'languages' );

		/** Identifiers *******************************************************/

		$this->root_blog_id = defined( 'BP_ROOT_BLOG' ) ? (int) BP_ROOT_BLOG : get_network()->site_id;

		/** Misc **************************************************************/

		$this->extend       = new stdClass();
		$this->domain       = 'bp-multiblog-mode';
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// Core
		require( $this->includes_dir . 'functions.php'   );
		require( $this->includes_dir . 'sub-actions.php' );

		// Admin
		if ( is_admin() ) {
			require( $this->includes_dir . 'admin.php'    );
			require( $this->includes_dir . 'settings.php' );
		}

		// Hooks
		if ( $this->can_multiblog() ) {
			require( $this->includes_dir . 'actions.php' );
		}
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Add actions to plugin activation and deactivation hooks
		add_action( 'activate_'   . $this->basename, 'bp_multiblog_mode_activation'   );
		add_action( 'deactivate_' . $this->basename, 'bp_multiblog_mode_deactivation' );

		// Load textdomain
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 20 );

		// Do Multiblog for the enabled site
		if ( $this->can_multiblog() && bp_multiblog_mode_is_enabled() ) {
			add_filter( 'bp_get_root_blog_id',  'get_current_blog_id' );
			add_filter( 'bp_is_multiblog_mode', '__return_true'       );

			// Reset actual root blog where necessary
			add_filter( 'bp_get_taxonomy_term_site_id', array( $this, 'taxonomy_term_site_id' ), 1 );
		}
	}

	/** Plugin **********************************************************/

	/**
	 * Return whether the plugin logic can be run
	 *
	 * Checks whether:
	 * - BP is network activated
	 * - BP_ENABLE_MULTIBLOG is not enabled through the constant
	 * - there are multiple sites in the network
	 *
	 * @since 1.0.0
	 *
	 * @return bool Can we do Multiblog?
	 */
	public function can_multiblog() {
		return bp_is_network_activated() && ! ( defined( 'BP_ENABLE_MULTIBLOG' ) && BP_ENABLE_MULTIBLOG ) && (int) get_blog_count() > 1;
	}

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the plugin folder will be
	 * removed on plugin updates. If you're creating custom translation
	 * files, please use the global language folder.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'plugin_locale' with {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @uses load_plugin_textdomain() To load the textdomain
	 */
	public function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/bp-multiblog-mode/' . $mofile;

		// Look in global /wp-content/languages/bp-multiblog-mode folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/bp-multiblog-mode/languages/ folder
		load_textdomain( $this->domain, $mofile_local );

		// Look in global /wp-content/languages/plugins/
		load_plugin_textdomain( $this->domain );
	}

	/** Public methods **************************************************/

	/**
	 * Modify the site ID where the taxonomy terms live
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id Site ID
	 * @return int Site ID
	 */
	public function taxonomy_term_site_id( $site_id ) {

		// Use the root's registered taxonomy terms
		if ( bp_multiblog_mode_use_root_taxonomy_terms() ) {
			$site_id = bp_multiblog_mode_get_root_blog_id();
		}

		return $site_id;
	}
}

/**
 * Return single instance of this main plugin class
 *
 * @since 1.0.0
 * 
 * @return BP Multiblog Mode class
 */
function bp_multiblog_mode() {
	return BP_Multiblog_Mode::instance();
}

// Initiate plugin on bp_after_setup_actions. Only on Multisite
if ( is_multisite() ) {
	add_action( 'bp_after_setup_actions', 'bp_multiblog_mode' );
}

endif; // class_exists

<?php

/**
 * BP Multiblog Mode Admin Functions
 *
 * @package BP Multiblog Mode
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'BP_Multiblog_Mode_Admin' ) ) :
/**
 * The BP Multiblog Mode Admin class
 *
 * @since 1.0.0
 */
class BP_Multiblog_Mode_Admin {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->setup_actions();
	}

	/**
	 * Define default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {
		$this->settings_page      = bp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
		$this->minimum_capability = bp_core_do_network_admin() ? 'manage_network_options' : 'manage_options';
		$this->screen_id          = 'settings_page_bp-multiblog-mode';

		if ( is_network_admin() ) {
			$this->screen_id .= '-network';
		}
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Admin page
		add_action( bp_core_admin_hook(),     array( $this, 'admin_menus' )      );
		add_filter( 'bp_admin_head',          array( $this, 'admin_head'  ), 999 );
		add_filter( 'bp_core_get_admin_tabs', array( $this, 'admin_tabs'  )      );

		// Styling
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Settings
		add_action( 'bp_admin_init',    array( $this, 'register_settings'      )        );
		add_filter( 'bp_map_meta_caps', array( $this, 'map_settings_meta_caps' ), 10, 4 );
	}

	/** Admin Page ******************************************************/

	/**
	 * Register network admin menu elements
	 *
	 * @since 1.0.0
	 */
	public function admin_menus() {

		// Bail when user cannot manage options
		if ( ! bp_current_user_can( 'manage_options' ) )
			return;

		// Core settings page
		$hook = add_submenu_page(
			$this->settings_page,
			__( 'BuddyPress Multiblog', 'bp-multiblog-mode' ),
			__( 'Multiblog', 'bp-multiblog-mode' ),
			$this->minimum_capability,
			'bp-multiblog-mode',
			'bp_multiblog_mode_admin_settings_page'
		);

		// Blend in BP's administration
		add_action( "admin_head-{$hook}", 'bp_core_modify_admin_menu_highlight' );
	}

	/**
	 * Hide the plugin page from the admin menu
	 *
	 * @see BP_Admin::admin_head()
	 *
	 * @since 1.0.0
	 */
	public function admin_head() {
		remove_submenu_page( $this->settings_page, 'bp-multiblog-mode' );
	}

	/**
	 * Modify the admin tabs of BP's admin page
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Page tags
	 * @return array Page tabs
	 */
	public function admin_tabs( $tabs ) {

		// Append Multiblog Mode page tab
		$tabs[] = array(
			'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-multiblog-mode' ), 'admin.php' ) ),
			'name' => __( 'Multiblog', 'bp-multiblog-mode' ),
		);

		return $tabs;
	}

	/** Styling *********************************************************/

	/**
	 * Add general styling to the admin area
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		// Define local variable(s)
		$styles = array();

		// Network admin page
		if ( get_current_screen()->id === $this->screen_id ) {

			// Mimic post inline-edit styles for .cat-checklist
			$styles[] = '.form-table p + .cat-checklist { margin-top: 6px; }';
			$styles[] = '.form-table .cat-checklist li, .form-table .cat-checklist input { margin: 0; position: relative; }';
			$styles[] = '.form-table .cat-checklist label { margin: .5em 0; display: block; }';
			$styles[] = '.form-table .cat-checklist input[type="checkbox"] { vertical-align: middle; }';
			$styles[] = '.form-table .cat-checklist .description { padding-left: 21px; display: block; opacity: .7; }';

			// Small screens
			$styles[] = '@media screen and (max-width: 782px) {';
			$styles[] = '.form-table .cat-checklist label { max-width: none; float: none; margin: 1em 0; font-size: 16px; }';
			$styles[] = '.form-table .cat-checklist .description { padding: 0 0 0 30px; }';
			$styles[] = '}';
		}

		if ( ! empty( $styles ) ) {
			wp_add_inline_style( 'bp-admin-common-css', implode( "\n", $styles ) );
		}
	}

	/** Settings ********************************************************/

	/**
	 * Register plugin settings
	 *
	 * @since 1.0.0
	 */
	public function register_settings() {

		// Bail if no sections available
		$sections = bp_multiblog_mode_admin_get_settings_sections();
		if ( empty( $sections ) )
			return false;

		// Loop through sections
		foreach ( (array) $sections as $section_id => $section ) {

			// Only proceed if current user can see this section
			if ( ! current_user_can( $section_id ) )
				continue;

			// Only add section and fields if section has fields
			$fields = bp_multiblog_mode_admin_get_settings_fields_for_section( $section_id );
			if ( empty( $fields ) )
				continue;

			// Define section page
			if ( ! empty( $section['page'] ) ) {
				$page = $section['page'];
			} else {
				$page = 'bp-multiblog-mode';
			}

			// Add the section
			add_settings_section( $section_id, $section['title'], $section['callback'], $page );

			// Loop through fields for this section
			foreach ( (array) $fields as $field_id => $field ) {

				// Add the field
				if ( ! empty( $field['callback'] ) && ! empty( $field['title'] ) ) {
					add_settings_field( $field_id, $field['title'], $field['callback'], $page, $section_id, $field['args'] );
				}

				// Register the setting
				if ( ! empty( $field['sanitize_callback'] ) ) {
					register_setting( $page, $field_id, $field['sanitize_callback'] );
				}
			}
		}
	}

	/**
	 * Map caps for the plugin settings
	 *
	 * @since 1.0.0
	 *
	 * @param array $caps Mapped caps
	 * @param string $cap Required capability name
	 * @param int $user_id User ID
	 * @param array $args Additional arguments
	 * @return array Mapped caps
	 */
	public function map_settings_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

		// Check the required capability
		switch ( $cap ) {

			// Settings
			case 'bp_multiblog_mode_settings_general_network' :
			case 'bp_multiblog_mode_settings_general' :
				$caps = array( $this->minimum_capability );
				break;
		}

		return $caps;
	}
}

/**
 * Setup the extension logic for BuddyPress
 *
 * @since 1.0.0
 *
 * @uses BP_Multiblog_Mode_Admin
 */
function bp_multiblog_mode_admin() {
	bp_multiblog_mode()->admin = new BP_Multiblog_Mode_Admin;
}

endif; // class_exists

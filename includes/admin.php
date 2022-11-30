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
		$this->parent_page        = bp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
		$this->settings_page      = is_network_admin() ? 'bp-multiblog-mode-network' : 'bp-multiblog-mode';
		$this->minimum_capability = bp_core_do_network_admin() ? 'manage_network_options' : 'manage_options';
		$this->plugin_screen_id   = 'settings_page_' . $this->settings_page;
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// For backcompat, consider admin hook
		$admin_menus_hook = function_exists( 'bp_core_get_admin_settings_tabs' ) ? 'bp_admin_submenu_pages' : bp_core_admin_hook();

		// Admin page
		add_action( $admin_menus_hook,                 array( $this, 'admin_menus' )         );
		add_filter( 'bp_admin_head',                   array( $this, 'admin_head'  ), 999    );
		add_filter( 'bp_core_get_admin_settings_tabs', array( $this, 'admin_tabs'  )         );
		add_filter( 'bp_core_get_admin_tabs',          array( $this, 'admin_tabs'  ),  10, 2 );

		// Styling
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Settings
		add_action( 'bp_admin_init',    array( $this, 'register_settings'      )        );
		add_filter( 'bp_map_meta_caps', array( $this, 'map_settings_meta_caps' ), 10, 4 );

		// XProfile
		add_action( 'xprofile_group_after_submitbox', array( $this, 'xprofile_group_sites_metabox' ) );
		add_action( 'xprofile_group_after_save',      array( $this, 'xprofile_save_group_sites'    ) );
		add_action( 'xprofile_admin_group_action',    array( $this, 'xprofile_group_admin_label'   ) );
	}

	/** Admin Page ******************************************************/

	/**
	 * Register network admin menu elements
	 *
	 * @since 1.0.0
	 *
	 * @param array $submenu_pages The BP_Admin submenu pages passed by reference
	 */
	public function admin_menus( &$submenu_pages = array() ) {

		// Bail when user cannot manage
		if ( ! bp_current_user_can( $this->minimum_capability ) )
			return;

		// Bail when the page has no settings
		if ( ! bp_multiblog_mode_admin_page_has_settings( $this->settings_page ) )
			return;

		$hooks = array();

		// Core settings page
		$settings_page = add_submenu_page(
			$this->parent_page,
			esc_html__( 'BuddyPress Multiblog', 'bp-multiblog-mode' ),
			esc_html__( 'Multiblog', 'bp-multiblog-mode' ),
			$this->minimum_capability,
			'bp-multiblog-mode',
			'bp_multiblog_mode_admin_settings_page'
		);

		$submenu_pages['settings']['bp-multiblog-mode'] = $settings_page;
		$hooks[]                                        = $settings_page;

		// Blend in BP's administration
		foreach ( $hooks as $hook ) {
			add_action( "admin_head-{$hook}", 'bp_core_modify_admin_menu_highlight' );
		}

	}

	/**
	 * Hide the plugin page from the admin menu
	 *
	 * @see BP_Admin::admin_head()
	 *
	 * @since 1.0.0
	 */
	public function admin_head() {
		remove_submenu_page( $this->parent_page, 'bp-multiblog-mode' );
	}

	/**
	 * Modify the admin tabs of BP's admin page
	 *
	 * @since 1.0.0
	 *
	 * @param array $tabs Page tags
	 * @param string $context Tabs context. Defaults to 'settings' for backcompat
	 * @return array Page tabs
	 */
	public function admin_tabs( $tabs, $context = 'settings' ) {

		// Prepare tabs
		$settings_tabs = array();
		$tools_tabs    = array();

		// Append Multiblog Mode page tab, when settings are registered
		if ( bp_multiblog_mode_admin_page_has_settings( $this->settings_page ) ) {
			$settings_tabs[] = array(
				'id'   => 'bp-multiblog-mode',
				'href' => bp_get_admin_url( add_query_arg( array( 'page' => 'bp-multiblog-mode' ), 'admin.php' ) ),
				'name' => esc_html__( 'Multiblog', 'bp-multiblog-mode' ),
			);
		}

		// Add settings tabs
		if ( 'settings' === $context ) {
			foreach ( $settings_tabs as $tab ) {

				// For backcompat, only add tab once
				if ( ! isset( $tabs[ $tab['id'] ] ) ) {
					$tabs[ $tab['id'] ] = $tab;
				}
			}

		// Add tools tabs
		} else if ( 'tools' === $context ) {
			foreach ( $tools_tabs as $tab ) {

				// For backcompat, only add tab once
				if ( ! isset( $tabs[ $tab['id'] ] ) ) {
					$tabs[ $tab['id'] ] = $tab;
				}
			}
		}

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
		$styles  = array();
		$screen  = get_current_screen();
		$network = is_network_admin() ? '-network' : '';

		// Network admin page
		if ( $screen->id === $this->plugin_screen_id ) {

			// Mimic post inline-edit styles for .cat-checklist
			$styles[] = '.form-table .widefat th, .form-table .widefat td { padding: 8px 10px; font-weight: normal; }';
			$styles[] = '.form-table .widefat .check-column { width: 2.2em; }';
			$styles[] = '.form-table p + .cat-checklist { margin-top: 6px; }';
			$styles[] = '.form-table .widefat .column-blogname strong { display: block; margin-bottom: .2em; font-size: 14px; }';
			$styles[] = '.form-table .widefat .column-blogname strong ~ span { font-size: 13px; font-style: italic; line-height: 1.5em; }';

			// Small screens
			$styles[] = '@media screen and (max-width: 782px) {';
			$styles[] = '.form-table .widefat th, .form-table .widefat td { display: table-cell; }';
			$styles[] = '}';

		// BP XProfile admin page
		} elseif ( "users_page_bp-profile-setup{$network}" === $screen->id ) {

			// Group detail
			$styles[] = '.wp-core-ui .tab-toolbar .button.group-sites { color: #555 !important; }';
			$styles[] = '.wp-core-ui .tab-toolbar .button.group-sites-error { color: #f00 !important; border-color: #f00 !important; }';

			// Add/edit group
			if ( isset( $_GET['mode'] ) && in_array( $_GET['mode'], array( 'add_group', 'edit_group' ) ) ) {

				// Sites metabox
				$styles[] = '#bp-multiblog-mode_sitediv label { margin: .5em 0; display: block; }';
				$styles[] = '#bp-multiblog-mode_sitediv label .description { padding-left: 25px; display: block; opacity: .7; }';

				// Small screens
				$styles[] = '@media screen and (max-width: 782px) {';
				$styles[] = '#bp-multiblog-mode_sitediv label { max-width: none; float: none; margin: 1em 0; font-size: 16px; }';
				$styles[] = '#bp-multiblog-mode_sitediv label .description { padding: 0 0 0 34px; }';
				$styles[] = '}';
			}
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

	/** XProfile ********************************************************/

	/**
	 * Add XProfile field group Sites metabox
	 *
	 * @since 1.0.0
	 *
	 * @param BP_XProfile_Group $group
	 */
	public function xprofile_group_sites_metabox( $group ) {

		// The primary field group is for all, so bail
		if ( 1 === (int) $group->id )
			return;

		// Bail when no sites are Multiblog enabled
		if ( ! $sites = bp_multiblog_mode_get_enabled_sites() )
			return;

		// Prepend the root site
		array_unshift( $sites, get_site( bp_multiblog_mode_get_root_blog_id() ) );

		// Get the group's sites
		$group_sites = bp_multiblog_mode_xprofile_get_group_sites( $group->id );

		?>

		<div id="bp-multiblog-mode_sitediv" class="postbox">
			<h2><?php esc_html_e( 'Sites', 'bp-multiblog-mode' ); ?></h2>
			<div class="inside">
				<p class="description"><?php esc_html_e( 'This group should be available at:', 'bp-multiblog-mode' ); ?></p>

				<ul>
					<?php foreach ( $sites as $site ) : ?>
					<li>
						<label for="group-site-<?php echo $site->id; ?>">
							<input name="group-sites[]" id="group-site-<?php echo $site->id; ?>" class="group-site-selector" type="checkbox" value="<?php echo $site->id; ?>" <?php checked( in_array( $site->id, $group_sites ) ); ?>/>
							<?php echo $site->blogname; ?>

							<?php if ( is_main_site( $site->id ) ) : ?>
								<strong>&mdash; <?php esc_html_e( 'Main Site', 'bp-multiblog-mode' ); ?></strong>
							<?php endif; ?>

							<span class="description"><?php echo $site->siteurl; ?></span>
						</label>
					</li>
					<?php endforeach; ?>
				</ul>
				<p class="description member-type-none-notice<?php if ( ! empty( $group_sites ) ) : ?> hide<?php endif; ?>"><?php esc_html_e( 'Unavailable to all sites.', 'bp-multiblog-mode' ) ?></p>
			</div>

			<input type="hidden" name="has-group-sites" value="1" />
		</div>

		<?php
	}

	/**
	 * Save the XProfile field group's Multiblog sites
	 * 
	 * @see BP_XProfile_Field::set_member_types()
	 *
	 * @since 1.0.0
	 *
	 * @param BP_XProfile_Group $group
	 */
	public function xprofile_save_group_sites( $group ) {

		// Bail when sites were not posted
		if ( ! isset( $_POST['has-group-sites'] ) )
			return;

		$group_sites = array();
		if ( isset( $_POST['group-sites'] ) ) {
			$group_sites = array_map( 'intval', (array) $_POST['group-sites'] );
		}

		// Delete all previous meta
		bp_xprofile_delete_meta( $group->id, 'group', 'multiblog_site' );

		/*
		 * We interpret an empty array as disassociating the group from all sites. This is
		 * represented internally with the '_none' flag.
		 */
		if ( empty( $group_sites ) ) {
			bp_xprofile_add_meta( $group->id, 'group', 'multiblog_site', '_none' );
		}

		/*
		 * Unrestricted groups are represented in the database as having no 'multiblog_site'.
		 * We detect whether a group is being set to unrestricted by checking whether the
		 * list of sites passed to the method is the same as the list of available sites,
		 * plus the root blog.
		 */
		$sites   = bp_multiblog_mode_get_enabled_sites( array( 'fields' => 'ids' ) );
		$sites[] = bp_multiblog_mode_get_root_blog_id();

		sort( $group_sites );
		sort( $sites );

		// Only save if this is a restricted group
		if ( $sites !== $group_sites ) {
			// Save new sites.
			foreach ( $group_sites as $site_id ) {
				bp_xprofile_add_meta( $group->id, 'group', 'multiblog_site', $site_id );
			}
		}
	}

	/**
	 * Display a label representing the XProfile group's sites
	 *
	 * @since 1.0.0
	 *
	 * @param BP_XProfile_Group $group
	 */
	public function xprofile_group_admin_label( $group ) {

		// The primary field group is for all, so bail
		if ( 1 === (int) $group->id )
			return;

		// Get all sites and the group's sites
		$sites       = bp_multiblog_mode_get_enabled_sites( array( 'fields' => 'ids' ) );
		$sites[]     = bp_multiblog_mode_get_root_blog_id();
		$group_sites = bp_multiblog_mode_xprofile_get_group_sites( $group->id );

		// Bail when the group applies to all sites
		if ( array_values( $sites ) == $group_sites )
			return;

		$label = '';
		if ( ! empty( $group_sites ) ) {
			$count = count( $group_sites );

			if ( 1 === $count ) {
				$site_id    = reset( $group_sites );
				$label_text = sprintf( esc_html__( 'Site: %s', 'bp-multiblog-mode' ), get_blog_option( $site_id, 'blogname' ) );
			} else {
				$label_text = sprintf( esc_html__( 'For %d Sites', 'bp-multiblog-mode' ), $count );
			}

			$label = '<div class="button group-sites" disabled="disabled">' . $label_text . '</div>';
		} else {
			$label = '<div class="button group-sites-error" disabled="disabled">' . esc_html__( 'Unavailable to all sites', 'bp-multiblog-mode' ) . '</div>';
		}

		echo $label;
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

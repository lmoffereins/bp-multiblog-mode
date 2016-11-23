<?php

/**
 * BP Multiblog Mode Settings Functions
 *
 * @package BP Multiblog Mode
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the plugin's settings sections
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_admin_get_settings_sections'
 * @return array Settings sections
 */
function bp_multiblog_mode_admin_get_settings_sections() {
	return (array) apply_filters( 'bp_multiblog_mode_admin_get_settings_sections', array(

		// General Network settings
		'bp_multiblog_mode_settings_general_network' => array(
			'title'    => esc_html__( 'General Settings', 'bp-multiblog-mode' ),
			'callback' => 'bp_multiblog_mode_admin_setting_callback_general_section',
			'page'     => 'bp-multiblog-mode-network',
		),

		// General settings
		'bp_multiblog_mode_settings_general' => array(
			'title'    => esc_html__( 'General Settings', 'bp-multiblog-mode' ),
			'callback' => 'bp_multiblog_mode_admin_setting_callback_general_section',
			'page'     => 'bp-multiblog-mode',
		),
	) );
}

/**
 * Return the plugin's settings fields
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_admin_get_settings_fields'
 * @return array Settings fields
 */
function bp_multiblog_mode_admin_get_settings_fields() {

	// Define settings fields
	$fields = array(

		// General Network settings		
		'bp_multiblog_mode_settings_general_network' => array(

			// Sites
			'_bp_multiblog_mode_sites' => array(
				'title'             => esc_html__( 'Sites', 'bp-multiblog-mode' ),
				'callback'          => 'bp_multiblog_mode_admin_setting_callback_sites',
				'sanitize_callback' => false, // We do our own saving of this field
				'args'              => array()
			),
		),

		// General settings		
		'bp_multiblog_mode_settings_general' => array(),
	);

	return (array) apply_filters( 'bp_multiblog_mode_admin_get_settings_fields', $fields );
}

/**
 * Get settings fields by section
 *
 * @since 1.0.0
 *
 * @param string $section_id
 * @return array|bool Array of fields or False when section is invalid
 */
function bp_multiblog_mode_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = bp_multiblog_mode_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return $retval;
}

/** General Network Section ***************************************************/

/**
 * Display the description of the General settings section
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_general_section() { /* Nothing to show here */ }

/**
 * Display the enable Multiblog per site setting field
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_sites() {

	// Get all sites of the current network
	$sites = bp_multiblog_mode_get_sites(); ?>

	<p><?php esc_html_e( 'Select the network sites that should have Multiblog enabled.', 'bp-multiblog-mode' ); ?></p>

	<ul class="cat-checklist">

		<?php foreach ( $sites as $site ) :
			$is_root_blog = bp_multiblog_mode_is_root_blog( $site->id ); ?>

		<li id="site-<?php echo esc_attr( $site->id ); ?>">
			<label class="selectit">
				<input value="<?php echo $site->id; ?>" type="checkbox" name="bp_multiblog_mode_sites[]" id="enabled-site-<?php echo esc_attr( $site->id ); ?>" <?php disabled( $is_root_blog ); checked( get_blog_option( $site->id, '_bp_multiblog_mode_enabled', false ) || $is_root_blog ); ?> />
				<?php echo $site->blogname; ?>

				<?php if ( $is_root_blog && ! is_main_site( $site->id ) ) : ?>
					<strong>&mdash; <?php esc_html_e( 'BuddyPress Root Site', 'bp-multiblog-mode' ); ?></strong>
				<?php elseif ( is_main_site( $site->id ) ) : ?>
					<strong>&mdash; <?php esc_html_e( 'Main Site', 'bp-multiblog-mode' ); ?></strong>
				<?php endif; ?>

				<span class="description"><?php echo $site->siteurl; ?></span>
			</label>
		</li>

		<?php endforeach; ?>

	</ul>

	<input type="hidden" name="bp_multiblog_mode_site_ids" value="<?php echo implode( ',', wp_list_pluck( $sites, 'id' ) ); ?>" />

	<?php
}

/** General Section ***********************************************************/

/** Settings Page *************************************************************/

/**
 * Display the network admin settings page
 *
 * @see bp_core_admin_settings()
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_settings_page() {

	// Like BP, let's save our own options, until the WP Settings API is updated to work with Multisite.
	$form_action   = is_network_admin() ? add_query_arg( 'page', 'bp-multiblog-mode', bp_get_admin_url( 'admin.php' ) ) : 'options.php';
	$settings_page = is_network_admin() ? 'bp-multiblog-mode-network' : 'bp-multiblog-mode';

	?>

	<div class="wrap">

		<h1><?php _e( 'BuddyPress Settings', 'buddypress' ); ?> </h1>

		<h2 class="nav-tab-wrapper"><?php bp_core_admin_tabs( __( 'Multiblog', 'bp-multiblog-mode' ) ); ?></h2>
	
		<form action="<?php echo esc_url( $form_action ) ?>" method="post">

			<?php settings_fields( $settings_page ); ?>

			<?php do_settings_sections( $settings_page ); ?>

			<p class="submit">
				<input type="submit" name="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'buddypress' ); ?>" />
			</p>

		</form>		
	</div>

	<?php
}

/**
 * Save admin settings
 *
 * @see bp_core_admin_settings_save()
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_settings_save() {
	global $wp_settings_fields;

	// Core settings are submitted
	if ( is_network_admin() && isset( $_GET['page'] ) && 'bp-multiblog-mode' == $_GET['page'] && !empty( $_POST['submit'] ) ) {
		check_admin_referer( 'bp-multiblog-mode-network-options' );

		// Save whether Sites are enabled
		if ( isset( $_POST['bp_multiblog_mode_site_ids'] ) ) {
			$site_ids = array_map( 'intval', explode( ',', $_POST['bp_multiblog_mode_site_ids'] ) );
			$enabled  = isset( $_POST['bp_multiblog_mode_sites'] ) ? array_map( 'absint', $_POST['bp_multiblog_mode_sites'] ) : array();

			foreach ( $site_ids as $site_id ) {
				update_blog_option( $site_id, '_bp_multiblog_mode_enabled', (int) in_array( $site_id, $enabled ) );
			}
		}

		// Because many settings are saved with checkboxes, and thus will have no values
		// in the $_POST array when unchecked, we loop through the registered settings.
		if ( isset( $wp_settings_fields['bp-multiblog-mode'] ) ) {
			foreach( (array) $wp_settings_fields['bp-multiblog-mode'] as $section => $settings ) {
				foreach( $settings as $setting_name => $setting ) {
					$value = isset( $_POST[$setting_name] ) ? $_POST[$setting_name] : '';

					bp_update_option( $setting_name, $value );
				}
			}
		}

		bp_core_redirect( add_query_arg( array( 'page' => 'bp-multiblog-mode', 'updated' => 'true' ), bp_get_admin_url( 'admin.php' ) ) );
	}
}

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
			'callback' => 'bp_multiblog_mode_admin_setting_callback_general_network_section',
			'page'     => 'bp-multiblog-mode-network',
		),

		// General settings
		'bp_multiblog_mode_settings_general' => array(
			'title'    => esc_html__( 'General Settings', 'bp-multiblog-mode' ),
			'callback' => 'bp_multiblog_mode_admin_setting_callback_general_section',
			'page'     => 'bp-multiblog-mode',
		),

		// Profile settings
		'bp_multiblog_mode_settings_profile' => array(
			'title'    => esc_html__( 'Profile Settings', 'bp-multiblog-mode' ),
			'callback' => 'bp_multiblog_mode_admin_setting_callback_profile_section',
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
		'bp_multiblog_mode_settings_general' => array(

			// Taxonomy terms
			'_bp_multiblog_mode_taxonomy_terms' => array(
				'title'             => esc_html__( 'Taxonomy terms', 'bp-multiblog-mode' ),
				'callback'          => 'bp_multiblog_mode_admin_setting_callback_taxonomy_terms',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),
		),

		// Profile settings		
		'bp_multiblog_mode_settings_profile' => array(


			// Avatar uploads
			'_bp_multiblog_mode_avatar_uploads' => array(
				'title'             => esc_html__( 'Avatar uploads', 'bp-multiblog-mode' ),
				'callback'          => 'bp_multiblog_mode_admin_setting_callback_avatar_uploads',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// File uploads
			'_bp_multiblog_mode_file_uploads' => array(
				'title'             => esc_html__( 'File uploads', 'bp-multiblog-mode' ),
				'callback'          => 'bp_multiblog_mode_admin_setting_callback_file_uploads',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),
		),
	);

	// Activity
	if ( bp_is_active( 'activity' ) ) {

		// Activity Stream
		$fields['bp_multiblog_mode_settings_general']['_bp_multiblog_mode_activity_stream'] = array(
			'title'             => esc_html__( 'Activity stream', 'bp-multiblog-mode' ),
			'callback'          => 'bp_multiblog_mode_admin_setting_callback_activity_stream',
			'sanitize_callback' => 'intval',
			'args'              => array()
		);

		// Site Members
		$fields['bp_multiblog_mode_settings_general']['_bp_multiblog_mode_site_members'] = array(
			'sanitize_callback' => 'intval',
			'args'              => array()
		);
	}

	// Avatar uploads is disabled
	if ( bp_disable_avatar_uploads( false ) ) {
		unset( $fields['bp_multiblog_mode_settings_profile']['_bp_multiblog_mode_avatar_uploads'] );
	}

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

	// Bail when section is empty
	if ( empty( $section_id ) )
		return false;

	$fields = bp_multiblog_mode_admin_get_settings_fields();
	$retval = isset( $fields[$section_id] ) ? $fields[$section_id] : false;

	return $retval;
}

/**
 * Return whether the admin page has registered settings
 *
 * @since 1.0.0
 *
 * @param string $page
 * @return bool Does the admin page have settings?
 */
function bp_multiblog_mode_admin_page_has_settings( $page = '' ) {

	// Bail when page is empty
	if ( empty( $page ) )
		return false;

	// Loop through the available sections
	$sections = wp_list_filter( bp_multiblog_mode_admin_get_settings_sections(), array( 'page' => $page ) );
	foreach ( (array) $sections as $section_id => $section ) {

		// Find out whether the section has fields
		$fields = bp_multiblog_mode_admin_get_settings_fields_for_section( $section_id );
		if ( ! empty( $fields ) ) {
			return true;
		}
	}

	return false;
}

/** General Network Section ***************************************************/

/**
 * Display the description of the General Network settings section
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_general_network_section() { /* Nothing to show */ }

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

/**
 * Display the description of the General settings section
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_general_section() { ?>

	<p><?php esc_html_e( 'Initially, this instance of BuddyPress is an identical presentation of the one at the root site. However, the settings below enable you to further tweak its unique presentation on this site to your own liking.', 'bp-multiblog-mode' ); ?></p>

	<?php
}

/**
 * Display the Taxonomy terms setting field
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_taxonomy_terms() { ?>

	<input value="1" type="checkbox" name="_bp_multiblog_mode_taxonomy_terms" id="_bp_multiblog_mode_taxonomy_terms" <?php checked( bp_get_form_option( '_bp_multiblog_mode_taxonomy_terms', false ) ); ?> />
	<label for="_bp_multiblog_mode_taxonomy_terms"><?php esc_html_e( "Register BuddyPress taxonomy terms and term relationships (like member types) on this site. Defaults to using the root's taxonomy terms.", 'bp-multiblog-mode' ); ?></label>

	<?php
}

/**
 * Display the Members setting field
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_site_members() { ?>

	<input value="1" type="checkbox" name="_bp_multiblog_mode_site_members" id="_bp_multiblog_mode_site_members" <?php checked( bp_get_form_option( '_bp_multiblog_mode_site_members', false ) ); ?> />
	<label for="_bp_multiblog_mode_site_members"><?php esc_html_e( "Limit this site's instance of BuddyPress to members who are registered users of this site", 'bp-multiblog-mode' ); ?></label>

	<?php
}

/**
 * Display the Activity stream setting field
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_activity_stream() { ?>

	<p><?php esc_html_e( 'Limit the Activity Stream to display only items that are the following:', 'bp-multiblog-mode' ); ?></p>

	<p>
		<input value="1" type="checkbox" name="_bp_multiblog_mode_activity_stream" id="_bp_multiblog_mode_activity_stream" <?php checked( bp_get_form_option( '_bp_multiblog_mode_activity_stream', false ) ); ?> />
		<label for="_bp_multiblog_mode_activity_stream"><?php esc_html_e( 'Items that are created on this site', 'bp-multiblog-mode' ); ?></label>
	</p>

	<p>
		<input value="1" type="checkbox" name="_bp_multiblog_mode_site_members" id="_bp_multiblog_mode_site_members" <?php checked( bp_get_form_option( '_bp_multiblog_mode_site_members', false ) ); ?> />
		<label for="_bp_multiblog_mode_site_members"><?php esc_html_e( 'Items that are created by members who are a user of this site', 'bp-multiblog-mode' ); ?></label>
	</p>

	<?php
}

/** Profile Section ***********************************************************/

/**
 * Display the description of the Profile settings section
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_profile_section() { /* Nothing to display */ }

/**
 * Display the Avatar uploads setting field
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_avatar_uploads() { ?>

	<input value="1" type="checkbox" name="_bp_multiblog_mode_avatar_uploads" id="_bp_multiblog_mode_avatar_uploads" <?php checked( bp_get_form_option( '_bp_multiblog_mode_avatar_uploads', false ) ); ?> />
	<label for="_bp_multiblog_mode_avatar_uploads"><?php esc_html_e( 'Store avatar uploads on this site. Defaults to using root avatars.', 'bp-multiblog-mode' ); ?></label>

	<?php
}

/**
 * Display the File uploads setting field
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_admin_setting_callback_file_uploads() { ?>

	<input value="1" type="checkbox" name="_bp_multiblog_mode_file_uploads" id="_bp_multiblog_mode_file_uploads" <?php checked( bp_get_form_option( '_bp_multiblog_mode_file_uploads', false ) ); ?> />
	<label for="_bp_multiblog_mode_file_uploads"><?php esc_html_e( 'Store non-avatar file uploads on this site. Defaults to using root file uploads.', 'bp-multiblog-mode' ); ?></label>

	<?php
}

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
	$settings_page = bp_multiblog_mode()->admin->settings_page;

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

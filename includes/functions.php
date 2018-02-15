<?php

/**
 * BP Multiblog Mode Functions
 *
 * @package BP Multiblog Mode
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the real root blog ID where BP actually lives
 *
 * @since 1.0.0
 *
 * @return int The real BP root blog ID
 */
function bp_multiblog_mode_get_root_blog_id() {
	return bp_multiblog_mode()->root_blog_id;
}

/**
 * Return whether the given site is the real BP root blog
 *
 * @since 1.0.0
 *
 * @param int $site_id Optional. Site id. Defaults to the current site id.
 * @return bool Is this the real BP root blog?
 */
function bp_multiblog_mode_is_root_blog( $site_id = 0 ) {

	// Default to the current site
	if ( empty( $site_id ) ) {
		$site_id = get_current_blog_id();
	}

	// Define return value
	$retval = false;

	// Check the real root blog ID
	if ( bp_multiblog_mode_get_root_blog_id() === (int) $site_id ) {
		$retval = true;
	}

	return $retval;
}

/**
 * Return whether the given site has Multiblog mode enabled
 *
 * @since 1.0.0
 *
 * @param int $site_id Optional. Site id. Defaults to the current site id.
 * @return bool Is Multiblog mode enabled?
 */
function bp_multiblog_mode_is_enabled( $site_id = 0 ) {

	// Default to the current site
	if ( empty( $site_id ) ) {
		$site_id = get_current_blog_id();
	}

	// Define return value
	$retval = false;

	// Check the site's setting when BP is network activated, but not for the real root blog
	if ( bp_is_network_activated() && ! bp_multiblog_mode_is_root_blog( $site_id ) ) {
		$retval = (bool) get_blog_option( $site_id, '_bp_multiblog_mode_enabled', false );
	}

	return $retval;
}

/**
 * Return the sites of the current network
 *
 * @since 1.0.0
 *
 * @param bool $enabled Optional. Whether to return only enabled sites. Defaults to false.
 * @return array Sites, maybe only enabled
 */
function bp_multiblog_mode_get_sites( $args = array() ) {

	// Provide a fallback for non-array parameter
	if ( ! is_array( $args ) ) {
		$args = array( 'multiblog' => (bool) $args );
	}

	// Define query args
	$args = wp_parse_args( $args, array(
		'network_id' => get_network()->id,
		'multiblog'  => false
	) );

	// Query Multiblog-only?
	$multiblog = (bool) $args['multiblog'];
	unset( $args['multiblog'] );

	// Get the Network's sites
	$sites = get_sites( $args );

	// Filter for enabled Multiblog sites
	if ( $multiblog ) {
		foreach ( $sites as $k => $site ) {
			
			// Remove the unenabled site
			if ( ! bp_multiblog_mode_is_enabled( is_a( $site, 'WP_Site' ) ? $site->id : (int) $site ) ) {
				unset( $sites[ $k ] );
			}
		}
	}

	return $sites;
}

/**
 * Return the Multiblog enabled sites of the current network
 *
 * @see bp_multiblog_mode_get_sites()
 *
 * @since 1.0.0
 *
 * @param array Optional. Query arguments for WP_Site_Query.
 * @return array Multiblog enabled sites
 */
function bp_multiblog_mode_get_enabled_sites( $args = array() ) {
	$args = wp_parse_args( $args, array( 'multiblog' => true ) );
	return bp_multiblog_mode_get_sites( $args );
}

/**
 * Return whether the site uses the root's taxonomy terms
 *
 * @since 1.0.0
 *
 * @return bool Using root's taxonomy terms?
 */
function bp_multiblog_mode_use_root_taxonomy_terms() {
	return ! get_option( '_bp_multiblog_mode_taxonomy_terms', false );
}

/** Activity ************************************************************/

/**
 * Modify the activity query WHERE statements
 *
 * @see BP_Activity_Activity::get()
 *
 * @since 1.0.0
 *
 * @param array $where Query WHERE statements
 * @param array $args Query arguments
 * @return array Query WHERE statements
 */
function bp_multiblog_mode_activity_limit_stream( $where, $args ) {
	global $wpdb;

	// Bail when Multiblog is not enabled
	if ( ! bp_multiblog_mode_is_enabled() )
		return $where;

	// Get BuddyPress
	$bp = buddypress();

	// Only when Multiblog is enabled
	if ( bp_multiblog_mode_is_enabled() ) {

		// Exclude activity items from deactivated components, not Blogs
		$components = array_diff( (array) $bp->deactivated_components, array( 'blogs' ) );
		$where['bp_multiblog_mode_deactivated_components'] = sprintf( "a.component NOT IN ( %s )", "'" . implode( "','", $components ) . "'" );
	}

	// Force return activity items belonging only to the current site
	if ( bp_get_option( '_bp_multiblog_mode_activity_stream', false ) ) {

		/**
		 * Column 'item_id' can also mean id from activity/forums/groups etc.
		 * components, so only filter blogs component activities.
		 */
		$where['bp_multiblog_mode_current_site'] = $wpdb->prepare( "( a.item_id = %d OR a.component <> %s )", get_current_blog_id(), 'blogs' );
	}

	// Force return activity items created by users of the current site
	if ( bp_get_option( '_bp_multiblog_mode_site_members', false ) ) {

		// `WP_User_Query` defaults to users of the current site
		$members = get_users( array( 'fields' => 'ids' ) );
		$where['bp_multiblog_mode_site_members'] = sprintf( "a.user_id IN ( %s )", implode( ',', $members ) );
	}

	return $where;
}

/** XProfile ************************************************************/

/**
 * Return the XProfile field group's Multiblog sites
 *
 * @see BP_XProfile_Field::get_member_types()
 *
 * @since 1.0.0
 *
 * @param int $group_id XProfile group id
 * @return array Site ids
 */
function bp_multiblog_mode_xprofile_get_group_sites( $group_id ) {

	// Get profile group meta
	$raw_sites = bp_xprofile_get_meta( $group_id, 'group', 'multiblog_site', false );

	// If `$raw_sites` is not an array, it probably means this is a new group (id=0).
	if ( ! is_array( $raw_sites ) ) {
		$raw_sites = array();
	}

	// If '_none' is found in the array, it overrides all sites.
	$sites = array();
	if ( ! in_array( '_none', $raw_sites ) ) {
		$enabled_sites = bp_multiblog_mode_get_enabled_sites( array( 'fields' => 'ids' ) );

		// Eliminate invalid sites saved in the database.
		foreach ( $raw_sites as $raw_site ) {
			// The root blog is a special case - it cannot be Multiblog enabled
			if ( bp_multiblog_mode_is_root_blog( $raw_site ) || in_array( $raw_site, $enabled_sites ) ) {
				$sites[] = $raw_site;
			}
		}

		// If no sites have been saved, intepret as *all* sites.
		if ( empty( $sites ) ) {
			$sites = array_values( $enabled_sites );

			// + the root blog
			$sites[] = bp_multiblog_mode_get_root_blog_id();
		}
	}

	return $sites;
}

/**
 * Modify the profile groups collection
 *
 * @since 1.0.0
 *
 * @param array $groups Profile groups
 * @param array $args Query arguments
 * @return array Profile groups
 */
function bp_multiblog_mode_xprofile_get_groups( $groups, $args ) {

	// Are we in the network admin?
	$network = is_network_admin() ? '-network' : '';

	// Not when editing profile fields
	if ( ! is_admin() || "users_page_bp-profile-setup{$network}" !== get_current_screen()->id ) {

		// Eliminate unassigned groups for the current site
		foreach ( $groups as $k => $group ) {
			if ( ! in_array( get_current_blog_id(), bp_multiblog_mode_xprofile_get_group_sites( $group->id ) ) ) {
				unset( $groups[ $k ] );
			}
		}

		$groups = array_values( $groups );
	}

	return $groups;
}

/** Files ***************************************************************/

/**
 * Return whether the site uses the root's avatar uploads
 *
 * @since 1.0.0
 *
 * @return bool Use root avatar uploads
 */
function bp_multiblog_mode_use_root_avatar_uploads() {
	return ! get_option( '_bp_multiblog_mode_avatar_uploads', false );
}

/**
 * Return whether the site uses the root's file uploads
 *
 * @since 1.0.0
 *
 * @return bool Use root file uploads
 */
function bp_multiblog_mode_use_root_file_uploads() {
	return ! get_option( '_bp_multiblog_mode_file_uploads', false );
}

/**
 * Define the upload directory at the root blog
 *
 * The upload dir is fetched once and stored in the BP global. So to
 * override this we predefine the upload dir early, before BP tries to
 * set it. Deviations from this directory are counted for in filters.
 *
 * @see bp_upload_dir()
 *
 * @since 1.0.0
 */
function bp_multiblog_mode_set_root_upload_dir() {
	$bp = buddypress();

	if ( empty( $bp->upload_dir ) && bp_multiblog_mode_is_enabled() && bp_multiblog_mode_use_root_file_uploads() ) {

		// Juggle to root blog.
		switch_to_blog( bp_multiblog_mode_get_root_blog_id() );

		// Get the upload directory (for root blog).
		$wp_upload_dir = wp_upload_dir();

		// Juggle back to current blog.
		restore_current_blog();

		// Bail if an error occurred.
		if ( ! empty( $wp_upload_dir['error'] ) ) {
			return false;
		}

		$bp->upload_dir = $wp_upload_dir;
	}
}

/**
 * Return the upload dir for the BP's root blog
 *
 * @since 1.0.0
 *
 * @param int $site_id Optional. Site id. Defaults to the current site id.
 * @return array Root upload dir data
 */
function bp_multiblog_mode_get_upload_dir( $site_id = 0 ) {
	$plugin = bp_multiblog_mode();

	if ( empty( $plugin->upload_dir ) ) {
		if ( empty( $site_id ) ) {
			$site_id = get_current_blog_id();
		}

		switch_to_blog( $site_id );

		// Get root upload dir
		$upload_dir = wp_upload_dir();

		restore_current_blog();

		// Bail when an error occurred
		if ( ! empty( $upload_dir['error'] ) )
			return false;

		$plugin->upload_dir = $upload_dir;
	}

	return $plugin->upload_dir;
}

/**
 * Return whether to filter the avatar upload directory
 *
 * @since 1.0.0
 *
 * @return bool Filter the avatar upload dir
 */
function bp_multiblog_mode_filter_avatar_upload_dir() {
	$use_file_uploads   = bp_multiblog_mode_use_root_file_uploads();
	$use_avatar_uploads = bp_multiblog_mode_use_root_avatar_uploads();

	/**
	 * Filter avatar upload directory when:
	 * - Multiblog mode is enabled
	 * - Upload dir is not overloaded and avatars should use the root dir
	 * - OR Upload dir *is* overloaded and avatars should *not* use the root dir
	 *
	 * In either other case (both (not) from root), no filtering is needed.
	 */
	return bp_multiblog_mode_is_enabled() && ( ( ! $use_file_uploads && $use_avatar_uploads ) || ( $use_file_uploads && ! $use_avatar_uploads ) );
}

/**
 * Modify the avatar's upload path
 *
 * @since 1.0.0
 *
 * @param string $path Avatar upload path
 * @return string Avatar upload path
 */
function bp_multiblog_mode_core_avatar_upload_path( $path ) {

	// Filtering required
	if ( bp_multiblog_mode_filter_avatar_upload_dir() && ! defined( 'BP_AVATAR_UPLOAD_PATH' ) ) {
		$uploads = bp_multiblog_mode_get_upload_dir( bp_multiblog_mode_use_root_avatar_uploads() ? bp_multiblog_mode_get_root_blog_id() : 0 );

		if ( $uploads ) {
			$path = $uploads['basedir'];
		}
	}

	return $path;
}

/**
 * Modify the avatar's base url
 *
 * @since 1.0.0
 *
 * @param string $url Avatar base url
 * @return string Avatar base url
 */
function bp_multiblog_mode_core_avatar_url( $url ) {

	// Filtering required
	if ( bp_multiblog_mode_filter_avatar_upload_dir() && ! defined( 'BP_AVATAR_URL' ) ) {
		$uploads = bp_multiblog_mode_get_upload_dir( bp_multiblog_mode_use_root_avatar_uploads() ? bp_multiblog_mode_get_root_blog_id() : 0 );

		if ( $uploads ) {
			$url = $uploads['baseurl'];

			if ( is_ssl() ) {
				$url = str_replace( 'http://', 'https://', $url );
			}
		}
	}

	return $url;
}

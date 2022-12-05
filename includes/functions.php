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
 * Return the actual root blog ID where BP actually lives
 *
 * @since 1.0.0
 *
 * @return int The actual BP root blog ID
 */
function bp_multiblog_mode_get_root_blog_id() {
	return bp_multiblog_mode()->root_blog_id;
}

/**
 * Return whether the given site is the actual BP root blog
 *
 * @since 1.0.0
 *
 * @param int $site_id Optional. Site id. Defaults to the current site id.
 * @return bool Is this the actual BP root blog?
 */
function bp_multiblog_mode_is_root_blog( $site_id = 0 ) {

	// Default to the current site
	if ( empty( $site_id ) ) {
		$site_id = get_current_blog_id();
	}

	// Define return value
	$retval = false;

	// Check the actual root blog ID
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
 * @uses apply_filters() Calls 'bp_multiblog_mode_is_enabled'
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
	$is_enabled = false;

	// Check the site's setting when BP is network activated, but not for the real root blog
	if ( bp_is_network_activated() && ! bp_multiblog_mode_is_root_blog( $site_id ) ) {
		$is_enabled = (bool) get_blog_option( $site_id, '_bp_multiblog_mode_enabled', false );
	}

	/**
	 * Filter whether Multiblog is enabled for the site
	 *
	 * @since 1.0.0
	 *
	 * @param bool $is_enabled Is Multiblog enabled
	 * @param int $site_id Site id
	 */
	return apply_filters( 'bp_multiblog_mode_is_enabled', $is_enabled, $site_id );
}

/**
 * Return the sites of the network
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_get_sites'
 *
 * @param array $query_args {
 *    Optional. Query arguments for WP_Site_Query.
 *
 *    @type bool $multiblog  Whether to return only Multiblog enabled sites.
 * }
 * @return array Sites, maybe only enabled
 */
function bp_multiblog_mode_get_sites( $query_args = array() ) {

	// Provide a fallback for non-array parameter
	if ( ! is_array( $query_args ) ) {
		$query_args = array( 'multiblog' => (bool) $query_args );
	}

	// Define query args
	$query_args = wp_parse_args( $query_args, array(
		'network_id' => get_network()->id,
		'multiblog'  => false
	) );

	// Query Multiblog-only?
	$multiblog = (bool) $query_args['multiblog'];

	// Get the Network's sites
	$sites = get_sites( $query_args );

	// Filter for Multiblog enabled sites
	if ( $multiblog ) {
		foreach ( $sites as $k => $site ) {
			
			// Remove the unenabled site
			if ( ! bp_multiblog_mode_is_enabled( is_a( $site, 'WP_Site' ) ? $site->id : (int) $site ) ) {
				unset( $sites[ $k ] );
			}
		}
	}

	/**
	 * Filter the sites of the network
	 *
	 * @since 1.0.0
	 *
	 * @param array $sites Sites
	 * @param array $query_args Query arguments for WP_Site_Query
	 */
	return apply_filters( 'bp_multiblog_mode_get_sites', $sites, $query_args );
}

/**
 * Return the Multiblog enabled sites of the network
 *
 * @see bp_multiblog_mode_get_sites()
 *
 * @since 1.0.0
 *
 * @param array $query_args Optional. See {@see bp_multiblog_mode_get_sites()}.
 * @return array Multiblog enabled sites
 */
function bp_multiblog_mode_get_enabled_sites( $query_args = array() ) {

	// Parse defaults
	$query_args = wp_parse_args( $query_args, array(
		'multiblog' => true
	) );

	return bp_multiblog_mode_get_sites( $query_args );
}

/**
 * Return whether the site uses the root's taxonomy terms
 *
 * @since 1.0.0
 *
 * @return bool Use root taxonomy terms
 */
function bp_multiblog_mode_use_root_taxonomy_terms() {
	$use_root_taxonomy_terms = ! get_option( '_bp_multiblog_mode_taxonomy_terms', false );

	/**
	 * Filter whether to use the root's taxonomy terms
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_root_taxonomy_terms Use the root's taxonomy terms
	 */
	return (bool) apply_filters( 'bp_multiblog_mode_use_root_taxonomy_terms', $use_root_taxonomy_terms );
}

/**
 * Return the given site's user ids
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_get_site_users'
 *
 * @param int $site_id Optional. Site ID. Defaults to the current site.
 * @param array $query_args Optional. Additional query arguments for `WP_User_Query`.
 * @return array Site user ids
 */
function bp_multiblog_mode_get_site_users( $site_id = 0, $query_args = array() ) {

	// Define user query args
	$query_args = wp_parse_args( $query_args, array( 'fields' => 'ids' ) );

	// Site id default is set in `WP_User_Query`
	if ( ! empty( $site_id ) ) {
		$query_args['blog_id'] = (int) $site_id;
	}

	$users = get_users( $query_args );

	/**
	 * Filter the site's users
	 *
	 * @since 1.0.0
	 *
	 * @param array $users Site user ids
	 * @param int $site_id Site id
	 * @param array $query_args Arguments for `WP_User_Query`
	 */
	return apply_filters( 'bp_multiblog_mode_get_site_users', $users, $site_id, $query_args );
}

/**
 * Return the given site's user count
 *
 * @since 1.0.0
 *
 * @param int $site_id Optional. Site ID. Defaults to the current site.
 * @param array $query_args Optional. Additional query arguments for `WP_User_Query`.
 * @return int Site user count
 */
function bp_multiblog_mode_get_site_total_user_count( $site_id = 0, $query_args = array() ) {

	// Define user query args
	$query_args = wp_parse_args( $query_args, array( 'count_total' => true ) );

	// Site id default is set in `WP_User_Query`
	if ( ! empty( $site_id ) ) {
		$query_args['blog_id'] = (int) $site_id;
	}

	$user_search = new WP_User_Query( $query_args );

	return $user_search->get_results();
}

/** Members *************************************************************/

/**
 * Return whether to limit BP content to site members
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_site_members'
 *
 * @return bool Limit content to site members
 */
function bp_multiblog_mode_limit_site_members() {
	$site_members = get_option( '_bp_multiblog_mode_site_members', false );

	/**
	 * Filter whether to limit BP content to site members
	 *
	 * @since 1.0.0
	 *
	 * @param bool $site_members Limit content to site members
	 */
	return (bool) apply_filters( 'bp_multiblog_mode_site_members', $site_members );
}

/**
 * Block member profiles of non-site members
 *
 * @since 1.0.0
 *
 * @param string $member_slug Member slug
 * @return string Member slug
 */
function bp_multiblog_mode_members_block_profile( $member_slug ) {

	// Block access to profiles of non-site members
	if ( bp_multiblog_mode_limit_site_members() ) {

		// Get the queried user
		$field = bp_is_username_compatibility_mode() ? 'login' : 'slug';
		$user  = get_user_by( $field, $member_slug );

		// Get the site members
		$site_members = array_map( 'intval', bp_multiblog_mode_get_site_users() );

		// The queried user is not a site member
		if ( $user && ! in_array( $user->ID, $site_members, true ) ) {

			// Mock a non-existing user slug because a falsey value will result in
			// a failed page request unaccounted for in `bp_core_set_uri_globals()`
			$member_slug = '___BP_MULTIBLOG_MODE___';
		}
	}

	return $member_slug;
}

/**
 * Modify the members query SQL clauses
 *
 * @since 1.0.0
 *
 * @param array $sql SQL clauses
 * @param BP_User_Query $query Members query
 * @return array SQL clauses
 */
function bp_multiblog_mode_members_limit_users( $sql, $query ) {

	// Force limit member query results to users of the current site
	if ( bp_multiblog_mode_limit_site_members() ) {

		// Get the site members
		$site_members = implode( ',', bp_multiblog_mode_get_site_users() );

		// The queried user(s) should be a site member
		$sql['where']['bp_multiblog_mode_limit_site_members'] = "u.{$query->uid_name} IN ({$site_members})";
	}

	return $sql;
}

/**
 * Modify the total site member count
 *
 * @see bp_core_get_total_member_count()
 *
 * @since 1.0.0
 *
 * @param int $count Site member count
 * @return int Site member count
 */
function bp_multiblog_mode_limit_total_member_count( $count ) {
	global $wpdb;

	// Recount total site member count
	if ( bp_multiblog_mode_limit_site_members() ) {

		// Get the site members
		$site_members = implode( ',', bp_multiblog_mode_get_site_users() );

		// The queried user(s) should be a site member
		$status_sql = bp_core_get_status_sql();
		$count      = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->users} WHERE {$status_sql} AND {$wpdb->users}.ID IN ({$site_members})" );
	}

	return $count;
}

/**
 * Modify the active site member count
 *
 * @see bp_core_get_active_member_count()
 *
 * @since 1.0.0
 *
 * @param int $count Site member count
 * @return int Site member count
 */
function bp_multiblog_mode_limit_active_member_count( $count ) {
	global $wpdb;

	// Recount active site member count
	if ( bp_multiblog_mode_limit_site_members() ) {
		$bp = buddypress();

		// Get the site members
		$site_members = implode( ',', bp_multiblog_mode_get_site_users() );

		// Avoid a costly join by splitting the lookup.
		if ( is_multisite() ) {
			$sql = "SELECT ID FROM {$wpdb->users} WHERE (user_status != 0 OR deleted != 0 OR user_status != 0)";
		} else {
			$sql = "SELECT ID FROM {$wpdb->users} WHERE user_status != 0";
		}

		// The queried user(s) should be a site member
		$exclude_users     = $wpdb->get_col( $sql );
		$exclude_users_sql = !empty( $exclude_users ) ? "AND user_id NOT IN (" . implode( ',', wp_parse_id_list( $exclude_users ) ) . ")" : '';
		$count             = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(user_id) FROM {$bp->members->table_name_last_activity} WHERE component = %s AND type = 'last_activity' {$exclude_users_sql} AND user_id IN ({$site_members})", $bp->members->id ) );
	}

	return $count;
}

/** Activity ************************************************************/

/**
 * Return whether to limit the activity stream to site items
 *
 * This concerns activity items that are created in the context of the site.
 *
 * @since 1.0.0
 *
 * @return bool Limit activity items to the site
 */
function bp_multiblog_mode_activity_limit_site_items() {
	$limit_site_items = get_option( '_bp_multiblog_mode_activity_stream', false );

	/**
	 * Filter whether to limit the activity stream to site items
	 *
	 * @since 1.0.0
	 *
	 * @param bool $limit_site_items Limit activity items to the site
	 */
	return (bool) apply_filters( 'bp_multiblog_mode_activity_limit_site_items', $limit_site_items );
}

/**
 * Return whether to limit the activity stream to content of site members
 *
 * This concerns activity items that are created in the context of the wider network.
 * It may effect in blocking items that are created by retired/removed site members.
 *
 * @since 1.0.0
 *
 * @return bool Limit activity items to site members
 */
function bp_multiblog_mode_activity_limit_site_members() {

	// Prefer the Site Members setting
	$limit_site_members = bp_multiblog_mode_limit_site_members();

	// Default to the Activity Site Members setting
	if ( ! $limit_site_members ) {
		$limit_site_members = get_option( '_bp_multiblog_mode_activity_limit_site_members', false );
	}

	/**
	 * Filter whether to limit the activity stream to content of site members
	 *
	 * @since 1.0.0
	 *
	 * @param bool $limit_site_members Limit activity items to site members
	 */
	return (bool) apply_filters( 'bp_multiblog_mode_activity_limit_site_members', $limit_site_members );
}

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

	// Exclude activity items from deactivated components, except Blogs
	$components = array_diff( (array) $bp->deactivated_components, array( 'blogs' ) );

	if ( $components ) {
		$components = "'" . implode( "','", $components ) . "'";

		// Activity item(s) should not be from deactivated components
		$where['bp_multiblog_mode_exclude_deactivated_components'] = "a.component NOT IN ({$components})";
	}

	// Limit activity items belonging to the current site
	if ( bp_multiblog_mode_activity_limit_site_items() ) {
		/**
		 * Column 'item_id' can also mean id from activity/forums/groups etc.
		 * components, so only filter blogs component activities.
		 */
		$where['bp_multiblog_mode_activity_limit_site_items'] = $wpdb->prepare( "(a.item_id = %d OR a.component <> %s)", get_current_blog_id(), 'blogs' );
	}

	// Limit activity items created by site members
	if ( bp_multiblog_mode_activity_limit_site_members() ) {

		// Get the site members
		$site_members = implode( ',', bp_multiblog_mode_get_site_users() );

		// Activity item creator(s) should be a site member
		$where['bp_multiblog_mode_activity_limit_site_members'] = "a.user_id IN ({$site_members})";
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
 * @uses apply_filters() Calls 'bp_multiblog_mode_use_root_avatar_uploads'
 *
 * @return bool Use root avatar uploads
 */
function bp_multiblog_mode_use_root_avatar_uploads() {
	$use_root_avatar_uploads = ! get_option( '_bp_multiblog_mode_avatar_uploads', false );

	/**
	 * Filter whether to use the root's avatar uploads
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_root_avatar_uploads Use the root's avatar uploads
	 */
	return (bool) apply_filters( 'bp_multiblog_mode_use_root_avatar_uploads', $use_root_avatar_uploads );
}

/**
 * Return whether the site uses the root's file uploads
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_use_root_file_uploads'
 *
 * @return bool Use root file uploads
 */
function bp_multiblog_mode_use_root_file_uploads() {
	$use_root_file_uploads = ! get_option( '_bp_multiblog_mode_file_uploads', false );

	/**
	 * Filter whether to use the root's file uploads
	 *
	 * @since 1.0.0
	 *
	 * @param bool $use_root_file_uploads Use the root's file uploads
	 */
	return (bool) apply_filters( 'bp_multiblog_mode_use_root_file_uploads', $use_root_file_uploads );
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
 *
 * @uses do_action() Calls 'bp_multiblog_mode_set_root_upload_dir'
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

		/**
		 * Act after BP's upload directory is changed to BP's root upload directory
		 *
		 * @since 1.0.0
		 */
		do_action( 'bp_multiblog_mode_set_root_upload_dir' );
	}
}

/**
 * Return the upload dir for the given site
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'bp_multiblog_mode_get_upload_dir'
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

	/**
	 * Filter the site's upload directory
	 *
	 * @since 1.0.0
	 *
	 * @param bool $upload_dir The site's upload directory
	 * @param int $site_id Site id
	 */
	return apply_filters( 'bp_multiblog_mode_get_upload_dir', $plugin->upload_dir, $site_id );
}

/**
 * Return whether to modify the avatar upload directory
 *
 * @since 1.0.0
 *
 * @return bool Modify the avatar upload dir
 */
function bp_multiblog_mode_modify_avatar_upload_dir() {
	$use_file_uploads   = bp_multiblog_mode_use_root_file_uploads();
	$use_avatar_uploads = bp_multiblog_mode_use_root_avatar_uploads();

	/**
	 * Modify the avatar upload directory when:
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
	if ( bp_multiblog_mode_modify_avatar_upload_dir() && ! defined( 'BP_AVATAR_UPLOAD_PATH' ) ) {
		$uploads = bp_multiblog_mode_get_upload_dir(
			bp_multiblog_mode_use_root_avatar_uploads()
				? bp_multiblog_mode_get_root_blog_id()
				: 0
		);

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
	if ( bp_multiblog_mode_modify_avatar_upload_dir() && ! defined( 'BP_AVATAR_URL' ) ) {
		$uploads = bp_multiblog_mode_get_upload_dir(
			bp_multiblog_mode_use_root_avatar_uploads()
				? bp_multiblog_mode_get_root_blog_id()
				: 0
		);

		if ( $uploads ) {
			$url = $uploads['baseurl'];

			if ( is_ssl() ) {
				$url = str_replace( 'http://', 'https://', $url );
			}
		}
	}

	return $url;
}

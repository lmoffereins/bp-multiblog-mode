<?php

/**
 * BP Multiblog Mode Actions
 *
 * @package BP Multiblog Mode
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Sub-actions *********************************************************/

add_action( 'bp_init',       'bp_multiblog_mode_init'       );
add_action( 'bp_admin_init', 'bp_multiblog_mode_admin_init' );

/** Activity ************************************************************/

add_filter( 'bp_activity_get_where_conditions', 'bp_multiblog_mode_activity_limit_stream', 10, 5 );

/** Members *************************************************************/

add_filter( 'bp_core_set_uri_globals_member_slug', 'bp_multiblog_mode_members_block_profile'            );
add_filter( 'bp_user_query_uid_clauses',           'bp_multiblog_mode_members_limit_users',       10, 2 );
add_filter( 'bp_core_get_total_member_count',      'bp_multiblog_mode_limit_total_member_count'         );
add_filter( 'bp_core_get_active_member_count',     'bp_multiblog_mode_limit_active_member_count'        );

/** XProfile ************************************************************/

add_filter( 'bp_xprofile_get_groups', 'bp_multiblog_mode_xprofile_get_groups', 10, 2 );

/** Files ***************************************************************/

add_action( 'bp_setup_globals',           'bp_multiblog_mode_set_root_upload_dir',  1 );
add_filter( 'bp_core_avatar_upload_path', 'bp_multiblog_mode_core_avatar_upload_path' );
add_filter( 'bp_core_avatar_url',         'bp_multiblog_mode_core_avatar_url'         );

/** Admin ***************************************************************/

if ( is_admin() ) {
	add_action( 'bp_multiblog_mode_init',       'bp_multiblog_mode_admin'                    );
	add_action( 'bp_multiblog_mode_admin_init', 'bp_multiblog_mode_admin_settings_save', 100 );
}

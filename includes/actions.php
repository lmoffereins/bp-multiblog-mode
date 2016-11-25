<?php

/**
 * BP Multiblog Mode Actions
 *
 * @package BP Multiblog Mode
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** General *************************************************************/

add_action( 'bp_init',       'bp_multiblog_mode_init'       );
add_action( 'bp_admin_init', 'bp_multiblog_mode_admin_init' );

/** Activity ************************************************************/

add_filter( 'bp_activity_get_where_conditions', 'bp_multiblog_mode_activity_limit_stream', 10, 5 );

/** XProfile ************************************************************/

add_filter( 'bp_xprofile_get_groups', 'bp_multiblog_mode_xprofile_get_groups', 10, 2 );

/** Admin ***************************************************************/

if ( is_admin() ) {
	add_action( 'bp_multiblog_mode_init',       'bp_multiblog_mode_admin'                    );
	add_action( 'bp_multiblog_mode_admin_init', 'bp_multiblog_mode_admin_settings_save', 100 );
}

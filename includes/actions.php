<?php

/**
 * Plugin Functions
 *
 * @package Plugin
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** General *************************************************************/

add_action( 'bp_init',       'bp_multiblog_mode_init'       );
add_action( 'bp_admin_init', 'bp_multiblog_mode_admin_init' );

/** XProfile ************************************************************/

add_filter( 'bp_xprofile_get_groups', 'bp_multiblog_mode_xprofile_get_groups', 10, 2 );

/** Admin ***************************************************************/

if ( is_admin() ) {
	add_action( 'bp_multiblog_mode_init',       'bp_multiblog_mode_admin'                    );
	add_action( 'bp_multiblog_mode_admin_init', 'bp_multiblog_mode_admin_settings_save', 100 );
}

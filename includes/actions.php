<?php

/**
 * Plugin Functions
 *
 * @package Plugin
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Admin ***************************************************************/

if ( is_admin() ) {
	add_action( 'bp_init',       'bp_multiblog_mode_admin'                    );
	add_action( 'bp_admin_init', 'bp_multiblog_mode_admin_settings_save', 100 );
}

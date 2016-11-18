<?php

/**
 * BP Multiblog Mode Sub-Actions
 *
 * @package BP Multiblog Mode
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Run dedicated activation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'bp_multiblog_mode_activation'
 */
function bp_multiblog_mode_activation() {
	do_action( 'bp_multiblog_mode_activation' );
}

/**
 * Run dedicated deactivation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'bp_multiblog_mode_deactivation'
 */
function bp_multiblog_mode_deactivation() {
	do_action( 'bp_multiblog_mode_deactivation' );
}

/**
 * Run dedicated init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'bp_multiblog_mode_init'
 */
function bp_multiblog_mode_init() {
	do_action( 'bp_multiblog_mode_init' );
}

/**
 * Run dedicated admin init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'bp_multiblog_mode_admin_init'
 */
function bp_multiblog_mode_admin_init() {
	do_action( 'bp_multiblog_mode_admin_init' );
}

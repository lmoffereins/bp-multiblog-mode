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

	// Enable when BP is network activated, but not when we're on the real root blog or the network admin
	if ( bp_is_network_activated() && ! bp_multiblog_mode_is_root_blog() && ! is_network_admin() ) {
		$retval = get_option( '_bp_multiblog_mode_enabled', false );
	}

	return $retval;
}

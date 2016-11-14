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

	// Enable when BP is network activated, but not when we're on the real root blog
	if ( bp_is_network_activated() && ! bp_multiblog_mode_is_root_blog( $site_id ) ) {
		$retval = get_blog_option( $site_id, '_bp_multiblog_mode_enabled', false );
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
function bp_multiblog_mode_get_sites( $enabled = false ) {

	// Get the Network's sites
	$sites = get_sites( array( 'network_id' => get_network()->id ) );

	// Filter for enabled sites
	if ( $enabled ) {
		foreach ( $sites as $k => $site ) {
			
			// Remove the unenabled site
			if ( ! bp_multiblog_mode_is_enabled( $site->id ) ) {
				unset( $sites[ $k ] );
			}
		}
	}

	return $sites;
}

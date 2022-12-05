# BP Multiblog Mode #

Enable and customize BuddyPress on other sites in your network than the root blog.

## Description ##

> This WordPress plugin requires at least [WordPress](https://wordpress.org) 4.6 and [BuddyPress](https://buddypress.org) 2.7.

Run BuddyPress on your network subsites, but not all of them. Instead of defining the BP_ENABLE_MULTIBLOG constant, with this plugin you can choose on which sites BuddyPress acts as if BP_ENABLE_MULTIBLOG was defined.

In addition, this plugin allows you to:

* Limit the BuddyPress implementation to site members
* Limit Activity Stream items to those belonging to the current site
* Define Extended Profile field groups per site
* Use BuddyPress avatars and/or other file uploads from the current site

Note that you need to have BuddyPress network-activated in your Multisite installation for this plugin to work.

## Installation ##

If you download BP Multiblog Mode manually, make sure it is uploaded to "/wp-content/plugins/bp-multiblog-mode/".

Activate BP Multiblog Mode in the "Plugins" network admin panel using the "Network Activate" link. You need to use WordPress Multisite, for this plugin to work. Additionally, you need to have BuddyPress network-activated in you Multisite installation, and not have defined the `BP_ENABLE_MULTIBLOG` constant manually.

## Updates ##

This plugin is not hosted in the official WordPress repository. Instead, updating is supported through use of the [GitHub Updater](https://github.com/afragen/github-updater/) plugin by @afragen and friends.

## Contribution ##

You can contribute to the development of this plugin by [opening a new issue](https://github.com/lmoffereins/bp-multiblog-mode/issues/) to report a bug or request a feature in the plugin's GitHub repository.
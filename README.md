# BP Multiblog Mode #

Enable and customize BuddyPress on other sites than the root blog.

## Description ##

Wanting to have BuddyPress run on your subsites, but not all of them? Instead of defining the BP_ENABLE_MULTIBLOG constant, with this plugin you can choose on which sites BuddyPress acts like BP_ENABLE_MULTIBLOG was defined. In addition this plugin allows you to:

* Define Extended Profile field groups per site
* Limit Activity Stream items to those beloning to the current site [todo]
* [more to come]

Note that you need to have BuddyPress network-activated in your multisite installation for this plugin to work.

## Installation ##

If you download BP Multiblog Mode manually, make sure it is uploaded to "/wp-content/plugins/bp-multiblog-mode/".

Activate BP Multiblog Mode in the "Plugins" network admin panel using the "Network Activate" link. You need to use WordPress Multisite, for this plugin to work. Additionally, you need to have BuddyPress network-activated in you Multisite installation, and not have defined the BP_ENABLE_MULTIBLOG constant manually.

## Updates ##

This plugin is not hosted in the official WordPress repository. Instead, updating is supported through use of the [GitHub Updater](https://github.com/afragen/github-updater/) plugin by @afragen and friends.

## Contribution ##

You can contribute to the development of this plugin by creating issues and PR's in the plugin's [GitHub repository](https://github.com/lmoffereins/bp-multiblog-mode/).

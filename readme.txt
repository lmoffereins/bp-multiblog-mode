=== BuddyPress Multiblog Mode ===
Contributors: Offereins
Tags: buddypress, multisite, multiblog, bp_enable_multiblog
Requires at least: WP 4.6, BP 2.7
Tested up to: WP 4.6, BP 2.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enable and customize BuddyPress on other sites than the root blog.

== Description ==

Wanting to have BuddyPress run on your subsites, but not all of them? Instead of defining the BP_ENABLE_MULTIBLOG constant, with this plugin you can choose on which sites BuddyPress acts like BP_ENABLE_MULTIBLOG was defined. In addition, this plugin allows you to:

* Define Extended Profile field groups per site
* Limit Activity Stream items to those belonging to the current site
* [more to come]

Note that you need to have BuddyPress network-activated in your Multisite installation for this plugin to work.

== Installation ==

If you download BP Multiblog Mode manually, make sure it is uploaded to "/wp-content/plugins/bp-multiblog-mode/".

Activate BP Multiblog Mode in the "Plugins" network admin panel using the "Network Activate" link. You need to use WordPress Multisite, for this plugin to work. Additionally, you need to have BuddyPress network-activated in you Multisite installation, and not have defined the BP_ENABLE_MULTIBLOG constant manually.

== Changelog ==

= 1.0.0 =
* Initial release
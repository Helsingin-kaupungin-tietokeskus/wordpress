=== Global Hide/Remove WordPress ToolBar ===
Contributors: Don Fischer
Donate link: http://www.fischercreativemedia.com/wordpress-plugins/donate/
Tags: admin, admin bar, toolbar, settings, options, hacks, plugin, quick, remove toolbar, remove admin bar
Requires at least: 3.1
Tested up to: 3.8.1
Stable tag: 1.5

Easily hide/remove Toolbars for logged in users for WP 3.1+.

== Description ==

Easily hide/remove the front end Toolbar for logged in users. 
To remove the back end toolbar (admin bar), use the included BRUTE FORCE plugin.

Adds an option to the Settings Menu to globally turn off the front end Toolbar and/or turn off the user option in the profile to show toolbar.

Brute Force plugin (included), removes ALL toolbars (admin and front end) as well as the profile user option. No settings are available for this plugin.
WordPress does not provive an easy method to remove the Admin Bar in the back end, so this plugin takes a different approach to override the admin bar.

= Standard Plugin Features (Front End Remover): = 
* Options Page with setting options
* Remove FRONT END WordPress Toolbar for logged in users
* Remove Profile "Show Toolbar when viewing site" message/settings
* Select User level for toolbar removal

= Optional Plugin Features (BRUTE FORCE Remover): =
* Removes Admin Bar Completely
* Removes Front End Toolbar for logged in users
* Removes Profile "Show Toolbar when viewing site" message/settings
* Works with WordPress 3.8 new user interface
* NO Settings for this plugin

= TROUBLESHOOTING: =
* Do Not Use both plugins at the same time.
* Please let me know if you run into any issues with this plugin by sending an email to adminbarplugin@fischercreativemedia.com


== Installation ==

= If you downloaded this plugin: =
* Upload `global-admin-bar-hide-or-remove` folder to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress
* Once Activated, you can add access the options page from the SETTINGS menu under TOOLBAR OPTIONS

= If you install this plugin through WordPress 2.8+ plugin search interface: =
* Click Install `Global Hide/Remove Toolbar Plugin`
* Activate the plugin through the 'Plugins' menu in WordPress
* Once Activated, you can add access the options page from the SETTINGS menu under TOOLBAR OPTIONS

== Frequently Asked Questions ==
= How does it work? =
It just simply adds an option page for you to turn the front end Toolbar on or off. You can also remove the notice in the profile page to show front end Toolbar.

= Can I remove the front end Toolbar in WordPress 3.3.1+? =
On the front end, yes. 
On the backend (admin area), you need to use the Brute Force Plugin..

= I see othe plugins like this - how is yours different? =
It is not that much different. But, most of the other ones just turn it off when you activate them. Wouldn't you want to have an option page to do that yourself if you like?
Aside from that, this plugin also uses a global variable that WordPress uses to see if the Toolbar should be on or off, and then also adds another method to ensure that if WordPress changes the Toolbar functionality in the near future, the plugin should still work.

There are not that many plugins to turn off the Admin bar, though. This plugin (Brute Force version) can do that. It also works with the new WordPress 3.8 interface, were many others do not.

== Changelog ==
= 1.5 = 
* Fix profile view to work as expected. Was either on or off - not based on user role. (12/2013)
* Updated some verbiage. (12/2013)
* Added new Brute Force Plugin to forcefully turn off admin menu and front end, if desired. We get a lot of requess for this. (12/2013)
* Added Language files for translations. (12/2013)

= 1.4 =
* Add Multi-site functionalities that allow Super Admin to turn off toolbars for certain levels. (02/11/2012)
* Added option to turn off only for certain user levels. (02/11/2012)

= 1.3 =
* Fix Screenshots for new version. (02/11/2012)
* Fix deprecated user level in page call for backend page. (02/11/2012)
= 1.2 =
* Fix wording for new Admin Bar options introduced in WP 3.3.(02/06/2012)
= 1.1 =
* Fix Action to remove option in user profile page. Worked in profile, but not user-edit.
= 1.0 =
* Plugin Release. (02/23/11)

=== Ultimate Google Analytics ===
Contributors: wvanderdeijl
Donate link: http://www.oratransplant.nl/uga/
Tags: google analytics, google, statistics, stats, tracking
Requires at least: 1.5.1.3
Tested up to: 2.3.2
Stable tag: trunk

Adds Google Analytics JavaScript to each page on your weblog. Can also add tracking to outbound links, downloads from your site and mail links.

== Description ==

Plugin to add Google Analytics JavaScript to each page on your weblog without making any changes to your template. The plugin can also add tracking to outbound links, downloads from your own site and mailto: links. 

The plugin is highly configurable. Read trough the list of features below to get a feeling of what this plugin can do. You can enable and disable all features individually, although the default configuration will suffice for 90% of the users.

**Features**

* Start with a simple configuration screen and hide more advanced/complex configuration in an Advanced Configuration mode. Most users will suffice with the simple configuration screen. If you're interested in tweaking and tuning this plugin, use the advanced settings.
* Do not make any changes to feeds, as it is not wise to include JavaScript in those
* Add the same JavaScript tracker code to the admin pages if you want to track those as well (switched off with the default settings)
* When adding the JavaScript tracker code to a page, put it at the end of the body. There are quite a few WordPress plugins for Google Analytics out there. Most of them include the JavaScript in the head section. This can delay the loading of your page and is not advised by Google
* When using a WordPress theme that does not invoke the wp_footer hook as it is supposed to do, the JavaScript tracker code will be added to the head section. This can delay the loading of your page. The only way to prevent this, is to have the theme author implement the correct plugin calls, fix the theme yourself or start using another theme
* Automatic check-for-updates to warn you (on the UGA Options page) if your version of Ultimate Google Analytics is outdated
* Does not add the tracker code to the pages when a logged on user of a configurable userlevel requests a page. This can be used to ignore your own page views and not skew your statistics. (Default configuration ignores page hits from users level 8 and up)
* Add tracking to outgoing links. You can also specify hostnames which should be considered internal (e.g. www.example.com, example.com and example.org). Links to these hostnames will be considered internal and the tracking event will not be added to those links. You can also specify the prefix to append to the link when sending it to Google Analytics so your outbound links will be logged to a logical directory structure. This way, you will be able to easily identify what pages visitors clicked on to leave your site. (The default configuration is to check outgoing links in the /outgoing/ directory at Google Analytics)
* Add tracking to download links. You can specify which file extensions should be considered downloads. Only internal links to these filetypes will be tracked. Internal links are either relative links (without a hostname) or links to the hostnames you defined as internal. You can also specify the prefix to append to the link when sending it to Google Analytics so your download links will be logged to a logical directory structure. This way, you will be able to easily identify what files your visitors downloaded. (The default configuration contains a list of common file extensions to be marked as downloads. These are tracked in the /downloads/ directory at Google Analytics by default)
* Add tracking to mailto: links. You can also specify the prefix to append to the link when sending it to Google Analytics so your mailto: links will be logged to a logical directory structure. This way, you will be able to easily identify what mailto: links your visitors clicked. (The default configuration is to track mailto: links in the /mailto/ directory at Google Anaytics)
* Specify if the outgoing, download and mailto: links should be tracked in the postings only, the comments, the comment author URL or any combination of these three.

== Installation ==

Installing is as simple as downloading the file from this site, placing it in your wp-content/plugins directory and activating the plugin. For the more detailed instructions read on.

1. Get a Google Analytics account at http://analytics.google.com.
1. Download the Ultimate Google Analytics ZIP file (see download section above)
1. Extract the zipfile and place the PHP file in the wp-content/plugins directory of your WordPress installation
1. Go to the administration page of your WordPress installation (normally at http://www.yourblog.com/wp-admin)
1. Click on the Plugins tab and search for Ultimate Google Analytics in the list
1. Activate the Ultimate Google Analytics plugin
1. You can now find an Ultimate GA page under Options to set the options of the plug-in
1. If you're comfortable reading HTML and feel like it, you can look at the HTML source code of your blog pages to see the included Google Analytics tracker code at the end of the page. You could also check if an onClick event is added to the outbound, download and/or mailto: links as specified in your options. Make sure that you’re not checking this as a logged on user if you’ve enabled Ignore Logged On Users. In that case, log out of WordPress before doing these checks. Also make sure you request your blog page at least twice to give the Ultimate Google Analytics plugin the change to detect is the wp_footer hook is called by your template.
1. Wait until Google Analytics updates your reports. Currently it seems like this can take up to 24 hours. Note that by default Google Analytics selects a week ending yesterday as its reporting period. Click on today in the lefthand calendar to see today’s statistics, if they’ve already been reported.

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot


=== Add Local Avatar ===
Contributors: peterwsterling
Donate link: http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/
Author URI: http://www.sterling-adventures.co.uk/
Plugin URI: http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/
Tags: avatar, gravatar, images, user, pictures, photos, global, local
Requires at least: 2.5
Tested up to: 3.8
Stable tag: trunk

This plugin adds the ability to manage global, local, and use twitter avatars.

== Description ==
Adds local (private) avatars for your users.  Maybe your users don't want a global avatar, aren't IT savvy enough to set a Gravatar up, simply want a private avatar for your blog, or any other reason too...  Well, from WordPress 2.5 onwards, you can manage local and global avatars with this plugin.  Plus you can use your Twitter avatar too!

<b>From v9.0 Avatars requires PHP version 5.1.3 or greater.</b>

The plug-in now delivers the much requested feature of allowing users to upload their own local avatar.  And, the latest new feature allows your users to use their <a href="http://twitter.com" rel="nofollow">Twitter</a> avatar.  The order of precedence is; *Local*, *Twitter*, then *Global*.  In other words; if you define a Local avatar for a user, that will be used, if there is no Local avatar defined and a Twitter ID is given for a user, the plugin will try to use that avatar.  Lastly, where no Local avatar is defined and no Twitter ID is given (or Twitter doesn't return a match), a unique Global avatar (<a href="http://gravatar.com/" rel="nofollow">Gravatar</a>) will be generated.

The local avatar feature list:<ul>
<li>Have global or local avatars for your users.</li>
<li>Use Twitter avatars for your users.</li>
<li>Allow avatars to be included in posts.  This is achieved securely; e-mail addresses are not exposed.</li>
<li>Detailed control over the default avatar to use for those users who do not have a global or local avatar.  Allows a choice of a custom image, 'mystery' blank image, blank, Wavatar, Monster ID, or Identicon.</li>
<li>Wraps avatars in code to support SnapShots (www.snap.com), should your site use these.</li>
<li>Permits users to upload their own local avatars.</li>
</ul>

You may also be interested in the sister <a href="http://www.sterling-adventures.co.uk/blog/2009/01/01/comments-with-avatars/">Comments with Avatars</a> plugin.

Plus there is now a new sidebar widget extension to the plug-in that provides a feature allowing users to manage their avatar from the sidebar without having to use WordPress' profile administration page.  It provides the same capability as the profile page.  This new feature provides yet more support for users who may not, for example, be savvy enough to use the WordPress administration interface.  Get the extension <a href="http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/">here</a>.

<a href="http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/">Donations</a> are welcome and help keep development going.


== Installation ==
* Just put the plug-in into your plug-in directory and activate it.
* Use the form, <code>Users</code> &raquo; <code>Avatars</code>, to define any local avatars for your users.  Specify the URI for an avatar image, something like <code>http://your.domain/avatars/image.jpg</code>, where <code>avatars</code> is a directory containing your local images.
* Also, you may set a default size (in pixels) for avatars and define a default image (e.g. <code>http://your.domain/avatars/default.jpg</code>) to use when no local or global avatar is available.
* Plus, you can also take advantage of the Gravatar.com feature to use Wavatar, Monster ID, Identicon, etc.
* Use: <code>&lt;?php $avtr = get_avatar(id [, size [, default-image-url]]); echo $avtr; ?&gt;</code>
* More example code to include in your template files is documented on the <code>Users</code> &raquo; <code>Avatars</code> page.


== Change Log ==
Changes and feature additions for the Local Avatar plugin:<ul>
<li>0.1 - Initial release.</li>
<li>1.0 - Added pagination of users list.</li>
<li>2.0 - Added pagination of the commenters list too.</li>
<li>2.1 - Added example formatting information.</li>
<li>3.0 - Added ability to place avatars in written post content (plus other tweaks).</li>
<li>3.1 - Minor tweaks to usage text and options.</li>
<li>3.2 - Added check for administration pages to stop user URL wrapping breaking comment editing.</li>
<li>3.3 - Spelling fixes!</li>
<li>4.0 - Wavatar, Monster ID and Identicon can be used.</li>
<li>4.1 - Author credit.</li>
<li>4.2 - Fix for credit option un-setting.</li>
<li>5.0 - Avatar options should only be managed by Administrators.</li>
<li>5.1 - Minor fix to repetition of show avatars WordPress setting.</li>
<li>5.2 - Cope with WP 2.6 avatar default.</li>
<li>6.0 - Added feature to allow users to upload their own avatar.</li>
<li>6.1 - Explanation of directory structure and 'chmod' fix, thanks to Tobias Schwarz.</li>
<li>6.2 - Improved unique file name creation, optional avatar upload resizing/cropping, and PHP 4 fix. Thanks to Gioele Agostinelli.</li>
<li>6.3 - Oops, a bug (mistake) with the scaling size fixed.</li>
<li>6.4 - Error in file naming fixed, with some help from "noyz319".</li>
<li>6.5 - Upload file type check (thanks to SumoSulsi) and internationalisation preparation.</li>
<li>6.6 - Fix to scaling when upgrading from old version of plugin without scaling option.</li>
<li>6.7 - Fix for lowercase extensions.</li>
<li>6.8 - Option for nickname / first name & surname.</li>
<li>7.0 - Support for user profile widget plug-in.</li>
<li>7.1 - Update for Marc Adrian to provide support for option for showing text in the optional widget.</li>
<li>7.2 - Class added to help with styling widget.</li>
<li>7.3 - Fix for user avatar upload that doesn't need re-sizing and a Russian translation.</li>
<li>7.4 - Root directory no longer DOCUMENT_ROOT.</li>
<li>7.5 - Use DOCUMENT_ROOT option for legacy users.</li>
<li>7.6 - Check for required core WP upload functions, only required for themes that expose the user profile pages.</li>
<li>8.0 - Added option to try to use a Twitter avatar.</li>
<li>8.1 - Simplified Twitter image URL logic.</li>
<li>8.2 - Control anchor wrapping of Avatars.</li>
<li>8.3 - Allow Twitter ID for optional widget.</li>
<li>9.0 - WPMU/Network re-work.  Thanks to Michael D. Tran for his efforts!</li>
<li>9.1 - Update for Admin Bar in WordPress v3.1</li>
<li>9.2 - Fix for local avatar upload to cope with the ever changing WP!</li>
<li>10.0 - New option to upsize local avatar images that are smaller than the set size.  Thanks to Nicholas Craig.</li>
<li>10.1 - Update for networked WP.  Thanks to Michael D Tran.</li>
<li>10.2 - alt tag for avatar img.</li>
<li>10.3 - Compress the paging header for Avatar tables, plus tidy up of table output code.</li>
<li>10.4 - Add 'retro' dynamic automatic Avatar type.</li>
<li>10.5 - Small change to hack for new (WP 3.3) Admin Bar style.</li>
<li>10.6 - Avatar settings link.</li>
<li>10.7 - Accidental removal of user editing avatar uploading section, now back where it should be!</li>
<li>11.0 - Crop user upload image for use as avatar.  Thanks to Barry Israel for the inspiration.</li>
<li>11.1 - Sanitize upload filenames.</li>
<li>11.2 - Fix for WP_PLUGIN_DIR (and updates for deprecated WP function calls).</li>
<li>11.3 - Fixes and removal of deprecated functions thanks to <strong>Ponç J.</strong>  Thanks!</li>
<li>12.0 - Use new Twitter OAuth API v1.1.</li>
<li>12.1 - Thanks to Per for pointing out that the Profile display needed moving down a bit...</li>
</ul>


== Screenshots ==
1. An example of the main avatars administration page.  Manage users global and local avatars etc.
2. The avatars options configuration section of the main avatars administration page.
3. The WordPress profile page shows the new floating section showing the current avatar.  Click manage to change.
4. Avatar Management popup.  Allowing upload of local avatars, with crop option, and setting of a Twitter ID.
5. Sample avatars: local, global, retro, gravatar, blank, wavatar, identicon, monsters, twitter - the lot!
6. Example of the optional simple sidebar widget allowing users to manage their own avatar.  Available from the plugin's homepage.


== Thanks ==
A lot of hard work has gone in to this plug-in, much of it at the request of people who use it, and I hope it is useful to you too!  Please consider these things...<ul>
<li>Please recognise your use of the plug-in on your blog.  Maybe post an article (with a link back to http://www.sterling-adventures.co.uk/blog/) to say how you've integrated the plug-in into your site?  Or simply make sure the Author Credit option is enabled on the options page.</li>
<li>Remember that a lot of the features of the plug-in are a direct result of people asking for them.  So, please get in contact and let me know what you think.</li>
<li>If you do find value in using the plug-in, please consider a donation at http://www.sterling-adventures.co.uk/blog/2008/03/01/avatars-plugin/  Size isn't important, it's the thought that counts!</li>
</ul>

Enjoy!


== Frequently Asked Questions ==

= How to install Avatars in a WordPress Multi Site environment =
Getting this plugin to work under a WordPress (WP) Multi Site (MS) configuration can be tricky, but as the instructions for WP MS installations say, you are expected to know what you are doing and have basic WordPress development, PHP, HTML, CSS, and UNIX/Linux administration experience.  See http://codex.wordpress.org/Create_A_Network.  However, here are some steps that have worked for others configuring this plugin for WP MS.<ol>
<li>In the <strong>Network Admin</strong> area, <em>network activate</em> the plugin.</li>
<li>Now under <strong>Users</strong> &raquo; <strong>Avatars</strong> look for the User Uploads option, check the box and save.</li>
</ol>

= When to use the Legacy Method for User Uploads =
Often the *legacy method* for User Uploads is required when using sub-domains.  Therefore, this is also regularly required for WordPress Multi Site setups.

= Setting the User Upload directory =
The directory uploaded Avatars are stored in is set by the field next to the enable user uploads option.  There is an example <code>/avatars</code> given in the instructions beneath the field.
This directory must be relative to your web-root.  For example, if all of your HTML files and your WP installation are in a directory <code>public_html</code> provided by your web host, this is the web-root.  So the <code>/avatars</code> directory goes here, i.e. <code>~/public_html/avatars</code>.
Make sure the privileges are set correctly.

= Where are the Avatar options =
The options for the Avatar plugin are available on the <code>User</code> &raquo; <code>Avatars</code> page, at the bottom.

= Show the Avatar of the logged in User =
If you are trying to show, say somewhere in a sidebar, the Avatar of the user who is logged-in try code something like this:
<code>
&lt;?php
	global $current_user;
	$known = !empty($current_user->nickname);
?&gt;
&lt;div style="float: right; margin: 4px;"&gt;
&lt;?php if($known) echo get_avatar($current_user->id, 30); ?&gt;
&lt;/div&gt;
</code>

= Optional Widget =
If you are considering purchasing the optional user profile widget, please make sure that this free Avatars plugin is configured properly and functioning correctly first.  The widget <strong>cannot</strong> work until the <strong>plugin</strong> is setup correctly.  The widget is not required in any way to make full use of all the plugin's features.

= Styling =
Got problems with you layout?  Take a look at adjusting the <code>avatars.css</code> file.  Perhaps removing <code>clear: both;</code> if alignment is being disrupted.

= New Ideas? =
Got your own great idea for addition in to the Avatars plugin's capabilities?  Let me know about it and I'll consider if it's possible to include in the next release...


== Internationalisation ==
Avatars provides support for language translations.  Ensure WPLANG is set in your <code>wp-config</code> file.
To help with the available translations create a .po translation and compile a .mo file.  If you would like this to be included in the general distribution please send these files back via the <a href="http://www.sterling-adventures.co.uk/Comments/feedback.php">feedback link</a>.  I can't accept any credit for these languages files, nor can I guarantee they are correct.

Available translations, from the English default, are:<ul>
<li>Persian (WPLANG = fa_IR).  Thanks to Mustafa Sufi.</li>
<li>Russian (WPLANG = ru_RU).  Thanks to Levati.</li>
<li>French (WPLANG = fr_FR).  Thanks to Adrien Schvalberg.</li>
<li>Spanish (WPLANG = es_ES).  Thanks to Naceira - http://www.naceira.com/</li>
<li>Ukrainian (WPLANG = uk_UK).  Thanks to Vadim Nekhai, website: http://onix.name/portfolio/</li>
<li>Portuguese Brazil (WPLANG = pt_BR).  Thanks to Steff.</li>
<li>Japanese (WPLANG = pt_ja).  Thanks to Kazuhiko Maeda.</li>
<li> Czech (WPLANG = cs_CZ).  Thanks to Dominik Chrástecký.</li>
</ul>

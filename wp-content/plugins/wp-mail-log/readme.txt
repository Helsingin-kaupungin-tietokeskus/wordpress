=== WP Mail Log ===
Contributors: chmac
Donate link: http://www.callum-macdonald.com/code/donate/
Tags: wp-mail, wp_mail, wpmail, wp-mail-smtp
Requires at least: 2.5
Tested up to: 3.1.1
Stable tag: 0.2

Log all calls to wp_mail() to a file wp-content/plugins/wp-mail-log/wp-mail-log.txt for debugging. Delete this file afterwards.

== Description ==

Log all calls to wp_mail() to a file wp-content/plugins/wp-mail-log/wp-mail-log.txt for debugging. Delete this file afterwards.

Please be advised, this file is probably publicly accessible on your server, and could well contain private information. This plugin is for debugging purposes only, I encourage you to delete the log file and deactivate the plugin once you’re finished with it.

You can view the file using the WordPress plugin editor. It will appear in the same folder as this plugin.

== Installation ==

Usual method.

== Frequently Asked Questions ==

New plugin, so no questions have yet been asked. :-)

If you have a question, please read the code. Failing that, you can try here:
http://www.callum-macdonald.com/code/wp-mail-log/

== Screenshots ==

No screenshots.

== Support Questions ==

If you have a question, please read the code. Failing that, you can try here:
http://www.callum-macdonald.com/code/wp-mail-log/

== Changelog ==

= 0.2 =
* Dump both phpmailer_init calls and wp_mail calls.
* Move login file to the plugin directory so it's visible in the plugin editor.

= 0.1 =
* Initial version.

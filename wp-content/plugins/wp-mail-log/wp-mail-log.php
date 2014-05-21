<?php
/*
Plugin Name: WP Mail Log
Version: 0.2
Plugin URI: http://www.callum-macdonald.com/code/
Description: Log all calls to wp_mail() to a file wp-content/plugins/wp-mail-log/wp-mail-log.txt for debugging. Delete this file afterwards.
Author: Callum Macdonald
Author URI: http://www.callum-macdonald.com/
*/

// Set priority to one to fire first before plugins
add_filter('wp_mail', 'log_wp_mail', 1);
add_filter('phpmailer_init', 'log_wp_mail', 1);

function log_wp_mail($args) {
	$log_message = "\n---MARK---\n" . var_export(array('date' => date('r'), 'args' => $args, backtrace => debug_backtrace()), true);
	// Now write the log message somewhere, for example:
	$fp = fopen(dirname(__FILE__) . '/wp-mail-log.txt', 'a+');
	fwrite($fp, $log_message);
	fclose($fp);
	return $args;
}


?>

<?php


global $hri_analytics_db_version;
$hri_analytics_db_version = "1.0";

/*
 * Create database
 */
function hri_analytics_install() {
	ini_set('error_reporting', E_ALL);
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	global $wpdb;
	global $hri_analytics_db_version;
	
	// Create table for nelonen reports episodes
	
	
	$table_name = "wp_hri_analytics_downloads_last_30d";

	$sql = "
	    CREATE TABLE IF NOT EXISTS `$table_name` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `data_post_id` bigint(20) NOT NULL DEFAULT '0',
          `event_action` varchar(255) DEFAULT NULL,
          `event_label` text,
          `event_count` bigint(20) NOT NULL DEFAULT '0',
          `event_count_unique` bigint(20) NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`)
        );";
	
	$wpdb->query($sql);
	
	$table_name = "wp_hri_analytics_pageviews_last_30d";

	$sql = "
	    CREATE TABLE IF NOT EXISTS `$table_name` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  		  `data_post_id` BIGINT(20) NOT NULL DEFAULT 0,
          `page_lang` VARCHAR(50) NOT NULL DEFAULT '',
          `page_path` TEXT,
          `page_visits` INT(4) NOT NULL DEFAULT 0,
          `page_visitors` INT(4) NOT NULL DEFAULT 0,
          `page_pageviews` INT(4) NOT NULL DEFAULT 0,
          PRIMARY KEY (`id`)
        );";

	$wpdb->query($sql);
	
	$table_name = "wp_hri_analytics_downloads_by_day";

	$sql = "
		CREATE TABLE IF NOT EXISTS $table_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		event_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
		data_post_id BIGINT(20) NOT NULL DEFAULT 0,
        event_action VARCHAR(255) NOT NULL DEFAULT '',
        event_label TEXT,
        event_count INT(4) NOT NULL DEFAULT 0,
        event_count_unique INT(4) NOT NULL DEFAULT 0,
		UNIQUE KEY id (id)
	);";
	
	$wpdb->query($sql);
	
	$table_name = "wp_hri_analytics_pageviews_by_day";

	$sql = "
		CREATE TABLE IF NOT EXISTS $table_name (
		id BIGINT(20) NOT NULL AUTO_INCREMENT,
		event_date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
		data_post_id BIGINT(20) NOT NULL DEFAULT 0,
        page_lang VARCHAR(50) NOT NULL DEFAULT '',
        page_path TEXT,
        page_visits INT(4) NOT NULL DEFAULT 0,
        page_visitors INT(4) NOT NULL DEFAULT 0,
        page_pageviews INT(4) NOT NULL DEFAULT 0,
		UNIQUE KEY id (id)
	);";
	
	$wpdb->query($sql);

	
	add_option("hri_analytics_db_version", $hri_analytics_db_version);
}

function hri_analytics_update_db_check() {
	global $hri_analytics_db_version;
	if (get_site_option('hri_analytics_db_version') != $hri_analytics_db_version) {
		hri_analytics_install();
	}
}

add_action('plugins_loaded', 'hri_analytics_update_db_check');

<?php
/*
Plugin Name: HRI Google Analytics
Description: Import Google analytics
Version: 0.1
Author: Oskari Kokko
Author URI: http://barabra.fi
License: A "Slug" license name e.g. GPL2
*/

require_once(dirname(__FILE__) . '/hri_analytics_helpers.php');

register_activation_hook(dirname(__FILE__) . '/hri_analytics_helpers.php', 'hri_analytics_install');

class hri_analytics_admin
{
    function __construct() {
        add_action( 'admin_menu', array($this, 'init') );
    }

    public function init() {
        add_menu_page('HRI Google analytics', 'HRI Google analytics', 'manage_options', 'hri_analytics_admin_actions_fp', array($this, 'admin_actions_fp'));
        add_submenu_page( 'hri_analytics_admin_actions_fp', 'Import Google Analytics data for last 30 days', 'Import Google Analytics data for last 30 days', 'manage_options', 'hri_analytics_admin_actions_import_ga_30d', array($this, 'admin_actions_import_ga_30d') );
        add_submenu_page( 'hri_analytics_admin_actions_fp', 'Import Google Analytics data for last day', 'Import Google Analytics data for last day', 'manage_options', 'hri_analytics_admin_actions_import_ga_1d', array($this, 'admin_actions_import_ga_1d') );
        add_submenu_page( 'hri_analytics_admin_actions_fp', 'Import Google Analytics data for all days', 'Import Google Analytics data for all days', 'manage_options', 'hri_analytics_admin_actions_import_ga_all', array($this, 'admin_actions_import_ga_all') );
        add_submenu_page( 'hri_analytics_admin_actions_fp', 'Import Google Analytics data for data page views', 'Import Google Analytics data for data page views', 'manage_options', 'hri_analytics_admin_actions_import_ga_page', array($this, 'admin_actions_import_ga_page') );
    }
    
    public function admin_actions_fp() {
        echo '<div class="wrap">';
        echo '<h1>HRI google analytics</h1>';
        echo '<ul>';
        echo '<li><a href="/fi/wp-admin/admin.php?page=hri_analytics_admin_actions_import_ga_30d">Import Google Analytics data for last 30 days</a></li>';
        echo '<li><a href="/fi/wp-admin/admin.php?page=hri_analytics_admin_actions_import_ga_1d">Import Google Analytics data for last day</a></li>';
        echo '<li><a href="/fi/wp-admin/admin.php?page=hri_analytics_admin_actions_import_ga_all">Import Google Analytics data for all days</a></li>';
        echo '</ul>';
        echo '</div>';
    }
    
    public function admin_actions_import_ga_30d() {
        echo '<div class="wrap">';
        echo '<h1>Import Google Analytics data</h1>';
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $hri_analytics_runner = new hri_analytics;
        echo $hri_analytics_runner->hri_analytics_run_importer_ga_30d(true);
        switch_to_blog($orginal_blog);
        echo '</div>';
    }
    public function admin_actions_import_ga_1d() {
        echo '<div class="wrap">';
        echo '<h1>Import Google Analytics data</h1>';
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $hri_analytics_runner = new hri_analytics;
        echo $hri_analytics_runner->hri_analytics_run_importer_ga_1d(true);
        switch_to_blog($orginal_blog);
        echo '</div>';
    }
    public function admin_actions_import_ga_all() {
        echo '<div class="wrap">';
        echo '<h1>Import Google Analytics data</h1>';
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $hri_analytics_runner = new hri_analytics;
        echo $hri_analytics_runner->hri_analytics_run_importer_ga_all(true);
        switch_to_blog($orginal_blog);
        echo '</div>';
    }
    
    public function admin_actions_import_ga_page() {
        echo '<div class="wrap">';
        echo '<h1>Import Google Analytics data page data</h1>';
        $date_str = '';
        if (   isset($_POST['hri_analytics_date'])
            && $_POST['hri_analytics_date'] != '') {
            
            $date_ts = strtotime($_POST['hri_analytics_date']);
            $date_str = date('d.m.Y', strtotime($_POST['hri_analytics_date']) + (60*60*24));
        }
        ?>
        <script>

        var $ = jQuery;

        $(document).ready(function () {
            var now = new Date().getTime();
            if (parseInt($('#date_ts').val()) < now) {
                setTimeout('reload_form()', 10000);
            }
        });
        
        function reload_form() {
            $('#ga_form').submit();
        }

        </script>
        <form method="post" id="ga_form">
            <input type="hidden" name="date_ts" id="date_ts" value="<?php echo $date_ts; ?>" />
            <label>
                Select date<br />
                <input value="<?php echo $date_str; ?>" type="text" class="datepicker" name="hri_analytics_date" />
            </label>
            <br /><br />
            <input type="submit" />
        </form>
        <?php
        if (isset($date_ts)) {
            echo "<br />Getting data for " . $_POST['hri_analytics_date'];
            $hri_analytics_runner = new hri_analytics;
            echo $hri_analytics_runner->hri_analytics_run_importer_ga_page(date('Y-m-d', $date_ts), true);
        }
        echo '</div>';
    }
}

class hri_analytics
{
    function __construct() {
//        add_action('parse_request', array($this, 'hri_analytics_parse_request'));
//        add_filter('query_vars', array($this, 'hri_analytics_query_vars'));
//        add_filter( 'init', array($this, 'hri_analytics_rewrite_rules'));
        // Use production settings as fallback.
        if(!defined('GOOGLE_ANALYTICS_TABLE_ID')) { define('GOOGLE_ANALYTICS_TABLE_ID', 37567144); }
        if(!defined('GOOGLE_ANALYTICS_CLIENT_ID')) { define('GOOGLE_ANALYTICS_CLIENT_ID', '258479274583.apps.googleusercontent.com'); }
        if(!defined('GOOGLE_ANALYTICS_SERVICE_ACCOUNT_NAME')) { define('GOOGLE_ANALYTICS_SERVICE_ACCOUNT_NAME', '258479274583@developer.gserviceaccount.com'); }
        if(!defined('GOOGLE_ANALYTICS_KEY_FILE')) { define('GOOGLE_ANALYTICS_KEY_FILE', '/d3558100d56be098ff190f7d6c2455fa5f69fb7f-privatekey.p12'); }
    }
    
//    public function hri_analytics_query_vars($vars) {
//        $new_vars = array('hri_analytics');
//        $vars = array_merge($new_vars, $vars);
//        return $vars;
//    }
    
//    public function hri_analytics_parse_request($wp) {
//        if (array_key_exists('hri_analytics', $wp->query_vars)
//                && $wp->query_vars['hri_analytics'] == 'run_importer_30d') {
//            hri_analytics::hri_analytics_run_importer_cron_30d($wp);
//        }
//
//        if (array_key_exists('hri_analytics', $wp->query_vars)
//                && $wp->query_vars['hri_analytics'] == 'run_importer_1d') {
//            hri_analytics::hri_analytics_run_importer_cron_1d($wp);
//        }
//        if (array_key_exists('hri_analytics', $wp->query_vars)
//                && $wp->query_vars['hri_analytics'] == 'run_importer_all') {
//            hri_analytics::hri_analytics_run_importer_cron_all($wp);
//        }
//    }
    
//    public function hri_analytics_rewrite_rules( $wp_rewrite ) {
//        add_rewrite_rule('hri_analytics/run_importer_30d','index.php?hri_analytics=run_importer_30d','top' );
//        add_rewrite_rule('hri_analytics/run_importer_1d','index.php?hri_analytics=run_importer_1d','top' );
//        add_rewrite_rule('hri_analytics/run_importer_all','index.php?hri_analytics=run_importer_all','top' );
//
//        flush_rewrite_rules();
//    }
    
    /** Solves the page name and language from given data. 
     *
     * @param mixed reference $parts - Note: referencing is not required, just a little optimization.
     * @return string reference $page_name
     * @return string reference $page_lang
     */
    private function solveNameAndLangFromParts(&$parts, &$page_name, &$page_lang) {
        
        foreach($parts as $part_key => $part) {

            if($part == 'data' || $part == 'dataset') {
                        
                $tmp_plus = $part_key + 1;
                $tmp_minus = $part_key - 1;
                
                if(isset($parts[$tmp_plus]) && $parts[$tmp_plus] != '') {
                    
                    $page_name = $parts[$tmp_plus];
                }
                if(isset($parts[$tmp_minus]) && $parts[$tmp_minus] != '') {
                    
                    $page_lang = $parts[$tmp_minus];
                }
                // Bugfix: if $tmp_minus was unset, $page_lang retained its default value.
                //         This sometimes caused values for /fi/dataset/xxx to be overwritten
                //         with values for /dataset/xxx, essentially wasting most of them.
                else {

                    $page_lang = '/';
                }
            }
        }
    }
    
    public function hri_analytics_run_importer_cron_30d($wp) {
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $this->hri_analytics_run_importer_ga_30d();
        switch_to_blog($orginal_blog);
        
    }
    
    public function hri_analytics_run_importer_ga_30d($output = false) {

        $tmp = '';
        $startDate = date('Y-m-d', strtotime("-31 days"));
        $endDate = date('Y-m-d', strtotime("-1 days"));

        
        global $wpdb;
                
        $service = hri_analytics::getGoogleAnalyticsService();

        $optParams = array('filters'=>'ga:eventCategory==Ladattavat tiedostot ja linkit', 'dimensions' => 'ga:eventCategory,ga:eventAction,ga:eventLabel', 'sort' => '-ga:totalEvents');
        
        $result = $service->data_ga->get('ga:' . GOOGLE_ANALYTICS_TABLE_ID, $startDate, $endDate, 'ga:totalEvents,ga:uniqueEvents', $optParams);
        if (!isset($result['rows'])) {
            if ($output) {
                return 'Error';
            } else {
                return false;
            }
        }
        
        
        $table_name = "wp_hri_analytics_downloads_last_30d";

    	$sql = "TRUNCATE $table_name; ";

    	$wpdb->query($sql);
    	
    	foreach ($result['rows'] as $row) {
    	    
    	    $post_id = 0;
            $post_id_arr = $wpdb->get_results("
                SELECT post.id from wp_posts post 
                JOIN wp_postmeta meta on post.ID = meta.post_id 
                WHERE meta.meta_key LIKE 'resources\__\_url'
                AND meta.meta_value = '$row[2]'
                AND post.post_type = 'data' 
                AND post.post_status = 'publish'
                LIMIT 1;
            ");

        	if (count($post_id_arr) > 0) {
        	    $post_id = $post_id_arr[0]->id;
        	}
    	    
    	    $wpdb->insert(
                $table_name, 
                array(
                    'data_post_id' => $post_id,
                    'event_action' => $row[1],
                    'event_label' => $row[2],
                    'event_count' => $row[3],
                    'event_count_unique' => $row[4],
                )
            );
    	}
    	
    	
    	$optParams = array('filters'=>'ga:pagePath=~.*/(data|dataset)/.*', 'dimensions' => 'ga:pagePath', 'sort' => '-ga:pageviews');
        
        $result = $service->data_ga->get('ga:' . GOOGLE_ANALYTICS_TABLE_ID, $startDate, $endDate, 'ga:visits,ga:visitors, ga:pageviews', $optParams);
        
        if (!isset($result['rows'])) {
            if ($output) {
                return 'Error';
            } else {
                return false;
            }
        }
        
        
        $table_name = "wp_hri_analytics_pageviews_last_30d";

    	$sql = "TRUNCATE $table_name; ";

    	$wpdb->query($sql);
    	// throw new Exception(print_r($result['rows'], true)); // DEBUG
    	foreach ($result['rows'] as $row) {
    	    
    	    
    	    $page_path = $row[0];
            $page_visits = $row[1];
            $page_visitors = $row[2];
            $page_pageviews = $row[3];
            
            $parts = explode('/', $page_path);
            
            if (count($parts) < 3) {
                continue;
            }
            
            $page_name = '';
            $page_lang = 'fi';
            
            $this->solveNameAndLangFromParts($parts, $page_name, $page_lang);

            if ($page_name == '') {
                echo "empty page name for page path: {$page_path}<br />";
                continue;
            }

    	    $post_id = 0;
            $post_arr = $wpdb->get_results("
                SELECT post.id from wp_posts post 
                JOIN wp_postmeta meta on post.ID = meta.post_id 
                WHERE meta.meta_key = 'ckan_url'
                AND post.post_name = '{$page_name}' OR meta.meta_value LIKE '%{$page_name}'
                AND post.post_type = 'data' 
                AND post.post_status = 'publish';
            ");
     
            if(count($post_arr) == 0 || !isset($post_arr[0]->id)) {
                continue;
            }
            else {
                $post_id = $post_arr[0]->id;
            }


            $wpdb->insert(
                $table_name, 
                array(
                    'data_post_id' => $post_id,
                    'page_lang' => $page_lang,
                    'page_path' => $page_path,
                    'page_visits' => $page_visits,
                    'page_visitors' => $page_visitors,
                    'page_pageviews' => $page_pageviews,
                )
            );
    	}
        
        
        
        if ($output) {
            return $tmp;
        }

    }
    
    public function hri_analytics_run_importer_cron_1d($wp) {
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $this->hri_analytics_run_importer_ga_1d();
        switch_to_blog($orginal_blog);
        
    }
    
    public function hri_analytics_run_importer_ga_1d($output = false) {

        $tmp = '';
        $startDate = date('Y-m-d', strtotime("-2 days"));
        $endDate = $startDate;
        $dateSQL = date('Y-m-d 00:00:00', strtotime($startDate));

        
        global $wpdb;

        $service = hri_analytics::getGoogleAnalyticsService();

        $optParams = array('filters'=>'ga:eventCategory==Ladattavat tiedostot ja linkit', 'dimensions' => 'ga:eventCategory,ga:eventAction,ga:eventLabel', 'sort' => '-ga:totalEvents');
        
        $result = $service->data_ga->get('ga:' . GOOGLE_ANALYTICS_TABLE_ID, $startDate, $endDate, 'ga:totalEvents,ga:uniqueEvents', $optParams);
        if (!isset($result['rows'])) {
            if ($output) {
                return 'Error';
            } else {
                return false;
            }
        }
        
        
        $table_name = "wp_hri_analytics_downloads_by_day";
    	
    	foreach ($result['rows'] as $row) {
    	    
    	    $post_id = 0;
            $post_arr = $wpdb->get_results("
                SELECT post.id, meta.meta_key from wp_posts post 
                JOIN wp_postmeta meta on post.ID = meta.post_id 
                WHERE meta.meta_key LIKE 'resources\__\_url'
                AND meta.meta_value = '$row[2]'
                AND post.post_type = 'data' 
                AND post.post_status = 'publish';
            ");

        	if (count($post_arr) > 0) {
                foreach ($post_arr as $tmp_post) {
                    $meta_num = str_replace('resources_', '', str_replace('_url', '', $tmp_post->meta_key));
                    $meta_format = $wpdb->get_results("
                        SELECT * FROM wp_postmeta
                        WHERE
                            meta_key = 'resources_".$meta_num."_format'
                            AND meta_value = '" . $row[1] . "'
                            AND post_id = " . $tmp_post->id . " ;
                    ");
                    if (count($meta_format > 0)) {
                        $post_id = $tmp_post->id;
                        break;
                    }
                }
        	    
        	}
        	
            $tmp_data = $wpdb->get_row('
				SELECT id FROM ' . $table_name . '
				WHERE event_date = "' . $dateSQL . '"
				AND event_label =  "' . $row[2] . '"
			;');

            if (isset($tmp_data)
                && isset($tmp_data->id)
                && $tmp_data->id != ''
            ) {
                $tmp_data_id = $tmp_data->id;
                $wpdb->update(
                    $table_name, 
                    array(
                        'data_post_id' => $post_id,
                        'event_action' => $row[1],
                        'event_count' => $row[3],
                        'event_count_unique' => $row[4],
                    ),
                    array('id' => $tmp_data_id)
                );
            } else {
                $wpdb->insert(
                    $table_name, 
                    array(
                        'data_post_id' => $post_id,
                        'event_date' => $dateSQL,
                        'event_action' => $row[1],
                        'event_label' => $row[2],
                        'event_count' => $row[3],
                        'event_count_unique' => $row[4],
                    )
                );
            }
    	}
        
        
        
        if ($output) {
            return $tmp;
        }

    }
    
    public function hri_analytics_run_importer_cron_all($wp) {
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $this->hri_analytics_run_importer_ga_all();
        switch_to_blog($orginal_blog);
        
    }
    
    public function hri_analytics_run_importer_ga_all($output = false) {

        $tmp = '';
        $startDate = date('Y-m-d', strtotime("1.1.2012"));
        $endDate = date('Y-m-d');

        
        global $wpdb;
                
        $service = hri_analytics::getGoogleAnalyticsService();

        $optParams = array('filters'=>'ga:eventCategory==Ladattavat tiedostot ja linkit', 'dimensions' => 'ga:eventCategory,ga:eventAction,ga:eventLabel', 'sort' => '-ga:totalEvents');
        
        $result = $service->data_ga->get('ga:' . GOOGLE_ANALYTICS_TABLE_ID, $startDate, $endDate, 'ga:totalEvents,ga:uniqueEvents', $optParams);
        if (!isset($result['rows'])) {
            if ($output) {
                return 'Error';
            } else {
                return false;
            }
        }
        
        $updated_counter = 0;
        
        
    	foreach ($result['rows'] as $row) {
    	    
    	    $post_id = 0;
            $post_id_arr = $wpdb->get_results("
                SELECT post.id from wp_posts post 
                JOIN wp_postmeta meta on post.ID = meta.post_id 
                WHERE meta.meta_key LIKE 'resources\__\_url'
                AND meta.meta_value = '$row[2]'
                AND post.post_type = 'data' 
                AND post.post_status = 'publish'
                LIMIT 1;
            ");

        	if (count($post_id_arr) > 0) {
        	    $post_id = $post_id_arr[0]->id;
        	}
    	    
            if ($post_id != 0) {
                $metas = $wpdb->get_results("
                    SELECT * FROM wp_postmeta
                    WHERE meta_key LIKE 'resources\__\_url'
                    AND meta_value = '$row[2]';
                ");
                
                foreach ($metas as $meta) {
                    $meta_num = str_replace('resources_', '', str_replace('_url', '', $meta->meta_key));
                    
                    $meta_format = $wpdb->get_results("
                        SELECT * FROM wp_postmeta
                        WHERE
                            meta_key = 'resources_".$meta_num."_format'
                            AND meta_value = '" . $row[1] . "'
                            AND post_id = " . $meta->post_id . " ;
                    ");
                    if (count($meta_format > 0)) {
                        $field_str = 'resources_' . $meta_num . '_gacount';
                        update_post_meta($meta->post_id, $field_str,$row[3]);
                        $updated_counter++;
                    }
                }
            }
    	}
    	
    	$tmp = 'Updated ' . $updated_counter . ' items';
        
        
        
        if ($output) {
            return $tmp;
        }

    }
    
    public function hri_analytics_run_importer_cron_page($wp) {
        $orginal_blog = get_current_blog_id();
        switch_to_blog(1);
        $this->hri_analytics_run_importer_ga_page();
        switch_to_blog($orginal_blog);
        
    }
    
    public function hri_analytics_run_importer_ga_page($date = null, $output = false) {

        $tmp = '';
        $startDate = $date;
        $endDate = $date;
        
        if ($date == null) {
            $date = date('Y-m-d', time() - (60 * 60 * 24 * 2));
            $startDate = $date;
            $endDate = $date;
        }
        
        $dateSQL = date('Y-m-d 00:00:00', strtotime($startDate));

        
        global $wpdb;
                
        $service = hri_analytics::getGoogleAnalyticsService();

        $optParams = array('filters'=>'ga:pagePath=~.*/(data|dataset)/.*', 'dimensions' => 'ga:pagePath', 'sort' => '-ga:pageviews');
        
        $result = $service->data_ga->get('ga:' . GOOGLE_ANALYTICS_TABLE_ID, $startDate, $endDate, 'ga:visits,ga:visitors, ga:pageviews', $optParams);
        // throw new Exception(print_r($result, true)); // DEBUG
        if (!isset($result['rows'])) {
            if ($output) {
                return 'Error';
            } else {
                return false;
            }
        }
        

		$inserted = 0;
		$updated = 0;

    	$table_name = "wp_hri_analytics_pageviews_by_day";
    	
    	foreach ($result['rows'] as $row) {
    	    
            $page_path = $row[0];
            $page_visits = $row[1];
            $page_visitors = $row[2];
            $page_pageviews = $row[3];
            
            $parts = explode('/', $page_path);
            
            if (count($parts) < 3) {
                continue;
            }
            
            $page_name = '';
            $page_lang = 'fi';
            
            $this->solveNameAndLangFromParts($parts, $page_name, $page_lang);

            if ($page_name == '') {
                echo "empty page name<br />";
                continue;
            }
            
    	    
    	    $post_id = 0;
            $post_arr = $wpdb->get_results("
                SELECT post.id from wp_posts post 
                JOIN wp_postmeta meta on post.ID = meta.post_id 
                WHERE meta.meta_key = 'ckan_url'
                AND post.post_name = '{$page_name}' OR meta.meta_value LIKE '%{$page_name}'
                AND post.post_type = 'data' 
                AND post.post_status = 'publish';
            ");
            
            if (   count($post_arr) == 0
                || !isset($post_arr[0]->id)) {
                continue;
            }
            
            $post_id = $post_arr[0]->id;
            
            $tmp_data = $wpdb->get_row('
				SELECT id FROM ' . $table_name . '
				WHERE event_date = "' . $dateSQL . '"
				AND data_post_id =  ' . $post_id . '
				AND page_lang =  "' . $page_lang . '"
			;');
			


            if (isset($tmp_data)
                && isset($tmp_data->id)
                && $tmp_data->id != ''
            ) {
                $tmp_data_id = $tmp_data->id;
                $wpdb->update(
                    $table_name, 
                    array(
                        'data_post_id' => $post_id,
                        'page_lang' => $page_lang,
                        'page_path' => $page_path,
                        'page_visits' => $page_visits,
                        'page_visitors' => $page_visitors,
                        'page_pageviews' => $page_pageviews,
                    ),
                    array('id' => $tmp_data_id)
                );
                $updated++;
            } else {
                $wpdb->insert(
                    $table_name, 
                    array(
                        'data_post_id' => $post_id,
                        'event_date' => $dateSQL,
                        'page_lang' => $page_lang,
                        'page_path' => $page_path,
                        'page_visits' => $page_visits,
                        'page_visitors' => $page_visitors,
                        'page_pageviews' => $page_pageviews,
                    )
                );
                $inserted++;
            }
    	}
    	
    	$tmp .= "<br />Created " . $inserted . '';
        $tmp .= "<br />Updated " . $updated . '';
        
        
        
        if ($output) {
            return $tmp;
        }

    }
    
    public function getGoogleAnalyticsService() {
        require_once(dirname(__FILE__) . '/google-api-php-client-read-only/src/Google_Client.php');
        require_once(dirname(__FILE__) . '/google-api-php-client-read-only/src/contrib/Google_AnalyticsService.php');

        // Set your client id, service account name, and the path to your private key.
        // For more information about obtaining these keys, visit:
        // https://developers.google.com/console/help/#service_accounts

        $client = new Google_Client();
        $client->setApplicationName("HRI Google Analytics");

        // Set your cached access token. Remember to replace $_SESSION with a
        // real database or memcached.
        if (isset($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
        }

        // Load the key in PKCS 12 format (you need to download this from the
        // Google API Console when the service account was created.
        // Make sure you keep your key.p12 file in a secure location, and isn't
        // readable by others.
        $key = file_get_contents(GOOGLE_ANALYTICS_KEY_FILE);
        $client->setAssertionCredentials(new Google_AssertionCredentials(
            GOOGLE_ANALYTICS_SERVICE_ACCOUNT_NAME,
            array('https://www.googleapis.com/auth/analytics.readonly'),
            $key)
        );

        $client->setClientId(GOOGLE_ANALYTICS_CLIENT_ID);
        $client->setAccessType('offline_access');
        
        $service = new Google_AnalyticsService($client);
        return $service;
    }
    
}

$hri_analytics_admin = new hri_analytics_admin;
$hri_analytics = new hri_analytics;

if( isset( $_GET['CRON_HRI_ANALYTICS'] ) && $_GET['CRON_HRI_ANALYTICS'] == '30d' ) {
	$hri_analytics->hri_analytics_run_importer_cron_30d(null);
	exit;
}

if( isset( $_GET['CRON_HRI_ANALYTICS'] ) && $_GET['CRON_HRI_ANALYTICS'] == '1d' ) {
	$hri_analytics->hri_analytics_run_importer_cron_1d(null);
	exit;
}

if( isset( $_GET['CRON_HRI_ANALYTICS'] ) && $_GET['CRON_HRI_ANALYTICS'] == 'all' ) {
	$hri_analytics->hri_analytics_run_importer_cron_all(null);
	exit;
}

if( isset( $_GET['CRON_HRI_ANALYTICS'] ) && $_GET['CRON_HRI_ANALYTICS'] == 'page' ) {
	$hri_analytics->hri_analytics_run_importer_cron_page(null);
	exit;
}

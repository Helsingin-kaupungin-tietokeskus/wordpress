<?php

get_template_part( 'functions', 'secondary' );

if( is_admin() ) {
	get_template_part( 'functions', 'admin' );
}

global $blog_id, $locale;

defined( 'ORIGINAL_BLOG_ID' ) or define( 'ORIGINAL_BLOG_ID', $blog_id );
defined( 'HRI_LANG' ) or define( 'HRI_LANG', substr( $locale, 0, 2 ) );

switch_to_blog(1);
define( 'ROOT_URL', home_url() );
restore_current_blog();

$pattern_url = str_replace( array('/', '.'), array('\/', '\.'), ROOT_URL );
$pattern_url = "/$pattern_url\\/(fi|en|se)\\//";

$hri_kses_args = array(
	'a' => array('href' => array(),'title' => array()),
	'br' => array(),
	'blockquote' => array(),
	'em' => array(),
	'li' => array(),
	'ol' => array(),
	'p' => array(),
	'span' => array('style'=>array('text-decoration')),
	'strong' => array(),
	'sub' => array(),
	'sup' => array(),
	'ul' => array()
);

switch( $blog_id ) {
	case 3:
		define( 'DATA_SEARCH_URL', ROOT_URL .		'/en/dataset?q=&sort=metadata_created+desc' );
		define( 'APP_SEARCH_URL', ROOT_URL .		'/en/applications/' );
		define( 'NEW_DISCUSSION_URL', ROOT_URL .	'/en/start-a-new-discussion/' );
		define( 'NEW_APP_IDEA_URL', ROOT_URL .		'/en/new-app-idea/' );
		define( 'NEW_APP_URL', ROOT_URL .			'/en/submit-new-application/' );
		define( 'NEW_DATA_REQUEST_URL', ROOT_URL .	'/en/' );
		break;
	default:
		define( 'DATA_SEARCH_URL', ROOT_URL .		'/fi/dataset?q=&sort=metadata_created+desc' );
		define( 'APP_SEARCH_URL', ROOT_URL .		'/fi/sovellukset/' );
		define( 'NEW_DISCUSSION_URL', ROOT_URL .	'/fi/aloita-uusi-keskustelu/' );
		define( 'NEW_APP_IDEA_URL', ROOT_URL .		'/fi/uusi-sovellusidea/' );
		define( 'NEW_APP_URL', ROOT_URL .			'/fi/ilmoita-uusi-sovellus/' );
		define( 'NEW_DATA_REQUEST_URL', ROOT_URL .	'/fi/uusi-datatoive/' );
}

add_action( 'after_setup_theme', 'hri_setup' );

function hri_setup() {

	add_theme_support( 'post-thumbnails' );
	add_image_size( 'tiny-square',	50, 50, true );
	add_image_size( 'small-app',	97, 60, true );
	add_image_size( 'med-square',	160, 160, true );
	add_image_size( 'square',		235, 235, true );
	add_image_size( 'app',			560, 300, true );		//carousel
	add_image_size( 'app2',			260, 146, true );		//application in gallery
	add_image_size( 'app3',			310, 400 );				//single-application

	add_theme_support( 'automatic-feed-links' );
	add_editor_style();

	load_theme_textdomain( 'hri', TEMPLATEPATH . '/languages' );

	register_nav_menus( array(
		'primary' => __( 'Päävalikko', 'hri' ),
	) );

	register_nav_menus( array(
		'footer' => __( 'Alavalikko', 'hri' ),
	) );

	register_sidebar(array(
		'name' => __( 'Etusivu', 'hri' ),
		'id' => 'front-page',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));
	register_sidebar(array(
		'name' => __( 'Etusivu2', 'hri' ),
		'id' => 'front-page2',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki', 'hri'),
		'id' => 'sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki Sivuille', 'hri'),
		'id' => 'sidebar-page',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki Datoille', 'hri'),
		'id' => 'sidebar-data',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki Artikkelilistauksille', 'hri'),
		'id' => 'sidebar-archive',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	add_filter( 'excerpt_length', function(){
		return 20;
	});

}

/* -------------------------------------------------------------------------------------------------------------------------------- Rewrite */

// http://codex.wordpress.org/Function_Reference/WP_Rewrite

add_filter( 'rewrite_rules_array', 'hri_add_rewrite_rules' );
add_filter( 'query_vars', 'hri_add_query_vars' );

function hri_add_rewrite_rules($rules) {

	$newrules = array();

	// Content types:
	$newrules['(data)/$'] = 'index.php?posttype=data';
	$newrules['(data)/(.+)$'] = 'index.php?posttype=data&postname=$matches[2]';

	$newrules['(applications)/(.+)$'] = 'index.php?posttype=application&postname=$matches[2]';
	$newrules['(sovellukset)/(.+)$'] = 'index.php?posttype=application&postname=$matches[2]';

	$newrules['(discussions)$'] = 'index.php?pagename=discussions';
	$newrules['(keskustelut)$'] = 'index.php?pagename=keskustelut';
	$newrules['(discussions)/(.+)$'] = 'index.php?posttype=discussion&postname=$matches[2]';
	$newrules['(keskustelut)/(.+)$'] = 'index.php?posttype=discussion&postname=$matches[2]';

	return $newrules + $rules;

}

function hri_add_query_vars($vars) {

	array_push($vars, 'posttype', 'postname', 'appcat');
	return $vars;

}

/* -------------------------------------------------------------------------------------------------------------------------------- Comment meta language things */

// comment language meta saving
add_action('comment_post','comment_save_data');

function comment_save_data($comment_id) {

	$langs = array('en','fi','se');
	if ( !in_array( $_POST['lang'], $langs) ) die('Error: Comment language meta fail');

	add_comment_meta($comment_id, 'hri_comment_lang', $_POST['lang'], true);

	if ( isset( $_POST['r1'] ) && $_POST['r1'] > 0 ) {

		for($i=1; $i<=3; ++$i){
			$r = (int) $_POST['r'.$i];
			if ($r < 1) $r = 1;
			if ($r > 5) $r = 5;
			add_comment_meta($comment_id, '_hri_rating'.$i, $r, true);
		}
	}
}

function add_hidden_lang() {

	global $blog_id, $post;

 	echo '<input type="hidden" name="lang" value="' . HRI_LANG . '" />
	<input type="hidden" id="hri-blog" value="' . $blog_id . '" />';

	return true;

}

add_filter('comment_form_top','add_hidden_lang');

if( isset($_POST) ) {

	if(
		isset( $_POST['r1'] ) && $_POST['r1'] > 0 &&
		isset( $_POST['r2'] ) && $_POST['r2'] > 0 &&
		isset( $_POST['r3'] ) && $_POST['r3'] > 0

	) add_action('pre_comment_on_post', function() {

		$some_random_value = uniqid();

		//allow empty ratings without comment text
		if( $_POST['comment'] == '' ) $_POST['comment'] = "<!-- rating {$some_random_value} -->";

	});

}

add_action('wp_ajax_hri_app', 'hri_ajax_app');
add_action('wp_ajax_nopriv_hri_app', 'hri_ajax_app');

function hri_ajax_app() {

	if( isset( $_POST['not'] ) ) $id = (int) $_POST['not'];

	switch_to_blog(1);

	$args = array(
		'post_type' => 'application',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'orderby' => 'rand'
	);

	if( isset($id) ) $args[ 'post__not_in' ] = array($id);

	$app = new WP_Query( $args );

	if( $app->have_posts() ) {

		global $post;

		$app->the_post();

		?><div style="display:none" id="post-<?php the_ID(); ?>" <?php post_class( 'post-highlight clear' ); ?>><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php

			hri_thumbnail();

			?></a>
				<div class="highlight-excerpt"><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php the_title(); ?></a>
					<p><?php echo get_post_meta( $post->ID, 'short_text', true ); ?></p>
				</div>
				<div class="clear"></div>
			</div><?php

	}

	exit();

}

/* -------------------------------------------------------------------------------------------------------------------------------- subscribe to comments */

function hri_subscribe() {

	global $post;

?>
<div id="hri_subscribe_toggle">
	<a id="comment_subscribe_link"><?php _e( 'Sähköposti-ilmoitus kommenteista: tilaa tai peru tilaus', 'hri' ); ?></a>
	<div id="subscribe_content" style="display:none">
		<form action="<?php

			echo ROOT_URL, '/', HRI_LANG;
			if( ORIGINAL_BLOG_ID == 2 ) echo '/tilaa-kommentit/';
			if( ORIGINAL_BLOG_ID == 3 ) echo '/subscribe-to-comments/';

			?>" method="post">
			<a id="subscribe_toggle_close" class="bold right"><?php _e( 'Sulje', 'hri' ); ?></a>
		<div class="row">
			<label for="hri_subscribe_email"><?php _e('Sähköposti', 'hri'); ?></label>
			<span class="required">*</span>
			<input class="text" id="hri_subscribe_email" type="email" required="required" size="30" value="" name="hri_subscribe_email" />
		</div>
		<div class="row">
			<input type="hidden" value="<?php echo $post->ID; ?>" name="hri_subscribe_post" />
			<input type="hidden" value="<?php echo get_current_blog_id(); ?>" name="hri_subscribe_blog" />
			<input checked="checked" class="hri_subscribe_option radio" type="radio" name="hri_subscribe_option" id="hri_subscribe_option1" value="1"><label class="hri_subscribe_label" for="hri_subscribe_option1"><?php _e('Tilaa ilmoitukset uusista viesteistä tähän keskusteluun', 'hri'); ?></label>
			<input class="hri_subscribe_option radio" type="radio" name="hri_subscribe_option" id="hri_subscribe_option2" value="2"><label class="hri_subscribe_label" for="hri_subscribe_option2"><?php _e('Peru ilmoitukset tämän keskustelun viesteistä', 'hri'); ?></label>
			<input class="hri_subscribe_option radio hri_subscribe_option_top_margin" type="radio" name="hri_subscribe_option" id="hri_subscribe_option3" value="3"><label class="hri_subscribe_label" class="hri_subscribe_option_top_margin" for="hri_subscribe_option3"><?php _e('Peru ilmoitukset kaikista HRI-keskusteluista', 'hri'); ?></label>
			<br class="clear" />
			<input class="plus-submit" type="submit" name="hri_subscribe_submit" id="hri_subscribe_submit" value="<?php _e('Lähetä', 'hri'); ?>" />
		</div>
		</form>
	</div>
</div>
<script type="text/javascript">
// <!--
jQuery(function($) {

	$('#hri_subscribe_toggle').insertBefore( '#respond' );

	$('#comment_subscribe_link').prependTo($('#reply-title')).click(function(){
		$(this).fadeOut();
		$('#subscribe_content').slideDown();
	});

	$('#subscribe_toggle_close').click(function(){
		$('#subscribe_content').slideUp();
		$('#comment_subscribe_link').fadeIn();
	});
});
// -->
</script>
<?php

}

add_action('comment_form_after', 'hri_subscribe');

add_filter('excerpt_more', function( $more ){
	return '&hellip;';
});

// Image upload for applications

function hri_image_upload() {

	switch_to_blog(1);

	$imageinfo = getimagesize( $_FILES['Filedata']['tmp_name'] );

	// allow only images recognized by getimagesize()
	if( $imageinfo !== false ) {

		$newID = media_handle_upload( 'Filedata', 0 );
		$thumbnail = wp_get_attachment_image_src( $newID, 'thumbnail' );

		add_post_meta( $newID, 'front_upload', 1 );

		echo $newID, '-', $thumbnail[0];

	}

	exit;

}

add_action('wp_ajax_hri_front_upload', 'hri_image_upload');
add_action('wp_ajax_nopriv_hri_front_upload', 'hri_image_upload');

function hriLinkByLang($post, $lang = 'fi') {

	switch($lang) {
		case 'fi':
			return hri_link(get_permalink( $post->ID ), 'fi',  ($post->post_type == 'data') ? 'dataset' : $post->post_type);
		case 'se':
			return hri_link(get_permalink( $post->ID ), 'se',  ($post->post_type == 'data') ? 'dataset' : $post->post_type);
		case 'eng':
			return hri_link(get_permalink( $post->ID ), 'eng', ($post->post_type == 'data') ? 'dataset' : $post->post_type);
	}

	return hri_link( get_permalink( $post->ID ), 'fi', ($post->post_type == 'data') ? 'dataset' : $post->post_type);
}

// Latest conversation wrapper

add_action('wp_ajax_nopriv_hri_latest_conversation', 'hri_latest_conversation');
add_action('wp_ajax_hri_latest_conversation', 'hri_latest_conversation');

function hri_latest_conversation() {
	global $wpdb;

	switch_to_blog(1);

	$latest_topic = $wpdb->get_results( "(SELECT SQL_CALC_FOUND_ROWS DISTINCT p.*, comment_date AS sort_date, '1' AS source FROM $wpdb->posts p LEFT JOIN ( SELECT comment_post_ID, MAX( comment_date ) AS comment_date FROM $wpdb->comments WHERE comment_approved = 1 GROUP BY comment_post_ID ) AS c ON c.comment_post_ID = p.ID WHERE p.post_status = 'publish' AND ( (p.post_type = 'discussion' OR p.post_type = 'data') AND p.comment_count > 0 ) ORDER BY c.comment_date DESC, p.post_title ASC) UNION (SELECT DISTINCT p.*, p.post_date AS sort_date, '2' AS source FROM $wpdb->posts p WHERE p.post_type = 'discussion' AND p.post_status = 'publish' AND p.comment_count = 0) ORDER BY sort_date DESC LIMIT 0, 1" );
	$post = $latest_topic[0];

	$json = array(
		"title" => $post->post_title,
	);

	if($post->post_type == 'data') { $post->post_type = 'dataset'; }

	$json["link"] = hriLinkByLang($post, $_POST['HRI_LANG']);

	echo json_encode($json);

	restore_current_blog();
	exit();
}

// Count datasets 

add_action('wp_ajax_nopriv_hri_count_datasets', 'hri_count_datasets');
add_action('wp_ajax_hri_count_datasets', 'hri_count_datasets');

function hri_count_datasets() {
	
	global $wpdb;

	switch_to_blog(1);
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'data' AND post_status = 'publish' AND $wpdb->posts.post_title <> ''" );

	$json = array(
		"count" => $count,
		"localized_string_1" => __('%s DATA-AINEISTOA', 'hri'),
		"localized_string_2" => __('YHTEENSÄ %s', 'hri')
	);

	echo json_encode($json);

	restore_current_blog();
	exit();
}

add_action('wp_ajax_nopriv_hri_get_gadata', 'hri_get_gadata');
add_action('wp_ajax_hri_get_gadata', 'hri_get_gadata');

function produceGAData($post_id) {

	global $wpdb;

	$data = array();

	if( get_post_meta( $post_id, "resources_0_id", true ) ) {
		
		$i = 0;

		while ( get_post_meta( $post_id, "resources_{$i}_id", true ) ) {

			$data_link = get_post_meta( $post_id, "resources_{$i}_url", true );
			$data_format = get_post_meta( $post_id, "resources_{$i}_format", true );
			$data_description = get_post_meta( $post_id, "resources_{$i}_description", true );
			$data_size = get_post_meta( $post_id, "resources_{$i}_size", true );

			$table_name = "wp_hri_analytics_pageviews_by_day";
			$table_name2 = "wp_hri_analytics_downloads_by_day";
			
			$downloads_arr = array();
			$has_data = false;
			$max_count = 0;
			for ($j = 30; $j > 0; $j--) {

				$dateSQL = date('Y-m-d 00:00:00', strtotime("-{$j} days"));
				$datePretty = date('d.m.Y', strtotime("-{$j} days"));
				
				$tmp_count = 0;
				$tmp_count2 = 0;
				
				$results = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE event_date = "' . $dateSQL . '" AND data_post_id =  "' . $post_id . '"');
				$results2 = $wpdb->get_row('SELECT SUM(event_count) as event_count_total FROM ' . $table_name2 . ' WHERE event_date = "' . $dateSQL . '" AND data_post_id =  "' . $post_id . '"');

				$tmp = array('date' => $datePretty);
				if (count($results) > 0) {
					foreach ($results as $result) {
						$tmp_count = $tmp_count + $result->page_pageviews;
					}

					$tmp['pageviews'] = $tmp_count;

					if ($tmp_count > 0) {
						$has_data = true;
					}
					if ($tmp_count > $max_count) {
						$max_count = $tmp_count;
					}
					
					$total_count += $tmp_count;
				} else {
					$tmp['pageviews'] = 0;
				}

				if (   count($results2) > 0
					&& $results2->event_count_total != '') {

					$tmp_count2 = $results2->event_count_total;
					$tmp['downloads'] = $tmp_count2;

					if ($tmp_count2 > 0) {
						$has_data = true;
					}
					if ($tmp_count2 > $max_count) {
						$max_count = $tmp_count2;
					}
					
					$total_count2 += $tmp_count2;
				} else {
					$tmp['downloads'] = 0;
				}
				$downloads_arr[] = $tmp;
				
			}

			// Gather the data in [date, page views, resource loads] form.
			$j = 0;
			foreach ($downloads_arr as $downloads_by_day) {

				// Note that downloads from previous loop run are added, rest of the data will stay the same.
				$data[0][$j] = array(0 => $downloads_by_day['date'], 1 => $downloads_by_day['pageviews'], 2 => (int)$data[0][$j][2] + (int)$downloads_by_day['downloads']);

				$j++;
			}

			$i++;
		}
	}

	return json_encode($data);
}

/** 
 * Get Google Analytics data for given dataset 
 * 
 * @param $_POST['slug']
 */
function hri_get_gadata() {
	
	global $wpdb;
	// Utilize this function from the XML-RPC side (hri-ckan/function_xml_rpc.php) to match given slug to WordPress post ID.
	$post_id = ckan_find_wordpress_post_id($args = array(false, false, $_POST['slug']));
	$post_id = $post_id[0]->ID;

	// http://hasin.me/2013/10/09/caching-ajax-requests-in-wordpress/
	/** Supply from cache -> **/
	if($cacheddata = get_option("CACHE__hri_get_gadata_{$post_id}", 0)) { die($cacheddata); }
	/** <- Supply from cache **/

	$jsondata = produceGAData($post_id);

	/** Save the cache **/
    update_option("CACHE__hri_get_gadata_{$post_id}", $jsondata);
    /** Output cache contents **/
	die($cacheddata);
}

/**
 * Deleting the CKAN's auth_tkt session cookie is quite hard, but the code below succeeds.
 * Note, that this script is also run during login or any other authentication process,
 * not just logout, but we can live with that. If the user is logged on to WordPress, CKAN
 * will trigger login.
 */
add_action('wp_authenticate', 'clearTheCKANCookie');

// http://www.blackbam.at/blackbams-blog/2011/06/09/wordpress-custom-external-authentication-loginlogout-php-script/
// http://stackoverflow.com/questions/686155/remove-a-cookie
function clearTheCKANCookie() {

	unset($_COOKIE['auth_tkt']);
	setcookie("auth_tkt", "", time()-3600, '/');
	setcookie("auth_tkt", "", time()-3600, '/', '.' . DOMAIN_CURRENT_SITE);
}

add_action('wp_ajax_nopriv_hri_dataset', 'hri_dataset');
add_action('wp_ajax_hri_dataset', 'hri_dataset');

/** Produces all required data (GA, related apps, post_id, comments) a CKAN's dataset page (identified by the slug) requires. */
function hri_dataset() {

	global $wpdb;
	// Utilize this function from the XML-RPC side (hri-ckan/function_xml_rpc.php) to match given slug to WordPress post ID.
	$post_id = ckan_find_wordpress_post_id($args = array(false, false, $_POST['slug']));
	$post_id = $post_id[0]->ID;

	echo produceGAData($post_id);
	echo "~";
	echo produceRelatedApps($post_id, $_POST['lang']);
	echo "~";
	echo $post_id;
	echo "~";
	echo json_encode(hri_getComments(array(0 => 2, 1 => null, 2 => null, 3 => array('post_id' => $post_id))));
	die();
}

add_action('wp_ajax_nopriv_hri_get_related_apps', 'hri_get_related_apps');
add_action('wp_ajax_hri_get_related_apps', 'hri_get_related_apps');

function produceRelatedApps($post_id, $lang = 'fi') {

	global $wpdb;
	switch_to_blog(1);

	$related_apps = new WP_Query(array(
		'post_type' => 'application',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_link_to_data',
				'value' => $post_id
			)
		)
	));

	restore_current_blog();

	ob_start();
	if($related_apps->have_posts()) {

		?><h2 class="module-heading icon"><?php if($lang == 'en') { echo 'Apps built with this data (in Finnish)'; } else { _e( 'Sovellukset tästä datasta', 'hri' ); } ?></h2><div class="sidebar-app-list"><?php

		while($related_apps->have_posts()) {

			$related_apps->the_post();

			?><div class="post-highlight clearfix">
				<a class="block left clearfix" href="<?php echo hri_link(get_permalink(), 'fi', 'application'); ?>"><?php hri_thumbnail('tiny-square'); ?></a>
				<div class="highlight-excerpt">
					<a href="<?php echo hri_link(get_permalink(), 'fi', 'application'); ?>"><?php the_title(); ?></a>
					<p><?php the_hri_field('short_text'); ?></p>
				</div>
			</div><?php

		}

		?></div><?php
	}
	$htmldata = ob_get_contents(); 
	ob_end_clean();

	return $htmldata;
}

function hri_get_related_apps() {

	// Utilize this function from the XML-RPC side (hri-ckan/function_xml_rpc.php) to match given slug to WordPress post ID.
	$post_id = ckan_find_wordpress_post_id($args = array(false, false, $_POST['slug']));
	$post_id = $post_id[0]->ID;

	// http://hasin.me/2013/10/09/caching-ajax-requests-in-wordpress/
	/** Supply from cache -> **/
	// if($cacheddata = get_option("CACHE__hri_get_related_apps_{$post_id}", 0)) { die($cacheddata); }
	/** <- Supply from cache **/

	$htmldata = produceRelatedApps($post_id);
	
	/** Save the cache **/
    // update_option("CACHE__hri_get_related_apps_{$post_id}", $htmldata);
    /** Output cache contents **/
	die($htmldata);
}
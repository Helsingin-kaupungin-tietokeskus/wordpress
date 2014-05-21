<?php

/*
Plugin Name: Platinum SEO Pack
Plugin URI: http://techblissonline.com/platinum-seo-pack/
Description: Complete SEO solution for your Wordpress blog.
Version: 1.3.8
Author: Rajesh - Techblissonline Dot Com
Author URI: http://techblissonline.com/
*/

/*
Copyright (C) 2008 Rajesh (http://techblissonline.com) (platinumseopack AT techblissonline DOT com)
Some code copyright 2007-2008 Uberdose, joost de valk 

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Platinum_SEO_Pack {

 	var $version = "1.3.8";

 	/** Max numbers of chars in auto-generated description */
 	var $max_description_length = 160;

 	/** Minimum number of chars an excerpt should be so that it can be used
 	 * as description. Touch only if you know what you're doing
 	 */
 	var $min_description_length = 1;

 	//var $ob_start_detected = false;

 	var $title_start = -1;

 	var $title_end = -1;

 	/** The title before rewriting */
 	var $orig_title = '';

 	/** Temp filename for the latest version. */
 	var $upgrade_filename = 'psptemp.zip';

 	/** Where to extract the downloaded newest version. */
 	var $upgrade_folder;

 	/** Any error in upgrading. */
 	var $upgrade_error;

 	/** Which zip to download in order to upgrade .*/
 	var $upgrade_url = 'http://downloads.wordpress.org/plugin/platinum-seo-pack.zip';

 	/** Filename of log file. */
 	var $log_file;

 	/** Flag whether there should be logging. */
 	var $do_log;

 	var $wp_version;

	function Platinum_SEO_Pack() {
		global $wp_version;
		$this->wp_version = $wp_version;
		
		// Pre-2.6 compatibility
		if ( ! defined( 'WP_CONTENT_URL' ) )
			define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
		if ( ! defined( 'WP_CONTENT_DIR' ) )
			define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
		if ( ! defined( 'WP_PLUGIN_URL' ) )
			define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
		if ( ! defined( 'WP_PLUGIN_DIR' ) )
			define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );		

		$this->log_file = dirname(__FILE__) . '/platinum_seo_pack.log';
		if (get_option('aiosp_do_log')) {
			$this->do_log = true;
		} else {
			$this->do_log = false;
		}

		$this->upgrade_filename = dirname(__FILE__) . '/' . $this->upgrade_filename;
		$this->upgrade_folder = dirname(__FILE__);
	}

	// Check if a given slug belongs to a post in the database
	function does_post_exist( $slug ) {

	 	global $wpdb;

	 	if( $ID = $wpdb->get_var( 'SELECT ID FROM '.$wpdb->posts.' WHERE post_name = "'.$slug.'" AND post_status = "publish" ' ) ) {
			return $ID;
		}
		else {
			return false;
		}

	}

	//301 redirect to new permalink
	function redirect_to_new_location( $post_new_location ) {

	    //301 redirect to new location
		header( "HTTP/1.1 301 Moved Permanently" );
		header( "Location: $post_new_location" );
	}


	  // When the post is not found, and is_404() == true, verify if the requested slug belongs to a post in the database.

	function has_permalink_changed() {

		if( is_404() ) {

		 	$slug = basename( $_SERVER['REQUEST_URI'] );			 
			
			$exts=array("/",".php",".html",".htm");
			
			// works with PHP version <= 5.x.x 
			foreach( $exts as $ext ) 
			{ 
				$slug = str_replace( $ext, "", $slug ); 
				$slug = trim($slug);
			} 

		 	if( $ID = $this->does_post_exist( $slug )) {

		 		$this->redirect_to_new_location( get_permalink( $ID ));

			}
		}
	}

	function apply_seo_title() {
		global $wp_query;
		$post = $wp_query->get_queried_object();

		if (is_feed()) {
			return;
		}

		if (is_single() || is_page() || $this->is_static_front_page()) {
		    $psp_disable = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_disable', true)));
			$psp_notitlerewrite = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_notitlerewrite', true)));
		    if ($psp_disable) {
		    	return;
		    }
			if ($psp_notitlerewrite) {
		    	return;
		    }
		}

		if (get_option('aiosp_rewrite_titles')) {
			ob_start(array($this, 'callback_for_title_rewrite'));
		}
	}

	function callback_for_title_rewrite($content) {

		$content = $this->rewrite_title($content);
		if (get_option('psp_nofollow_ext_links')){
			$content = $this->nofollow_home_category($content);
		}

		return $content;
	}

	function init() {
		if (function_exists('load_plugin_textdomain')) {
		
			load_plugin_textdomain('platinum_seo_pack', false, WP_PLUGIN_DIR . '/platinum-seo-pack');
			
		}
	}

	function is_static_front_page() {
		global $wp_query;
		$post = $wp_query->get_queried_object();
		return get_option('show_on_front') == 'page' && is_page() && $post->ID == get_option('page_on_front');
	}

	function is_static_posts_page() {
		global $wp_query;
		$post = $wp_query->get_queried_object();
		return get_option('show_on_front') == 'page' && is_home() && $post->ID == get_option('page_for_posts');
	}

	function add_nofollow($matches) {
		$origin = get_bloginfo('wpurl');
		if ((strpos($matches[2],$origin)) === false && ( strpos($matches[1],'rel="nofollow"') === false ) && ( strpos($matches[3],'rel="nofollow"') === false ) && ( strpos($matches[1],'rel="external nofollow"') === false ) && ( strpos($matches[3],'rel="external nofollow"') === false )) {
			$nofollow = ' rel="nofollow" ';
		} else {
			$nofollow = '';
		}
		return '<a href="' . $matches[2] . '"' . $nofollow . $matches[1] . $matches[3] . '>' . $matches[4] . '</a>';
	}

	function nofollow_home_category($output) {
		// Loop through the content of each post and add a nofollow to links on home page or a category page.
		if (is_home() || is_category() ||is_search() || (function_exists('is_tag') && is_tag()) || is_author()) {
			//$anchorPattern = '/<a (.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';
			$anchorPattern = '/<a ([^<>]*?)href="(.*?)"([^<>]*?)>(.*?)<\/a>/i';
			$output = preg_replace_callback($anchorPattern,array(get_class($this),'add_nofollow'),$output);
		}
		return $output;
	}

	function noindex_feed() {

		echo '<xhtml:meta xmlns:xhtml="http://www.w3.org/1999/xhtml" name="robots" content="noindex" />'."\n";
	}

	function nofollow_link($output) {

		return str_replace('<a ','<a rel="nofollow" ',$output);

	}


	function nofollow_category_listing($output) {

		if ( (get_option('psp_nofollow_cat_posts') && (is_single() || is_search()) ) || (get_option('psp_nofollow_cat_pages') && (is_home() || is_page() || is_category() || is_tag() || $this->is_static_front_page()) ) ) {

			$output = $this->nofollow_link($output);
			return $output;

		} else {

			return $output;
		}
	}
	
	function nofollow_archive_listing($output) {

		if ( (get_option('psp_nofollow_arc_posts') && (is_single() || is_search()) ) || (get_option('psp_nofollow_arc_pages') && (is_home() || is_page() || is_category() || is_tag() || $this->is_static_front_page()) ) ) {   								
			$output = $this->nofollow_link($output);			
			return $output;

		} else {

			return $output;
		}
	}

	function nofollow_taglinks($output) {

		$output = str_replace('rel="tag"','rel="nofollow tag"',$output);
		return $output;
	}

	function echo_to_blog_header() {
		if (is_feed()) {
			return;
		}

		global $wp_query;
		$post = $wp_query->get_queried_object();
		$meta_string = null;
		$meta = null;
		$can_link = '';
		$canonical = get_option('psp_canonical');		

		//echo("wp_head() " . wp_title('', false) . " is_home() => " . is_home() . ", is_page() => " . is_page() . ", is_single() => " . is_single() . ", is_static_front_page() => " . $this->is_static_front_page() . ", is_static_posts_page() => " . $this->is_static_posts_page());

		if (is_single() || is_page()) {
		    $psp_disable = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_disable', true)));
				
		    if ($psp_disable) {
				return;
		    }
			
		    //$pspmeta = $_POST["psp_robotsmeta"];
			$pspmeta = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'robotsmeta', true)));

			if (isset($pspmeta)  &&  !empty($pspmeta)) {				
				if ( get_option('psp_comnts_pages_noindex') && get_option('page_comments') && (get_query_var('cpage') >= 1 || get_query_var('cpage') < get_comment_pages_count()) ) {
					$meta .= "noindex,follow";
				} else {
					$meta = $pspmeta;
				}

			} else {                
				if ( get_option('psp_comnts_pages_noindex') && get_option('page_comments') && (get_query_var('cpage') >= 1 || get_query_var('cpage') < get_comment_pages_count()) ) {
					$meta .= "noindex,follow";
				} else {
					$meta .= "index,follow";
				}

			}
			$psp_noarchive = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_noarchive', true)));
			if ($psp_noarchive) {
				if ($meta != "") {
					$meta .= ",";
				}

				$meta .= "noarchive";

			}

          		$psp_nosnippet = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_nosnippet', true)));
			if ($psp_nosnippet) {
				if ($meta != "") {
					$meta .= ",";
				}

				$meta .= "nosnippet";

			}

		} else if ( (is_author() && get_option('psp_author_archives_noindex')) || (is_category() && get_option('psp_category_noindex')) || (is_date() && get_option('psp_archive_noindex')) || (is_search() && get_option('psp_search_results_noindex')) || (function_exists('is_tag') && is_tag() && get_option('psp_tags_noindex')) ) {
			$meta .= "noindex,follow";

		} else if (is_home()) {
			if (get_option('psp_sub_pages_home_noindex') && get_query_var('paged') > 1) {
				$meta .= "noindex,follow";
			} else {
				$meta .= "index,follow";
			}
		}else if ((function_exists('is_tag') && is_tag() && !get_option('psp_tags_noindex'))|| (is_category() && !get_option('psp_category_noindex')) || (is_author() && !get_option('psp_author_archives_noindex')) || (is_date() && !get_option('psp_archive_noindex')) || (is_search() && !get_option('psp_search_results_noindex')) ) {
			if (get_option('psp_sub_pages_home_noindex') && get_query_var('paged') > 1) {
				$meta .= "noindex,follow";
			} else {
				$meta .= "index,follow";
			}
		}
		if (get_option('psp_noodp_metatag')) {

			if ($meta != "") {
				$meta .= ",";
			}

			$meta .= "noodp";
		}

		if (get_option('psp_noydir_metatag')) {

			if ($meta != "") {
				$meta .= ",";
			}

			$meta .= "noydir";

		}

		if ($meta != "" || isset($meta)) {

		if ($meta_string != "" || isset($meta_string)) {

			$$meta_string .= "\n";
		}

		$meta_string .= '<meta name="robots" content="'.$meta.'" />';

		}

		echo "\n<!-- platinum seo pack $this->version ";
		if ($this->ob_start_detected) {
			echo "ob_start_detected ";
		}
		//echo "[$this->title_start,$this->title_end] ";
		echo "-->\n";

		if ((is_home() && get_option('aiosp_home_keywords'))) { // || $this->is_static_front_page()) {
			$keywords = trim($this->internationalize(get_option('aiosp_home_keywords')));
		} else {
			$keywords = $this->get_all_keywords();
		}
		if (is_single() || is_page() || $this->is_static_front_page()) {
            //if ($this->is_static_front_page()) {
			//	$description = trim(stripcslashes($this->internationalize(get_option('aiosp_home_description'))));
            //} else {
            	$description = $this->get_post_description($post);
				if ($canonical) {					
						$post_link = get_permalink($post->ID);
						$can_link = $this->paged_link($post_link); 
						//$can_link = trailingslashit($can_link);
					
					if ($this->is_static_front_page()){						
						$can_link = trailingslashit($can_link);
					}
				}
           // }
		} else if (is_home()) {
		
			$description = trim(stripcslashes($this->internationalize(get_option('aiosp_home_description'))));
			
			if ($canonical) {
				if ((get_option('show_on_front') == 'page') && ($pageid = get_option('page_for_posts'))) 
				{
					$page_for_posts_link = get_permalink($pageid);
					$can_link = $this->paged_link($page_for_posts_link);
					$can_link = trailingslashit($can_link);
				} else {
					$home_link = get_option('home');
					$can_link = $this->paged_link($home_link);
					$can_link = trailingslashit($can_link);	
				}
			}
		} else if (is_category()) {
		
			$cat_description = category_description();
			$description = $this->get_string_between($cat_description, "[description]", "[/description]");			
			$keywords = $this->get_string_between($cat_description, "[keywords]", "[/keywords]");
			
			if ($description == "" || $description == null) {				
				$description = $this->trim_excerpt_without_filters($this->internationalize($cat_description));
			} else {				
				$description = $this->trim_excerpt_without_filters($this->internationalize($description));
			}
			
			if ($keywords != "" || $keywords != null) {
				$keywords = $this->internationalize($keywords);
			}
			if ($canonical) {
				$cat_link = get_category_link(get_query_var('cat'));
				$can_link = $this->paged_link($cat_link);
			}
		} else if (is_date() && ($canonical)) {
			if (get_query_var('m')) {
		        $m = preg_replace('/[^0-9]/', '', get_query_var('m'));
		        switch (strlen($m)) {
		            case 4: 
		                $can_link = get_year_link($m);
						$can_link = $this->paged_link($can_link);
		                break;
		            case 6: 
		                $can_link = get_month_link(substr($m, 0, 4), substr($m, 4, 2));
						$can_link = $this->paged_link($can_link);
		                break;
		            case 8: 
		                $can_link = get_day_link(substr($m, 0, 4), substr($m, 4, 2),
		                                     substr($m, 6, 2));
						$can_link = $this->paged_link($can_link);					 
		                break;
		            default:
		                $can_link = '';
		        }
		    }
			if ($wp_query->is_day) {
		        $can_link = get_day_link(get_query_var('year'),
		                             get_query_var('monthnum'),
		                             get_query_var('day'));
				$can_link = $this->paged_link($can_link);					 
		    } else if ($wp_query->is_month) {
		        $can_link = get_month_link(get_query_var('year'),
		                               get_query_var('monthnum'));
				$can_link = $this->paged_link($can_link);					   
		    } else if ($wp_query->is_year) {
		        $can_link = get_year_link(get_query_var('year'));
				$can_link = $this->paged_link($can_link);
		    }
		} else if (function_exists('is_tag') && is_tag()) {
		
			if (function_exists('tag_description')) {
				$tag_description = tag_description();
				$description = $this->get_string_between($tag_description, "[description]", "[/description]");			
				$keywords = $this->get_string_between($tag_description, "[keywords]", "[/keywords]");
				
				if ($description == "" || $description == null) {				
					$description = $this->trim_excerpt_without_filters($this->internationalize($tag_description));
				} else {				
					$description = $this->trim_excerpt_without_filters($this->internationalize($description));
				}
				
				if ($keywords != "" || $keywords != null) {
					$keywords = $this->internationalize($keywords);
				}
			}
		
			if ($canonical) {
				$tag = get_term_by('slug',get_query_var('tag'),'post_tag');
	             if (!empty($tag->term_id)) {
	                    $tag_link = get_tag_link($tag->term_id);
	             } 
				 $can_link = $this->paged_link($tag_link);
			}	 
		} else if (is_author() && ($canonical)) {
	        global $wp_version;
	        if ($wp_version >= '2') {
	            $author = get_userdata(get_query_var('author'));
	            if ($author === false)
	                return false;
	            $auth_link = get_author_link(false, $author->ID, $author->user_nicename);
				$can_link = $this->paged_link($auth_link);
	        } else {
	            global $cache_userdata;
	            $userid = get_query_var('author');
	            $auth_link = get_author_link(false, $userid, $cache_userdata[$userid]->user_nicename);
				$can_link = $this->paged_link($auth_link);
	        }
	    }

		if (isset($description) && (strlen($description) > $this->min_description_length) && !((is_home() && is_paged()) || (is_category() && is_paged()) || (function_exists('is_tag') && is_tag() && is_paged()))) {
			$description = trim(strip_tags($description));
			$description = str_replace('"', '', $description);

			// replace newlines on mac / windows?
			$description = str_replace("\r\n", ' ', $description);

			// maybe linux uses this alone
			$description = str_replace("\n", ' ', $description);

			if (isset($meta_string)) {
				$meta_string .= "\n";
			} else {
				$meta_string = '';
			}

			// description format
            $description_format = get_option('aiosp_description_format');
            if (!isset($description_format) || empty($description_format)) {
            	$description_format = "%description%";
            }
            $description = str_replace('%description%', $description, $description_format);
            $description = str_replace('%blog_title%', get_bloginfo('name'), $description);
            $description = str_replace('%blog_description%', get_bloginfo('description'), $description);
            $description = str_replace('%wp_title%', $this->get_original_title(), $description);

            $meta_string .= sprintf("<meta name=\"description\" content=\"%s\" />", $description);
		}

		if (isset($keywords) && !empty($keywords) && !((is_home() && is_paged()) || (is_category() && is_paged()) || (function_exists('is_tag') && is_tag() && is_paged()))) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string .= sprintf("<meta name=\"keywords\" content=\"%s\" />", $keywords);
		}

		$page_meta = stripcslashes(get_option('aiosp_page_meta_tags'));
		$post_meta = stripcslashes(get_option('aiosp_post_meta_tags'));
		$home_meta = stripcslashes(get_option('aiosp_home_meta_tags'));
		if (is_page() && isset($page_meta) && !empty($page_meta)) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			echo "\n$page_meta";
		}

		if (is_single() && isset($post_meta) && !empty($post_meta)) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string .= "$post_meta";
		}

		if (is_home() && !empty($home_meta)) {
			if (isset($meta_string)) {
				$meta_string .= "\n";
			}
			$meta_string .= "$home_meta";
		}

		if ($meta_string != null) {
			echo "$meta_string\n";
		}
		
		if ($can_link != '' && ($canonical)) {
			echo "".'<link rel="canonical" href="'.$can_link.'" />'."\n";
		}

		echo "<!-- /platinum one seo pack -->\n";
	}
	
	function paged_link($link) {
		$page = get_query_var('paged');
		$has_ut = function_exists('user_trailingslashit');
	    if ($page && $page > 1) {
	        $link = trailingslashit($link) ."page/". "$page";
	        if ($has_ut) {
	            $link = user_trailingslashit($link, 'paged');
	        } else {
	            $link .= '/';
	        }
		}
			return $link;
	}	
	
	function psp_category_description() {
		$cat_description = category_description();	
		
		$description = $this->get_string_between($cat_description, "[description]", "[/description]");	
		if ($description == "" || $description == null) {
			$description = $this->internationalize($cat_description);
		} else {
			$description = $this->internationalize($description);
		}
		echo "$description"."\n";
	}
	
	function psp_category_keywords() {
		$cat_description = category_description();
		$keywords = $this->get_string_between($cat_description, "[keywords]", "[/keywords]");
		
		if ($keywords != "" || $keywords != null) {
				$keywords = $this->internationalize($keywords);
		}
		echo "$keywords"."\n";
	}
	
	function psp_tag_description() {
		$tag_description = tag_description();	
		
		$description = $this->get_string_between($tag_description, "[description]", "[/description]");	
		if ($description == "" || $description == null) {
			$description = $this->internationalize($tag_description);
		} else {
			$description = $this->internationalize($description);
		}
		echo "$description"."\n";
	}
	
	function psp_tag_keywords() {
		$tag_description = tag_description();
		$keywords = $this->get_string_between($tag_description, "[keywords]", "[/keywords]");
		
		if ($keywords != "" || $keywords != null) {
				$keywords = $this->internationalize($keywords);
		}
		echo "$keywords"."\n";
	}

	function get_post_description($post) {
	    $description = trim(stripcslashes($this->internationalize(get_post_meta($post->ID, "description", true))));
		if (!$description) {
			$description = $this->trim_excerpt_without_filters_full_length($this->internationalize($post->post_excerpt));
			if (!$description && get_option("aiosp_generate_descriptions")) {
				$description = $this->trim_excerpt_without_filters($this->internationalize($post->post_content));
			}
		}

		// "internal whitespace trim"
		$description = preg_replace("/\s\s+/", " ", $description);

		return $description;
	}
	
	function get_string_between($string, $start, $end){
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);   
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
	}

	function replace_title($content, $title) {
		$title = trim(strip_tags($title));

		$title_tag_start = "<title>";
		$title_tag_end = "</title>";
		$len_start = strlen($title_tag_start);
		$len_end = strlen($title_tag_end);
		$title = stripcslashes(trim($title));
		$start = strpos($content, $title_tag_start);
		$end = strpos($content, $title_tag_end);

		$this->title_start = $start;
		$this->title_end = $end;
		$this->orig_title = $title;

		if ($start && $end) {
			$header = substr($content, 0, $start + $len_start) . $title .  substr($content, $end);
		} else {
			// this breaks some sitemap plugins (like wpg2)
			//$header = $content . "<title>$title</title>";

			$header = $content;
		}

		return $header;
	}

	function internationalize($in) {
		if (function_exists('langswitch_filter_langs_with_message')) {
			$in = langswitch_filter_langs_with_message($in);
		}
		if (function_exists('polyglot_filter')) {
			$in = polyglot_filter($in);
		}
		if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
			$in = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($in);
		}
		$in = apply_filters('localization', $in);
		return $in;
	}

	/** @return The original title as delivered by WP (well, in most cases) */
	function get_original_title() {
		global $wp_query;
		if (!$wp_query) {
			return null;
		}

		$post = $wp_query->get_queried_object();

		// the_search_query() is not suitable, it cannot just return
		global $s;

		$title = null;

		if (is_home()) {
			$title = get_option('blogname');
		} else if (is_single()) {
			$title = $this->internationalize(wp_title('', false));
		} else if (is_search() && isset($s) && !empty($s)) {
			if (function_exists('attribute_escape')) {
				$search = attribute_escape($s);
			} else {
				$search = esc_attr($s);
			}
			$search = $this->capitalize($search);
			$title = $search;
		} else if (is_category() && !is_feed()) {
			//$category_description = $this->internationalize(category_description());
			$category_name = ucwords($this->internationalize(single_cat_title('', false)));
			$title = $category_name;
		} else if (is_page()) {
			$title = $this->internationalize(wp_title('', false));
		} else if (function_exists('is_tag') && is_tag()) {
			global $utw;
			if ($utw) {
				$tags = $utw->GetCurrentTagSet();
				$tag = $tags[0]->tag;
		        $tag = str_replace('-', ' ', $tag);
			} else {
				// wordpress > 2.3
				$tag = $this->internationalize(wp_title('', false));
			}
			if ($tag) {
				$title = $tag;
			}
		} else if (is_archive()) {
			$title = $this->internationalize(wp_title('', false));
		} else if (is_404()) {
		    $title_format = get_option('aiosp_404_title_format');
		    $new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
		    $new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
		    $new_title = str_replace('%request_url%', $_SERVER['REQUEST_URI'], $new_title);
		    $new_title = str_replace('%request_words%', $this->request_as_words($_SERVER['REQUEST_URI']), $new_title);
				$title = $new_title;
			}

			return trim($title);
		}

	function paged_title($title) {
		// the page number if paged
		global $paged;

		// simple tagging support
		global $STagging;

		if (is_paged() || (isset($STagging) && $STagging->is_tag_view() && $paged)) {
			$part = $this->internationalize(get_option('aiosp_paged_format'));
			if (isset($part) || !empty($part)) {
				$part = " " . trim($part);
				$part = str_replace('%page%', $paged, $part);
				$this->log("paged_title() [$title] [$part]");
				$title .= $part;
			}
		}
		return $title;
	}

	function rewrite_title($header) {
		global $wp_query;
		if (!$wp_query) {
			$header .= "<!-- no wp_query found! -->\n";
			return $header;
		}

		$post = $wp_query->get_queried_object();

		// the_search_query() is not suitable, it cannot just return
		global $s;

		// simple tagging support
		global $STagging;

		if (is_home()) {
			$title = $this->internationalize(get_option('aiosp_home_title'));
			if (empty($title)) {
				$title = $this->internationalize(get_option('blogname'));
			}
			$title = $this->paged_title($title);
			$header = $this->replace_title($header, $title);
		} else if (is_single() || $this->is_static_front_page()) {
			// we're not in the loop :(
			$authordata = get_userdata($post->post_author);
			$categories = get_the_category($post->ID);
			$category = '';
			if (count($categories) > 0) {
				$category = $categories[0]->cat_name;
			}
			$title = $this->internationalize(get_post_meta($post->ID, "title", true));
			if (!$title) {
				$title = $this->internationalize(get_post_meta($post->ID, "title_tag", true));
				if (!$title) {
					$title = $this->internationalize(wp_title('', false));
				}
			}
			$psp_notitleformat = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_notitleformat', true)));
			if (!$psp_notitleformat) {    	
		   
				$title_format = get_option('aiosp_post_title_format');
				$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
				$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
				$new_title = str_replace('%post_title%', $title, $new_title);
				$new_title = str_replace('%category%', $category, $new_title);
				$new_title = str_replace('%category_title%', $category, $new_title);
				$new_title = str_replace('%post_author_login%', $authordata->user_login, $new_title);
				$new_title = str_replace('%post_author_nicename%', $authordata->user_nicename, $new_title);
				$new_title = str_replace('%post_author_firstname%', ucwords($authordata->first_name), $new_title);
				$new_title = str_replace('%post_author_lastname%', ucwords($authordata->last_name), $new_title);
				$title = $new_title;
			}
			$title = trim($title);
			$header = $this->replace_title($header, $title);
		} else if (is_search() && isset($s) && !empty($s)) {
			if (function_exists('attribute_escape')) {
				$search = attribute_escape($s);
			} else {
				$search = esc_attr($s);
			}
			$search = $this->capitalize($search);
            $title_format = get_option('aiosp_search_title_format');
            $title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
            $title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
            $title = str_replace('%search%', $search, $title);
			$header = $this->replace_title($header, $title);
		} else if (is_category() && !is_feed()) {
			$category_description = $this->internationalize(category_description());
			$category_name = ucwords($this->internationalize(single_cat_title('', false)));
            $title_format = get_option('aiosp_category_title_format');
            $title = str_replace('%category_title%', $category_name, $title_format);
            $title = str_replace('%category_description%', $category_description, $title);
            $title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title);
            $title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
            $title = $this->paged_title($title);
			$header = $this->replace_title($header, $title);
		} else if (is_page()) {
			// we're not in the loop :(
			$authordata = get_userdata($post->post_author);
			//if ($this->is_static_front_page()) {
				//if ($this->internationalize(get_option('aiosp_home_title'))) {
				//	$header = $this->replace_title($header, $this->internationalize(get_option('aiosp_home_title')));
				//}
			//} else {
				$title = $this->internationalize(get_post_meta($post->ID, "title", true));
				if (!$title) {
					$title = $this->internationalize(wp_title('', false));
				}
				$psp_notitleformat = htmlspecialchars(stripcslashes(get_post_meta($post->ID, 'psp_notitleformat', true)));
			    if (!$psp_notitleformat) {
					$title_format = get_option('aiosp_page_title_format');
					$new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
					$new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
					$new_title = str_replace('%page_title%', $title, $new_title);
					$new_title = str_replace('%page_author_login%', $authordata->user_login, $new_title);
					$new_title = str_replace('%page_author_nicename%', $authordata->user_nicename, $new_title);
					$new_title = str_replace('%page_author_firstname%', ucwords($authordata->first_name), $new_title);
					$new_title = str_replace('%page_author_lastname%', ucwords($authordata->last_name), $new_title);
					$title = trim($new_title);
				}
				$header = $this->replace_title($header, $title);
			//}
		} else if (function_exists('is_tag') && is_tag()) {
			global $utw;
			if ($utw) {
				$tags = $utw->GetCurrentTagSet();
				$tag = $tags[0]->tag;
	            $tag = str_replace('-', ' ', $tag);
			} else {
				// wordpress > 2.3
				$tag = $this->internationalize(wp_title('', false));
			}
			if ($tag) {
	            $tag = $this->capitalize($tag);
	            $title_format = get_option('aiosp_tag_title_format');
	            $title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
	            $title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
	            $title = str_replace('%tag%', $tag, $title);
	            $title = $this->paged_title($title);
				$header = $this->replace_title($header, $title);
			}
		} else if (isset($STagging) && $STagging->is_tag_view()) { // simple tagging support
			$tag = $STagging->search_tag;
			if ($tag) {
	            $tag = $this->capitalize($tag);
	            $title_format = get_option('aiosp_tag_title_format');
	            $title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
	            $title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $title);
	            $title = str_replace('%tag%', $tag, $title);
	            $title = $this->paged_title($title);
				$header = $this->replace_title($header, $title);
			}
      } else if (is_tax()) { // added by Aidan Curran, sww.co.nz
         $term = get_term_by('slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
         $title_format = get_option('psp_taxonomy_title_format');
         $new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
         $new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
         $new_title = str_replace('%term%', $term->name, $new_title);
         $title = $this->paged_title($new_title);
			$header = $this->replace_title($header, $title);
		} else if (is_archive()) {
			$date = $this->internationalize(wp_title('', false));
            $title_format = get_option('aiosp_archive_title_format');
            $new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
            $new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
            $new_title = str_replace('%date%', $date, $new_title);
            
			$title = trim($new_title);
            $title = $this->paged_title($title);
			$header = $this->replace_title($header, $title);
		} else if (is_404()) {
            $title_format = get_option('aiosp_404_title_format');
            $new_title = str_replace('%blog_title%', $this->internationalize(get_bloginfo('name')), $title_format);
            $new_title = str_replace('%blog_description%', $this->internationalize(get_bloginfo('description')), $new_title);
            $new_title = str_replace('%request_url%', $_SERVER['REQUEST_URI'], $new_title);
            $new_title = str_replace('%request_words%', $this->request_as_words($_SERVER['REQUEST_URI']), $new_title);
			$header = $this->replace_title($header, $new_title);
		}

		return $header;

	}

	/**
	 * @return User-readable nice words for a given request.
	 */
	function request_as_words($request) {
		$request = htmlspecialchars($request);
		$request = str_replace('.html', ' ', $request);
		$request = str_replace('.htm', ' ', $request);
		$request = str_replace('.', ' ', $request);
		$request = str_replace('/', ' ', $request);
		$request_a = explode(' ', $request);
		$request_new = array();
		foreach ($request_a as $token) {
			$request_new[] = ucwords(trim($token));
		}
		$request = implode(' ', $request_new);
		return $request;
	}

	function capitalize($s) {
		$s = trim($s);
		$tokens = explode(' ', $s);
		while (list($key, $val) = each($tokens)) {
			$tokens[$key] = trim($tokens[$key]);
			/*if (function_exists('mb_strtoupper')) {
				$tokens[$key] = mb_strtoupper(substr($tokens[$key], 0, 1)) . substr($tokens[$key], 1);
			} else {*/
				$tokens[$key] = strtoupper(substr($tokens[$key], 0, 1)) . substr($tokens[$key], 1);
			/*}*/
		}
		$s = implode(' ', $tokens);
		return $s;
	}

	function trim_excerpt_without_filters($text) {
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		$max = $this->max_description_length;

		if ($max < strlen($text)) {
			while($text[$max] != ' ' && $max > $this->min_description_length) {
				$max--;
			}
		}
		$text = substr($text, 0, $max);
		return trim(stripcslashes($text));
	}

	function trim_excerpt_without_filters_full_length($text) {
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = strip_tags($text);
		return trim(stripcslashes($text));
	}

	/**
	 * @return comma-separated list of unique keywords
	 */
	function get_all_keywords() {
		global $posts;

		if (is_404()) {
			return null;
		}

		// if we are on synthetic pages
		if (!is_home() && !is_page() && !is_single() && !$this->is_static_front_page() && !$this->is_static_posts_page()) {
			return null;
		}

	    $keywords = array();
	    if (is_array($posts)) {
	        foreach ($posts as $post) {
	            if ($post) {

	                // custom field keywords
	                $keywords_a = $keywords_i = null;
	                $description_a = $description_i = null;
	                $id = $post->ID;
		            $keywords_i = stripcslashes($this->internationalize(get_post_meta($post->ID, "keywords", true)));
	                $keywords_i = str_replace('"', '', $keywords_i);
	                if (isset($keywords_i) && !empty($keywords_i)) {
	                	$traverse = explode(',', $keywords_i);
	                	foreach ($traverse as $keyword) {
	                		$keywords[] = $keyword;
	                	}
	                }

	                if (get_option('psp_use_tags') && !is_page()) {
						// WP 2.3 tags
		                if (function_exists('get_the_tags')) {
		                	$tags = get_the_tags($post->ID);
		                	if ($tags && is_array($tags)) {
			                	foreach ($tags as $tag) {
			                		$keywords[] = $this->internationalize($tag->name);
			                	}
		                	}
		                }

		                // Ultimate Tag Warrior integration
		                global $utw;
		                if ($utw) {
		                	$tags = $utw->GetTagsForPost($post);
		                	if (is_array($tags)) {
			                	foreach ($tags as $tag) {
									$tag = $tag->tag;
									$tag = str_replace('_',' ', $tag);
									$tag = str_replace('-',' ',$tag);
									$tag = stripcslashes($tag);
			                		$keywords[] = $tag;
			                	}
		                	}
		                }
					}
	                // autometa
	                $autometa = stripcslashes(get_post_meta($post->ID, "autometa", true));
	                if (isset($autometa) && !empty($autometa)) {
	                	$autometa_array = explode(' ', $autometa);
	                	foreach ($autometa_array as $e) {
	                		$keywords[] = $e;
	                	}
	                }

	            	if (get_option('aiosp_use_categories') && !is_page()) {
		                $categories = get_the_category($post->ID);
		                foreach ($categories as $category) {
		                	$keywords[] = $this->internationalize($category->cat_name);
		                }
	            	}

	            }
	        }
	    }

	    return $this->get_unique_keywords($keywords);
	}

	function get_meta_keywords() {
		global $posts;

	    $keywords = array();
	    if (is_array($posts)) {
	        foreach ($posts as $post) {
	            if ($post) {
	                // custom field keywords
	                $keywords_a = $keywords_i = null;
	                $description_a = $description_i = null;
	                $id = $post->ID;
		            $keywords_i = stripcslashes(get_post_meta($post->ID, "keywords", true));
	                $keywords_i = str_replace('"', '', $keywords_i);
	                if (isset($keywords_i) && !empty($keywords_i)) {
	                    $keywords[] = $keywords_i;
	                }
	            }
	        }
	    }

	    return $this->get_unique_keywords($keywords);
	}

	function get_unique_keywords($keywords) {
		$uni_keywords = array();
		foreach ($keywords as $word) {
		    $uni_keywords[] = $word;
			/*if (function_exists('mb_strtolower')) {
				if (mb_detect_encoding($word) == 'UTF8') {
					$small_keywords[] = mb_strtolower($word, 'UTF8');	
				} else {
					$small_keywords[] = strtolower($word);					
				}
			} else {
				$small_keywords[] = strtolower($word);
			}*/
		}
		$keywords_ar = array_unique($uni_keywords);
		return implode(',', $keywords_ar);
	}

	function get_url($url)	{
		if (function_exists('file_get_contents')) {
			$file = file_get_contents($url);
		} else {
	        $curl = curl_init($url);
	        curl_setopt($curl, CURLOPT_HEADER, 0);
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	        $file = curl_exec($curl);
	        curl_close($curl);
	    }
	    return $file;
	}
	
	function add_footer_link()
	{
	?>
		<small>SEO Powered by <a href="http://techblissonline.com/platinum-seo-pack/" target="_blank">Platinum SEO</a> from <a href="http://techblissonline.com/" target="_blank">Techblissonline</a></small>
	<?php	
	}

	function log($message) {
		if ($this->do_log) {
			error_log(date('Y-m-d H:i:s') . " " . $message . "\n", 3, $this->log_file);
		}
	}

	function download_newest_version() {
		$success = true;
	    $file_content = $this->get_url($this->upgrade_url);
	    if ($file_content === false) {
	    	$this->upgrade_error = sprintf(__("Could not download distribution (%s)"), $this->upgrade_url);
			$success = false;
	    } else if (strlen($file_content) < 100) {
	    	$this->upgrade_error = sprintf(__("Could not download distribution (%s): %s"), $this->upgrade_url, $file_content);
			$success = false;
	    } else {
	    	$this->log(sprintf("filesize of download ZIP: %d", strlen($file_content)));
		    $fh = @fopen($this->upgrade_filename, 'w');
		    $this->log("fh is $fh");
		    if (!$fh) {
		    	$this->upgrade_error = sprintf(__("Could not open %s for writing"), $this->upgrade_filename);
		    	$this->upgrade_error .= "<br />";
		    	$this->upgrade_error .= sprintf(__("Please make sure %s is writable"), $this->upgrade_folder);
		    	$success = false;
		    } else {
		    	$bytes_written = @fwrite($fh, $file_content);
			    $this->log("wrote $bytes_written bytes");
		    	if (!$bytes_written) {
			    	$this->upgrade_error = sprintf(__("Could not write to %s"), $this->upgrade_filename);
			    	$success = false;
		    	}
		    }
		    if ($success) {
		    	fclose($fh);
		    }
	    }
	    return $success;
	}

	function install_newest_version() {
		$success = $this->download_newest_version();
	    if ($success) {
		    $success = $this->extract_plugin();
		    unlink($this->upgrade_filename);
	    }
	    return $success;
	}

	function extract_plugin() {
	    if (!class_exists('PclZip')) {
	        require_once ('pclzip.lib.php');
	    }
	    $archive = new PclZip($this->upgrade_filename);
	    $files = $archive->extract(PCLZIP_OPT_STOP_ON_ERROR, PCLZIP_OPT_REPLACE_NEWER, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_PATH, $this->upgrade_folder);
	    $this->log("files is $files");
	    if (is_array($files)) {
	    	$num_extracted = sizeof($files);
		    $this->log("extracted $num_extracted files to $this->upgrade_folder");
		    $this->log(print_r($files, true));
	    	return true;
	    } else {
	    	$this->upgrade_error = $archive->errorInfo();
	    	return false;
	    }
	}

	/** crude approximization of whether current user is an admin */
	function is_admin() {
		return current_user_can('level_8');
	}	

	function add_meta_index_tags($id) {
	    $awmp_edit = $_POST["psp_edit"];
		$nonce = $_POST['psp-meta-nonce'];
	    if (isset($awmp_edit) && !empty($awmp_edit) && wp_verify_nonce($nonce, 'psp-meta-nonce')) {
		    $keywords = $_POST["psp_keywords"];
		    $description = $_POST["psp_description"];
		    $title = $_POST["psp_title"];
		    //$pspmeta = $_POST["psp_robotsmeta"];
		    //$psp_disable = $_POST["psp_disable"];
			//$psp_noarchive = $_POST["psp_noarchive"];
		    //$psp_nosnippet = $_POST["psp_nosnippet"];

		    delete_post_meta($id, 'keywords');
		    delete_post_meta($id, 'description');
		    delete_post_meta($id, 'title');
		    if ($this->is_admin()) {
		    	delete_post_meta($id, 'psp_disable');
		    	delete_post_meta($id, 'robotsmeta');
				delete_post_meta($id, 'psp_notitlerewrite');
				delete_post_meta($id, 'psp_notitleformat');
				delete_post_meta($id, 'psp_noarchive');
				delete_post_meta($id, 'psp_nosnippet');
		    }

		    if (isset($keywords) && !empty($keywords)) {
			    add_post_meta($id, 'keywords', $keywords);
		    }
		    if (isset($description) && !empty($description)) {
			    add_post_meta($id, 'description', $description);
		    }
		    if (isset($title) && !empty($title)) {
			    add_post_meta($id, 'title', $title);
		    }
		    if ($this->is_admin()) {
			
				$pspmeta = $_POST["psp_robotsmeta"];
				$psp_disable = $_POST["psp_disable"];
				$psp_notitlerewrite = $_POST["psp_notitlerewrite"];
				$psp_notitleformat = $_POST["psp_notitleformat"];
				$psp_noarchive = $_POST["psp_noarchive"];
				$psp_nosnippet = $_POST["psp_nosnippet"];
	
		    	if (isset($psp_disable) && !empty($psp_disable)) {
			    		
					add_post_meta($id, 'psp_disable', $psp_disable);
		    		}
				if (isset($psp_notitlerewrite) && !empty($psp_notitlerewrite)) {
			    		
					add_post_meta($id, 'psp_notitlerewrite', $psp_notitlerewrite);
		    	}
				if (isset($psp_notitleformat) && !empty($psp_notitleformat)) {
			    		
					add_post_meta($id, 'psp_notitleformat', $psp_notitleformat);
		    	}
		    	if (isset($pspmeta) && !empty($pspmeta)) {

			    		add_post_meta($id, 'robotsmeta', $pspmeta);
		    		}
				if (isset($psp_noarchive) && !empty($psp_noarchive)) {

			    		add_post_meta($id, 'psp_noarchive', $psp_noarchive);
		    		}
				if (isset($psp_nosnippet) && !empty($psp_nosnippet)) {

			    		add_post_meta($id, 'psp_nosnippet', $psp_nosnippet);
		    		}
		    }
	    }
	}

	function psp_form_to_add_metatags() {
	    global $post;
	    $post_id = $post;
	    if (is_object($post_id)) {
	    	$post_id = $post_id->ID;
	    }

	    $robotsmeta = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'robotsmeta', true)));
		if (isset($robotsmeta) && empty($robotsmeta)) {
			$robotsmeta = "index,follow";
		}

	    $keywords = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'keywords', true)));
	    $title = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'title', true)));
	    $description = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'description', true)));
	    $psp_meta = $robotsmeta;
	    $psp_disable = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'psp_disable', true)));
		$psp_notitlerewrite = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'psp_notitlerewrite', true)));
		$psp_notitleformat = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'psp_notitleformat', true)));
		$psp_noarchive = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'psp_noarchive', true)));
        $psp_nosnippet = htmlspecialchars(stripcslashes(get_post_meta($post_id, 'psp_nosnippet', true)));
		?>
		<SCRIPT LANGUAGE="JavaScript">
		<!-- Begin
		function countChars(field,cntfield) {
		cntfield.value = field.value.length;
		}
		//  End -->
		</script>
		
		<?php if ((substr($this->wp_version, 0, 3) < '2.5')) { ?>
		<div class="dbx-b-ox-wrapper">
		<fieldset id="seodiv" class="dbx-box">
		<div class="dbx-h-andle-wrapper">
		<h3 class="dbx-handle"><?php _e('Platinum SEO Pack', 'platinum_seo_pack') ?></h3>
		</div>
		<div class="dbx-c-ontent-wrapper">
		<div class="dbx-content">
		<?php } ?>

		<a target="__blank" href="http://techblissonline.com/platinum-seo-pack/"><?php _e('Click here for Support', 'platinum_seo_pack') ?></a> |
		<a target="__blank" href="http://techblissonline.com/meta-robots-tag-indexfollownoindexnofollow-in-platinum-seo-plugin/"><?php _e('Know about the meta tags', 'platinum_seo_pack') ?></a>
		<input value="psp_edit" type="hidden" name="psp_edit" />
		<table style="margin-bottom:40px">
		<tr>
		<th style="text-align:left;" colspan="2">
		</th>
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Title:', 'platinum_seo_pack') ?></th>
		<td><input value="<?php echo $title ?>" type="text" name="psp_title" id="psp_title" size="92" onKeyDown="countChars(document.post.psp_title,document.post.length2)"	onKeyUp="countChars(document.post.psp_title,document.post.length2)"/></td></tr><tr><th></th><td>
		<input readonly type="text" name="length2" size="3" maxlength="2" value="<?php echo strlen($title);?>" />
		<font color="blue"><?php _e(' characters. Most search engines use a maximum of 70 chars for the title.', 'platinum_seo_pack') ?></font></td>		
		</tr>
		<tr>
		<th scope="row" style="text-align:right;"><?php _e('Description:', 'platinum_seo_pack') ?></th>
		<td><textarea name="psp_description" id="psp_description" rows="4" cols="60"
		onKeyDown="countChars(document.post.psp_description,document.post.length1)"
		onKeyUp="countChars(document.post.psp_description,document.post.length1)"><?php echo $description ?></textarea></td></tr><tr><th></th><td>
		<input readonly type="text" name="length1" size="3" maxlength="3" value="<?php echo strlen($description);?>" />
		<font color="blue"><?php _e(' characters. Most search engines use a maximum of 160 chars for the description.', 'platinum_seo_pack') ?></font>
		</td>
		</tr>
		<tr>
		<th scope="row" style="text-align:left;"><?php _e('Keywords (comma separated):', 'platinum_seo_pack') ?></th>
		<td><input value="<?php echo $keywords ?>" type="text" name="psp_keywords" size="92"/></td>
		</tr>
		<input type="hidden" name="psp-meta-nonce" value="<?php echo wp_create_nonce('psp-meta-nonce') ?>" />

		<?php if ( $this->is_admin() ) { ?>
		<tr>
		<th scope="row" style="text-align:left;"><?php _e('PSP Meta Index and Nofollow Tags:', 'platinum_seo_pack') ?></th>
		<td><label for="meta_robots_index_follow" class="selectit"><input id="meta_robots_index_follow" name="psp_robotsmeta" type="radio" value="index,follow" <?php if ($psp_meta == "index,follow") echo 'checked="1"'?>/>index, follow</label>&nbsp;&nbsp;
		<label for="meta_robots_index_nofollow" class="selectit"><input id="meta_robots_index_nofollow" name="psp_robotsmeta" type="radio" value="index,nofollow" <?php if ($psp_meta == "index,nofollow") echo 'checked="1"'?>/>index, nofollow</label>&nbsp;&nbsp;
		<label for="meta_robots_noindex_follow" class="selectit"><input id="meta_robots_noindex_follow" name="psp_robotsmeta" type="radio" value="noindex,follow" <?php if ($psp_meta == "noindex,follow") echo 'checked="1"'?>/>noindex, follow</label>&nbsp;&nbsp;
		<label for="meta_robots_noindex_nofollow" class="selectit"><input id="meta_robots_noindex_nofollow" name="psp_robotsmeta" type="radio" value="noindex,nofollow" <?php if ($psp_meta == "noindex,nofollow") echo 'checked="1"'?>/>noindex, nofollow</label></td>
		</tr>
		<?php } ?>
		
		<?php if ($this->is_admin()) { ?>		
		<tr>
		<th scope="row" style="text-align:left; vertical-align:top;">
		<?php _e('NOARCHIVE this page/post:', 'platinum_seo_pack')?>
		</th>
		<td>
		<input type="checkbox" name="psp_noarchive" <?php if ($psp_noarchive) echo "checked=\"1\""; ?>/>
		</td>
        </tr>
        <tr>
        </tr>
        <tr>		
		<th scope="row" style="text-align:left; vertical-align:top;">
		<?php _e('NOSNIPPET of this page/post:', 'platinum_seo_pack')?>
		</th>
		<td>
		<input type="checkbox" name="psp_nosnippet" <?php if ($psp_nosnippet) echo "checked=\"1\""; ?>/>
		</td>
		</tr>
		<tr>
        </tr>
		<tr>
		<th scope="row" style="text-align:left; vertical-align:top;">
		<?php _e('Disable title rewrite on this page/post:', 'platinum_seo_pack')?>
		</th>
		<td>
		<input type="checkbox" name="psp_notitlerewrite" <?php if ($psp_notitlerewrite) echo "checked=\"1\""; ?>/>
		</td>
		</tr>
		<tr>
        </tr>
		<tr>
		<th scope="row" style="text-align:left; vertical-align:top;">
		<?php _e('Disable PSP title format on this page/post:', 'platinum_seo_pack')?>
		</th>
		<td>
		<input type="checkbox" name="psp_notitleformat" <?php if ($psp_notitleformat) echo "checked=\"1\""; ?>/>
		</td>
		</tr>
		<tr>
        </tr>
		<tr>
		<th scope="row" style="text-align:left; vertical-align:top;">
		<?php _e('Disable PSP on this page/post:', 'platinum_seo_pack')?>
		</th>
		<td>
		<input type="checkbox" name="psp_disable" <?php if ($psp_disable) echo "checked=\"1\""; ?>/>
		</td>
		</tr>
		<?php } ?>
		
		</table>		

		<?php if ((substr($this->wp_version, 0, 3) < '2.5')) { ?>
		</div>
		</fieldset>
		</div>
		<?php } ?>
		<?php  
	}

	function admin_menu() {
		$file = __FILE__;
		$filem = 'platinum-seo-pack/aioseop-migrate.php';

		// hack for 1.5
		if (substr($this->wp_version, 0, 3) == '1.5') {
			$file = 'platinum-seo-pack/platinum_seo_pack.php';
		}
		//add_management_page(__('Platinum SEO Title', 'platinum_seo_pack'), __('Platinum SEO', 'platinum_seo_pack'), 10, $file, array($this, 'management_panel'));
		//add_submenu_page('options-general.php', __('Platinum SEO', 'platinum_seo_pack'), __('Platinum SEO', 'platinum_seo_pack'), 10, $file, array($this, 'options_panel'));
		add_menu_page(__('Platinum SEO', 'platinum_seo_pack'), __('Platinum SEO', 'platinum_seo_pack'), 10, $file, array($this, 'options_panel'));
		add_submenu_page($file, __('Migrate from All in one SEO', 'platinum_seo_pack'), __('Migrate from All in one SEO', 'platinum_seo_pack'), 10, $filem);
		if( function_exists( 'add_meta_box' )) {
			if ( function_exists( 'get_post_types' ) ) {
				$post_types = get_post_types( array(), 'objects' );
				foreach ( $post_types as $post_type ) {					
					add_meta_box( 'postpsp', __( 'Platinum SEO Pack', 'platinum_seo_pack' ), array($this, 'psp_form_to_add_metatags'), $post_type->name, 'normal', 'high' );
				}
			} else {
				add_meta_box( 'postpsp', __( 'Platinum SEO Pack', 'platinum_seo_pack' ), 
		                array($this, 'psp_form_to_add_metatags'), 'post', 'normal', 'high' );
				add_meta_box( 'postpsp', __( 'Platinum SEO Pack', 'platinum_seo_pack' ), 
		                array($this, 'psp_form_to_add_metatags'), 'page', 'normal', 'high' );				
			}		    
		} else {
		    add_action('dbx_post_advanced', 'psp_form_to_add_metatags' );
		    add_action('dbx_page_advanced', 'psp_form_to_add_metatags' );
		}
	}

	function management_panel() {
		$message = null;
		$base_url = "edit.php?page=" . __FILE__;
		//echo($base_url);
		$type = $_REQUEST['type'];
		if (!isset($type)) {
			$type = "posts";
		}

?>

  <ul class="psp_menu">
    <li><a href="<?php echo $base_url ?>&type=posts">Posts</a>
    </li>
    <li><a href="<?php echo $base_url ?>&type=pages">Pages</a>
    </li>
  </ul>

<?php

		if ($type == "posts") {
			echo("posts");
		} elseif ($type == "pages") {
			echo("pages");
		}

	}

	function options_panel() {
		$message = null;
		$message_updated = __("Platinum SEO Options Updated.", 'platinum_seo_pack');

		// update options
		if ($_POST['action'] && $_POST['action'] == 'psp_update') {
			$nonce = $_POST['psp-options-nonce'];
			if (!wp_verify_nonce($nonce, 'psp-options-nonce')) die ( 'Security Check - If you receive this in error, log out and back in to WordPress');
			$message = $message_updated;
			update_option('aiosp_home_title', $_POST['psp_home_title']);
			update_option('aiosp_home_description', $_POST['psp_home_description']);
			update_option('aiosp_home_keywords', $_POST['psp_home_keywords']);
			update_option('psp_max_words_excerpt', $_POST['psp_max_words_excerpt']);
			update_option('psp_canonical', $_POST['psp_canonical']);
			update_option('aiosp_rewrite_titles', $_POST['psp_rewrite_titles']);
			update_option('aiosp_post_title_format', $_POST['psp_post_title_format']);
			update_option('aiosp_page_title_format', $_POST['psp_page_title_format']);
			update_option('aiosp_category_title_format', $_POST['psp_category_title_format']);
			update_option('psp_taxonomy_title_format', $_POST['psp_taxonomy_title_format']);  // added by Aidan - sww.co.nz
			update_option('aiosp_archive_title_format', $_POST['psp_archive_title_format']);
			update_option('aiosp_tag_title_format', $_POST['psp_tag_title_format']);
			update_option('aiosp_search_title_format', $_POST['psp_search_title_format']);
			update_option('aiosp_description_format', $_POST['psp_description_format']);
			update_option('aiosp_404_title_format', $_POST['psp_404_title_format']);
			update_option('aiosp_paged_format', $_POST['psp_paged_format']);
			update_option('aiosp_use_categories', $_POST['psp_use_categories']);
			update_option('psp_use_tags', $_POST['psp_use_tags']);
			update_option('psp_category_noindex', $_POST['psp_category_noindex']);
			update_option('psp_archive_noindex', $_POST['psp_archive_noindex']);
			update_option('psp_tags_noindex', $_POST['psp_tags_noindex']);
			update_option('psp_comnts_pages_noindex', $_POST['psp_comnts_pages_noindex']);
			update_option('psp_comnts_feeds_noindex', $_POST['psp_comnts_feeds_noindex']);
			update_option('psp_rss_feeds_noindex', $_POST['psp_rss_feeds_noindex']);
			update_option('psp_search_results_noindex', $_POST['psp_search_results_noindex']);
			update_option('psp_sub_pages_home_noindex', $_POST['psp_sub_pages_home_noindex']);
			update_option('psp_author_archives_noindex', $_POST['psp_author_archives_noindex']);
			update_option('psp_noodp_metatag', $_POST['psp_noodp_metatag']);
			update_option('psp_noydir_metatag', $_POST['psp_noydir_metatag']);
			update_option('psp_nofollow_cat_pages', $_POST['psp_nofollow_cat_pages']);
			update_option('psp_nofollow_cat_posts', $_POST['psp_nofollow_cat_posts']);
			update_option('psp_nofollow_arc_pages', $_POST['psp_nofollow_arc_pages']);
			update_option('psp_nofollow_arc_posts', $_POST['psp_nofollow_arc_posts']);
			update_option('psp_nofollow_ext_links', $_POST['psp_nofollow_ext_links']);
			update_option('psp_nofollow_login_reg', $_POST['psp_nofollow_login_reg']);
			update_option('psp_nofollow_tag_pages', $_POST['psp_nofollow_tag_pages']);
			update_option('psp_permalink_redirect', $_POST['psp_permalink_redirect']);
			update_option('aiosp_generate_descriptions', $_POST['psp_generate_descriptions']);
			update_option('psp_debug_info', $_POST['psp_debug_info']);
			update_option('aiosp_post_meta_tags', $_POST['psp_post_meta_tags']);
			update_option('aiosp_page_meta_tags', $_POST['psp_page_meta_tags']);
			update_option('aiosp_home_meta_tags', $_POST['psp_home_meta_tags']);
			update_option('aiosp_do_log', $_POST['psp_do_log']);
			update_option('psp_link_home', $_POST['psp_link_home']);
			if (function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}
		} elseif ($_POST['psp_upgrade']) {
			$message = __("Upgraded to newest version. Please revisit the options page to ensure you see the newest version.", 'platinum_seo_pack');
			$success = $this->install_newest_version();
			if (!$success) {
				$message = __("Upgrade failed", 'platinum_seo_pack');
				if (isset($this->upgrade_error) && !empty($this->upgrade_error)) {
					$message .= ": " . $this->upgrade_error;
				} else {
					$message .= ".";
				}
			}
		}

?>
<?php if ($message) : ?>
<div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
<?php endif; ?>
<div id="dropmessage" class="updated" style="display:none;"></div>
<div class="wrap">
<h2><?php _e('Platinum SEO Plugin Options', 'platinum_seo_pack'); ?></h2>
<p>
<?php _e("This is version ", 'platinum_seo_pack') ?><?php _e("$this->version ", 'platinum_seo_pack') ?>
&nbsp;
| <a target="_blank" title="<?php _e('FAQ', 'platinum_seo_pack') ?>"
href="http://techblissonline.com/platinum-seo-pack-faq/"><?php _e('FAQ', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Platinum SEO Plugin Feedback', 'platinum_seo_pack') ?>" href="http://techblissonline.com/platinum-seo-pack/"><?php _e('Feedback', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Platinum SEO - What is new in version 1.3.4?', 'platinum_seo_pack') ?>" href="http://techblissonline.com/platinum-seo-pack/"><?php _e('What is new in version 1.3.4?', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Platinum SEO - Smart Options, Smart Benefits', 'platinum_seo_pack') ?>" href="http://techblissonline.com/wordpress-seo-plugin-smart-options-benefits/"><?php _e('Wordpress SEO options', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Donations for Platinum SEO Plugin', 'platinum_seo_pack') ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=rrajeshbab%40gmail%2ecom&item_name=Platinum%20SEO%20plugin%20development%20and%20support%20expenses&item_number=1&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=IN&bn=PP%2dDonationsBF&charset=UTF%2d8"><?php _e('Please Donate', 'platinum_seo_pack') ?></a>
| <a target="_blank" title="<?php _e('Save Bandwidth with Chennai Central Plugin', 'platinum_seo_pack') ?>" href="http://techblissonline.com/save-bandwidth/"><?php _e('Save Bandwidth with Chennai Central Plugin', 'platinum_seo_pack') ?></a>
</p>

<script type="text/javascript">
<!--
    function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
//-->
</script>
<h3><?php _e('Pls. write a review or choose to link back, if you cannot donate', 'platinum_seo_pack') ?></h3>
<h3><?php _e('Click on option titles to get help!', 'platinum_seo_pack') ?></h3>

<form name="dofollow" action="" method="post">
<table class="form-table">

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_permalink_redirect_tip');">
<?php _e('Automatically do 301 redirects for permalink changes:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_permalink_redirect" <?php if (get_option('psp_permalink_redirect')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_permalink_redirect_tip">
<?php
_e('Check this to Automatically do 301 redirects for any permalink changes', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_home_title_tip');">
<?php _e('Home Title:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<textarea cols="57" rows="2" name="psp_home_title"><?php echo stripcslashes(get_option('aiosp_home_title')); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="psp_home_title_tip">
<?php
_e('As the name implies, this will be the title of your homepage. This is independent of any other option. If not set, the default blog title will get used.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_home_description_tip');">
<?php _e('Home Description:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<textarea cols="57" rows="2" name="psp_home_description"><?php echo stripcslashes(get_option('aiosp_home_description')); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="psp_home_description_tip">
<?php
_e('The META description for your homepage. The default is no META description, if this is not set.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_home_keywords_tip');">
<?php _e('Home Keywords (comma separated):', 'platinum_seo_pack')?>
</a>
</td>
<td>
<textarea cols="57" rows="2" name="psp_home_keywords"><?php echo stripcslashes(get_option('aiosp_home_keywords')); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="psp_home_keywords_tip">
<?php
_e("A comma separated list of the most important keywords for your site homepage. Use optimal number of keywords.", 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_canonical_tip');">
<?php _e('Canonical URLs:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_canonical" <?php if (get_option('psp_canonical')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_canonical_tip">
<?php
_e("Choose this option to set up canonical URLs for your Home page, Single Post, Category and Tag Pages.", 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_rewrite_titles_tip');">
<?php _e('Rewrite Titles:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_rewrite_titles" <?php if (get_option('aiosp_rewrite_titles')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_rewrite_titles_tip">
<?php
_e("Note that this is all about the title tag. This is what you see in your browser's window title bar. This is NOT visible on a page, only in the window title bar and of course in the source. If set, all page, post, category, search and archive page titles get rewritten. You can specify the format for most of them. For example: The default templates puts the title tag of posts like this: Blog Archive >> Blog Name >> Post Title. But this is far from optimal. With the default post title format, Rewrite Title rewrites this to Post Title | Blog Name. If you have manually defined a title (in one of the text fields for Platinum SEO Plugin input) this will become the title of your post in the format string.", 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_post_title_format_tip');">
<?php _e('Post Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_post_title_format" value="<?php echo stripcslashes(get_option('aiosp_post_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_post_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%post_title% - The original title of the post', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%category_title% - The (main) category of the post', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%category% - Alias for %category_title%', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%post_author_login% - This post author's login", 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%post_author_nicename% - This post author's nicename", 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%post_author_firstname% - This post author's first name (capitalized)", 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%post_author_lastname% - This post author's last name (capitalized)", 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_page_title_format_tip');">
<?php _e('Page Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_page_title_format" value="<?php echo stripcslashes(get_option('aiosp_page_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_page_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%page_title% - The original title of the page', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%page_author_login% - This page author's login", 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%page_author_nicename% - This page author's nicename", 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%page_author_firstname% - This page author's first name (capitalized)", 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e("%page_author_lastname% - This page author's last name (capitalized)", 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_category_title_format_tip');">
<?php _e('Category Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_category_title_format" value="<?php echo stripcslashes(get_option('aiosp_category_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_category_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%category_title% - The original title of the category', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%category_description% - The description of the category', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<?php /* added by Aidan - sww.co.nz */ ?>
<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_taxonomy_title_format_tip');">
<?php _e('Custom Taxonomy Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_taxonomy_title_format" value="<?php echo stripcslashes(get_option('psp_taxonomy_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_taxonomy_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%term% - The custom taxonomy term', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>


<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_archive_title_format_tip');">
<?php _e('Archive Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_archive_title_format" value="<?php echo stripcslashes(get_option('aiosp_archive_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_archive_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%date% - The original archive title given by wordpress, e.g. "2007" or "2007 August"', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_tag_title_format_tip');">
<?php _e('Tag Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_tag_title_format" value="<?php echo stripcslashes(get_option('aiosp_tag_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_tag_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%tag% - The name of the tag', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_search_title_format_tip');">
<?php _e('Search Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_search_title_format" value="<?php echo stripcslashes(get_option('aiosp_search_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_search_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%search% - What was searched for', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_description_format_tip');">
<?php _e('Description Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_description_format" value="<?php echo stripcslashes(get_option('aiosp_description_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_description_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%description% - The original description as determined by the plugin, for e.g. the excerpt if one is set or an auto-generated one, if that option is set', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%wp_title% - The original wordpress title, for e.g. post title for posts', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_404_title_format_tip');">
<?php _e('404 Title Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_404_title_format" value="<?php echo stripcslashes(get_option('aiosp_404_title_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_404_title_format_tip">
<?php
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%blog_title% - Your blog title', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%blog_description% - Your blog description', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%request_url% - The original URL path, like "/url-that-does-not-exist/"', 'platinum_seo_pack'); echo('</li>');
echo('<li>'); _e('%request_words% - The URL path in human readable form, like "Url That Does Not Exist"', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_paged_format_tip');">
<?php _e('Paged Format:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input size="59" name="psp_paged_format" value="<?php echo stripcslashes(get_option('aiosp_paged_format')); ?>"/>
<div style="max-width:500px; text-align:left; display:none" id="psp_paged_format_tip">
<?php
_e('This string gets appended/prepended to titles when they are for paged index pages (like home or archive pages).', 'platinum_seo_pack');
_e('The following macros are supported:', 'platinum_seo_pack');
echo('<ul>');
echo('<li>'); _e('%page% - The page number', 'platinum_seo_pack'); echo('</li>');
echo('</ul>');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_use_categories_tip');">
<?php _e('Use Categories for META keywords:', 'platinum_seo_pack')?>
</td>
<td>
<input type="checkbox" name="psp_use_categories" <?php if (get_option('aiosp_use_categories')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_use_categories_tip">
<?php
_e('Check this if you want your categories for a given post to be used as META keywords for the post (in addition to any keywords you specify on the post edit page).', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_category_noindex_tip');">
<?php _e('Use noindex for Categories:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_category_noindex" <?php if (get_option('psp_category_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_category_noindex_tip">
<?php
_e('Check this for excluding category pages from being crawled. Might help to avoid duplicate content.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_archive_noindex_tip');">
<?php _e('Use noindex for Date based Archives:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_archive_noindex" <?php if (get_option('psp_archive_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_archive_noindex_tip">
<?php
_e('Check this for excluding date based archive pages from being crawled. Useful for avoiding duplicate content.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_use_tags_tip');">
<?php _e('Use Tags for META keywords:', 'platinum_seo_pack')?>
</td>
<td>
<input type="checkbox" name="psp_use_tags" <?php if (get_option('psp_use_tags')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_use_tags_tip">
<?php
_e('Check this if you want your tags for a given post to be used as META keywords for the post (in addition to any keywords you specify on the post edit page).', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_tags_noindex_tip');">
<?php _e('Use noindex for Tag Archives:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_tags_noindex" <?php if (get_option('psp_tags_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_tags_noindex_tip">
<?php
_e('Check this for excluding tag pages from being crawled. Might help to avoid duplicate content.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_comnts_pages_noindex_tip');">
<?php _e('Use noindex for comment pages of posts(Introduced in wordpress 2.7):', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_comnts_pages_noindex" <?php if (get_option('psp_comnts_pages_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_comnts_pages_noindex_tip">
<?php
_e('Check this for excluding comments pages from being indexed. Thereby avoid duplicate content if you wish to use Comment paging (from wordpress 2.7). Note that wordpress 2.7 creates comments pages when the option to break comments into pages is chosen in WP 2.7 under Settings-->Discussion', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_comnts_feeds_noindex_tip');">
<?php _e('Use noindex for comments RSS feeds:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_comnts_feeds_noindex" <?php if (get_option('psp_comnts_feeds_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_comnts_feeds_noindex_tip">
<?php
_e('Check this for excluding comments RSS feeds from being indexed.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_rss_feeds_noindex_tip');">
<?php _e('Use noindex for all RSS feeds:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_rss_feeds_noindex" <?php if (get_option('psp_rss_feeds_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_rss_feeds_noindex_tip">
<?php
_e('Check this for excluding all RSS feeds from being indexed.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_search_results_noindex_tip');">
<?php _e('Use noindex for Search result pages on the site:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_search_results_noindex" <?php if (get_option('psp_search_results_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_search_results_noindex_tip">
<?php
_e('Check this for excluding all search result pages from being indexed.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_sub_pages_home_noindex_tip');">
<?php _e('Use noindex for sub pages:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_sub_pages_home_noindex" <?php if (get_option('psp_sub_pages_home_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_sub_pages_home_noindex_tip">
<?php
_e('Check this for excluding all sub pages of home, categories, date based archives, tag, search and author pages from being indexed.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_author_archives_noindex_tip');">
<?php _e('Use noindex for author archives:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_author_archives_noindex" <?php if (get_option('psp_author_archives_noindex')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_author_archives_noindex_tip">
<?php
_e('Check this for excluding author archives from being indexed.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_noodp_metatag_tip');">
<?php _e('Add noodp meta tag:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_noodp_metatag" <?php if (get_option('psp_noodp_metatag')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_noodp_metatag_tip">
<?php
_e('Check this for adding noopd meta tag.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_noydir_metatag_tip');">
<?php _e('Add noydir meta tag:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_noydir_metatag" <?php if (get_option('psp_noydir_metatag')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_noydir_metatag_tip">
<?php
_e('Check this for adding noydir meta tag.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_cat_pages_tip');">
<?php _e('nofollow category listings on pages:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_cat_pages" <?php if (get_option('psp_nofollow_cat_pages')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_cat_pages_tip">
<?php
_e('Check this to nofollow category listings on pages.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_cat_posts_tip');">
<?php _e('nofollow category listings on posts:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_cat_posts" <?php if (get_option('psp_nofollow_cat_posts')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_cat_posts_tip">
<?php
_e('Check this to nofollow category listings on posts (Not recommended)', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_arc_pages_tip');">
<?php _e('nofollow archive listings on pages:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_arc_pages" <?php if (get_option('psp_nofollow_arc_pages')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_arc_pages_tip">
<?php
_e('Check this to nofollow archive listings on pages.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_arc_posts_tip');">
<?php _e('nofollow archive listings on posts:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_arc_posts" <?php if (get_option('psp_nofollow_arc_posts')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_arc_posts_tip">
<?php
_e('Check this to nofollow archive listings on posts (Not recommended)', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_ext_links_tip');">
<?php _e('nofollow external links on front page:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_ext_links" <?php if (get_option('psp_nofollow_ext_links')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_ext_links_tip">
<?php
_e('Check this to nofollow external links on front page including home,category,author,tag and search pages.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_login_reg_tip');">
<?php _e('nofollow login and registration links:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_login_reg" <?php if (get_option('psp_nofollow_login_reg')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_login_reg_tip">
<?php
_e('Check this to nofollow login and registration links', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_nofollow_tag_pages_tip');">
<?php _e('nofollow links to tag pages:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_nofollow_tag_pages" <?php if (get_option('psp_nofollow_tag_pages')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_nofollow_tag_pages_tip">
<?php
_e('Check this to nofollow links to tag pages', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_generate_descriptions_tip');">
<?php _e('Autogenerate Descriptions:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_generate_descriptions" <?php if (get_option('aiosp_generate_descriptions')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_generate_descriptions_tip">
<?php
_e("Check this and your META descriptions will get autogenerated, if there's no excerpt.", 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_post_meta_tags_tip');">
<?php _e('Additional Post Headers:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<textarea cols="57" rows="2" name="psp_post_meta_tags"><?php echo stripcslashes(get_option('aiosp_post_meta_tags')); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="psp_post_meta_tags_tip">
<?php
_e('What you enter here will be copied verbatim to your header on post pages. You can enter whatever additional headers you want here, even references to stylesheets or google, yahoo, msn verification links.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_page_meta_tags_tip');">
<?php _e('Additional Page Headers:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<textarea cols="57" rows="2" name="psp_page_meta_tags"><?php echo stripcslashes(get_option('aiosp_page_meta_tags')); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="psp_page_meta_tags_tip">
<?php
_e('What you enter here will be copied verbatim to your header on pages. You can enter whatever additional headers you want here, even references to stylesheets or google, yahoo, msn verification links', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_home_meta_tags_tip');">
<?php _e('Additional Home Headers:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<textarea cols="57" rows="2" name="psp_home_meta_tags"><?php echo stripcslashes(get_option('aiosp_home_meta_tags')); ?></textarea>
<div style="max-width:500px; text-align:left; display:none" id="psp_home_meta_tags_tip">
<?php
_e('What you enter here will be copied verbatim to your header on the home page. You can enter whatever additional headers you want here, even references to stylesheets or google, yahoo, msn verification links', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_do_log_tip');">
<?php _e('Log important events:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_do_log" <?php if (get_option('aiosp_do_log')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_do_log_tip">
<?php
_e('Check this and Platinum SEO pack will create a log of important events (platinum_seo_pack.log) in its plugin directory which might help debugging it. Make sure this directory is writable.', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

<tr>
<th scope="row" style="text-align:right; vertical-align:top;">
<a style="cursor:pointer;" title="<?php _e('Click for Help!', 'platinum_seo_pack')?>" onclick="toggleVisibility('psp_link_home_tip');">
<?php _e('Link To Platinum SEO:', 'platinum_seo_pack')?>
</a>
</td>
<td>
<input type="checkbox" name="psp_link_home" <?php if (get_option('psp_link_home')) echo "checked=\"1\""; ?>/>
<div style="max-width:500px; text-align:left; display:none" id="psp_link_home_tip">
<?php
_e('Check this to link to Platinum SEO and spread the word.If you do not want to donate, atleast link to home.Linking would not hurt your site in any way.Think twice before you decide not to link', 'platinum_seo_pack');
 ?>
</div>
</td>
</tr>

</table>
<p class="submit">
<input type="hidden" name="action" value="psp_update" />
<input type="hidden" name="psp-options-nonce" value="<?php echo wp_create_nonce('psp-options-nonce'); ?>" />
<input type="submit" name="Submit" value="<?php _e('Update Options', 'platinum_seo_pack')?> &raquo;" />
</p>
</form>
</div>
<?php

	} // options_panel

}

add_option("aiosp_home_description", null, '', 'yes');
add_option("aiosp_home_title", null, '', 'yes');
add_option("psp_canonical", 1, '', 'yes');
add_option("aiosp_home_keywords", null, '', 'yes');
add_option("aiosp_rewrite_titles", 1, '', 'yes');
add_option("aiosp_use_categories", 0, '', 'yes');
add_option("psp_use_tags", 0, '', 'yes');
add_option("psp_category_noindex", 0, '', 'yes');
add_option("psp_archive_noindex", 1, '', 'yes');
add_option("psp_tags_noindex", 0, '', 'yes');
add_option("psp_comnts_pages_noindex", 1, '', 'yes');
add_option("psp_comnts_feeds_noindex", 1, '', 'yes');
add_option("psp_rss_feeds_noindex", 1, '', 'yes');
add_option("psp_search_results_noindex", 1, '', 'yes');
add_option("psp_sub_pages_home_noindex", 1, '', 'yes');
add_option("psp_author_archives_noindex", 1, '', 'yes');
add_option("psp_noodp_metatag", 1, '', 'yes');
add_option("psp_noydir_metatag", 1, '', 'yes');
add_option("psp_nofollow_cat_pages", 0, '', 'yes');
add_option("psp_nofollow_cat_posts", 0, '', 'yes');
add_option("psp_nofollow_arc_pages", 0, '', 'yes');
add_option("psp_nofollow_arc_posts", 0, '', 'yes');
add_option("psp_nofollow_ext_links", 0, '', 'yes');
add_option("psp_nofollow_login_reg", 1, '', 'yes');
add_option("psp_nofollow_tag_pages", 0, '', 'yes');
add_option("psp_permalink_redirect", 1, '', 'yes');
add_option("aiosp_generate_descriptions", 1, '', 'yes');
add_option("aiosp_post_title_format", '%post_title% | %blog_title%', '', 'yes');
add_option("aiosp_page_title_format", '%page_title% | %blog_title%', '', 'yes');
add_option("aiosp_category_title_format", '%category_title% | %blog_title%', '', 'yes');
add_option("psp_taxonomy_title_format", '%term% | %blog_title%', '', 'yes');  // added by Aidan - sww.co.nz
add_option("aiosp_archive_title_format", '%date% | %blog_title%', '', 'yes');
add_option("aiosp_tag_title_format", '%tag% | %blog_title%', '', 'yes');
add_option("aiosp_search_title_format", '%search% | %blog_title%', '', 'yes');
add_option("aiosp_description_format", '%description%', '', 'yes');
add_option("aiosp_paged_format", ' - Part %page%', '', 'yes');
add_option("aiosp_404_title_format", 'Nothing found for %request_words%', '', 'yes');
add_option("aiosp_post_meta_tags", '', '', 'yes');
add_option("aiosp_page_meta_tags", '', '', 'yes');
add_option("aiosp_home_meta_tags", '', '', 'yes');
add_option("aiosp_do_log", null, '', 'yes');
add_option("psp_link_home", 0, '', 'yes');

$psp = new Platinum_SEO_Pack();
add_action('wp_head', array($psp, 'echo_to_blog_header'));

if (get_option('psp_permalink_redirect')) {
	add_action( 'template_redirect', array($psp, 'has_permalink_changed') );
}

add_action('get_header', array($psp, 'apply_seo_title'));

add_action('init', array($psp, 'init'));

add_action('edit_post', array($psp, 'add_meta_index_tags'));
add_action('publish_post', array($psp, 'add_meta_index_tags'));
add_action('save_post', array($psp, 'add_meta_index_tags'));
add_action('edit_page_form', array($psp, 'add_meta_index_tags'));

add_action('admin_menu', array($psp, 'admin_menu'));

if ((substr($psp->wp_version, 0, 3) >= '2.3') || (substr($psp->wp_version, 0, 3) == '2.5') || (substr($psp->wp_version, 0, 3) == '2.6')) {
	if (get_option('psp_comnts_feeds_noindex') || get_option('psp_rss_feeds_noindex')) {
		add_action('commentsrss2_head', array($psp,'noindex_feed'));
	}
}
if (get_option('psp_rss_feeds_noindex')) {
	add_action('rss_head', array($psp,'noindex_feed'));
	add_action('rss2_head', array($psp,'noindex_feed'));
}

if (get_option('psp_nofollow_cat_pages') || get_option('psp_nofollow_cat_posts')) {
	add_filter('wp_list_categories',array($psp,'nofollow_category_listing'));
	add_filter('the_category',array($psp,'nofollow_category_listing'));
}

if (get_option('psp_nofollow_arc_pages') || get_option('psp_nofollow_arc_posts')) {
	add_filter('get_archives_link',array($psp,'nofollow_archive_listing'));	
}
if (get_option('psp_nofollow_login_reg')) {
	add_filter('loginout',array($psp,'nofollow_link'));
	add_filter('register',array($psp,'nofollow_link'));
}
if (get_option('psp_nofollow_tag_pages')) {
	add_filter('the_tags',array($psp,'nofollow_taglinks'));
}

if (get_option('psp_link_home')) {
	add_action('wp_footer',array($psp,'add_footer_link'));
}

add_action('get_psp_category_description',array($psp,'psp_category_description'));
add_action('get_psp_category_keywords',array($psp,'psp_category_keywords'));
add_action('get_psp_tag_description',array($psp,'psp_tag_description'));
add_action('get_psp_tag_keywords',array($psp,'psp_tag_keywords'));

/** if (get_option('psp_nofollow_ext_links')) {
	add_filter('the_content',array($psp,'nofollow_home_category'));
} **/

?>
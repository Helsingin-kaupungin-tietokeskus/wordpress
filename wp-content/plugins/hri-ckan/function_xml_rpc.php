<?php

/**
 * The new CKAN 2.1.1 delivers metadata arrays in a different format compared to the old one
 * Note that if there's only one item, the input is a string (and no change is required)
 * This function returns the data in the old format where necessary.
 *
 * Old format: "category": "just one" -- "category": ["1.", "2.","here's a space"]
 * New format: "category": "just one" -- "category": "{"",1.,2.,\"here's a space\"}"
 *
 * @param string $array
 * @return array $array
 * @author Janne Mikkonen <janne.mikkonen@hiq.fi>
 */
function fix_new_ckan_arrays($array) {
	
	// Sometimes stuff comes in form "category": [\"1.\", \"2.\",\"here's a space\"]
	// Replace the first character so this function realizes to take care of it.
	if(strpos($array[0], "\\") !== 0) { 

		$array[0] = str_replace("[", "{", $array[0], $count = 1);
		$array[0] = str_replace("\\", "", $array[0]);
	}

	// Recognice new format arrays with this.
	if(strpos($array[0], '{') !== 0) { return $array; }
	
	// Get rid of the first "{" and the last "}".
	$data = str_replace("{", "", $array[0], $count = 1);
	$data = substr($data, 0, -1);
	// When adding a resource to an existing dataset, we get these.
	$data = str_replace('"', "", $data);
	// Extra white space after comma causes troubles.
	$data = str_replace(', ', ",", $data);
	
	$array = explode(',', $data);
	
	// Empty value ("") creeps in sometimes, let's get rid of it.
	$key = array_search('""', $array);
	if($key !== false) {
		
		unset($array[$key]);
	}
		
	return $array;
}

/**
 * The new CKAN 2.1.1 delivers metadata arrays in a different format compared to the old one
 * Note that if there's only one item, the input is a string (and no change is required)
 * This function returns the data in the old format where necessary.
 *
 * Old format: "category": "just one" -- "category": ["1.", "2.","here's a space"]
 * New format: "category": "just one" -- "category": "{"",1.,2.,\"here's a space\"}"
 *
 * @param string $string
 * @return string $string
 * @author Janne Mikkonen <janne.mikkonen@hiq.fi>
 */
function fix_new_ckan_strings($string) {
	
	// Sometimes stuff comes in form "category": [\"1.\", \"2.\",\"here's a space\"]
	// Replace the first character so this function realizes to take care of it.
	if(strpos($string, "\\") !== 0) { $string = str_replace("[", "{", $string, $count = 1);	}

	// Recognice new format strings with this.
	if(strpos($string, '{') !== 0) { return $string; }
	
	// Get rid of the first "{" and the last "}".
	$string = str_replace("{", "", $string, $count = 1);
	$string = substr($string, 0, -1);
	// Also get rid of the empty string if we have one.
	$string = str_replace('"",', "", $string, $count = 1);
	// When adding a resource to an existing dataset, we get these.
	$string = str_replace('"', "", $string);
	// Add some spacing to make the text more visually appealing.
	$string = str_replace(",", ", ", $string);
	
	return $string;
}

/**
 * Search a post's WordPress post_id by slug
 * This functionality is required when transferring posts' comments
 * over to CKAN.
 *
 * @param mixed $args
 * @return int $post_id
 * @author Janne Mikkonen <janne.mikkonen@hiq.fi>
 */
function ckan_find_wordpress_post_id($args) {
	
	ini_set('html_errors', 0);

	// Parse the arguments, assuming they're in the correct order
	$username	= $args[0];
	$password	= $args[1];
	$slug		= $args[2];
	
	global $wp_xmlrpc_server;
	
	// Let's run a check to see if credentials are okay
	/* if(!$user = $wp_xmlrpc_server->login($username, $password)) {
		return $wp_xmlrpc_server->error;
	} */

	global $wpdb;
	
	return $wpdb->get_results( "SELECT p.ID FROM $wpdb->posts p WHERE p.post_type = 'data' AND p.guid LIKE 'http://www.hri.fi/blog/data/{$slug}/' OR p.post_name = '{$slug}';" );
}

/**
 * NOTE! This is modified wp_xmlrpc_server::wp_getComments from wp-includes/class-wp-xmlrpc-server.php
 *
 * Removed $this->login($username, $password) and current_user_can( 'moderate_comments' ) requirement.
 *
 * @param array $args Method parameters.
 * @return array. Contains a collection of comments. See {@link wp_xmlrpc_server::wp_getComment()} for a description of each item contents
 */
function hri_getComments($args) {

	hri_escape($args);

	$blog_id	= (int) $args[0];
	$username	= $args[1];
	$password	= $args[2];
	$struct		= isset( $args[3] ) ? $args[3] : array();

	/*if ( !$user = $this->login($username, $password) )
		return $this->error;

	if ( !current_user_can( 'moderate_comments' ) )
		return new IXR_Error( 401, __( 'Sorry, you cannot edit comments.' ) );*/

	do_action('xmlrpc_call', 'wp.getComments');

	if ( isset($struct['status']) )
		$status = $struct['status'];
	else
		$status = '';

	$post_id = '';
	if ( isset($struct['post_id']) )
		$post_id = absint($struct['post_id']);

	$offset = 0;
	if ( isset($struct['offset']) )
		$offset = absint($struct['offset']);

	$number = 25;
	if ( isset($struct['number']) )
		$number = absint($struct['number']);

	$comments = get_comments( array('status' => $status, 'post_id' => $post_id, 'offset' => $offset, 'number' => $number ) );

	$comments_struct = array();

	foreach ( $comments as $comment ) {
		$comments_struct[] = hri_prepare_comment( $comment );
	}

	return $comments_struct;
}

/**
 * NOTE! This is modified wp_xmlrpc_server::wp_newComment from wp-includes/class-wp-xmlrpc-server.php
 *
 * Removed $this->login($username, $password) and login requirement.
 *
 * @param array $args Method parameters.
 * @return mixed {@link wp_new_comment()}
 */
function hri_newComment($args) {
	
	global $wpdb;

	hri_escape($args);

	$blog_id	= (int) $args[0];
	$username	= $args[1];
	$password	= $args[2];
	$post		= $args[3];
	$content_struct = $args[4];

	$logged_id      = false;

	if ( is_numeric($post) )
		$post_id = absint($post);
	else
		$post_id = url_to_postid($post);

	if ( ! $post_id )
		return new IXR_Error( 404, __( 'Invalid post ID.' ) );

	if ( ! get_post($post_id) )
		return new IXR_Error( 404, __( 'Invalid post ID.' ) );

	$comment['comment_post_ID'] = $post_id;

	if ( $logged_in ) {
		$comment['comment_author'] = $wpdb->escape( $user->display_name );
		$comment['comment_author_email'] = $wpdb->escape( $user->user_email );
		$comment['comment_author_url'] = $wpdb->escape( $user->user_url );
		$comment['user_ID'] = $user->ID;
	} else {
		$comment['comment_author'] = '';
		if ( isset($content_struct['author']) )
			$comment['comment_author'] = $content_struct['author'];

		$comment['comment_author_email'] = '';
		if ( isset($content_struct['author_email']) )
			$comment['comment_author_email'] = $content_struct['author_email'];

		$comment['comment_author_url'] = '';
		if ( isset($content_struct['author_url']) )
			$comment['comment_author_url'] = $content_struct['author_url'];

		$comment['user_ID'] = 0;

		if ( 6 > strlen($comment['comment_author_email']) || '' == $comment['comment_author'] )
			return new IXR_Error( 403, __( 'Comment author name and email are required' ) );
		elseif ( !is_email($comment['comment_author_email']) )
			return new IXR_Error( 403, __( 'A valid email address is required' ) );
	}

	$comment['comment_parent']  = isset($content_struct['comment_parent']) ? absint($content_struct['comment_parent']) : 0;
	$comment['comment_content'] = isset($content_struct['content']) ? $content_struct['content'] : null;

	do_action('xmlrpc_call', 'wp.newComment');

	$comment_ID = wp_new_comment( $comment );

	do_action( 'xmlrpc_call_success_wp_newComment', $comment_ID, $args );

	return $comment_ID;
}

/* The following helpers I had to copy from the original class because I cannot extend it here... >< */


function hri_escape(&$array) {
	global $wpdb;

	if (!is_array($array)) {
		return($wpdb->escape($array));
	} else {
		foreach ( (array) $array as $k => $v ) {
			if ( is_array($v) ) {
				hri_escape($array[$k]);
			} else if ( is_object($v) ) {
				//skip
			} else {
				$array[$k] = $wpdb->escape($v);
			}
		}
	}
}

function hri_prepare_comment( $comment ) {
	// Format page date.
	$comment_date = hri_convert_date( $comment->comment_date );
	$comment_date_gmt = hri_convert_date_gmt( $comment->comment_date_gmt, $comment->comment_date );

	if ( '0' == $comment->comment_approved )
		$comment_status = 'hold';
	else if ( 'spam' == $comment->comment_approved )
		$comment_status = 'spam';
	else if ( '1' == $comment->comment_approved )
		$comment_status = 'approve';
	else
		$comment_status = $comment->comment_approved;

	$_comment = array(
		'date_created_gmt' => $comment_date_gmt,
		'user_id'          => $comment->user_id,
		'comment_id'       => $comment->comment_ID,
		'parent'           => $comment->comment_parent,
		'status'           => $comment_status,
		'content'          => $comment->comment_content,
		'link'             => get_comment_link($comment),
		'post_id'          => $comment->comment_post_ID,
		'post_title'       => get_the_title($comment->comment_post_ID),
		'author'           => $comment->comment_author,
		'author_url'       => $comment->comment_author_url,
		'author_email'     => $comment->comment_author_email,
		'author_ip'        => $comment->comment_author_IP,
		'type'             => $comment->comment_type,
	);

	return apply_filters( 'xmlrpc_prepare_comment', $_comment, $comment );
}

function hri_convert_date( $date ) {
	if ( $date === '0000-00-00 00:00:00' ) {
		return new IXR_Date( '00000000T00:00:00Z' );
	}
	return mysql2date( 'd.m.Y H:i', $date, false );
}

function hri_convert_date_gmt( $date_gmt, $date ) {
	if ( $date !== '0000-00-00 00:00:00' && $date_gmt === '0000-00-00 00:00:00' ) {
		return new IXR_Date( get_gmt_from_date( mysql2date( 'Y-m-d H:i:s', $date, false ), 'Ymd\TH:i:s' ) );
	}
	return hri_convert_date( $date_gmt );
}

/* End of copied functions */

/**
 * NOTE! This is modified wp_subscribe_reloaded::new_comment_posted from wp-content/plugins/subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php
 *
 * Removed parts concerning posting a new comment as we wish simply to subscribe here.
 *
 * @param array $args Method parameters.
 * @return mixed
 */
function hri_subscribeToComments($args) {

	include_once('../subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php');

	$wp_subscribe_reloaded = new wp_subscribe_reloaded();

	$username	= $args[0];
	$password	= $args[1];
	$post		= $args[2];
	$email  	= $args[3];

	$status 	= 'Y';

	// Are subscriptions allowed for this post?
	$is_disabled = get_post_meta($post, 'stcr_disable_subscriptions', true);
	if(!empty($is_disabled)) { return false; }

	if(!$wp_subscribe_reloaded->is_user_subscribed($post, $email)) {

		// Are we using double check-in?
		$approved_subscriptions = $wp_subscribe_reloaded->get_subscriptions(array('status', 'email'), array('equals', 'equals'), array('Y', $email));
		if((get_option('subscribe_reloaded_enable_double_check', 'no') == 'yes') && !is_user_logged_in() && empty($approved_subscriptions)) {
			$status = "{$status}C";
			$wp_subscribe_reloaded->confirmation_email($post, $email);
		}
		$wp_subscribe_reloaded->add_subscription($post, $email, $status);

		return true;
	}
	else {
		
		return new IXR_Error( 401, __( 'Already subscribed to post\'s comments.' ) );
	}
}

/**
 * Based on wp_subscribe_reloaded::delete_subscriptions from wp-content/plugins/subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php
 *
 * Removed parts concerning posting a new comment as we wish simply to subscribe here.
 *
 * @param array $args Method parameters.
 * @return mixed
 */
function hri_removeSubscriptionToComments($args) {
	
	include_once('../subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php');

	$wp_subscribe_reloaded = new wp_subscribe_reloaded();

	$username	= $args[0];
	$password	= $args[1];
	$post		= $args[2];
	$email  	= $args[3];

	if($wp_subscribe_reloaded->is_user_subscribed($post, $email)) {
		
		$wp_subscribe_reloaded->delete_subscriptions($post, $email);

		return 1;
	}
	else {
		
		return new IXR_Error( 401, __( 'Cannot remove a non-existing subscription.' ) );
	}
}

function hri_removeAllSubscriptionsToComments($args) {
	
	include_once('../subscribe-to-comments-reloaded/subscribe-to-comments-reloaded.php');

	$wp_subscribe_reloaded = new wp_subscribe_reloaded();

	$username	= $args[0];
	$password	= $args[1];
	$email  	= $args[2];

	$users_subscriptions = $wp_subscribe_reloaded->get_subscriptions(array('status', 'email'), array('equals', 'equals'), array('Y', $email));

	$post_ids = array();
	foreach($users_subscriptions as $subscription) {

		$post_ids[] = $subscription->post_id;
	}
	
	$wp_subscribe_reloaded->delete_subscriptions($post_ids, $email);

	return 1;
}


function save_ckan_data_package( $args ) {

	ini_set('html_errors', 0);

	// Parse the arguments, assuming they're in the correct order
	$username	= $args[0];
	$password	= $args[1];
	
	global $wp_xmlrpc_server;
	
	// Let's run a check to see if credentials are okay
	if ( !$user = $wp_xmlrpc_server->login($username, $password) ) {
		return $wp_xmlrpc_server->error;
	}
	
	if ( !is_array( $args[2] ) ) $json_string = $args[2];
	else $json_string = reset( $args[2] );
	
	if ( strpos( $json_string, 'body' ) === 0 ) $json_string = trim(substr( $json_string, 4 ));
	
	$json = json_decode( $json_string, true );
		
	if ( $json == null ) return "0-JSON parsing failed";

	/*
	 * if entity-type is not set or it is something different than package, stop here
	 */
	if( !isset( $json['entity-type'] ) || $json['entity-type'] != 'Package' ) return '1-skipped non Package';
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 * Get licenses
	 * --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 */
	
	$string_licenses = file_get_contents( ABSPATH . 'wp-content/plugins/hri-ckan/lisenssit.txt');
	$licenses = json_decode( $string_licenses, true );
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 * Get data already in WP to array. Keys in array are IDs in WPs database and value is CKAN ID
	 * --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 */

	global $wpdb;

	$data_ckan_ids = array();

	$results = $wpdb->get_results( "SELECT p.ID, pm.meta_value FROM $wpdb->posts p JOIN $wpdb->postmeta pm ON p.ID = pm.post_id WHERE p.post_type = 'data' AND pm.meta_key = 'id';" );
	if( $results ) {

		foreach( $results as $r ) {

			$data_ckan_ids[ $r->ID ] = $r->meta_value;

		}

	}
	
	//http://www.php.net/manual/en/function.array-values.php#86190
	function array_flatten($array, &$newArray = Array() ,$prefix='',$delimiter='|') { 
		foreach ($array as $key => $child) { 
			if (is_array($child)) { 
				$newPrefix = $prefix.$key.$delimiter; 
				$newArray =& array_flatten($child, $newArray ,$newPrefix, $delimiter); 
			} else { 
				$newArray[$prefix.$key] = $child; 
			} 
		} 
		return $newArray; 
	}

	$json_flat = array();

	// Flatten the array to single dimension
	array_flatten($json['payload'], $json_flat, null, '_');

	// Preset some default values
	$title = 'Unnamed data';
	$name = 'unnamed-data';
	$post_status = 'pending';

	if( isset( $json_flat['id'] ) ) $json_ckan_id = $json_flat['id'];
	else return '0-Package did not contain ID';

	// Loop for all values in array
	foreach ($json_flat as $property => $value) {

		// Some values has special purpose, so we seperate them
		if ( $property == 'extras_Hakutieto' || $property == 'extras_search_info' ) {
			
			$wp_meta['extras_search_info'] = $value;
			
		} elseif ( $property == 'title' ) {
			
			$title = $value;
			$name = sanitize_title($title, 'new-data');
			
		} elseif ( substr( $property, 0, 5 ) == 'tags_' ) {
		
			// Tags are collected to an array
			$tags[] = $value;
		
		} elseif ( substr( $property, 0, 18 ) == 'extras_categories_' || $property == 'categories' || $property == 'extras_categories' ) {
		
			// Categories to a seperate array too
			$cats[] = $value;
		
		} elseif ( substr( $property, 0, 10) == 'license_id' ) {
			
			// Get URL for license
			$wp_meta[$property] = $value;
			foreach ( $licenses as $license ) {
				
				if ( $license['id'] ==  $value ) $wp_meta['license_url'] = $license['url'];
				
			}
			
		} else {
			
			// The new CKAN 2.1.1 handles these fields differently, so that into account.
			if($property == 'extras_geographic_coverage' || $property == 'extras_geographic_granularity' || $property == 'extras_temporal_granularity') {
				
				$value = fix_new_ckan_strings($value);
			}
			
			// Everything else goes to items WP meta
			$wp_meta[$property] = $value;
			
			if ( $property == 'id' ) continue;

			if( $property == 'state' ) {
				if( $value == 'draft' ) $post_status = 'draft';
				elseif( $value == 'active' ) $post_status = 'publish';
			}

			// State can be active, pending or deleted
			if($property == 'state' && $value == 'deleted') {

				// Data already exists in our DB, lets delete data AND keywords, categories etc to related ONLY to this data
				if ( count($data_ckan_ids) > 0 && in_array( $json_ckan_id, $data_ckan_ids ) ) {
					$res = $wpdb->get_results('SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key = "id" AND meta_value = "'.$json_ckan_id.'"');
					$ids = array();
					foreach($res as $r) {
						array_push($ids, $r->post_id);
					}
					$ids = implode(", ", $ids);

					// todo: use wp_delete_post()

					// Delete old data
					$q = 'DELETE FROM ' . $wpdb->prefix . 'posts WHERE ' . $wpdb->prefix . 'posts.ID in ('.$ids.')';
					error_log("Deleting data: ".$q);
					$wpdb->query($q);

					// Delete postmeta ' . $wpdb->prefix . 'terms
					$q = 'DELETE FROM ' . $wpdb->prefix . 'term_relationships WHERE object_id IN ('.$ids.')';
					error_log("Deleting term relationships: ".$q);
					$wpdb->query($q);

					$q = 'DELETE ' . $wpdb->prefix . 'term_taxonomy FROM ' . $wpdb->prefix . 'term_taxonomy WHERE term_taxonomy_id NOT IN (SELECT ' . $wpdb->prefix . 'term_relationships.term_taxonomy_id FROM ' . $wpdb->prefix . 'term_relationships)';
					error_log("Deleting term taxonomy: ".$q);
					$wpdb->query($q);

					$q = 'DELETE ' . $wpdb->prefix . 'terms FROM ' . $wpdb->prefix . 'terms WHERE term_id NOT IN (SELECT ' . $wpdb->prefix . 'term_taxonomy.term_id FROM ' . $wpdb->prefix . 'term_taxonomy)';
					error_log("Deleting terms: ".$q);
					$wpdb->query($q);

					// Delete postmeta
					$q = 'DELETE ' . $wpdb->prefix . 'postmeta FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'postmeta.post_id in ('.$ids.')';
					error_log("Deleting postmeta: ".$q);
					$wpdb->query($q);

					// TODO: Comments and discussions -> draft
				}

				return '1-deleted';
			}
		
		}
		
	}
	
	/*
	 * ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 * Now we have vars $content, $title, $name and arrays $tags and $wp_meta
	 *
	 * Check are we updating an item that already exists in WP or should we create a new one?
	 * ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 */
	
	// Updating existing
	if ( count($data_ckan_ids) > 0 && in_array( $json_ckan_id, $data_ckan_ids ) ) {
		
		// Already in WP

		// Get WP ID of that item
		$wp_id = array_search( $json_ckan_id, $data_ckan_ids );
		
		$update = array( 'ID' => $wp_id, 'post_content' => '', 'post_title' => $title, 'post_name' => $name, 'post_status' => $post_status );
		wp_update_post( $update );
		
	} else  {
		
		// Add new item to WP and get its ID
		$insert = array(
			'post_title' => $title,
			'post_name' => $name,
			'post_type' => 'data',
			'post_status' => $post_status,
		);
		
		$wp_id = wp_insert_post( $insert );
		
	}

	/*
	 * ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 * Now $content, $title and $name has been updated to an old item or a new item has been created. In both cases, $wp_id contains target ID
	 *
	 * Next we add meta data, cats and tags
	 * ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 */

	global $wpdb;

	if ( isset($wp_meta) && !empty($wp_meta) ){

		$results = $wpdb->get_col( "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = $wp_id AND meta_key REGEXP '_([0-9]+)$';" );

		$existing_wp_meta_arrays = array();

		if( $results ) {

			foreach( $results as $result ) {

				$key = preg_replace( '/(_[0-9]+)$/', '', $result );
				$existing_wp_meta_arrays[ $key ] = true;

			}

			$existing_wp_meta_arrays = array_keys( $existing_wp_meta_arrays );

		}

		if( !empty( $existing_wp_meta_arrays ) ) {

			foreach( $existing_wp_meta_arrays as $existing_wp_meta_array ) {

				if( in_array( $existing_wp_meta_array, $wp_meta ) ) $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id = $wp_id AND meta_key LIKE '$existing_wp_meta_array%'" );

			}

		}


		$keys_in_string = implode( ' ', array_keys( $wp_meta ) );

		preg_match_all( '/([_\-a-z0-9]+?)_0/', $keys_in_string, $matches );

		if( !empty( $matches ) ) {

			$tmp = array();

			foreach( $matches[1] as $a ) {

				$tmp[$a] = true;

			}

			$meta_arrays = array_keys( $tmp );

			foreach( $meta_arrays as $meta_array ) {

				$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id = $wp_id AND ( meta_key = '$meta_array' OR meta_key REGEXP '{$meta_array}_([0-9]+)' )" );

			}

		}

		foreach ( $wp_meta as $property => $value ) {

			update_post_meta( $wp_id, $property, $value );

		}

	}

	/*
	 * ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 * Loop all cats and tags from CKAN data and test are they found in $catlist / $taglist
	 *
	 * If found, add that cat's / tag's ID to array of to-be-added cats / tags
	 * If not found, create a new cat / tag first and then add it to array
	 * ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 */

	$taglist = null;
	$catlist = null;

	if ( isset( $cats ) && !empty( $cats ) ) {
		$cats_in_wp = $wpdb->get_results("SELECT te.term_id, te.name FROM $wpdb->terms te, $wpdb->term_taxonomy tx WHERE tx.taxonomy = 'category' AND te.term_id = tx.term_id");
		foreach( $cats_in_wp as $cat_in_wp ) {
			$catlist[$cat_in_wp->term_id] = $cat_in_wp->name;			
		}
		
		// Turn a Python-dictionary string into an array when necessary (for the new CKAN 2.1.1).
		$cats = fix_new_ckan_arrays($cats);
		
		foreach( $cats as $cat ) {
			if ( $catlist ) $old_cat_id = array_search( $cat, $catlist );
			else $old_cat_id = false;
			
			if ( $old_cat_id !== false ) {
				
				$add_cats_to_wp[] = (int) $old_cat_id;
				
			} else {

				$new_term_array = wp_insert_term( $cat, 'category' );

				if( is_array($new_term_array) ) $add_cats_to_wp[] = (int) $new_term_array['term_id'];

			}
		}
		
		wp_set_object_terms( $wp_id, $add_cats_to_wp, 'category');
		
	}

	/*
	 * ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 * Fetch all tags from WPDB and array them (tag ID as key and tag as value).
	 * ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	 */

	if ( isset( $tags ) && !empty( $tags ) ) {
		
		$tags_in_wp = $wpdb->get_results("SELECT te.term_id, te.name FROM $wpdb->terms te, $wpdb->term_taxonomy tx WHERE tx.taxonomy = 'post_tag' AND te.term_id = tx.term_id");
		foreach( $tags_in_wp as $tag_in_wp ) {
			$taglist[$tag_in_wp->term_id] = $tag_in_wp->name;
		}
	
		foreach( $tags as $tag ) {
			
			if ( $taglist ) $old_tag_id = array_search( $tag, $taglist );
			else $old_tag_id = false;
			
			if ( $old_tag_id !== false ) {
				
				$add_tags_to_wp[] = (int) $old_tag_id;
				
			} else {
				
				$new_term_array = wp_insert_term( $tag, 'post_tag' );
				
				// Now we have added new tag to wp. Add that's tag id to $add_tags_to_wp
				if( is_array($new_term_array) ) $add_tags_to_wp[] = (int) $new_term_array['term_id'];
				
			}
		}
		
		// Update posts cats and tags. Array $add_tags_to_wp contains ID's of all tags to be added to post, both already existed tags and ones we created in previous loop
		wp_set_object_terms( $wp_id, $add_tags_to_wp, 'post_tag');
		
	}

	return '1-ok';

}

?>

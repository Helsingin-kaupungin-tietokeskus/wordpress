<?php
/*
Plugin Name: CKAN for Helsinki Region Infoshare
Plugin URI: http://www.hri.fi/
Description: A plugin which brings CKAN content to WordPress
Version: 0.35
Author: Barabra
Author URI: http://www.barabra.fi/
License: GPL
*/

load_plugin_textdomain('hri-ckan', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

/* -------------------------------------------------------------------------------------------------------------------------------- Register custom content types */

add_action( 'init', 'custom_contents_setup' );

if ( ! function_exists( 'custom_contents_setup' ) ) {

	function custom_contents_setup() {

		register_post_type('data', array(
			'labels' => array(
				'name' => __('Data', 'hri-ckan'),
			),
			'capability_type' => 'post',
			'public' => true,
			'supports' => array('title','editor','comments'),
			'taxonomies' => array('post_tag','category'),
			'rewrite' => array('slug' => __('data', 'hri-ckan')),
			'exclude_from_search' => false,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'query_var' => true
		));
	
		register_post_type('discussion', array(
			'labels' => array(
				'name' => __('Discussions', 'hri-ckan'),
				'singular_name' => __('Discussion', 'hri-ckan'),
			),
			'capability_type' => 'post',
			'public' => true,
			'supports' => array('title','editor', 'custom-fields','comments','author'),
			'taxonomies' => array('post_tag'),
			'rewrite' => array('slug' => __('discussions', 'hri-ckan')),
			'exclude_from_search' => false,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'query_var' => true
		));
		
		register_post_type('application', array(
			'labels' => array(
				'name' => __('Applications', 'hri-ckan'),
				'singular_name' => __('Application', 'hri-ckan'),
			),
			'capability_type' => 'post',
			'public' => true,
			'supports' => array('title','editor', 'custom-fields','comments','thumbnail'),
			'taxonomies' => array('post_tag'),
			'rewrite' => array('slug' => __('applications', 'hri-ckan')),
			'exclude_from_search' => false,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'query_var' => true
		));

		$labels = array(
			'name' => 'Application categories',
			'singular_name' => 'Application category',
			'search_items' =>  'Search categories',
			'popular_items' => 'Popular categories',
			'all_items' => 'All categories',
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => 'Edit category',
			'update_item' => 'Update category',
			'add_new_item' => 'Add new category',
			'new_item_name' => 'New category name',
			'separate_items_with_commas' => 'Separate categories with comma',
			'add_or_remove_items' => 'Add or remove categories',
			'choose_from_most_used' => 'Pick from most popular categories',
		);

		register_taxonomy('hri_appcats','application', array(
			'label' => __('Application category'),
			'labels' => $labels,
			'hierarchical' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'category' ),
		));
		
		register_post_type('application-idea', array(
			'labels' => array(
				'name' => __('Application ideas', 'hri-ckan'),
				'singular_name' => __('Application idea', 'hri-ckan'),
			),
			'capability_type' => 'post',
			'public' => true,
			'has_archive' => true,
			'supports' => array('title','editor', 'custom-fields','comments'),
			'taxonomies' => array('post_tag'),
			'rewrite' => array('slug' => __('application-ideas', 'hri-ckan')),
			'exclude_from_search' => false,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'query_var' => true
		));

		register_post_type('data-request', array(
			'labels' => array(
				'name' => __('Data requests', 'hri-ckan'),
				'singular_name' => __('Data request', 'hri-ckan'),
			),
			'capability_type' => 'post',
			'public' => true,
			'has_archive' => true,
			'supports' => array('title','editor', 'custom-fields','comments'),
			'taxonomies' => array('post_tag'),
			'rewrite' => array('slug' => __('data-requests', 'hri-ckan')),
			'exclude_from_search' => false,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'query_var' => true
		));

		register_post_type('help-page', array(
			'labels' => array(
				'name' => __('Help pages', 'hri-ckan'),
				'singular_name' => __('Help page', 'hri-ckan'),
			),
			'capability_type' => 'page',
			'public' => true,
			'hierarchical' => true,
			'supports' => array('title','editor', 'custom-fields', 'page-attributes'),
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'show_ui' => true,
			'query_var' => true
		));

	}
}

/* -------------------------------------------------------------------------------------------------------------------------------- Admin extras */

if ( is_admin() ) require( ABSPATH . 'wp-content/plugins/hri-ckan/function_admin_extras.php' );

/* -------------------------------------------------------------------------------------------------------------------------------- Random data widget */

require( ABSPATH . 'wp-content/plugins/hri-ckan/function_widget.php' );

add_action('widgets_init', function() {

	$hri_widgets = array( 'hri_random_data', 'hri_editors_pick', 'hri_vb_widget', 'hri_latest_apps', 'hri_infobox' );
	foreach( $hri_widgets as $hri_widget ) register_widget( $hri_widget );

});

/* -------------------------------------------------------------------------------------------------------------------------------- Ajax autocomplete data / tags */

add_action('wp_ajax_get_data_titles', 'get_data_titles');
add_action('wp_ajax_nopriv_get_data_titles', 'get_data_titles');

add_action('wp_ajax_get_tag_names', 'get_tag_names');
add_action('wp_ajax_nopriv_get_tag_names', 'get_tag_names');

require( ABSPATH . 'wp-content/plugins/hri-ckan/function_ajax_autocomplete.php' );

/* -------------------------------------------------------------------------------------------------------------------------------- Ajax search */

add_action('wp_ajax_hri_search', 'hri_search');
add_action('wp_ajax_nopriv_hri_search', 'hri_search');

require( ABSPATH . 'wp-content/plugins/hri-ckan/function_ajax_search.php' );

/* -------------------------------------------------------------------------------------------------------------------------------- Ajax discussion search / list */

add_action('wp_ajax_hri_discussion_search', 'hri_discussion_search');
add_action('wp_ajax_nopriv_hri_discussion_search', 'hri_discussion_search');

require( ABSPATH . 'wp-content/plugins/hri-ckan/function_ajax_discussions.php' );

/* -------------------------------------------------------------------------------------------------------------------------------- XML-RPC */

add_filter('xmlrpc_methods', 'hri_xmlrpc_methods');

function hri_xmlrpc_methods($methods) {

	$methods['save_package'] = 'save_ckan_data_package';
	$methods['wp.getPostId'] = 'ckan_find_wordpress_post_id';
	$methods['hri.getComments'] = 'hri_getComments';
	$methods['hri.newComment'] = 'hri_newComment';
	$methods['hri.subscribeToComments'] = 'hri_subscribeToComments';
	$methods['hri.removeSubscriptionToComments'] = 'hri_removeSubscriptionToComments';
	$methods['hri.removeAllSubscriptionsToComments'] = 'hri_removeAllSubscriptionsToComments';

	return $methods;
}

require( ABSPATH . 'wp-content/plugins/hri-ckan/function_xml_rpc.php' );

?>

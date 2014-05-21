<?php

function get_data_titles() {
	
	global $wpdb;
	switch_to_blog(1);

	if( isset( $_GET['not'] ) && !empty( $_GET['not'] ) ) {

		$not_in = preg_replace( '/([^0-9,])/', '', $_GET['not'] );
		$not_in = substr( $not_in, 0, strlen( $not_in )-1 );
		$not_clause = " AND ID NOT IN ($not_in)";

	} else  $not_clause = '';
	
	$s = mysql_real_escape_string( $_GET['search_string'] );
	$q = "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_status = 'publish' AND post_type = 'data' AND post_title LIKE '%$s%' $not_clause LIMIT 0 , 10";
	$results = $wpdb->get_results( $q );
	
	$i = 0;
	$answers = array();
	
	foreach ( $results as $r ) {
		
		$answers[$i]['id'] = $r->ID;
		$answers[$i]['title'] = $r->post_title;
		$i++;
		
	}
	
	echo json_encode( $answers );
	exit;
	
}

function get_tag_names() {
	
	global $wpdb;
//	switch_to_blog(1);

	if( isset( $_GET['not'] ) && !empty( $_GET['not'] ) ) {

		$not_in = preg_replace( '/([^0-9,])/', '', $_GET['not'] );
		$not_in = substr( $not_in, 0, strlen( $not_in )-1 );
		$not_clause = " AND t.term_id NOT IN ($not_in)";

	} else  $not_clause = '';
	
	$s = mysql_real_escape_string( $_GET['search_string'] );
	$q = "SELECT t.term_id, t.name FROM {$wpdb->prefix}terms t, {$wpdb->prefix}term_taxonomy x WHERE t.name LIKE '%$s%' AND x.term_id = t.term_id AND x.taxonomy = 'post_tag' $not_clause LIMIT 0 , 10";
	$results = $wpdb->get_results( $q );
	
	$i = 0;
	$answers = array();
	
	foreach ( $results as $r ) {
		
		$answers[$i]['id'] = $r->term_id;
		$answers[$i]['title'] = $r->name;
		$i++;
		
	}
	
	echo json_encode( $answers );
	exit;
	
}

?>
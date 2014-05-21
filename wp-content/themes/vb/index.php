<?php

global $wp_query;

switch_to_blog(2);

// Find redirect target by post slug
if( isset( $wp_query->query_vars['name'] ) && $wp_query->query_vars['name'] ) {

	$redirect_target = new WP_Query(array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'name' => $wp_query->query_vars['name']
	));

	if( $redirect_target->have_posts() ) {

		$redirect_target->the_post();
		$link = get_permalink();

		header( "Location: $link", true, 301 );
		exit;

	}

}

// Find redirect target by category slug
if( isset( $wp_query->query_vars['category_name'] ) && $wp_query->query_vars['category_name'] ) {

	$term = get_term_by( 'slug', $wp_query->query_vars['category_name'], 'category' );
	$link = get_term_link( $term );

	if( is_string( $link ) ) {

		header( "Location: $link", true, 301 );
		exit;

	}

}

$home_url = home_url();
header( "Location: $home_url", true, 301 );
exit;

?>
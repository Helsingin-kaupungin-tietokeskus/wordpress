<?php
/**
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
*/

//$s = get_search_query();
$s = get_query_var( 's' );

$remove = array( '"', '\'', '\\' );
$s = str_replace( $remove, '', $s );

if (ORIGINAL_BLOG_ID == 2) $url = 'haku';
if (ORIGINAL_BLOG_ID == 3) $url = 'search';
if (ORIGINAL_BLOG_ID == 4) $url = 'sok';

header('Location: ' . home_url() . '/' . $url . '/?words=' . $s);
exit; ?>
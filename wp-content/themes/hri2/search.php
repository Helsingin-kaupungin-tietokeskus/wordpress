<?php
$s = get_query_var( 's' );

$remove = array( '"', '\'', '\\' );
$s = str_replace( $remove, '', $s );

if (ORIGINAL_BLOG_ID == 3) $url = 'search';
elseif (ORIGINAL_BLOG_ID == 4) $url = 'sok';
else $url = 'haku';

header('Location: ' . home_url() . '/' . $url . '/?words=' . $s);
exit;

?>
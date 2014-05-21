<?php
/*
Plugin Name: Really simple antispam
Description: Prevents comment spam
Author: Jukka Tuppurainen
Version: 0.2
*/

add_filter( 'comment_form_before', 'rsa_antispam_start' );
add_filter( 'comment_form_after', 'rsa_antispam_end' );

function rsa_antispam_start() {
	ob_start();
}

function rsa_antispam_end() {
	$commentform = ob_get_clean();
	$commentform = preg_replace('/\s\s+/', ' ', $commentform);
	$commentform = trim(str_replace("'", "\'", $commentform));
	$commentform = str_replace("\n", "", $commentform);
	$commentform = str_replace("\t", "", $commentform);
	$commentform = str_replace('"comment"', '"\'+String.fromCharCode(0x63)+\'omment"', $commentform);
	$commentform = str_replace('<form', '\'+String.fromCharCode(074)+String.fromCharCode(102)+\'orm', $commentform);
	echo '<script type="text/javascript">
// <!--
document.write(\'' . $commentform . '\');
// -->
</script>';
	?><noscript><a id="#commentform"></a>Commenting requires JavaScript.</noscript><?php
}
?>
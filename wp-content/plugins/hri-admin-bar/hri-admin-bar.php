<?php

/*
Plugin Name: HRI admin bar
Plugin URI: http://www.hri.fi/
Description: Inserts language codes to admin bar
Version: 1.2
Author: Barabra
Author URI: http://www.barabra.fi/
*/

add_action( 'init', function() {
	wp_enqueue_script( 'jquery' );
});

// Admin bar
add_action( is_admin() ? 'admin_head' : 'wp_head', function() {
	if ( is_user_logged_in() ) {
	?>
<style type="text/css">.hri_identifier{color:#999 !important;text-transform:uppercase;padding-left:10px !important;text-shadow:none !important;}</style>
<script type="text/javascript">
// <!--
jQuery(function($) {
<?php // Lisätään blogin nimeen /xx admin-barissa ?>
	$('#wp-admin-bar-my-sites-list>li>a').each(function(){

		var a=$(this).attr('href').substr(<?php echo strlen( 'http://' . $_SERVER['SERVER_NAME'] )+1; ?>).replace(/(\/*)wp-admin\//,'');
		if(a!='visualisointiblogi'){$(this).append('<span class="hri_identifier">/'+a+'</span>')};

	});
});
// -->
</script><?php
	}
});

// my-sites.php
if ( is_admin() ) add_action( 'admin_head', function() {
	?>	
<script type="text/javascript">
// <!--
jQuery(function() {
<?php // Lisätään /xx/ blogin nimen perään my-sites.php:ssa ?>
	jQuery('.my-sites-php #myblogs h3').each(function(){
		var a=jQuery(this).next('p').children('a').attr('href').substr(<?php echo strlen( 'http://' . $_SERVER['SERVER_NAME'] ); ?>)
		if(a!='/visualisointiblogi'){jQuery(this).append('<span class="hri_identifier">'+a+'</span>')};
	});
});
// -->
</script>
<?php
});

?>
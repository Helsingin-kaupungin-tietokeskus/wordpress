<?php
/**
 * Template name: Activity Stream
 */

add_action( 'wp_head', function() {

?>
<style type="text/css">

#hri_recent_checkboxes {border-bottom:1px solid #e5e5e5;position:relative;margin-bottom:40px;padding-bottom:20px}
#hri_recent_checkboxes .cb{background: url('<?php bloginfo('template_url'); ?>/img/hri_checkbox.png') no-repeat 0 -23px;padding:1px 5px 1px 16px;width:94px;white-space:nowrap;display:block;float:left;cursor:pointer}
#hri_recent_checkboxes .cba{background-position:0 7px;}
#hri_recent_update{width:32px;position:absolute;bottom:-10px;right:0;text-align:right;background:#fff url('<?php bloginfo('template_url'); ?>/img/refresh.png') no-repeat 5px 0;color:#33a4db;cursor:pointer;height:22px;line-height:22px;font-weight:bold}
#hri_recent_update:hover{background-position:5px -24px;color:#6fbf69}
</style><?php

} );

get_header();

restore_current_blog();

?>

<div class="column col-wide">

	<h1><?php the_title(); ?></h1>

	<div id="hri_activity">
	<?php if ( function_exists('hri_recent_activity') ) { hri_recent_activity(); } ?>
	</div>
</div>

<?php

get_sidebar();
get_footer(); ?>
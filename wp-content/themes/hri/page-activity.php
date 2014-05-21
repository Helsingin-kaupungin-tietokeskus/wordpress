<?php
/**
 * Template name: Activity Stream
 */

if ( function_exists('hri_recent_activity') ) wp_enqueue_style( 'hri-activity', home_url('/') . 'wp-content/plugins/hri-activity/hri-activity.css' );

get_header();

restore_current_blog();

?>
	<div id="container">
		<div id="content" role="main">

			<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</div>

			<div id="hri_activity">
<?php if ( function_exists('hri_recent_activity') ) { hri_recent_activity(); } ?>
			</div>
		</div><!-- #content -->
	</div><!-- #container -->
<?php

get_sidebar();
get_footer(); ?>
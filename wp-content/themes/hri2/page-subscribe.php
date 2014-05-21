<?php

/**
 * Template name: Sähköpostilista
 */

get_header();

get_sidebar( 'page' );

?><div class="column col-wide"><?php

if ( have_posts() ) {

	while ( have_posts() ) {

		the_post();

		global $post;

		?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1><?php the_title(); ?></h1>

			<div class="content clearfix"><?php the_content(); ?></div>


		</article><?php

	}
}

?></div><?php

get_footer();

?>
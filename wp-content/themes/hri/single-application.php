<?php
/**
 * The Template for displaying all single applications.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

	<div id="left-column">
	
		<a href="<?php
		if (ORIGINAL_BLOG_ID == 2) echo home_url() . '/fi/sovellukset/';
		if (ORIGINAL_BLOG_ID == 3) echo home_url() . '/en/applications/';
		if (ORIGINAL_BLOG_ID == 4) echo home_url() . '/se/';
		?>" class="blocklink"><?php _e('All applications','twentyten'); ?></a>
	
	</div>

		<div id="container" class="middle-column">
			<div id="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
					<h1 class="entry-title"><?php the_title(); ?></h1>

<?php
					if (has_post_thumbnail()) {

						$f = (int) get_post_meta( $post->ID, '_thumbnail_id', true );


	?><a href="<?php
						$org = wp_get_attachment_image_src( $f, 'large' );
						echo $org[0];

						?>"><?php the_post_thumbnail(); ?></a><?php

				} else $f = false; ?>

					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->

					<?php		 $images = new WP_Query(array(

					'post_type' => 'attachment',
					'post_parent' => $post->ID,
					'post_status' => 'inherit',
					'posts_per_page' => -1

				));

					if ($images->have_posts()) {
						while ($images->have_posts()) {

							$images->the_post();
							if ( $post->ID !== $f && strpos($post->post_mime_type, 'image') === 0) {

								$org = wp_get_attachment_image_src( $post->ID, 'large' );
								$img = wp_get_attachment_image_src( $post->ID, 'post-thumbnail' );

								?><a href="<?php echo $org[0]; ?>"><img src="<?php echo $img[0]; ?>" alt="" /></a><?php

							}
						}
					}

					wp_reset_postdata();

					?>

					<div class="entry-utility">
					<?php
						$tags_list = get_the_term_list_hri( '', ' ' );
						if ( $tags_list ):
					?>
						<span class="tag-links <?php echo $locale; ?>">
							<?php echo '<span class="entry-utility-prep entry-utility-prep-tag-links">' . __('Tags', 'twentyten') . '</span>' . $tags_list; ?>
						</span>
						<div class="clear"></div>
						<?php endif; ?>
					
					<span class="comments-link"><?php if ($post->comment_status == 'open') {
						echo '<span class="entry-utility-prep-comments-links">' . __( 'Comments', 'twentyten' ) . '</span>';

						ob_start();
						comments_popup_link( __( 'Comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) );
						$out = ob_get_clean();

						echo hri_link( $out, HRI_LANG, 'application' );

					}
					?></span>
					<div class="clear"></div>
					
						<?php edit_post_link( __( 'Edit', 'twentyten' ), '<div class="edit-link">', '</div>' ); ?>
					</div><!-- .entry-utility -->

					<div class="entry-meta"><?php hri_time_since( $post->post_date ); ?></div>
					
					<?php
					
					$app_URL = get_post_meta( $post->ID, 'app_URL', true );
					$app_author = get_post_meta( $post->ID, 'app_author', true );
					$app_author_URL = get_post_meta( $post->ID, 'app_author_URL', true );
					
					if( $app_URL || $app_author || $app_author_URL ) {

					?><h2><?php _e('Information','twentyten'); ?></h2>
						<table class="ckan thright"><?php

						if ( $app_URL ) echo '<tr><th>' . __('Application\'s URL','twentyten') . '</th><td>' . hri_make_short_link($app_URL) . '</td></tr>';
						if ( $app_author ) echo '<tr><th>' . __('Author','twentyten') . '</th><td>' . $app_author . '</td></tr>';
						if ( $app_author_URL ) echo '<tr><th>' . __('Author\'s URL','twentyten') . '</th><td>' . hri_make_short_link($app_author_URL) . '</td></tr>';
					
						?></table><?php

					}

					$links = get_post_meta( $post->ID, '_link_to_data' );
					if ( $links ) {
					
						_e('This application is linked to data', 'twentyten');

						$show_links = array();

						foreach( $links as $link ) { $show_links[$link] = true; }

						foreach( array_keys( $show_links ) as $link ) {

						?><div><?php

							$linked_data = new WP_Query( array(
								'post_type' => 'data',
								'post_status' => 'publish',
								'p' => $link
							)); //'post_type=data&p=' . $link);

							if ( $linked_data->have_posts() ) {

								$linked_data->the_post();

								echo '<a href="' . hri_link( get_permalink( $link ), HRI_LANG, 'data') . '">' . data_title( HRI_LANG, false ) . '</a>';

							}
							wp_reset_postdata();

							?></div><?php

						}
					}

					hri_add_this();

					?>
					
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

<?php endwhile; // end of the loop. ?>

			</div><!-- ncontent -->
		</div><!-- #container -->

<?php get_footer(); ?>
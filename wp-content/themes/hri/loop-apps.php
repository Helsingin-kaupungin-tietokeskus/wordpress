<?php
/**
 * The loop that displays apps.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

global $args;

$content_type = '';
if ($args['posttype'] == 'application') $content_type = 'applications';
if ($args['posttype'] == 'data-request') $content_type = 'data-requests';

function tags_to_search( $tag_string ) {

	if ( ORIGINAL_BLOG_ID == 2 ) $url = str_replace( 'tag/', 'data-haku/?tags=', $tag_string );
	if ( ORIGINAL_BLOG_ID == 3 ) $url = str_replace( 'tag/', 'data-search/?tags=', $tag_string );

	$url = str_replace( '/"', '"', $url );

	return $url;

}

/* Display navigation to next/previous pages when applicable */ ?>
<?php if ( $wp_query->max_num_pages > 1 ) : ?>
	<div id="nav-above" class="navigation">
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
	</div><!-- #nav-above -->
<?php endif; ?>

<?php /* If there are no posts to display, such as an empty archive page */ ?>
<?php if ( ! have_posts() ) : ?>
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title"><?php _e( 'Not Found', 'twentyten' ); ?></h1>
		<div class="entry-content">
			<p><?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'twentyten' ); ?></p>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
<?php endif; ?>
<?php $counter = 0;?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php if($counter == 0) { 
		$counter = 1; 
 	} else {
	?>
	<hr />
	<?php } ?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<h2 class="entry-title"><a href="<?php echo hri_link( get_permalink(), HRI_LANG, $content_type); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h2>

<?php
		if ( has_post_thumbnail() ) {

			?><a href="<?php echo hri_link( get_permalink(), HRI_LANG, $content_type); ?>" title="<?php printf( esc_attr__( 'Permalink to %s', 'twentyten' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_post_thumbnail('hri_square'); ?></a><?php

		}

		if ( is_archive() || is_search() ) : // Only display excerpts for archives and search. ?>
		<div class="entry-summary">
			<?php the_excerpt(); echo hri_link( hri_read_more(), HRI_LANG, $content_type); ?>
		</div><!-- .entry-summary -->
<?php else : ?>
		<div class="entry-content">
			<?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
		</div><!-- .entry-content -->
<?php endif; ?>

		<div class="entry-utility">
			<?php

			if ( count( get_the_category() ) ) : ?>
				<span class="cat-links">
					<?php printf( __( '<span class="%1$s">Categories</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list(' ') ); ?>
				</span>
				<div class="clear"></div>

			<?php endif; ?>
			<?php

				$tags_list = get_the_tag_list( '', ' ' );

				$tags_list = hri_link( $tags_list, HRI_LANG, 'tag' );

				if ( $tags_list ):
			?>
				<span class="tag-links">
					<?php printf( __( '<span class="%1$s">Tags</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', tags_to_search( $tags_list ) ); ?>
				</span>
				<div class="clear"></div>

			<?php endif; ?>
			<span class="comments-link"><?php if ($post->comment_status == 'open') {
				echo '<span class="entry-utility-prep-comments-links">' . __( 'Comments', 'twentyten' ) . '</span>';

				ob_start();
				comments_popup_link( __( 'Comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) );
				$out = ob_get_clean();

				echo hri_link( $out, HRI_LANG, $content_type );

			}
			?></span>
			<div class="clear"></div>

			<?php edit_post_link( __( 'Edit', 'twentyten' ), '<div class="edit-link">', '</div>' ); ?>
		</div><!-- .entry-utility -->

		<div class="entry-meta"><?php hri_time_since( $post->post_date ); ?></div>

	</div><!-- #post-## -->

	<?php comments_template( '', true ); ?>

<?php endwhile; // End the loop. Whew. ?>

<?php /* Display navigation to next/previous pages when applicable */ ?>
<?php if (  $wp_query->max_num_pages > 1 ) : ?>
				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyten' ) ); ?></div>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
				</div><!-- #nav-below -->
<?php endif; ?>

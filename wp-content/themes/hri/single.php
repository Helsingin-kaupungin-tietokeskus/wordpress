<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->

<?php if ( get_the_author_meta('user_nicename') != 'admin' ) : ?>
					<div id="entry-author-info">
					
						<div id="author-avatar">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), apply_filters( 'twentyten_author_bio_avatar_size', 50 ) ); ?>
						</div><!-- #author-avatar -->
						<div id="author-description">
							<!--<span class="article-author"><?php __('Article author','twentyten') ?></span>-->
							<h2><?php printf( esc_attr__( '%s', 'twentyten' ), get_the_author() ); ?></h2>
							
<?php if ( get_the_author_meta( 'position' ) ) : ?><span class="author-detail"><?php echo get_the_author_meta( 'position' ); ?></span><br /><?php endif; ?>
<?php if ( get_the_author_meta( 'organization' ) ) :
	if ( get_the_author_meta( 'organization_home_page' ) ) echo '<span class="author-detail"><a target="_blank" href="' . get_the_author_meta( 'organization_home_page' ) . '">' . get_the_author_meta( 'organization' ) . '</a></span>';
	else echo '<span class="author-detail">' . get_the_author_meta( 'organization' ) . '</span><br />';
endif; ?>
							
							<div id="author-link">
								<a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
									<?php //printf( __( 'View all posts by %s <span class="meta-nav">&rarr;</span>', 'twentyten' ), get_the_author() ); ?>
								</a>
							</div><!-- #author-link	-->
						</div><!-- #author-description -->
					</div><!-- #entry-author-info -->
<?php endif; ?>

					<div class="entry-utility">
					<?php 
					restore_current_blog();
					if ( count( get_the_category() ) ) : ?>
						<span class="cat-links">
							<?php printf( __( '<span class="%1$s">Categories</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list(' ') ); ?>
						</span>
						<div class="clear"></div>
						
					<?php endif; ?>
					<?php
					
                        restore_current_blog();
					
						$tags_list = get_the_tag_list( '', ' ' );
						if ( $tags_list ):
					?>
						<span class="tag-links">
							<?php printf( __( '<span class="%1$s">Tags</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list ); ?>
						</span>
						<div class="clear"></div>
						
					<?php endif; ?>
					<span class="comments-link"><?php if ($post->comment_status == 'open') {
						echo '<span class="entry-utility-prep-comments-links">' . __( 'Comments', 'twentyten' ) . '</span>';
						comments_popup_link( __( 'Comment', 'twentyten' ), __( '1 Comment', 'twentyten' ), __( '% Comments', 'twentyten' ) );
					}	
					?></span>
					<div class="clear"></div>
					
					<?php edit_post_link( __( 'Edit', 'twentyten' ), '<div class="edit-link">', '</div>' ); ?>
				</div><!-- .entry-utility -->

					<div class="entry-meta"><?php hri_time_since( $post->post_date ); ?></div>
					<?php hri_add_this(); ?>
				</div><!-- #post-## -->

				<?php comments_template( '', true ); ?>

<?php endwhile; // end of the loop. ?>

			</div><!-- ncontent -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

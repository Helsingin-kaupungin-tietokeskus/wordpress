<?php
/**
 * The Template for displaying all single data requests.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

global $wpdb;

get_header();

if ( have_posts() ) while ( have_posts() ) : the_post();

restore_current_blog();

?>

		<div id="left-column">
			
			<div class="entry-utility">
			
				<a href="<?php

	if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL . '/fi/datatoiveet/';
	if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL . '/en/data-requests/';
	if (ORIGINAL_BLOG_ID == 4) echo ROOT_URL . '/se/';
	
	 ?>" class="blocklink"><?php _e('Back to data requests','twentyten'); ?></a>
			
				
				<?php
					$tags_list = get_the_term_list_hri( '', ' ' );
					if ( $tags_list ):
				?>
					<span class="tag-links <?php echo $locale; ?>">
						<?php echo '<span class="entry-utility-prep entry-utility-prep-tag-links">' . __('Tags', 'twentyten') . '</span>' . $tags_list; ?>
					</span>
					<div class="clear"></div>
					
				<?php endif; ?>
				
				<div class="clear"></div>
				
				<?php edit_post_link( __( 'Edit', 'twentyten' ), '<div class="edit-link">', '</div>' ); ?>
			</div><!-- .entry-utility -->
		</div>
		
		<div id="container" class="middle-column">
			<div id="content" role="main">

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					
					<h1 class="entry-title"><?php data_title( substr( get_bloginfo('language'), 0, 2 ) ); //the_title(); ?></h1>

					<div class="entry-content">
					    
						<?php

						the_content();

						hri_add_this();

						?>
					</div><!-- .entry-content -->


				</div><!-- #post-## -->	
				<?php comments_template( '/comments.php', true ); ?>

			</div><!-- ncontent -->
		</div><!-- #container -->

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>

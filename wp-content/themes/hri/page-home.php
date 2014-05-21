<?php
/**
 * Template name: Home page
 *
 * @package WordPress
 * @subpackage HRI
 * @since HRI 0.1
 */

get_header(); ?>

<div id="homepagetop">
	<div id="hometopleft">
		<div class="hometopleftbox">
	
	<?php
		global $switched;    
		switch_to_blog(1);
	?>
	
		
		<h2 id="h2top1"><?php _e('Data search','twentyten'); ?></h2><?php
	
	if (ORIGINAL_BLOG_ID == 2) $url = '/fi/data-haku/';
	if (ORIGINAL_BLOG_ID == 3) $url = '/en/data-search/';
	if (ORIGINAL_BLOG_ID == 4) $url = '/se/data-sok/';
			
	?><div class="searchdata"<?php if (ORIGINAL_BLOG_ID == 3 || ORIGINAL_BLOG_ID == 4) echo ' style="width:400px"'; ?>>

			<form method="post" action="<?php echo home_url() . $url; ?>"><input type="text" id="datasearch" class="dsearch <?php echo $locale; ?>" size="30" name="datasearch" placeholder="<?php _e('Search term','twentyten') ?>" /><input type="submit" value="<?php _e('Search','twentyten') ?>" id="datasearchsubmit" name="datasearchsubmit" /></form>
			<a class="readmore" href="<?php echo home_url() . $url; ?>" style="float: left; clear: both;"><?php _e('Advanced search','twentyten') ?></a>
			</div><!-- .searchdata -->
		</div>
	
		<?php if(ORIGINAL_BLOG_ID == 2) { ?>
		<div id="hometoplefttags">
			<h2 id="h2top2"><?php _e('Popular tags','twentyten'); ?></h2><div class="tags_cloud">
			<?php
			
			$args = array(
    'smallest'                  => 13, 
    'largest'                   => 13,
    'unit'                      => 'px', 
    'number'                    => 10,  
    'format'                    => 'flat',
    'separator'                 => ' ',
    'orderby'                   => 'count', 
    'order'                     => 'DESC',
    'exclude'                   => null, 
    'include'                   => null, 
    'topic_count_text_callback' => 'default_topic_count_text',
    'link'                      => 'view', 
    'taxonomy'                  => 'post_tag', 
    'echo'                      => true,
    'data'			=> true // We want to redirect to DATA search
	);
	
	wp_tag_cloud_hri( $args );
			
			?><br class="clear" />
			</div>
		</div>
		<?php } ?>
		
	</div>
	
	<div id="hometopright">
		<h2 id="h2top3"><?php _e('Newest data','twentyten'); ?></h2>
		<?php
		
		$d = new WP_Query('post_type=data&show_posts=1');
		if ( $d->have_posts() ) {
		
			$d->the_post(); ?>
			
		<div id="hometoprightpost">
			<h4 class="bold blue"><a href="<?php echo hri_link( get_permalink( $post->ID ), substr( get_bloginfo('language'),0,2 ), 'data'); ?>"><?php data_title( substr( get_bloginfo('language'),0,2 ) ); ?></a></h4>
			
			<?php

			$notes = notes( false );
			if( strlen($notes) > 125 ) $notes = substr( $notes, 0, strpos( $notes, ' ', 125 )) . ' &hellip;';
			echo $notes;

			?>
			</div>
			
			<?php /*<div style="padding-top:6px;"><div style="color:#b8b8b8" class="floatr"><?php _e('Published','twentyten'); ?> <?php the_date(); ?>
			</div> */ ?>
			
			<?php 
			restore_current_blog();
			
			echo hri_link( hri_read_more(), substr( get_bloginfo('language'),0,2 ), 'data');

			?>
			
			</div><?php
		}
		
		?>
	</div>
</div>

<?php if ( is_active_sidebar( 'big-frontpage-widget-area' ) ) : ?>
	<div class="clear"></div>
	<div id="etusivuyla" class="widget-area">
<?php
restore_current_blog();
?>
		<?php dynamic_sidebar( 'big-frontpage-widget-area' ); ?>
		<br class="clear" />
	</div>
<?php endif; ?>

			<!-- #content -->

	<div id="hri_home_widgets">
<?php
// new home, 9 cells
for ($r = 1; $r <= 3; ++$r) {

	?>
	<div class="hri_home_widget_row" id="row-<?php echo $r; ?>">
<?php

	for ($c = 1; $c <= 3; ++$c) {

		?>
		<div class="hri_home_widget_cell" id="row-<?php echo $r; ?>-cell-<?php echo $c; ?>"><?php dynamic_sidebar("Home page: row $r cell $c"); ?></div>
<?php

	}

	?></div><?php
}
?>

</div>
<br class="clear" />

<?php get_footer(); ?>

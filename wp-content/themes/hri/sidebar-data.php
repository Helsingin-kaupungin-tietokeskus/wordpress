<?php
/**
 * The Sidebar for single data
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>

		<div id="primary" class="widget-area" role="complementary">
			<ul>
				<?php

				$lang = substr( get_bloginfo('language'),0,2 );

				$data_ID = $post->ID;
				$post_name = $post->post_name;

				hri_related_discussions( (array) $data_ID, array(), 5 );

				wp_reset_postdata();

				// -------------------------------------------------------------------------------------------------------------------------------- START A NEW DISCUSSION

				?>
<!--			<li class="widget-container">
				<form class="call_to_action_container_form call_to_action_container_form_add_discussion" action="<?php echo home_url();

		if (ORIGINAL_BLOG_ID == 2) echo '/fi/aloita-uusi-keskustelu/';
		if (ORIGINAL_BLOG_ID == 3) echo '/en/start-a-new-discussion/';
		if (ORIGINAL_BLOG_ID == 4) echo '/se/'; // TODO

				?>" method="post">
					<input type="hidden" name="linked_id" id="linked_id" value="<?php echo $post->ID; ?>" />
					<input class="call_to_action_submit call_to_action_submit_add_discussion" type="submit" name="linked_submit" id="linked_submit" value="<?php _e('Start a new discussion from this data','twentyten'); ?>" />
				</form>
			</li>-->
			
			<li class="widget-container">
				<?php
				$startNewUrl = '';
				if (ORIGINAL_BLOG_ID == 2) $startNewUrl = home_url() . '/fi/aloita-uusi-keskustelu/';
				if (ORIGINAL_BLOG_ID == 3) $startNewUrl = home_url() . '/en/start-a-new-discussion/';
				?>
				<div class="call_to_action_container_data">
					<div class="call_to_action_container_data_row">
						<div class="icon-row">
							<div class="icon icon-add-discussion">&nbsp;</div>
						</div>
						<a class="no-hover" href="<?php echo $startNewUrl; ?>?linked_id=<?php echo $post->ID; ?>"><h3 class="widget-title-link title-add-discussion"><?php _e('Start a new discussion from this data','twentyten'); ?></h3></a>
					</div>
				</div>
			</li>

			<li class="widget-container widget_apps">
				<?php

				// -------------------------------------------------------------------------------------------------------------------------------- RELATED APPS

				$related_apps = new WP_Query('post_type=application&posts_per_page=-1&meta_key=_link_to_data&meta_value=' . $data_ID);

				if ( $related_apps->have_posts() ) {

					echo '<h3 class="widget-title">' . __('Applications', 'twentyten') . '</h3>';

					while ( $related_apps->have_posts() ) {

						$related_apps->the_post(); ?>

						<div class="hri_app">
							<h4><a href="<?php echo hri_link( get_permalink(), $lang, 'applications' ); ?>"><?php the_title(); ?></a></h4>
							<div class="postdate"><?php hri_time_since($post->post_date); ?></div>
							<?php

							the_excerpt();

							echo hri_link( hri_read_more(), $lang, 'applications' );

							?>
						</div>

				<?php }

/*					if ( $related_apps->found_posts > 3 ) { // found_posts == 0 when using posts_per_page = -1

						?><a class="blocklink" href="<?php echo home_url();

						// TODO: linkki vain tämän datan sovelluksiin. Tarvitaan rewrite & query muutos
			
	if (ORIGINAL_BLOG_ID == 2) echo '/fi/sovellukset/';
	if (ORIGINAL_BLOG_ID == 3) echo '/en/applications/';

				?>"><?php _e('See all applications','twentyten'); ?></a><?php

					} */

				}

				wp_reset_postdata();

				// -------------------------------------------------------------------------------------------------------------------------------- SUBMIT A NEW APP

				?>
			
<!--				<form action="<?php echo home_url();
			
	if (ORIGINAL_BLOG_ID == 2) echo '/fi/ilmoita-uusi-sovellus/';
	if (ORIGINAL_BLOG_ID == 3) echo '/en/new-application-submission/';
	if (ORIGINAL_BLOG_ID == 4) echo '/se/'; // TODO
			
				?>" method="post">
					<input type="hidden" name="linked_id" id="linked_app_id" value="<?php echo $post->ID; ?>" />
					<input type="submit" name="linked_submit" id="linked_app_submit" value="<?php _e('New application submission','twentyten'); ?>" />
				</form>-->
				<?php
				$startNewUrl = '';
				if (ORIGINAL_BLOG_ID == 2) $startNewUrl = home_url() . '/fi/ilmoita-uusi-sovellus/';
				if (ORIGINAL_BLOG_ID == 3) $startNewUrl = home_url() . '/en/new-application-submission/';
				?>
				<div class="call_to_action_container_data">
					<div class="call_to_action_container_data_row">
						<div class="icon-row">
							<div class="icon icon-add-application">&nbsp;</div>
						</div>
						<a class="no-hover" href="<?php echo $startNewUrl; ?>?linked_id=<?php echo $post->ID; ?>"><h3 class="widget-title-link title-add-application"><?php _e('New application submission from this data','twentyten'); ?></h3></a>
					</div>
				</div>
			</li>
			</ul>
		</div><!-- #primary .widget-area -->
	

<?php
/**
 * The Template for displaying all single discussions.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header();

if ( have_posts() ) {

	the_post();

	$this_disccusion_id = get_the_ID();

?>

	<div id="left-column"><?php
	if ( ORIGINAL_BLOG_ID == 2 ) echo '<a class="blocklink" href="' . ROOT_URL . '/fi/keskustelut/">Kaikki keskustelut</a>';
	// <a class="blocklink newblocklink" href="' . ROOT_URL . '/fi/aloita-uusi-keskustelu/">+ Aloita uusi keskustelu</a>
	
	if ( ORIGINAL_BLOG_ID == 3 ) echo '<a class="blocklink" href="' . ROOT_URL . '/en/discussions/">All discussions</a>';
	// <a class="blocklink newblocklink" href="' . ROOT_URL . '/en/start-a-new-discussion/">+ Start a new discussion</a>

	$taglist = get_the_tag_list(' ');
	if( $taglist ) :
	?>
		<span class="tag-links">
			<?php printf( __( '<span class="%1$s">Tags</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-tag-links', $taglist ); ?>
		</span>
	<?php endif; ?>
		<?php hri_add_this(); ?>
	</div>

		<div id="container" class="middle-column">
			<div id="content" role="main">

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="comment-body">
						<div class="entry-content">
							<?php the_content(); ?>
						</div>
						<div class="clear"></div><div class="comment-nuoli"></div>
					</div>

<?php

$custom = get_post_custom();

if( isset( $custom['user_name'] ) ) {

	$name = $custom['user_name'][0];
	$email = $custom['user_email'][0];

} else  {

	$user = get_userdata( $post->post_author );
	$name = $user->display_name;
	$email = $user->user_email;

}

?>
					<div class="comment-meta commentmetadata">
						<?php echo get_avatar( $email, 30 ); ?>
						<span class="name">
							<cite class="fn"><?php echo $name; ?></cite>
						</span>
						<br />
						<span class="timestamp"><?php hri_time_since( $post->post_date ); ?></span>
					</div>

				</div><!-- #post-## -->

				<?php comments_template( '/comments-discussion.php', true ); ?>

			</div><!-- ncontent -->
		</div><!-- #container -->

<?php

	$links = get_post_meta( $post->ID, '_link_to_data' );
	if ( $links ) {

?>
<div id="primary" class="widget-area">
	<ul class="xoxo">
	<?php
		$linked_data = new WP_Query(array(
			'post_type' => 'data',
			'post__in' => $links
		));

		if ( $linked_data->have_posts() ) {

		?><li class="widget-container">
			<div class="widget widget_data"><?php

			echo '<h3 class="widget-title">', __('Related data', 'twentyten'), '</h3>';

			while ( $linked_data->have_posts() ) {
				$linked_data->the_post();

				?><div class="related-data"><?php hri_rating(); ?>
				<a class="post-title" href="<?php echo hri_link( get_permalink(), HRI_LANG, 'data'); ?>"><?php the_title(); ?></a>
				<?php

				echo nl2br( n_words( notes(false,false), 10 ) );

				echo hri_link( hri_read_more(), HRI_LANG, 'data');

				if( ORIGINAL_BLOG_ID == 2 ) { ?>

				<form action="<?php echo ROOT_URL, '/fi/aloita-uusi-keskustelu/'; ?>" method="post">
					<input type="hidden" name="linked_id" value="<?php the_ID(); ?>" />
					<input type="submit" class="blocklink small" name="linked_submit" value="+ <?php _e('Start a new discussion from this data', 'twentyten'); ?>" />
				</form>
				<?php

				}

				?>
				</div><?php

			}
			?>
			</div>
		</li><?php
		}

		hri_related_discussions( $links, (array) $this_disccusion_id, 1 );

		$related_apps = new WP_Query(array(
			'post_type' => 'application',
			'post__not_in' => array($this_disccusion_id),
			'post_status' => 'publish',
			'posts_per_page' => 5,
			'meta_query' => array(
				array(
					'key' => '_link_to_data',
					'compare' => 'IN',
					'value' => $links
				)
			)
		));

		if($related_apps->have_posts()) {

		?><li class="widget-container">
			<div class="widget widget_apps"><?php

			echo '<h3 class="widget-title">', __('Related applications', 'twentyten'), '</h3>';

			while($related_apps->have_posts()) {
				$related_apps->the_post();

				?><a class="post-title" href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application'); ?>"><?php the_title(); ?></a><?php

				echo hri_link( hri_read_more(), HRI_LANG, 'application');

			}

			// todo: hae kaikki sovellukset
		?>
			</div>
		</li><?php

		}

		wp_reset_postdata();

?>

	</ul>
</div>
<?php
	}
}


get_footer(); ?>

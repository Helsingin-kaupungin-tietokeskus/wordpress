<?php

restore_current_blog();

get_header();

switch_to_blog(1);

if ( have_posts() ) {

	the_post();

	$this_disccusion_id = get_the_ID();

?>
<script type="text/javascript">
// <!--
jQuery('#main-nav').find('.discussion-parent').addClass('current-page-ancestor');
// -->
</script>
<div class="column col-narrow">
	<nav>
		<ul>
			<li>
				<div class="icon icon-back-arrow"></div>
				<a href="<?php

				echo ROOT_URL;
				if( ORIGINAL_BLOG_ID == 3 ) echo '/en/discussions/';
				else echo '/fi/keskustelut/';

				?>"><?php

				if( strpos( $_SERVER['HTTP_REFERER'], __( 'keskustelut', 'hri' ) ) !== false ) _e( 'Takaisin keskusteluihin', 'hri' );
				else _e( 'Kaikki keskustelut', 'hri' );

				?>
				</a>
			</li>
		</ul>
	</nav>

	<a class="with-medium-circle" href="<?php echo NEW_DISCUSSION_URL; ?>">
		<div class="circle medium green icon-discussion"></div>
		<?php _e( 'Uusi keskustelu', 'hri' ); ?>
	</a>

	<div class="clear clearfix row">&nbsp;</div>
<?php

	$links = get_post_meta( $post->ID, '_link_to_data' );
	if ( $links ) {

		$linked_data = new WP_Query(array(
			'post_type' => 'data',
			'posts_per_page' => -1,
			'post_status' => 'publish',
			'post__in' => $links
		));

		if ( $linked_data->have_posts() ) {

			?>
			<div class="infobox bluebox">

				<div class="heading">
					<div class="infobox-icon infobox-icon-data"></div>
					<h3><?php _e( 'Liittyvää dataa', 'hri' ); ?></h3>
				</div>

				<ul>
				<?php

				while ( $linked_data->have_posts() ) {

					$linked_data->the_post();

					?>
					<li>
						<h5><?php data_title(); ?></h5>

						<?php echo nl2br( n_words( notes(false,false), 10 ) ); ?>

						<div class="arrowdiv clearfix">
							<a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'dataset' ); ?>" class="readmore arrow"><?php echo __('Lue lisää', 'hri'); ?></a>
						</div>
					</li>
					<?php
				}

				?>
				</ul>

			</div>
			<?php
		}

		wp_reset_postdata();

	}

	$related_apps = new WP_Query(array(
		'post_type' => 'application',
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

		?>
		<div class="infobox bluebox">
			<div class="heading">
				<div class="infobox-icon infobox-icon-app"></div>
				<h3><?php _e( 'Liittyvät sovellukset', 'hri' ); ?></h3>
			</div>
			<ul class="link-list"><?php

			while ( $related_apps->have_posts() ) {

				$related_apps->the_post();

				?>
				
				<li>
					<a class="block clearfix" href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>">
						<?php the_post_thumbnail( 'tiny-square' ); ?>
						<span class="arrow"><?php the_title(); ?></span>
					</a>
				</li>

				<?php
			}

			?>
			</ul>
		</div><?php

		wp_reset_postdata();

		?></div><?php

	}

	?>
</div>

<div class="column col-wide">
	<?php hri_add_this(); ?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<h1><?php the_title(); ?></h1>

		<div class="row">
		<?php hri_link_to_last_comment(); ?>
		</div>

		<div class="row comment-body">
			<?php the_content(); ?>
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
			<span class="name"><?php echo $name; ?></span>
			<br />
			<span class="timestamp"><?php hri_time_since( $post->post_date_gmt ); ?></span>
		</div>

	</div>

	<?php comments_template( '/comments-discussion.php', true ); ?>
</div>

<?php

}

get_footer(); ?>
<?php

restore_current_blog();

get_header();

switch_to_blog(1);

?>
<script type="text/javascript">
// <!--
jQuery('#main-nav .application-parent').addClass('current-page-ancestor');
$(document).ready(function($){

	$( 'article a.app-mini-thumb').click(function(){

		if( !$(this).hasClass('current-app-mini-thumb') ) {

			$('.current-app-mini-thumb').removeClass( 'current-app-mini-thumb' );

			var src = $(this).addClass('current-app-mini-thumb').attr('href');

			$('article .wp-post-image').attr( 'src', src);

		}

		return false;

	});

});
// -->
</script>
<div class="column col-wide"><?php

if ( have_posts() ) {

	while ( have_posts() ) {

		the_post();

		global $post;

		?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1><?php the_title(); ?></h1>

			<?php if( has_post_thumbnail() ) { ?><div class="app-thumb left"><?php

			$f = (int) get_post_meta( $post->ID, '_thumbnail_id', true );

			the_post_thumbnail( 'app3' );

			function where_mime_type_image( $where ) {

				global $wpdb;
				$where .= " AND $wpdb->posts.post_mime_type LIKE 'image/%'";

				return $where;

			}

			add_filter( 'posts_where', 'where_mime_type_image' );

			$images = new WP_Query(array(

				'post_type' => 'attachment',
				'post_parent' => $post->ID,
				'post_status' => 'inherit',
				'posts_per_page' => -1

			));

			remove_filter( 'posts_where', 'where_mime_type_image' );

			if ($images->have_posts() && $images->post_count > 1 ) {
				while ($images->have_posts()) {

					$images->the_post();

					$org = wp_get_attachment_image_src( $post->ID, 'app3' );
					$img = wp_get_attachment_image_src( $post->ID, 'small-app' );

					?><a class="<?php if( $post->ID == $f ) echo 'current-app-mini-thumb '; ?>app-mini-thumb" href="<?php echo $org[0]; ?>"><img src="<?php echo $img[0]; ?>" alt="" /></a><?php

				}
			}

			wp_reset_postdata();

			?></div><?php } ?>
			<div class="app-meta left clearfix">
			<?php

				$cats = wp_get_object_terms( $post->ID, 'hri_appcats' );

				if( $cats ) {

					?><h6><?php _e( 'Kategoriat', 'hri' ); ?></h6><div class="clearfix"><?php

					switch_to_blog(1);

					foreach( $cats as $cat ) {

						?><a class="term" href="<?php echo APP_SEARCH_URL, '?application_category=', $cat->term_id; ?>"><?php echo $cat->name; ?></a><?php

					}

					restore_current_blog();

					?></div><?php

				}
				
				$tags = wp_get_object_terms( $post->ID, 'post_tag' );

				if( $tags ) {

					?><h6 style="margin-top:10px"><?php _e( 'Avainsanat', 'hri' ); ?></h6><div class="clearfix"><?php

					switch_to_blog(1);

					foreach( $tags as $tag ) {

						?><a class="term" href="<?php echo APP_SEARCH_URL, '?application_tag=', $tag->term_id; ?>"><?php echo $tag->name; ?></a><?php

					}

					restore_current_blog();

					?></div><?php

				}

				$data = get_post_meta( $post->ID, '_link_to_data' );

				if( !empty( $data ) ) {

					?><h6 class="underline">Data:</h6><?php

					foreach( $data as $d ) {

						?><a class="block" href="<?php echo hri_link( get_permalink( $d ), HRI_LANG, 'data' ); ?>"><?php echo get_the_title( $d ); ?></a><?php

					}

				}

				$app_url = get_post_meta( $post->ID, 'app_URL', true );
				if( $app_url ) {

					?><h6 class="underline"><?php _e( 'Sovelluksen www-osoite', 'hri' ); ?>:</h6><a class="block" href="<?php echo $app_url; ?>"><?php echo $app_url; ?></a><?php

				}

				$author = get_post_meta( $post->ID, 'app_author', true );
				if( $author ) {

					$app_count = app_count_for_author( $author );

					?><h6 class="underline"><?php _e( 'Tekijä', 'hri' ); ?>:</h6><?php

					if( $app_count > 1 ) {

						?><a class="block" href="<?php echo APP_SEARCH_URL, '?application_author=', urlencode( $author ); ?>"><?php

					}

					echo esc_html( $author );

					if( $app_count > 1 ) {

						?></a><?php

					}

				}

				$author_url = get_post_meta( $post->ID, 'app_author_URL', true );
				if( $author_url ) {

					?><h6 class="underline"><?php _e( 'Tekijän www-osoite', 'hri' ); ?>:</h6><a class="block" href="<?php echo $author_url; ?>"><?php echo $author_url; ?></a><?php

				}

			?>
			</div>
			<div class="content row"><?php the_content(); ?></div>

			<?php hri_add_this(true); ?>

		</article><?php

		comments_template();

	}
}

?></div>

<aside class="column col-narrow">
	<form action="<?php echo APP_SEARCH_URL ?>" method="get" class="hri-search small-search">
		<input id="search" name="search" class="hri-input" type="text" placeholder="<?php _e( 'Hae sovelluksia', 'hri' ); ?>" />
		<input class="hri-submit" type="submit" />
	</form>
	<?php

	if( isset($author) && $author ) {

		$related_apps = new WP_Query(array(
			'post_type' => 'application',
			'post_status' => 'publish',
			'posts_per_page' => 4,
			'post__not_in' => array($post->ID),
			'meta_query' => array(
				array(
					'key' => 'app_author',
					'value' => $author
				)
			)
		));

		if( $related_apps->have_posts() ) {

			?><div class="infobox bluebox clearfix">

			<div class="heading multiline">
				<h3><?php printf( __( 'Lisää sovelluksia tekijältä %s', 'hri' ), $author); ?></h3>
			</div>
			<ul class="link-list"><?php

			while( $related_apps->have_posts() ) {

				$related_apps->the_post();

				?><li><a class="block clearfix" href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php the_post_thumbnail( 'tiny-square' ); ?>
					<span class="arrow"><?php the_title(); ?></span></a></li><?php

			}

			?></ul></div><?php

			wp_reset_postdata();

		}

		unset( $related_apps );

	}
	
	?>
</aside>
<?php

get_footer();

?>
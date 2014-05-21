<?php

/*
 * Template name: Sovella dataa
 */

get_header();

?>
<script type="text/javascript">
// <!--
$(document).ready(function($) {

	$('#data-steps li').click(function(){

		var id = $(this).attr('id').substr(10);

		$('#data-steps li').removeClass( 'current-step' );
		$(this).addClass( 'current-step' );

		$('.content-columns>div').hide();
		$('#content-' + id).show();

		$('#step-arrow').animate({ left : ( id - 1) * 152 + 38 },250);

	});

	var doing_ajax_app = false;
	var url = "<?php echo ROOT_URL;

	if (ORIGINAL_BLOG_ID == 2) echo '/fi';
	if (ORIGINAL_BLOG_ID == 3) echo '/en';
	if (ORIGINAL_BLOG_ID == 4) echo '/se';

	?>/wp-admin/admin-ajax.php";

	$('#app-ajax-btn').click(function(){

		if( doing_ajax_app == false ) {

			doing_ajax_app = true;

			var id = $('#app-highlight-c div').attr('id').substr(5);

			$('#app-highlight-c .application').fadeOut(150,function(){

				$(this).remove();

				$.ajax({
					type: 'POST',
					url: url,
					data: {
						action	: "hri_app",
						not		: id
					},
					dataType: 'html'
				}).done(function(data){

					$('#app-highlight-c').html(data).find('.post-highlight').fadeIn(150, function(){

						$('.infobox-row').each(function(){

							var max_h=0;

							$(this).find('.infobox').css({ height : 'auto' }).each(function(){
								if( $(this).height() > max_h ) max_h = $(this).height();
							});

							$(this).find('.infobox').height( max_h );

						});

					});
					doing_ajax_app = false;

				});

			});

		}

	});

});
// -->
</script>
<div class="column full"><?php

if ( have_posts() ) {

	while ( have_posts() ) {

		the_post();

		?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1><?php the_title(); ?></h1>

			<div class="content"><?php the_content(); ?></div>

			<div id="data-steps">
				<ol class="clearfix">
					<li class="current-step" id="data-step-1"><div class="active-step" id="data-step-1-active"></div><div class="number">1</div>
						<p><?php _e( 'Löydä dataa', 'hri' ); ?></p>
					</li>
					<li id="data-step-2"><div class="active-step" id="data-step-2-active"></div><div class="number">2</div>
						<p><?php _e( 'Keksi tai löydä sovellusidea', 'hri' ); ?></p>
					</li>
					<li id="data-step-3"><div class="active-step" id="data-step-3-active"></div><div class="number">3</div>
						<p><?php _e( 'Luo sovellus', 'hri' ); ?></p>
					</li>
					<li id="data-step-4"><div class="active-step" id="data-step-4-active"></div><div class="number">4</div>
						<p><?php _e( 'Julkaise sovellus verkossa', 'hri' ); ?></p>
					</li>
					<li class="last-step" id="data-step-5"><div class="active-step" id="data-step-5-active"></div><div class="number">5</div>
						<p><?php _e( 'Käyttäjät antavat palautetta ja keskustelevat', 'hri' ); ?></p>
					</li>
				</ol>
				<div id="step-arrow"></div>
			</div>

			<div class="clear clearfix content-columns">

				<div class="clearfix" id="content-1"><div class="content content-steps"><?php the_hri_field( 'step1' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step1right' ); ?></div></div>
				<div class="clearfix" id="content-2"><div class="content content-steps"><?php the_hri_field( 'step2' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step2right' ); ?></div></div>
				<div class="clearfix" id="content-3"><div class="content content-steps"><?php the_hri_field( 'step3' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step3right' ); ?></div></div>
				<div class="clearfix" id="content-4"><div class="content content-steps"><?php the_hri_field( 'step4' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step4right' ); ?></div></div>
				<div class="clearfix" id="content-5"><div class="content content-steps"><?php the_hri_field( 'step5' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step5right' ); ?></div></div>
				
			</div>

		</article><?php

	}

}

?>
	<div class="infobox-row">
		<div class="infobox infobox-3 clearfix left">
			<div class="heading rel"><div class="infobox-icon infobox-icon-app"></div><h3><a href="<?php echo APP_SEARCH_URL; ?>"><?php _e( 'Sovellukset', 'hri' ); ?></a></h3><a id="app-ajax-btn" title="<?php _e( 'Satunnainen sovellus', 'hri' ); ?>"></a></div>

			<?php

			switch_to_blog(1);

			$app = new WP_Query(array(
				'post_type' => 'application',
				'post_status' => 'publish',
				'posts_per_page' => 1
			)); // todo: "jos on uudempi kuin 1kk. Muussa tapauksessa satunnainen sovellus."

			if( $app->have_posts() ) {

				global $post;

				$app->the_post();

				?>

				<div id="app-highlight-c">
					<div id="post-<?php the_ID(); ?>" <?php post_class( 'clear post-highlight' ); ?>><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php

					hri_thumbnail();

					?></a>
						<div class="highlight-excerpt"><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php the_title(); ?></a>
							<p><?php echo get_post_meta( $post->ID, 'short_text', true ); ?></p>
						</div>
						<div class="clear"></div>
					</div>
				</div><?php

				wp_reset_postdata();

			}

			restore_current_blog();

			?>
			<p><a class="arrow" href="<?php echo APP_SEARCH_URL; ?>"><?php _e( 'Lisää sovelluksia', 'hri' ); ?></a></p>
		</div>

		<div class="infobox infobox-3 clearfix left">
			<?php

			$term_tyokalut = get_term_by( 'slug', 'tyokalut', 'category' );

			if( $term_tyokalut ) $term_tyokalut_link = get_term_link( $term_tyokalut, 'category' );
			if( !isset( $term_tyokalut_link ) || is_wp_error( $term_tyokalut_link ) ) $term_tyokalut_link = '';

			$term_vb = get_term_by( 'slug', 'visualisointiblogi', 'category' );

			if( $term_vb ) $term_vb_link = get_term_link( $term_vb, 'category' );
			if( !isset( $term_vb_link ) || is_wp_error( $term_vb_link ) ) $term_vb_link = '';

			?><div class="heading rel"><div class="infobox-icon infobox-icon-tools"></div><h3><a href="<?php echo $term_tyokalut_link; ?>"><?php _e( 'Työkalut', 'hri' ); ?></a></h3></div><?php
			
			$vbpost = new WP_Query(array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'category__in' => array( $term_vb->term_id )
			));

			$latest_vb_post_ID = $vbpost->posts[0]->ID;
			
			$article = new WP_Query(array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'post__not_in' => array( $latest_vb_post_ID ),
				'category__in' => array( $term_tyokalut->term_id )
			));
			
			if( $article->have_posts() ) {

				$article->the_post();

				?><a class="bold" href="<?php the_permalink(); ?>"><?php hri_thumbnail(); the_title(); ?></a><?php

//				$content = apply_filters('the_content', get_the_content());
//
//				$content_end = strpos( $content, '</p>' ) + 4;
//				if( $content_end > 0 ){
//					$content = substr( $content, 0, $content_end );
//				}

				$content = n_chars( strip_tags( get_the_excerpt() ), 230, 250 );

				?><p><?php echo $content; ?> <a class="bold" href="<?php the_permalink(); ?>"><?php _e( 'Lue lisää', 'hri' ); ?></a></p><p>
                    <a class="arrow" href="<?php echo $term_tyokalut_link; ?>"><?php _e( 'Lisää työkaluja', 'hri' ); ?></a></p><?php

				wp_reset_postdata();

			}

			unset( $article );
			
			?>
		</div>

		<div class="infobox infobox-3 clearfix left">
			<div class="heading rel"><a href="<?php echo $term_vb_link; ?>"><img src="<?php bloginfo('template_url'); ?>/img/heading-vb.png" alt="<?php _e( 'Visualisointiblogi', 'hri' ); ?>" style="position:relative;top:4px;left:10px;" /></a></div>
			<?php

			$vb = new WP_Query(array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'p' => $latest_vb_post_ID
			));

			if( $vb->have_posts() ) {

				$vb->the_post();

				?><a class="bold" href="<?php the_permalink(); ?>"><?php hri_thumbnail(); the_title(); ?></a><?php

				$content = apply_filters('the_content', get_the_content());

				$content_end = strpos( $content, '</p>' ) + 4;
				if( $content_end > 0 ){
					$content = substr( $content, 0, $content_end );
				}

				$content = n_chars( strip_tags( $content ), 230, 250 );

				?><p><?php echo $content; ?> <a class="bold" href="<?php the_permalink(); ?>"><?php _e( 'Lue lisää', 'hri' ); ?></a></p><?php

				wp_reset_postdata();

			}

			unset( $vb );

			?>
		</div>
	</div>
</div>
<?php

get_footer();

?>
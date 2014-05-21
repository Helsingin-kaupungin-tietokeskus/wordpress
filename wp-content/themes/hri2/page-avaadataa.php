<?php

/*
 * Template name: Avaa dataa
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
						<p><?php _e( 'Löydetään dataa', 'hri' ); ?></p>
					</li>
					<li id="data-step-2"><div class="active-step" id="data-step-2-active"></div><div class="number">2</div>
						<p><?php _e( 'Varmistetaan julkaisukelpoisuus', 'hri' ); ?></p>
					</li>
					<li id="data-step-3"><div class="active-step" id="data-step-3-active"></div><div class="number">3</div>
						<p><?php _e( 'Valmistellaan avattavaksi', 'hri' ); ?></p>
					</li>
					<li class="last-step" id="data-step-4"><div class="active-step" id="data-step-4-active"></div><div class="number">4</div>
						<p><?php _e( 'Julkaistaan HRI:ssä, keskustellaan ja sovelletaan', 'hri' ); ?></p>
					</li>
				</ol>
				<div id="step-arrow"></div>
			</div>

			<div class="clear clearfix content-columns">

				<div class="clearfix" id="content-1"><div class="content content-steps"><?php the_hri_field( 'step1' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step1right' ); ?></div></div>
				<div class="clearfix" id="content-2"><div class="content content-steps"><?php the_hri_field( 'step2' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step2right' ); ?></div></div>
				<div class="clearfix" id="content-3"><div class="content content-steps"><?php the_hri_field( 'step3' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step3right' ); ?></div></div>
				<div class="clearfix" id="content-4"><div class="content content-steps"><?php the_hri_field( 'step4' ); ?></div><div class="step-col-narrow"><?php the_hri_field( 'step4right' ); ?></div></div>

			</div>

		</article><?php

	}

}

?>
	<div class="infobox-row">
		<div class="infobox infobox-3 clearfix left"><?php

			$noste = new WP_Query(array(
				'post_type' => 'any',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'p' => $id
			));

			if( $noste->have_posts() ) {

				$noste->the_post();

				?><div class="heading"><div class="infobox-icon infobox-icon-info"></div><h3><a href="<?php echo home_url('/mita-on-avoin-data/'); ?>"><?php the_title(); ?></a></h3></div><div class="img-to-thumb"> <img src="http://www.hri.fi/fi/files/2010/09/iStock_000019511022_ExtraSmall-75x75.jpg" alt="" /></div><?php

				$content = apply_filters('the_content', get_the_content());

				$content_end = strpos( $content, '</p>' ) + 4;
				if( $content_end > 0 ){
					$content = substr( $content, 0, $content_end );
				}

				$content = n_chars( strip_tags( $content ), 230, 250 );

				?><p><?php echo $content; ?> <a class="bold" href="<?php echo home_url('/mita-on-avoin-data/'); ?>"><?php _e( 'Lue lisää', 'hri' ); ?></a></p><?php

				wp_reset_postdata();

			}

			unset( $noste );

		?></div>

		<div class="infobox infobox-3 clearfix left"><?php

			switch_to_blog(1);

			$discussion = new WP_Query(array(
				'post_type' => 'discussion',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => '_link_to_data'
					)
				)
			)); // @todo: some discussions have _link_to_data metas linking to non existing datas. Change query to JOIN with existing and published datas only

			if( $discussion->have_posts() ) {

				$discussion->the_post();

				global $post;

				?><div class="heading"><div class="infobox-icon infobox-icon-discuss"></div><h3><a href="<?php echo ROOT_URL, '/fi/keskustelut/'; ?>"><?php _e( 'Keskustelut', 'hri' ); ?></a></h3></div><?php

				if( $post->comment_count > 0 ) {

					global $wpdb;

					$comment = $wpdb->get_results( "SELECT * FROM wp_comments WHERE comment_post_ID = {$post->ID} ORDER BY comment_date DESC LIMIT 0,1" );
					$comment = $comment[0];
					$virtual = false;

				} else {

					$comment = array(
						'hri_excerpt' => n_words( strip_tags( $post->post_content ), 15 ),
						'comment_post_ID' => $post->ID,
						'comment_date' => $post->post_date
					);

					if( isset($meta_author) && $meta_author ) {

						$comment['comment_author'] = $meta_author;
						$comment['comment_author_email'] = get_post_meta( $post->ID, 'user_email', true );
					}
					else {

						$userdata = get_userdata( $post->post_author );

						$comment['comment_author'] = $userdata->display_name;
						$comment['comment_author_email'] = $userdata->user_email;
					}

					$comment = (object) $comment;
					$virtual = true;

				}

				hri_comment_excerpt( $comment, $virtual );

				$linked = get_post_meta( $post->ID, '_link_to_data', true );

				$linked_data = new WP_Query(array(
					'post_type' => 'data',
					'post_status' => 'publish',
					'posts_per_page' => 1,
					'p' => $linked
				));

				if( $linked_data->have_posts() ) {

					_e( 'Liittyvä data:', 'hri' );

					$linked_data->the_post();

					?><p><a class="bold block nowrap" style="width:100%;overflow:hidden;text-overflow:ellipsis;" href="<?php echo hri_link( get_permalink(), HRI_LANG, 'data' ); ?>"><?php data_title(); ?></a></p><?php

					wp_reset_postdata();

				}

				unset( $linked_data );

				wp_reset_postdata();

			}

			?><p><a class="arrow bold" href="<?php echo ROOT_URL, '/fi/keskustelut/'; ?>"><?php _e( 'Kaikki keskustelut', 'hri' ); ?></a></p><?php

			unset( $discussion );

			restore_current_blog();

		?></div>

		<div class="infobox infobox-3 clearfix left">
			<?php

			$term_kaytannon_kokemuksia = get_term_by( 'slug', 'kaytannon-kokemuksia', 'category' );
			if( $term_kaytannon_kokemuksia ) $term_kaytannon_kokemuksia_link = get_term_link( $term_kaytannon_kokemuksia, 'category' );
			if( !isset( $term_kaytannon_kokemuksia_link ) || is_wp_error( $term_kaytannon_kokemuksia_link ) ) $term_kaytannon_kokemuksia_link = '';

			?>
			<div class="heading"><div class="infobox-icon infobox-icon-info"></div><h3><a href="<?php echo $term_kaytannon_kokemuksia_link; ?>"><?php _e( 'Käytännön kokemuksia', 'hri' ); ?></a></h3></div><?php
			
			$article = new WP_Query(array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'category__in' => $term_kaytannon_kokemuksia->term_id
			));
			
			if( $article->have_posts() ) {
				
				$article->the_post();

				?><a class="bold" href="<?php the_permalink(); ?>"><?php hri_thumbnail(); the_title(); ?></a><?php
				
				$content = apply_filters('the_content', get_the_content());

				$content_end = strpos( $content, '</p>' ) + 4;
				if( $content_end > 0 ){
					$content = substr( $content, 0, $content_end );
				}

				$content = n_chars( strip_tags( $content ), 230, 250 );

				?><p><?php echo $content; ?> <a class="bold" href="<?php the_permalink(); ?>"><?php _e( 'Lue lisää', 'hri' ); ?></a></p><p>
					<a class="arrow" href="<?php echo $term_kaytannon_kokemuksia_link; ?>"><?php _e( 'Lisää käytännön kokemuksia', 'hri' ); ?></a></p><?php
				
				wp_reset_postdata();
				
			}
			
			unset( $article );

		?></div>
	</div>
</div><?php

get_footer();

?>
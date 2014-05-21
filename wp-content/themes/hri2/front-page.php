<?php

get_header();

the_post();

if( ORIGINAL_BLOG_ID == 3 ) {

	?><div class="content" id="content-en"><?php the_content(); ?></div><?php

}

?>
<script type="text/javascript">
// <!--
$(document).ready(function($) {

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

			$('#app-highlight-c .post-highlight').fadeOut(150,function(){

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
					$('#app-highlight-c').html(data).find('.post-highlight').fadeIn(150);
					doing_ajax_app = false;
				});

			});

		}

	});

});
// -->
</script><?php


if ( ORIGINAL_BLOG_ID != 4 ) { // not sv_SE

//	if ( have_posts() ) {
	
//		while ( have_posts() ) {
	
//			the_post();
	
			?><article id="post-<?php the_ID(); ?>" <?php post_class('column no-tb col-half'); ?>>

			<h1><?php

				$titles = array(
					__('Avoin data = Avoin yhteiskunta', 'hri' ),
					__('Avoin data = Uutta liiketoimintaa', 'hri' ),
					__('Avoin data = Parempia nettipalveluja', 'hri' ),
					__('Avoin data = Mainioita mobiilisovelluksia', 'hri' ),
					__('Avoin data = Osallistava hallinto', 'hri' ),
					__('Avoin data = Ennennäkemättömiä oivalluksia', 'hri' ),
					__('Avoin data = Digiajan polttoaine', 'hri' )
				);

				echo $titles[ rand(0, count( $titles ) - 1) ];

				?></h1>
			<?php if ( ORIGINAL_BLOG_ID != 3 ) { ?><div class="content"><?php the_content(); ?></div><?php } ?>

			<?php
			if(ORIGINAL_BLOG_ID == 3) {
				echo '<a class="arrow" href="' . ROOT_URL . '/en/about/">';
				_e( 'Lisää HRI:stä', 'hri' );
				echo '</a>';
				echo '<a class="arrow" href="' . ROOT_URL . '/en/about/open-data">';
				_e( 'Lisää avoimesta datasta', 'hri' );
				echo '</a>';
			} else {
				echo '<a class="arrow" href="' . ROOT_URL . '/fi/hri-projekti/">';
				_e( 'Lisää HRI:stä', 'hri' );
				echo '</a>';
				echo '<a class="arrow" href="' . ROOT_URL . '/fi/mita-on-avoin-data/">';
				_e( 'Lisää avoimesta datasta', 'hri' );
				echo '</a>';
			}
			?>

			</article>

			<div class="column no-tb col-half">
				<form method="get" action="<?php echo DATA_SEARCH_URL; ?>" class="hri-search">
					<input class="hri-input" name="text" type="text" placeholder="<?php _e( 'Hae dataa...', 'hri' ); ?>" />
					<input class="hri-submit" type="submit" />
				</form>
				<a class="arrow" href="<?php echo DATA_SEARCH_URL; ?>"><?php _e( 'Tarkempi datahaku', 'hri' ) ?></a>
			</div>

			<?php
	
//		}
	
//	}

	$stickies = get_option( 'sticky_posts' );

	if( $stickies ) {

		$sticky_posts = new WP_Query(array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 4,
			'post__in' => $stickies
		));

	}

	$args = array(
		'post_type' => 'post',
		'post_status' => 'publish',
		'posts_per_page' => '4',
	);

	if( $stickies ) {
		$args['ignore_sticky_posts'] = true;
		$args['post__not_in'] = $stickies;
		$args['posts_per_page'] = 4 - $sticky_posts->post_count;
	}

	$nostot = new WP_Query( $args );

	if( $stickies ) {
		$nostot->posts = array_merge( $sticky_posts->posts, $nostot->posts );
		$nostot->post_count = count( $nostot->posts );
	}

	unset( $sticky_posts );

	if( $nostot->have_posts() ) {
	
		?><div id="nostot"><?php
	
		$nosto = 1;
	
		while( $nostot->have_posts() && $nosto < 5 ) {
	
			$nostot->the_post();
	
			?><a class="front-page-link" href="<?php the_permalink(); ?>">
				<article id="post-<?php the_ID(); ?>" <?php
	
				$class = $nosto++ % 2 == 1 ? 'odd' : 'even';
				if( is_sticky() ) $class .= ' sticky';
	
				$thumb = false;
	
				if( has_post_thumbnail() ) {
					$class .= ' post-thumb';
					$thumb = true;
				}
	
				post_class( $class );
	
				?>>
				<?php the_post_thumbnail( 'square' ); ?>
				<div class="image-overlay"></div>
				<div class="inner">
					<?php if( !$thumb ) { ?><h2><?php the_title(); ?></h2><?php } ?>
					<div class="more" title="<?php printf( __( 'Lue artikkeli %s', 'hri' ), get_the_title() ); ?>"></div>
					<?php if( $thumb ) { ?><h2><?php the_title(); ?></h2><?php } ?>
				</div><?php
			?></article>
			</a>
	<?php
	
		}
	
		?>
		<a class="read-more" href="<?php
	
			if (ORIGINAL_BLOG_ID == 3) echo home_url(), '/category/news/';
			else echo home_url(), '/category/ajankohtaista/';
	
		?>"><?php _e( 'Lisää artikkeleita', 'hri' ); ?> <span>»</span></a>
		<div class="clear"></div>
	</div><?php
	
	}
	
	
	if (ORIGINAL_BLOG_ID == 2) {
	?>
	<div class="row">
		<div class="column col-half">
			<a id="avaa-dataa" href="<?php echo ROOT_URL; ?>/fi/avaa-dataa/"><h2 class="big"><?php _e('Avaa dataa', 'hri'); ?></h2></a>
			<a class="arrow" href="<?php echo ROOT_URL; ?>/fi/avaa-dataa/"><?php _e('Näin pääset alkuun', 'hri'); ?></a>
			<a class="arrow" href="<?php echo ROOT_URL; ?>/fi/avaa-dataa/miksi-avata-dataa/"><?php _e('Miksi datan avaaminen on tärkeää?', 'hri') ?></a>
			<div class="clear"></div>
			<?php
	
			switch_to_blog(1);
	
			function filter_empty_titles($where){
	
				global $wpdb;
				$where .= " AND $wpdb->posts.post_title <> ''";
				return $where;
	
			}
	
			add_filter( 'posts_where', 'filter_empty_titles' );
	
			$new_data = new WP_Query(array(
				'post_type' => 'data',
				'post_status' => 'publish',
				'posts_per_page' => 3
			));
	
			remove_filter( 'posts_where', 'filter_empty_titles' );
	
			if( $new_data->have_posts() ) {
	
				?><div class="h4-c"><a href="<?php echo DATA_SEARCH_URL; ?>"><h4 class="icon icon-small-data"><?php _e('Uusimmat datat', 'hri'); ?></h4></a></div><ul class="dash-list"><?php
	
				while( $new_data->have_posts() ) {
	
					$new_data->the_post();
					?><li><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'data' ); ?>"><?php data_title(); ?></a></li><?php
	
				}
	
				?></ul><a class="read-more" href="<?php
	
					echo ROOT_URL;
					if( ORIGINAL_BLOG_ID == 3 ) echo '/en/data-search/';
					else echo '/fi/data-haku/';
	
				?>"><?php _e('Lisää dataa', 'hri') ?> <span>»</span></a><?php
	
				wp_reset_postdata();
	
			}
	
			unset( $new_data );
	
			?>
		</div>
		
		<div class="column col-half rel">
			
			<a id="sovella-dataa" href="<?php echo ROOT_URL; ?>/fi/kayta-dataa/"><h2 class="big"><?php _e('Käytä dataa', 'hri'); ?></h2></a>
			<a class="arrow" href="<?php echo ROOT_URL; ?>/fi/kayta-dataa/"><?php _e('Näin pääset alkuun', 'hri'); ?></a>
			<a class="arrow clear" href="<?php echo ROOT_URL; ?>/fi/category/ajankohtaista/visualisointiblogi/tyokalut/"><?php _e('Työkaluja', 'hri'); ?></a>
			<div class="clear"></div>
			<?php 
			
			$app = new WP_Query(array(
				'post_type' => 'application',
				'post_status' => 'publish',
				'posts_per_page' => 1
			));
			
			if( $app->have_posts() ) {
	
				global $post;
	
				$app->the_post();
	
				?><div class="h4-c"><a id="app-ajax-btn" title="<?php _e( 'Satunnainen sovellus', 'hri' ); ?>"></a><a href="<?php echo APP_SEARCH_URL; ?>"><h4 class="icon icon-small-apps"><?php _e('Sovellukset', 'hri'); ?></h4></a></div>
	
				<div id="app-highlight-c">
					<div id="post-<?php the_ID(); ?>" <?php post_class( 'post-highlight clear' ); ?>><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php
	
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
	
			?><a class="read-more" href="<?php
	
			echo ROOT_URL;
			if( ORIGINAL_BLOG_ID == 3 ) echo '/en/applications/';
			else echo '/fi/sovellukset/';
	
			?>"><?php _e('Lisää sovelluksia', 'hri') ?> <span>»</span></a><?php
			
			unset( $app );
	
			restore_current_blog();
			
			?>
	
		</div>
		<div class="clear"></div>
	</div>
	
	<div class="row" style="margin-top:11px">
		<div class="column col-half no-tb">
		    <?php
		    
		    $results = $wpdb->get_results('
            	SELECT DISTINCT data_post_id, SUM(page_pageviews) as sum_page_pageviews  FROM wp_hri_analytics_pageviews_last_30d
            	WHERE data_post_id != 0
            	GROUP BY data_post_id
            	ORDER BY sum_page_pageviews DESC
            	LIMIT 3
            ');

			if( $results ) {

				?><div class="h4-c">
				<h4 class="icon icon-small-downloads">&nbsp;<?php _e('Katsotuimmat datat - viimeisen kuukauden Top 3', 'hri'); ?></h4>
				</div>
				<ul class="dash-list"><?php

				switch_to_blog(1);
				foreach ($results as $result) {
					$post_id = $result->data_post_id;
					$download_data = new WP_Query(array(
						'post_type' => 'data',
						'post_status' => 'publish',
						'post__in' => array($post_id)
					));

					while( $download_data->have_posts() ) {

						$download_data->the_post();
						?><li><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'data' ); ?>"><?php data_title(); ?></a></li><?php

					}
				}
				wp_reset_postdata();

				switch_to_blog(ORIGINAL_BLOG_ID);
				?></ul><?php

			}

			?>
		    
			<div id="activity-stream" style="margin-top:31px"><?php
	
			dynamic_sidebar( 'front-page' );
	
			?></div>
			<a class="read-more" href="<?php
	
			echo ROOT_URL;
			if( ORIGINAL_BLOG_ID == 3 ) echo '/en/activity/';
			else echo '/fi/aktiviteetti/';
	
			?>"><?php _e('Lisää päivityksiä', 'hri') ?> <span>»</span></a>
	
		</div>
		<div class="column col-half no-tb">

			<div class="h4-c">
				<a href="<?php echo NEW_DATA_REQUEST_URL; ?>">
					<h4 class="icon icon-small-data"><?php _e( 'Toivo dataa!', 'hri' ); ?></h4>
				</a>
			</div>

			<div class="clearfix post-highlight">
				<a href="<?php echo NEW_DATA_REQUEST_URL; ?>" class="icon-wish"></a>
				<div class="highlight-excerpt">
					<p><?php _e( 'Etkö löydä haluamaasi dataa HRIstä?', 'hri' ); ?></p>
					<a href="<?php echo NEW_DATA_REQUEST_URL; ?>"><?php _e( 'Tee datatoive ja kerro meille siitä', 'hri' ); ?></a>
				</div>
			</div>

			<a class="read-more" href="<?php

				if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL, '/fi/datatoiveet/';
				if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL, '/en/data-requests/';

			?>"><?php _e('Kaikki datatoiveet', 'hri') ?> <span>»</span></a>


			<?php

			$term_vb = get_term_by( 'slug', 'visualisointiblogi', 'category' );

			$vb = new WP_Query(array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'category__in' => array( $term_vb->term_id )
			));
			
			if( $vb->have_posts() ) {
	
				?><h2 class="vb-title"><?php

					if( $term_vb ) $term_vb_link = get_term_link( $term_vb, 'category' );
					if( !isset( $term_vb_link ) || is_wp_error( $term_vb_link ) ) $term_vb_link = '';

					if( $term_vb_link ) echo '<a href="', $term_vb_link, '">';

				?><img src="<?php echo home_url() ?>/wp-content/themes/hri2/img/vb-title.png" alt="Visualisointiblogi" /><?php

					if( $term_vb_link ) echo '</a>';

				?></h2>
				<div class="clear"></div>
				<div class="post-highlight vb-highlight"><?php
	
					$vb->the_post();
	
					hri_thumbnail();
	
					?><div class="highlight-excerpt"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><?php

					echo '<p>', n_words( strip_tags( hri_excerpt( false ) ), 10 ), '</p>';

					?></div>
					<div class="clear"></div>
				</div><?php
	
				wp_reset_postdata();
				
			}
			
			unset( $vb );
	

			?>
			<div class="row">
				<h3 style="font-size:20px"><?php _e( 'HRI Facebookissa','hri' ); ?></h3>
				<p><?php printf( __( 'Helsinki Region Infoshare toimii ja tiedottaa aktiivisesti myös Facebookissa. <a href="%s">Liity mukaan keskusteluun</a> myös siellä!', 'hri' ), 'http://www.facebook.com/helsinkiregioninfoshare'); ?></p>
				<div class="fb-like" data-href="http://www.hri.fi" data-send="true" data-width="442" data-show-faces="true"></div>
			</div>
			
		</div>
	</div>
	<?php
	} else {
	?>
	<div class="row">
		<div class="column col-half no-tb">
			<?php
	
			switch_to_blog(1);
	
			function filter_empty_titles($where){
	
				global $wpdb;
				$where .= " AND $wpdb->posts.post_title <> ''";
				return $where;
	
			}
	
			add_filter( 'posts_where', 'filter_empty_titles' );
	
			$new_data = new WP_Query(array(
				'post_type' => 'data',
				'post_status' => 'publish',
				'posts_per_page' => 3
			));
	
			remove_filter( 'posts_where', 'filter_empty_titles' );
	
			if( $new_data->have_posts() ) {
	
				?><div class="h4-c"><a href="<?php echo DATA_SEARCH_URL; ?>"><h4 class="icon icon-small-data"><?php _e('Uusimmat datat', 'hri'); ?></h4></a></div><ul class="dash-list"><?php
	
				while( $new_data->have_posts() ) {
	
					$new_data->the_post();
					?><li><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'data' ); ?>"><?php data_title(); ?></a></li><?php
	
				}
	
				?></ul><a class="read-more" href="<?php
	
					echo ROOT_URL;
					if( ORIGINAL_BLOG_ID == 3 ) echo '/en/data-search/';
					else echo '/fi/data-haku/';
	
				?>"><?php _e('Lisää dataa', 'hri') ?> <span>»</span></a><?php
	
				wp_reset_postdata();
	
			}
	
			unset( $new_data );
	
			?>
		</div>
		
		<div class="column col-half no-tb">
			<div class="row">
				<div class="h4-c">
					<h4 class="icon icon-small-speech"><?php _e( 'HRI Facebookissa','hri' ); ?></h4>
				</div>
				<br />
				<p><?php printf( __( 'Helsinki Region Infoshare toimii ja tiedottaa aktiivisesti myös Facebookissa. <a href="%s">Liity mukaan keskusteluun</a> myös siellä!', 'hri' ), 'http://www.facebook.com/helsinkiregioninfoshare'); ?></p>
				<div class="fb-like" data-href="http://www.hri.fi" data-send="true" data-width="442" data-show-faces="true"></div>
			</div>
		</div>
	
	</div>
	
	<?php

	}

} else { // sv_SE

//	if ( have_posts() ) {
//		while ( have_posts() ) {
//			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class('column no-tb'); ?>>
				<div class="content"><?php the_content(); ?></div>
			</article>
	
			<?php
//		}
//	}

}

get_footer();

?>
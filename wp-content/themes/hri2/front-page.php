<?php

/**
 * Categories selection in format: [translation, id in CKAN].
 */
$categories = array(
	array(__('ASUMINEN', 'hri'), 'Asuminen'),
	array(__('KAAVAT JA KIINTEISTÖT', 'hri'), 'Kaavat ja kiinteistöt'),
	array(__('KARTAT JA PAIKKATIETO', 'hri'), 'Kartat ja paikkatieto'),
	array(__('LAKI JA OIKEUSTURVA', 'hri'), 'Laki ja oikeusturva'),
	array(__('SOSIAALIPALVELUT', 'hri'), 'Sosiaalipalvelut'),
	array(__('LIIKENNE', 'hri'), 'Liikenne'),
	array(__('LIIKUNTA JA ULKOILU', 'hri'), 'Liikunta ja ulkoilu'),
	array(__('OPETUS JA KOULUTUS', 'hri'), 'Opetus ja koulutus'),
	array(__('RAKENTAMINEN', 'hri'), 'Rakentaminen'),
	array(__('YMPÄRISTÖ JA LUONTO', 'hri'), 'Ympäristö ja luonto'),
	array(__('TURVALLISUUS', 'hri'), 'Turvallisuus'),
	array(__('KULTTUURI', 'hri'), 'Kulttuuri'),
	array(__('TALOUS JA VEROTUS', 'hri'), 'Talous ja verotus'),
	array(__('TYÖ JA ELINKEINOT', 'hri'), 'Työ ja elinkeinot'),
	array(__('HALLINTO', 'hri'), 'Hallinto'),
	array(__('VÄESTÖ', 'hri'), 'Väestö'),
	array(__('MATKAILU', 'hri'), 'Matkailu'),
	array(__('TERVEYS', 'hri'), 'Terveys'),
	array(__('NEUVONTA', 'hri'), 'Neuvonta'),
	array(__('TIETOTEKNIIKKA', 'hri'), 'Tietotekniikka')
);

# Sort the categories based on the translated title.
function cmpCategories($a, $b) { return ($a[0] < $b[0]) ? -1 : 1; }

usort($categories, "cmpCategories");

/**
 * Helper function for getting 4 news posts - stickied news take precedence.
 *
 * @return posts $nostot
 */
function getNewsPosts($post_type = 'post') {

	$stickies = get_option( 'sticky_posts' );

	if( $post_type == 'post' && $stickies ) {

		$sticky_posts = new WP_Query(array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 4,
			'post__in' => $stickies
		));

	}

	$args = array(
		'post_type' => $post_type,
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

	return $nostot;
}

/**
 * Helper function for creating a 4 post introduction element.
 *
 * @param posts $nostot
 */
function produceNews($nostot) {

	if( $nostot->have_posts() ) {
	
		$nosto = 1;
	
		while( $nostot->have_posts() && $nosto < 5 ) {
	
			$nostot->the_post();
	
			?><a class="front-page-link" href="<?php the_permalink(); ?>"><article id="post-<?php the_ID(); ?>" <?php
	
				$class = $nosto++ % 2 == 1 ? 'odd' : 'even';
				if( is_sticky() ) $class .= ' sticky';
	
				$thumb = false;
	
				if( has_post_thumbnail() ) {
					$class .= ' post-thumb';
					$thumb = true;
				}
	
				post_class( $class );
	
				?>>
				<?php // the_post_thumbnail( 'square' ); ?>
				<?php
				// Because users' pictures do not have a thumbnail generated, we cannot simply call the_post_thumbnail()
				// here as it retains aspect ratio instead of resizing to given dimensions. 
				// instead we have get go through this below process to hardcode dimensions by hand ><
				// Consider disabling WordPress auto-cropping from Admin panel somewhere...
				$post_thumb = get_the_post_thumbnail( $page->ID, 'square' );

				$post_thumb = htmlOverwriteAttributeValue($post_thumb, 'width', 235);
				$post_thumb = htmlOverwriteAttributeValue($post_thumb, 'height', 235);

				echo $post_thumb;
				?>
				<div class="image-overlay"></div>
				<div class="inner">
					<?php if( !$thumb ) { ?><h2><?php the_title(); ?></h2><?php } ?>
					<div class="more" title="<?php printf( __( 'Lue artikkeli %s', 'hri' ), get_the_title() ); ?>"></div>
					<?php if( $thumb ) { ?><h2><?php the_title(); ?></h2><?php } ?>
				</div>
			</article></a><?php
		}
	}
}

function produceLink($text, $url) {
	echo '<a class="read-more" href="' . $url . '" style="padding-right: 20px;">' . $text . ' <span>»</span></a>';
}

/**
 * Helper function for creating a link to the News section.
 */
function produceMoreNewsLink() {

	$url = home_url() . ((ORIGINAL_BLOG_ID == 3) ? '/category/news/' : '/category/ajankohtaista/');
	
	produceLink(__('Näytä lisää artikkeleita', 'hri'), $url);
}

/**
 * Helper function for creating a 4 post introduction element.
 *
 * @param posts $nostot
 */
function produceApps($nostot) {

	if( $nostot->have_posts() ) {
	
		$nosto = 1;
	
		while( $nostot->have_posts() && $nosto < 5 ) {
	
			$nostot->the_post();
	
			?><a class="front-page-link" href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><article id="post-<?php the_ID(); ?>" <?php
	
				$class = $nosto++ % 2 == 1 ? 'odd' : 'even';
				if( is_sticky() ) $class .= ' sticky';
	
				$thumb = false;
	
				if( has_post_thumbnail() ) {
					$class .= ' post-thumb';
					$thumb = true;
				}
	
				post_class( $class );
	
				?>>
				<?php
				// Because users' pictures do not have a thumbnail generated, we cannot simply call the_post_thumbnail()
				// here as it retains aspect ratio instead of resizing to given dimensions. 
				// instead we have get go through this below process to hardcode dimensions by hand ><
				// Consider disabling WordPress auto-cropping from Admin panel somewhere...
				$post_thumb = get_the_post_thumbnail( $page->ID, 'square' );

				$post_thumb = htmlOverwriteAttributeValue($post_thumb, 'width', 235);
				$post_thumb = htmlOverwriteAttributeValue($post_thumb, 'height', 235);

				echo $post_thumb;
				?>
				<div class="image-overlay"></div>
				<div class="inner">
					<?php if( !$thumb ) { ?><h2><?php the_title(); ?></h2><?php } ?>
					<div class="more" title="<?php printf( __( 'Lue artikkeli %s', 'hri' ), get_the_title() ); ?>"></div>
					<?php if( $thumb ) { ?><h2><?php the_title(); ?></h2><?php } ?>
				</div>
			</article></a><?php
		}
	}
}

/**
 * Helper function for creating a link to the Applications section.
 */
function produceMoreAppsLink() {

	$url = home_url() . ((ORIGINAL_BLOG_ID == 3) ? '/en/applications/' : '/fi/sovellukset/');
		
	produceLink(__('Näytä lisää sovelluksia', 'hri'), $url);
}

/**
 * Helper function for creating a link to the Applications section.
 */
function produceReportYourAppsLink() {

	produceLink(__('Ilmoita uusi sovellus', 'hri'), '/fi/ilmoita-uusi-sovellus/');
}

/**
 * Helper function for getting the amount of datasets aka. 'data'-typed posts.
 */
function getDataCount() {

	global $wpdb;

	$count = 0;

	switch_to_blog(1);
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'data' AND post_status = 'publish' AND {$wpdb->posts}.post_title <> ''" );
	restore_current_blog();

	return $count;
}

/**
 * Helper function for getting the amount of datasets aka. 'data'-typed posts belonging to a given category.
 *
 * @param string $category_wpslug
 * @return $count
 */
function getCategoryDataCount($category_wpslug) {

	global $wpdb;

	$count = 0;

	switch_to_blog(1);
	$count = $wpdb->get_var("
		SELECT COUNT(*)
		FROM {$wpdb->posts}, {$wpdb->term_taxonomy}, {$wpdb->term_relationships}, {$wpdb->terms}
		WHERE post_type = 'data'
			AND {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
			AND {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id
			AND {$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id
			AND {$wpdb->terms}.slug = '{$category_wpslug}'
			AND post_status = 'publish'
			AND {$wpdb->posts}.post_title <> ''
		"
	);
	restore_current_blog();

	return $count;
}

/** 
 * Helper function for altering HTML-attributes' values. 
 */
function htmlOverwriteAttributeValue($html, $attribute, $value) {

	if(strpos($html, $attribute . '="') !== false) {

		$attr_pos = strpos($html, $attribute . '="');
		$quote1pos = $attr_pos + strlen($attribute . '="');
		$quote2pos = strpos($html, '"', $quote1pos);

		$html = substr($html, 0, $quote1pos) . $value . substr($html, $quote2pos);
	}

	return $html;
}


get_header();

the_post();

if( ORIGINAL_BLOG_ID == 3 ) {

	?><div class="content" id="content-en"><?php the_content(); ?></div><?php

}

?>

<div class="clear-more-front-page"></div>

<script type="text/javascript">
// <!--

function toggleShowCategories() {
	
	jQuery('.hidden-category').toggle();
	
	var more = "<?php echo __('Näytä lisää kategorioita', 'hri'); ?> »";
	var less = "<?php echo __('Näytä vähemmän kategorioita', 'hri'); ?> »";
	if(jQuery("#category-toggler").html() === more) {

		jQuery("#category-toggler").html(less);
	}
	else {

		jQuery("#category-toggler").html(more);
	}
}

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

	?>
	<div>
		<article id="post-<?php the_ID(); ?>" <?php post_class('column no-tb'); ?>>
			<h1 style="margin-bottom: 0px; margin-top: -32px; width: 60%; text-align: center; margin-left: auto; margin-right: auto;"><font style="color: #084169;"><?php echo __('Avointa dataa pääkaupunkiseudulta vapaasti hyödynnettäväksesi', 'hri'); ?></font></h1>
		</article>
	</div>

	<div class="clear-more-front-page"></div>

	<div class="nostot" style="margin: 0 10px;">
		<?php

		$nostot = getNewsPosts();
		produceNews($nostot);
		produceMoreNewsLink();

		?>
	</div>
	
	<div class="clear-10px"></div>

	<div class="clear-more-front-page"></div>

	<div id="data-search-and-categories" class="row gray-background">

		<div class="clear-10px"></div>

		<div class="data-search">
			<div class="column no-tb get-data"><b><?php echo __('HAE DATAA', 'hri'); ?></b></div>
			<div class="column no-tb col-half search-data">
				<form method="get" action="<?php echo DATA_SEARCH_URL; ?>" class="hri-search">
					<input class="hri-input" name="q" type="text" placeholder="<?php echo __( 'Hae dataa...', 'hri' ); ?>" />
					<input type="hidden" name="sort" value="metadata_created desc" />
					<input class="hri-submit" type="submit" />
				</form>
			</div>
			<a class="data-count-link" href="<?php echo DATA_SEARCH_URL; ?>">
				<div class="column no-tb data-count">
					<center>
						<div class="row"><font class="data-count-number"><b><?php echo getDataCount(); ?></b></font></div>
						<font class="row data-count-text"><?php echo __('TIETOAINEISTOA', 'hri'); ?></font>
					</center>
				</div>
			</a>
			<div class="column no-tb arrow-right">&nbsp;</div>
		</div>
		<div class="clear"></div>
		<a class="arrow right" href="<?php echo DATA_SEARCH_URL; ?>"><?php echo __('SIIRRY TARKEMPAAN DATAHAKUUN', 'hri'); ?></a>
		<a class="arrow right" href="<?php echo ROOT_URL . '/' . 'fi' . '/uusi-datatoive/'; ?>"><?php echo __('TOIVO DATAA', 'hri'); ?></a>
		<a class="arrow right" href="<?php echo ROOT_URL . '/' . 'fi' . '/avaa-dataa/ilmoita-tietoaineisto'; ?>"><?php echo __('ILMOITA UUSI DATA', 'hri'); ?></a>

		<div class="clear-20px"></div>

		<h2 class="front-page"><?php echo __('TAI SELAA KATEGORIOITTAIN', 'hri'); ?></h2>

		<div class="row categories">
			<?php

			function createImgSrc($iconsource) { return home_url() . '/wp-content/themes/hri2/img/' . $iconsource . '.png'; }

			$i = 0;
			foreach($categories as $category) {
				
				$ckanid = $category[1];
				$name = $category[0];
				$ckanlink = str_replace(' ', '+', $ckanid);
				$iconsource = str_replace('ö', 'o', str_replace('ä', 'a', str_replace(' ', '_', strtolower($ckanid))));
				$mouseover_iconsource = $iconsource . '_mouseover';
				$wpslug = str_replace(' ', '-', strtolower($ckanid));

				$datacount = getCategoryDataCount($wpslug);

				?>
				<div class="category-box <?php if($i >= 12) { echo 'hidden-category'; } ?>">
					<a href="<?php echo ROOT_URL . '/' . HRI_LANG . '/dataset?categories=' . $ckanlink; ?>&sort=metadata_created+desc">
						<div style="width: 100%;">
							<center><img src="<?php echo createImgSrc($iconsource); ?>" onmouseover="this.src='<?php echo createImgSrc($mouseover_iconsource); ?>'" onmouseout="this.src='<?php echo createImgSrc($iconsource); ?>'"></img></center>
						</div>
						<center><?php echo $name; ?> (<?php echo $datacount; ?>)</center>
					</a>
				</div>
				<?php

				$i++;
				if($i % 6 == 0) { echo '<div class="clear"></div>'; }
			}
			?>
			
			<div class="clear-10px"></div>
			
			<a class="bottom-right" href="javascript:void(0);" id="category-toggler" onclick="javascript:toggleShowCategories();"><?php echo __('Näytä lisää kategorioita', 'hri'); ?> »</a>
			
			<div class="clear-10px"></div>
		</div>

	</div>

	<div class="clear-more-front-page"></div>

	<?php if(ORIGINAL_BLOG_ID == 2 || ORIGINAL_BLOG_ID == 3) { ?>

	<div class="clear-10px"></div>

	<div class="row" style="margin-top: 11px;">
		<div class="column col-half no-tb gray-background" style="float: right;">
		    <?php
		    
		    $results = $wpdb->get_results('
            	SELECT DISTINCT data_post_id, SUM(page_pageviews) as sum_page_pageviews  FROM wp_hri_analytics_pageviews_last_30d
            	WHERE data_post_id != 0
            	GROUP BY data_post_id
            	ORDER BY sum_page_pageviews DESC
            	LIMIT 10
            ');

			if( $results ) {

				?><div class="h4-c" style="margin-top: 11px; padding: 11px 11px;"><h4 class="icon icon-small-downloads" style="font-size: 26px; color: #084169;">&nbsp;<?php echo __('KATSOTUIMMAT DATAT', 'hri'); ?></h4>
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
						?><li><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'dataset' ); ?>"><?php data_title(); ?></a> (<?php echo $result->sum_page_pageviews; ?>)</li><?php
					}
				}
				wp_reset_postdata();

				switch_to_blog(ORIGINAL_BLOG_ID);

				?></ul><?php
			}

			?>
		</div>
		<div class="column col-half no-tb gray-background">

			<?php switch_to_blog(1); ?>

			<div class="h4-c" style="margin-top: 11px; padding: 11px 11px;"><h4 class="icon icon-small-downloads" style="font-size: 26px; color: #084169;">&nbsp;<?php echo __('UUSIMMAT DATAT', 'hri'); ?></h4></div>
			<ul class="dash-list">
			
				<?php

				$newest_data = new WP_Query(array(
					'post_type' => 'data',
					'post_status' => 'publish',
					'posts_per_page' => 10,
				));

				while($newest_data->have_posts()) {

					$newest_data->the_post();

					$post_date = date('d.m.y', strtotime($post->post_date_gmt));

					?><li><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'dataset' ); ?>"><?php data_title(); ?></a> <?php echo $post_date; ?></li><?php
				}

				?>

			</ul>
		</div>
	</div>

	<div class="clear-more-front-page"></div>

	<!-- <div class="clear-10px"></div>

	<div class="row gray-background">
		
		<div style="padding-left: 42%;"><a class="arrow" href="<?php echo DATA_SEARCH_URL; ?>"><?php echo __('Näytä lisää dataa', 'hri'); ?></a></div>
		
		<div class="clear"></div>
	
	</div> -->

	<div class="clear-20px"></div>

	<? } // End if(ORIGINAL_BLOG_ID == 2 || ORIGINAL_BLOG_ID == 3) ?>

	<? if(ORIGINAL_BLOG_ID == 2) { ?>
	
	<div class="nostot" style="padding: 0 10px;">

		<h2 class="front-page"><?php echo __('SOVELLUKSET', 'hri'); ?></h2>

		<?php

		$nostot = getNewsPosts('application');
		produceApps($nostot);
		produceMoreAppsLink();
		produceReportYourAppsLink();
		
		switch_to_blog(ORIGINAL_BLOG_ID);
		
		?>

		<div class="clear"></div>
	</div>

	<div class="clear-20px"></div>

	<div class="clear-more-front-page"></div>

	<div>
		<div class="column col-half no-tb">
		    
			<div id="activity-stream">
				<?php dynamic_sidebar('front-page'); ?>
			</div>
			<a class="read-more" href="<?php

			echo ROOT_URL;
			if( ORIGINAL_BLOG_ID == 3 ) echo '/en/activity/';
			else echo '/fi/aktiviteetti/';

			?>"><?php echo __('Näytä lisää päivityksiä', 'hri') ?> <span>»</span></a>
			<div class="clear"></div>
		
		</div>
		<div class="column col-half no-tb">

			<div class="twitter-img-resizecrop">
				<img src="<?php echo home_url() ?>/wp-content/themes/hri2/img/Twitter_logo_blue.png"></img>
			</div>
			<div style="float: left;"><h1 style="margin: 10px 20px; font-size: 26px; color: #084169;">@HRInfoshare</h1></div>

			<div class="clear"></div>

			<?php dynamic_sidebar('front-page2'); ?>

			<div class="clear"></div>

		</div>
		<div class="clear"></div>
	</div>
</div>
	<?php

	} // End if(ORIGINAL_BLOG_ID == 2)

} else { // sv_SE

	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class('column no-tb'); ?>>
		<div class="content"><?php the_content(); ?></div>
	</article>

	<?php

}

get_footer();

?>
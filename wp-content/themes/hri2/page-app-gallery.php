<?php

/*
 * Template name: Application gallery
 */

$body_class = 'app-gallery';

global $wpdb;

wp_enqueue_script( 'jquery.tools', get_bloginfo('template_url') . '/js/jquery.tools.min.js', array('jquery') );

get_header();

$is_wide = (
	(isset( $_GET['search']) && !empty( $_GET['search'] )) ||
//	(isset( $_GET['search_text']) && !empty( $_GET['search_text'] )) ||
	(isset( $_GET['application_author']) && !empty( $_GET['application_author'] )) ||
	(isset( $_GET['application_tag']) && !empty( $_GET['application_tag'] )) ||
	(isset( $_GET['application_category']) && !empty( $_GET['application_category'] ))
);

?>
<script type="text/javascript">
// <!--

jQuery('#main-nav').find('.application-parent').addClass('current-page-ancestor');

$(document).ready(function () {

	var $=jQuery.noConflict();

	var page = <?php
		if(!isset($_GET['searchpage']) || empty($_GET['searchpage']) || !ctype_digit($_GET['searchpage'])) {
			echo 1;
		} else {
			echo $_GET['searchpage'];
		}
	?>;

	var author = '<?php if( isset( $_GET['application_author'] ) ) echo esc_attr( $_GET['application_author'] ); ?>';
	var tag = '<?php if( isset( $_GET['application_tag'] ) ) echo esc_attr( $_GET['application_tag'] ); ?>';
	var category = '<?php if( isset( $_GET['application_category'] ) ) echo esc_attr( $_GET['application_category'] ); ?>';
	var search = $('#search');
	var searchString;
	var url = "<?php echo ROOT_URL;

	if (ORIGINAL_BLOG_ID == 2) echo '/fi';
	if (ORIGINAL_BLOG_ID == 3) echo '/en';
	if (ORIGINAL_BLOG_ID == 4) echo '/se';

	?>/wp-admin/admin-ajax.php";
	var last_search = "";

	function doSearchString() {

		search.val( search.val().replace(/"/g,"") );

		searchString = 'search_text=' + search.val().replace(/,/g, '\\,');
		searchString += '&author='+author.replace(/,/g, '\\,');
		searchString += '&app_tag='+tag;
		searchString += '&category='+category;
		searchString += "&searchpage="+page;
		searchString += '&sort='+$('#appselect').val();
		searchString += '&perpage='+( $('#apps').hasClass('apps-wide') ? 9 : 6 );

	}

	function doSearch() {

		$('#app-loading').show();

		if( last_search != search.val() ) {

			last_search = search.val();
			page = 1;

		}

		doSearchString();

		$('.search-result-meta').remove();
		$('#results').text('');

		$.cookie("searchString", null);
		$.cookie("searchString", searchString.replace(/tag=/g, "tags=").replace(/ /g, "%20"), { path: '/' });
		$.cookie("searchType", "application", { path: '/' });

		if( typeof _gaq !== 'undefined' ){
			_gaq.push(['_trackEvent', 'Search', 'Apps', searchString]);
		}

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: "hri_search",
				search_string: 'application|<?php echo HRI_LANG; ?>|'+searchString,
				page: page,
				locale: '<?php echo get_locale(); ?>'
			},
			dataType: 'html',
			success: function(data) {
				document.getElementById('results').innerHTML = data;
				$('.search-result-meta').appendTo( $('#app-top-col') );
			}
		}).done(function(){
			$('#app-loading').hide();
		});

	}

	function go_wide(){

		$('#app-top-col').html('');
		$('#tags-c, #tags-show-all').hide();
		$('#apps').addClass('apps-wide');

	}

	var original_h;

	$('#tags-show-all').click(function () {

		if( $(this).hasClass('tags-open') ) {

			$(this).removeClass('tags-open').find('span').text('<?php _e( 'Näytä kaikki', 'hri' ); ?>');
			$('#tags-title').text('<?php _e( 'Yleisimmät avainsanat', 'hri' ); ?>');

			$('#app-side-top-col-content').animate({
				left : 0,
				width: 250
			}, function(){

				$('#app-top-col').css({
					left: 0
				});

				$('#tags').animate({
					height : 144
				});

				$('#apps-above').animate({
					height : original_h
				});

			});

		} else {

			$(this).addClass('tags-open').find('span').text('<?php _e( 'Sulje', 'hri' ); ?>');
			$('#tags-title').text('<?php _e( 'Kaikki avainsanat', 'hri' ); ?>');

			$('#app-side-top-col-content').animate({
				left : -680,
				width: 940
			}, function(){

//				$('#app-top-col').fadeTo( 250, 0.01 );
				$('#app-top-col').css({
					left: -9999
				});

				$('#tags').animate({
					height : $('#tags-inner').height()
				});

				original_h = $('#apps-above').height();

				var target_h = $('#tags-inner').height() + 200;

				if( target_h > $('#apps-above').height() ) {
					$('#apps-above').animate({
						height : target_h
					});
				}

			});

		}

	});

	$('.scrollable').scrollable({ circular: true }).navigator();

	$('#app-carousel').css({ height : $('#app-carousel .items').height() });

	$('#appselect').change(function(){
		$('#appselecttext').text( $(this).find('option:selected').text() );
	});

	$(document).on( 'click', '#apps .pager a', function(){

		if( !$(this).hasClass('pagedisabled') ) {

			if( $(this).hasClass('pagenum') ) {
				page = $(this).attr('id').substr(2);
			} else if( $(this).hasClass('previous') ) {
				--page;
			} else if ( $(this).hasClass('next') ) {
				++page;
			}

			doSearch();

		}

		return false;

	});

	$('#app-search').submit(function(e){

		e.preventDefault();

		author = '';
		tag = '';

		go_wide();
		doSearch();

		return false;

	});

	$('.app-author-link').click(function(){

		author = encodeURIComponent( $(this).text() );

		go_wide();
		doSearch();

	});

	if( $('#tags-inner').height() <= $('#tags').height() ) {
		$('#tags-show-all').hide();
	}

	$('#appselect').change(function(){
		doSearch();
	});

	doSearch();

});
// -->
</script>

<div id="apps-above" class="clearfix" style="margin-bottom:30px">
	<div id="app-top-col" class="column no-tb col-wide clearfix rel"><?php

		if( !$is_wide ) {

			switch_to_blog(1);

			$highlights = new WP_Query(array(
				'post_type' => 'application',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'meta_query' => array(
					array(
						'key' => 'featured_app',
						'value' => 1
					)
				)
			));

			if ( !$highlights->have_posts() ) {

				$highlights = new WP_Query(array(
					'post_type' => 'application',
					'post_status' => 'publish',
					'posts_per_page' => 5,
					'orderby' => 'rand'
				));

			}

			if ( $highlights->have_posts() ) {

				?><div id="app-carousel-c">
					<a class="prev previous browse"></a>
					<a class="next browse"></a>

					<div id="app-carousel" class="scrollable">
					<div class="items"><?php

					while ( $highlights->have_posts() ) {

						$highlights->the_post();

						global $post;

						$app_ID = $post->ID;

						$app_link = hri_link( get_permalink(), HRI_LANG, 'application' );

						?><div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<?php

							the_post_thumbnail( 'app' );

							?><a class="app-cover-link" href="<?php echo $app_link; ?>"><h1><?php the_title(); ?></h1></a>
							<div class="content"><a class="app-content-link" href="<?php echo $app_link; ?>"></a><p><?php echo get_post_meta( $app_ID, 'short_text', true ); ?></p>
								<?php

								$author = get_post_meta( $app_ID, 'app_author', true );
								if( $author ) {

									$app_count = app_count_for_author( $author );

									?><p class="author-link-p"><span class="bold caps"><?php _e( 'Tekijä', 'hri' ); ?>:</span> <?php

									if( $app_count > 1 ) {

										?><a class="orange" href="<?php echo APP_SEARCH_URL, '?application_author=', $author; ?>"><?php

									}

									echo esc_html( $author );

									if( $app_count > 1 ) echo '</a>';

									?></p><?php

								}

								$tags = wp_get_post_terms( $app_ID, 'post_tag' );
								if( $tags ) {

									?><div class="app-tags"><?php

									foreach( $tags as $tag ) {

										?><a class="term" href="<?php echo APP_SEARCH_URL, '?application_tag=', $tag->term_id; ?>" data-value="<?php echo $tag->term_id; ?>"><?php echo $tag->name; ?></a><?php

									}

									?></div><?php

								}

								?>
							</div>
						</div><?php

					}

					?></div>
					</div>
				</div>

				<div class="pagerwrap"><div class="pager carousel-pager">
					<div class="navi"></div>
				</div></div><?php

			}

		}

	?></div>
	<div id="app-side-top-col" class="column no-tb col-narrow clearfix">
		<div id="app-side-top-col-content">

			<form action="" method="post" id="app-search" class="hri-search small-search">
				<input id="search" class="hri-input" type="text" placeholder="<?php _e( 'Hae sovelluksia', 'hri' ); ?>" value="<?php

					if( isset( $_GET['search'] ) ) echo esc_html( $_GET['search'] );

				?>" />
				<input class="hri-submit" type="submit" />
			</form>
			<?php if( !$is_wide ) { ?>
			<div id="tags-c" class="clearfix">
				<h2 id="tags-title" class="row"><?php _e( 'Yleisimmät avainsanat', 'hri' ); ?></h2>
				<div id="tags">
					<div id="tags-inner" class="clearfix">
					<?php

					$tags = $wpdb->get_results( "SELECT t.* FROM $wpdb->terms t
	JOIN $wpdb->term_taxonomy tx ON tx.term_id = t.term_id AND tx.taxonomy = 'post_tag'
	JOIN $wpdb->term_relationships tr ON tx.term_taxonomy_id = tr.term_taxonomy_id
	JOIN $wpdb->posts p ON tr.object_id = p.ID AND p.post_type = 'application' AND p.post_status = 'publish'
	GROUP BY term_id ORDER BY COUNT(*) DESC" );

					if( !is_wp_error( $tags ) ) foreach( $tags as $term ) {

						?><a class="term" href="<?php echo APP_SEARCH_URL, '?application_tag=', $term->term_id; ?>" data-value="<?php echo $term->term_id; ?>"><?php echo $term->name; ?></a><?php

					}

					?></div>
				</div>
			</div>
			<a id="tags-show-all"><span><?php _e( 'Näytä kaikki', 'hri' ); ?></span> »</a>
			<?php } ?>
		</div>
	</div>
</div>

<? if(ORIGINAL_BLOG_ID == 3) { 
	
	?><h4>The app gallery is only available in Finnish.</h4><?

} // End if(ORIGINAL_BLOG_ID == 3)
?>

<div id="apps" class="row clear clearfix<?php if( $is_wide ) echo ' apps-wide'; ?>">

	<div id="app-wrapper" class="clearfix">

		<div id="app-gallery" class="rel">

			<h1 class="left"><?php _e( 'Sovellusgalleria', 'hri' ); ?></h1>

			<div id="appselect-c">
				<label for="appselect"><?php _e( 'Järjestä', 'hri' ); ?>:</label>
				<div id="appselecttext"><?php _e( 'Julkaisuajan mukaan', 'hri' ); ?></div>
				<div class="selectmark"></div>
				<select name="searchselect" id="appselect">
					<option value="-1"><?php _e( 'Julkaisuajan mukaan', 'hri' ); ?></option>
					<option value="2"><?php _e( 'Nimen mukaan', 'hri' ); ?></option>
				</select>
			</div>

			<div id="app-loading"></div>

			<div id="results">
			<?php

			restore_current_blog();

			?>
			</div>
		</div>

<?php if( !$is_wide ) { ?>
		<div id="app-sidebar">

			<a id="my-app-btn" class="caps plus-link" href="<?php echo NEW_APP_URL; ?>">
				<?php _e( 'Ilmoita oma sovelluksesi', 'hri' ); ?>
			</a>

			<?php if(ORIGINAL_BLOG_ID == 2): ?>
			<h2><?php _e( 'Sovellusideat', 'hri' ); ?></h2>
			<?php
			
			$appidea = new WP_Query(array(
				'post_type' => 'application-idea',
				'post_status' => 'publish',
				'posts_per_page' => 1
			));
			
			if( $appidea->have_posts() ) {
				
				$appidea->the_post();

				?><a class="caps" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

				<p><?php the_excerpt(); ?></p>

				<a class="arrow" href="<?php

					echo ROOT_URL;
					if( ORIGINAL_BLOG_ID == 3 ) {
						echo '/en/application-ideas/';
					} else {
						echo '/fi/sovellusideat/';
					}

					?>"><?php _e( 'Näytä kaikki sovellusideat', 'hri' ); ?></a>
				<a class="arrow" href="<?php echo NEW_APP_IDEA_URL; ?>"><?php _e( 'Onko sinulla idea sovelluksesta?', 'hri' ); ?></a>
				<?php

				wp_reset_postdata();
				
			}
			
			unset( $appidea );
			endif;

			/* // Removed as agreed on HRI-108.
			$rss = fetch_feed( 'http://data.gov.uk/apps/%2A/rss.xml' );

			$maxitems = 0;

			if (!is_wp_error( $rss ) ) { // Checks that the object is created correctly
				// Figure out how many total items there are, but limit it to 5.
				$maxitems = $rss->get_item_quantity(5);

				// Build an array of all the items, starting with element 0 (first element).
				$rss_items = $rss->get_items(0, $maxitems);
			};

			?><h2><?php _e( 'Sovelluksia maailmalta', 'hri' ); ?></h2><ul id="app-gallery-rss"><?php

			if ($maxitems == 0) {

				?><li><?php _e( 'Ei syötteitä', 'hri' ); ?></li><?php

			} else {

				// Loop through each feed item and display each item as a hyperlink.
				foreach ( $rss_items as $item ) {

					$feed_excerpt = strip_tags( substr( $item->get_content(), 0, strpos( $item->get_content(), '</p>' ) ) );

					?><li><a class="caps" href="<?php echo esc_url( $item->get_permalink() ); ?>"><?php echo esc_html( $item->get_title() ); ?></a><p><?php echo $feed_excerpt; ?></p><a href="http://data.gov.uk">data.gov.uk</a></li><?php

				}

			}*/

			?></ul>

		</div>
<?php } ?>
	</div>

</div>

<?php

get_footer();

?>
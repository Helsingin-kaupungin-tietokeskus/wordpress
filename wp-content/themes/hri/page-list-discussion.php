<?php
/**
 * Template name: search / list discussions
 *
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>

<script type="text/javascript">
// <!--

var page = <?php
	if(!isset($_GET['page']) || empty($_GET['page']) || !ctype_digit($_GET['page'])) {
		echo 1;
	} else {
		echo $_GET['page'];
	}
?>;

var $=jQuery.noConflict();
$(document).ready( function() {
	
	function doSearch() {
		$('.result-discussion').hide();
		$('#results>div, .searching').show();
	
		// Create searchString for AJAX request
		var searchString = 'search_text=' + $('#search_text').val();
		$('.filterbox').each( function() {
			searchString += '&' + $(this).attr('id').substr(4) + '=';
			$(this).children('.filters').children('.filter').each( function() {
				if ( $(this).children('input').size() > 0 ) { searchString += $(this).children('input').val() + ','; }
				else { searchString += $(this).text() + ','; }
			})
		});
		searchString += "&page="+page;

		var sort = $('.sortactive').attr('id').substr(4);
		if($('.sortmark').hasClass('sortreverse')) sort = parseInt( sort ) * -1;

		searchString += '&sort='+sort;

		var url = "<?php echo ROOT_URL;

		if (ORIGINAL_BLOG_ID == 2) echo '/fi';
		if (ORIGINAL_BLOG_ID == 3) echo '/en';
		if (ORIGINAL_BLOG_ID == 4) echo '/se';

		?>/wp-admin/admin-ajax.php";

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: "hri_discussion_search",
				search_string: 'discussion|<?php echo HRI_LANG; ?>|'+searchString,
				locale: "<?php echo get_locale(); ?>"
			},
			dataType: 'html',
			success: function(data) {
				$('#results_discussions').children('.searching').hide().siblings('.result-discussions').html(data).show();
				$('.pager a').removeAttr('href');
			}
		});
	}
	
	doSearch();

	$('#search').click( function() { doSearch(0); return false; });

	$('.filter').live('click', function() {
		
		var filterText = $(this).text();

		var remove_el;

		if ( $(this).parent().parent().attr('id') == 'box_data' ) {
			remove_el = $(this).parent().parent();
		} else {
			remove_el = $(this);
		}

		remove_el.fadeOut(300, function() {
			remove_el.remove();
			page = 1;
			doSearch();
		})

	});



	$('a.pagenum').live('click', function() {

		page = parseInt( $(this).attr('id').substr(2), 10);
		doSearch();

	});
	
	$('.pager a.next').live('click', function() {

		var lastpage = $('.pagenum').size()+1;

		if ( page < lastpage ) {
			++page;
			doSearch();
		}

	});

	$('.pager a.previous').live('click', function() {

		if ( page > 1 ) {
			--page;
			doSearch();
		}

	});

	<?php hri_js_sort_options(); ?>

});

// -->
</script>

		<div id="container" class="one-column">
			<div id="content" role="main">
				<?php
				$startNewUrl = '';
				if (ORIGINAL_BLOG_ID == 2) $startNewUrl = home_url() . '/fi/aloita-uusi-keskustelu/';
				if (ORIGINAL_BLOG_ID == 3) $startNewUrl = home_url() . '/en/start-a-new-discussion/';
				?>
				<div id="search_call_to_actions">
					<div class="call_to_action_container">
						<a class="no-hover" href="<?php echo $startNewUrl; ?>"><h3 class="widget-title-link title-add-discussion"><?php _e('Start a new discussion','twentyten'); ?></h3></a>
					</div>
				</div>
				<h1 class="discussion"><?php the_title(); ?></h1>

				<form action="<?php echo home_url();
			
	if (ORIGINAL_BLOG_ID == 2) echo '/fi/keskustelut/';
	if (ORIGINAL_BLOG_ID == 3) echo '/en/discussions/';
			
			?>" method="post">
				
				<div class="searchboxwrap discussionsearch">
				
					<div class="searchbox" id="box_text">
						<label for="search_text"><?php _e('Search','twentyten'); ?></label>
						<input type="text" size="14" name="search_text" id="search_text" />
						<input type="submit" name="search" id="search" value="<?php _e('search','twentyten'); ?>" />
					</div>
<?php

if ( !empty( $_GET['related_to']) || !empty( $_POST['linked'] ) ) { ?>

		<div class="searchbox searchboxwide filterbox" id="box_data">
			<?php _e('Related to data','twentyten'); ?>

			<div class="filters">
					<?php

/** @var wpdb $wpdb */
global $wpdb;

if( isset( $_GET['related_to']) ) $data = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='data' AND post_status='publish' AND post_name='%s'", $_GET['related_to'] ) );

if ( isset( $_POST['linked'] ) ) {

	$ids = preg_replace( '/([^0-9,])/', '', $_POST['linked'] );

	$query = "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='data' AND post_status='publish' AND ID IN ($ids)";

	$data = $wpdb->get_results( $query );

}

if ( isset( $data ) && $data ) {

	foreach( $data as $d ) {

		echo '<a class="filter">', $d->post_title;

		echo '<input type="hidden" value="', $d->ID, '" /></a>';

	}

} else {

	echo '<a class="filter">" <code>', esc_html( $_GET['related_to'] ), '</code> " not found<input type="hidden" value="1" /></a>';

}

				?>
				</div>

		</div>

<?php

}

?>
					
					<div class="clear">&nbsp;</div>
				</div>
				
				</form>
				
				<div id="results">
					<div id="results_discussions"><a id="discussions"></a>

						<div class="headingrow" style="line-height:32px;padding:5px 10px 0 10px;position:relative;">
							<a id="sort2" class="sortoption"><?php _e('Title', 'twentyten'); ?><div class="sortmark_grey"></div></a>

							<a style="position:absolute;left:502px;" id="sort3" class="sortoption sortactive"><?php _e('Latest message', 'twentyten'); ?><div class="sortmark_grey"></div></a>
						</div>
					
						<div class="searching"></div>
						
						<div class="result-discussions"></div>
					</div>
				</div>

			</div><!-- #content -->
		</div><!-- #container -->
		
		<div id="overlay"></div>

<?php get_footer(); ?>

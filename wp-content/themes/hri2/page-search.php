<?php
/**
 * Template name: Full search
 */

get_header(); ?>

<script type="text/javascript">
// <!--

var clickSleep = false;
var page = <?php
	if(!isset($_GET['page']) || empty($_GET['page']) || !ctype_digit($_GET['page'])) {
		echo 1;
	} else {
		echo $_GET['page'];
	}
?>;

$(document).ready( function($) {

	function doSearch() {

		$('.result').hide();
		$('#results>div, .searching').show();
	
		// Create searchString for AJAX request
		var searchString = 'search_text=' + $('#search').val();
		searchString += "&page="+page;

		var sort_sel = $('.asc, .desc');

		if( sort_sel.size() > 0 ) {
			var sort = sort_sel.attr('id').substr(4);
			if( sort_sel.hasClass('desc') ) sort = parseInt( sort ) * -1;

			searchString += '&sort='+sort;
		}

		$.cookie("searchString", null);
		$.cookie("searchString", searchString.replace(/tag=/g, "tags=").replace(/ /g, "%20"), { path: '/' });
		$.cookie("searchType", "all", { path: '/' });

		var url = "<?php echo ROOT_URL;

		if (ORIGINAL_BLOG_ID == 2) echo '/fi';
		if (ORIGINAL_BLOG_ID == 3) echo '/en';
		if (ORIGINAL_BLOG_ID == 4) echo '/se';

		?>/wp-admin/admin-ajax.php";

		if( typeof _gaq !== 'undefined' ){
			_gaq.push(['_trackEvent', 'Search', 'Full', searchString]);
		}

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: "hri_search",
				search_string: 'datalite|<?php echo HRI_LANG; ?>|'+searchString+'|3',
				locale: '<?php echo get_locale(); ?>'
			},
			dataType: 'html',
			success: function(data) {
				document.getElementById('results_data_lite_result').innerHTML = data;
				$('#results_data_lite_result').siblings('.searching').fadeOut(250).siblings('.result').slideDown(500);
			}
		});

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: "hri_search",
				search_string: 'fullsearch|<?php echo HRI_LANG; ?>|'+searchString,
				locale: '<?php echo get_locale(); ?>'
			},
			dataType: 'html',
			success: function(data) {
				document.getElementById('results_search_result').innerHTML = data;
				$('#results_search_result').siblings('.searching').fadeOut(250).siblings('.result').slideDown(500);
			}
		});
	}
	
	doSearch();

	$('.hri-cancel').click(function(){
		$('#search').val('');
		doSearch();
	});

	$('.hri-submit').click( function() { doSearch(); return false; });

	<?php hri_js_sort_options(); ?>

});
// -->
</script>
<div class="column full">
	<h1><?php _e( 'Hae', 'hri' ); ?></h1>

	<form id="full-search">
				
		<div class="hri-search">
			<input id="search" class="hri-input" type="text" placeholder="<?php _e( 'Syötä hakusanat...', 'hri' ); ?>" value="<?php

				if( isset( $_GET['words'] ) ) echo esc_html( $_GET['words'] );

			?>" />
			<a class="hri-cancel"></a>
			<input class="hri-submit" type="submit" />
		</div>

	</form>

	<div id="results">

		<div id="results_search">
			<div class="headingrow">

				<h6><?php _e( 'Hakutulokset', 'hri' ); ?></h6>
				<a class="sort" id="sort2"><?php _e( 'Otsikko', 'hri' ); ?></a>
				<a class="sort" id="sort1"><?php _e( 'Päivämäärä', 'hri' ); ?></a>
				<a class="sort" id="sort5"><?php _e( 'Viimeisin kommentti', 'hri' ); ?></a>

			</div>

			<div class="searching"></div>
			<div class="result" id="results_search_result"></div>

		</div>

		<div id="results_data_lite">
			<div class="headingrow">
				<h6><?php _e( 'Hakutulokset datasta', 'hri' ); ?></h6>
			</div>

			<div class="searching"></div>
			<div class="result" id="results_data_lite_result"></div>

		</div>

	</div>

</div>

<?php get_footer(); ?>
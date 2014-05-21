<?php
/**
 * Template name: FULL search (blue)
 *
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
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

var $=jQuery.noConflict();
$(document).ready( function() {
		
	<?php
		// Insert values to the form before search

		if(isset($_GET['search_text']) && !empty($_GET['search_text'])) {
		?>
			$("#search_text").val("<?php echo htmlspecialchars($_GET['search_text']); ?>");
			$("#search_text_ajax").val("<?php echo htmlspecialchars($_GET['search_text']); ?>");
		<?php
		}
	?>
	
	function doSearch() {

		$('.result').hide();
		$('#results>div, .searching').show();
	
		// Create searchString for AJAX request
		var searchString = 'search_text=' + $('#search_text').val();
		$('.filterbox').each( function() {
			searchString += '&' + $(this).attr('id').substr(4) + '=';
			$(this).children('.filters').children('.filter').each( function() {
				if ( $(this).children('input').size() > 0 ) { searchString += $(this).children('input').val() + ','; }
				else { searchString += $(this).text() + ','; }
			});
		});
		searchString += "&page="+page;

		var sort = $('.sortactive').attr('id').substr(4);
		if($('.sortmark').hasClass('sortreverse')) sort = parseInt( sort ) * -1;

		searchString += '&sort='+sort;

		$.cookie("searchString", null);
		$.cookie("searchString", searchString.replace(/tag=/g, "tags=").replace(/ /g, "%20"), { path: '/' });
		$.cookie("searchType", "all", { path: '/' });

		var url = "<?php echo ROOT_URL;

		if (ORIGINAL_BLOG_ID == 2) echo '/fi';
		if (ORIGINAL_BLOG_ID == 3) echo '/en';
		if (ORIGINAL_BLOG_ID == 4) echo '/se';

		?>/wp-admin/admin-ajax.php";

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
				document.getElementById('results_data_result').innerHTML = data; // .html(data) does not work with IE
				$('#search-data').children('.searching').fadeOut(250).siblings('.result').slideDown(500);
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
				document.getElementById('results_other_result').innerHTML = data;
				$('#search-other').children('.searching').fadeOut(250).siblings('.result').slideDown(500);
			}
		});
	}
	
	doSearch();

	$('#search').click( function() { doSearch(); return false; });

	<?php hri_js_sort_options(); ?>

});
// -->
</script>
		<div id="container" class="one-column searchall">
			<div id="content" role="main">

				<?php global $wpdb; ?>

				<form action="<?php
                        switch_to_blog(1);
                        if (ORIGINAL_BLOG_ID == 2) echo home_url() . '/fi/haku/';
                        if (ORIGINAL_BLOG_ID == 3) echo home_url() . '/en/search/';
                        if (ORIGINAL_BLOG_ID == 4) echo home_url() . '/se/';
                        restore_current_blog();
                ?>" method="post">
				
				<div class="searchboxwrap fullsearch <?php echo $locale; ?>">
				
					<div class="searchbox" id="box_text">
						<label for="search_text"><?php _e('Search text','twentyten'); ?></label>
						<input type="text" size="14" name="search_text" id="search_text" value="<?php
						
						$words = $_REQUEST['words'];
						
						if ( $words ) echo esc_attr( str_replace( ',', ' ', $words) );
						
						?>" />

						<input type="submit" name="search" id="search" value="<?php _e('Search','twentyten'); ?>" />
					</div>
				
					<div class="clear">&nbsp;</div>
				</div>
				
				</form>

				<div id="search-other">
					<div class="seach_bg_container">
						<h2 class="floatl"><?php _e('Search results','twentyten'); ?></h2>

	<?php
	$sort = array();
	if ( isset($_GET['sort']) ) $i = (int) $_GET['sort'];
	if ( !isset( $i ) || $i < 1 || $i > 3 ) $i = 1;
	$sort[$i] = ' sortactive';
	
	$sortmark_grey = '<div class="sortmark_grey"></div>';
	?>

						<span style="width:429px;padding-left:5px;" class="floatl" id="sort"><?php _e('Sort by','twentyten'); ?>:
							<a style="margin-left:15px;" id="sort2" class="sortoption<?php echo $sort[2]; ?>"><?php _e('Title','twentyten'); echo $sortmark_grey; ?></a>
							<a style="margin-left:20px;" id="sort1" class="sortoption<?php echo $sort[1]; ?>"><?php _e('Date','twentyten'); echo $sortmark_grey; ?></a>
							<a style="margin-left:16px;" id="sort3" class="sortoption<?php echo $sort[3]; ?>"><?php _e('Latest comment','twentyten'); echo $sortmark_grey; ?></a>
						</span>
						<div class="clear"></div>
					</div>
					<div class="searching"></div>
					<div class="result" id="results_other_result"></div>
				</div>

				<div id="search-data">
					<div class="seach_bg_container">
						<h2><?php _e('Search results from data','twentyten'); ?></h2>
						<div class="clear"></div>
					</div>
					<div class="searching"></div>
					<div class="result" id="results_data_result"></div>
				</div>

			</div><!-- #content -->
		</div><!-- #container -->
		
		<div id="overlay"></div>

<?php get_footer(); ?>

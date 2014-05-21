<?php
/**
 * Template name: DATA search
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

			$remove = array( '\'', '"', '\\' );
			$search_text = str_replace( $remove, '', htmlspecialchars( $_GET['search_text'] ) );

		?>
			$("#search_text").val("<?php echo $search_text; ?>");
			$("#search_text_ajax").val("<?php echo $search_text; ?>");
		<?php
		}

		$vars = array('tags', 'area', 'category', 'filetype', 'producer');

		foreach($vars as $var) {
			if(isset($_GET[$var]) && !empty($_GET[$var])) {
				$name = $var;
				if($var == 'tags') $name = 'tag'; // WP doesn't allow $_GET['tag']
				$values = explode(",", $_GET[$var]);

				foreach($values as $val) {
					$val = htmlspecialchars(trim($val));
					if(!empty($val)) {

						$term = null;
						
						if($name == 'tag') {
							$term = get_term_by( 'id', $val, 'post_tag' );
							if (!isset($term->name)) {
								$term = get_term_by( 'name', $val, 'post_tag' );
							}
							$name = 'post_tag';
						} elseif($name == 'category') {
							$term = get_term_by( 'id', $val, 'category' );
						}

					?>
						$("#box_<?php echo $name; ?> .blocklink").html("<?php _e('Change','twentyten'); ?>");
						$("#box_<?php echo $name; ?> .filters").append('<a class="filter"><?php echo ( isset($term->name) ) ? $term->name : $val; ?><input type="hidden" value="<?php echo ( isset($term->name) ) ? $term->term_id : $val; ?>"></a>');
						$("#box_<?php echo $name; ?> .options a.opt input[value=\"<?php echo $val; ?>\"]").parent().addClass('ao');
					<?php
					}
				}
			}
		}

	?>

	var searchString;

	function doSearchString() {

		// Create searchString for AJAX request
		searchString = 'search_text=' + $('#search_text').val();
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

	}

	var url = "<?php echo ROOT_URL;

	if (ORIGINAL_BLOG_ID == 2) echo '/fi';
	if (ORIGINAL_BLOG_ID == 3) echo '/en';
	if (ORIGINAL_BLOG_ID == 4) echo '/se';

	?>/wp-admin/admin-ajax.php";

	function doSearch() {

		var a = $('#search_text_ajax').val();
		a = a.replace(/[,'"]/g,"");
		$('#search_text, #search_text_ajax').val( a );

		doSearchString();

		$('.result').hide();
		$('#results>div, .searching').show();

		$.cookie("searchString", null);
		$.cookie("searchString", searchString.replace(/tag=/g, "tags=").replace(/ /g, "%20"), { path: '/' });
		$.cookie("searchType", "data", { path: '/' });

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: "hri_search",
				search_string: 'data|<?php echo substr( get_bloginfo('language'),0,2 ); ?>|'+searchString,
				page: page,
				locale: '<?php echo get_locale(); ?>'
			},
			dataType: 'html',
			success: function(data) {
				document.getElementById('results_data_result').innerHTML = data;
				$('#results_data').children('.searching').fadeOut(250).siblings('.result').slideDown(500);
			}
		});
	}

	var changed = false;

	function do_filter( option ) {

		option.clone().appendTo(
			option.parent().siblings('.filters')
		).removeClass('opt ao').addClass('filter').attr('id', 'f_'+option.attr('id') );

		$('#f_'+option.attr('id')).children('span').remove();

	}

	function update_options(){

		doSearchString();

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				action: "hri_search",
				search_string: 'search|<?php echo substr( get_bloginfo('language'),0,2 ); ?>|'+searchString,
				page: page,
				locale: '<?php echo get_locale(); ?>'
			},
			dataType: 'json',
			success: function(data) {
				$('.opt').remove();
				var c1,c2,c3,databox,i=0,opttxt;
				for( var box in data ) {
					if(data.hasOwnProperty(box)) {
						databox = data[box];
						for(var option in databox) {
							if( databox.hasOwnProperty(option) ) {
								if( typeof( databox[option][0] ) != 'undefined' ) {
									c1 = (databox[option][0] == 0) ? ' eo' : '';
									c2 = (databox[option][1] == 1) ? ' ao' : '';
									opttxt = ( typeof( databox[option][3] ) == 'undefined' ) ? databox[option][2] : databox[option][3];
									// ie-fix
									c3 = (c1 != '' && c2 != '') ? ' eoao': '';
		
									$('#box_'+box).children('.options').append( $('<a id="opt'+i+'" class="opt'+c1+c2+c3+'">'+opttxt+'<span>('+databox[option][0]+')</span><input type="hidden" value="'+databox[option][2]+'" /></a>') );
									++i;
								}
							}
						}
					}
				}
			}
		});
	}

	function closePopup() {

		$('.searchboxwrap').css({ 'height' : 'auto' });
		$('#overlay').fadeOut(500, function() { $('.above_overlay').removeClass('above_overlay'); } );
		$('.options').hide();

		if ( page > 1 ) {
			page = 1;
			changed = true;
		}

		if ( changed ) {
			update_options();
			doSearch();
		}

		$('.searchbox').removeClass('overlay_open').each(function() {
			if ($(this).children('.filters').children('.filter').size() > 0 ) {
				$(this).children('a.blocklink').text('<?php _e('Change','twentyten'); ?>');
			} else {
				$(this).children('a.blocklink').text('<?php _e('No filter','twentyten'); ?>');
			}
		});

	}


	update_options();
	doSearch();

	$('#clearallbtn').click(function() {
		if ( $('.filters a').size() > 0 || page > 1 || $('#search_text').val() != "" ) {

			$('#search_text').val("");
			$('#search_text_ajax').val("");

			$('.options .ao').removeClass('ao');
			$('.filters a').remove();
			page = 1;
			update_options();
			doSearch();
		}
	});

	$('#search').click( function() { page = 1; doSearch(); return false; });

	$('.opt').live('click', function() {

		changed = true;

		$(this).toggleClass('ao');

		var box =  $(this).parent().parent();
		var box_id = box.attr('id');

		if ( $(this).hasClass('ao') ) {
			// add filter
			do_filter($(this));
		} else {
			// remove filter
			$( '#f_'+$(this).attr('id')).remove();
		}

	});

	$('#overlay').click( function() { if(clickSleep) { return false; } closePopup(); });

	$('.searchbox a.blocklink').click( function() {

		if(clickSleep) { return false; }

		if ( $(this).hasClass('above_overlay') ) {

			closePopup();

		} else {

			// lock .searchboxwrap height
			$(this).parent().parent().css({ 'height' : $(this).parent().parent().css('height') });
			$(this).parent().addClass('overlay_open');
			
			changed = false;
			$(this).addClass('above_overlay');
			$('#overlay').fadeTo(500,0.75);
			$('.options').hide();

			$(this).next().show();
			clickSleep = true;

			setTimeout("changeClickSleep()", 400);
		}
	});


	$('#search_text').click( function() {

		if(clickSleep) { return false; }

		if ( $(this).hasClass('above_overlay') ) {
			closePopup();
		} else {
			$('#overlay').fadeTo(500,0.75);
			$('.options').hide();

			$(this).next().show();
			$('#search_text_ajax').focus();
			$('#search_text_ajax').select();
			clickSleep = true;

			setTimeout("changeClickSleep()", 400);
		}
	});

	$('#clearsearchtext').click(function() {
		$('#search_text').val("");
		$('#search_text_ajax').val("");
	});

	$('#search_text_ajax').keyup(function(event) {
		changed = true;
		$('#search_text').val($('#search_text_ajax').val());
	});

	$('.selectclear').click( function() {
		changed = true;
		$(this).parent().siblings().removeClass('ao');
		$(this).parent().parent().siblings('.filters').children('.filter').remove();
	});
	$('.selectall').click( function() {
		changed = true;

		$(this).parent().siblings('.opt').addClass('ao').each(function(){
			do_filter($(this));
		})
	});
	$('.apply').click( function() { closePopup(); });

	$('.filter').live('click', function() {

		var filterText = $(this).text();

		// Removing filter also removes matching .opt's .ao class
		var val = $(this).children('input').val();
		$('.opt input[value="'+val+'"]').parent().removeClass('ao');

		$(this).fadeOut(300, function() {

			$(this).remove();
			page = 1;
			update_options();
			doSearch();

		})
	});

	<?php hri_js_sort_options(); ?>

});

function changeClickSleep() {
	clickSleep = false;
}

// -->
</script>

		<div id="container" class="one-column">
			<div id="content" role="main">

				<?php global $wpdb; ?>
				<?php
				if ( ORIGINAL_BLOG_ID == 2 ) {
				?>
				<div id="search_call_to_actions">
					<div class="call_to_action_container">
						<a class="no-hover" href="<?php echo ROOT_URL; ?>/fi/uusi-datatoive/"><h3 class="widget-title-link title-add-data-request">Toivo uutta dataa</h3></a>
					</div>
				</div>
				<?php
				}
				?>
				<h1 class="data"><?php _e('Data search','twentyten'); ?></h1>

				<form action="<?php

					echo ROOT_URL;

					if (ORIGINAL_BLOG_ID == 2) echo '/fi/data-haku/';
					if (ORIGINAL_BLOG_ID == 3) echo '/en/data-search/';
					if (ORIGINAL_BLOG_ID == 4) echo '/se/';

				?>" method="post">

				<div class="searchboxwrap <?php echo $locale; ?>">

					<div class="searchbox data" id="box_text">
						<label for="search_text"><?php _e('Search text','twentyten'); ?></label>
						<input type="text" size="14" name="search_text" id="search_text" value="<?php

						if ( isset($_POST['datasearch']) ) {

							$remove = array( '\'', '"', '\\' );
							$search_text = str_replace( $remove, '', $_POST['datasearch'] );
							$search_text = esc_html( str_replace( ',', ' ', $search_text ));

							echo $search_text;

						}

						?>" readonly="readonly" />
						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle">
								<label for="search_text_ajax" class="title"><?php _e('Search text','twentyten'); ?></label><a id="clearsearchtext"><?php _e('Clear','twentyten'); ?></a>
							</div>
							<input type="text" size="14" name="search_text_ajax" id="search_text_ajax" value="<?php if ( isset( $search_text )) echo $search_text; ?>" />
							<input type="image" src="<?php echo bloginfo('template_url');  ?>/images/longsearch_blue_submit.jpg" class="apply" id="search" value="Search" />
							<a class="apply"><?php _e('OK','twentyten'); ?></a>
						</div>
					</div>

					<div class="searchbox filterbox" id="box_area">
						<?php _e('Area','twentyten'); ?>
						<a class="blocklink"><?php _e('No filter','twentyten'); ?><input type="hidden" value="area" /></a>

						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle">
								<span class="title"><?php _e('Area','twentyten'); ?></span><a class="selectclear"><?php _e('Select none','twentyten'); ?><a class="selectall"><?php _e('Select all','twentyten'); ?></a>
							</div>
							<?php

//							switch_to_blog(1);
//
//							$areas = $wpdb->get_results( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'extras_geographic_coverage' GROUP BY meta_value" );
//							if ( $areas ) foreach ( $areas as $area ) {
//
//								echo '<a class="opt">' . $area->meta_value . '</a>';
//
//							}

							?>

							<a class="apply"><?php _e('OK','twentyten'); ?></a>

						</div>

						<div class="filters"></div>

					</div>

					<div class="searchbox filterbox" id="box_category">
						<?php _e('Category','twentyten'); ?>	
						<a class="blocklink"><?php _e('No filter','twentyten'); ?><input type="hidden" value="category" /></a>
						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle">
								<span class="title"><?php _e('Category','twentyten'); ?></span>
								<a class="selectclear"><?php _e('Select none','twentyten'); ?><a class="selectall"><?php _e('Select all','twentyten'); ?></a>
							</div>
<?php

//$categories = get_categories();
//foreach ( $categories as $cat ) {
//	echo "<a class=\"opt\">{$cat->name}<input type=\"hidden\" value=\"{$cat->term_id}\" /></a>";
//}

?>

							<a class="apply"><?php _e('OK','twentyten'); ?></a>

						</div>

						<div class="filters">
						</div>
					</div>

					<div class="searchbox filterbox" id="box_post_tag">
						<?php _e('Tags','twentyten'); ?>
						<a class="blocklink"><?php _e('No filter','twentyten'); ?><input type="hidden" value="post_tag" /></a>
						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle">
								<span class="title"><?php _e('Tags','twentyten'); ?></span>
								<a class="selectclear"><?php _e('Select none','twentyten'); ?><a class="selectall"><?php _e('Select all','twentyten'); ?></a>
							</div>
<?php

//$tags = get_tags_per_post_type('data');
//if ($tags) foreach( $tags as $tag ) {
//
//	echo "<a class=\"opt\">{$tag->name}<input type=\"hidden\" value=\"{$tag->term_id}\" /></a>\n";
//
//}

?>
							<a class="apply"><?php _e('OK','twentyten'); ?></a>

						</div>

						<div class="filters">
						</div>

					</div>

					<div class="searchbox filterbox" id="box_filetype">
						<?php _e('Filetype','twentyten'); ?>
						<a class="blocklink"><?php _e('No filter','twentyten'); ?><input type="hidden" value="filetype" /></a>
						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle small">
								<span class="title"><?php _e('Filetype','twentyten'); ?></span><a class="selectclear"><?php _e('Select none','twentyten'); ?><a class="selectall"><?php _e('Select all','twentyten'); ?></a>
							</div>
							<?php

//							$filetypes = $wpdb->get_results( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE 'resources\__\_format' GROUP BY meta_value" );
//							if ( $filetypes ) foreach ( $filetypes as $filetype ) {
//
//								echo '<a class="opt">' . $filetype->meta_value . '</a>';
//
//							}

							?>

							<a class="apply"><?php _e('OK','twentyten'); ?></a>

						</div>

						<div class="filters">
						</div>

					</div>

<?php /*				<div class="searchbox filterbox" id="box_lang">
						<?php _e('Data language','twentyten'); ?>
						<a class="blocklink"><?php _e('No filter','twentyten'); ?></a>
						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle">
								<span class="title"><?php _e('Language','twentyten'); ?></span><a class="selectclear"><?php _e('Select none','twentyten'); ?><a class="selectall"><?php _e('Select all','twentyten'); ?></a>
							</div>
							<a class="opt"><?php _e('Finnish','twentyten'); ?></a>
							<a class="opt"><?php _e('English','twentyten'); ?></a>
							<a class="opt"><?php _e('Swedish','twentyten'); ?></a>

							<a class="appy"><?php _e('OK','twentyten'); ?></a>

						</div>

						<div class="filters">
						</div>
					</div>
*/ ?>

					<div class="searchbox filterbox" id="box_producer">
						<?php _e('Producer','twentyten'); ?>
						<a class="blocklink"><?php _e('No filter','twentyten'); ?><input type="hidden" value="producer" /></a>
						<div class="options"><div class="optionsarrow"></div>
							<div class="optionstitle">
								<span class="title"><?php _e('Producer','twentyten'); ?></span><a class="selectclear"><?php _e('Select none','twentyten'); ?><a class="selectall"><?php _e('Select all','twentyten'); ?></a>
							</div>
							<?php

//							$authors = $wpdb->get_results( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = 'author' GROUP BY meta_value" );
//							if ( $authors ) foreach ( $authors as $author ) {
//
//								echo '<a class="opt">' . $author->meta_value . '</a>';
//
//							}
//							restore_current_blog();

							?>

							<a class="apply"><?php _e('OK','twentyten'); ?></a>

						</div>

						<div class="filters">
						</div>

					</div>
					<div id="clearallbtn"><?php _e('Clear all','twentyten'); ?></div>
					<div class="clear">&nbsp;</div>
				</div>
<?php // IE8 won't submit form with enter if form is not visible on page load, so .options is styled "display:block" in ie8.css and then hid with script below. ?>
<!--[if lte IE 8]><script type="text/javascript">jQuery('.options').hide();</script><![endif]-->
				</form>

<?php
$sort = array();
//if( isset($_COOKIE['sort']) ) $i = (int) $_COOKIE['sort'];

if ( isset($_GET['sort']) ) $i = (int) $_GET['sort'];
else $i = 1;

if ( $i == 0 ) $i = 1;

if ( $i < 0 ) {

	$i *= -1;
	$sortmark[$i] = '<div class="sortmark sortreverse"></div>';

} else {

	$sortmark[$i] = '<div class="sortmark"></div>';

}

$sort[$i] = ' sortactive';

$sortmark_grey = '<div class="sortmark_grey"></div>';
?>

				<div id="results">

					<div id="results_data"><a id="data"></a>
						<div class="headingrow">
							<span style="width:434px; padding-right: 20px;" class="floatl">
								<span style="width:429px; padding-left: 5px;" class="floatl" id="sort"><!-- <?php _e('Sort by','twentyten');?>: -->
									<a id="sort2" class="sortoption<?php if (isset($sort[2])) { echo $sort[2]; } ?>"><?php _e('Title','twentyten'); if (isset($sortmark[2])) { echo $sortmark[2]; } else { echo $sortmark_grey; } ?></a> |
									<a id="sort1" class="sortoption<?php if (isset($sort[1])) { echo $sort[1]; } ?>"><?php _e('Date','twentyten'); if (isset($sortmark[1])) { echo $sortmark[1]; } else { echo $sortmark_grey; } ?></a>
									<a style="float: right;" id="sort4" class="sortoption<?php if (isset($sort[4])) { echo $sort[4]; } ?>"><?php _e('Rating','twentyten'); if (isset($sortmark[4])) { echo $sortmark[4]; } else { echo $sortmark_grey; } ?></a>
								</span>
							</span>

							<span class="spb_h"><a id="sort3" class="sortoption<?php if (isset($sort[3])) { echo $sort[3]; } ?>"><?php _e('The latest comment','twentyten'); if (isset($sortmark[3])) { echo $sortmark[3]; } else { echo $sortmark_grey; } ?></a></span><span class="spb_h"><?php _e('The latest discussion','twentyten'); ?></span><span class="spb_h"><?php _e('The latest application','twentyten'); ?></span>
						</div>

						<div class="searching"></div>

						<div class="result" id="results_data_result"></div>

					</div>

				</div>

			</div><!-- #content -->
		</div><!-- #container -->

		<div id="overlay"></div>

<?php get_footer(); ?>

<?php
/**
 * Template name: Discussions
 */

get_header(); ?>

<script type="text/javascript">
// <!--

var $=jQuery.noConflict();

$(document).ready( function() {

	var $=jQuery.noConflict();

	var page = <?php
		if(!isset($_GET['page']) || empty($_GET['page']) || !ctype_digit($_GET['page'])) {
			echo 1;
		} else {
			echo $_GET['page'];
		}
	?>;

	var search = $('#search');
	var searchString;

	function doSearch() {
		$('.result-discussion').hide();
		$('#results>div, .searching').show();

		search.val( search.val().replace(/[,'"]/g,"") );

		searchString = 'search_text=' + $('#search').val();

		$('.filterbox').each( function() {
			searchString += '&' + $(this).attr('id').substr(4) + '=';
			$(this).children('.filters').children('.filter').each( function() {
				if ( $(this).children('input').size() > 0 ) { searchString += $(this).children('input').val() + ','; }
				else { searchString += $(this).text() + ','; }
			})
		});
		searchString += "&searchpage="+page;

		var sort_sel = $('.asc, .desc');

		if( sort_sel.size() > 0 ) {
			var sort = sort_sel.attr('id').substr(4);
			if( sort_sel.hasClass('desc') ) sort = parseInt( sort ) * -1;

			searchString += '&sort='+sort;
		}

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

	search.change(function(){

		if( $(this).val().length > 0 ) {
			$('.hri-cancel').fadeIn();
		} else {
			$('.hri-cancel').fadeOut();
		}

		doSearchString();

	});

	$('.hri-submit').click( function() { doSearch(); return false; });
	$('#search_discussion').submit( function() { doSearch(); return false; });

	$('.hri-cancel').click(function(){

		$(this).fadeOut();

		$('#search').val('');
		doSearch();

	});

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

	doSearch();

});

// -->
</script>


<div class="column no-tb full">
	<h1><?php the_title(); ?></h1>
	<form id="search_discussion" action="<?php the_permalink(); ?>" method="post">
		<div class="searchboxwrap discussionsearch clearfix">

			<a class="with-medium-circle left" style="margin-top:5px" href="<?php

				echo ROOT_URL;
				if( ORIGINAL_BLOG_ID == 3 ) echo '/en/start-a-new-discussion/';
				else echo '/fi/aloita-uusi-keskustelu/';

			?>"><div class="circle medium green icon-discussion"></div><?php _e( 'Aloita uusi keskustelu', 'hri' ); ?></a>

			<div class="hri-search right">
				<input id="search" class="hri-input" type="text" placeholder="<?php _e( 'Syötä hakusanat...', 'hri' ); ?>" value="<?php

					if( isset( $_GET['search'] ) ) echo esc_html( $_GET['search'] );

				?>" />
				<a class="hri-cancel"></a>
				<input class="hri-submit" type="submit" />
			</div>

			<?php

if ( !empty( $_GET['related_to']) || !empty( $_POST['linked'] ) ) {

?>
			<div class="searchbox searchboxwide filterbox" id="box_data">
			<?php _e('Liittyvä data','hri'); ?>

				<div class="filters"><?php

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

		?><a class="filter" data-value="<?php echo $d->ID; ?>"><?php echo $d->post_title ?></a><?php

	}

} else {

	echo '<a class="filter" data-value="">" <code>', esc_html( $_GET['related_to'] ), '</code> " not found.</a>';

}

				?>
				</div>
			</div>
<?php

}

?>

		</div>
	</form>

	<div id="results" style="margin-top:30px">
		<div id="results_discussions"><a id="discussions"></a>

			<div class="headingrow">
				<a class="sort" id="sort2"><?php _e( 'Otsikko', 'hri' ); ?></a>
				<a class="sort" id="sort4"><?php _e( 'Viestit', 'hri' ); ?></a>
				<a class="sort desc" id="sort7"><?php _e( 'Viimeisin viesti', 'hri' ); ?></a>
			</div>

			<div class="searching"></div>

			<div class="result-discussions"></div>
		</div>
	</div>

</div>

<?php get_footer(); ?>
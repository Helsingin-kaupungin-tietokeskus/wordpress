<?php
/**
 * Template name: data search
 */

//wp_enqueue_script( 'jquery.ui.autocomplete.HRI', get_bloginfo( 'template_url' ) . '/js/jquery.ui.autocomplete.HRI.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
// Required to allow HTML in source labels for autocomplete.
wp_enqueue_script( 'jquery.ui.autocomplete.html', get_bloginfo( 'template_url' ) . '/js/jquery.ui.autocomplete.html.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
// Include this jQuery UI style to hide ui-helper-hidden-accessible class elements.
wp_enqueue_style( 'wp-jquery-ui-dialog' );

get_header();

?><script type="text/javascript">
// <!--

var clickSleep = false;
var page = <?php
	if(!isset($_GET['searchpage']) || empty($_GET['searchpage']) || !ctype_digit($_GET['searchpage'])) {
		echo 1;
	} else {
		echo $_GET['searchpage'];
	}
?>;

$(document).ready(function($) {

	if(!String.prototype.trim) {
		String.prototype.trim = function () {
			return this.replace(/^\s+|\s+$/g,'');
		};
	}

	var cached_result = new Array( false, false ),
		changed = false,
		$delete_all = $('#delete-all'),
		hashString,
		$hri_cancel = $('.hri-cancel'),
		names_pending = false,
		old_value = '',
		post_tags = [],
		$search = $('#search'),
		search_changed = false,
		searchString,
		searchTimer,
		selected_autocomplete_string,
		url = "<?php echo ROOT_URL;

	if (ORIGINAL_BLOG_ID == 2) echo '/fi';
	if (ORIGINAL_BLOG_ID == 3) echo '/en';
	if (ORIGINAL_BLOG_ID == 4) echo '/se';

	?>/wp-admin/admin-ajax.php",
		xhr_search,
		xhr_options;

	if( window.location.hash.length > 1 ) {

		var params=window.location.hash.substr(1).split('&');
		var paramname='';
		var paramvalues;

		for (var i = 0; i < params.length; ++i) {

			paramname=params[i].substr(0, params[i].indexOf('=') );
			if( paramname.length>0 ) {

				if( paramname == 'search_text' ) {

					$search.val( decodeURIComponent( params[i].substr(params[i].indexOf('=')+1) ) );

				} else {

					paramvalues=params[i].substr(params[i].indexOf('=')+1).split(',');

					for (var b = 0; b < paramvalues.length; ++b) {

						if( paramvalues[b].length > 0 ) $('#box_'+paramname+' .filters').append( $('<a class="filter name-pending" data-value="'+decodeURIComponent( paramvalues[b] )+'"></a>') );
						names_pending = true;

					}
				}
			}
		}
	}
	
<?php

	$vars = array('tags', 'area', 'category', 'filetype', 'producer');

	switch_to_blog(1);

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
	$("#box_<?php echo $name; ?> .filterlink").addClass('filters_on').find('span').text("<?php _e('Muuta','hri'); ?>");
	$("#box_<?php echo $name; ?> .filters").append('<a class="filter" data-value="<?php echo ( isset($term->term_id) ) ? $term->term_id : $val; ?>"><?php echo ( isset($term->name) ) ? $term->name : $val; ?></a>');
	$("#box_<?php echo $name; ?> .options a.opt[data-value=\"<?php echo $val; ?>\"]").addClass('ao');
				<?php
				}
			}
		}
	}

	restore_current_blog();

	?>

	//http://stackoverflow.com/questions/263743/how-to-get-caret-position-in-textarea
	function getCaret() {

		var el = document.getElementById('search');

		if (el.selectionStart) {
			return el.selectionStart;
		} else if (document.selection) {
			el.focus();

			var r = document.selection.createRange();
			if (r == null) {
				return 0;
			}

			var re = el.createTextRange(),
				rc = re.duplicate();
			re.moveToBookmark(r.getBookmark());
			rc.setEndPoint('EndToStart', re);

			return rc.text.length;
		}
		return 0;
	}

	function do_filter( option ) {

		option.clone().appendTo(
			option.parent().parent().find('.filters')
		).removeClass('opt ao').addClass('filter').attr('id', 'f_'+option.attr('id') );

		$('#f_'+option.attr('id')).children('span').remove();

	}

	function _doSearch(data) {

		$('.rescount, #new-data-req').remove();

		document.getElementById('results_data_result').innerHTML = data;
		$('#rescount').hide().prependTo( $('#rescount_c')).fadeIn( 150 );
		$('#results_data').children('.searching').fadeOut(250).siblings('.result').slideDown(300);

		var heading_row = $('.headingrow');

		if( $('.no_results').size() > 0 ) {

			heading_row.slideUp();

			$( '<a href="<?php echo  NEW_DATA_REQUEST_URL; ?>" class="icon-wish no-results"></a><a href="<?php echo NEW_DATA_REQUEST_URL; ?>"><?php _e( 'Toivo uutta dataa', 'hri' ); ?></a>').appendTo( $('#results')).fadeIn();



			/*
			$( '<a id="new-data-req" class="plus-link left" href="<?php echo NEW_DATA_REQUEST_URL; ?>"><?php _e( 'Toivo uutta dataa', 'hri' ); ?></a>' ).appendTo( $('#results') ).fadeIn();
			 */


		} else {

			heading_row.slideDown();

		}

	}

	function doSearch() {

		search_changed = false;

		$('#new-data-req').fadeOut( 100, function(){
			$(this).remove();
		});

		doSearchString();

		$('.result').hide();
		$('.rescount').remove();
		$('#results>div, .searching').show();

		$.cookie("searchString", null);
		$.cookie("searchString", searchString.replace(/tag=/g, "tags=").replace(/ /g, "%20"), { path: '/' });
		$.cookie("searchType", "data", { path: '/' });

		if( cached_result[0] && searchString == '&sort=-1' ) {

			_doSearch(cached_result[0]);

		} else {

			if( typeof _gaq !== 'undefined' ){
				_gaq.push(['_trackEvent', 'Search', 'Data', searchString]);
			}

			xhr_search = $.ajax({
				type: 'POST',
				url: url,
				data: {
					action: "hri_search",
					search_string: 'data|<?php echo HRI_LANG; ?>|'+searchString,
					locale: '<?php echo get_locale(); ?>'
				},
				dataType: 'html',
				success: function(data) {

					if( searchString == '&sort=-1' ) {
						cached_result[0] = data;
					}

					_doSearch(data);

				}
			});

		}

	}

	$search.change(function(e){

		page = 1;
		if( $search.val() != old_value ){
			search_changed = true;
			old_value = $search.val();
		}

		doSearchString();

	}).blur(function(){

		if( search_changed ) {
			doSearch();
		}

	});

	function closePopup() {

		$('#search-filters').css({ height : 'auto' });
		$('#overlay').fadeOut(400, function(){
			$('.above_overlay').removeClass('above_overlay');
		});
		$('.options').hide();

		if ( page > 1 ) {
			page = 1;
			changed = true;
		}

		if ( changed ) {
			update_options();
			doSearch();
		}

		$('.filterbox').removeClass('overlay_open');

		check_things();

		anim_filter_boxes();

	}

	function doSearchString() {

		var comma = false;
		$search.val( $search.val().replace(/"/g,"") );

		searchString = '';
		hashString = '';

		if( $search.val().length > 0 ) {

			searchString += 'search_text=' + $search.val().replace(/,/g, '\\,');
			hashString += 'search_text=' + encodeURIComponent( $search.val() );

		}

		$('.filterbox').has('.filter').each( function() {

			comma = false;

			searchString += '&' + $(this).attr('id').substr(4) + '=';
			hashString += ( hashString.length > 0 ? '&' : '' ) + $(this).attr('id').substr(4) + '=';

			$(this).find('.filter').each( function() {

				if ( $(this).attr('data-value').length > 0 ) {

					searchString += $(this).attr('data-value') + ',';

					if( comma ) hashString += ',';
					hashString += encodeURIComponent( $(this).attr('data-value') );
					comma=true;

				}

			});

		});

		var sort_sel = $('.asc, .desc');

		if( sort_sel.size() > 0 ) {
			var sort = sort_sel.attr('id').substr(4);
			if( sort_sel.hasClass('desc') ) sort = parseInt( sort ) * -1;

			searchString += '&sort='+sort;
			if( sort != -1 ) hashString += '&sort='+sort;

		}

		if( page != 1 ) {
			searchString += "&searchpage="+page;
			hashString += '&page='+page;
		}

		if( hashString.length > 0 ) {

			if( hashString.substr( 0,1 ) == '&' ) hashString = hashString.substr( 1 );

			window.location.hash = hashString;

		} else {

			window.location.hash = '';

		}

		check_things();

	}

	function update_pending_names( box, id, value, text ) {

		var a = $('#box_'+box+' a.name-pending[data-value="'+value+'"]');
		if(a.size() == 0 ) return false;

		a.removeClass( 'name-pending' ).addClass( 'filter').attr( 'id', 'f_opt'+id ).text( text );
		return true;

	}

	function _update_options(data){

		$('.opt').remove();
		post_tags.length=0;

		var c1,c2,c3,databox,i=0,opttxt,updated=false;
		for( var box in data ) {
			if(data.hasOwnProperty(box)) {
				databox = data[box];
				for(var option in databox) {
					if( databox.hasOwnProperty(option) ) {
						if( typeof( databox[option][0] ) != 'undefined' ) {

							opttxt = ( typeof( databox[option][3] ) == 'undefined' ) ? databox[option][2] : databox[option][3];

							if( names_pending ) updated = update_pending_names( box, i, databox[option][2], opttxt );

							c1 = (databox[option][0] == 0) ? ' eo' : '';
							c2 = (databox[option][1] == 1 || updated) ? ' ao' : '';
							// ie-fix
							c3 = (c1 != '' && c2 != '') ? ' eoao': '';

							if( box != 'post_tag' ) {

								$('#box_'+box).children('.options').append( $('<a id="opt'+i+'" class="opt'+c1+c2+c3+'" data-value="'+databox[option][2]+'">'+opttxt+'<span>'+databox[option][0]+'</span></a>') );

							} else {

								if( databox[option][0] > 0 ) {
									post_tags.push({
										label : opttxt+'<span>'+databox[option][0]+'</span>',
										text  : opttxt,
										value : databox[option][2]
									});
								}
							}
							++i;
						}
					}
				}
			}
		}

		$search.autocomplete( "option", {

			source : function( request, response ){

				var request_term = '';
				var pos = getCaret();
				var next_space = request.term.indexOf( ' ', pos );

				if( next_space >= 0 ) {

					request_term = request.term.substr( 0, next_space);

					var prev_space = request_term.lastIndexOf( ' ' );
					if( prev_space < 0 ) prev_space = 0;

					request_term = request_term.substr( prev_space ).trim();

				} else {

					request_term = request.term.split(/\s+/g).pop();

				}

				selected_autocomplete_string = request_term;

				if( request_term.length > 1 ) response( $.ui.autocomplete.filter( $.map( post_tags, function( item ) {

					if( $('#box_post_tag').find('.filter[data-value="'+item.value+'"]').size() == 0 ) {

						return {
							label: marker(item.label, request_term),
							text : item.text,
							value: item.value
						};

					} else return false;

				} ), request_term) );

				/*
				 * to limit results, change line above to:

				} ), request_term).slice(0, 10) );

				 */

				else response( $.ui.autocomplete.filter( [], request_term ) ); // Don't show list for empty term

			}

		});

		anim_filter_boxes();

	}

	function update_options(){

		doSearchString();

		if( cached_result[1] && searchString == '&sort=-1' ) {

			_update_options(cached_result[1]);

		} else {

			xhr_options = $.ajax({
				type: 'POST',
				url: url,
				data: {
					action: "hri_search",
					search_string: 'search|<?php echo HRI_LANG; ?>|'+searchString,
					page: page,
					locale: '<?php echo get_locale(); ?>'
				},
				dataType: 'json',
				success: function(data) {

					if( searchString == '&sort=-1' ) {
						cached_result[1] = data;
					}

					_update_options(data);

				}
			});

		}

	}

	function check_things(){

		var $box_post_tag = $('#box_post_tag');
		
		if( $box_post_tag.find('.filter').size() > 0 ) {
			$box_post_tag.find('h5').fadeIn();
		} else {
			$box_post_tag.find('h5').fadeOut();
		}
		
		if( $search.val().length > 0 ) {
			$hri_cancel.fadeIn();
		} else {
			$hri_cancel.fadeOut();
		}

		$('.filterbox').each(function(){
			if( $(this).find('.filter').size() > 0 ){

				$(this).find('.filterlink').addClass('filters_on').find('span').text('<?php _e( 'Muuta', 'hri' ); ?>');

			} else {

				$(this).find('.filterlink').removeClass('filters_on').find('span').text('<?php _e( 'Ei suodatusta', 'hri' ); ?>');

			}
		});

		( $('.filter').size() > 0 || $search.val().length > 0 ) ? $delete_all.fadeIn() : $delete_all.fadeOut();
		
	}

	function marker(a, b) {
		var matcher = new RegExp("("+$.ui.autocomplete.escapeRegex(b)+")", "ig" );
		return a.replace(matcher, "<em>$1</em>");
	}

	$search.autocomplete({
		minLength: 2,
		source	: post_tags,
		html	: true,
		focus	: function() {
			return false;
		},
		select	: function( event, ui ) {

			if ( typeof( ui.item.value ) != "undefined" ) {

				var filter = '<a class="filter" style="display:none" data-value="'+ui.item.value+'">'+ui.item.text+'</a>';
				$(filter).appendTo( $('#box_post_tag').find('.filters')).delay(250).fadeIn(250);
				check_things();

				var txt = $search.val();
				var pos = getCaret();
				var start, end;

				start = txt.substr(0,pos);
				end = txt.substr(pos);

				start = start.substr( 0, start.lastIndexOf( ' ' ) );

				$search.val( start+end );

				anim_filter_boxes();
				update_options();
				doSearch();

			} else {

				return false;

			}

			return false;

		}
	});

	$hri_cancel.click(function(){

		if(typeof xhr_search != 'undefined') xhr_search.abort();
		if(typeof xhr_search != 'undefined') xhr_options.abort();

		$(this).fadeOut();

		$search.val('');
		old_value = '';
		update_options_and_doSearch();

	});

	$delete_all.click(function(){

		if(typeof xhr_search != 'undefined') xhr_search.abort();
		if(typeof xhr_options != 'undefined') xhr_options.abort();

		page = 1;
		$search.val('');
		old_value = '';
		$('.filter').remove();
		$('.ao').removeClass('ao');
		$('.filterlink').find('span').text('<?php _e( 'Ei suodatusta', 'hri' ); ?>');

		anim_filter_boxes();

		update_options_and_doSearch();

	});

	$(document).on( 'click', '.opt', function(){

		changed = true;

		$(this).toggleClass('ao');

		if ($(this).hasClass('ao')) {
			// add filter
			do_filter($(this));
		} else {
			// remove filter
			$(this).closest('.filterbox').find('.filter[data-value="'+$(this).attr('data-value')+'"]').remove();
		}

	});

	$(document).on( 'click', '.filter', function(){

		// Removing filter also removes matching .opt's .ao class
		var val = $(this).attr('data-value');

		$(this).parent().parent().parent().find('.opt[data-value="'+val+'"]').removeClass('ao');

		$(this).fadeOut(300, function() {

			$(this).remove();
			check_things();

			page = 1;

			clearTimeout( searchTimer );
			searchTimer = setTimeout( update_options_and_doSearch, 600 );

			anim_filter_boxes();

		});
	});

	function update_options_and_doSearch(){
		update_options();
		doSearch();
	}

	$('#overlay, .options-ok').click( function() { if(clickSleep) { return false; } closePopup(); });

	$('a.filterlink').click( function() {

		if(clickSleep) { return false; }

		if ( $(this).hasClass('above_overlay') ) {

			closePopup();

		} else {

			// lock .searchboxwrap height
			$(this).parent().parent().css({ 'height' : $(this).parent().parent().css('height') });
			$(this).parent().addClass('overlay_open');

			changed = false;
			$(this).addClass('above_overlay');
			$('#overlay').fadeTo( 400, 0.6 );
			$('.options').hide();

			$(this).next().show();
			clickSleep = true;

			setTimeout( function() {
				clickSleep = false;
			}, 250);
		}
	});

	$('#data-search').submit(function(){

		update_options_and_doSearch();
		return false;

	});

	<?php hri_js_sort_options(); ?>

	function anim_filter_boxes(){

		$('.filters_wrap').each(function(){
			$(this).css({ height : $(this).children('.filters').height() });
		});

	}

	$('a.pagenum').live('click', function(e) {

		e.preventDefault();

		page = parseInt( $(this).attr('id').substr(2), 10);
		doSearch();

		return false;

	});

	$('.pager a.next').live('click', function(e) {

		e.preventDefault();

		var lastpage = parseInt( $('.pager').find('.pagenum').last().attr('id').substr(2), 10);

		if ( page < lastpage ) {
			++page;
			doSearch();
		}

		return false;

	});

	$('.pager a.previous').live('click', function(e) {

		e.preventDefault();

		if ( page > 1 ) {
			--page;
			doSearch();
		}

		return false;

	});

	setTimeout( anim_filter_boxes, 50 );

	update_options();
	doSearch();

});
// -->
</script>
<div class="column full no-tb">
	<h1><?php _e( 'Hae dataa', 'hri' ); ?></h1>

	<form id="data-search">

		<a id="delete-all"><div><?php _e( 'Tyhjennä kaikki hakukriteerit', 'hri' ); ?></div></a>

		<div class="hri-search">
			<input id="search" class="hri-input" type="text" placeholder="<?php _e( 'Syötä hakusanat...', 'hri' ); ?>" value="<?php

				if( isset( $_GET['text'] ) ) echo esc_html( stripslashes( $_GET['text'] ) );

			?>" />
			<a class="hri-cancel"></a>
			<input class="hri-submit" type="submit" />
		</div>

		<div class="filterboxes" id="search-filters">

			<div class="filterbox" id="box_post_tag">
				<div class="filters_wrap">
					<div class="filters clearfix"><h5><?php _e( 'Avainsanat', 'hri' ); ?></h5></div>
				</div>
			</div>

			<div class="filterbox clear" id="box_area">
				<h5><?php _e( 'Alue', 'hri' ); ?></h5>
				<a class="filterlink"><span><span><?php _e( 'Ei suodatusta', 'hri' ); ?></span></span><div class="circle-down"></div></a>

				<div class="options clearfix"><a class="options-ok">OK</a></div>
				<div class="filters_wrap"><div class="filters clearfix"></div></div>
			</div>

			<div class="filterbox" id="box_category">
				<h5><?php _e( 'Kategoria', 'hri' ); ?></h5>
				<a class="filterlink"><span><?php _e( 'Ei suodatusta', 'hri' ); ?></span><div class="circle-down"></div></a>

				<div class="options clearfix"><a class="options-ok">OK</a></div>
				<div class="filters_wrap"><div class="filters clearfix"></div></div>
			</div>

			<div class="filterbox" id="box_filetype">
				<h5><?php _e( 'Tiedostomuoto', 'hri' ); ?></h5>
				<a class="filterlink"><span><?php _e( 'Ei suodatusta', 'hri' ); ?></span><div class="circle-down"></div></a>

				<div class="options clearfix"><a class="options-ok">OK</a></div>
				<div class="filters_wrap"><div class="filters clearfix"></div></div>
			</div>

			<div class="filterbox lastbox" id="box_producer">
				<h5><?php _e( 'Ylläpitäjä', 'hri' ); ?></h5>
				<a class="filterlink"><span><?php _e( 'Ei suodatusta', 'hri' ); ?></span><div class="circle-down"></div></a>

				<div class="options clearfix"><a class="options-ok">OK</a></div>
				<div class="filters_wrap"><div class="filters clearfix"></div></div>
			</div>

			<div class="clear"></div>
			<div id="overlay"></div>
		</div>
	</form>

	<div id="results">
		<div id="rescount_c"></div>
		<div id="results_data"><a id="data"></a>
			<div class="headingrow">

				<a class="sort" id="sort2"><?php _e( 'Otsikko', 'hri' ); ?></a>
				<a class="sort" id="sort1"><?php _e( 'Päivämäärä', 'hri' ); ?></a>
				<a class="sort" id="sort4"><?php _e( 'Arvosana', 'hri' ); ?></a>
				<a class="sort" id="sort5"><?php _e( 'Kommentit', 'hri' ); ?></a>
				<a class="sort" id="sort6"><?php _e( 'Keskustelut', 'hri' ); ?></a>
				<a class="sort" id="sort7"><?php _e( 'Viimeisin sovellus', 'hri' ); ?></a>

			</div>

			<div class="searching"></div>
			<div class="result" id="results_data_result"></div>

		</div>
	</div>

</div>
<?php

get_footer();

?>
<?php

/*
 * Secondary functions (formatting, etc) for HRI 2.0.
 *
 * Only functions to this file. WP hooks and function calls to functions.php
 */

/**
 * @param string $string
 * @param int $n
 * @param string $append
 * @return string
 */
function n_words( $string, $n, $append = '&hellip;' ) {
	$words = explode( ' ', $string );
	if ( count( $words ) > $n ) {
		$words = array_splice( $words, 0, $n );
		$string = implode(' ', $words);
		if($append) $string .= $append;
	}
	return $string;
}

/**
 * @param string $string
 * @param int $n
 * @param int $hard_truncate
 * @param string $append
 * @return mixed
 *
 * Truncates string at next space after $n chars. If $hard_truncate is given, string will be truncated after given chars even in middle of a word.
 */
function n_chars( $string, $n, $hard_truncate = 0, $append = '&hellip;' ) {

	if( strlen( $string ) <= $n ) return $string;

	$string = mb_substr( $string, 0, strpos( $string, ' ', $n ) );

	if( $hard_truncate > 0 && strlen( $string ) > $hard_truncate ) {
		$string = mb_substr( $string, 0, $hard_truncate );
	}

	if( $append ) $string .= $append;
	return $string;

}

/*
 * JavaScript functions for data and tags autocomplete
 *
 * Used in page-new-*.php
 */

function hri_js_data_autocomplete() { ?>

	function datastring() {
		var datastring = '';
		$('#data_filters .filter').each( function() {
			datastring += $(this).attr('data-id')+',';
		});
		$('#datastring').val( datastring );
	}

	datastring();

	$( "#data" ).autocomplete({
		source: function(request, response) {
			$.ajax({
				url: url,
				dataType: "json",
				data: {
					action: "get_data_titles",
					search_string: request.term,
					not: $('#datastring').val()
				},

				success: function(data) {
					// Hide the loading image
					$( "#datalink" ).css("background", "#F9F9F9");
					if (data.length == 0 ){
						response(["<?php _e('Ei hakutuloksia','hri'); ?>"]);
					}
					else{
						response( $.map( data, function( item ) {
							return {
								label: item.title,
								id: item.id
							}
						}));
					}

				},
				error: function(data) {
					// response("[<?php _e('Ei hakutuloksia','hri'); ?>]");
					$( "#datalink" ).css("background", "#F9F9F9");
				}
			})
		},
		minLength: 2,
		select: function( event, ui ) {

			if ( typeof( ui.item.id ) != "undefined" ) {

				var filter = '<a class="filter" data-id="'+ui.item.id+'">'+ui.item.label+'</a>';
				$(filter).appendTo( $('#data_filters') );
				datastring();

			} else {
				return false;
			}

			$('#data').val('');
			return false;

		},
		search: function( event, ui ) {
			$( "#datalink" ).css("background", "#F9F9F9 url('<?php echo get_bloginfo('template_url'); ?>/images/ajax-loader_small.gif') no-repeat 98% center");
		},
		close: function(event, ui) {
			$( "#datalink" ).css("background", "#F9F9F9");
		}
	});

	$('#data_filters .filter').live('click', function() {
		$(this).fadeOut(300, function() {
			$(this).remove();
			datastring();
		});
	});

<?php }

function hri_js_tags_autocomplete() { ?>

	function tagstring() {
		var tagstring = '';
		$('#tag_filters .filter').each( function() {
			tagstring += $(this).attr('data-id')+',';
		});
		$('#tagstring').val( tagstring );
	}

	tagstring();

	$( "#tags" ).autocomplete({
		source: function(request, response) {
			$.ajax({
				url: url,
				dataType: "json",
				data: {
					action: "get_tag_names",
					search_string: request.term,
					not: $('#tagstring').val()
				},

				success: function(data) {
					$( "#tags" ).css("background", "#F9F9F9");
					if (data.length == 0 ){
						response(["<?php _e('Ei hakutuloksia','hri'); ?>"]);
					}
					else{
						response( $.map( data, function( item ) {
							return {
								label: item.title,
								id: item.id
							}
						}));
					}

				},
				error: function(data) {
					// response(["<?php _e('Ei hakutuloksia','hri'); ?>"]);
					$( "#tags" ).css("background", "#F9F9F9");
				}
			})
		},
		minLength: 2,
		select: function( event, ui ) {

			if( ui.item.label != "<?php _e('Ei hakutuloksia','hri'); ?>" ) {

				var filter = '<a class="filter" data-id="'+ui.item.id+'">'+ui.item.label+'</a>';
				$(filter).appendTo( $('#tag_filters') );

				tagstring();

			}

			$('#tags').val('');
			return false;

		},
		search: function( event, ui ) {
			$( "#tags" ).css("background", "#F9F9F9 url('<?php echo get_bloginfo('template_url'); ?>/images/ajax-loader_small.gif') no-repeat 98% center");
		},
		close: function(event, ui) {
			$( "#tags" ).css("background", "#F9F9F9");
		}
	});

	$('#tag_filters .filter').live('click', function() {
		$(this).fadeOut(300, function() {
			$(this).remove();
			tagstring();
		});
	});

<?php }

function hri_js_validate_newcontentform() {

?>	$('#newcontentform').submit(function() {
		var error = false;

		if($('#newd_title').val() == "") {
			$('#title_error').html("<?php _e('Täytä kenttä','hri'); ?>");
			error = true;
		}

<?php if ( !is_user_logged_in() ) { ?>
		if($('#newd_username').val() == "") {
			$('#username_error').html("<?php _e('Täytä kenttä','hri'); ?>");
			error = true;
		}
		if($('#newd_useremail').val() == "") {
			$('#email_error').html("<?php _e('Täytä kenttä','hri'); ?>");
			error = true;
		}
		<?php } ?>

		if(error) return false;
	});

<?php }

/*
 * PHP functions for new content
 *
 * Used in page-new-*.php
 */

/**
 * @param int $ID
 * @return void
 */
function hri_set_tags_from_string( &$ID ) {

	$tags = explode( ',', $_POST['tagstring'] );
	if( end($tags) == '' ) array_pop( $tags );

	$tags_to_add = array();

	foreach( $tags as $tag ) {
		$tags_to_add[ (int) $tag ] = true;
	}

	if( !empty( $tags_to_add ) ) wp_set_object_terms( $ID, array_keys( $tags_to_add ), 'post_tag');

}

/**
 * @param int $ID
 * @return void
 */
function hri_set_data_from_string( &$ID ) {

	$datas = explode( ',', $_POST['datastring'] );
	if( end($datas) == '' ) array_pop($datas);

	$datas_to_add = array();

	foreach( $datas as $data ) {
		$datas_to_add[ (int) $data ] = true;
	}

	foreach( array_keys( $datas_to_add ) as $data ) {
		add_post_meta( $ID, '_link_to_data', $data );
	}

}

function hri_add_filter() {

	global $wpdb;

	$id = (int) $_REQUEST['linked_id'];

	switch_to_blog(1);
	$data_title = $wpdb->get_var( "SELECT post_title FROM $wpdb->posts WHERE ID = $id;");
	restore_current_blog();

	if ( $data_title ) ?><a class="filter" data-id="<?php echo $id; ?>"><?php echo $data_title; ?></a><?php

}

function hri_js_sort_options() {
?>

	function setSort(el) {
		if ( el.hasClass('asc') || el.hasClass('desc') ){
			el.toggleClass('asc desc');
		} else {
			$('.sort').removeClass('asc desc');
			el.addClass('desc');
		}
	}

//	$('.sortactive').each(function(){
//		var el = $(this);
//		el.find('.sortmark_grey').addClass('sortmark').removeClass('sortmark_grey sortreverse');
// 	});

	$('.sort').click(function(){
		page = 1;
		setSort( $(this) );

        //if ($('#search-and-browse').attr('data-current-view') == 'search' || $('#search-and-browse').attr('data-current-view') == 'init' ) {
            doSearch();
        //} else {
            //doSearchVis(currentArea, currentCategory);
        //}
	});

<?php

	if( isset( $_GET['sort'] ) ) $sort = (int) $_GET['sort'];
	else $sort = -1;

	if ( $sort == 0 ) $sort = -1;

	if ( $sort < 0 ) {

		$sort *= -1;
		$class = 'desc';

	} else {

		$class = 'asc';

	}

	?>
	$('#sort<?php echo $sort; ?>').addClass('<?php echo $class; ?>');
<?php

}


/* -------------------------------------------------------------------------------------------------------------------------------- data with language title */

/**
 * @param string $lang
 * @param bool $echo
 * @return bool|string
 */
function data_title( $lang = HRI_LANG, $echo = true ) {

	global $post;
	$title = false;

	if ( $lang == 'en' ) {

		$title = get_post_meta( $post->ID, 'extras_title_en', true );

	} elseif ( $lang == 'se' ) {

		$title = get_post_meta( $post->ID, 'extras_title_se', true );

	}

	if ( !$title ) $title = get_the_title();

	if ( $echo === true ) {

		echo $title;
		return true;

	}
	else return $title;

}

/**
 * @param bool $echo
 * @param bool $breaklines
 * @param bool $lang
 * @param int $post_id
 * @return bool|string
 */
function notes( $echo = true, $breaklines = true, $lang = false, $post_id = 0 ) {

	$notes = false;

	if( !$post_id ) {

		global $post;
		$post_id = $post->ID;

	}

	if (ORIGINAL_BLOG_ID == 3 || $lang == 'en' ) $notes = get_post_meta( $post_id, 'extras_notes_en', true );
	if (ORIGINAL_BLOG_ID == 4 || $lang == 'se' ) $notes = get_post_meta( $post_id, 'extras_notes_se', true );
	if (ORIGINAL_BLOG_ID == 2 || $lang == 'fi' || !$notes ) $notes = get_post_meta( $post_id, 'notes', true );

	if($breaklines) $notes = nl2br( $notes );

	if ($notes) {

		if ($echo) {

			echo $notes;
			return true;

		}

		else return $notes;

	} else return false;

}

function links_in_text( $string, $reg = '~((?:https?://|www\d*\.)\S+[-\w+&@#/%=\~|])~' ) {

	function parse_links( $m ) {

		$link_limit = 60;
		$link_format = '<a href="%s" rel="external">%s</a>';
		$href = $name = html_entity_decode($m[0]);

		if ( strpos( $href, '://' ) === false ) {
			$href = 'http://' . $href;
		}

		if( strlen($name) > $link_limit ) {
			$k = ( $link_limit - 3 ) >> 1;
			$name = substr( $name, 0, $k ) . '...' . substr( $name, -$k );
		}

		return sprintf( $link_format, htmlentities($href), htmlentities($name) );
	}

	return preg_replace_callback( $reg, 'parse_links', $string );
}

/**
 * @param string $date
 */
function hri_time_since( $date ) {

	if( function_exists( 'time_since' ) ) {

		echo '<span title="', $date, '">', time_since( strtotime( $date ) ), '</span> ', __('sitten', 'hri');

	} else {

		echo '<span title="', $date, '">', $date, '</span>';

	}

}

function hri_make_short_link($url) {

	if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) $url = 'http://' . $url;

	if ( strlen( $url ) > 50 ) $short_url = substr( $url, 0, 50 ) . '&hellip;';
	else $short_url = $url;

	$tag = "<a class=\"singlerow\" target=\"_blank\" title=\"$url\" href=\"$url\">$short_url</a>";

	return $tag;

}

function hri_check_query_params() {

	global $query_string;
	parse_str( $query_string, $args );

	$loop_part = null;

	if ( isset( $args['postname'] ) && isset( $args['posttype'] ) ) {

		switch_to_blog(1);

		query_posts( 'post_type=' . $args['posttype'] . '&name=' . $args['postname'] );

		if ( have_posts() ) {

			get_template_part( 'single', $args['posttype'] );

		} else {

			global $wp_query;
			$wp_query->set_404();

			get_template_part( '404' );

		}

		exit;

	} elseif ( isset( $args['posttype'] )) {

		switch_to_blog(1);

		if ( $args['posttype'] == 'application' ) {

			$query_args = array(
				'post_type' => 'application',
				'order' => 'DESC',
				'orderby' => 'date',
				'posts_per_page' => -1
			);

			if ( isset($args['appcat']) ) {

				$app_cats = explode( '/', $args['appcat'] );

				$query_args['tax_query'] = array(
					array(
						'taxonomy' => 'hri_appcats',
						'field' => 'slug',
						'terms' => end( $app_cats )
					)
				);

			};

			query_posts( $query_args );
			$loop_part = 'apps';


		} else {
			query_posts( 'post_type=' . $args['posttype'] );
			$loop_part = '';
		}

	}

}

/**
 * @param bool $top_border
 */
function hri_add_this( $top_border = false ) {

	?>
<div class="addthis_toolbox addthis_default_style<?php if( $top_border ) echo ' top-border'; ?>">
	<div class="inside">
		<a class="addthis_button_facebook_like" <?php /*fb:like:layout="button_count"*/ ?>></a>
		<a class="addthis_button_tweet"></a>
		<a class="addthis_button_email"><?php _e( 'Sähköposti', 'hri' ); ?></a>
	</div>
</div>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4d793e5d41cc89d9"></script>
<?php

	if (HRI_LANG == 'fi') { ?><script>var addthis_config = {ui_language:"fi"}</script><?php }

}

function hri_rating( $count = false ) {

	global $post,$wpdb;

	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts p INNER JOIN ({$wpdb->prefix}comments c, {$wpdb->prefix}commentmeta m) ON ( p.ID = c.comment_post_ID AND c.comment_ID AND c.comment_ID = m.comment_ID AND meta_key LIKE '\_hri\_rating_' ) WHERE p.ID = {$post->ID} AND c.comment_approved = 1" );

	$avg = 0;
	$i = 0;

	if ( $results ) {

		$r = array();
		foreach ( $results as $result ) {

			if (  $result->meta_key == '_hri_rating1' ) $r[1][] = (int) $result->meta_value;
			if (  $result->meta_key == '_hri_rating2' ) $r[2][] = (int) $result->meta_value;
			if (  $result->meta_key == '_hri_rating3' ) $r[3][] = (int) $result->meta_value;
			$i++;

		}
		if ( $i > 0 ) {
			$sum = array_sum( $r[1] ) + array_sum( $r[2] ) + array_sum( $r[3] );
			$avg = $sum / $i;
		}
	}

	if ( $count && $i ) {
		echo '<div class="ratecount">(' . ($i / 3) . ')</div> <div class="ratingbg"><div title="' . round( $avg, 1 ) . '/5" class="rating" style="width:' . round( $avg * 20, 0) . '%"></div></div>';
	} else {
		echo '<div class="ratingbg"><div title="' . round( $avg, 1 ) . '/5" class="rating" style="width:' . round( $avg * 20, 0) . '%"></div></div>';
	}

}

/**
 * @param string $url
 * @param string $lang
 * @param null|string $datatype
 * @param bool $leaveDate
 * @return mixed
 */
function hri_link( $url, $lang = HRI_LANG, $datatype = null, $leaveDate = false, $post = null) {

	if ( isset($datatype) ) {

		$url = str_replace( '/blog/', "/$lang/$datatype/", $url );

		// For CKAN-URLs we need to remove the last '/'.
		if($datatype == 'dataset') { 

			$url = str_replace( "/{$lang}/dataset/data/", "/data/", $url );
			$url = str_replace( '/data/', "/{$lang}/dataset/", $url );
			$url = substr($url, 0, -1);

			// Bugfix for HRI-134: before, only problematic URLs (ones with dashes or hyphens) were corrected
			//                     using ckan_url, but the correction is required also for URLs that are
			//                     changed manually and not derived from the title. So from now on we try to 
			//                     create all dataset links like this.
			if(is_integer($post)) { $post = get_post($post); }
			if(!is_object($post)) { global $post; }
			if(is_object($post) && !empty($post->ID)) {

				// Find where to cut the old title off.
				$pos = strpos($url, "/{$lang}/dataset/");
				$pos += strlen("/{$lang}/dataset/");

				// Dig the post's info for the correct one and add it.
				$ckan_url = get_post_meta($post->ID, 'ckan_url', true);
				$pos2 = strpos($ckan_url, "/dataset/");
				$pos2 += strlen("/dataset/");

				$url = substr($url, 0, $pos) . substr($ckan_url, $pos2);
			}
		}
		else {

			global $pattern_url;

			if( !preg_match( $pattern_url, $url ) ) {
				$url = str_replace( ROOT_URL.'/', ROOT_URL."/$lang/", $url);
			}
		}
	} else {
		$url = str_replace( '/blog/', "/$lang/", $url );
	}

	if ($lang == 'fi') {
		$url = str_replace( 'discussions', 'keskustelut', $url );
		$url = str_replace( 'applications', 'sovellukset', $url );
		$url = str_replace( 'application-ideas', 'sovellusideat', $url );
		$url = str_replace( 'data-requests', 'datatoiveet', $url );
		// HRI-115: for some reason the string below haunts the Finnish link.
		$url = str_replace( 'application/', '', $url );
	}

	// Remove date ( 1111/11/11 ) from link
	if ( !$leaveDate ) $url = preg_replace( '(\d{4}\/\d{2}\/\d{2}\/)', '', $url );

	return $url;

}

/**
 * @param int $page
 * @param string $url
 * @param int $result_count
 * @param int $per_page
 * @param boolean $as_param
 * @return boolean|string
 */
function hri_pager($page, $url = '', $result_count, $per_page = 10, $as_param = true) {

	$pager = "<div class='pagerwrap'><div class='pager'><!-- ".$url." -->";

	if( $as_param ) {

		if($url != '') $url .= '&amp;';
		$path = '?' . $url . 'searchpage=';
		$trailing_slash = false;

	} else {

		$path = $url . 'page/';
		$trailing_slash = '/';

	}

	if( $page == 1 ) {
		$pager .= '<a class="previous pagedisabled"></a>';
	} else {
		$pager .= '<a href="'.$path.($page-1).$trailing_slash.'" class="previous"></a>';
	}

	$pageCount = (int) ceil( $result_count / $per_page );

	if($pageCount < 2) return false;
	if ($pageCount < 4) {
		for($i = (($page <= 3)?1:$page-3); $i < (($pageCount > $page+4)?$page+4:$pageCount+1); $i++) {
			if($i == $page) {
				$pager .= ' <strong class="curpage">'.$i.'</strong> ';
			} else {
				$pager .= ' <a href="'.$path.($i).$trailing_slash.'" class="pagenum" id="pn'.$i.'">'.$i.'</a> ';
			}
		}
	} else {
		$first_show = false;
		$max_page = 0;
		if ($page > 4 ) {
			$pager .= ' <a href="'.$path.'1'.$trailing_slash.'" class="pagenum" id="pn1">1</a>';
			$pager .= ' <a href="'.$path.'2'.$trailing_slash.'" class="pagenum" id="pn2">2</a>';
			if ($page > 7) {
				$pager .= ' <div class="divider">&hellip;</div>';
			} elseif( $page == 7 ) {
				$pager .= ' <a href="'.$path.'3'.$trailing_slash.'" class="pagenum" id="pn3">3</a>';
			}
			$first_show = true;
		}
		for($i = (($page <= 3)?1:$page-3); $i < (($pageCount > $page+4)?$page+4:$pageCount+1); $i++) {
			if (($i == 1 || $i == 2) && $first_show) {
				continue;
			}
			if($i == $page) {
				$pager .= ' <strong class="curpage">'.$i.'</strong>';
			} else {
				$pager .= ' <a href="'.$path.($i).$trailing_slash.'" class="pagenum" id="pn'.$i.'">'.$i.'</a>';
			}
			$max_page = $i;
		}
		if ($max_page < $pageCount) {
			if( $max_page + 2 < $pageCount - 1 ) $pager .= ' <div class="divider">&hellip;</div>';
			elseif( $max_page + 1 == $pageCount - 2 ) $pager .= ' <a href="'.$path.($pageCount-2).$trailing_slash.'" class="pagenum" id="pn'.($pageCount-2).'">'.($pageCount-2).'</a>';

			$pager .= ' <a href="'.$path.($pageCount-1).$trailing_slash.'" class="pagenum" id="pn'.($pageCount-1).'">'.($pageCount-1).'</a> ';
			$pager .= ' <a href="'.$path.$pageCount.$trailing_slash.'" class="pagenum" id="pn'.$pageCount.'">'.$pageCount.'</a> ';
		}
	}

	if( $page == $pageCount ) {
		$pager .= '<a class="next pagedisabled"></a>';
	} else {
		$pager .= '<a href="'.$path.($page+1).$trailing_slash.'" class="next"></a>';
	}

	$pager .= '</div></div>';

	return $pager;
}

function hri_comment_paging(){

	if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) {

		?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Vanhemmat kommentit', 'hri' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Uudemmat kommentit <span class="meta-nav">&rarr;</span>', 'hri' ) ); ?></div>
			</div>

		<?php

	}

}

/**
 * Standard comment
 * @param object $comment
 * @param array $args
 * @param int $depth
 */
function hri_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div class="comment-content" id="comment-<?php comment_ID(); ?>">

		<div class="comment-body"><?php comment_text(); ?><div class="comment-nuoli"></div></div>
		<div class="comment-meta commentmetadata">
			<?php echo get_avatar( $comment, 30 ); ?>
			<span class="name">
				<?php echo get_comment_author_link( (int) $comment->comment_ID ); ?>
				<?php if ( $comment->comment_approved == '0' ) { ?>
				<br />
				<em><?php _e( 'Kommenttisi odottaa hyväksyntää.', 'hri' ); ?></em>
				<?php } ?>
			</span>
			<br />
			<span class="timestamp">
				<?php

				hri_time_since( $comment->comment_date );
				edit_comment_link( __( 'Muokkaa', 'hri' ), ' ' );

				?>
			</span>

			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'class' => 'comment-reply-link' ) ) ); ?>

			<a class="report-comment" id="report-comment-<?php comment_ID(); ?>"><?php _e('Ilmoita asiaton kommentti','hri'); ?></a>
		</div>

	</div>

	<?php
			break;
	endswitch;
}

/**
 * Comment [with ratings]
 * @param object $comment
 * @param array $args
 * @param int $depth
 */
function comments_ratings( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case '' :

			$cID = (int) get_comment_ID();

			$rating1 = (int) get_comment_meta( $cID, '_hri_rating1', true );

			$clang = 'lang-' . get_comment_meta( get_comment_ID(), 'hri_comment_lang', true );

			$rating_class = ( $rating1 ) ? 'comment_type_rating' : 'comment_type_comment';

	?>
	<li <?php comment_class( array($rating_class, $clang) ); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">

		<div class="comment-body">
			<?php if ( $rating1 ) {

$r2 = (int) get_comment_meta( $cID, '_hri_rating2', true );
$r3 = (int) get_comment_meta( $cID, '_hri_rating3', true );

$sum = $rating1 + $r2 + $r3;
$avg = $sum / 3;
echo '<div class="comment_ratings_container comment_ratings_container_wide">';
echo '<div class="rating_container rating_container_floating">Kuvaus: <div class="ratingbg"><div title="' . round( $rating1, 1 ) . '/5" class="rating" style="width:' . round( $rating1 * 20, 0) . '%"></div></div></div>';
echo '<div class="rating_container rating_container_floating">Hyödyllisyys: <div class="ratingbg"><div title="' . round( $r2, 1 ) . '/5" class="rating" style="width:' . round( $r2 * 20, 0) . '%"></div></div></div>';
echo '<div class="rating_container rating_container_floating">Käytettävyys: <div class="ratingbg"><div title="' . round( $r3, 1 ) . '/5" class="rating" style="width:' . round( $r3 * 20, 0) . '%"></div></div></div>';
//echo '<div class="ratingbg"><div title="' . round( $avg, 1 ) . '/5" class="rating" style="width:' . round( $avg * 20, 0) . '%"></div></div>';
echo '</div>';

}  ?>
			<div class="comment-body-content">
				<?php comment_text(); ?>
			</div>
			<div class="clear"></div><div class="comment-nuoli"></div>
		</div>
		<div class="clear"></div>
		<div class="comment-meta commentmetadata">
			<?php echo get_avatar( $comment, 30 ); ?>
			<span class="name">
				<?php echo get_comment_author_link(); ?>
				<?php if ( $comment->comment_approved == '0' ) { ?>
				<br />
				<em><?php _e( 'Kommenttisi odottaa hyväksyntää.', 'hri' ); ?></em>
				<?php } ?>
			</span>
			<br />
			<span class="timestamp">
				<?php

				hri_time_since( $comment->comment_date );
				edit_comment_link( __( 'Muokkaa', 'hri' ), ' ' );

				?>
			</span>

			<a class="report-comment" id="report-comment-<?php comment_ID(); ?>"><?php _e('Ilmoita asiaton kommentti','hri'); ?></a>

			<?php //if ( !$rating1 ) { comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'class' => 'comment-reply-link' ) ) ); }
			comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'class' => 'comment-reply-link' ) ) );
			?>

		</div>

	</div>

	<?php
			break;
	endswitch;
}

/**
 * Comment excerpt / link to full comment
 * @param object $comment
 * @param boolean $virtual Is this a post type discussion displayed as comment (true) or a real wordpress comment (false)?
 * @param boolean $is_discussion
 */
function hri_comment_excerpt($comment, $virtual = false, $is_discussion = false, $post_type = '') {

	$link = hri_link( get_permalink( $comment->comment_post_ID ), HRI_LANG, ($post_type == 'data') ? 'dataset' : '' );
?>
	<div class="comment hri-comment-excerpt">
		<a class="hri-comment-excerpt-link" href="<?php echo $link; if( !$virtual ) { ?>#comment-<?php echo $comment->comment_ID; } ?>">
			<div class="comment-body">
		<?php

			$excerpt = isset( $comment->hri_excerpt ) ? $comment->hri_excerpt : get_comment_excerpt( $comment->comment_ID );
//			$excerpt = n_words( $excerpt, 10 );
			$excerpt = n_chars( $excerpt, 90, 100 );

			if( strlen( $excerpt ) > 1 ) echo '<p>', $excerpt, '</p>';

			if( !$virtual ) {
				$rating1 = (int) get_comment_meta( $comment->comment_ID, '_hri_rating1', true );

				if( $rating1 ) {

					$r2 = (int) get_comment_meta( $comment->comment_ID, '_hri_rating2', true );
					$r3 = (int) get_comment_meta( $comment->comment_ID, '_hri_rating3', true );

					$sum = $rating1 + $r2 + $r3;
					$avg = $sum / 3;

					?><div class="ratings_lift"><div class="ratingbg"><div title="<?php echo round( $avg, 1 ); ?>/5" class="rating" style="width:<?php echo round( $avg * 20, 0); ?>%"></div></div></div><?php

				}
			}

		?>
			<div class="comment-nuoli"></div>
		</div>
			<?php if ($is_discussion) {
			?>
			<div class="comment-meta commentmetadata commentmetadatadiscussion">
				<?php echo get_avatar( $comment, 30 ); ?>
				<div class="discussion_comment_author">
					<cite><?php echo $comment->comment_author; ?></cite>
				</div>
				<span class="timestamp"><?php hri_time_since( $comment->comment_date ); ?></span>
			</div>
			<?php
			} else { ?>
			<div class="comment-meta commentmetadata">
				<?php echo get_avatar( $comment, 30 ); ?>
				<cite><?php echo $comment->comment_author; ?></cite>
				<br />
				<span class="timestamp"><?php hri_time_since( $comment->comment_date ); ?></span>
			</div>
			<?php } ?>
		</a>
	</div>
<?php

}

/**
 * @param string $field
 */
function the_hri_field( $field ){

	if( function_exists( 'the_field' ) ) {
		the_field( $field );
	}

}

function hri_author() {

	global $post;

	?><div class="author clear clearfix"><?php

		echo get_avatar( $post->post_author, 60 );

		?>
		<div class="meta"><?php _e( 'Kirjoittaja', 'hri' ); ?></div>
		<div class="name"><?php echo get_the_author(); ?></div>
		<div><?php hri_time_since( $post->post_date ); ?></div>
	</div>

	<?php

}

/**
 *
 * Echoes article image or the first image from the post content
 *
 * @param string $size
 * @param string $wrapper_class
 * @return bool
 *
 */
function hri_thumbnail( $size = 'thumbnail', $wrapper_class = 'img-to-thumb' ){

	global $post;

	if( function_exists( 'get_video_thumbnail' ) ) {

		$video_thumb = get_video_thumbnail();
		if( is_string( $video_thumb ) && $video_thumb != "" ) {

			?><div class="<?php if( $wrapper_class ) echo $wrapper_class; ?>"><img src="<?php echo $video_thumb; ?>" alt="video"/></div><?php

			return true;

		}

	}

	if( has_post_thumbnail() ) {

		?><div class="<?php if( $wrapper_class ) echo $wrapper_class; ?>"><?php

		the_post_thumbnail( $size );

		?></div><?php

		return true;

	} else {

		$content = get_the_content();
		$img_start = strpos( $content, '<img' );

		if( $img_start !== false ) {

			// check does resized version exists

			$match = array();

			// match value of the first src attribute after opening <img tag
			preg_match( '/src=["\'](.*?)["\']/', substr( $content, $img_start ), $match );
			$file = $match[1];

			$ext = substr( $file, strrpos( $file, '.' ) + 1 );

			// remove protocol and domain name
			$file = str_replace( ROOT_URL, '', $file );
			$file = ABSPATH . 'wp-content/blogs.dir' . str_replace( array( '/fi/', '/en/', '/se/' ), array( '/2/', '/3/', '/4/' ), $file);

			// remove file extension and possible image size
			$file = preg_replace( '/(-[0-9]+x[0-9]+)*\.[0-9a-z]+$/', '', $file );

			if( in_array( $size, array('thumbnail', 'medium', 'large') ) ) {

				$width = get_option( $size.'_size_w' );
				$height = get_option( $size.'_size_h' );

			} else {

				global $_wp_additional_image_sizes;
				$width = $_wp_additional_image_sizes[ $size ][ 'width' ];
				$height = $_wp_additional_image_sizes[ $size ][ 'height' ];

			}

			// add requested image size's dimensions
			$file .= "-{$width}x{$height}.$ext";

			if( file_exists( $file ) ) {

				// requested size does exists

				// remove path and add protocol and domain name
				$file = home_url() . substr( $file, strpos( $file, '/files' ) );

				?><div class="<?php if( $wrapper_class ) echo $wrapper_class; ?>"><img src="<?php echo $file; ?>" alt="" /></div><?php

				return true;

			} else {

				// requested size does not exists: use same image as in the content, possibly scaled down with CSS

				$img_end = strpos( $content, '>', $img_start );
				$len = $img_end - $img_start + 1;

				?><div class="<?php if( $wrapper_class ) echo $wrapper_class; ?>"><?php

					$img_tag = substr( $content, $img_start, $len );

					echo $img_tag

				?></div><?php

				return true;

			}

		}

		return false;

	}

}

function hri_excerpt( $echo = true ){

	$excerpt = get_the_excerpt();

	$remove = array( '<p></p>', '<p> </p>', '<p>&nbsp;</p>' );
	$excerpt = str_replace( $remove, '', $excerpt );

	if( $echo ) {
		echo $excerpt;
		return null;
	}
	else return $excerpt;

}

function hri_report_comment_form() {
?>

<script type="text/javascript">
// <!--
document.write('<?php

	if( ORIGINAL_BLOG_ID == 3) $target = ROOT_URL . '/en/report-comment/';
	else $target = ROOT_URL . '/fi/ilmoita-kommentti/';

	$form = '<div style="display:none" id="report-container"><div id="report-top"></div><h4>' . __('Ilmoita epäasiallinen sisältö','hri') . '</h4><p>' . __('Kerro, miksi tämä sisältö pitäisi mielestäsi poistaa.','hri') . '</p><form id="report-form" action="' . $target .'" method="post"><input type="hidden" name="comment_ID" id="comment_ID" /><textarea rows="5" cols="20" name="reporttext"></textarea><p><label for="report-email">' . __('Sähköpostiosoitteesi','hri') . ':</label> <input type="text" class="text" size="30" name="report-email" id="report-email" /></p><input class="report-comment-submit" type="submit" value="' . __('Lähetä','hri') . '" /></form></div>';

	$form = str_replace('"comment"', '"\'+String.fromCharCode(0x63)+\'omment"', $form);
	echo str_replace('<form', '\'+String.fromCharCode(074)+String.fromCharCode(102)+\'orm', $form);

?>');
jQuery(function($) {
	$('.report-comment').click(function(){
		if( $(this).hasClass('report-cancel') ) {

			$('.report-comment, #respond').show();
			$(this).text('<?php _e('Ilmoita tämä kommentti','hri'); ?>').removeClass('report-cancel').parent().parent().removeClass('report-target');
			$('.comment-reply-link').show();

			$('#report-container').hide();
			$('#comment_ID').val('');

		} else {

			$('.report-comment').not( $(this) ).hide();
			$(this).text('<?php _e('Peruuta','hri'); ?>').addClass('report-cancel').parent().parent().addClass('report-target');
			$('.comment-reply-link, #respond').hide();

			$('#report-container').appendTo( $(this).parent() ).show();
			$('#comment_ID').val( $(this).attr('id').substr(7)+'-'+$('#hri-blog').val() );

		}
	});
});
// -->
</script>
<?php

}

/**
 * @param string $class
 * @return string
 */
function hri_read_more( $class = '' ){

	if( $class != '' ) $class = ' ' . $class;
	return '<a href="' . get_permalink() . '" class="readmore' . $class . '">' . __('Lue lisää', 'hri') . '</a>';

}

function hri_tinymce( $hri_class = false ) {

	wp_enqueue_script( 'tinymce', get_bloginfo('template_url') . '/js/tiny_mce/tiny_mce.js', array('jquery') );

	if( $hri_class ) $GLOBALS['hri_tinymce_class'] = $hri_class;
	add_action( 'wp_head', 'hri_do_tinymce', 11, 1);

}

function hri_do_tinymce() {

	$class = isset( $GLOBALS['hri_tinymce_class'] ) ? $GLOBALS['hri_tinymce_class'] : false;

?><script type="text/javascript">
tinyMCE.init({
	remove_script_host : false,
	convert_urls : false,
	mode : "textareas",
<?php if( $class ) echo "\teditor_selector : \"$class\",\n" ?>
	theme : "advanced",
	theme_advanced_buttons1 : "bold,italic,underline,sub,sup,charmap,separator,link,unlink,separator,bullist,numlist",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
<?php if (ORIGINAL_BLOG_ID == 2) { echo '	language : "fi",'; } ?>
	setupcontent_callback : "myCustomSetupContent"
});
function myCustomSetupContent() {
	tinyMCE.getInstanceById('newd_text').getWin().document.body.style.backgroundColor='#F9F9F9';
}
</script>
<?php

}

function hri_swfupload() {

	wp_enqueue_script( 'swfupload', get_bloginfo('template_url') . '/js/swfupload.js' );
	wp_enqueue_script( 'swfupload_handlers', get_bloginfo('template_url') . '/js/swfupload_handlers.js' );

	add_action( 'wp_head', function() {

		$max_upload = 1; // MB

?><script type="text/javascript">

var swfu;
var hri_zerobyte = "<?php _e('Virhe: Tyhjä tiedosto','hri'); ?>";
var hri_toobig = "<?php _e('Virhe: Suurin sallittu koko ylittyi','hri'); ?>";
var hri_imageprocess = "<?php _e('Virhe: Tiedoston prosessointi epäonnistui','hri'); ?>";
var hri_allimages = "<?php _e('Kaikki kuvat vastaanotettu.','hri'); ?>";
var hri_featuredimage = "<?php _e('Artikkelikuva','hri'); ?>";
var hri_delete = "<?php _e('Poista','hri'); ?>";

window.onload = function () {
	swfu = new SWFUpload({
		// Backend Settings
		upload_url: "<?php echo admin_url(); ?>admin-ajax.php",
		post_params: { action: 'hri_front_upload', "PHPSESSID": "<?php echo session_id(); ?>"},

		// File Upload Settings
		file_size_limit : "<?php echo $max_upload; ?> MB",
		file_types : "*.jpg;*.jpeg;*.gif;*.png",
		file_types_description : "Web Image Files",
		file_upload_limit : "0",

		// Event Handler Settings - these functions as defined in Handlers.js
		//  The handlers are not part of SWFUpload but are part of my website and control how
		//  my website reacts to the SWFUpload events.
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,

		// Button Settings
		button_image_url : "<?php bloginfo('template_url'); ?>/img/upload_plus.png",
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 180,
		button_height: 18,
		button_text : '<span class="button"><?php echo __( 'Valitse kuvat', 'hri' ); ?></span>',
		button_text_style : '.button { font-family: Arial, sans-serif; font-size: 12pt; } .buttonSmall { font-size: 10pt; }',
		button_text_top_padding: 0,
		button_text_left_padding: 18,
		button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
		button_cursor: SWFUpload.CURSOR.HAND,

		// Flash Settings
		flash_url : "<?php bloginfo('template_url'); ?>/js/swfupload.swf",

		custom_settings : {
			upload_target : "divFileProgressContainer"
		},

		// Debug Settings
		debug: false

	});
};

</script><?php

	}, 11);

	session_start();
	$_SESSION['file_info'] = array();

}

function hri_form_is_logged_in() {

	global $current_user;

	if ( is_user_logged_in() ) {

		?><p><?php _e('Kirjauduttu sisään käyttäjänä', 'hri'); ?> <?php

		echo $current_user->display_name; ?>. <a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e('Kirjaudu ulos?', 'hri'); ?></a>

		</p><?php

		return true;

	} else return false;

}

function hri_link_to_last_comment() {

	global $post, $wpdb;

	if( $post->comment_count > 2 ) {

		$latest_ID = $wpdb->get_var( "SELECT * FROM $wpdb->comments WHERE ( comment_parent = (SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_parent = 0 AND comment_approved = 1 ORDER BY comment_date DESC LIMIT 0,1) OR comment_parent = 0 ) AND comment_approved = 1 AND comment_post_ID = $post->ID ORDER BY comment_date DESC LIMIT 0,1" );

		if( $latest_ID ) {

			?><a class="bold" href="#comment-<?php echo $latest_ID; ?>"><?php _e( 'Siiry viimeiseen kommenttiin.', 'hri' ); ?></a><?php

		}

	}

}

/**
 * @param string $author
 * @return int
 */
function app_count_for_author( $author = '' ) {

	global $wpdb;

	$q = "SELECT COUNT(*) FROM $wpdb->posts JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id WHERE $wpdb->posts.post_type = 'application' AND $wpdb->posts.post_status = 'publish' AND $wpdb->postmeta.meta_key = 'app_author' AND $wpdb->postmeta.meta_value = '%s'";

	$app_count = $wpdb->get_var( $wpdb->prepare( $q, $author ) );

	return (int) $app_count;

}

/**
 * Tests if any of a post's assigned categories are descendants of target categories
 *
 * @param int|array $cats The target categories. Integer ID or array of integer IDs
 * @param int|object $_post The post. Omit to test the current post in the Loop or main query
 * @return bool True if at least 1 of the post's categories is a descendant of any of the target categories
 * @see get_term_by() You can get a category by name or slug, then pass ID to this function
 * @uses get_term_children() Passes $cats
 * @uses in_category() Passes $_post (can be empty)
 * @version 2.7
 * @link http://codex.wordpress.org/Function_Reference/in_category#Testing_if_a_post_is_in_a_descendant_category
 */
if ( ! function_exists( 'post_is_in_descendant_category' ) ) {
	function post_is_in_descendant_category( $cats, $_post = null ) {
		foreach ( (array) $cats as $cat ) {
			// get_term_children() accepts integer ID only
			$descendants = get_term_children( (int) $cat, 'category' );
			if ( $descendants && in_category( $descendants, $_post ) )
				return true;
		}
		return false;
	}
}

/**
 * Is current category descendant to given category?
 *
 * @param int $term_id
 * @return bool
 */
function is_category_descendant_to( $term_id ){

	global $wp_query;

	if( !isset( $wp_query->query_vars['cat'] ) ) return false;

	$anch = get_ancestors( $wp_query->query_vars['cat'], 'category' );

	return ( is_array( $anch ) && in_array( $term_id, $anch ) );

}

/**
 * @param float $size
 * @return string
 */
function hri_filesize( $size ){

	if( !$size ) return '';

	$space = HRI_LANG == 'fi' ? ' ' : '';

	if		( $size > 1000000000 )	$size_str = round($size / 1000000000, 2) . $space . 'GB';
	elseif	( $size > 1000000 )		$size_str = round($size / 1000000, 1) . $space . 'MB';
	elseif	( $size > 1000 )		$size_str = round($size / 1000) . $space . 'KB';
	else							$size_str = round($size / 1000, 2) . $space . 'KB';

	if( HRI_LANG == 'fi' ) $size_str = str_replace( '.', ',', $size_str );

	return $size_str;

}

?>
<?php
remove_filter('template_redirect', 'redirect_canonical');
/**
 *
 *	HRI
 *
 */

global $blog_id, $locale;

define( 'ORIGINAL_BLOG_ID', $blog_id );
define( 'HRI_LANG', substr( $locale, 0, 2 ) );

switch_to_blog(1);
define( 'ROOT_URL', home_url() );
restore_current_blog();

$hri_kses_args = array(
	'a' => array('href' => array(),'title' => array()),
	'br' => array(),
	'blockquote' => array(),
	'em' => array(),
	'li' => array(),
	'ol' => array(),
	'p' => array(),
	'span' => array('style'=>array('text-decoration')),
	'strong' => array(),
	'sub' => array(),
	'sup' => array(),
	'ul' => array()
);

function hri_image_upload() {

	switch_to_blog(1);

	$imageinfo = getimagesize( $_FILES['Filedata']['tmp_name'] );

	// allow only images recognized by getimagesize()
	if( $imageinfo !== false ) {

		$newID = media_handle_upload( 'Filedata', 0 );
		$thumbnail = wp_get_attachment_image_src( $newID, 'thumbnail' );

		add_post_meta( $newID, 'front_upload', 1 );

		echo $newID, '-', $thumbnail[0];

	}

	exit;

}

add_action('wp_ajax_hri_front_upload', 'hri_image_upload');
add_action('wp_ajax_nopriv_hri_front_upload', 'hri_image_upload');

function hri_tinymce( $hri_class = false ) {

	wp_enqueue_script( 'tinymce', get_bloginfo('template_url') . '/js/tiny_mce/tiny_mce.js', array('jquery') );

	if( $hri_class ) $GLOBALS['hri_tinymce_class'] = $hri_class;
	add_action( 'wp_head', 'hri_do_tinymce', 11, 1);

}

function hri_do_tinymce() {

	$class = isset( $GLOBALS['hri_tinymce_class'] ) ? $GLOBALS['hri_tinymce_class'] : false;

?><script type="text/javascript">
tinyMCE.init({
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
var hri_zerobyte = "<?php _e('Error: Zero byte file','twentyten'); ?>";
var hri_toobig = "<?php _e('Error: Max file size exceeded','twentyten'); ?>";
var hri_imageprocess = "<?php _e('Error: File processing failed','twentyten'); ?>";
var hri_allimages = "<?php _e('All images received.','twentyten'); ?>";
var hri_featuredimage = "<?php _e('Featured image','twentyten'); ?>";
var hri_delete = "<?php _e('Delete','twentyten'); ?>";

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
		button_image_url : "<?php bloginfo('template_url'); ?>/images/upload_plus.png",
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 180,
		button_height: 18,
		button_text : '<span class="button"><?php echo __( 'Select Images', 'twentyten' ); ?></span>',
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

function hri_make_short_link($url) {
	
	if ( strpos( $url, 'http://' ) !== 0 && strpos( $url, 'https://' ) !== 0 ) $url = 'http://' . $url;

	if ( strlen( $url ) > 50 ) $short_url = substr( $url, 0, 50 ) . '&hellip;';
	else $short_url = $url;
	
	$tag = "<a class=\"singlerow\" target=\"_blank\" title=\"$url\" href=\"$url\">$short_url</a>";
	
	return $tag;

}

add_filter('wp_die_handler', 'call_my_wp_die_handler');

function call_my_wp_die_handler() { return 'my_wp_die_handler'; }

function my_wp_die_handler( $message, $title = '', $args = array() ) {
	$defaults = array( 'response' => 500 );
	$r = wp_parse_args($args, $defaults);

	if($message == 'Error: please type a comment.' && isset($_POST['r1'])) return;

	$have_gettext = function_exists('__');

	if ( function_exists( 'is_wp_error' ) && is_wp_error( $message ) ) {
		if ( empty( $title ) ) {
			$error_data = $message->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['title'] ) )
				$title = $error_data['title'];
		}
		$errors = $message->get_error_messages();
		switch ( count( $errors ) ) :
		case 0 :
			$message = '';
			break;
		case 1 :
			$message = "<p>{$errors[0]}</p>";
			break;
		default :
			$message = "<ul>\n\t\t<li>" . join( "</li>\n\t\t<li>", $errors ) . "</li>\n\t</ul>";
			break;
		endswitch;
	} elseif ( is_string( $message ) ) {
		$message = "<p>$message</p>";
	}

	if ( isset( $r['back_link'] ) && $r['back_link'] ) {
		$back_text = $have_gettext? __('&laquo; Back') : '&laquo; Back';
		$message .= "\n<p><a href='javascript:history.back()'>$back_text</p>";
	}

	if ( defined( 'WP_SITEURL' ) && '' != WP_SITEURL )
		$admin_dir = WP_SITEURL . '/wp-admin/';
	elseif ( function_exists( 'get_bloginfo' ) && '' != get_bloginfo( 'wpurl' ) )
		$admin_dir = get_bloginfo( 'wpurl' ) . '/wp-admin/';
	elseif ( strpos( $_SERVER['PHP_SELF'], 'wp-admin' ) !== false )
		$admin_dir = '';
	else
		$admin_dir = 'wp-admin/';

	if ( !function_exists( 'did_action' ) || !did_action( 'admin_head' ) ) :
	if ( !headers_sent() ) {
		status_header( $r['response'] );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
	}

	if ( empty($title) )
		$title = $have_gettext ? __('WordPress &rsaquo; Error') : 'WordPress &rsaquo; Error';



	$text_direction = 'ltr';
	if ( isset($r['text_direction']) && 'rtl' == $r['text_direction'] )
		$text_direction = 'rtl';
	elseif ( function_exists( 'is_rtl' ) && is_rtl() )
		$text_direction = 'rtl';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- Ticket #11289, IE bug fix: always pad the error page with enough characters such that it is greater than 512 bytes, even after gzip compression abcdefghijklmnopqrstuvwxyz1234567890aabbccddeeffgghhiijjkkllmmnnooppqqrrssttuuvvwwxxyyzz11223344556677889900abacbcbdcdcededfefegfgfhghgihihjijikjkjlklkmlmlnmnmononpopoqpqprqrqsrsrtstsubcbcdcdedefefgfabcadefbghicjkldmnoepqrfstugvwxhyz1i234j567k890laabmbccnddeoeffpgghqhiirjjksklltmmnunoovppqwqrrxsstytuuzvvw0wxx1yyz2z113223434455666777889890091abc2def3ghi4jkl5mno6pqr7stu8vwx9yz11aab2bcc3dd4ee5ff6gg7hh8ii9j0jk1kl2lmm3nnoo4p5pq6qrr7ss8tt9uuvv0wwx1x2yyzz13aba4cbcb5dcdc6dedfef8egf9gfh0ghg1ihi2hji3jik4jkj5lkl6kml7mln8mnm9ono -->
<html xmlns="http://www.w3.org/1999/xhtml" <?php if ( function_exists( 'language_attributes' ) && function_exists( 'is_rtl' ) ) language_attributes(); else echo "dir='$text_direction'"; ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $title ?></title>
	<link rel="stylesheet" href="<?php echo $admin_dir; ?>css/install.css" type="text/css" />
<?php
if ( 'rtl' == $text_direction ) : ?>
	<link rel="stylesheet" href="<?php echo $admin_dir; ?>css/install-rtl.css" type="text/css" />
<?php endif; ?>
</head>
<body id="error-page">
<?php endif; ?>
	<?php echo $message; ?>
</body>
</html>
<?php
	die();
}

/**
 * @param string $post_type
 * @return array
 */
function get_tags_per_post_type($post_type) {

	global $wpdb;
	$tag_list = $wpdb->get_results( "SELECT DISTINCT(t.term_id),t.name FROM {$wpdb->terms} t JOIN {$wpdb->term_taxonomy} tx ON t.term_id = tx.term_id AND tx.taxonomy = 'post_tag' JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tx.term_taxonomy_id JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_type = '$post_type'" );

	return $tag_list;
}

/* -------------------------------------------------------------------------------------------------------------------------------- HRI tag list override */


function get_the_term_list_hri( $before = '', $sep = '', $after = '' ) {
	$id = 0;
	$taxonomy = 'post_tag';

	$terms = get_the_terms( $id, $taxonomy );

	if ( is_wp_error( $terms ) )
		return $terms;

	if ( empty( $terms ) )
		return false;

	foreach ( $terms as $term ) {
		$link = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $link ) )
			return $link;

			// We deleted the tag option from the normal search, so we use the data search
		if (ORIGINAL_BLOG_ID == 2) $link = home_url() . '/fi/data-haku/?tags=' . $term->slug;
		if (ORIGINAL_BLOG_ID == 3) $link = home_url() . '/en/data-search/?tags=' . $term->slug;
		if (ORIGINAL_BLOG_ID == 4) $link = home_url() . '/se/';
		
		$term_links[] = '<a href="' . $link . '" rel="tag">' . $term->name . '</a>';
	}

	$term_links = apply_filters( "term_links-$taxonomy", $term_links );

	return $before . join( $sep, $term_links ) . $after;
}


/* -------------------------------------------------------------------------------------------------------------------------------- HRI tag cloud override */

function wp_tag_cloud_hri( $args = '' ) {
	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
		'format' => 'flat', 'separator' => "\n", 'orderby' => 'name', 'order' => 'ASC',
		'exclude' => '', 'include' => '', 'link' => 'view', 'taxonomy' => 'post_tag', 'echo' => true,
		'data' => false // data search or normal search
	);
	$args = wp_parse_args( $args, $defaults );

	$tags = get_terms( $args['taxonomy'], array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) ) ); // Always query top tags

	if ( empty( $tags ) ) return false;

	foreach ( $tags as $key => $tag ) {
				
		if($args['data']) {
			
			if (ORIGINAL_BLOG_ID == 2) $link = home_url() . '/fi/data-haku/?tags=' . $tag->slug;
			if (ORIGINAL_BLOG_ID == 3) $link = home_url() . '/en/data-search/?tags=' . $tag->slug;
			if (ORIGINAL_BLOG_ID == 4) $link = home_url() . '/se/';
			
		} else {
			
			if (ORIGINAL_BLOG_ID == 2) $link = home_url() . '/fi/haku/?tags=' . $tag->slug;
			if (ORIGINAL_BLOG_ID == 3) $link = home_url() . '/en/search/?tags=' . $tag->slug;
			if (ORIGINAL_BLOG_ID == 4) $link = home_url() . '/se/';
			
		}
		
		$tags[ $key ]->link = $link;
		$tags[ $key ]->id = $tag->term_id;
		$tags[ $key ]->name = $tags[ $key ]->name.' ('.$tag->count.')'; // topic_count_text_callback didn't work???
	}

	$return = wp_generate_tag_cloud( $tags, $args ); // Here's where those top tags get sorted according to $args

	$return = apply_filters( 'wp_tag_cloud', $return, $args );

	if ( 'array' == $args['format'] || empty($args['echo']) )
		return $return;

	echo $return;
}

/* ------------------------------------------------------------------------------------------------ HRI comments / comment excerpts / ratings */

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
				
				<?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>
				<?php if ( $comment->comment_approved == '0' ) : ?>
				<br />
				<em><?php _e( 'Your comment is awaiting moderation.', 'twentyten' ); ?></em>
				<?php endif; ?>
			</span><!-- .comment-author .vcard -->
			<br />
			<span class="timestamp"><?php
				hri_time_since( $comment->comment_date );
				edit_comment_link( __( '(Edit)', 'twentyten' ), ' ' );
			?></span>

			<a class="report-comment" id="report-comment-<?php comment_ID(); ?>"><?php _e('Report this comment','twentyten'); ?></a>
			
			<?php //if ( !$rating1 ) { comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'class' => 'comment-reply-link' ) ) ); } 
			comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'class' => 'comment-reply-link' ) ) );
			?>

		</div>

	</div><div class="clear"></div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'twentyten' ); ?> <?php comment_author_link(); ?></p>
	<?php
			break;
	endswitch;
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
		<div id="comment-<?php comment_ID(); ?>">

		<div class="comment-body"><?php comment_text(); ?><div class="comment-nuoli"></div></div>
		<div class="comment-meta commentmetadata">
			<?php echo get_avatar( $comment, 30 ); ?>
			<span class="name">
				<?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>
				<?php if ( $comment->comment_approved == '0' ) : ?>
				<br />
				<em><?php _e( 'Your comment is awaiting moderation.', 'twentyten' ); ?></em>
				<?php endif; ?>
			</span><!-- .comment-author .vcard -->
			<br />
			<span class="timestamp"><a href="<?php echo esc_url( hri_link( get_comment_link( $comment->comment_ID ), HRI_LANG, '' ) ); ?>">
				<?php hri_time_since( $comment->comment_date ); ?>
					</a><?php edit_comment_link( __( '(Edit)', 'twentyten' ), ' ' );
				?>
			</span>

			<a class="report-comment" id="report-comment-<?php comment_ID(); ?>"><?php _e('Report this comment','twentyten'); ?></a>

			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'class' => 'comment-reply-link' ) ) ); ?>
		</div>

	</div><div class="clear">
</div>

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'twentyten' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __('(Edit)', 'twentyten'), ' ' ); ?></p>
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
function hri_comment_excerpt( $comment, $virtual = false , $is_discussion = false) {

	$link = hri_link( get_permalink( $comment->comment_post_ID ), HRI_LANG, '' );
?>
	<div class="comment hri-comment-excerpt">
		<a class="hri-comment-excerpt-link" href="<?php echo $link; if( !$virtual ) { ?>#comment-<?php echo $comment->comment_ID; } ?>">
			<div class="comment-body">
		<?php

			$excerpt = isset( $comment->hri_excerpt ) ? $comment->hri_excerpt : get_comment_excerpt( $comment->comment_ID );

			echo '<p>', $excerpt, '</p>';

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


/* -------------------------------------------------------------------------------------------------------------------------------- subscribe to comments */

function hri_subscribe() {

	global $post;

?>
<div id="hri_subsribe_toggle">
	<div id="subsribe_toggle_header">
		<h3><a id="subsribe_toggle_header_link"><?php _e( 'Subscribe without commenting or unsubribe', 'twentyten' ); ?></a></h3>
	</div>

	<div class="clear"></div>
	<div id="subsribe_content" style="display: none">
		<form action="<?php

			echo ROOT_URL, '/', HRI_LANG;
			if( ORIGINAL_BLOG_ID == 2 ) echo '/tilaa-kommentit/';
			if( ORIGINAL_BLOG_ID == 3 ) echo '/subscribe-to-comments/';

			?>" method="post">
		<p class="comment-form-email">
			<label for="hri_subscribe_email"><?php _e('Email', 'twentyten'); ?></label>
			<span class="required">*</span>
			<input id="hri_subscribe_email" type="text" size="30" value="" name="hri_subscribe_email" />
		</p>
		<p>
			<input type="hidden" value="<?php echo $post->ID; ?>" name="hri_subscribe_post" />
			<input type="hidden" value="<?php echo get_current_blog_id(); ?>" name="hri_subscribe_blog" />
			<input class="hri_subscribe_option" type="radio" name="hri_subscribe_option" id="hri_subscribe_option1" value="1"><label for="hri_subscribe_option1"><?php _e('Subscribe to this', 'twentyten'); ?></label>
			<input class="hri_subscribe_option" type="radio" name="hri_subscribe_option" id="hri_subscribe_option2" value="2"><label for="hri_subscribe_option2"><?php _e('Unsubscribe from this', 'twentyten'); ?></label>
			<input class="hri_subscribe_option hri_subscribe_option_top_margin" type="radio" name="hri_subscribe_option" id="hri_subscribe_option3" value="3"><label class="hri_subscribe_option_top_margin" for="hri_subscribe_option3"><?php _e('Unsubscribe from all', 'twentyten'); ?></label>
			<br class="clear" />
			<input type="submit" name="hri_subscribe_submit" id="hri_subscribe_submit" value="<?php _e('Submit', 'twentyten'); ?>" />
		</p>
		</form>
	</div>
	<a style="display: none" id="subsribe_toggle_close"><?php _e( 'Close', 'twentyten' ); ?><div class="sortmark sortreverse"></div></a>
	<div class="clear"></div>
</div>
<script type="text/javascript">
// <!--
jQuery(function ($) {
	$('#subsribe_toggle_header').click(function(){
		$('#subsribe_content').slideDown();
		$('#subsribe_toggle_close').show();
	});
	$('#subsribe_toggle_close').click(function(){
		$('#subsribe_content').slideUp();
		$(this).hide();
	});
});
// -->
</script>
<?php

}

add_action('comment_form_after', 'hri_subscribe');

/* -------------------------------------------------------------------------------------------------------------------------------- data with language title */

function data_title( $lang = 'fi', $echo = true ) {

	global $post;
	$title = false;

	if ( $lang == 'en' ) {

		$title = get_post_meta( $post->ID, 'extras_title_en', true );

	} elseif ( $lang == 'se' ) {

		$title = get_post_meta( $post->ID, 'extras_title_se', true );

	}

	if ( !$title ) $title = get_the_title();

	if ( $echo === true ) echo $title;
	else return $title;

}

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
		if ($echo) echo $notes;
		else return $notes;
	} else return false;

}

define( 'LINK_LIMIT', 60 );
define( 'LINK_FORMAT', '<a href="%s" rel="external">%s</a>' );

function links_in_text( $string, $reg = '~((?:https?://|www\d*\.)\S+[-\w+&@#/%=\~|])~' ) {
	function parse_links( $m ) {
		$href = $name = html_entity_decode($m[0]);

		if ( strpos( $href, '://' ) === false ) {
			$href = 'http://' . $href;
		}

		if( strlen($name) > LINK_LIMIT ) {
			$k = ( LINK_LIMIT - 3 ) >> 1;
			$name = substr( $name, 0, $k ) . '...' . substr( $name, -$k );
		}

		return sprintf( LINK_FORMAT, htmlentities($href), htmlentities($name) );
	}

	return preg_replace_callback( $reg, 'parse_links', $string );
}

/**
 * @param string $date
 */
function hri_time_since( $date ) {

	if( function_exists( 'time_since' ) ) {

		echo '<span title="', $date, '">', time_since( strtotime( $date ) ), '</span> ', __('ago', 'twentyten');

	} else {

		echo '<span title="', $date, '">', $date, '</span> ', __('ago', 'twentyten');

	}

}

/**
 * @param int $page
 * @param string $url
 * @param int $result_count
 * @return boolean|string
 */
function hri_pager($page, $url = '', $result_count) {

	$pager = "<div class='pager'>";

	if($url != '') $url .= '&amp;';

	if($page > 1) {
		$pager .= '<a href="?'.$url.'&page='.($page-1).'" class="previous">'.__('Previous', 'hri-ckan').'</a>';
	}

	$pageCount = ceil($result_count/10);

	if($pageCount < 2) return false;
	if ($pageCount < 6) {
		for($i = (($page <= 5)?1:$page-5); $i < (($pageCount > $page+6)?$page+6:$pageCount+1); $i++) {
			if($i == $page) {
				$pager .= ' <strong class="curpage">'.$i.'</strong> ';
			} else {
				$pager .= ' <a href="?'.$url.'page='.($i).'" class="pagenum" id="pn'.$i.'">'.$i.'</a> ';
			}
		}
	} else {
		$first_show = false;
		if ($page > 6 ) {
			$pager .= ' <a href="?'.$url.'page=1" class="pagenum" id="pn1">1</a> ';
			$pager .= ' <a href="?'.$url.'page=2" class="pagenum" id="pn2">2</a> ';
			if ($pageCount > 9) {
				$pager .= ' &hellip; ';
			}
			$first_show = true;
		}
		for($i = (($page <= 5)?1:$page-5); $i < (($pageCount > $page+6)?$page+6:$pageCount+1); $i++) {
			if (($i == 1 || $i == 2) && $first_show) {
				continue;
			}
			if($i == $page) {
				$pager .= ' <strong class="curpage">'.$i.'</strong> ';
			} else {
				$pager .= ' <a href="?'.$url.'page='.($i).'" class="pagenum" id="pn'.$i.'">'.$i.'</a> ';
			}
		}
		if ($page < ($pageCount-7)) {
			$pager .= ' &hellip; ';
			$pager .= ' <a href="?'.$url.'page='.($pageCount-1).'" class="pagenum" id="pn'.($pageCount-1).'">'.($pageCount-1).'</a> ';
			$pager .= ' <a href="?'.$url.'page='.$pageCount.'" class="pagenum" id="pn'.$pageCount.'">'.$pageCount.'</a> ';
		}
	}
	if($pageCount > $page) {
		$pager .= '<a href="?'.$url.'page='.($page+1).'" class="next">'.__('Next', 'hri-ckan').'</a>';
	}
	$pager .= '</div>';

	return $pager;
}

/**
 * @param array $linked
 * @param array $exclude
 * @param int $max_results
 */
function hri_related_discussions( $linked, $exclude = array(), $max_results = 5 ) {

	global $wpdb;

	$args = array(
		'post_type' => 'discussion',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'meta_query' => array(
			array(
				'key' => '_link_to_data',
				'compare' => 'IN',
				'value' => $linked
			)
		)
	);

	if( !empty( $exclude ) ) {

		$args['post__not_in'] = $exclude;

	}

	$related_discussions = new WP_Query( $args );

	if( $related_discussions->have_posts() ) {

		$i = 0;

	?><li class="widget-container">
		<div class="widget widget_discussion"><?php

		echo '<h3 class="widget-title">', __('Related discussions', 'twentyten'), '</h3>';
		while( $related_discussions->have_posts() && $i < $max_results ) {

			$related_discussions->the_post();

			global $post;

			?><h4><?php the_title(); ?></h4><?php

			if( $post->comment_count > 0 ) {

				$comment = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}comments WHERE comment_post_ID = {$post->ID} ORDER BY comment_date DESC LIMIT 0,1" );

				hri_comment_excerpt( $comment );

			} else {

				$hri_virtual_comment = array(
					'hri_excerpt' => n_words( strip_tags( $post->post_content ), 20 ),
					'comment_post_ID' => $post->ID,
					'comment_date' => $post->post_date
				);

				if( isset($meta_author) && $meta_author ) {
					$hri_virtual_comment['comment_author'] = $meta_author;
					$hri_virtual_comment['comment_author_email'] = get_post_meta( $post->ID, 'user_email', true );
				}
				else {
					$custom = get_post_custom($post->ID);
					if( isset( $custom['user_name'] ) ) {

						$hri_virtual_comment['comment_author'] = $custom['user_name'][0];
						$hri_virtual_comment['comment_author_email'] = $custom['user_email'][0];

					} else  {
						$userdata = get_userdata( $post->post_author );

						$hri_virtual_comment['comment_author'] = $userdata->display_name;
						$hri_virtual_comment['comment_author_email'] = $userdata->user_email;
					}
				}

				hri_comment_excerpt( (object) $hri_virtual_comment, true );

			}

			++$i;

		}
		
		if( $related_discussions->post_count > $max_results ) {

			?><form action="<?php

				echo home_url();

				if (ORIGINAL_BLOG_ID == 2) echo '/fi/keskustelut/';
				if (ORIGINAL_BLOG_ID == 3) echo '/en/discussions/';

			?>" method="post">
				<input type="hidden" value="<?php echo implode( ',', $linked ); ?>" name="linked" />
				<input type="submit" name="linked_submit" value="<?php _e('See all discussions','twentyten'); ?>" />
			</form><?php

		}
		
		?>
		</div>
	</li><?php

	}

}

/* -------------------------------------------------------------------------------------------------------------------------------- cross-blog functions */

function hri_link( $url, $lang = 'fi', $datatype = null, $leaveDate = false) {

	if ( isset($datatype) ) {
	
		$url = str_replace( 'blog', $lang . '/' . $datatype, $url );
	
		if (strpos( $url, ROOT_URL.'/' . $lang) === false) {
			$url = str_replace( ROOT_URL.'/', ROOT_URL.'/' . $lang . '/', $url);
		}
	} else {
		$url = str_replace( 'blog/', $lang.'/', $url );
	}
	
	if ($lang == 'fi') $url = str_replace( 'discussions', 'keskustelut', $url );
	if ($lang == 'fi') $url = str_replace( 'applications', 'sovellukset', $url );
	if ($lang == 'fi') $url = str_replace( 'application-ideas', 'sovellusideat', $url );
	if ($lang == 'fi') $url = str_replace( 'data-requests', 'datatoiveet', $url );
	
	// Remove date ( 1111/11/11 ) from link
	if ( !$leaveDate ) $url = preg_replace( '(\d{4}\/\d{2}\/\d{2}\/)', '', $url );

	return $url;
	
}

/* -------------------------------------------------------------------------------------------------------------------------------- Rewrite */

// http://codex.wordpress.org/Function_Reference/WP_Rewrite

add_filter('rewrite_rules_array','wp_insertMyRewriteRules');
add_filter('query_vars','wp_insertMyRewriteQueryVars');

// Adding new rules
function wp_insertMyRewriteRules($rules) {
	$newrules = array();
	
	// Content types:
	$newrules['(data)/$'] = 'index.php?posttype=data';
	$newrules['(data)/(.+)$'] = 'index.php?posttype=data&postname=$matches[2]';
	
	$newrules['(applications)$'] = 'index.php?posttype=application';
	$newrules['(sovellukset)$'] = 'index.php?posttype=application';
	$newrules['(applications)/(category)/(.+)$'] = 'index.php?posttype=application&appcat=$matches[3]';
	$newrules['(sovellukset)/(kategoria)/(.+)$'] = 'index.php?posttype=application&appcat=$matches[3]';

	$newrules['(applications)/(.+)$'] = 'index.php?posttype=application&postname=$matches[2]';
	$newrules['(sovellukset)/(.+)$'] = 'index.php?posttype=application&postname=$matches[2]';

//	$newrules['(application-ideas)$'] = 'index.php?posttype=application-idea';
//	$newrules['(sovellusideat)$'] = 'index.php?posttype=application-idea';
//	$newrules['(application-ideas)/(.+)$'] = 'index.php?posttype=application-idea&postname=$matches[2]';
//	$newrules['(sovellusideat)/(.+)$'] = 'index.php?posttype=application-idea&postname=$matches[2]';
	
	$newrules['(discussions)$'] = 'index.php?pagename=discussions';
	$newrules['(keskustelut)$'] = 'index.php?pagename=keskustelut';
	$newrules['(discussions)/(.+)$'] = 'index.php?posttype=discussion&postname=$matches[2]';
	$newrules['(keskustelut)/(.+)$'] = 'index.php?posttype=discussion&postname=$matches[2]';

//	$newrules['(data-requests)$'] = 'index.php?posttype=data-request';
//	$newrules['(datatoiveet)$'] = 'index.php?posttype=data-request';
//	$newrules['(data-requests)/(.+)$'] = 'index.php?posttype=data-request&postname=$matches[2]';
//	$newrules['(datatoiveet)/(.+)$'] = 'index.php?posttype=data-request&postname=$matches[2]';

	return $newrules + $rules;

}

// Adding the id var so that WP recognizes it
function wp_insertMyRewriteQueryVars($vars) {
	array_push($vars, 'posttype', 'postname', 'appcat');
	return $vars;
}
 
/* -------------------------------------------------------------------------------------------------------------------------------- Comment meta language things */

// comment language meta saving
add_action('comment_post','comment_save_data');

function comment_save_data($comment_id) {
	
	$langs = array('en','fi','se');
	if ( !in_array( $_POST['lang'], $langs) ) die('Error: Comment language meta fail');
	
	add_comment_meta($comment_id, 'hri_comment_lang', $_POST['lang'], true);
	
	if ( isset( $_POST['r1'] ) ) {

		for($i=1; $i<=3; ++$i){
			$r = (int) $_POST['r'.$i];
			if ($r < 1) $r = 1;
			if ($r > 5) $r = 5;
			add_comment_meta($comment_id, '_hri_rating'.$i, $r, true);
		}
	}
}

function add_hidden_lang() {

	global $blog_id, $post;

 	echo '<input type="hidden" name="lang" value="' . HRI_LANG . '" />
	<input type="hidden" name="redirect_to" value="' . hri_link( get_permalink( $post->ID ), HRI_LANG, 'data' ) . '" />
	<input type="hidden" id="hri-blog" value="' . $blog_id . '" />';
	
	return true;
	
}

add_filter('comment_form_top','add_hidden_lang');

/* -------------------------------------------------------------------------------------------------------------------------------- Comments meta language ends */

/**
 * @param string $string
 * @param int $n
 * @param string $append
 * @return string
 */
function n_words( $string, $n, $append = ' &hellip;' ) {
	$words = explode( ' ', $string );
	if ( count( $words ) > $n ) {
		$words = array_splice( $words, 0, $n );
		$string = implode(' ', $words);
		if($append) $string .= $append;
	}
	return $string;
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

$themename = "HRI";
$shortname = "hri";
$options = array (
 
	array( "name" => "HRI Options",	"type" => "title"),
	array( "type" => "open"),
	array( "name" => "Links Category",
		"desc" => "List footer links from this category",
		"id" => $shortname."_footer_links",
		"type" => "textarea",
		"std" => ""),
	 
	array( "type" => "close")
 
);

function hri_pagetree($pageID) {

	$curID = get_post_ancestors($pageID);

//	if($curID && $curID[0]<>0) $pageID = $curID[0];
	if($curID && $curID[0]<>0) $pageID = end($curID);

	$children = wp_list_pages('title_li=&child_of='.$pageID.'&echo=0');
	
	if ($children) {
		echo '<ul id="pagetree">';
		echo $children;
		echo '</ul>';
	}

}

load_theme_textdomain( 'twentyten', TEMPLATEPATH . '/languages' );


/**
 * Make Excerpt functionality better
 * @return mixed|string
 */
function improved_trim_excerpt() {
	
	//+ Jonas Raoni Soares Silva
	//@ http://jsfromhell.com
	
	global $post;
	$text = get_the_content('');
	$text = apply_filters('the_content', $text);
	$text = str_replace(']]>', ']]&gt;', $text);
	$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
	
	if (!class_exists('String')) {
		class String {
			public static function truncate($text, $length, $suffix = '&hellip;', $isHTML = true){
				$i = 0;
				$simpleTags=array('br'=>true,'hr'=>true,'input'=>true,'image'=>true,'link'=>true,'meta'=>true);
				$tags = array();
				if($isHTML){
					preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
					foreach($m as $o){
						if($o[0][1] - $i >= $length)
							break;
						$t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);
						// test if the tag is unpaired, then we mustn't save them
						if($t[0] != '/' && (!isset($simpleTags[$t])))
							$tags[] = $t;
						elseif(end($tags) == substr($t, 1))
							array_pop($tags);
						$i += $o[1][1] - $o[0][1];
					}
				}
				
				// output without closing tags
				$output = substr($text, 0, $length = min(strlen($text),  $length + $i));
				// closing tags
				$output2 = (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');
				
				// Find last space or HTML tag (solving problem with last space in HTML tag eg. <span class="new">)
				$pos = (int)end(end(preg_split('/<.*>| /', $output, -1, PREG_SPLIT_OFFSET_CAPTURE)));
				// Append closing tags to output
				$output.=$output2;
		
				// Get everything until last space
				$one = substr($output, 0, $pos);
				// Get the rest
				$two = substr($output, $pos, (strlen($output) - $pos));
				// Extract all tags from the last bit
				preg_match_all('/<(.*?)>/s', $two, $tags);
				// Add suffix if needed
				if (strlen($text) > $length) { $one .= $suffix; }
				// Re-attach tags
				$output = $one . implode('',$tags[0]);
		
				//added to remove  unnecessary closure
				$output = str_replace('</!-->','',$output); 
		
				return $output;
			}
		}
	}
	
	return String::truncate($text, 200, '&hellip;', true, false);
	
}

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'improved_trim_excerpt');

/* Custom profiilikenttiä */

function adjust_contact_methods( $contactmethods ) {
	// remove unnecessary fields
	unset($contactmethods['aim']);
	unset($contactmethods['jabber']);
	unset($contactmethods['yim']);

	// add a few new ones
	$contactmethods['phone'] = 'Phone';
	return $contactmethods;
}
add_filter('user_contactmethods','adjust_contact_methods',10,1);

// Add extra fields to user profile and hide some unnecessary ones
function extra_user_profile_fields( $user ) { ?>
	<h3><?php _e("Professional information", "blank"); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="position"><?php _e("Title"); ?></label></th>
			<td>
				<input type="text" name="position" id="position" value="<?php echo esc_attr( get_the_author_meta( 'position', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e("Your professional title or position in your organization."); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="organization"><?php _e("Organization"); ?></label></th>
			<td>
				<input type="text" name="organization" id="organization" value="<?php echo esc_attr( get_the_author_meta( 'organization', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e("The name of your company or organization."); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="organization_home_page"><?php _e("Organization home page"); ?></label></th>
			<td>
				<input type="text" name="organization_home_page" id="organization_home_page" value="<?php echo esc_attr( get_the_author_meta( 'organization_home_page', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e("The URL of your organization's home page. Remember to include <strong>http://</strong>"); ?></span>
			</td>
		</tr>
	</table>
		
	<script type="text/javascript">
		function cleanprofileform() {
			// hide the omplete Personal Options section
			jQuery("h3:contains('Personal Options')").hide();
			jQuery("h3:contains('Personal Options')").next("table").hide();
			// Hide nickname field (entire table row)
			jQuery("label[for^='nickname']").parents("tr").hide();
		}

		// clean up as soon as this is loaded
		cleanprofileform();
		
		jQuery(document).ready(function() {
			// once more when the dom is ready just to be on the safe side
			cleanprofileform();
		});
	</script>

	<h3><?php _e("Moderation notifications", "twentyten"); ?></h3>
	<table class="form-table" style="margin-bottom:20px">
		<tr>
			<th><?php _e('Comment notifications','twentyten'); ?></th>
			<td>
				<input type="checkbox" name="hri_notifications" id="hri_notifications" <?php if( get_the_author_meta( 'hri_notifications', $user->ID ) == '1' ) echo 'checked="checked" '; ?>/><label for="hri_notifications"><?php _e('Instant email notifications on flagged comments.','twentyten'); ?></label><br />

			</td>
		</tr>
		<tr>
			<th><label for="hri_digest_interval"><?php _e('Moderation digest','twentyten'); ?></label></th>
			<td><?php
$digest_interval = get_the_author_meta('hri_digest_interval', $user->ID);
$intervals = array(
	0 => __('Never', 'twentyten'),
	1 => __('Daily', 'twentyten'),
	2 => __('Weekly', 'twentyten')
);
?>
				<select style="width:15em" name="hri_digest_interval" id="hri_digest_interval">
<?php
foreach( $intervals as $s => $iv ) {
	echo '<option';
	if ( $s == $digest_interval ) {
		echo ' selected="selected"';
	}
	echo ' value="'.$s.'">'.$iv.'</option>';
} ?>
				</select>
					<br />
				<span class="description"><?php _e('Email digest for all pending content.','twentyten'); ?></span>
			</td>
		</tr>
	</table>

<?php }

// remove profile page's additional_capabilities list
add_action( 'additional_capabilities_display', function() { return false; });

function save_extra_user_profile_fields( $user_id ) {
	if (!current_user_can('edit_user', $user_id )) {
		return false;
	}
	update_user_meta( $user_id, 'position', trim(strip_tags($_POST['position'])) );
	update_user_meta( $user_id, 'organization', trim(strip_tags($_POST['organization'])) );
	update_user_meta( $user_id, 'organization_home_page', trim(strip_tags($_POST['organization_home_page'])) );
	
	$notifications = isset($_POST['hri_notifications']) ? 1 : 0;

	update_user_meta( $user_id, 'hri_notifications', $notifications );
	update_user_meta( $user_id, 'hri_digest_interval', (int) $_POST['hri_digest_interval'] );
	
}

// hook into profile forms
add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );
// hook into save events
add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );

/**
 * TwentyTen functions and definitions
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) )
	$content_width = 460;

add_action( 'after_setup_theme', 'hri_setup' );
function hri_setup() {

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	add_theme_support( 'post-thumbnails' );
	add_image_size( 'post-thumbnail', 460, 9999 );
	add_image_size( 'hri_square', 460, 460, true );
	add_image_size( 'hri_column', 300, 9999 );
	add_image_size( 'hri_column_crop', 300, 150, true );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'twentyten', TEMPLATEPATH . '/languages' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'twentyten' ),
	) );

}

/**
 * @return string
 */
function hri_read_more(){
	return '<a href="' . get_permalink() . '" class="readmore">' . __('Read more', 'twentyten') . '</a>';
}

function hri_add_this() {

	// AddThis Button BEGIN
?>
<div class="addthis_toolbox addthis_default_style">
	<div class="inside">
		<a class="addthis_button_facebook_like" <?php /*fb:like:layout="button_count"*/ ?>></a>
		<a class="addthis_button_tweet"></a>
		<a class="addthis_button_email">Email</a>
	</div>
</div>
<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4d793e5d41cc89d9"></script>
<?php
if (HRI_LANG == 'fi') {
?>
<script>
var addthis_config = {
     ui_language: "fi"
}
</script>
<?php
}
?>
<?php
	// AddThis Button END
}

function hri_form_is_logged_in() {

	global $current_user;

	if ( is_user_logged_in() ) {

		?><p><?php _e('Logged in as', 'twentyten'); ?> <?php

		echo $current_user->display_name; ?>. <a href="<?php echo wp_logout_url( get_permalink() ); ?>"><?php _e('Log out?', 'twentyten'); ?></a>

		</p><?php

		return true;

	} else return false;

}

function hri_report_comment_form() {
?>

<script type="text/javascript">
// <!--
document.write('<?php

	if( ORIGINAL_BLOG_ID == 2) $target = ROOT_URL . '/fi/ilmoita-kommentti/';
	if( ORIGINAL_BLOG_ID == 3) $target = ROOT_URL . '/en/report-comment/';

	$form = '<div style="display:none" id="report-container"><div id="report-top"></div><h4>' . __('Report offensive content','twentyten') . '</h4><p>' . __('Tell us why you think this content should be removed.','twentyten') . '</p><form id="report-form" action="' . $target .'" method="post"><input type="hidden" name="comment_ID" id="comment_ID" /><textarea rows="5" cols="20" name="reporttext"></textarea><p><label for="report-email">' . __('Your email','twentyten') . ':</label> <input type="text" size="30" name="report-email" id="report-email" /></p><input type="submit" value="' . __('Submit','twentyten') . '" /></form></div>';

	$form = str_replace('"comment"', '"\'+String.fromCharCode(0x63)+\'omment"', $form);
	echo str_replace('<form', '\'+String.fromCharCode(074)+String.fromCharCode(102)+\'orm', $form);

?>');
jQuery(function($) {
	$('.report-comment').click(function(){
		if( $(this).hasClass('report-cancel') ) {

			$('.report-comment, #respond').show();
			$(this).text('<?php _e('Report this comment','twentyten'); ?>').removeClass('report-cancel').parent().parent().removeClass('report-target');
			$('.comment-reply-link').show();

			$('#report-container').hide();
			$('#comment_ID').val('');

		} else {

			$('.report-comment').not( $(this) ).hide();
			$(this).text('<?php _e('Cancel','twentyten'); ?>').addClass('report-cancel').parent().parent().addClass('report-target');
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
 * Remove inline styles printed when the gallery shortcode is used.
 *
 * Galleries are styled by the theme in Twenty Ten's style.css.
 *
 * @since Twenty Ten 1.0
 * @param $css string
 * @return string The gallery style filter, with the styles themselves removed.
 */
function twentyten_remove_gallery_css( $css ) {
	return preg_replace( "#<style type='text/css'>(.*?)</style>#s", '', $css );
}
add_filter( 'gallery_style', 'twentyten_remove_gallery_css' );

/**
 * Register widgetized areas
 *
 * @since Twenty Ten 1.0
 * @uses register_sidebar
 */
function twentyten_widgets_init() {

	register_sidebar( array(
		'name' => __( 'Big front page Widget Area', 'twentyten' ),
		'id' => 'big-frontpage-widget-area',
		'description' => __( 'The big front page widget area', 'twentyten' ),
		'before_widget' => '<div id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// new home, 9 cells
	for( $r = 1; $r <= 3; ++$r ) {
		for( $c = 1; $c <= 3; ++$c ) {
			register_sidebar(array('name'=>"Home page: row $r cell $c",
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
			));
		}
	}

	register_sidebar( array(
		'name' => __( 'Add new application: Sidebar', 'twentyten' ),
		'id' => 'new-app-page-widget-area',
		'description' => __( 'The widget area for new application form', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Defaults:
	// Area 1, located at the top of the sidebar.
	register_sidebar( array(
		'name' => __( 'Subpage: Sidebar', 'twentyten' ),
		'id' => 'primary-widget-area',
		'description' => __( 'The primary widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 4, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Footer: Logo area', 'twentyten' ),
		'id' => 'second-footer-widget-area',
		'description' => __( 'The second footer widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 3, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Footer: Left column', 'twentyten' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'The first footer widget area', 'twentyten' ),
		'before_widget' => '',
		'after_widget' => '',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Area 6, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Footer: Right column', 'twentyten' ),
		'id' => 'fourth-footer-widget-area',
		'description' => __( 'The thrird footer widget area', 'twentyten' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
/** Register sidebars by running twentyten_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'twentyten_widgets_init' );

/* HRI widget classes */
function hri_widget_form_extend( $instance, $widget ) {

	if ( !isset($instance['classes']) )
	$instance['classes'] = null;

	$hri_classes = array(
		'hri_widget_default' => __('Default','twentyten'),
		'hri_widget_fb' => __('Facebook','twentyten'),
		'hri_widget_feedback' => __('Feedback','twentyten'),
	);

	?>
<p>
	<label for="widget-<?php echo $widget->id_base; ?>-<?php echo $widget->number; ?>-classes"><?php _e('CSS class','twentyten'); ?>:</label>
	<select name="widget-<?php echo $widget->id_base; ?>[<?php echo $widget->number; ?>][classes]" id="widget-<?php echo $widget->id_base; ?>-<?php echo $widget->number; ?>-classes" class="widefat">
<?php

foreach( array_keys($hri_classes) as $hri_class_key ) {

	$selected = ( $instance['classes'] == $hri_class_key ) ? " selected='selected'" : null;
?><option<?php echo $selected; ?> value="<?php echo $hri_class_key ?>"><?php echo $hri_classes[ $hri_class_key ]; ?></option><?php

}

?>
	</select>
</p>
	<?php

	return $instance;
}

add_filter('widget_form_callback', 'hri_widget_form_extend', 10, 2);

function hri_widget_update( $instance, $new_instance ) {

	$instance['classes'] = $new_instance['classes'];
	return $instance;

}

add_filter( 'widget_update_callback', 'hri_widget_update', 10, 2 );

function hri_dynamic_sidebar_params( $params ) {
	global $wp_registered_widgets;
	$widget_id = $params[0]['widget_id'];
	$widget_obj = $wp_registered_widgets[$widget_id];
	$widget_opt = get_option($widget_obj['callback'][0]->option_name);
	$widget_num = $widget_obj['params'][0]['number'];

	if ( isset($widget_opt[$widget_num]['classes']) && !empty($widget_opt[$widget_num]['classes']) )
	$params[0]['before_widget'] = preg_replace( '/class="/', "class=\"{$widget_opt[$widget_num]['classes']} ", $params[0]['before_widget'], 1 );

	return $params;
}

add_filter( 'dynamic_sidebar_params', 'hri_dynamic_sidebar_params' );

/* Widget classes ends */


/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 *
 * To override this in a child theme, remove the filter and optionally add your own
 * function tied to the widgets_init action hook.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_remove_recent_comments_style() {
	global $wp_widget_factory;
	remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
}
add_action( 'widgets_init', 'twentyten_remove_recent_comments_style' );

function hri_help_post_tree_root($pageID) {
	$curID = get_post_ancestors($pageID);

	echo '<ul id="pagetree">';
	hri_help_post_tree($pageID, 0, $curID, 0);
	echo '</ul>';
}

function hri_help_post_tree($pageID, $parentID, $curID, $level = 0) {
	// The Query
	$args = array(
		'post_type' => 'help-page',
		'post_status' => 'publish',
		'post_parent' => $parentID,
		'orderby' => 'menu_order',
		'posts_per_page' => 20
	);
	$the_query = new WP_Query( $args );
	global $post;
	// The Loop
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		if ($pageID == $post->ID) {
			echo '<li class="current_page_item">';
		} else {
			echo '<li>';
		}
		echo '<a href="'. get_permalink() . '">' . get_the_title() . '</a>';
		if (   (   is_array($curID)
				&& in_array($post->ID, $curID))
			|| $pageID == $post->ID
			) {
			echo '<ul class="submenu children">';
			hri_help_post_tree($pageID, $post->ID, $curID);
			echo '</ul>';
		}
		echo '</li>';
	}
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
						response(["<?php _e('No search results','twentyten'); ?>"]);
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
					// response("[<?php _e('No search results','twentyten'); ?>]");
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
						response(["<?php _e('No search results','twentyten'); ?>"]);
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
					// response(["<?php _e('No search results','twentyten'); ?>"]);
					$( "#tags" ).css("background", "#F9F9F9");
				}
			})
		},
		minLength: 2,
		select: function( event, ui ) {

			if( ui.item.label != "<?php _e('No search results','twentyten'); ?>" ) {

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
			$('#title_error').html("<?php _e('Fill the field','twentyten'); ?>");
			error = true;
		}

<?php if ( !is_user_logged_in() ) { ?>
		if($('#newd_username').val() == "") {
			$('#username_error').html("<?php _e('Fill the field','twentyten'); ?>");
			error = true;
		}
		if($('#newd_useremail').val() == "") {
			$('#email_error').html("<?php _e('Fill the field','twentyten'); ?>");
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
		if (el.hasClass('sortactive')){
			el.find('.sortmark').toggleClass('sortreverse');
		} else {
			$('.sortactive').removeClass('sortactive');
			$('.sortmark').addClass('sortmark_grey').removeClass('sortmark');
			el.addClass('sortactive');
			el.find('.sortmark_grey').addClass('sortmark').removeClass('sortmark_grey sortreverse');
		}
	}

	$('.sortactive').each(function(){
		var el = $(this);
		el.find('.sortmark_grey').addClass('sortmark').removeClass('sortmark_grey sortreverse');
	});

	$('.sortoption').click(function(){
		page = 1;
		var el = $(this);
		setSort(el);
		doSearch();
	});

<?php }

?>

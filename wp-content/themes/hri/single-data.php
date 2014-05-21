<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

global $wpdb;

if(isset($_POST['submit_email2owner']) && !empty($_POST['submit_email2owner']) && ctype_digit($_POST['email2owner_pid']) && !empty($_POST['email2owner_from'])) {
	$pid = $_POST['email2owner_pid'];
	$from = strip_tags( $_POST['email2owner_name'] ) . " <" . strip_tags($_POST['email2owner_from']) . ">";
	$subject = strip_tags($_POST['email2owner_subject']);
	$message = strip_tags($_POST['email2owner_message']);
	$post = get_post( $pid, 'OBJECT' );

	$message .= "\r\n\r\n--------------------\r\nViesti on lähetetty hri.fi-palvelusta otsikolle ".$post->post_title."";

	$to = get_post_meta( $pid, 'author_email', true );

	$headers = 'From: '.$from . "\r\n" .
    'Reply-To: '.$from . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

	wp_mail( $to, $subject, $message, $headers );

	$message_sent = true;
} else {
	$message_sent = false;
}

get_header();

if ( have_posts() ) while ( have_posts() ) : the_post();

?>
<script type="text/javascript">
// <!--
var $=jQuery.noConflict();
$(document).ready( function() {
	var $=jQuery.noConflict();

	$('.comment_type_comment .comment-body').each( function(){
		if( $(this).text() == '' ) $(this).hide();
	});

	var url = "<?php bloginfo('template_url'); ?>/images/";

	var star_on = 'star_new_on.png';
	var star_off = 'star_new_off.png';

	$(document).ready(function() {

		function total() {
			var avg = ( parseInt($('#quality-score').val()) + parseInt($('#topicality-score').val()) + parseInt($('#usability-score').val()) ) / 3;
			$('#overall_rating .rate2').css({ width :  avg*15 });
		}

		$('#quality').raty({
			starOn:		star_on,
			starOff:	star_off,
			path:		url,
			hintList:	['bad', 'poor', 'regular', 'good', 'gorgeous'],
			scoreName:	'r1',
			click:		total
		});
		$('#topicality').raty({
			starOn:		star_on,
			starOff:	star_off,
			path:		url,
			hintList:	['bad', 'poor', 'regular', 'good', 'gorgeous'],
			scoreName:	'r2',
			click:		total
		});
		$('#usability').raty({
			starOn:		star_on,
			starOff:	star_off,
			path:		url,
			hintList:	['bad', 'poor', 'regular', 'good', 'gorgeous'],
			scoreName:	'r3',
			click:		total
		});

		$('#displayemail2owner').click(function () {
			if($('#displayemail2owner').html() != '<?php _e('Close the form', 'twentyten'); ?>') {
				$('#email2owner').slideToggle();
				$('#displayemail2owner').html('<?php _e('Close the form', 'twentyten'); ?>');
			} else {
				$('#email2owner').slideToggle();
				$('#displayemail2owner').html('<?php _e('Contact maintainer','twentyten'); ?>');
			}
		});

		
	});

	function show_lang_only() {
		if ( $('#ckan_comments_fi').size() > 0 ) { $('li.comment:not(.lang-fi)').hide(); }
		if ( $('#ckan_comments_en').size() > 0 ) { $('li.comment:not(.lang-en)').hide(); }
		if ( $('#ckan_comments_sv').size() > 0 ) { $('li.comment:not(.lang-sv)').hide(); }
		$('li.comment.lang-').show();
	}
	
	function count_comments_per_lang() {

		var counter_fi = $('.commentlist .lang-fi').size();
		var counter_en = $('.commentlist .lang-en').size();
		var counter_se = $('.commentlist .lang-se').size();
		var counter_null = $('.commentlist .lang-').size();
		var counter_all = counter_fi + counter_se + counter_en + counter_null;

		$('#ckan_comments_en .commentcount').html('&nbsp;('+(counter_en + counter_null)+')');
		$('#ckan_comments_se .commentcount').html('&nbsp;('+(counter_se + counter_null)+')');
		$('#ckan_comments_fi .commentcount').html('&nbsp;('+(counter_fi + counter_null)+')');
		
		$('#ckan_comments_all .commentcount').html('&nbsp;('+counter_all+')');
		
		if (   counter_all == (counter_fi + counter_null)
			&& $('#ckan_comments_fi').size() > 0) {
			$('#comments_ratings').hide();
		}
		if (   counter_all == (counter_se + counter_null)
			&& $('#ckan_comments_se').size() > 0) {
			$('#comments_ratings').hide();
		}
		if (   counter_all == (counter_en + counter_null)
			&& $('#ckan_comments_en').size() > 0) {
			$('#comments_ratings').hide();
		}
	}
	
	count_comments_per_lang();

	$('li.comment_type_rating,#reviews-title').show();
	$('#ckan_comments_and_ratings .header .ratings').hide();

	show_lang_only();
	
	$('#ratertoggle').prependTo( $('#commentform') );
	
	$('#ratertoggle').show();
	
	$('#ckan_comments_and_ratings .scroll_link').show();
	$('#rater').hide();

	function remove_stars( el_id, val ) {
		$(el_id+'-score').val(0);

		for( var i = val; i>0; --i ) {
			var f = function(n){ return function() { $(el_id+'-'+n).attr( 'src', url+star_off ); }; }(i);
			setTimeout(f, val*50 - i*50);
		}

	}

	$('#ratertoggler_close').click(function() {
		$('.comment-form-comment .required').show();

		var fields = new Array("#quality","#topicality","#usability");
		var time = new Array();
		var i = 0;
		
		time[0] = 0;
		time[1] = 0;
		time[2] = 0;

		for( var f in fields ) {
			time[i] = parseInt( $(fields[i]+'-score').val(), 10 );
			++i;
		}
		
/*		$('.quality').raty('readOnly', true);
		$('.topicality').raty('readOnly', true);
		$('.usability').raty('readOnly', true);*/

		setTimeout( function() { remove_stars( fields[0], time[0] ); }, 0 );
		setTimeout( function() { remove_stars( fields[1], time[1] ); }, time[0] * 30 );
		setTimeout( function() { remove_stars( fields[2], time[2] ); }, (time[0]+time[1]) * 30 );

		overall_time = (time[0]+time[1]+time[2])*120;
		
		if (overall_time == 0) {
			$('#ratertoggler_close').hide();
			$('#ratertoggler_open').show();
			$('#rater').slideUp('slow', function() {
				$('#rater').prependTo( $('#ratercontainer'));
			});
		} else {
			$('#overall_rating .rate2').animate( {width:0}, overall_time, function() {
				$('#ratertoggler_close').hide();
				$('#ratertoggler_open').show();
				$('#rater').slideUp('slow', function() {
					$('#rater').prependTo( $('#ratercontainer'));
				});

			});
		}

		return false;
	});
	
	$('#ratertoggle_header').click(function() {
		if ($('#ratertoggler_close').is(':visible')) {
			$('.comment-form-comment .required').show();

			var fields = new Array("#quality","#topicality","#usability");
			var time = new Array();
			var i = 0;

			time[0] = 0;
			time[1] = 0;
			time[2] = 0;

			for( var f in fields ) {
				time[i] = parseInt( $(fields[i]+'-score').val(), 10 );
				++i;
			}

			setTimeout( function() { remove_stars( fields[0], time[0] ); }, 0 );
			setTimeout( function() { remove_stars( fields[1], time[1] ); }, time[0] * 30 );
			setTimeout( function() { remove_stars( fields[2], time[2] ); }, (time[0]+time[1]) * 30 );

			overall_time = (time[0]+time[1]+time[2])*120;

			if (overall_time == 0) {
				$('#ratertoggler_close').hide();
				$('#ratertoggler_open').show();
				$('#rater').slideUp('slow', function() {
					$('#rater').prependTo( $('#ratercontainer'));
				});
			} else {
				$('#overall_rating .rate2').animate( {width:0}, overall_time, function() {
					$('#ratertoggler_close').hide();
					$('#ratertoggler_open').show();
					$('#rater').slideUp('slow', function() {
						$('#rater').prependTo( $('#ratercontainer'));
					});

				});
			}
		} else {
			$('#ratertoggler_open').hide();
			$('#rater').prependTo( $('#ratercontainer_dummy') );
			$('#rater').slideDown();
			$('#ratertoggler_close').show();
			$('.comment-form-comment .required').hide();
		}
		return false;
	});
	
	$('.subscribe-to-comments').insertBefore($('.form-submit'));
	
	$('#reply-title').insertAfter('#ratertoggle');
	$('#reply-title').show();

	// Tab1-1
	$('#ckan_comments_all').click( function() {

		if(!$(this).hasClass('selected')) {
			$('#ckan_comments a:not(.scroll_link)').toggleClass('selected');
		}

		$('li.comment:not(.comment_type_rating)').show();
		$('li.comment').show();
	});

	// Tab1-2
	$('.commentlang').click( function() {

		if(!$(this).hasClass('selected')) {
			$('#ckan_comments a:not(.scroll_link)').toggleClass('selected');
		}

		show_lang_only();
	});
	


	$('#commentform').submit(function() {
		var error = false;
		
		if($('#comment').val() == "" && $('#ratertoggler_open').is(':visible')) {
			//$('#comment_error').html("Täytä kommenttikenttä");
			$("label[for='comment']").html('<span class="formerror"><?php _e('Fill the field','twentyten'); ?></span><?php _e('Comment','twentyten'); ?>');
			error = true;
		}/* else if($('#comment').val().length < 20 && $('#ratertoggler_open').is(':visible')) {
			//$('#comment_error').html("Täytä kommenttikenttä");
			$("label[for='comment']").html('<span class="formerror"><?php _e('Your comment is too short','twentyten'); ?></span><?php _e('Comment','twentyten'); ?>');
			error = true;
		}*/ else {
			$("label[for='comment']").html('<?php _e('Comment','twentyten'); ?>');
		}
		

		<?php if ( !is_user_logged_in() ) { ?>
		if($('#author').val() == "") {
			//$('#author_error').html("Täytä nimi");
			$("label[for='author']").html('<span class="formerror"><?php _e('Fill the field','twentyten'); ?></span><?php _e('Name','twentyten'); ?>');
			error = true;
		} else {
			$("label[for='author']").html('<?php _e('Name','twentyten'); ?>');
		}
		if($('#email').val() == "") {
			//$('#email_error').html("Täytä sähköposti");
			$("label[for='email']").html('<span class="formerror"><?php _e('Fill the field','twentyten'); ?></span><?php _e('Email','twentyten'); ?>');
			error = true;
		} else {
			var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			var address = $('#email').val();
			if(reg.test(address) == false) {
				$("label[for='email']").html('<span class="formerror"><?php _e('Fill valid email','twentyten'); ?></span><?php _e('Email','twentyten'); ?>');
				error = true;
			} else {
				$("label[for='email']").html('<?php _e('Email','twentyten'); ?>');
			}
			
		}
		<?php } ?>

		if (error) {
			error = false;
			return false;
		} 
	});

	$('.comment_type_rating .comment-body').each( function(){

		var tmpstring = $('.comment-body-content', this).text();

//		if( $('.comment-body-content', this).text().trim() == '' ) {
		if( jQuery.trim( tmpstring ) == '' ) {

			$('.rating_container', this).addClass('rating_container_floating');
			$('.comment_ratings_container', this).addClass('comment_ratings_container_wide');
			$(this).css('min-height','60px');

		}
	});
});

// -->
</script>

<?php
$dataSearchUrl = '';
if (ORIGINAL_BLOG_ID == 2) $dataSearchUrl = home_url() . '/fi/data-haku/';
if (ORIGINAL_BLOG_ID == 3) $dataSearchUrl = home_url() . '/en/data-search/';
if (ORIGINAL_BLOG_ID == 4) $dataSearchUrl = home_url() . '/se/data-sok/';
?>

		<div id="left-column">
			
			<div class="entry-utility">
			
				<a href="<?php

	echo $dataSearchUrl . '?';
	
	echo str_replace( '&', '&amp;', htmlspecialchars_decode($_COOKIE['searchString'])); ?>" class="blocklink">
	<?php
		
	if ( isset( $_SERVER['HTTP_REFERER'] ) && (strpos($_SERVER['HTTP_REFERER'], 'fi/data-haku/') !== false || strpos($_SERVER['HTTP_REFERER'], 'en/data-search/') !== false) ) _e('Back to search results','twentyten');
	else _e('Data search','twentyten');
	?>
				</a>
			
			
				<?php 
				    /**
				    * Tää on Jarin viritys, täytyy säätää vielä noi localet (alla alkuperäinen, joka ei toiminu)
				    **/
				
				    global $post;
					$categories = get_the_category($post->ID);
					if (count($categories)>0) {
					?>
	 		    	<span class="cat-links">
 		        		<span class="entry-utility-prep entry-utility-prep-cat-links"><?php echo __('Categories','twentyten'); ?></span>
					<?php
					    foreach($categories as $category) { 
	                        echo '<a href="'.$dataSearchUrl.'?search_text=&amp;category=' . $category->term_id . '">'  . $category->name . '</a>';
	                    }
					?>
					</span>
					<?php
					
					}
			    ?>
			
			<div class="clear"></div>
			
			    <?php /* if ( count( get_the_category($post->ID) ) ) : ?>
					<span class="cat-links">
						<?php //printf( __( '<span class="%1$s">Categories</span> %2$s', 'twentyten' ), 'entry-utility-prep entry-utility-prep-cat-links', get_the_category_list(' ') ); ?>
					</span>
					<div class="clear"></div>
					
				<?php endif; */ ?>
				
				<?php
					$tags_list = get_the_term_list_hri( '', ' ' );
					if ( $tags_list ):
						global $locale;
				?>
					<span class="tag-links <?php echo $locale; ?>">
						<?php echo '<span class="entry-utility-prep entry-utility-prep-tag-links">' . __('Tags', 'twentyten') . '</span>' . $tags_list; ?>
					</span>
					<div class="clear"></div>
					
				<?php endif; ?>

				<div class="clear"></div>
				
				<?php edit_post_link( __( 'Edit', 'twentyten' ), '<div class="edit-link">', '</div>' ); ?>
			</div><!-- .entry-utility -->
			<?php
			hri_add_this();
			?>
		</div>
		
		<div id="container" class="middle-column">
			<?php

			$discussions = false;
			if( ORIGINAL_BLOG_ID == 2 ) $discussions = '/keskustelut/';
			if( ORIGINAL_BLOG_ID == 3 ) $discussions = '/discussions/';

			if ( $discussions && strpos($_SERVER['HTTP_REFERER'], $discussions ) !== false ) {

				?><a href="<?php echo ROOT_URL, '/', HRI_LANG, $discussions; ?>"><?php _e('Back to discussions', 'twentyten'); ?></a><?php

			}

			?>
			<div id="content" role="main">

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<?php hri_rating(); ?>
					
					<h1 class="entry-title"><?php data_title( substr( get_bloginfo('language'), 0, 2 ) ); //the_title(); ?></h1>

					<div class="entry-content">

						<?php
							$notes = notes( false, false );

							echo nl2br(links_in_text( $notes ));
						?>
					</div><!-- .entry-content -->

					<?php
						// Hide Downloads & links if there isn't any
					if( get_post_meta( $post->ID, "resources_0_id", true ) ) {
					?>
					<h2><?php _e('Downloads & links','twentyten'); ?></h2>
					
					<?php
					
						$i = 0;
					
						while ( get_post_meta( $post->ID, "resources_{$i}_id", true ) ) { 
							$data_link = get_post_meta( $post->ID, "resources_{$i}_url", true );
							$data_format = get_post_meta( $post->ID, "resources_{$i}_format", true );
							$data_gacount = get_post_meta( $post->ID, "resources_{$i}_gacount", true );
							$ga_str = '';
							if ($data_gacount
								&& $data_gacount != '0') {
								$ga_str = '<span class="data_gacount">'.$data_gacount.'</span>';
							}
							?>
					
							<a target="_blank" onclick="_gaq.push(['_trackEvent', 'Ladattavat tiedostot ja linkit', '<?php echo $data_format; ?>', '<?php echo $data_link; ?>']);" href="<?php echo $data_link; ?>" class="ckan-data-link"><?php echo get_post_meta( $post->ID, "resources_{$i}_description", true ); ?><span class="data-format"><?php echo $data_format; ?></span><?php echo $ga_str; ?></a>
					
					<?php
							$i++;
					
						}
					}

					// Hide Information if there isn't any
					$created = get_post_meta( $post->ID, 'metadata_created', true );
					$modified = get_post_meta( $post->ID, 'metadata_modified', true );
					$url = get_post_meta( $post->ID, 'url', true );
					$license = get_post_meta( $post->ID, 'license', true );
					$license_url = get_post_meta( $post->ID, 'license_url', true );
					$maintainer = get_post_meta( $post->ID, 'maintainer', true );
					$author = get_post_meta( $post->ID, 'author', true );
					
					if( $modified || $url || $license || $maintainer || $author ) {
					?>
					<h2><?php _e('Information','twentyten'); ?></h2>
					<table class="ckan thright"><?php

						if ( $created ) echo '<tr><th scope="row">' . __('Created','twentyten') . '</th><td>' . date( 'j.n.Y', strtotime( $created ) ) . '</td></tr>';

						if ( $modified ) echo '<tr><th scope="row">' . __('Modified','twentyten') . '</th><td>' . date( 'j.n.Y', strtotime( $modified ) ) . '</td></tr>';

						if ( $license ) {
							?><tr><th scope="row"><?php _e('License','twentyten'); ?></th><td><?php

							if( isset( $license_url ) && $license_url != '' ) {

								?><a href="<?php echo $license_url ?>" target="_blank"><?php echo $license; ?></a><?php

							} else echo $license;

							?></td></tr><?php

						}

						if ( $maintainer ) echo '<tr><th scope="row">' . __('Maintainer','twentyten') . '</th><td>' . $maintainer . '<br />';
						else if($author) echo '<tr><th scope="row">' . __('Maintainer','twentyten') . '</th><td>' . $author . '<br />';

						if($maintainer || $author) {
						?>
						<a id="displayemail2owner" class="blocklink">
						<?php
						_e('Contact maintainer','twentyten');

						?>
						</a></td></tr>

						<tr><td colspan="2">

<div id="email2owner" style="display: none">
	<form action="" method="post">
		<label for="email2owner_name"><?php echo _e('Name', 'twentyten'); ?></label>
		<input type="text" name="email2owner_name" id="email2owner_name" /><br />
		<label for="email2owner_from"><?php echo _e('Your email', 'twentyten'); ?></label>
		<input type="text" name="email2owner_from" id="email2owner_from" /><br />
		<label for="email2owner_subject"><?php echo _e('Subject', 'twentyten'); ?></label>
		<input type="text" name="email2owner_subject" id="email2owner_subject" /><br />
		<label for="email2owner_message"><?php echo _e('Message', 'twentyten'); ?></label>
		<textarea name="email2owner_message" id="email2owner_message" cols="45" rows="8" required="required"></textarea>
		<input type="hidden" name="email2owner_pid" value="<?php echo $post->ID; ?>" />
		<input name="submit_email2owner" type="submit" id="email2owner_submit" value="<?php echo _e('Send', 'twentyten'); ?>" />
	</form>
</div>

						</td></tr><?php
						}
						
						if ( $url ) echo '<tr><th scope="row">' . __('Maintainer page','twentyten') . '</th><td>' . hri_make_short_link($url) . '</td></tr>';

						function ckan_extra_fields( $query_field, $title , $type = 'string') {

							global $post,$wpdb;
							$results = $wpdb->get_results("SELECT m.meta_value FROM {$wpdb->prefix}postmeta m, {$wpdb->prefix}posts p WHERE meta_key LIKE '$query_field' AND m.post_id = p.ID AND p.ID = " . $post->ID);

							if ($results) {

								echo '<tr><th scope="row">' . $title . '</th><td>';
								$g = null;

								foreach ($results as $r) {
									if ($type == 'date') {
										$g[] = date( 'j.n.Y', strtotime( $r->meta_value ) );
									} else {
										$g[] = $r->meta_value;
									}
								}

								echo implode(', ', $g) . '</td></tr>';
							}
						}

						ckan_extra_fields('extras\_agency%', __('Agency','twentyten'));
						ckan_extra_fields('extras\_department%', __('Department','twentyten'));
						ckan_extra_fields('extras\_geographic\_coverage\_%', __('Geographic coverage','twentyten'));
						ckan_extra_fields('extras\_geographic\_granularity\_%', __('Geographic granularity','twentyten'));
						ckan_extra_fields('extras\_source%', __('Source','twentyten'));
						ckan_extra_fields('extras\_update\_frequency%', __('Update frequency','twentyten'));
						ckan_extra_fields('extras\_temporal\_granularity\_%', __('Temporal granularity','twentyten'));
						ckan_extra_fields('extras\_temporal\_coverage', __('Temporal coverage','twentyten'));
						ckan_extra_fields('extras\_temporal\_coverage-from', __('Temporal coverage from','twentyten'), 'date');
						ckan_extra_fields('extras\_temporal\_coverage-to', __('Temporal coverage to','twentyten'), 'date');
						ckan_extra_fields('extras\_temporal\_granularity-other', __('Other','twentyten'));

						if (ORIGINAL_BLOG_ID == 3) $externalref = get_post_meta( $post->ID, 'extras_external_reference_en', true );
						elseif (ORIGINAL_BLOG_ID == 4) $externalref = get_post_meta( $post->ID, 'extras_external_reference_se', true );
						else $externalref = get_post_meta( $post->ID, 'extras_external_reference', true );

						if ( $externalref ) echo '<tr><th>' . __('External reference','twentyten') . '</th><td>' . hri_make_short_link($externalref) . '</td></tr>';

						?></table><?php

					}

				?>
	

					<div id="ckan_comments_and_ratings">
						
						<div class="header">
							<a style="float: right;" href="#ratertoggle" class="scroll_link"><?php _e('Comment or rate','twentyten'); ?></a>
							<h2 class="comments_and_ratings"><?php _e('Comments and ratings', 'twentyten'); ?></h2>
						</div>

						<div id="comments_ratings">

							<div id="ckan_comments" class="tab active">
							<?php
							if ( get_bloginfo('language') == 'fi-FI' ) echo '<div>Näytä:</div><a class="selected commentlang" id="ckan_comments_fi">Suomeksi<span class="commentcount"></span></a><a id="ckan_comments_all">Kaikki<span class="commentcount"></span></a>';
							elseif ( get_bloginfo('language') == 'sv-SE' ) echo '<div>Visa:</div><a class="selected commentlang" id="ckan_comments_sv">Svenska<span class="commentcount"></span></a><a id="ckan_comments_all">???<span class="commentcount"></span></a>';
							else echo '<div>Show:</div><a class="selected commentlang" id="ckan_comments_en">English<span class="commentcount"></span></a><a id="ckan_comments_all">All<span class="commentcount"></span></a>';

							?></div>

<?php							
/*							<div id="ckan_ratings" class="tab passive">
								<a href="#reply-title" class="scroll_link"><__?php _e('Review','twentyten'); ?></a>
							</div>
*/
?>
					</div>
<?php
$has_ratings = $wpdb->get_var("SELECT meta_id FROM {$wpdb->commentmeta} cm
INNER JOIN {$wpdb->comments} c ON c.comment_ID = cm.comment_id AND c.comment_approved = 1
INNER JOIN {$wpdb->posts} p ON p.ID = c.comment_post_ID AND p.ID = {$post->ID}
WHERE cm.meta_key LIKE '\_hri\_rating_' LIMIT 0,1;");

if ( $has_ratings ) {
?>

						<div id="ratingsummary" style="display:none;">
						<table>
							<tr>
								<td class="col1"><?php _e('Description','twentyten'); ?></td>
								<td class="col2"><?php


$avg = $wpdb->get_var("SELECT AVG(meta_value) FROM {$wpdb->prefix}commentmeta m INNER JOIN {$wpdb->prefix}comments c ON m.comment_id = c.comment_ID WHERE c.comment_post_ID = {$post->ID} AND m.meta_key = '_hri_rating1' AND c.comment_approved = 1");

echo '<div class="ratingbg"><div title="' . round( $avg, 1 ) . '/5" class="rating2" style="width:' . round( $avg * 20, 0) . '%"></div></div>';

								?></td>
								<td class="ratingdesc"><?php _e('Are data description and information comprehensive and accurate?','twentyten'); ?></td>
							</tr>
							<tr>
								<td class="col1"><?php _e('Relevance','twentyten'); ?></td>
								<td class="col2"><?php

global $wpdb;
$avg = $wpdb->get_var("SELECT AVG(meta_value) FROM {$wpdb->prefix}commentmeta m INNER JOIN {$wpdb->prefix}comments c ON m.comment_id = c.comment_ID WHERE c.comment_post_ID = {$post->ID} AND m.meta_key = '_hri_rating2' AND c.comment_approved = 1");

echo '<div class="ratingbg"><div title="' . round( $avg, 1 ) . '/5" class="rating2" style="width:' . round( $avg * 20, 0) . '%"></div></div>';

								?></td>
								<td class="ratingdesc"><?php _e('Does the data provide useful information?','twentyten'); ?></td>
							</tr>
							<tr>
								<td class="col1"><?php _e('Usability','twentyten'); ?></td>
								<td class="col2"><?php

global $wpdb;
$avg = $wpdb->get_var("SELECT AVG(meta_value) FROM {$wpdb->prefix}commentmeta m INNER JOIN {$wpdb->prefix}comments c ON m.comment_id = c.comment_ID WHERE c.comment_post_ID = {$post->ID} AND m.meta_key = '_hri_rating3' AND c.comment_approved = 1");

echo '<div class="ratingbg"><div title="' . round( $avg, 1 ) . '/5" class="rating2" style="width:' . round( $avg * 20, 0) . '%"></div></div>';

								?></td>
								<td class="ratingdesc"><?php _e('Is the data in practical format and structure?','twentyten'); ?></td>
							</tr>
							<tr>
								<td class="col1"><?php _e('Overall rating','twentyten'); ?></td>
								<td class="col2"><?php hri_rating(); ?></td>
								<td></td>
							</tr>
						</table>
						</div><!-- #ratingsummary -->
<?php } ?>

						<div class="clear"></div>
					</div>


				</div><!-- #post-## -->
				<?php comments_template( '/comments_ratings.php', true );

					if ( $discussions && strpos($_SERVER['HTTP_REFERER'], $discussions ) !== false ) {

						?><a href="<?php echo ROOT_URL, '/', HRI_LANG, $discussions; ?>"><?php _e('Back to discussions', 'twentyten'); ?></a><?php

					}

				?>
			</div><!-- ncontent -->
		</div><!-- #container -->

<?php endwhile; // end of the loop. ?>

<?php get_sidebar('data'); ?>
<?php get_footer(); ?>

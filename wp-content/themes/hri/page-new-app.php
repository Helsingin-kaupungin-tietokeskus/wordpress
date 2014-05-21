<?php
/**
 * Template name: new application form
 *
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

global $wpdb;

hri_tinymce('hrimceEditor');
hri_swfupload();

if ( isset($_GET['success']) ) {

//	$content = '<h1>' . __('A new application created successfully','twentyten') . '</h1><p>' . __('The application is held for moderation and will be published soon.','twentyten') . '</p>';
	$content = '<h1>' . __('A new application created successfully','twentyten') . '</h1><p>' . __('Admins of HRI will moderate your application submission and if needed, will be in touch with you. You can reach HRI-admins from address <a href="mailto:hri-info@forumvirium.fi">hri-info@forumvirium.fi</a>.','twentyten') . '</p>';

}

if ( isset($_POST['newd_submit']) ) {
	
	switch_to_blog(1);
	
	$author_id = ( is_user_logged_in() ) ? $current_user->ID : 1;
	
	if ( $author_id == 1 && !is_user_logged_in() ) {
		
		$newd_username = mysql_real_escape_string( strip_tags($_POST['newd_username']) );
		$newd_useremail = mysql_real_escape_string( filter_var( $_POST['newd_useremail'], FILTER_VALIDATE_EMAIL ) );

		$content = null;

		if ( strlen( $newd_username ) < 3) $content = '<div class="error">' . __('Name must be atleast three characters','twentyten') . '</div>';
		if ( $newd_useremail === false ) $content .= '<div class="error">' . __('Email is required','twentyten') . '</div>';
	}

	if ( isset($_POST['new_app_URL']) && $_POST['new_app_URL'] ) $new_app_URL = mysql_real_escape_string( strip_tags($_POST['new_app_URL']) );
	if ( isset($_POST['new_app_author']) && $_POST['new_app_author'] ) $new_app_author = mysql_real_escape_string( strip_tags($_POST['new_app_author']) );
	if ( isset($_POST['new_app_author_URL']) && $_POST['new_app_author_URL'] ) $new_app_author_URL = mysql_real_escape_string( strip_tags($_POST['new_app_author_URL']) );
	
	if ( !isset( $content ) ) {

		global $hri_kses_args;

		// Insert new post
		$insert = array(
			'post_title' => esc_html( strip_tags( $_POST['newd_title'] ) ),
			'post_name' => sanitize_title($_POST['newd_title'] , 'new-application'),
			'post_type' => 'application',
			'post_status' => 'pending',
			'post_content' => wp_kses($_POST['newd_text'], $hri_kses_args ),
			'post_author' => $author_id,
			'post_date_gmt' => date( 'Y-m-d H:i:s', time() )
		);
		
		$newID = wp_insert_post( $insert );
		
		if ($_POST['newd_text_2'] != '') {
			update_post_meta( $newID, 'comments_to_hri_admins', $_POST['newd_text_2'] );
		}
		$insert_header = false;
		// No exit() after header() to allow saving meta-data
		if ( $newID ) $insert_header = true;
		
		else $content = '<div class="error">Something failed.</div>';

		
		if ( !$newID ) $content = '<div class="error">Something failed.</div>';
		
		// Insert meta to the new post

		if ( $author_id == 1 && !is_user_logged_in() ) {
		
			update_post_meta( $newID, 'user_name', $newd_username );
			update_post_meta( $newID, 'user_email', $newd_useremail );
		
		}

		if ( isset( $_POST['appcat']) && !empty( $_POST['appcat'] ) ) {
			foreach ( $_POST['appcat'] as $appcat ) {
				$appcats[] = (int) $appcat;
			}
			wp_set_post_terms( $newID, $appcats, 'hri_appcats' );
		}

		if ( isset($new_app_URL) ) {
		
			if ( strtolower( substr( $new_app_URL, 0, 4 ) ) != 'http' ) $new_app_URL = 'http://' . $new_app_URL;
			update_post_meta( $newID, 'app_URL', $new_app_URL );

		}
		if ( isset($new_app_author) ) update_post_meta( $newID, 'app_author', $new_app_author );
		if ( isset($new_app_author_URL) ) {
		
			if ( strtolower( substr( $new_app_author_URL, 0, 4 ) ) != 'http' ) $new_app_author_URL = 'http://' . $new_app_author_URL;
			update_post_meta( $newID, 'app_author_URL', $new_app_author_URL );
			
		}
		
		if ( isset( $_POST['datastring'] ) && !empty( $_POST['datastring'] ) ) hri_set_data_from_string( $newID );
		if ( isset( $_POST['tagstring'] ) && !empty( $_POST['tagstring'] ) ) hri_set_tags_from_string( $newID );

		if( isset( $_POST['hri_featured'] )) {

			$featured = (int) $_POST['hri_featured'];
			if( $featured ) update_post_meta( $newID, '_thumbnail_id', $featured );
		}

		if( isset( $_POST['attachments'] ) && !empty( $_POST['attachments'] ) ) {
			foreach( $_POST['attachments'] as $a ) {
				$a = (int) $a;

				$updates = array(
					'ID' => $a,
					'post_parent' => $newID
				);

				wp_update_post( $updates );

			}
		}
		if ( $insert_header ) header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?success');
	}

	restore_current_blog();
	
}

get_header();

restore_current_blog();

?>

<script type="text/javascript">

var $=jQuery.noConflict();

$(document).ready(function(){

	var url = "<?php echo ROOT_URL; ?>/wp-admin/admin-ajax.php";

	<?php

	hri_js_data_autocomplete();

//	hri_js_tags_autocomplete();

	hri_js_validate_newcontentform();

	?>

	$('#uploads a.delete').live('click',function(){
		$(this).parent().fadeOut(250,function(){ $(this).remove(); });
	});
	
});

</script>

	<div id="left-column">
		<a href="<?php
			
	if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL . '/fi/sovellukset/';
	if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL . '/en/applications/';
			
			?>" class="blocklink"><?php _e('All applications','twentyten'); ?></a>
	</div>

		<div id="container" class="form-content-container">
			<div id="content" role="main">

				<?php
					if ( isset( $content ) ) echo $content;
					else {
				?>
			
				<h1 class="entry-title"><?php _e('Application submission', 'twentyten'); ?></h1>

				<form action="" id="newcontentform" method="post" enctype="multipart/form-data">
				
<?php hri_form_is_logged_in(); ?>
					<div class="newd_form_item_container">
						<label for="newd_title"><?php _e('Application name', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="title_error"></span>
						<input type="text" name="newd_title" id="newd_title" />
					</div>
					<div class="input_with_helptext">
						<div class="newd_form_item_container">
							<label for="newd_text"><?php _e('Application description', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="text_error"></span>
							<textarea cols="40" rows="5" class="hrimceEditor" name="newd_text" id="newd_text"></textarea>
						</div>
						<span class="helptext"><br /><?php _e('Describe your application as informative as you can. Keep the description short.','twentyten'); ?></span>
					</div>
					<div class="input_with_helptext">
						<div class="newd_form_item_container">
							<span id="spanButtonPlaceholder"></span>
							<div id="divFileProgressContainer"></div>
							<div id="uploads"></div>
						</div>
						<span class="helptext"><?php _e('Approved filetypes: jpg, gif, png. Max size 1 MB. Featured image will be cropped to square.','twentyten'); ?></span>
					</div>

					<div class="newd_form_item_container">
						<label for="new_app_URL"><?php _e('Application\'s URL', 'twentyten'); ?></label><span class="formerror" id="app_url_error"></span>
						<input type="text" name="new_app_URL" id="new_app_URL" />
					</div>
					<h2><?php _e('Data', 'twentyten'); ?></h2>
					<em><?php _e('Is this application related to a data?','twentyten'); ?></em>
					<div class="input_with_helptext">
						<div class="newd_form_item_container">
							<div class="filter_container">
								<input type="text" name="data" id="data" placeholder="<?php _e('Data','twentyten') ?>" />
								<div id="data_filters" class="filters">
								<?php if ( isset( $_POST['linked_id'] ) ) hri_add_filter(); ?>
								</div>
								<input type="hidden" name="datastring" id="datastring" />
							</div>
						</div>
						<span class="helptext"><?php _e('Find by data title. You can add application to multiple data by doing multiple queryes.','twentyten'); ?></span>
					</div>
					<div class="newd_form_item_container" style="display: none;">
						<div class="filter_container">
							<input type="text" name="tags" id="tags" placeholder="<?php _e('Tags','twentyten') ?>" />
							<div id="tag_filters" class="filters">
							</div>
							<input type="hidden" name="tagstring" id="tagstring" />
						</div>
					</div>
<?php

//switch_to_blog(1);
//$res = $wpdb->get_results("SELECT tt.term_id, tx.description, tx.parent, tt.name FROM {$wpdb->term_taxonomy} tx, {$wpdb->terms} tt WHERE tt.term_id = tx.term_id AND tx.taxonomy = 'hri_appcats' ORDER BY tx.parent ASC", ARRAY_A); /* AND tx.count > 0 */
//restore_current_blog();

$res = false;
if ( $res ) {

	?><div class="newd_form_item_container"><h2><?php _e('Category','twentyten'); ?></h2><?php

	foreach( $res as $r ) {
		// Todo: app-categorioiden näyttäminen sisennetysti toimii vain toiselle tasolle asti
		// 1st level app-category
		if ( $r['parent'] == 0 ) $sorted[ $r['term_id'] ]['appcat'] = $r;
		else {
			// 2nd level
			if ( isset($sorted[ $r['parent'] ]) ) $sorted[ $r['parent'] ][ $r['term_id'] ]['appcat'] = $r;
			
			// 3rd level?
		}
	}

	foreach( $sorted as $s ) {

		foreach ( $s as $key => $s2 ) {

			if ( $key == 'appcat' ) {

				$r = $s['appcat'];
				$style = null;

			} else {

				$r = $s[$key]['appcat'];
				$style = ' style="margin-left:20px;"';

			}

			?><input<?php echo $style; ?> type="checkbox" value="<?php echo $r['term_id']; ?>" name="appcat[]" id="appcat<?php echo $r['term_id']; ?>" /><label for="appcat<?php echo $r['term_id']; ?>"><?php echo $r['name']; ?></label><br /><?php

		}
	}
	

	?></div><?php

}
?>

				<h2><?php _e('Author', 'twentyten'); ?></h2>
				<div class="newd_form_item_container">
					<label for="new_app_author"><?php _e('Application author', 'twentyten'); ?></label><span class="formerror" id="app_author_error"></span>
					<input type="text" name="new_app_author" id="new_app_author" />
				</div>
				<div class="newd_form_item_container">
					<label for="new_app_author_URL"><?php _e('Application author\'s URL', 'twentyten'); ?></label><span class="formerror" id="app_author_url_error"></span>
					<input type="text" name="new_app_author_URL" id="new_app_author_URL" />
				</div>
				<?php
				if ( !is_user_logged_in() ) {

					$author = wp_get_current_commenter();

				?>
						<h2><?php _e('Your info', 'twentyten'); ?></h2>
						<div class="input_with_helptext">
							<div class="newd_form_item_container">
								<label for="newd_username"><?php _e('Name', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="username_error"></span>
								<input type="text" name="newd_username" id="newd_username" value="<?php if ( isset( $author['comment_author'] ) ) echo $author['comment_author']; ?>" />
							</div>
						</div>
						<div class="input_with_helptext">
							<div class="newd_form_item_container">
								<label for="newd_useremail"><?php _e('Email', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="email_error"></span>
								<input type="text" name="newd_useremail" id="newd_useremail" value="<?php if ( isset( $author['comment_author_email'] ) ) echo $author['comment_author_email']; ?>" />
							</div>
							<span class="helptext"><br /><?php _e('Your email won\'t be published.','twentyten'); ?></span>
						</div>

				<?php } ?>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
						<label for="newd_text_2"><?php _e('Other / Your message to HRI admins', 'twentyten'); ?></label>
						<textarea cols="40" rows="5" name="newd_text_2" id="newd_text_2"></textarea>
					</div>
					<span class="helptext"><br /><?php _e('Other information related to the application or special instructions related to publishing.','twentyten'); ?></span>
				</div><br />
				<div class="newd_form_item_container">
					<p class="form-submit" style="text-align: right;">
						<input type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Submit','twentyten');?>" />
					</p>
				</div>
				</form>

				<?php } ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>

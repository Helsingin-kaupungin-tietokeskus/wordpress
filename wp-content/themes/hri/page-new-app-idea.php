<?php
/**
 * Template name: new application idea form
 *
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

hri_tinymce('hrimceEditor');

if ( isset($_GET['success']) ) {

	$content = '<h1>' . __('A new application idea submitted successfully','twentyten') . '</h1><p>' . __('The application idea is held for moderation and will be published soon.','twentyten') . '</p>';

}

if ( isset($_POST['newd_submit']) ) {
	
//	switch_to_blog(1);
	
	$author_id = ( is_user_logged_in() ) ? $current_user->ID : 1;
	
	if ( $author_id == 1 && !is_user_logged_in() ) {
		
		$newd_username = mysql_real_escape_string( strip_tags($_POST['newd_username']) );
		$newd_useremail = filter_var( $_POST['newd_useremail'], FILTER_VALIDATE_EMAIL );
		
		if ( strlen( $newd_username ) < 3) $content = '<div class="error">' . __('Name must be atleast three characters','twentyten') . '</div>';
		if ( $newd_useremail === false ) $content .= '<div class="error">' . __('Email is required','twentyten') . '</div>';
		
	}
	
	if ( !isset( $content ) ) {

		global $hri_kses_args;
		// Insert new post
		$insert = array(
			'post_title' => esc_html( strip_tags( $_POST['newd_title'] ) ),
			'post_name' => sanitize_title($_POST['newd_title'] , 'new-application-idea'),
			'post_type' => 'application-idea',
			'post_status' => 'pending',
			'post_content' => wp_kses($_POST['newd_text'], $hri_kses_args ),
			'post_author' => $author_id,
			'post_date_gmt' => date( 'Y-m-d H:i:s', time() )
		);
		
		$newID = wp_insert_post( $insert );

		if ($_POST['newd_text_2'] != '') {
			update_post_meta( $newID, 'comments_to_hri_admins', $_POST['newd_text_2'] );
		}

		// No exit() after header() to allow saving meta-data
		if ( $newID ) header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?success');
		
		else $content = '<div class="error">Something failed.</div>';


		
		if ( $author_id == 1 && !is_user_logged_in() ) {
		
			update_post_meta( $newID, 'user_name', $newd_username );
			update_post_meta( $newID, 'user_email', $newd_useremail );
		
		}

		if ( isset( $_POST['datastring'] ) && !empty( $_POST['datastring'] ) ) hri_set_data_from_string( $newID );
		if ( isset( $_POST['tagstring'] ) && !empty( $_POST['tagstring'] ) ) hri_set_tags_from_string( $newID );

	}
	
//	restore_current_blog();
	
}

get_header();

restore_current_blog();

?>

<script type="text/javascript">
// <!--
var $=jQuery.noConflict();

$(document).ready(function(){
						   
	var url = "<?php restore_current_blog(); echo home_url(); ?>/wp-admin/admin-ajax.php";

	<?php

	hri_js_data_autocomplete();

//	hri_js_tags_autocomplete();

	hri_js_validate_newcontentform();

	?>
	
});
// -->
</script>

	<div id="left-column">
		<a href="<?php
			
	if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL . '/fi/sovellusideat/';
	if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL . '/en/application-ideas/';
			
			?>" class="blocklink"><?php _e('All application ideas','twentyten'); ?></a>
	</div>

		<div id="container" class="form-content-container">
			<div id="content" role="main">

				<?php
					if ( isset( $content ) ) echo $content;
					else {
				?>
			
				<h1 class="entry-title"><?php _e('Submit a new application idea', 'twentyten'); ?></h1>
				
				<form action="" id="newcontentform" method="post">

<?php hri_form_is_logged_in(); ?>
				<div class="newd_form_item_container">
					<label for="newd_title"><?php _e('Idea name', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="title_error"></span>
					<input type="text" name="newd_title" id="newd_title" />
				</div>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
						<label for="newd_text"><?php _e('Idea description', 'twentyten'); ?></label>
						<textarea cols="40" rows="5" class="hrimceEditor" name="newd_text" id="newd_text"></textarea>
					</div>
					<span class="helptext"><br /><?php _e('Describe your idea as informative as you can.','twentyten'); ?></span>
				</div>
				
<br />
				<h2><?php _e('Data', 'twentyten'); ?></h2>
				<em><?php _e('Is this idea related to a data published in HRI.','twentyten'); ?></em>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
						<div class="filter_container">
							<div class="input_with_helptext">
								<input type="text" name="data" id="data" placeholder="<?php _e('Search by data name','twentyten') ?>" />
							</div>
							<div id="data_filters" class="filters">
							<?php if ( isset( $_POST['linked_id'] ) ) hri_add_filter(); ?>
							</div>
							<input type="hidden" name="datastring" id="datastring" />
						</div>
					</div>
					<span class="helptext"><?php _e('Find by data title. You can add discussion to multiple data by doing multiple queryes.','twentyten'); ?></span>
				</div>


<?php /*		<div class="filter_container">
					<input type="text" name="tags" id="tags" placeholder="<?php _e('Tags','twentyten') ?>" />
					<div id="tag_filters" class="filters">
					</div>
					<input type="hidden" name="tagstring" id="tagstring" />
				</div>*/ ?>

<?php if ( !is_user_logged_in() ) { ?>
				<h2><?php _e('Your info', 'twentyten'); ?></h2>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
						<label for="newd_username"><?php _e('Name', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="username_error"></span>
						<input type="text" name="newd_username" id="newd_username" value="<?php if ( isset( $author['comment_author'] ) ) echo $author['comment_author']; ?>" />
					</div>
					<span class="helptext"><br /><?php _e('Your name is published in the new discussion.','twentyten'); ?></span>
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
		<label for="newd_text"><?php _e('Other / Your message to HRI admins', 'twentyten'); ?></label>
		<textarea cols="40" rows="5" name="newd_text_2" id="newd_text_2"></textarea>
	</div>
	<span class="helptext"><br /><?php _e('Other information related to the idea or special instructions related to publishing.','twentyten'); ?></span>
</div><br />
<div class="newd_form_item_container">
<?php _e('Your idea is moderated and it can be merged to other similar ideas. Chosen ideas are published on this website.','twentyten'); ?>
</div>
				<div class="newd_form_item_container">
					<p class="form-submit" style="text-align: right;">
						<input type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Submit', 'twentyten'); ?>" />
					</p>
				</div>
				</form>

				<?php } ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>

<?php
/**
 * Template name: new discussion form
 *
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

global $wpdb;

hri_tinymce();

if ( isset($_GET['success']) ) {

	$content = '<h1>' . __('A new discussion started successfully','twentyten') . '</h1>';
	if ( !isset($_GET['published']) ) $content .= '<p>' . __('The discussion is held for moderation and will be published soon.','twentyten') . '</p>';

}

if ( isset($_POST['newd_submit']) ) {
	
	switch_to_blog(1);
	
	$author_id = ( is_user_logged_in() ) ? $current_user->ID : 1;
	
	if ( $author_id == 1 && !is_user_logged_in() ) {
		
		$newd_username = mysql_real_escape_string( strip_tags($_POST['newd_username']) );
		$newd_useremail = mysql_real_escape_string( filter_var( $_POST['newd_useremail'], FILTER_VALIDATE_EMAIL ) );

		$content = null;

		if ( strlen( $newd_username ) < 3) $content .= '<div class="error">' . __('Name must be atleast three characters','twentyten') . '</div>';
		if ( $newd_useremail == false ) $content .= '<div class="error">' . __('Email is required','twentyten') . '</div>';
		
	}
	
	if ( !isset( $content ) ) {

		if ( is_user_logged_in() ) $approved = 1;
		else $approved = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}comments WHERE comment_author = '$newd_username' AND comment_author_email = '$newd_useremail' AND comment_approved = 1");

		if ( $approved > 0 ) $status = 'publish';
		else $status = 'pending';

		global $hri_kses_args;

		// Insert new post
		$insert = array(
			'post_title' => esc_html( strip_tags( $_POST['newd_title'] ) ),
			'post_name' => sanitize_title($_POST['newd_title'] , 'new-discussion'),
			'post_type' => 'discussion',
			'post_status' => $status,
			'post_content' => wp_kses($_POST['newd_text'], $hri_kses_args ),
			'post_author' => $author_id,
			'post_date_gmt' => date( 'Y-m-d H:i:s', time() )
		);
		
		$newID = wp_insert_post( $insert );
		
		// No exit() after header() to allow saving meta-data
		if ( $newID && $approved > 0 ) header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?success&published');
		elseif ( $newID ) header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?success');
		else $content = '<div class="error">Something failed.</div>';
		
		// Insert meta to the new post

		if ( in_array( $_POST['lang'], array( 'fi','en','se' ) ) ) update_post_meta( $newID, 'lang', $_POST['lang'] );

		if ( $author_id == 1 && !is_user_logged_in() ) {
		
			update_post_meta( $newID, 'user_name', $newd_username );
			update_post_meta( $newID, 'user_email', $newd_useremail );
		
		}

		if ( isset( $_POST['datastring'] ) && !empty( $_POST['datastring'] ) ) hri_set_data_from_string( $newID );
		if ( isset( $_POST['tagstring'] ) && !empty( $_POST['tagstring'] ) ) hri_set_tags_from_string( $newID );

	}
	
	restore_current_blog();
	
}

get_header();

restore_current_blog();

?>

<script type="text/javascript">
// <!--
var $=jQuery.noConflict();

$(document).ready(function(){

	var url = "<?php echo ROOT_URL; ?>/wp-admin/admin-ajax.php";

	<?php

	hri_js_data_autocomplete();

	hri_js_tags_autocomplete();

	hri_js_validate_newcontentform();

	?>
	
});
// -->
</script>

	<div id="left-column">
		<a href="<?php
			
	if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL . '/fi/keskustelut/';
	if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL . '/en/discussions/';
			
			?>" class="blocklink"><?php _e('All discussions','twentyten'); ?></a>
	</div>

		<div id="container" class="form-content-container">
			<div id="content" role="main">

				<?php
					if ( isset( $content ) ) echo $content;
					else {
				?>
			
				<h1 class="entry-title"><?php _e('Start a new discussion', 'twentyten'); ?></h1>
				
				<form action="" id="newcontentform" method="post">
					<input type="hidden" name="lang" value="<?php

	if (ORIGINAL_BLOG_ID == 2) echo 'fi';
	if (ORIGINAL_BLOG_ID == 3) echo 'en';
	if (ORIGINAL_BLOG_ID == 4) echo 'se';
			
			?>" />
<?php hri_form_is_logged_in(); ?>
				<div class="newd_form_item_container">
					<label for="newd_title"><?php _e('Subject', 'twentyten'); ?></label><span class="required"> *</span><span class="formerror" id="title_error"></span>
					<input type="text" name="newd_title" id="newd_title" />
				</div>
				<div class="newd_form_item_container">
					<textarea cols="40" rows="5" name="newd_text" id="newd_text"></textarea>
				</div>
				<?php if ( !is_user_logged_in() ) {

					$author = wp_get_current_commenter();

					?><br />
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

				<?php } ?><br />
<h2><?php _e('Data', 'twentyten'); ?></h2>
<div class="filter_container">
	<p><em><?php _e('Is this discussion related to a data published in HRI.','twentyten'); ?></em></p>
	<div class="input_with_helptext">
		<div class="newd_form_item_container">
			<input type="text" name="data" id="data" placeholder="<?php _e('Search by data name','twentyten') ?>" />
			<div id="data_filters" class="filters">
			<?php
			if ( isset( $_REQUEST['linked_id'] ) ) {
				hri_add_filter();
			}
			?>
			</div>
			<input type="hidden" name="datastring" id="datastring" />
		</div>
		<span class="helptext"><?php _e('Find by data title. You can add discussion to multiple data by doing multiple queryes.','twentyten'); ?></span>
	</div>
	<div class="newd_form_item_container">
	</div>
</div>

<?php /* <div class="filter_container">
	<input type="text" name="tags" id="tags" placeholder="<?php _e('Tags','twentyten') ?>" />
	<div id="tag_filters" class="filters">
	</div>
	<input type="hidden" name="tagstring" id="tagstring" />
</div>*/ ?>
<div class="newd_form_item_container">
	<?php _e('New discussions are moderated before publishing.','twentyten'); ?>
			
				
				
				<p class="form-submit" style="text-align: right;">
					<input type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Submit','twentyten'); ?>" />
				</p>
				</div>
				</form>

				<?php } ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_footer(); ?>

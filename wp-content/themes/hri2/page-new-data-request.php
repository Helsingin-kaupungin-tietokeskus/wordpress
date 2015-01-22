<?php
/**
 * Template name: new data request form
 */

hri_tinymce();
if ( isset($_GET['success']) ) {

	$content = '<h1>' . __('Uusi datatoive luotiin onnistuneesti','hri') . '</h1><p>' . __('Datatoive odottaa ylläpitäjien hyväksyntää ja julkaistaan pian.','hri') . '</p>';

}

if ( isset($_POST['newd_submit']) ) {
	
	$author_id = ( is_user_logged_in() ) ? $current_user->ID : 1;
	
	if ( $author_id == 1 && !is_user_logged_in() ) {
		
		$newd_username = mysql_real_escape_string( strip_tags($_POST['newd_username']) );
		$newd_useremail = mysql_real_escape_string( filter_var( $_POST['newd_useremail'], FILTER_VALIDATE_EMAIL ) );

		$content = null;

		if ( strlen( $newd_username ) < 3) $content .= '<div class="error">' . __('Nimen täytyy olla vähintään kolme merkkiä','hri') . '</div>';
		if ( $newd_useremail == false ) $content .= '<div class="error">' . __('Sähköpostiosoite vaaditaan','hri') . '</div>';
		
	}
	
	if ( !isset( $content ) ) {

		global $hri_kses_args;
	
		// Insert new post
		$insert = array(
			'post_title' => esc_html( strip_tags( $_POST['newd_title'] ) ),
			'post_name' => sanitize_title($_POST['newd_title'] , 'data-request'),
			'post_type' => 'data-request',
			'post_status' => 'pending',
			'post_content' => wp_kses($_POST['newd_text'], $hri_kses_args ),
			'post_author' => $author_id,
			'post_date_gmt' => date( 'Y-m-d H:i:s', time() )
		);
		
		$newID = wp_insert_post( $insert );
		
		// No exit() after header() to allow saving meta-data
		if ( $newID ) header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?success');
		
		else $content = '<div class="error">Something failed.</div>';
		
		if ( $author_id == 1 && !is_user_logged_in() ) {
		
			update_post_meta( $newID, 'user_name', $newd_username );
			update_post_meta( $newID, 'user_email', $newd_useremail );
		
		}

		if ( isset( $_POST['tagstring'] ) && !empty( $_POST['tagstring'] ) ) hri_set_tags_from_string( $newID );
		
	}
	
//	restore_current_blog();
	
}

get_header();

restore_current_blog();

?>

<script type="text/javascript">

var $=jQuery.noConflict();

$(document).ready(function(){
						   
	var url = "<?php echo ROOT_URL; ?>/wp-admin/admin-ajax.php";
			
	<?php

	hri_js_tags_autocomplete();

	hri_js_validate_newcontentform();

	?>
	
});

</script>

<div class="column col-narrow">
	<nav>
		<ul>
			<li>
				<div class="icon icon-back-arrow"></div><a href="<?php

				if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL, '/fi/datatoiveet/';
				if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL, '/en/data-requests/';

				?>"><?php _e( 'Kaikki datatoiveet', 'hri' ); ?></a></li>
		</ul>
	</nav>
</div>

<div class="column col-wide">
	<div class="sub-col">
	<?php
		if ( isset( $content ) ) echo $content;
		else {
	?>

		<div class="clearfix">
			<div class="icon-wish"></div><h1 class="with-icon"><?php _e('Lähetä uusi datatoive', 'hri'); ?></h1>
		</div>

		<form action="" id="newcontentform" method="post">

			<?php hri_form_is_logged_in(); ?>
			<div class="newd_form_item_container" style="display: none;">
				<label for="newd_title"><?php _e('Aihe', 'hri'); ?></label><span class="required">*</span><span class="formerror" id="title_error"></span>
				<input type="text" name="newd_title" id="newd_title" value="Datatoive" />
			</div>

			<div class="newd_form_item_container">
				<label for="newd_text"><?php _e('Toiveesi', 'hri'); ?></label>
				<textarea cols="40" rows="5" name="newd_text" id="newd_text"></textarea><br />
			</div>

	<?php if ( !is_user_logged_in() ) { ?>

			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<label for="newd_username"><?php _e('Nimi', 'hri'); ?></label><span class="required">*</span><span class="formerror" id="username_error"></span>
					<input class="text" type="text" name="newd_username" id="newd_username" value="<?php if ( isset( $author['comment_author'] ) ) echo $author['comment_author']; ?>" />
				</div>
				<span class="helptext"><br /><?php _e('Nimeäsi ei julkaista datatoiveen yhteydessä.','hri'); ?></span>
			</div>

			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<label for="newd_useremail"><?php _e('Sähköposti', 'hri'); ?></label><span class="required">*</span><span class="formerror" id="email_error"></span>
					<input class="text" type="text" name="newd_useremail" id="newd_useremail" value="<?php if ( isset( $author['comment_author_email'] ) ) echo $author['comment_author_email']; ?>" />
				</div>
				<span class="helptext"><br /><?php _e('Sähköpostiosoitettasi ei julkaista.','hri'); ?></span>
			</div>

	<?php } ?>

			<div class="newd_form_item_container">
			<?php _e('Datatoiveesi arvioidaan, sitä saatetaan muokata ja se saatetaan yhdistää muihin vastaaviin toiveisiin. Muokatut toiveet julkaistaan tällä sivustolla ja välitetään mahdollisuuksien mukaan datasta vastaavalle taholle.','hri'); ?>
			</div>

			<div class="newd_form_item_container">
				<p class="form-submit">
					<input type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Lähetä','hri') ?>" />
				</p>
			</div>
		</form>

				<?php } ?>

	</div>
</div>

<?php get_footer(); ?>
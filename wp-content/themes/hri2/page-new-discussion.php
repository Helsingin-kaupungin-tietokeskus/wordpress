<?php
/**
 * Template name: new discussion form
 */

global $wpdb;

hri_tinymce();

if ( isset($_GET['success']) ) {

	$content = '<h1>' . __('Uusi keskustelu aloitettu onnistuneesti','hri') . '</h1>';
	if ( !isset($_GET['published']) ) $content .= '<p>' . __('Keskustelu odottaa ylläpitäjien hyväksyntää ja julkaistaan pian','hri') . '</p>';

}

$error = false;

if ( isset($_POST['newd_submit']) ) {
	
	switch_to_blog(1);
	
	$author_id = ( is_user_logged_in() ) ? $current_user->ID : 1;

	if ( $author_id == 1 && !is_user_logged_in() ) {
		
		$newd_username = mysql_real_escape_string( strip_tags($_POST['newd_username']) );
		$newd_useremail = mysql_real_escape_string( filter_var( $_POST['newd_useremail'], FILTER_VALIDATE_EMAIL ) );

		$content = '';

		if ( strlen( $newd_username ) < 3) {

			$content .= '<div class="error">' . __('Nimen täytyy olla vähintään kolme merkkiä','hri') . '</div>';
			$error = true;

		}
		if ( $newd_useremail == false ) {

			$content .= '<div class="error">' . __('Sähköpostiosoite vaaditaan','hri') . '</div>';
			$error = true;

		}
		
	}

	if ( !$error ) {

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
		if ( $newID && $approved > 0 ) {

			header('Location: ' . NEW_DISCUSSION_URL . '?success&published');
			$content = '<script>window.location="' . NEW_DISCUSSION_URL . '?success&published"</script>';
			$content .= __( 'Klikkaa tästä:', 'hri' ) . ' <a href="' . NEW_DISCUSSION_URL . '?success&published">' . NEW_DISCUSSION_URL . '</a>';

		} elseif ( $newID ) {

			header('Location: ' . NEW_DISCUSSION_URL . '?success');
			$content = '<script>window.location="' . NEW_DISCUSSION_URL . '?success"</script>';
			$content .= __( 'Klikkaa tästä:', 'hri' ) . ' <a href="' . NEW_DISCUSSION_URL . '?success">' . NEW_DISCUSSION_URL . '</a>';

		} else $content = '<div class="error">' . __( 'Jokin epäonnistui', 'hri' ) . '</div>';
		
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

$(document).ready(function($){

	var url = "<?php echo ROOT_URL; ?>/wp-admin/admin-ajax.php";

	<?php

	hri_js_data_autocomplete();

	hri_js_tags_autocomplete();

	hri_js_validate_newcontentform();

	?>
	
});
// -->
</script>

<div class="column col-narrow">
	<nav>
		<ul>
			<li>
				<div class="icon icon-back-arrow"></div><a href="<?php

				echo ROOT_URL;
				if( ORIGINAL_BLOG_ID == 3 ) echo '/en/discussions/';
				else echo '/fi/keskustelut/';

				?>"><?php _e( 'Kaikki keskustelut', 'hri' ); ?></a></li>
		</ul>
	</nav>
</div>

<div class="column col-wide">
	<div class="sub-col">
	<?php
		if ( isset( $content ) ) {

			if( isset( $error ) && $error ) {

				$content .= '<p><a href="javascript:window.history.back();">' . __( 'Takaisin', 'hri' ) . '</a>.</p>';

			}

			echo $content;

		} else {
	?>

		<h1><?php _e('Aloita uusi keskustelu', 'hri'); ?></h1>

		<form action="" id="newcontentform" method="post">
			<input type="hidden" name="lang" value="<?php echo HRI_LANG; ?>" />

			<?php hri_form_is_logged_in(); ?>

			<label for="newd_title"><?php _e('Aihe', 'hri'); ?></label><span class="required">*</span>
			<input class="text" type="text" required="required" name="newd_title" id="newd_title" />

			<div class="newd_form_item_container">
				<textarea cols="40" rows="5" name="newd_text" id="newd_text"></textarea><br />
			</div>

			<?php if ( !is_user_logged_in() ) {

			$author = wp_get_current_commenter();

			?><br />
			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<label for="newd_username"><?php _e('Nimi', 'hri'); ?></label><span class="required">*</span>
					<input class="text" type="text" required="required" name="newd_username" id="newd_username" value="<?php if ( isset( $author['comment_author'] ) ) echo $author['comment_author']; ?>" />
				</div>
				<span class="helptext"><br /><?php _e('Nimesi julkaistaan keskustelunavauksen yhteydessä.','hri'); ?></span>
			</div>

			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<label for="newd_useremail"><?php _e('Sähköposti', 'hri'); ?></label><span class="required">*</span>
					<input class="text" type="email" required="required" name="newd_useremail" id="newd_useremail" value="<?php if ( isset( $author['comment_author_email'] ) ) echo $author['comment_author_email']; ?>" />
				</div>
				<span class="helptext"><br /><?php _e('Sähköpostiosoitettasi ei julkaista.','hri'); ?></span>
			</div>

	<?php } ?><br />

			<h2 class="blue"><?php _e('Data', 'hri'); ?></h2>

			<div class="filter_container">
				<p><em><?php _e('Liittyykö tämä keskustelu HRI-palvelussa julkaistuun dataan?','hri'); ?></em></p>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
					<input class="text" type="text" name="hri_data" id="data" placeholder="<?php _e('Hae datan nimellä','hri') ?>" />
					<div id="data_filters" class="filters clearfix">
					<?php
					if ( isset( $_REQUEST['linked_id'] ) ) {
						hri_add_filter();
					}
					?>
					</div>
					<input type="hidden" name="datastring" id="datastring" />
					</div>
					<span class="helptext"><?php _e('Etsi datan otsikolla. Voit liittää keskustelun useisiin data-aineistoihin tekemällä useita hakuja.','hri'); ?></span>
				</div>
			</div>


			<div class="newd_form_item_container">
			<?php _e('Keskustelunavaukset arvioidaan ja niitä saatetaan muokata ennen julkaisua.','hri'); ?>
			</div>

			<p><input style="margin-left:0" class="plus-submit" type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Lähetä','hri'); ?>" /></p>
		</form>

	<?php } ?>
	</div>
</div>

<?php get_footer(); ?>

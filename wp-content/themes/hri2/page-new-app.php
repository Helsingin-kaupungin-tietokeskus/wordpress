<?php
/**
 * Template name: new application form
 */

global $wpdb;

hri_tinymce('hrimceEditor');
hri_swfupload();

if ( isset($_GET['success']) ) {

	$content = '<h1>' . __('Uusi sovellus ilmoitettu onnistuneesti','hri') . '</h1><p>' . __('HRI-ylläpito käy ilmoituksesi läpi ja ottaa tarvittaessa yhteyttä antamaasi sähköpostiosoitteeseen. Tavoitat ylläpidon osoitteesta <a href="mailto:hri@hel.fi">hri@hel.fi</a>.','hri') . '</p>';

}

if ( isset($_POST['newd_submit']) ) {
	
	switch_to_blog(1);
	
	$author_id = ( is_user_logged_in() ) ? $current_user->ID : 1;
	
	if ( $author_id == 1 && !is_user_logged_in() ) {
		
		$newd_username = mysql_real_escape_string( strip_tags($_POST['newd_username']) );
		$newd_useremail = mysql_real_escape_string( filter_var( $_POST['newd_useremail'], FILTER_VALIDATE_EMAIL ) );

		$content = null;

		if ( strlen( $newd_username ) < 3) $content .= '<div class="error">' . __('Nimen täytyy olla vähintään kolme merkkiä','hri') . '</div>';
		if ( $newd_useremail == false ) $content .= '<div class="error">' . __('Sähköpostiosoite vaaditaan','hri') . '</div>';

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
		
		else $content = '<div class="error">' . __( 'Jokin epäonnistui', 'hri' ) . '</div>';

		
		if ( !$newID ) $content = '<div class="error">' . __( 'Jokin epäonnistui', 'hri' ) . '</div>';
		
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
$(document).ready(function($){

	var url = "<?php echo ROOT_URL; ?>/wp-admin/admin-ajax.php";

	<?php

	hri_js_data_autocomplete();

//	hri_js_tags_autocomplete();

	hri_js_validate_newcontentform();

	?>

	$('#uploads').find('a.delete').live('click',function(){
		$(this).parent().fadeOut(250,function(){ $(this).remove(); });
	});
	
});

</script>

<div class="column col-narrow">
	<nav>
		<ul>
			<li>
				<div class="icon icon-back-arrow"></div><a href="<?php

				if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL, '/fi/sovellukset/';
				if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL, '/en/applications/';

				?>"><?php _e( 'Kaikki sovellukset', 'hri' ); ?></a>
			</li>
			<?php if(ORIGINAL_BLOG_ID == 2): ?>
			<li>
				<div class="icon icon-back-arrow"></div><a href="<?php echo ROOT_URL . '/en/submit-new-application/'; ?>">This form in English</a>
			</li>
			<?php endif; ?>
			<?php if(ORIGINAL_BLOG_ID == 3): ?>
			<li>
				<div class="icon icon-back-arrow"></div><a href="<?php echo ROOT_URL . '/fi/ilmoita-uusi-sovellus/'; ?>">This form in Finnish</a>
			</li>
			<?php endif; ?>
		</ul>
	</nav>
</div>

<div class="column col-wide">
	<div class="sub-col">

		<?php
			if ( isset( $content ) ) echo $content;
			else {
		?>

		<h1 class="entry-title"><?php _e('Ilmoita uusi sovellus', 'hri'); ?></h1>

		<form action="" id="newcontentform" method="post" enctype="multipart/form-data">

<?php hri_form_is_logged_in(); ?>
			<div class="newd_form_item_container">
				<label for="newd_title"><?php _e('Sovelluksen nimi', 'hri'); ?></label><span class="required">*</span><span class="formerror" id="title_error"></span>
				<input class="text" type="text" name="newd_title" id="newd_title" />
			</div>
			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<label for="newd_text"><?php _e('Sovelluksen kuvaus', 'hri'); ?></label><span class="required">*</span><span class="formerror" id="text_error"></span>
					<textarea cols="40" rows="5" class="hrimceEditor" name="newd_text" id="newd_text"></textarea>
				</div>
				<span class="helptext"><br /><?php _e('Kuvaile ideaasi mahdollisimman informatiivisesti. Pidäthän kuvauksen lyhyenä.','hri'); ?></span>
			</div>
			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<span id="spanButtonPlaceholder"></span>
					<div id="divFileProgressContainer"></div>
					<div id="uploads"></div>
				</div>
				<span class="helptext"><?php _e('Hyväksytyt tiedosto tyypit: jpg, gif, png. Suurin sallittu koko 1 Mt. Kansikuva leikataan automaattisesti neliön muotoiseksi.','hri'); ?></span>
			</div>

			<div class="newd_form_item_container">
				<label for="new_app_URL"><?php _e('Sovelluksen www-osoite', 'hri'); ?></label><span class="formerror" id="app_url_error"></span>
				<input class="text" type="text" name="new_app_URL" id="new_app_URL" />
			</div>
			<h2><?php _e('Data', 'hri'); ?></h2>
			<em><?php _e('Liittyykö sovellus dataan?','hri'); ?></em>
			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<div class="filter_container">
						<input class="text" type="text" id="data" placeholder="<?php _e('Data','hri') ?>" />
						<div id="data_filters" class="filters">
						<?php
						if ( isset( $_REQUEST['linked_id'] ) ) {
							hri_add_filter();
						}
						?>
						</div>
						<input type="hidden" name="datastring" id="datastring" />
					</div>
				</div>
				<span class="helptext"><?php _e('Etsi datan otsikolla. Voit liittää sovelluksen useisiin data-aineistoihin tekemällä useita hakuja.','hri'); ?></span>
			</div>

		<h2><?php _e('Ylläpitäjä', 'hri'); ?></h2>
		<div class="newd_form_item_container">
			<label for="new_app_author"><?php _e('Sovelluksen ylläpitäjä', 'hri'); ?></label><span class="formerror" id="app_author_error"></span>
			<input class="text" type="text" name="new_app_author" id="new_app_author" />
		</div>
		<div class="newd_form_item_container">
			<label for="new_app_author_URL"><?php _e('Sovelluksen ylläpitäjän www-osoite', 'hri'); ?></label><span class="formerror" id="app_author_url_error"></span>
			<input class="text" type="text" name="new_app_author_URL" id="new_app_author_URL" />
		</div>
		<?php
		if ( !is_user_logged_in() ) {

			$author = wp_get_current_commenter();

		?>
				<h2><?php _e('Omat tietosi', 'hri'); ?></h2>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
						<label for="newd_username"><?php _e('Nimi', 'hri'); ?></label><span class="required"> *</span><span class="formerror" id="username_error"></span>
						<input class="text" type="text" name="newd_username" id="newd_username" value="<?php if ( isset( $author['comment_author'] ) ) echo $author['comment_author']; ?>" />
					</div>
				</div>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
						<label for="newd_useremail"><?php _e('Sähköposti', 'hri'); ?></label><span class="required"> *</span><span class="formerror" id="email_error"></span>
						<input class="text" type="text" name="newd_useremail" id="newd_useremail" value="<?php if ( isset( $author['comment_author_email'] ) ) echo $author['comment_author_email']; ?>" />
					</div>
					<span class="helptext"><br /><?php _e('Sähköpostiosoitettasi ei julkaista.','hri'); ?></span>
				</div>

		<?php } ?>
		<div class="input_with_helptext">
			<div class="newd_form_item_container">
				<label for="newd_text_2"><?php _e('Muuta / Terveisesi HRI-ylläpidolle', 'hri'); ?></label>
				<textarea cols="40" rows="5" name="newd_text_2" id="newd_text_2"></textarea>
			</div>
			<span class="helptext"><br /><?php _e('Muuta sovellukseen liittyvää tietoa tai erityisohjeita julkaisuun liittyen.','hri'); ?></span>
		</div><br />
		<div class="newd_form_item_container">
			<p class="form-submit" style="text-align: right;">
				<input type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Lähetä','hri');?>" />
			</p>
		</div>
		</form>

		<?php } ?>

	</div>
</div>

<?php get_footer(); ?>
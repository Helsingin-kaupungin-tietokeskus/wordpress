<?php
/**
 * Template name: new application idea form
 */

hri_tinymce('hrimceEditor');

if ( isset($_GET['success']) ) {

	$content = '<h1>' . __('Uusi sovellusidea luotiin onnistuneesti','hri') . '</h1><p>' . __('Sovellusidea odottaa ylläpitäjien hyväksyntää ja julkaistaan pian.','hri') . '</p>';

}

if ( isset($_POST['newd_submit']) ) {
	
//	switch_to_blog(1);
	
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
		
		else $content = '<div class="error">' . __( 'Jokin epäonnistui', 'hri' ) . '</div>';


		
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

	$ = jQuery;

	<?php

	hri_js_data_autocomplete();

//	hri_js_tags_autocomplete();

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

				if (ORIGINAL_BLOG_ID == 2) echo ROOT_URL, '/fi/sovellusideat/';
				if (ORIGINAL_BLOG_ID == 3) echo ROOT_URL, '/en/application-ideas/';

				?>"><?php _e( 'Kaikki sovellusideat', 'hri' ); ?></a></li>
		</ul>
	</nav>
</div>

<div class="column col-wide">
	<div class="sub-col">

		<?php
			if ( isset( $content ) ) echo $content;
			else {
		?>

		<h1><?php _e( 'Lähetä uusi sovellusidea', 'hri' ); ?></h1>

		<form action="" id="newcontentform" method="post">

			<?php hri_form_is_logged_in(); ?>
			<div class="newd_form_item_container">
				<label for="newd_title"><?php _e('Idean nimi', 'hri'); ?></label><span class="required"> *</span><span class="formerror" id="title_error"></span>
				<input class="text" type="text" name="newd_title" id="newd_title" />
			</div>
			<div class="input_with_helptext">
				<div class="newd_form_item_container">
					<label for="newd_text"><?php _e('Idean kuvaus', 'hri'); ?></label>
					<textarea cols="40" rows="5" class="hrimceEditor" name="newd_text" id="newd_text"></textarea>
				</div>
				<span class="helptext"><br /><?php _e('Kuvaile ideaasi mahdollisimman informatiivisesti.','hri'); ?></span>
			</div>

			<br />
			<h2 class="blue"><?php _e('Data', 'twentyten'); ?></h2>

			<div class="filter_container">
				<p><em><?php _e('Liittyykö tämä keskustelu HRI-palvelussa julkaistuun dataan?','hri'); ?></em></p>
				<div class="input_with_helptext">
					<div class="newd_form_item_container">
					<input class="text" type="text" name="data" id="data" placeholder="<?php _e('Hae datan nimellä','hri') ?>" />
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


<?php if ( !is_user_logged_in() ) { ?>
		<h2 class="blue"><?php _e('Omat tietosi', 'hri'); ?></h2>
		<div class="input_with_helptext">
			<div class="newd_form_item_container">
				<label for="newd_username"><?php _e('Nimi', 'hri'); ?></label><span class="required"> *</span><span class="formerror" id="username_error"></span>
				<input class="text" type="text" name="newd_username" id="newd_username" value="<?php if ( isset( $author['comment_author'] ) ) echo $author['comment_author']; ?>" />
			</div>
			<span class="helptext"><br /><?php _e('Nimesi julkaistaan keskustelunavauksen yhteydessä.','hri'); ?></span>
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
<label for="newd_text"><?php _e('Muuta / Terveisesi HRI-ylläpidolle', 'hri'); ?></label>
<textarea cols="40" rows="5" name="newd_text_2" id="newd_text_2"></textarea>
</div>
<span class="helptext"><br /><?php _e('Muuta ideaan liittyvää tietoa tai erityisohjeita julkaisuun liittyen.','hri'); ?></span>
</div><br />
<div class="newd_form_item_container">
<?php _e('Ilmoitustasi saatetaan muokata ja se saatetaan yhdistää muihin vastaaviin ideoihin. Valitut ideat julkaistaan tällä sivustolla.','hri'); ?>
</div>
		<div class="newd_form_item_container">
			<p class="form-submit" style="text-align: right;">
				<input type="submit" name="newd_submit" id="newd_submit" value="<?php _e('Lähetä', 'hri'); ?>" />
			</p>
		</div>
		</form>

		<?php } ?>

	</div>
</div>

<?php get_footer(); ?>
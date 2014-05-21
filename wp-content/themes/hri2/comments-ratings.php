
			<div id="comments">
<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'Tämä artikkeli on salasanasuojattu.', 'hri' ); ?></p>
			</div><!-- #comments -->
<?php
		return;
	endif;

	if ( have_comments() ) {

		?><h2 class="green row"><?php _e('Kommentit ja arvostelut', 'hri'); ?></h2><?php

		hri_link_to_last_comment();

		hri_comment_paging();

		?>
			<ol class="commentlist">
				<?php wp_list_comments( array( 'callback' => 'comments_ratings' ) ); ?>
			</ol>
<?php

		hri_comment_paging();

	} else {

		?><p><?php _e( 'Ei kommentteja', 'hri' ); ?>.</p><?php

	}

$custom_args = array(

	'id_form' => 'commentform-rate',
	'title_reply' => __( 'Kommentoi', 'hri' ),
	'fields' => array(

		'author' => '<p class="comment-form-author"><label for="author">' . __( 'Nimi', 'hri' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label> <input class="text" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" /></p>',

		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Sähköposti', 'hri' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label> <input class="text" id="email" name="email" type="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" /></p>'

	),

	'comment_field' => '<p class="comment-form-comment"><label for="comment">' . __( 'Kommentti', 'hri' ) . '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',

	'comment_notes_before' => __( 'Sähköpostiasi ei julkaista. Vaaditut kentät ovat merkitty *-merkillä.', 'hri' ),
	'comment_notes_after' => '' // subscribe_reloaded_show() // See notes below

);

comment_form( $custom_args );

?>
<script type="text/javascript">
// HiQ: Was forced to comment subscribe_reloaded_show() as comment_form does something strange to the HTML within, breaking the JS-string that holds it all.
//      What boggles my mind is that comment_form uses document.write to output the HTML from JS-string in the first place...
$('<?php echo str_replace("'", "\\'", subscribe_reloaded_show()) ?>').insertAfter('#respond .comment-form-comment');
</script>

<div id="ratercontainer">

	<div id="rater-toggle-bar" class="clearfix">
		<h4 class="left green" style="margin-bottom:10px"><?php _e( 'Lisää myös arvostelu', 'hri' ); ?></h4>
		<a class="right" id="ratertogglelink"><?php _e( 'Avaa', 'hri' ); ?></a>
	</div>

	<div id="rater" style="display:none">
		<div class="ratingseg clearfix">
			<div class="ratingname"><?php _e('Kuvaus','hri'); ?></div>
			<div id="quality"></div>
			<div class="ratingdesc"><?php _e('Ovatko datan kuvaus ja tiedot kattavia ja paikkansapitäviä?','hri'); ?></div>
		</div>
		<div class="ratingseg clearfix">
			<div class="ratingname"><?php _e('Hyödyllisyys','hri'); ?></div>
			<div id="topicality"></div>
			<div class="ratingdesc"><?php _e('Tarjoaako data hyödyllistä tietoa?','hri'); ?></div>
		</div>
		<div class="ratingseg clearfix">
			<div class="ratingname"><?php _e('Käytettävyys','hri'); ?></div>
			<div id="usability"></div>
			<div class="ratingdesc"><?php _e('Onko data hyvin käytettävässä muodossa?','hri'); ?></div>
		</div>
		<div class="ratingseg clearfix">
			<div class="ratingname gray"><?php _e('Yleisarvosana','hri'); ?></div>
			<div id="overall_rating">
				<div class="ratebg"><div class="rate"></div></div>
			</div>
			<div class="ratingdesc gray"><?php _e('Antamiesi arvosanojen keskiarvo','hri'); ?></div>
		</div>
	
	</div>
</div>
<?php
hri_report_comment_form();

?>

</div><!-- #comments -->

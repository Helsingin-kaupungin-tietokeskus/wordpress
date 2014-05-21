
			<div id="comments">
<?php

	if ( have_comments() ) {

		?><div class="row"></div><?php

		hri_comment_paging();

		?>
			<ol class="commentlist">
				<?php wp_list_comments( array( 'callback' => 'hri_comment' ) ); ?>
			</ol>
<?php

		hri_comment_paging();

	}

$custom_args = array(
	'title_reply' => __( 'Vastaa', 'hri' ),
	'fields' => array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Nimi', 'hri' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label> ' . '<input class="text" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Sähköposti', 'hri' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label> ' . '<input class="text" id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>',
	),
	'comment_notes_after' => '' // subscribe_reloaded_show() // See notes below
);
comment_form( $custom_args );

hri_report_comment_form();

?>
<script type="text/javascript">
// HiQ: Was forced to comment subscribe_reloaded_show() as comment_form does something strange to the HTML within, breaking the JS-string that holds it all.
//      What boggles my mind is that comment_form uses document.write to output the HTML from JS-string in the first place...
$('<?php echo str_replace("'", "\\'", subscribe_reloaded_show()) ?>').insertAfter('#respond .comment-form-comment');
</script>

</div><!-- #comments -->

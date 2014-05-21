<?php
/**
 * The template for displaying Comments [with ratings].
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

?>

			<div id="comments">
<?php if ( post_password_required() ) : ?>
				<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'twentyten' ); ?></p>
			</div><!-- #comments -->
<?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

<?php if ( have_comments() ) : ?>
<!--			<h3 id="comments-title"><?php _e('Comments and ratings', 'twentyten'); ?></h3>-->

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'twentyten' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
			</div> <!-- .navigation -->
<?php endif; // check for comment navigation ?>

			<ol class="commentlist">
				<?php wp_list_comments( array( 'callback' => 'comments_ratings' ) ); ?>
			</ol>

<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
			<div class="navigation">
				<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'twentyten' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'twentyten' ) ); ?></div>
			</div><!-- .navigation -->
<?php endif; // check for comment navigation ?>

<?php else : // or, if we don't have comments:

	/* If there are no comments and comments are closed,
	 * let's leave a little note, shall we?
	 */
	if ( ! comments_open() ) :
/*?>
	<p class="nocomments"><?php _e( 'Comments are closed.', 'twentyten' ); ?></p>
<?php */ endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>

<?php

if( !isset($aria_req) ) $aria_req = null;
if( !isset($commenter['comment_comment']) ) $commenter['comment_comment'] = null;

$custom_args = array(
	'title_reply' => __( 'Comment', 'twentyten' ),
	'label_submit' => __( 'Post', 'twentyten' ),
	'comment_field' => '<p class="comment-form-comment">' . '<label for="comment">' . __( 'Comment', 'twentyten' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .               '<textarea id="comment" name="comment" cols="45" rows="8" '.$aria_req.'>'.esc_attr( $commenter['comment_comment'] ).'</textarea></p>',
	'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.', 'twentyten' ) . ( $req ? sprintf( ' ' . __('Required fields are marked %s', 'twentyten'), '<span class="required">*</span>' ) : '' ) . '</p>',
	'fields' => array(
		'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'twentyten' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) .               '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'twentyten' ) . '</label> ' . ( $req ? '<span class="required">*</span>' : '' ) . '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>'
	),
	'comment_notes_after' => subscribe_reloaded_show()
);

comment_form( $custom_args );
echo '<div id="ratertoggle">
	<div id="ratertoggle_header">
		<h3><a id="ratertoggle_header_link" href="#">'.__( 'Rate', 'twentyten' ).'</a></h3>
		<a id="ratertoggler_open" href="#">' . __('Open','twentyten') . '<div class="sortmark"></div></a>
	</div>
	<div class="clear"></div>
	<div id="ratercontainer_dummy"></div>
	<a style="display: none;" id="ratertoggler_close" href="#">' . __('Close (no ratings)','twentyten') . '<div class="sortmark sortreverse"></div></a>
	<div class="clear"></div>
</div>';

echo '<div id="ratercontainer">
<div id="rater">

<div class="ratingseg">
	<div class="ratingname">' . __('Description','twentyten') . '</div>
	<div id="quality"></div>
	<div class="ratingdesc">' . __('Are data description and information comprehensive and accurate?','twentyten') . '</div>
</div>
<div class="ratingseg">
	<div class="ratingname">' . __('Relevance','twentyten') . '</div>
	<div id="topicality"></div>
	<div class="ratingdesc">' . __('Does the data provide useful information?','twentyten') . '</div>
</div>
<div class="ratingseg">
	<div class="ratingname">' . __('Usability','twentyten') . '</div>
	<div id="usability"></div>
	<div class="ratingdesc">' . __('Is the data in practical format and structure?','twentyten') . '</div>
</div>
<div class="ratingseg">
	<div class="ratingname gray">' . __('Overall rating','twentyten') . '</div>
	<div id="overall_rating">
		<div class="rate"><div class="rate2"></div></div>

	</div>
	<div class="ratingdesc gray">' . __('Overall based on your ratings','twentyten') . '</div>
</div>

</div>
</div>';

hri_report_comment_form();

?>


</div><!-- #comments -->

<?php
/**
 * Template name: Report comment
 */

if ( !isset($_POST['comment_ID']) && !isset( $_GET['success'] ) ) {

	header('Location: ' . home_url() );
	exit();

} elseif( $_POST['comment_ID'] ) {

	$mailed = false;

//	$comment_ID = (int) substr( $_POST['comment_ID'], strpos( $_POST['comment_ID'], '-' )+1 );
	list( , $comment_ID, $target_blog_id ) = explode( '-', $_POST['comment_ID'] );

	$comment_ID = absint( $comment_ID );
	$target_blog_id = absint( $target_blog_id );

	global $wpdb;
	$users = $wpdb->get_results( "SELECT * FROM {$wpdb->users}, {$wpdb->usermeta} WHERE ID = user_id AND meta_key = 'hri_notifications' AND meta_value = 1" );

	if( !$users ) {

		$user = array(
			'user_email' => get_option('admin_email')
		);

		$users[] = (object) $user;

	}

	if( $users && $target_blog_id ) {

		$bcc = array();

		foreach( $users as $user ) $bcc[] = $user->user_email;

		if( !empty($bcc) ) {

			switch_to_blog( $target_blog_id );
			$comment = get_comment( $comment_ID );

			$url = admin_url('comment.php?action=editcomment&c=') . $comment->comment_ID;

			$email = '';
			if( isset( $_POST['report-email'] ) ) $email = stripslashes( strip_tags( $_POST['report-email'] ));
			if ( $email == '' ) $email = 'Nimetön';

			$message = "$email on ilmoittanut tämän kommentin asiattomaksi:\n$url";

			$reporttext = '';
			if( isset( $_POST['reporttext'] ) ) $reporttext = stripslashes( strip_tags( $_POST['reporttext'] ));
			if ( $reporttext == '' ) $reporttext = '-';

			$message .= "\n\nIlmoituksen syy: $reporttext";

			$message .= "\n\nKommentin sisältö:\n" . $comment->comment_content;

//			$from = get_option('admin_email');
			$from = 'notifications@hri.fi';
			$headers = "From: HRI <$from>\r\n";

			$headers = "Bcc: " . implode( ', ', $bcc ) . "\r\n";

			$mailed = wp_mail( '', 'Ilmiannettu kommentti', $message, $headers );

			if( $mailed ) {

				if( ORIGINAL_BLOG_ID == 2) $target = ROOT_URL . '/fi/ilmoita-kommentti/?success';
				if( ORIGINAL_BLOG_ID == 3) $target = ROOT_URL . '/en/report-comment/?success';

				header('Location: ' . $target );
				exit();

			}
 
			restore_current_blog();
			
		}
	}

	$failed = true;
}

get_header(); ?>

<div class="column col-wide">
	<h1><?php the_title(); ?></h1>

	<p><?php

		if( isset( $failed ) ) _e('Jokin epäonnistui.','hri');
		if( isset( $_GET['success'] ) ) _e('Ilmoitus lähetetty ylläpitäjille.','hri');

	?></p>
</div><?php

get_sidebar();

get_footer();

?>
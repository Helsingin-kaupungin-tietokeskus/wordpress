<?php
/**
 * Template name: Subscribe to comments
 */

if ( !isset($_POST['hri_subscribe_submit']) ) {

	header('Location: ' . home_url() );
	exit();

} else {

	$email = filter_var( $_POST['hri_subscribe_email'], FILTER_VALIDATE_EMAIL );
	$option = (int) $_POST['hri_subscribe_option'];
	$post_id = (int) $_POST['hri_subscribe_post'];
	$blog_id = (int) $_POST['hri_subscribe_blog'];

	$fail = false;
	$messages = array();

	if( !$email ) {
		$messages[] = __('Virheellinen sähköpostiosoite', 'hri');
		$fail = true;
	}

	if( $option < 1 || $option > 3 ) {
		$messages[] = __( 'Virheellinen valinta', 'hri' );
		$fail = true;
	}

	if( $blog_id < 1 || $blog_id > 3 || !$post_id ) {
		$messages[] = __( 'Virhe', 'hri' );
		$fail = true;
	}

	if( !$fail ) {

		$email = mysql_real_escape_string( $email );

		global $wpdb;
		$prefixes = array(
			1 => 'wp_',
			2 => 'wp_2_',
			3 => 'wp_3_',
			4 => 'wp_4_'
		);

		switch( $option ) {
			case 1:

				$dt = date_i18n('Y-m-d H:i:s');

				$metavalue = "$dt|Y";

				$response = $wpdb->query( "INSERT INTO {$prefixes[$blog_id]}postmeta (post_id, meta_key, meta_value) VALUES ( $post_id, '_stcr@_$email', '$metavalue' )" );

				if( $response > 0 ) $messages[] = __('Kommentit tilattu onnistuneesti', 'hri');

			break;

			case 2:

				switch_to_blog( $blog_id );

				$wpdb->query( "DELETE FROM {$prefixes[$blog_id]}postmeta WHERE meta_key = '_stcr@_$email' AND post_id = $post_id" );
				$messages[] = __('Tilaus peruttu', 'hri');

			break;

			case 3:

				foreach( $prefixes as $p ) {

					$wpdb->query( "DELETE FROM {$p}postmeta WHERE meta_key = '_stcr@_$email'" );
				}
				$messages[] = __('Tilaus peruttu');

			break;

		}
	}
}

get_header();

?><div class="column col-wide"><?php

if ( have_posts() ) while ( have_posts() ) {

	the_post();

?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<h1><?php the_title(); ?></h1>
		<div class="content"><?php if( $messages ) foreach( $messages as $m ) echo "<p>$m</p>"; ?></div>
	</div>
<?php

}

?></div><?php

get_sidebar();
get_footer();

?>
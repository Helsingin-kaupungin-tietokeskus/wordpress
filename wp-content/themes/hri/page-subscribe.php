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
		$messages[] = __('Invalid email address', 'twentyten');
		$fail = true;
	}

	if( $option < 1 || $option > 3 ) {
		$messages[] = __( 'Incorrect option', 'twentyten' );
		$fail = true;
	}

	if( $blog_id < 1 || $blog_id > 3 || !$post_id ) {
		$messages[] = __( 'Error', 'twentyten' );
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

				if( $response > 0 ) $messages[] = __('Subscribed to comments succesfully', 'twentyten');

			break;

			case 2:

				switch_to_blog( $blog_id );

				echo "DELETE FROM {$prefixes[$blog_id]}postmeta WHERE meta_key = '_stcr@_$email' AND post_id = $post_id";
				exit;

				//$wpdb->query( "DELETE FROM {$prefixes[$blog_id]}postmeta WHERE meta_key = '_stcr@_$email' AND post_id = $post_id" );
				$messages[] = _('Unsubscribed succesfully');

			break;

			case 3:

				foreach( $prefixes as $p ) {

					$wpdb->query( "DELETE FROM {$p}postmeta WHERE meta_key = '_stcr@_$email'" );
				}
				$messages[] = _('Unsubscribed succesfully');

			break;

		}
	}
}

get_header(); ?>

		<div id="container">
			<div id="content" role="main">

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-content">

						<?php
							if( $messages ) foreach( $messages as $m ) echo "<p>$m</p>";
						?>

					</div><!-- .entry-content -->
					
				</div><!-- #post-## -->

<?php endwhile; ?>

			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

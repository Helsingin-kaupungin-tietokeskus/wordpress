<?php
/**
 * @package WordPress
 * @subpackage HRI
 * @since HRI 0.1
 */

global $query_string;
parse_str( $query_string, $args );

$loop_part = null;

if ( isset( $args['postname'] ) && isset( $args['posttype'] ) ) {
		
	switch_to_blog(1);
	
	query_posts( 'post_type=' . $args['posttype'] . '&name=' . $args['postname'] );
	
	if ( have_posts() ) {
		
		$template_file = ABSPATH . 'wp-content/themes/hri/single-' . $args['posttype'] . '.php';
		if ( file_exists( $template_file ) ) include( $template_file );
		else include( ABSPATH . 'wp-content/themes/hri/single.php' );
		
	} else {
		
		global $wp_query;
		$wp_query->set_404();
		
		include( ABSPATH . 'wp-content/themes/hri/404.php' );
		
	}
	
	exit;
	
} elseif ( isset( $args['posttype'] )) {

	switch_to_blog(1);
	
	if ( $args['posttype'] == 'application' ) {

		$query_args = array(
			'post_type' => 'application',
			'order' => 'DESC',
			'orderby' => 'date',
			'posts_per_page' => -1
		);

		if ( isset($args['appcat']) ) {

			$app_cats = explode( '/', $args['appcat'] );

			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'hri_appcats',
					'field' => 'slug',
					'terms' => end( $app_cats )
				)
			);

		};

		query_posts( $query_args );
		$loop_part = 'apps';


	} else {
		query_posts( 'post_type=' . $args['posttype'] );
		$loop_part = '';
	}

}

get_header();

if ( $args['posttype'] == 'application' ) {
?>
<script type="text/javascript">
// <!--
var $ = jQuery.noConflict();
$(document).ready(function(){$('.app-menu-item').addClass('current-menu-item');});
// -->
</script>
<?php } ?>

		<div id="container"<?php if ( isset($left_column) ) echo ' class="middle-column"'; ?>>
			<div id="content" role="main">

			<?php get_template_part( 'loop', $loop_part ); 	?>
			
			</div><!-- #content -->
		</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>

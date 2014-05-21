<?php
/**
 * The Header for our theme.
 *
 * @package WordPress
 * @subpackage HRI
 * @since HRI 0.1
 */

global $switched;

$current_site_id = get_current_site()->id;
if ( $current_site_id != ORIGINAL_BLOG_ID ) restore_current_blog();

global $blogname;

 ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/images/favicon.png" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	$title = '';
	global $page, $paged;

	$title .= wp_title( '|', false, 'right' );

	// Add the blog name.
	$title .= get_bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " | $site_description";
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 ) {
		$title .= ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );
	}

	echo $title;
	
	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php
	/* We add some JavaScript to pages with the comment form
	 * to support sites with threaded comments (when in use).
	 */
	if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui', get_bloginfo('template_url') . '/jquery-ui/jquery-ui-1.8.9.custom.min.js', array('jquery') );

	wp_head();
?>

<!--[if lte IE 8]><link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('template_url')?>/style_ie8.css" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('template_url')?>/style_ie7.css" /><![endif]-->
<script type='text/javascript' src='<?php bloginfo('template_url')?>/js/jquery.cookie.js'></script>
<script type='text/javascript' src='<?php bloginfo('template_url')?>/js/jquery.raty.min.js'></script>
<script type='text/javascript' src='<?php bloginfo('template_url')?>/js/jquery.scrollTo-min.js'></script>
<script type='text/javascript' src='<?php bloginfo('template_url')?>/js/scrollSettings.js'></script>
<script type="text/javascript">
/*<![CDATA[*/
jQuery(document).ready(function() {
	if ($('#menu-item-874').hasClass('current-menu-item')) {
		$('#menu-item-107').removeClass('current-menu-item current-category-ancestor current-category-parent');
	}
});
/*]]>*/
</script>
<?php
$user_agent = $_SERVER['HTTP_USER_AGENT'];
if (strstr($user_agent, 'OS X')) {
?>
<style>
#primary ul h3.widget-title,
#hri_home_widgets h3.widget-title,
#hri_home_widgets .twtr-widget h3 {
	padding-top:3px;
	height: 32px !important;
}
</style>
<?php
}
?>
<meta property="og:title" content="<?php echo $title; ?>" />

<link rel="image_src" type="image/gif" href="http://www.hri.fi/wp-content/themes/hri/images/icon-facebook.gif" />
<?php
$og_locale = 'fi_FI';
$meta_description = 'www.hri.fi -verkkopalvelu tarjoaa helpon tavan löytää, saada ja hyödyntää Helsingin seudun julkisia tietovarantoja avoimena datana. Tarjolla oleva tieto on pääosin tilastodataa, mutta palvelun kautta löytää myös muuta seudun avointa dataa.';
if (ORIGINAL_BLOG_ID == 1 || ORIGINAL_BLOG_ID == 3) {
	$og_locale = 'en_US';
	$meta_description = 'The www.hri.fi online service offers you an easy way to find, obtain and utilise public data pools from the Helsinki Region as open data. The available data is mainly statistical data, but other open data from the region is also available.

	We are working on improving the functionalities of the online service and promoting the availability of open data by listening to our users. This service provides a channel to submit your requests and feedback and to participate in related discussions.

	This online service is part of the Helsinki Region Infoshare project in the cities of the Helsinki Region.';
}

if (ORIGINAL_BLOG_ID == 4) {
	$og_locale = 'sv_SE';
	$meta_description = 'Projektet Helsinki Region Infoshare (HRI) tillhandahåller information om Helsingforsregionen så att alla kan komma åt den snabbt och behändigt. Informationen öppnas för medborgare, företag, universitet, högskolor, forskningsanstalter samt kommunförvaltning och statsförvaltning. Informationen finns gratis tillgänglig och får användas fritt.';
}

$meta_image = home_url( '/' ) . 'wp-content/themes/hri/images/logo_fb.png';
?>
<meta property="og:locale" content="<?php echo $og_locale; ?>" />
<meta property="og:url" content="http://www.hri.fi<?php echo $_SERVER['REQUEST_URI']; ?>" />
<?php

global $query_string;
parse_str( $query_string, $args );

if (   isset( $args['posttype'] ) 
	&& $args['posttype'] == 'data' ) {

	global $wp_query;

	switch_to_blog(1);
	$this_post = get_post( $wp_query->get_queried_object_id() );
	
	$meta_description = n_words( notes( false, false, ORIGINAL_BLOG_ID, $this_post->ID ), 30, false );

	restore_current_blog();
} elseif (   isset( $args['posttype'] ) 
		&& $args['posttype'] == 'application' ) {
	global $wp_query;

	switch_to_blog(1);
	$this_post = get_post( $wp_query->get_queried_object_id() );

	if (strlen($this_post->post_content)>0) {
		$meta_description = n_words( $this_post->post_content, 30, false );
	}
	
	$f = (int) get_post_meta( $this_post->ID, '_thumbnail_id', true );
	
	if ($f != 0) {
		$image = wp_get_attachment_image_src( $f, 'hri_square' );
		if (   isset($image)
			&& isset($image[0])) {
			$meta_image = $image[0];
		}
	}

	restore_current_blog();
} else {
	global $wp_query;

	$this_post = get_post( $wp_query->get_queried_object_id() );
	if (   isset($this_post)
		&& isset($this_post->post_content)) {
		if (strlen($this_post->post_content)>0) {
			$meta_description = n_words( $this_post->post_content, 30, false );
			$f = (int) get_post_meta( $this_post->ID, '_thumbnail_id', true );

			if ($f != 0) {
				$image = wp_get_attachment_image_src( $f, 'hri_square' );
				if (   isset($image)
					&& isset($image[0])) {
					$meta_image = $image[0];
				}
			}
		} else {
			switch_to_blog(1);
			$this_post = get_post( $wp_query->get_queried_object_id() );

			if (strlen($this_post->post_content)>0) {
				$meta_description = n_words( $this_post->post_content, 30, false );
			}
			
			$f = (int) get_post_meta( $this_post->ID, '_thumbnail_id', true );

			if ($f != 0) {
				$image = wp_get_attachment_image_src( $f, 'hri_square' );
				if (   isset($image)
					&& isset($image[0])) {
					$meta_image = $image[0];
				}
			}

			restore_current_blog();
		}
	}

}

?>
	<meta property="og:description" content="<?php echo strip_tags($meta_description); ?>" />
	<meta property="og:image" content="<?php echo $meta_image; ?>" />
<?php

?>

</head>

<body <?php body_class($blogname); ?>>

<div id="headercontainer">
	<div id="header">
		<div class="floatr kielilinkit">
			<a class="kielikauttaviiva" href="<?php echo ROOT_URL; ?>/fi/">Suomeksi</a>
			<a class="kielikauttaviiva" href="<?php echo ROOT_URL; ?>/en/">In English</a>
			<a href="<?php echo ROOT_URL; ?>/se/">På svenska</a>
		</div>
		<a class="logolink" href="<?php echo home_url( '/' ); ?>" rel="home">
			<img src="<?php echo home_url( '/' ); ?>wp-content/themes/hri/images/logo.png" alt="Helsinki Region Infoshare" />
		</a>
<?php
get_search_form();
wp_nav_menu( array( 'container_class' => 'hrinav', 'theme_location' => 'primary', 'fallback_cb' => false ) );
?>

	</div>
</div>

<div id="maincontainer">
<div id="wrapper" class="hfeed">

	<div id="main">

<?php

if ( $current_site_id != ORIGINAL_BLOG_ID ) switch_to_blog( $current_site_id );

?>

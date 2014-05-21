<?php

global $blogname, $switched;

?><!DOCTYPE html>
<!--[if lte IE 8]><html class="no-js old-ie" <?php language_attributes(); ?>><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<link rel="shortcut icon" href="<?php bloginfo('template_url'); ?>/images/favicon.png" />
<title><?php

	$title = '';
	global $page, $paged;

	$title .= wp_title( '|', false, 'right' );
	$title .= get_bloginfo( 'name' );

	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " | $site_description";
	}

	if ( $paged >= 2 || $page >= 2 ) {
		$title .= ' | ' . sprintf( __( 'Sivu %s', 'hri' ), max( $paged, $page ) );
	}

	echo $title;
	
	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php

	if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );

	wp_enqueue_script( 'jquery' );

	// brbr: jquery.ui.autocomplete has been modified (in jquery-ui-1.8.21.custom.min.js) to accept SPACE, LEFT, RIGHT keys
	//wp_enqueue_script( 'jquery-ui', get_bloginfo('template_url') . '/js/jquery-ui/jquery-ui-1.8.21.custom.min.js' );
	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );

	wp_head();

?>
<script type="text/javascript" src="<?php bloginfo('template_url'); ?>/js/modernizr.custom.73902.js"></script>
<?php /*
<script type="text/javascript" src="http://use.typekit.com/mgx0lij.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
 */ ?>
<script type='text/javascript' src='<?php bloginfo('template_url'); ?>/js/jquery.cookie.js'></script>
<script type='text/javascript' src='<?php bloginfo('template_url'); ?>/js/jquery.raty.min.js'></script>
<script type='text/javascript' src='<?php bloginfo('template_url'); ?>/js/jquery.scrollTo-min.js'></script>
<script type='text/javascript' src='<?php bloginfo('template_url'); ?>/js/hri.js'></script>
<!--[if lte IE 9]><script src="<?php bloginfo( 'template_url' ); ?>/js/hri-ie.js"></script><![endif]-->
<!--[if lte IE 9]><link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/ie9.css" /><![endif]-->
<!--[if lte IE 8]><link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/ie8.css" /><![endif]-->
<!--[if lte IE 7]><link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'template_url' ); ?>/ie7.css" /><![endif]-->
<?php get_template_part( 'meta', 'og' ); ?>
</head>

<body <?php

	$body_classes = '';
	global $body_class;

	if( isset( $body_class ) ) $body_classes .= $body_class . ' ';

	$body_classes .= $blogname;

	body_class( $body_classes );

?>>
<div id="fb-root"></div>
<script>(function(d, s, id) {
var js, fjs = d.getElementsByTagName(s)[0];
if (d.getElementById(id)) return;
js = d.createElement(s); js.id = id;
js.src = "//connect.facebook.net/<?php global $locale; echo $locale; ?>/all.js#xfbml=1&appId=142633425787141";
fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<a id="top"></a>
<div class="bg">
	<header class="wrapper"><?php

		if( strpos( $_SERVER['SERVER_NAME'], 'staging' ) !== false ){
			?><div style="position:absolute;top:55px;margin-left:240px;font-size:40px;font-weight:bold;color:#74c16d;text-transform:uppercase;">Staging</div><?php
		}

		?>
		<a href="<?php echo home_url(); ?>" rel="home"><img id="logo" src="<?php bloginfo('template_url'); ?>/img/logo.png" alt="Helsinki Region Infoshare"<?php

			if( !is_front_page() ) { ?> title="<?php _e( 'Etusivulle', 'hri' ); ?>" <?php }

		?>/></a>

		<nav id="languages" class="right">
			<a href="<?php echo ROOT_URL; ?>/fi/">Suomeksi</a> /
			<a href="<?php echo ROOT_URL; ?>/en/">Key points in English</a> /
			<a href="<?php echo ROOT_URL; ?>/se/">Kort på svenska</a>
			<?php if ($blogname != 'se') {
				get_search_form();
			} ?>
		</nav>
	</header>

<?php if ($blogname != 'se'): ?>
	<div id="main-nav-wrap">
		<div id="main-nav-c">
			<nav id="main-nav" class="wrapper">
				<?php

				$hri_menu = wp_nav_menu( array( 'container' => false, 'theme_location' => 'primary', 'fallback_cb' => false, 'echo' => false ) );

				echo str_replace( '</ul>', '<li class="finish"></li></ul>', $hri_menu );

				?>
			</nav>
		</div>
	</div>

	<?php if( is_front_page() ) { ?>
		<div id="graybar" class="wrapper gray-wrapper">
			<span class="left icon-data-count"><?php _e( 'Yhteensä', 'hri' ); ?> <a href="<?php echo DATA_SEARCH_URL; ?>"><?php
	
				global $wpdb;
	
				switch_to_blog(1);
				$data_count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'data' AND post_status = 'publish' AND $wpdb->posts.post_title <> ''" );
				echo $data_count;
	
				?> <?php _e( 'data-aineistoa', 'hri'); ?></a>.
			</span>
			<span class="right"><?php
			
				$latest_topic = $wpdb->get_results( "(SELECT SQL_CALC_FOUND_ROWS DISTINCT p.*, comment_date AS sort_date, '1' AS source FROM $wpdb->posts p LEFT JOIN ( SELECT comment_post_ID, MAX( comment_date ) AS comment_date FROM $wpdb->comments WHERE comment_approved = 1 GROUP BY comment_post_ID ) AS c ON c.comment_post_ID = p.ID WHERE p.post_status = 'publish' AND ( (p.post_type = 'discussion' OR p.post_type = 'data') AND p.comment_count > 0 ) ORDER BY c.comment_date DESC, p.post_title ASC) UNION (SELECT DISTINCT p.*, p.post_date AS sort_date, '2' AS source FROM $wpdb->posts p WHERE p.post_type = 'discussion' AND p.post_status = 'publish' AND p.comment_count = 0) ORDER BY sort_date DESC LIMIT 0, 1" );
		
				$post = $latest_topic[0];
		
				if( $latest_topic[0]->post_type == 'data' ) {
		
					$link = '<a href="' . hri_link( get_permalink( $latest_topic[0]->ID ), HRI_LANG, 'data' ) . '">' . data_title( HRI_LANG, false ) . '</a>';
		
				} else {
		
					$link = '<a href="' . hri_link( get_permalink( $latest_topic[0]->ID ), HRI_LANG, 'discussion' ) . '">' . $latest_topic[0]->post_title . '</a>';
		
				}
			
				if ($blogname != 'en') {
					printf( __('Tuorein keskustelu on aiheesta %s.', 'hri'), $link );
				}
				wp_reset_postdata();
		
				restore_current_blog();
				
			?></span>
		</div>
	<?php } ?>
<?php endif; // ^if $blogname != 'se' ?>
	<div id="main" class="wrapper" role="main">
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
<link rel="stylesheet" type="text/css" media="all" href='<?php bloginfo('template_url'); ?>/hri-common.css' />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php

	if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );

	wp_enqueue_script( 'jquery' );

	// brbr: jquery.ui.autocomplete has been modified (in jquery-ui-1.8.21.custom.min.js) to accept SPACE, LEFT, RIGHT keys
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
<script type="text/javascript">var wordpress_url = "<?php echo ROOT_URL; ?>";</script>
<script type="text/javascript">var HRI_LANG = "<?php echo HRI_LANG; ?>";</script>
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

	// $body_classes .= $blogname; // Does not work with WordPress 4.2.2 any more
 	$body_classes .= substr(get_bloginfo( 'language' ), 0, 2);

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
	<header id="hri-header" class="hri-wrapper"><?php

		if( strpos( $_SERVER['SERVER_NAME'], 'staging' ) !== false ){
			?><div style="position:absolute;top:55px;margin-left:240px;font-size:40px;font-weight:bold;color:#74c16d;text-transform:uppercase;">Staging</div><?php
		}

		?>
		<a href="<?php echo home_url(); ?>" rel="home"><img id="hri-logo" src="<?php bloginfo('template_url'); ?>/img/logo.png" alt="Helsinki Region Infoshare"<?php

			if( !is_front_page() ) { ?> title="<?php _e( 'Etusivulle', 'hri' ); ?>" <?php }

		?>/></a>

		<div class="hri-header-content right">
			<nav id="hri-languages" class="right">
				<a href="<?php echo ROOT_URL; ?>/fi/">Suomeksi</a> |
				<a href="<?php echo ROOT_URL; ?>/en/">Key points in English</a> |
				<a href="<?php echo ROOT_URL; ?>/se/">Kort på svenska</a>
			</nav>
	    	
	    	<div id="hri-info" class="">
      		</div>
		</div>

	</header>

<?php if ($blogname != 'se'): ?>
	<div id="main-nav-wrap">
		<div id="main-nav-c">
			<nav id="main-nav" class="hri-wrapper">
				<?php

				$hri_menu = wp_nav_menu( array( 'container' => false, 'theme_location' => 'primary', 'fallback_cb' => false, 'echo' => false ) );

				echo str_replace( '/data-haku/', '/dataset?q=&sort=metadata_created+desc', str_replace( '</ul>', '<li class="finish"></li></ul>', $hri_menu ));

				?>
			</nav>
		</div>
	</div>
<?php endif; // ^if $blogname != 'se' ?>
	<div id="main" class="wrapper" role="main">

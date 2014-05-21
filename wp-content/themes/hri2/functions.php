<?php
remove_filter('template_redirect', 'redirect_canonical');
get_template_part( 'functions', 'secondary' );

if( is_admin() ) {
	get_template_part( 'functions', 'admin' );
}

global $blog_id, $locale;

defined( 'ORIGINAL_BLOG_ID' ) or define( 'ORIGINAL_BLOG_ID', $blog_id );
defined( 'HRI_LANG' ) or define( 'HRI_LANG', substr( $locale, 0, 2 ) );

switch_to_blog(1);
define( 'ROOT_URL', home_url() );
restore_current_blog();

$pattern_url = str_replace( array('/', '.'), array('\/', '\.'), ROOT_URL );
$pattern_url = "/$pattern_url\\/(fi|en|se)\\//";

$hri_kses_args = array(
	'a' => array('href' => array(),'title' => array()),
	'br' => array(),
	'blockquote' => array(),
	'em' => array(),
	'li' => array(),
	'ol' => array(),
	'p' => array(),
	'span' => array('style'=>array('text-decoration')),
	'strong' => array(),
	'sub' => array(),
	'sup' => array(),
	'ul' => array()
);

switch( $blog_id ) {
	case 3:
		define( 'DATA_SEARCH_URL', ROOT_URL .		'/en/data-search/' );
		define( 'APP_SEARCH_URL', ROOT_URL .		'/en/applications/' );
		define( 'NEW_DISCUSSION_URL', ROOT_URL .	'/en/start-a-new-discussion/' );
		define( 'NEW_APP_IDEA_URL', ROOT_URL .		'/en/new-app-idea/' );
		define( 'NEW_APP_URL', ROOT_URL .			'/en/' );
		define( 'NEW_DATA_REQUEST_URL', ROOT_URL .	'/en/' );
		break;
	default:
		define( 'DATA_SEARCH_URL', ROOT_URL .		'/fi/data-haku/' );
		define( 'APP_SEARCH_URL', ROOT_URL .		'/fi/sovellukset/' );
		define( 'NEW_DISCUSSION_URL', ROOT_URL .	'/fi/aloita-uusi-keskustelu/' );
		define( 'NEW_APP_IDEA_URL', ROOT_URL .		'/fi/uusi-sovellusidea/' );
		define( 'NEW_APP_URL', ROOT_URL .			'/fi/ilmoita-uusi-sovellus/' );
		define( 'NEW_DATA_REQUEST_URL', ROOT_URL .	'/fi/uusi-datatoive/' );
}

add_action( 'after_setup_theme', 'hri_setup' );

function hri_setup() {

	add_theme_support( 'post-thumbnails' );
	add_image_size( 'tiny-square',	50, 50, true );
	add_image_size( 'small-app',	97, 60, true );
	add_image_size( 'med-square',	160, 160, true );
	add_image_size( 'square',		235, 235, true );
	add_image_size( 'app',			560, 300, true );		//carousel
	add_image_size( 'app2',			260, 146, true );		//application in gallery
	add_image_size( 'app3',			310, 400 );				//single-application

	add_theme_support( 'automatic-feed-links' );
	add_editor_style();

	load_theme_textdomain( 'hri', TEMPLATEPATH . '/languages' );

	register_nav_menus( array(
		'primary' => __( 'Päävalikko', 'hri' ),
	) );

	register_nav_menus( array(
		'footer' => __( 'Alavalikko', 'hri' ),
	) );

	register_sidebar(array(
		'name' => __( 'Etusivu', 'hri' ),
		'id' => 'front-page',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki', 'hri'),
		'id' => 'sidebar',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki Sivuille', 'hri'),
		'id' => 'sidebar-page',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki Datoille', 'hri'),
		'id' => 'sidebar-data',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Sivupalkki Artikkelilistauksille', 'hri'),
		'id' => 'sidebar-archive',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	add_filter( 'excerpt_length', function(){
		return 20;
	});

}

/* -------------------------------------------------------------------------------------------------------------------------------- Rewrite */

// http://codex.wordpress.org/Function_Reference/WP_Rewrite

add_filter( 'rewrite_rules_array', 'hri_add_rewrite_rules' );
add_filter( 'query_vars', 'hri_add_query_vars' );

function hri_add_rewrite_rules($rules) {

	$newrules = array();

	// Content types:
	$newrules['(data)/$'] = 'index.php?posttype=data';
	$newrules['(data)/(.+)$'] = 'index.php?posttype=data&postname=$matches[2]';

	$newrules['(applications)/(.+)$'] = 'index.php?posttype=application&postname=$matches[2]';
	$newrules['(sovellukset)/(.+)$'] = 'index.php?posttype=application&postname=$matches[2]';

	$newrules['(discussions)$'] = 'index.php?pagename=discussions';
	$newrules['(keskustelut)$'] = 'index.php?pagename=keskustelut';
	$newrules['(discussions)/(.+)$'] = 'index.php?posttype=discussion&postname=$matches[2]';
	$newrules['(keskustelut)/(.+)$'] = 'index.php?posttype=discussion&postname=$matches[2]';

	return $newrules + $rules;

}

function hri_add_query_vars($vars) {

	array_push($vars, 'posttype', 'postname', 'appcat');
	return $vars;

}

/* -------------------------------------------------------------------------------------------------------------------------------- Comment meta language things */

// comment language meta saving
add_action('comment_post','comment_save_data');

function comment_save_data($comment_id) {

	$langs = array('en','fi','se');
	if ( !in_array( $_POST['lang'], $langs) ) die('Error: Comment language meta fail');

	add_comment_meta($comment_id, 'hri_comment_lang', $_POST['lang'], true);

	if ( isset( $_POST['r1'] ) && $_POST['r1'] > 0 ) {

		for($i=1; $i<=3; ++$i){
			$r = (int) $_POST['r'.$i];
			if ($r < 1) $r = 1;
			if ($r > 5) $r = 5;
			add_comment_meta($comment_id, '_hri_rating'.$i, $r, true);
		}
	}
}

function add_hidden_lang() {

	global $blog_id, $post;

 	echo '<input type="hidden" name="lang" value="' . HRI_LANG . '" />
	<input type="hidden" name="redirect_to" value="' . hri_link( get_permalink( $post->ID ), HRI_LANG, 'data' ) . '" />
	<input type="hidden" id="hri-blog" value="' . $blog_id . '" />';

	return true;

}

add_filter('comment_form_top','add_hidden_lang');

if( isset($_POST) ) {

	if(
		isset( $_POST['r1'] ) && $_POST['r1'] > 0 &&
		isset( $_POST['r2'] ) && $_POST['r2'] > 0 &&
		isset( $_POST['r3'] ) && $_POST['r3'] > 0

	) add_action('pre_comment_on_post', function() {

		$some_random_value = uniqid();

		//allow empty ratings without comment text
		if( $_POST['comment'] == '' ) $_POST['comment'] = "<!-- rating {$some_random_value} -->";

	});

}

add_action('wp_ajax_hri_app', 'hri_ajax_app');
add_action('wp_ajax_nopriv_hri_app', 'hri_ajax_app');

function hri_ajax_app() {

	if( isset( $_POST['not'] ) ) $id = (int) $_POST['not'];

	switch_to_blog(1);

	$args = array(
		'post_type' => 'application',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'orderby' => 'rand'
	);

	if( isset($id) ) $args[ 'post__not_in' ] = array($id);

	$app = new WP_Query( $args );

	if( $app->have_posts() ) {

		global $post;

		$app->the_post();

		?><div style="display:none" id="post-<?php the_ID(); ?>" <?php post_class( 'post-highlight clear' ); ?>><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php

			hri_thumbnail();

			?></a>
				<div class="highlight-excerpt"><a href="<?php echo hri_link( get_permalink(), HRI_LANG, 'application' ); ?>"><?php the_title(); ?></a>
					<p><?php echo get_post_meta( $post->ID, 'short_text', true ); ?></p>
				</div>
				<div class="clear"></div>
			</div><?php

	}

	exit();

}

/* -------------------------------------------------------------------------------------------------------------------------------- subscribe to comments */

function hri_subscribe() {

	global $post;

?>
<div id="hri_subscribe_toggle">
	<a id="comment_subscribe_link"><?php _e( 'Sähköposti-ilmoitus kommenteista: tilaa tai peru tilaus', 'hri' ); ?></a>
	<div id="subscribe_content" style="display:none">
		<form action="<?php

			echo ROOT_URL, '/', HRI_LANG;
			if( ORIGINAL_BLOG_ID == 2 ) echo '/tilaa-kommentit/';
			if( ORIGINAL_BLOG_ID == 3 ) echo '/subscribe-to-comments/';

			?>" method="post">
			<a id="subscribe_toggle_close" class="bold right"><?php _e( 'Sulje', 'hri' ); ?></a>
		<div class="row">
			<label for="hri_subscribe_email"><?php _e('Sähköposti', 'hri'); ?></label>
			<span class="required">*</span>
			<input class="text" id="hri_subscribe_email" type="email" required="required" size="30" value="" name="hri_subscribe_email" />
		</div>
		<div class="row">
			<input type="hidden" value="<?php echo $post->ID; ?>" name="hri_subscribe_post" />
			<input type="hidden" value="<?php echo get_current_blog_id(); ?>" name="hri_subscribe_blog" />
			<input checked="checked" class="hri_subscribe_option radio" type="radio" name="hri_subscribe_option" id="hri_subscribe_option1" value="1"><label class="hri_subscribe_label" for="hri_subscribe_option1"><?php _e('Tilaa ilmoitukset uusista viesteistä tähän keskusteluun', 'hri'); ?></label>
			<input class="hri_subscribe_option radio" type="radio" name="hri_subscribe_option" id="hri_subscribe_option2" value="2"><label class="hri_subscribe_label" for="hri_subscribe_option2"><?php _e('Peru ilmoitukset tämän keskustelun viesteistä', 'hri'); ?></label>
			<input class="hri_subscribe_option radio hri_subscribe_option_top_margin" type="radio" name="hri_subscribe_option" id="hri_subscribe_option3" value="3"><label class="hri_subscribe_label" class="hri_subscribe_option_top_margin" for="hri_subscribe_option3"><?php _e('Peru ilmoitukset kaikista HRI-keskusteluista', 'hri'); ?></label>
			<br class="clear" />
			<input class="plus-submit" type="submit" name="hri_subscribe_submit" id="hri_subscribe_submit" value="<?php _e('Lähetä', 'hri'); ?>" />
		</div>
		</form>
	</div>
</div>
<script type="text/javascript">
// <!--
jQuery(function($) {

	$('#hri_subscribe_toggle').insertBefore( '#respond' );

	$('#comment_subscribe_link').prependTo($('#reply-title')).click(function(){
		$(this).fadeOut();
		$('#subscribe_content').slideDown();
	});

	$('#subscribe_toggle_close').click(function(){
		$('#subscribe_content').slideUp();
		$('#comment_subscribe_link').fadeIn();
	});
});
// -->
</script>
<?php

}

add_action('comment_form_after', 'hri_subscribe');

add_filter('excerpt_more', function( $more ){
	return '&hellip;';
});

// Image upload for applications

function hri_image_upload() {

	switch_to_blog(1);

	$imageinfo = getimagesize( $_FILES['Filedata']['tmp_name'] );

	// allow only images recognized by getimagesize()
	if( $imageinfo !== false ) {

		$newID = media_handle_upload( 'Filedata', 0 );
		$thumbnail = wp_get_attachment_image_src( $newID, 'thumbnail' );

		add_post_meta( $newID, 'front_upload', 1 );

		echo $newID, '-', $thumbnail[0];

	}

	exit;

}

add_action('wp_ajax_hri_front_upload', 'hri_image_upload');
add_action('wp_ajax_nopriv_hri_front_upload', 'hri_image_upload');

?>

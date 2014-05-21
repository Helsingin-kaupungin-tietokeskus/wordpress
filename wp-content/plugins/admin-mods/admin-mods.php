<?php
/*
Plugin Name: Administration interface modifications
Description: Enhancements to the administration interface.
Author: Hallanvaara
Version: 1.0
Author URI: http://www.hallanvaara.com
*/

// *** Head ***

// Head mod function
function make_mine() {
    $url = get_option('siteurl');
    $url = $url . '/wp-content/plugins/admin-mods/admin.css';
    echo '<link rel="stylesheet" type="text/css" href="' . $url . '" />';
    
    // Get site path nicely formatted, eg. "/fi/" => "/fi" or "/" => ""
    $site_path = strtoupper(rtrim(parse_url(get_option('home'), PHP_URL_PATH), '/'));
    
	// Use JS to do some adjustments
	echo '
		<script language="javascript">
			jQuery(document).ready(function() {
				jQuery("label.selectit[for$=\'ping_status\']").hide(); // hide trackback and pingback option
				//jQuery("form#post").before("<div class=\"instructions\"><h3>Ohjeita</h3><ul><li>kiinnitä huomiota otsikointiin: ole informatiivinen ja kiinnostava</li><li>muista merkitä artikkeliisi <a href=\"/ohjeet/artikkelien-kirjoittaminen\">oteraja</a></li><li>älä HUUDA</li></ul></div>"); // add some instructions after edit form title
				jQuery("span#site-title").after("<span id=\"site-path\">' . $site_path . '</span>");
				if (pagenow == "page") {
					if (jQuery("html").attr("lang") == "fi-FI") {
						jQuery("#categorydiv h3.hndle span").html("Sisältöä samasta aiheesta");
						jQuery("#categorydiv #taxonomy-category").before("<p><strong>Näytä tämän sivun yhteydessä sisältöä valituista aiheista</strong></p>");
					} else if (jQuery("html").attr("lang") == "sv-SE") {
						jQuery("#categorydiv h3.hndle span").html("Relaterat innehåll");
						jQuery("#categorydiv #taxonomy-category").before("<p><strong>Visa relaterat innehåll från de valda kategorierna med denna sida</strong></p>");
					} else {
						jQuery("#categorydiv h3.hndle span").html("Related content");
						jQuery("#categorydiv #taxonomy-category").before("<p><strong>Show related content from these categories with this page</strong></p>");
					}
				}
			});
		</script>
	';
}

// Hook into admin panel head
add_action('admin_head', 'make_mine');


// *** Login ***
// login mod function
function my_login() {
	echo '<link rel="stylesheet" type="text/css" href="' . get_option('siteurl') . '/wp-content/plugins/admin-mods/login.css" />'."\n";
}
// Hook into login page head
add_action('login_head', 'my_login');



// *** Media handling ***
function filter_media() {
	echo 'kiisseli';
}
add_action('get_media_item','filter_media');


?>
<?php
/**
 * Template name: data search
 */

/*
$uriparams = explode('/', $_SERVER['REQUEST_URI']);
echo $uriparams[3]; print_r($uriparams);
if($uriparams[2] === 'data-haku' && (!isset($uriparams[3]) || empty($uriparams[3]) || $uriparams[3] === '#')) {
	header("Location: /" . HRI_LANG . "/dataset");
}
*/

//wp_enqueue_script( 'jquery.ui.autocomplete.HRI', get_bloginfo( 'template_url' ) . '/js/jquery.ui.autocomplete.HRI.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
// Required to allow HTML in source labels for autocomplete.
wp_enqueue_script( 'jquery.ui.autocomplete.html', get_bloginfo( 'template_url' ) . '/js/jquery.ui.autocomplete.html.js', array( 'jquery', 'jquery-ui-autocomplete' ) );
// Include this jQuery UI style to hide ui-helper-hidden-accessible class elements.
wp_enqueue_style( 'wp-jquery-ui-dialog' );

get_header();

?><script type="text/javascript">
// <!--
// Redirect to CKAN's data search URL.
if(document.URL.indexOf("data-search") > 0) {
	window.setInterval(function() { window.location.replace('/en/dataset?q=&sort=metadata_created+desc') }, 10000);
}
else {
	window.setInterval(function() { window.location.replace('/fi/dataset?q=&sort=metadata_created+desc') }, 10000);
}
</script>

<?php if(HRI_LANG == 'fi'): ?>
<h5>Tämä hakusivu on vanhentunut. Sinut uudelleenohjataan uuteen datahakuun 10 sekunnin kuluessa - tai voit klikata <a href="/fi/dataset?q=&sort=metadata_created+desc">tästä</a>. Hakuparametreja ei säilytetä.</h5>
<?php else: ?>
<h5>This search page is outdated. You shall be redirected to the new data search in 10 seconds - or you can click <a href="/en/dataset?q=&sort=metadata_created+desc">here</a>. Search parameters are not preserved.</h5>
<?php endif; ?>
<?php

get_footer();

?>
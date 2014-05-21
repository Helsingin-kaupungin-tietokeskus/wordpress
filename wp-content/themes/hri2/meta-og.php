<meta property="og:title" content="<?php global $title; echo $title; ?>" />

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
	$queried_object_id = $wp_query->get_queried_object_id();
	$this_post = get_post( $queried_object_id );
	if( !empty( $this_post ) ) {

		$meta_description = n_words( notes( false, false, ORIGINAL_BLOG_ID, $this_post->ID ), 30, false );

	}

	restore_current_blog();
} elseif ( isset( $args['posttype'] )
		&& $args['posttype'] == 'application' ) {
	global $wp_query;

	switch_to_blog(1);
	$queried_object_id = $wp_query->get_queried_object_id();
	$this_post = get_post( $queried_object_id );

	if( !empty( $this_post ) ) {
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
	}

	restore_current_blog();
} else {
	global $wp_query;

	$queried_object_id = $wp_query->get_queried_object_id();
	$this_post = get_post( $queried_object_id );

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
			$queried_object_id = $wp_query->get_queried_object_id();
			$this_post = get_post( $queried_object_id );

			if( !empty( $this_post ) ) {

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

			}

			restore_current_blog();
		}
	}

}

?>
<meta property="og:description" content="<?php echo strip_tags($meta_description); ?>" />
<meta property="og:image" content="<?php echo $meta_image; ?>" />
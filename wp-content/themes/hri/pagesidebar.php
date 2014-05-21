<?php
/*
Keskimmäinen palsta
*/
?>

<div id="pagesidebar">

<?php

// ------------- Subpages

global $args;

if ( $args['posttype'] == 'application' ) {

	?>
	<ul id="pagetree">
		<?php
			
			switch_to_blog(1);
			$app_args = array(
				'taxonomy' => 'hri_appcats',
				'hierarchical' => true,
				'title_li' => '',
				'use_desc_for_title' => true,
				'echo' => false
			);

			$app_cats = wp_list_categories( $app_args );
			restore_current_blog();

			// http://localhost/hri_multisite/category/open-souce/

			if ( ORIGINAL_BLOG_ID == 2 ) $app_cats = str_replace( 'category', 'fi/sovellukset/kategoria', $app_cats );
			if ( ORIGINAL_BLOG_ID == 3 ) $app_cats = str_replace( 'category', 'en/applications/category', $app_cats );

			echo $app_cats;

		?>
	</ul>
	<?php

//	if ( ORIGINAL_BLOG_ID == 2 ) echo '<a class="blocklink newblocklink" href="' . ROOT_URL . '/fi/ilmoita-uusi-sovellus/">+ Ilmoita uusi sovellus</a>';
//	if ( ORIGINAL_BLOG_ID == 3 ) echo '<a class="blocklink newblocklink" href="' . ROOT_URL . '/en/new-application-submission/">+ New application submission</a>';

}

if ( $args['posttype'] == 'application-idea' || is_post_type_archive('application-idea') ) {
	if ( ORIGINAL_BLOG_ID == 2 ) echo '<a class="blocklink newblocklink" href="' . ROOT_URL . '/fi/uusi-sovellusidea/">+ Uusi sovellusidea</a>';
	if ( ORIGINAL_BLOG_ID == 3 ) echo '<a class="blocklink newblocklink" href="' . ROOT_URL . '/en/new-application-idea/">+ New application idea</a>';
}

if ( $args['posttype'] == 'data-request' || is_post_type_archive('data-request') ) {
	if ( ORIGINAL_BLOG_ID == 2 ) echo '<a class="blocklink newblocklink" href="' . ROOT_URL . '/fi/uusi-datatoive/">+ Uusi datatoive</a>';
	if ( ORIGINAL_BLOG_ID == 3 ) echo '<a class="blocklink newblocklink" href="' . ROOT_URL . '/en/new-data-request/">+ New data request</a>';
}

if (is_category()) {

	$parentCatList = get_category_parents($cat, false, ',', true);
	if (is_string($parentCatList)) {
		$parentCatListArray = explode(",",$parentCatList);
		$topParent = $parentCatListArray[0];
	
		$this_category = get_category_by_slug($topParent);
	
		$category_tree = wp_list_categories('echo=0&orderby=id&show_count=0&title_li=&use_desc_for_title=1&child_of='.$this_category->cat_ID);
	
		if ( substr_count( $category_tree, '<a' ) > 1 ) echo '<ul id="pagetree">' . $category_tree . '</ul>';
	}
	
}
if(is_single()){
	if ($post->post_type == 'help-page' && is_user_logged_in()) {
		hri_help_post_tree_root($post->ID);
	} else {
		$category = get_the_category($post->ID);

		if ( !empty($category) ) {

			$category_id = get_category_by_slug($category[0]->category_nicename );
			$parentCatList = get_category_parents( $category_id, false, ",", true );

			if ( !is_object( $parentCatList ) || !get_class( $parentCatList ) === 'WP_Error' ) {

				$parentCatListArray = split(",",$parentCatList);

				$topParent = isset( $parentCatListArray[0] ) ?  $parentCatListArray[0] : null;
				$this_category = get_category_by_slug($topParent);

				$catlist = wp_list_categories('echo=0&orderby=id&show_count=0&title_li=&use_desc_for_title=1&child_of='.$this_category->cat_ID);
				if ( substr_count( $catlist, '<a' ) > 1 ) {

					echo '<ul id=pagetree>';

					$catlist = wp_list_categories('orderby=id&show_count=0&title_li=&use_desc_for_title=1&child_of='.$this_category->cat_ID.'&echo=0');
					$catitemID = "cat-item-" . $category[0]->cat_ID;

					$position = strpos($catlist, $catitemID);

					if ($position) {

						// Lisätään css-luokka current-cat sekaan
						$catlist = str_replace($catitemID, "current-cat ".$catitemID, $catlist);

					}
					echo $catlist . '</ul>';
				}

			}
		}
	}
}

if(is_page()){
	hri_pagetree($post->ID);
}


// ------------- Related posts
// TODO: onko tämä enää käytössä?
// Käydään ensiksi läpi avoinna olevan $postin kategoriat. Otsikot lisätään valmiina linkkeinä omaan taulukkoon ja $catsiin kategorioiden ID:t
if(is_page()){

	foreach((get_the_category($post->ID)) as $category) {
	
		$otsikot[] = '<h2 class="inline"><a href="' . home_url( '/' ) . 'category/' . $category->slug . '">' . $category->name . '</a></h2>';	
		
		$cats[] = $category->cat_ID;
	
	}
	
	if (!empty($otsikot)) {
	
		if (count($otsikot) > 1) for ($i = 0; $i < count($otsikot); $i++) {
	
			//Lisätään otsikot $html-muuttujaan ja tarvittaessa väliin pilkku tai ja-sana
			if ($i == count($otsikot) - 1) $html .= ' ' . __('and','twentyten') . ' ';
			elseif ($i > 0) $html .= ', ';
			$html .= $otsikot[$i];
	
		} else $html .= $otsikot[0];
	
		// Muodostetaan hakulauseke WP:lle
		$hriWPQ = "showposts=3&cat=";
		$hriWPQ .= implode(',', $cats);
		
		$html .= '<div class="pagesiderbarnostot">';
		
		$cat_posts = new WP_Query($hriWPQ);
	
		// Jos kategorioista löytyi postauksia
		if ($cat_posts->have_posts()) {
	
			while ( $cat_posts->have_posts() ) {
			
				$cat_posts->the_post();	
	
				$html .= '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>' . get_the_excerpt() . hri_read_more();
				
			}
			
		}
		
		$html .= '</div>';
		
	}
	
	if (isset($html)) echo $html;
	
}

?>

</div>
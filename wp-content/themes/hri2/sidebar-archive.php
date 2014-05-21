<div class="column col-narrow"><?php

	if (is_category()) {

		$parentCatList = get_category_parents($cat, false, ',', true);

		if (is_string($parentCatList)) {
			$parentCatListArray = explode(",",$parentCatList);
			$topParent = $parentCatListArray[0];

			$this_category = get_category_by_slug($topParent);

			$category_tree = wp_list_categories('echo=0&orderby=order&show_count=0&title_li=&use_desc_for_title=1&child_of='.$this_category->cat_ID);

			if ( substr_count( $category_tree, '<a' ) > 1 ) echo '<nav><ul>' . $category_tree . '</ul></nav>';
		}

	}

	global $wp_query;

	if( $wp_query->query_vars['post_type'] == 'application-idea' ) {

		?><div class="infobox bluebox clearfix ">
	<div class="multiline heading">
		<h3>Onko sinulla idea sovelluksesta?</h3>
	</div>
		<div class="circle-icon circle-icon-app"></div>
		<div>
			<p>Jaa sovellusideasi muille. Joku saattaa innostua ja luoda ideoimasi sovelluksen.</p>
			<a class="arrow" href="<?php echo ROOT_URL, '/fi/uusi-sovellusidea/' ?>">Lähetä sovellusidea</a>
		</div>
	</div><?php

	} else {

		?><aside><?php dynamic_sidebar('sidebar-archive'); ?></aside><?php

	}

?>
</div>
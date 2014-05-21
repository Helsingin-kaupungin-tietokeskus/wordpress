<aside class="column col-narrow">

	<?php

	global $post;

	$terms = wp_get_post_terms( $post->ID, 'category' );

	if( !empty( $terms ) )  {

		$parentCatList = get_category_parents( ( $terms[0]->parent > 0 ) ? $terms[0]->parent : $terms[0]->term_id , false, ',', true);

		if (is_string($parentCatList)) {
			$parentCatListArray = explode(",",$parentCatList);
			$topParent = $parentCatListArray[0];

			$this_category = get_category_by_slug($topParent);

			$category_tree = wp_list_categories('echo=0&orderby=order&show_count=0&title_li=&use_desc_for_title=1&child_of='.$this_category->cat_ID);

			if ( substr_count( $category_tree, '<a' ) > 1 ) echo '<nav><ul>' . $category_tree . '</ul></nav>';
		}

	}

	?>

	<?php dynamic_sidebar('sidebar'); ?>

	<div class="custom-sidebar">
		<?php the_field( '_custom_sidebar' ); ?>
	</div>

</aside>
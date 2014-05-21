<aside class="column col-narrow">

	<nav class="clearfix">
		<?php

		global $post;

		$curID = get_post_ancestors( $post->ID );

		if($curID && $curID[0]<>0) $pageID = end($curID);
		else $pageID = $post->ID;

		$children = wp_list_pages('title_li=&child_of='.$pageID.'&echo=0');

		if ($children) {

			?><ul><?php

			echo $children;

			?></ul><?php
		}

		?>
	</nav>

	<?php dynamic_sidebar('sidebar-page'); ?>

	<div class="custom-sidebar">
		<?php the_field( '_custom_sidebar' ); ?>
	</div>

</aside>
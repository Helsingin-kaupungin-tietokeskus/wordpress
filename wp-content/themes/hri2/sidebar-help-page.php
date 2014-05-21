<aside class="column col-narrow">

	<nav class="clearfix">
		<?php

		global $post;

		$curID = get_post_ancestors( $post->ID );

		if($curID && $curID[0]<>0) $pageID = end($curID);
		else $pageID = $post->ID;

		// http://core.trac.wordpress.org/ticket/17590 - wp_list_pages() not setting "current_page_item" classes on custom post types
		add_filter( 'page_css_class', function( $css_class, $page ) {
			global $post;
			if ( $post->ID == $page->ID ) {
				$css_class[] = 'current_page_item';
			}
			return $css_class;
		}, 10, 2 );

		$children = wp_list_pages('title_li=&echo=0&post_type=help-page');

		if ($children) {

			?><ul><?php

			echo $children;

			?></ul><?php
		}

		?>
	</nav>

</aside>
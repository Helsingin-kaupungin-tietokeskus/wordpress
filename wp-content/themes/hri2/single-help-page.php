<?php

get_header();

if (!is_user_logged_in()) {

	wp_login_form();

} else {

	get_sidebar( 'help-page' );

	?><div class="column col-wide"><?php

	if ( have_posts() ) {

		while ( have_posts() ) {

			the_post();

			global $post;

			?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<h1><?php the_title(); ?></h1>

				<div class="content clearfix"><?php the_content(); ?></div>

				<?php

				$cats = wp_get_post_categories( $post->ID );
				if( $cats ) {

					?><h6><?php _e( 'Kategoriat', 'hri' ); ?></h6><div class="clearfix"><?php

					foreach( $cats as $cat ) {

						$term = get_term_by( 'id', $cat, 'category' );

						?><a class="term" href="<?php echo home_url(), '/category/', $term->slug; ?>/"><?php echo $term->name; ?></a><?php

					}

					?></div><?php

				}

				hri_author();

				$topborder = false;
				if( $post->post_author == 1 ) $topborder = true;

				hri_add_this( $topborder );

				?>

			</article><?php

			comments_template();

		}
	}

	?></div><?php

}

get_footer();

?>
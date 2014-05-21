<?php

hri_check_query_params();

get_header();

get_sidebar();

?><div class="column col-wide"><?php

if ( have_posts() ) {
	
	while ( have_posts() ) {

		the_post();

		global $post;

		?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php

			$term_vb = get_term_by( 'slug', 'visualisointiblogi', 'category' );
			if( $term_vb ) $term_vb_link = get_term_link( $term_vb, 'category' );
			if( !isset( $term_vb_link ) || is_wp_error( $term_vb_link ) ) $term_vb_link = '';

			$vb_post = ( post_is_in_descendant_category( $term_vb->term_id ) || in_category( $term_vb->term_id ) ) ? true : false;

			if( $vb_post ) { ?><a class="post-vb-link" href="<?php echo $term_vb_link ?>"><img class="post-vb-icon" src="<?php bloginfo( 'template_url' ) ?>/img/vb-post-icon.png" alt="<?php _e( 'Visualisointiblogi', 'hri' ); ?>" /></a><?php } ?>

			<h1 class="clear-none"><?php the_title(); ?></h1>

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

get_footer();

?>
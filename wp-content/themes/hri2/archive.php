<?php

get_header();

global $wp_query;
if( $wp_query->query_vars['post_type'] == 'data-request' ) {

	get_template_part( 'sidebar', 'datarequests' );

} else {

	get_template_part( 'sidebar', 'archive' );

}

global $paged;

if( ORIGINAL_BLOG_ID == 2 ) {

	$term_vb = get_term_by( 'slug', 'visualisointiblogi', 'category' );

	$is_category_vb = ( is_category( $term_vb->term_id ) || is_category_descendant_to( $term_vb->term_id ) ) ? true : false;

} else {

	$is_category_vb = $term_vb = false;

}

?><div class="column col-wide<?php if( $is_category_vb ) { echo ' no-tb'; } ?>"><?php

	if( $is_category_vb ) {

		?><div id="vb_cat"><img id="vb_cat_logo" src="<?php bloginfo( 'template_url' ); ?>/img/vb-title-big.png" alt="Visualisointi blogi" /><?php

		if( !$paged ) {

			echo term_description( $term_vb->term_id, 'category' );

		}

		?></div><?php

	}

	if ( have_posts() ) {

		if( $term_vb ) $term_vb_link = get_term_link( $term_vb, 'category' );
		if( !isset( $term_vb_link ) || is_wp_error( $term_vb_link ) ) $term_vb_link = '';

		while ( have_posts() ) {

			the_post();

			global $post;

			$vb_post = ( !$is_category_vb && ( in_category( $term_vb->term_id ) || post_is_in_descendant_category( $term_vb->term_id ) ) ) ? true : false;

			?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if( $vb_post ) { ?><a class="post-vb-link" href="<?php echo $term_vb_link ?>"><img class="post-vb-icon" src="<?php bloginfo( 'template_url' ) ?>/img/vb-post-icon.png" alt="<?php _e( 'Visualisointiblogi', 'hri' ); ?>" /></a><?php } ?>

				<h1 class="clear-none"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1><?php

				hri_thumbnail( 'med-square', 'archive-thumb left' );

				?><div class="content excerpt-content clearfix"><?php hri_excerpt(); ?></div>

				<div class="post-details">
					<div class="details-time"><?php hri_time_since( $post->post_date ); ?></div>
					<a title="<?php

						if( $post->comment_count == 1 ) {

							_e( '1 kommentti', 'hri' );

						} elseif( $post->comment_count == 0 ) {

							_e( 'Ei kommentteja', 'hri' );

						} else {

							printf( __( '%s kommenttia', 'hri' ), $post->comment_count);

						}

					?>" href="<?php the_permalink(); ?>#comments" class="details-commentcount"><div class="cc_n"><?php echo $post->comment_count; ?></div></a>
					<div class="details-readmore">
						<?php if( $vb_post ) { ?><a class="vb-link" href="<?php echo $term_vb_link ?>"><?php echo $term_vb->name; ?></a><?php } ?>
						<a class="block" href="<?php the_permalink(); ?>"><div class="small-more"></div><?php _e( 'Lue lisää', 'hri' ); ?></a>
					</div>
				</div>

			</article><?php

		}

	}

	global $paged;
	$page = isset( $paged ) && $paged > 1 ? $paged : 1;

	$request_uri = preg_replace( '/\/page\/([0-9]+)\//', '/', $_SERVER['REQUEST_URI'] );

	echo hri_pager( $page, ROOT_URL . $request_uri, $wp_query->found_posts, $wp_query->query_vars['posts_per_page'], false );

?></div><?php

get_footer();

?>
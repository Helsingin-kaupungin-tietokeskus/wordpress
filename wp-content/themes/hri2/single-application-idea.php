<?php

get_header();

?><aside class="column col-narrow">
	<nav>
		<ul>
			<li>
				<div class="icon icon-back-arrow"></div>
				<a href="<?php echo ROOT_URL; ?>/fi/sovellusideat/">Kaikki sovellusideat</a>
			</li>
		</ul>
	</nav>
</aside>
<div class="column col-wide"><?php

if ( have_posts() ) {
	
	while ( have_posts() ) {

		the_post();

		global $post;

		?><article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h1 class="clear-none"><?php the_title(); ?></h1>

			<div class="content clearfix"><?php the_content(); ?></div>

			<?php

			$data = get_post_meta( $post->ID, '_link_to_data' );

			if( !empty( $data ) ) {

				?><h6 class="underline"><?php _e( 'Liittyvää dataa', 'hri' ); ?>:</h6><?php

				switch_to_blog(1);

				foreach( $data as $d ) {

					?><a class="block" href="<?php echo hri_link( get_permalink( $d ), HRI_LANG, 'dataset', false, get_post($d) ); ?>"><?php echo get_the_title( $d ); ?></a><?php

				}

				restore_current_blog();

			}

			hri_add_this( true );

			?>

		</article><?php

		comments_template();

	}
}

?></div><?php

get_footer();

?>
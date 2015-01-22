<?php

function hri_discussion_search() {

//	$start = microtime(true);

	switch_to_blog(1);

	/**
	 * Parse search string to array
	 */

	$illegal = array('\\', "\0", "\n", "\r", "\t", "'", '"', "\x1a", "\x0B");
	$s = str_replace( $illegal, '', $_POST['search_string']);

	list( $search_type, $lang, $search_string ) = explode( '|', $s );
	if( !in_array( $lang, array( 'fi', 'en', 'se' ) ) ) $lang = 'fi';

	$search_array = array();
	$search_params = explode('&', $search_string);

	// Replace spaces with comma and replace double commas with sinlge comma until they are not found anymore
	$search_params[0] = str_replace( ' ', ',', $search_params[0] );
	while ( strpos( $search_params[0], ',,' ) !== false ) $search_params[0] = str_replace( ',,', ',', $search_params[0] );

	foreach( $search_params as $search_param ) {

		if ( substr( $search_param, -1) == ',' ) $search_param = substr( $search_param, 0, strlen( $search_param ) - 1 );

		list($key,$values_string) = explode('=', $search_param);

		if ($values_string) {

			$values = explode(',',$values_string);
			$search_array[$key] = array();
			foreach ( $values as $value ) {

				$search_array[$key][] = $value;

			}
		}
	}

	$page = (int) $search_array['searchpage'][0];
	if ( $page < 1 ) $page = 1;

	global $wpdb;

	require_once(ABSPATH . '/wp-content/plugins/hri-ckan/hri_ckan_query_class.php');

	$query1 = new hri_ckan_query( $wpdb->prefix, true, 'p.*, comment_date AS sort_date', 1 );
	$query1->set_where_raw( "( (p.post_type = 'discussion' OR p.post_type = 'data') AND p.comment_count > 0 )" );
	if ( isset( $search_array['data'] ) )				$query1->search_meta( '_link_to_data', $search_array['data'] );
	if ( isset( $search_array['search_text'] ) )		$query1->search_text( $search_array['search_text'] );
	$query1->set_sorts( array(3) );

	$query1->build_query();
	$query1 = $query1->get_query();

	$query2 = new hri_ckan_query( $wpdb->prefix, false, 'p.*, p.post_date AS sort_date', 2 );
	$query2->set_where( 'p.post_type', 'discussion' );
	$query2->set_where_raw( 'p.comment_count = 0' );

	if ( isset( $search_array['data'] ) )				$query2->search_meta( '_link_to_data', $search_array['data'] );
	if ( isset( $search_array['search_text'] ) )		$query2->search_text( $search_array['search_text'] );

	$query2->build_query();
	$query2 = $query2->get_query();

	$query = "($query1)\nUNION\n($query2)\n";

	if ( isset( $search_array['sort'] ) && !empty($search_array['sort']) ) {

		if( $search_array['sort'][0] == 7 ) $sort = "ORDER BY sort_date ASC";
		if( $search_array['sort'][0] == -7 )$sort = "ORDER BY sort_date DESC";
		if( $search_array['sort'][0] == 2 ) $sort = "ORDER BY post_title ASC";
		if( $search_array['sort'][0] == -2 )$sort = "ORDER BY post_title DESC";
		if( $search_array['sort'][0] == 4 ) $sort = "ORDER BY comment_count ASC";
		if( $search_array['sort'][0] == -4 )$sort = "ORDER BY comment_count DESC";

	}

	if ( !isset($sort) ) $sort = "ORDER BY sort_date DESC";

	$query .= $sort . " LIMIT " . (($page-1)*10) . ", 10";

	$res = $wpdb->get_results( $query );

	if ( $res ) {

		$res2 = $wpdb->get_var( "SELECT FOUND_ROWS();" );
		$nsearch = 0;

		foreach( $res as $r ) {

			$post = get_post( $r->ID );

			$GLOBALS['post'] = $post;

			setup_postdata( $post );

			?><div class="result-discussion searchpost <?php echo ( ++$nsearch % 2 == 1 ? 'odd' : 'even' ); ?>">
			<div class="col1">
				<h3><?php

					if ($post->post_type == 'data') {

						?><div class="result-type"><a href="<?php

							echo ROOT_URL;
							if( ORIGINAL_BLOG_ID == 2 ) echo '/fi/dataset?q=&sort=metadata_created+desc';
							if( ORIGINAL_BLOG_ID == 3 ) echo '/en/dataset?q=&sort=metadata_created+desc';

						?>"><?php
							echo $post->post_type;
						?></a></div><?php

					}

					?><a class="titlelink" href="<?php

					$url = hri_link( get_permalink(), $lang, ($post->post_type == 'data') ? 'dataset' : $post->post_type );

					if ($post->post_type == 'data') $url .= '#comments';

					echo $url;

				?>"><?php $post->post_type == 'data' ? data_title( HRI_LANG ) : the_title(); ?></a>
			</h3>

			<?php

			if( $post->post_type == 'data' ) {

				$first = $wpdb->get_row("SELECT * FROM wp_comments WHERE comment_post_ID = {$post->ID} AND comment_approved = 1 ORDER BY comment_date ASC LIMIT 0,1");

				?><span class="timestamp"><?php
				_e( 'Started', 'hri-ckan' );
				echo ' ';

				hri_time_since( $first->comment_date );
				?></span><?php

			} else {

				$meta_author = get_post_meta( get_the_ID(), 'user_name', true );

				?><cite><?php

				if ( $meta_author != false ) echo $meta_author;
				else the_author();

				?></cite>, <span class="timestamp"><?php hri_time_since( $post->post_date ); ?></span><?php

			}
?>
			</div>
			<div class="col2">
				<a href="<?php echo $url; ?>"><div class="left cc_n"><?php

				$cc = get_comments_number();

				if( $post->post_type == 'data' ) {

					echo $cc;

				} else {

					echo $cc + 1;

				}

			?></div></a>
			</div>
			<div class="col3">
			<?php

				$comment = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_post_ID = {$post->ID} AND comment_approved = 1 ORDER BY comment_date DESC LIMIT 0,1" );

				if( $comment ) { hri_comment_excerpt( $comment[0] , false, true, $post->post_type ); }
				else {

					$hri_virtual_comment = array(
						'hri_excerpt' => n_words( strip_tags( $post->post_content ), 15 ),
						'comment_post_ID' => $post->ID,
						'comment_date' => $post->post_date
					);

					if( isset($meta_author) && $meta_author ) {

						$hri_virtual_comment['comment_author'] = $meta_author;
						$hri_virtual_comment['comment_author_email'] = get_post_meta( $post->ID, 'user_email', true );
					}
					else {

						$userdata = get_userdata( $post->post_author );

						$hri_virtual_comment['comment_author'] = $userdata->display_name;
						$hri_virtual_comment['comment_author_email'] = $userdata->user_email;
					}

					hri_comment_excerpt((object)$hri_virtual_comment, true , false);

				}

			?>
			</div>
			<div class="clear"></div>
		</div>

	<?php

			}

		echo hri_pager( $page, '' , $res2 );

	}
	else _e('No discussions found.', 'hri-ckan');

//	$end = microtime(true);

//	echo '<pre>Search took ' . round( $end - $start, 4 ) . ' seconds.</pre>';

	exit;

}

?>
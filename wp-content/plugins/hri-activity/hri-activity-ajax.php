<?php

function hri_check_types( &$types ) {

	global $hri_post_type_keys;
	foreach( $types as $t ) {
		if( !in_array( $t, $hri_post_type_keys ) ) {
			echo '-1';
			exit;
		}
	}
}

/**
 * @param array $recent_comments
 * @param array $recent_posts
 * @return array
 */
function combine_recent( &$recent_comments, &$recent_posts ) {

	$return = array_merge( $recent_comments, $recent_posts );

	usort($return, function($a, $b) {

		$a_date = isset( $a->ID ) ? $a->post_date : $a->comment_date;
		$b_date = isset( $b->ID ) ? $b->post_date : $b->comment_date;

		if ($a_date == $b_date) return 0;
		return ($a_date > $b_date) ? -1 : 1;

	});

	return $return;

}

function hri_recent_activity_ajax() {

	/**
	 * @var wpdb $wpdb;
	 */

	global $locale,$wpdb;
	$lang = substr( $locale, 0, 2 );
	
	switch_to_blog(1);

	$illegal = array('\\', "\0", "\n", "\r", "\t", "'", '"', "\x1a", "\x0B");
	$s = str_replace( $illegal, '', $_POST['showstring']);

	$show_types = explode(',', $s);
	array_pop( $show_types );

	hri_check_types( $show_types );

	$in_widget = ( isset( $_POST['widget'] ) && $_POST['widget'] == 1 ) ? true : false;

	$show_items = isset( $_POST['count'] ) ? (int) $_POST['count'] : 10;
	$blogs = $wpdb->get_results( "SELECT blog_id, path FROM {$wpdb->blogs}" );

	if( $blogs ) {

		$select_blogs = array();
		foreach( $blogs as $b ) {

			$prefix = $b->blog_id > 1 ? $wpdb->prefix . $b->blog_id . '_' : $wpdb->prefix;
			$sql = "(SELECT *, {$b->blog_id} AS source FROM {$prefix}posts WHERE post_status = 'publish' AND post_type IN ( '" . implode( "', '" , $show_types) . "' ))";
			$select_blogs[] = $sql;

		}

		$query = implode( " UNION\n", $select_blogs ) . " ORDER BY post_date DESC LIMIT 0,$show_items";

		$recent_posts = $wpdb->get_results( $query );

		if ( isset($_POST['comments']) && $_POST['comments'] == 1 ) {

			$select_blogs = array();
			foreach( $blogs as $b ) {

				$prefix = $b->blog_id > 1 ? $wpdb->prefix . $b->blog_id . '_' : $wpdb->prefix;
				$sql = "(SELECT c.*, {$b->blog_id} AS source, post_type FROM {$prefix}comments c, {$prefix}posts WHERE comment_approved = '1' AND comment_type = '' AND ID = comment_post_ID)";
				$select_blogs[] = $sql;

			}

			$query = implode( " UNION\n", $select_blogs ) . " ORDER BY comment_date DESC LIMIT 0,$show_items";

			$recent_comments = $wpdb->get_results( $query );

		} else $recent_comments = array();

		if ( $recent_posts || $recent_comments ) {

			$all_recent = combine_recent( $recent_comments, $recent_posts );

			$i = 0;
			$post_types = get_post_types( null, 'objects' );

			foreach( $all_recent as $recent ) {

				switch_to_blog( $recent->source );

				if( isset($recent->ID) ) {
					// post

					?><div class="recent-item">
						<h4><?php echo $post_types[ $recent->post_type ]->labels->singular_name; ?> /</h4>
						<a class="article-name" href="<?php

						$link = get_permalink( $recent->ID );

						if( $recent->source == 1 ) {
							$link = hri_link( $link, $lang, $recent->post_type );
						}

						echo $link;

						?>"><?php echo get_the_title( $recent->ID ); ?></a>
						<div class="recent_date"><?php hri_time_since( $recent->post_date ); ?></div>
						<a class="block" href="<?php echo $link; ?>"><?php

						$words = $in_widget ? 8 : 24;

						if( $recent->post_type == 'data' ) {
							$short_text = n_words( notes( false, false, HRI_LANG, $recent->ID ) , $words, '' );
						} else {
							$short_text = n_words( preg_replace( '(\[(.)*\])', '', strip_tags( $recent->post_content )), $words, '');
						}

						if( strpos( $short_text, ' ' ) !== false ) {
							$pos = strrpos( $short_text, ' ' );
							$start = substr( $short_text, 0, $pos );
							$end = substr( $short_text, $pos + 1 );
						} else {
							$start = $short_text;
							$end = '';
						}

						echo $start; ?> <div class="inline nowrap"><?php echo $end; ?><div class="arrow-after"></div></div></a><?php


					?></div><?php

				} else {
					// comment

					$comment_post_url = hri_link( get_permalink( $recent->comment_post_ID ), $lang, $recent->post_type );
					$comment_post_title = strip_tags(get_the_title( $recent->comment_post_ID ));

?><div class="recent-item comment">
					<h4><?php

						$r1 = get_comment_meta( $recent->comment_ID, '_hri_rating1', true );

						if( $r1 ) {
							_e( 'Review', 'hri-activity' );
						} else {
							_e('Comment','hri-activity');
						}

					?> /</h4>
					<a class="article-name" href="<?php echo $comment_post_url; ?>"><?php echo get_the_title( $recent->comment_post_ID ); ?></a>
					<div class="recent_date"><?php hri_time_since( $recent->comment_date ); ?></div><?php

					if( $in_widget ) {

						?><a class="block" href="<?php echo $comment_post_url, '#comment-', $recent->comment_ID; ?>"><?php

						if( $r1 ) {
							$r2 = (int) get_comment_meta( $recent->comment_ID, '_hri_rating2', true );
							$r3 = (int) get_comment_meta( $recent->comment_ID, '_hri_rating3', true );

							$sum = $r1 + $r2 + $r3;
							$avg = $sum / 3;

							?><div class="ratings_lift"><div class="ratingbg"><div title="<?php echo round( $avg, 1 ); ?>/5" class="rating" style="width:<?php echo round( $avg * 20, 0); ?>%"></div></div></div><?php

						}

						$comment_excerpt = get_comment_excerpt( $recent->comment_ID );

						if( $comment_excerpt && $comment_excerpt != ' ' ) echo n_words( $comment_excerpt, 10 );
 ?><div class="arrow-after"></div></a><?php

					} else {

						hri_comment_excerpt( $recent );

					}

					?></div><?php

				}

				restore_current_blog();

				if (++$i >= $show_items) break;

			}

			/*if ( $in_widget ) {

				?><a class="readmore" href="<?php

				restore_current_blog();
				echo home_url('/');
				if( ORIGINAL_BLOG_ID == 2 ) echo 'aktiviteetti';
				if( ORIGINAL_BLOG_ID == 3 ) echo 'activity';

				?>/"><?php _e('Read more','hri-activity'); ?></a><?php

			}*/

		} else {
			echo '<p>', __('No any recent activity found.','hri-activity') ,'</p>';
		}
	}

	restore_current_blog();
	exit;
}

function hri_recent_admin_activity_ajax() {

	/**
	 * @var wpdb $wpdb;
	 */

	global $wpdb;
	switch_to_blog(1);

	$illegal = array('\\', "\0", "\n", "\r", "\t", "'", '"', "\x1a", "\x0B");
	$s = str_replace( $illegal, '', $_POST['showstring']);

	$show_types = explode(',', $s);
	array_pop( $show_types );

	$show_items = isset( $_POST['count'] ) ? (int) $_POST['count'] : 10;
	$blogs = $wpdb->get_results( "SELECT blog_id, path FROM {$wpdb->blogs}" );

	if( $blogs ) {

		$select_blogs = array();
		foreach( $blogs as $b ) {

			$prefix = $b->blog_id > 1 ? $wpdb->prefix . $b->blog_id . '_' : $wpdb->prefix;


			$sql = "(SELECT *, {$b->blog_id} AS source FROM {$prefix}posts WHERE post_status ";
			if( current_user_can( 'publish_posts' ) ) $sql .= "IN ('publish','pending')";
			else $sql .= "= 'publish'";

			$sql .= " AND post_type IN ( '" . implode( "', '" , $show_types) . "' ))";

			$select_blogs[] = $sql;

		}

		$query = implode( " UNION\n", $select_blogs ) . " ORDER BY post_date DESC LIMIT 0,$show_items";

		$recent_posts = $wpdb->get_results( $query );

		if ( isset($_POST['comments']) && $_POST['comments'] == 1 ) {

			$select_blogs = array();
			foreach( $blogs as $b ) {

				$prefix = $b->blog_id > 1 ? $wpdb->prefix . $b->blog_id . '_' : $wpdb->prefix;

				$sql = "(SELECT *, {$b->blog_id} AS source FROM {$prefix}comments";
				$sql .= current_user_can( 'moderate_comments' ) ? " WHERE comment_approved != 'trash')" : " WHERE comment_approved = '1')";

				$select_blogs[] = $sql;

			}

			$query = implode( " UNION\n", $select_blogs ) . " ORDER BY comment_date DESC LIMIT 0,$show_items";

			$recent_comments = $wpdb->get_results( $query );

		} else $recent_comments = array();

		if ( $recent_posts || $recent_comments ) {

			$all_recent = combine_recent( $recent_comments, $recent_posts );

			$i = 0;
			$post_types = get_post_types( null, 'objects' );

			foreach( $all_recent as $recent ) {

				switch_to_blog( $recent->source );

				$odd_or_even = $i % 2 == 0 ? 'even ' : 'odd ';

				if( isset($recent->ID) ) {
					// post

					?><div class="recent-item <?php echo $odd_or_even; if( $recent->post_status == 'pending' ) echo ' unapproved'; ?>">
					<div class="recent_date"><?php if (function_exists('time_since')) { echo time_since(abs(strtotime($recent->post_date)), time()), ' ', __('ago','hri-activity'); } ?></div>

					<?php

					echo "<h4>", __('New','hri-activity'), " ", $post_types[ $recent->post_type ]->labels->singular_name, edit_post_link( $recent->post_title ,' ','',$recent->ID);
					if( $recent->post_status == 'pending' ) echo ' <span class="comment_status">' . __( '[Pending]' ) . '</span>';
					echo "</h4>";

					$post = get_post( $recent->ID );
					setup_postdata( $post );

					echo "<p class='activity_excerpt'>", preg_replace( '(\[(.)*\])', '', strip_tags( get_the_excerpt() )), "</p>";

					?></div><?php

				} else {

					// comment

					$comment_post_url = get_edit_post_link( $recent->comment_post_ID );
					$comment_post_title = strip_tags(get_the_title( $recent->comment_post_ID ));
					$comment_post_link = "<a href='$comment_post_url'>$comment_post_title</a>";

					?><div class="recent-item comment <?php echo $odd_or_even, wp_get_comment_status($recent->comment_ID) ?>">
					<div class="recent_date"><?php if (function_exists('time_since')) { echo time_since(abs(strtotime($recent->comment_date)), time()), ' ', __('ago','hri-activity'); } ?></div>

					<h4 class="comment-meta">
					<?php

					echo get_avatar( $recent, 30 ), ucfirst( __('Comment')), ' ';

					printf( __( 'from %1$s on %2$s', 'hri-activity' ), '<cite class="comment-author">' . $recent->comment_author . '</cite>', $comment_post_link );

					if( $recent->comment_approved == 'spam' ) echo ' <span class="comment_status">' . __( '[Spam]' ) . '</span>';
					elseif( $recent->comment_approved == 0 ) echo ' <span class="comment_status">' . __( '[Pending]' ) . '</span>';

					?>
					</h4>
					<p><?php comment_excerpt( $recent->comment_ID ); ?></p>

					</div>

				<?php

				}

				restore_current_blog();

				if (++$i >= $show_items) break;

			}

		} else {
			echo '<p>', __('No any recent activity found.','hri-activity'), '</p>';
		}
	}

	restore_current_blog();
	exit;

}
?>
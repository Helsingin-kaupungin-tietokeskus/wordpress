<?php
/*
Plugin Name: Better recent comments widget
Plugin URI: http://www.hallanvaara.com/wpstuff/better-recent-comments
Description: A replacement for the native WP recent comments plugin. Can tell to what post the comment was made, for example.
Version: 1.0
Author: Hallanvaara
Author URI: http://www.hallanvaara.com
*/


class Better_recent_comments extends WP_Widget {

    // The widget construct. Mumbo-jumbo that loads our code.
    function Better_recent_comments() {
        $widget_ops = array( 'classname' => 'widget_betterrecentcomments', 'description' => __( "A bit more information on most recent comments" ) );
        $this->WP_Widget('BetterRecentComments', __('Better Recent Comments'), $widget_ops);
    }

    // This code displays the widget on the screen.
    function widget($args, $instance) {
        
		extract($args);
        echo $before_widget;
        if(!empty($instance['title'])) {
            echo $before_title . $instance['title'] . $after_title;
        }
		$num_recent_posts = $instance['count'];
        // begin show widget contents stuff
	
		global $wpdb, $id;
		
		// SETTINGS
		// EXCLUDE C BY POST AUTHOR?
		// set to true to exclude an author's comments from his/her own posts.
		// set to false to include them.
		// default: true
		$exclude_authors_comments = true;
		if ($instance['exclude_authors_comments'] != true) {$exclude_authors_comments = false;}
		
		// SHOWING COMMENT CONTENT
		// show comment text
		$show_comment_text = true;
		if ($instance['show_comment_text'] != true) {$show_comment_text = false;}
		// comment text max length
		$max_comment_length = $instance['max_comment_length'];
		
		// POST TITLE TRUNCATION
		// longest length post title allowed
		// (entries whose titles are longer will be truncated.)
		// set it to 0 to avoid truncation altogether.
		// default: 0
		$max_title_length = $instance['max_title_length'];
		
		// INCLUDE LINKS TO COMMENT AUTHORS' WEBSITES?
		// if true, the names of the commenters will be hyperlinked to the websites
		// they specify when filling out the comment form.
		// if false, their names will appear as plain (unlinked) text.
		// default: true
		$link_to_commenters_websites = true;
		if ($instance['link_to_commenters_websites'] != true) {$link_to_commenters_websites = false;}
		
		// COMMENT TYPES TO INCLUDE
		// set this to true to prevent trackbacks from appearing in comment list
		// set it to false to show trackbacks/pingbacks
		// default: true
		$suppress_trackbacks = true;
		if ($instance['suppress_trackbacks'] != true) {$suppress_trackbacks = false;}
		
		
		///////////////////////////////////////////////////////////////
	
		// Second row below is not needed if you are showing one comment per post/topic (see about 30 rows further)
		$sql = "
			SELECT
				comment_post_ID,
				comment_date_gmt,
		";
				
		if ($show_comment_text) {
			$sql .= "
				comment_content,
			";
		}
		
		$sql .= "
				MAX(comment_ID) AS comment_ID
			FROM
				$wpdb->comments C,
				$wpdb->posts P,
				$wpdb->postmeta POSTMETA
			WHERE
				C.comment_post_ID = P.ID
				AND comment_approved = '1'
				AND P.ID = POSTMETA.post_id 
		";
		
		if ($exclude_authors_comments) {
			$sql .= "
				AND C.user_id != P.post_author
			";
		}
	
		if ($suppress_trackbacks) {
			$sql .= "
				AND comment_type = ''
			";
		}
	
		# hack alert: assumes that comments are continuously ordered by comment_ID
		/* To be used if you want only one comment per post
		$sql .= "
			GROUP BY GROUP BY comment_post_ID
			ORDER BY comment_ID DESC
			LIMIT $num_recent_posts
		";
		*/
		/* To be used if you want all comments in chronological order */
		$sql .= "
			GROUP BY comment_date_gmt
			ORDER BY comment_date_gmt DESC
			LIMIT $num_recent_posts
		";
	
		$posts = $wpdb->get_results($sql);
	
	
		if (empty($posts)) {
			echo '<!-- no posts with comments -->';
		}
	
		# build IN clause list
		$comment_id_sql_list = '(';
	
		# we're guaranteed at least one...
		$comment_id_sql_list .= $posts[0]->comment_ID;
	
		for ($i = 1; $i < (count($posts)); $i++) {
			$comment_id_sql_list .= ',' . $posts[$i]->comment_ID;
		}
	
		$comment_id_sql_list .= ')';
	
	
		$sql = "
			SELECT
				comment_post_ID,
				comment_author,
				comment_author_url,
				comment_content,
				post_title,
				user_id
			FROM
				$wpdb->comments C,
				$wpdb->posts P
			WHERE
				C.comment_post_ID = P.ID
			AND comment_approved = '1'
		";
	
		if (!empty($identify_authors_by)) {
			$sql .= "
				AND $identify_authors_by NOT IN $excludes_sql_list
			";
		}
	
		if ($exclude_authors_comments) {
			$sql .= "
				AND C.user_id != P.post_author
			";
		}
	
		$sql .= "
			AND C.comment_ID IN $comment_id_sql_list
			ORDER BY comment_ID DESC
		";
	
		$comments = $wpdb->get_results($sql);
		
		/* This sorta works
		$comments = $wpdb->get_results("
			SELECT $wpdb->comments.* 
			FROM $wpdb->comments 
			JOIN $wpdb->posts 
			ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID 
			WHERE comment_approved = '1' 
			AND post_status = 'publish' 
			ORDER BY comment_date_gmt DESC 
			LIMIT 15
		"); */
		
	
	
		$output = '';
	
		foreach ($comments as $comment) {
			$output .= "\n";
	
			$name = $comment->comment_author;
			$author_id = $comment->user_id;
			$post_id = $comment->comment_post_ID;
			$permalink = get_permalink($post_id);
			$post_title = stripslashes(strip_tags($comment->post_title));
			$post_text = $show_comment_text ? stripslashes(strip_tags($comment->comment_content)) : '';
	
			# truncate post title
			if ($max_title_length and (strlen($post_title) > $max_title_length)) {
				$post_title = htmlspecialchars(rtrim(substr($post_title, 0, $max_title_length))) . '&hellip;';
			}
			else {
				$post_title = htmlspecialchars($post_title);
			}
			
			# truncate post text
			if ($max_comment_length and (strlen($post_text) > $max_comment_length)) {
				$post_text = htmlspecialchars(rtrim(substr($post_text, 0, $max_comment_length))) . '&hellip;';
			}
			else {
				$post_text = htmlspecialchars($post_text);
			}
			
	
			# assemble the output
			$output .= "<div class='content'><h3>";
			$output .= '<a href="?author=' . $author_id . '">';
			$output .= "$name";
			$output .= '</a>';
	
			# (yes, css would be better)
			$output .= ' ' . __('to article') . " <a href='$permalink'>$post_title</a>:</h3>";
			if (!empty($post_text)) {
				$output .= "<p>" . $post_text . "</p>";
			}
			$output .= "</div>\n";
		}
	
		echo $output;		
		
		
		// end show widget contents stuff
		
		
        echo $after_widget;
		
    }

    // Updates the settings.
    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    // The admin form.
    function form($instance) {
        echo '<div id="BetterRecentComments-admin-panel">';
        echo '<p>' . __('This widget will show the most recent comments with a little more precision than the WP original widget') . '.</p>';
		echo '<p>';
		echo '<label for="' . $this->get_field_id("title") .'">' . __('Title') . ':</label>';
        echo '<input type="text" class="widefat" ';
        echo 'name="' . $this->get_field_name("title") . '" ';
        echo 'id="' . $this->get_field_id("title") . '" ';
        echo 'value="' . $instance["title"] . '" />';
        echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id("count") .'">' . __('How many comments') . '</label>';
        echo '<input type="text" size="3" ';
        echo 'name="' . $this->get_field_name("count") . '" ';
        echo 'id="' . $this->get_field_id("count") . '" ';
        echo 'value="' . $instance["count"] . '" />';
        echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id("show_comment_text") .'">';
		echo '<input type="checkbox" class="checkbox" ';
		echo 'name="' . $this->get_field_name("show_comment_text") . '" ';
        echo 'id="' . $this->get_field_id("show_comment_text") . '" ';
        echo 'value="true"';
		if ($instance["show_comment_text"] == "true") {echo ' checked="checked"';}
		echo ' />';
		echo ' ' . __('Show comment text') . '</label>';
		echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id("max_comment_length") .'">' . __('Max comment length') . ': </label>';
        echo '<input type="text" size="3" ';
        echo 'name="' . $this->get_field_name("max_comment_length") . '" ';
        echo 'id="' . $this->get_field_id("max_comment_length") . '" ';
        echo 'value="' . $instance["max_comment_length"] . '" />';
		echo __('Chars') . ' <small>(0=' . __('unlimited') . ')</small>';
        echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id("exclude_authors_comments") .'">';
		echo '<input type="checkbox" class="checkbox" ';
		echo 'name="' . $this->get_field_name("exclude_authors_comments") . '" ';
        echo 'id="' . $this->get_field_id("exclude_authors_comments") . '" ';
        echo 'value="true"';
		if ($instance["exclude_authors_comments"] == "true") {echo ' checked="checked"';}
		echo ' />';
		echo ' ' . __('Exclude post author\'s comments') . '</label>';
		echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id("max_title_length") .'">' . __('Max title length') . ': </label>';
        echo '<input type="text" size="3" ';
        echo 'name="' . $this->get_field_name("max_title_length") . '" ';
        echo 'id="' . $this->get_field_id("max_title_length") . '" ';
        echo 'value="' . $instance["max_title_length"] . '" />';
		echo __('Chars') . ' <small>(0=unlimited)</small>';
        echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id("suppress_trackbacks") .'">';
		echo '<input type="checkbox" class="checkbox" ';
		echo 'name="' . $this->get_field_name("suppress_trackbacks") . '" ';
        echo 'id="' . $this->get_field_id("suppress_trackbacks") . '" ';
        echo 'value="true"';
		if ($instance["suppress_trackbacks"] == "true") {echo ' checked="checked"';}
		echo ' />';
		echo ' ' . __('Suppress trackbacks') . '</label>';
		echo '</p>';
		
        echo '</div>';
    }

} // end class

// Register the widget.
add_action('widgets_init', create_function('', 'return register_widget("Better_recent_comments");'));
?>
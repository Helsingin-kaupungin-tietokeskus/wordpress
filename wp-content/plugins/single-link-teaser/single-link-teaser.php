<?php
/*
Plugin Name: Single link teaser widget
Description: Adds a teaser widget that displays any title and text and takes one link. This can be style dso that the whole teaser is clickable.
Author: Hallanvaara
Version: 1.0
*/

class SingleLinkTeaser extends WP_Widget {
	
	// The widget construct. Mumbo-jumbo that loads our code.
    function SingleLinkTeaser() {
        $widget_ops = array( 'classname' => 'widget_singlelinkteaser', 'description' => __( "A teaser widget that displays any title and text and takes one link. The whole teaser is clickable." ) );
        $this->WP_Widget('SingleLinkTeaser', __('Single link teaser'), $widget_ops);
    }


	/**
	 * Displays Single link teaser on blog.
	 */
	function widget($args, $instance) {
		
		extract($args);
        
		// Begin widget
		echo $before_widget;
		
		// Begin link 
		if( $instance["link"] ) {
			echo '<a href="' . $instance["link"] . '"';
			if ( $instance["targetblank"]) {
				echo ' target="_blank"';
			}
			echo '>';
		}
		
		// Widget title
		if( $instance["title"] ) {
			echo $before_title;
			echo $instance["title"];
			echo $after_title;
		}
		
		// Widget text
		if ( $instance["text"] ) {
			echo $instance["text"];
		}
		
		// HRI readmore link image		
		echo '<img class="hrireadmore" src="' . home_url( '/' ) . 'wp-content/themes/hri/images/readmore_' . substr( get_bloginfo('language'),0,2 ) . '.png" alt="' . __( 'Continue reading', 'twentyten' ) . '" />';
		
		// Close link
		if( $instance["link"] ) {
			echo '</a>';
		}
		
		// Wrap it up
		echo $after_widget;
	
	}
	
	/**
	 * Form processing... Dead simple.
	 */
	function update($new_instance, $old_instance) {
		return $new_instance;
	}
	
	/**
	 * The configuration form.
	 */
	function form($instance) {
		?>

<p>
	<label for="<?php echo $this->get_field_id("title"); ?>">
		<?php _e( 'Title' ); ?>
		:
		<input class="widefat" id="<?php echo $this->get_field_id("title"); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($instance["title"]); ?>" />
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id("text"); ?>">
		<?php _e( 'Text' ); ?>
		:
		<textarea class="widefat" rows="10" cols="20" id="<?php echo $this->get_field_id("text"); ?>" name="<?php echo $this->get_field_name("text"); ?>"><?php echo esc_attr($instance["text"]); ?></textarea>
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id("link"); ?>">
		<?php _e( 'Link' ); ?>
		:
		<input class="widefat" id="<?php echo $this->get_field_id("link"); ?>" name="<?php echo $this->get_field_name("link"); ?>" type="text" value="<?php echo esc_attr($instance["link"]); ?>" />
		<small>Remember to include <em>http://</em> if entering an absolute URL.</small>
	</label>
</p>
<p>
	<label for="<?php echo $this->get_field_id("targetblank"); ?>">
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id("targetblank"); ?>" name="<?php echo $this->get_field_name("targetblank"); ?>"<?php checked( (bool) $instance["targetblank"], true ); ?> />
		<?php _e( 'Open link in new window' ); ?>
	</label>
</p>
<?php
	}

}

add_action( 'widgets_init', create_function('', 'return register_widget("SingleLinkTeaser");') );


?>

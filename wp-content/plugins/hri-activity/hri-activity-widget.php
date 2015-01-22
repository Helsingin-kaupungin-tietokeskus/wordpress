<?php

class hri_activity extends WP_Widget {

	function hri_activity() {
		$this->WP_Widget('hri-activity', __('Activity Stream', 'hri-activity'));
	}

	function form( $instance ) {

		$title = esc_attr($instance['title']);
		$count = (int) $instance['count'];

		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count', 'hri-activity' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="number" value="<?php echo $count; ?>" /></label>
</p>
<?php

	}

	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = (int) $new_instance['count'];

        return $instance;

	}

	function widget( $args, $instance ) {

		global $hri_post_type_keys;
		$title = apply_filters('widget_title', $instance['title']);
		$count = 5;

		extract( $args );
		// From $args:
		/* @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */

		echo $before_widget;

		if( ORIGINAL_BLOG_ID == 3 ) $url = ROOT_URL . '/en/activity-stream/';
		else $url = ROOT_URL . '/fi/aktiviteetti/';

		// if ( !empty($title) ) echo '<a href="', $url, '">', $before_title, $title, $after_title, '</a>';
		if(!empty($title)) { echo $before_title . $title . $after_title; }

?>
<script type="text/javascript">
// <!--
jQuery(function($) {
	function hri_recent_update() {
		$.ajax({
			type: 'POST',
			url: '<?php echo home_url('/wp-admin/'); ?>admin-ajax.php',
			data: {
				action: "get_recent",
				count: <?php echo $count; ?>,
				showstring: '<?php echo implode(',', $hri_post_type_keys), ','; ?>',
				comments: 1,
				widget: 1
			},
			dataType: 'html',
			complete: function() {
			},
			success: function(data) {
				$('#widget_recent_ajax').html(data).show();
			}
		});
	}
	hri_recent_update();
});
// -->
</script>
<div id="widget_recent_ajax" style="display:none"></div>
<?php

		echo $after_widget;

	}

}

?>
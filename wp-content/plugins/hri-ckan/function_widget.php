<?php

class hri_random_data extends WP_Widget {
	
	function hri_random_data() {
	
		$this->WP_Widget('hri-random-data', __('Random data', 'hri-ckan'));
		
	}
	
	function form($instance) {
		
		$title = esc_attr($instance['title']);
		$showdescription = ( $instance['showdescription'] == 1 ) ? 'checked="checked"' : '';
		$showrating = ( $instance['showrating'] == 1 ) ? 'checked="checked"' : '';
		$desclen = (int) $instance['desclen'];
		$count = (int) $instance['count'];

		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count', 'hri-ckan' ); ?></label><br />
	<input style="background:#fff;" class="widefat" type="number" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo $count; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('showdescription'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('showdescription'); ?>" name="<?php echo $this->get_field_name('showdescription'); ?>" value="1" <?php echo $showdescription; ?> /> <?php _e('Show description', 'hri-ckan' ); ?></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('desclen'); ?>"><?php _e('Description length', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('desclen'); ?>" name="<?php echo $this->get_field_name('desclen'); ?>" type="text" value="<?php echo $desclen; ?>" /></label><small><?php _e('Number of words to display from description, leave empty to display entire description','hri-ckan'); ?></small>
</p>
<p>
	<label for="<?php echo $this->get_field_id('showrating'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('showrating'); ?>" name="<?php echo $this->get_field_name('showrating'); ?>" value="1" <?php echo $showrating; ?> /> <?php _e('Show rating', 'hri-ckan' ); ?></label>
</p>

		<?php

	}

	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['showdescription'] = ($new_instance['showdescription']) ? 1 : 0;
		$instance['showrating'] = ($new_instance['showrating']) ? 1 : 0;
		$instance['desclen'] = (int) $new_instance['desclen'];
		$instance['count'] = (int) $new_instance['count'];

        return $instance;
		
	}

	function widget($args, $instance) {
	
		$title = apply_filters('widget_title', $instance['title']);

		extract( $args );
		// From $args:
		/* @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */
		
		echo $before_widget;

		switch_to_blog(1);
		$random = new WP_Query('post_type=data&orderby=rand&posts_per_page=' . $instance['count'] );

		if ( $random->have_posts() ) {

			if ( !empty($title) ) echo $before_title . $title . $after_title;

			while ( $random->have_posts() ) {
				$random->the_post();

				?><div class="random-data"><?php

				if ( $instance['showrating'] ) {
					hri_rating();
				}

				?>
				<a class="post-title" href="<?php echo hri_link( get_permalink(), substr( get_bloginfo('language'),0,2 ), 'data'); ?>"><?php the_title(); ?></a>
				<?php

				if( $instance['showdescription'] ) {
					$notes = notes(false,false);

					if( $instance['desclen'] && $instance['desclen'] > 0 ) {

						$words = explode(" ", $notes);

						if( count($words) > $instance['desclen'] ) {
							$words = array_splice( $words, 0, $instance['desclen'] );
							$notes = implode(' ', $words) . '&hellip;';
						}

					}

					echo nl2br( $notes );

				}

				echo hri_link( hri_read_more(), substr( get_bloginfo('language'),0,2 ), 'data');

				?></div><?php

			}
		}
		
		restore_current_blog();
		
		echo $after_widget;
		
	}

}

class hri_editors_pick extends WP_Widget {

	function hri_editors_pick() {

		$this->WP_Widget('hri-editors-pick', __('Editor\'s pick', 'hri-ckan'));

	}

	function form($instance) {

		$title = esc_attr($instance['title']);
		$freetextarea = esc_attr($instance['freetextarea']);
		$e_name = esc_attr($instance['e_name']);
		$e_title = esc_attr($instance['e_title']);
		$showtitle = ( $instance['showtitle'] == 1 ) ? 'checked="checked"' : '';
		$showdescription = ( $instance['showdescription'] == 1 ) ? 'checked="checked"' : '';
		$showrating = ( $instance['showrating'] == 1 ) ? 'checked="checked"' : '';
		$desclen = (int) $instance['desclen'];

		for($i = 1; $i <= 5; ++$i) {
			${'pick'.$i} = (int) $instance['pick'.$i];
			${'freetext'.$i} = esc_html($instance['freetext'.$i]);
		}

		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('freetextarea'); ?>"><?php _e('Text:', 'hri-ckan' ); ?></label><textarea style="display:block;width:100%" id="<?php echo $this->get_field_id('freetextarea'); ?>" name="<?php echo $this->get_field_name('freetextarea'); ?>" cols="5" rows="10"><?php echo $freetextarea; ?></textarea>
</p>
<p>
	<label for="<?php echo $this->get_field_id('e_name'); ?>"><?php _e('Editor\'s name', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('e_name'); ?>" name="<?php echo $this->get_field_name('e_name'); ?>" type="text" value="<?php echo $e_name; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('e_title'); ?>"><?php _e('Editor\'s title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('e_title'); ?>" name="<?php echo $this->get_field_name('e_title'); ?>" type="text" value="<?php echo $e_title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('showtitle'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('showtitle'); ?>" name="<?php echo $this->get_field_name('showtitle'); ?>" value="1" <?php echo $showtitle; ?> /> <?php _e('Show titles', 'hri-ckan' ); ?></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('showdescription'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('showdescription'); ?>" name="<?php echo $this->get_field_name('showdescription'); ?>" value="1" <?php echo $showdescription; ?> /> <?php _e('Show descriptions', 'hri-ckan' ); ?></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('desclen'); ?>"><?php _e('Description length', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('desclen'); ?>" name="<?php echo $this->get_field_name('desclen'); ?>" type="text" value="<?php echo $desclen; ?>" /></label><small><?php _e('Number of words to display from descriptions, leave empty to display entire description','hri-ckan'); ?></small>
</p>
<p>
	<label for="<?php echo $this->get_field_id('showrating'); ?>"><input type="checkbox" id="<?php echo $this->get_field_id('showrating'); ?>" name="<?php echo $this->get_field_name('showrating'); ?>" value="1" <?php echo $showrating; ?> /> <?php _e('Show ratings', 'hri-ckan' ); ?></label>
</p>
		<?php

		global $wpdb;
		$res = $wpdb->get_results("SELECT ID,post_title FROM wp_posts WHERE post_type = 'data' AND post_status = 'publish' ORDER BY post_title ASC");

		if ( $res ) {
			$titles = array( 0 => '' );
			foreach($res as $r) {
				$titles[$r->ID] = $r->post_title;
			}

			for( $i = 1; $i<=5; $i++ ) {
			?>

<p>
	<label for="<?php echo $this->get_field_id('pick'.$i); ?>"><?php _e('Pick', 'hri-ckan' ); echo " #$i"; ?>: <select style="max-width:100%" id="<?php echo $this->get_field_id('pick'.$i); ?>" name="<?php echo $this->get_field_name('pick'.$i); ?>"><?php


	foreach($titles as $id=>$title) {
		echo "<option value=\"$id\"";
		if ( ${'pick'.$i} == $id ) echo ' selected="selected"';
		echo ">$title</option>";
	}

?></select></label>
	<label for="<?php echo $this->get_field_id('freetext'.$i); ?>"><?php _e('Free text', 'hri-ckan'); echo " #$i"; ?></label>
	<textarea style="display:block;width:100%;" rows="5" cols="10" id="<?php echo $this->get_field_id('freetext'.$i); ?>" name="<?php echo $this->get_field_name('freetext'.$i); ?>" ><?php echo ${'freetext'.$i}; ?></textarea>
</p>

			<?php
			}
		}

	}

	function update($new_instance, $old_instance) {

		$cs = get_bloginfo( 'charset' );

		$instance = $old_instance;
		$instance['title'] = htmlspecialchars( strip_tags($new_instance['title']), ENT_QUOTES, $cs);
		$instance['freetextarea'] = htmlspecialchars( strip_tags($new_instance['freetextarea']), ENT_QUOTES, $cs);
		$instance['e_name'] = htmlspecialchars( strip_tags($new_instance['e_name']), ENT_QUOTES, $cs);
		$instance['e_title'] = htmlspecialchars( strip_tags($new_instance['e_title']), ENT_QUOTES, $cs);
		$instance['showtitle'] = ($new_instance['showtitle']) ? 1 : 0;
		$instance['showdescription'] = ($new_instance['showdescription']) ? 1 : 0;
		$instance['showrating'] = ($new_instance['showrating']) ? 1 : 0;
		$instance['desclen'] = (int) $new_instance['desclen'];

		for($i = 1; $i <= 5; ++$i) {
			$instance['pick'.$i] = (int) $new_instance['pick'.$i];
			$instance['freetext'.$i] = htmlspecialchars($new_instance['freetext'.$i], ENT_QUOTES, $cs);
		}

		return $instance;

	}

	function widget($args, $instance) {

		$title = apply_filters('widget_title', $instance['title']);

		extract( $args );
		// From $args:
		/* @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */

		echo $before_widget;

		if ( !empty($title) ) echo $before_title . $title . $after_title;

		if ( isset( $instance['freetextarea'] ) && !empty( $instance['freetextarea'] ) ) echo '<p>',$instance['freetextarea'],'</p>';

		if ( isset( $instance['e_name'] ) && !empty( $instance['e_name'] ) && isset( $instance['e_title'] ) && !empty( $instance['e_title'] ) ) {

			echo '<p><strong>',$instance['e_name'],'</strong><br />',$instance['e_title'],'</p>';

		}

		switch_to_blog(1);

		for( $i = 1; $i <= 5; $i++) {
			$id = (int) $instance['pick' . $i];
			if ( $id > 0 ) {
				if( $instance['pick' . $i] ) $pick_IDs[] = $id;
				$freetexts[$id] = $instance['freetext' . $i];
			}
		}

		if( empty($pick_IDs) ) return false;

		$GLOBALS['pick_IDs'] = $pick_IDs;

		add_filter( 'posts_orderby', 'order_by_field');

		if (!function_exists('order_by_field')) { function order_by_field() {
			return " FIELD( ID, " . implode( ',', $GLOBALS['pick_IDs'] ) . ")";
		}; }

		$picks = new WP_Query( array(
			'post_type' => 'data',
			'post_status' => 'publish',
			'post__in' => $pick_IDs
		));

		remove_filter( 'posts_orderby', 'order_by_field' );

		if ( $picks->have_posts() ) {
			while ( $picks->have_posts() ) {

				$picks->the_post();

				?>
			<div class="editors-pick">
				<?php

				if ( $instance['showrating'] ) {
					hri_rating();
				}

				if ( isset( $instance['showtitle'] ) && $instance['showtitle'] ) {
				?>
<a class="post-title" href="<?php echo hri_link( get_permalink(), substr( get_bloginfo('language'),0,2 ), 'data'); ?>"><?php the_title(); ?></a>
				<?php
				}

				if( isset($freetexts[ get_the_ID() ]) ) {

					echo '<p>',$freetexts[ get_the_ID() ],'</p>';

				}

				if( $instance['showdescription'] ) {
					$notes = notes(false);

					if( $instance['desclen'] && $instance['desclen'] > 0 ) {

						$notes = n_words( $notes, $instance['desclen'] );

					}

					echo $notes;

				}

				echo hri_link( hri_read_more(), substr( get_bloginfo('language'),0,2 ), 'data');

				?></div><?php

			}
		}

		restore_current_blog();

		echo $after_widget;

	}

}

class hri_vb_widget extends WP_Widget {

	function __construct() {

		$this->WP_Widget('hri-vb-widget', __('Visualisointiblogi widget', 'hri-ckan'), array( 'description' => __('Displays the latest post from visualization blog.','hri-ckan') ) );

	}

	function update($new_instance, $old_instance) {

		$cs = get_bloginfo( 'charset' );
		$instance['title'] = htmlspecialchars( strip_tags($new_instance['title']), ENT_QUOTES, $cs);
		$instance['freetext'] = $new_instance['freetext'];
		return $instance;
		
	}

	function form($instance) {

		$title = esc_attr($instance['title']);
		$freetext = esc_attr($instance['freetext']);
		
		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('freetext'); ?>"><?php _e('Text:', 'hri-ckan' ); ?></label>
	<textarea style="width:100%" id="<?php echo $this->get_field_id('freetext'); ?>" name="<?php echo $this->get_field_name('freetext'); ?>" rows="10" cols="20"><?php echo $freetext; ?></textarea>
</p>
<?php

	}

	function widget($args, $instance) {

		$title = apply_filters('widget_title', $instance['title']);

		extract( $args );
		// From $args:
		/* @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */

		switch_to_blog(5);

		$vb_post = new WP_Query( array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page' => 1
		));

		if ( $vb_post->have_posts() ) {

			echo $before_widget;

			if ( !empty($title) ) echo $before_title . $title . $after_title;

			if ( isset( $instance['freetext'] ) ) echo $instance['freetext'];

			$vb_post->the_post();

			?>

			<p><a class="post-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></p>

			<?php

			echo hri_read_more();

			echo $after_widget;

		}

		restore_current_blog();

	}
}

class hri_latest_apps extends WP_Widget {

	function __construct() {

		$this->WP_Widget('hri_latest_apps', __('Latest applications', 'hri-ckan'), array( 'description' => __('Displays latest applications.','hri-ckan') ) );

	}

	function update($new_instance, $old_instance) {

		$cs = get_bloginfo( 'charset' );
		$instance['title'] = htmlspecialchars( strip_tags($new_instance['title']), ENT_QUOTES, $cs);
		$instance['count'] = (int) $new_instance['count'];
		return $instance;

	}

	function form($instance) {

		$title = esc_attr($instance['title']);
		$count = (int) $instance['count'];

		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Count', 'hri-ckan' ); ?>:</label>
	<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="number" value="<?php echo $count; ?>" />
</p>
<?php

	}

	function widget($args, $instance) {

		$title = apply_filters('widget_title', $instance['title']);
		$count = (int) $instance['count'];

		extract( $args );
		// From $args:
		/* @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */

		switch_to_blog(1);

		$apps = new WP_Query( array(
			'post_type' => 'application',
			'post_status' => 'publish',
			'posts_per_page' => $count
		));

		if( $apps->have_posts() ) {

			echo $before_widget;

			global $path;
			
			$link = '<a href="' . home_url() . $path . __( 'applications', 'hri-ckan' ) . '/">';

			if ( !empty($title) ) echo $link, $before_title, $title, $after_title, '</a>';

			while( $apps->have_posts() ) {

				$apps->the_post();
				global $post;

				?>
				<div class="hri_app"><a class="post-title" href="<?php hri_link( get_permalink() ); ?>"><?php the_title(); ?></a>
					<div class="postdate"><?php hri_time_since( $post->post_date ); ?></div><?php

				echo strip_tags( get_the_excerpt() );

				echo hri_read_more();

				?></div><?php
			}

			echo $after_widget;

		}

		restore_current_blog();

	}

}

class hri_infobox extends WP_Widget {

	static public $icons1 = array(
		'hri' => 'HRI-logo',
		'excl' => 'Huutomerkki',
		'app' => 'Sovellus',
		'left' => 'Nuoli vasemmalle',
		'right' => 'Nuoli oikealle',
		'up' => 'Nuoli ylös',
		'minus' => 'Miinus',
		'download' => 'Lataa',
		'data' => 'Data',
		'phone' => 'Puhelin',
		'share' => 'Jako',
		'tools' => 'Työkalu',
		'find' => 'Löydä',
		'stats' => 'Tilasto',
		'idea' => 'Idea',
		'search' => 'Haku',
		'mail' => 'Posti',
		'ok' => 'Ok',
		'discuss' => 'Keskustele',
		'info' => 'Info',
		'plus' => 'Plus'
	);

	static public $icons2 = array(
		'tools' => 'Työkalu',
		'find' => 'Löydä',
		'ok' => 'Ok',
		'stats' => 'Tilasto',
		'idea' => 'Idea',
		'search' => 'Haku',
		'mail' => 'Posti',
		'info' => 'Info',
		'random' => 'Satunnainen',
		'excl' => 'Huutomerkki',
		'app' => 'Sovellus',
		'download' => 'Lataa',
		'discuss' => 'Keskustele',
		'left' => 'Nuoli vasemmalle',
		'right' => 'Nuoli oikealle',
		'up' => 'Nuoli ylös',
		'data' => 'Data',
		'plus' => 'Plus',
		'minus' => 'Minus',
		'share' => 'Jaa',
		'phone' => 'Puhelin',
		'hri' => 'HRI-logo',
	);

	function hri_infobox() {

		$this->WP_Widget('hri-infobox', __('Infobox', 'hri-ckan'));

	}

	function form($instance) {

		$color = ( isset($instance['color']) && $instance['color'] == 1 ) ? 1 : 0;
		$title = esc_attr($instance['title']);
		$icon1 = $instance['icon1'];
		$icon2 = $instance['icon2'];
		$content = $instance['content'];
		$url = esc_attr($instance['url']);
		$link_text = esc_attr($instance['link_text']);

		?>
<p>
	<?php _e( 'Color:', 'hri-ckan' ); ?>
	<input<?php if( $color == 0 ) echo ' checked="checked"'; ?> type="radio" id="<?php echo $this->get_field_id('infoboxcolor0'); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" value="0" /><label for="<?php echo $this->get_field_id('infoboxcolor0'); ?>"><?php _e( 'Light', 'hri-ckan' ); ?></label>
	<input<?php if( $color == 1 ) echo ' checked="checked"'; ?> type="radio" id="<?php echo $this->get_field_id('infoboxcolor1'); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" value="1" /><label for="<?php echo $this->get_field_id('infoboxcolor1'); ?>"><?php _e( 'Blue', 'hri-ckan' ); ?></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('icon1'); ?>"><?php _e( 'Title icon', 'hri-ckan' ); ?></label><select name="<?php echo $this->get_field_name('icon1'); ?>" id="<?php echo $this->get_field_id('icon1'); ?>">
		<option value=""><?php _e( '(empty)', 'hri-ckan' ); ?></option><?php

		if( !empty( hri_infobox::$icons1 ) ) foreach (hri_infobox::$icons1 as $key => $icon) {

			?><option<?php if( $icon1 == $key ) echo ' selected="selected"'; ?>  value="<?php echo $key; ?>"><?php echo $icon; ?></option><?php

		}

	?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('icon2'); ?>"><?php _e( 'Main icon', 'hri-ckan' ); ?></label><select name="<?php echo $this->get_field_name('icon2'); ?>" id="<?php echo $this->get_field_id('icon2'); ?>">
		<option value=""><?php _e( '(empty)', 'hri-ckan' ); ?></option><?php

		if( !empty( hri_infobox::$icons2 ) ) foreach (hri_infobox::$icons2 as $key => $icon) {

			?><option<?php if( $icon2 == $key ) echo ' selected="selected"'; ?> value="<?php echo $key ?>"><?php echo $icon; ?></option><?php

		}

	?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('content'); ?>"><?php _e( 'Content', 'hri-ckan' ); ?></label>
	<textarea style="display:block;width:100%;" rows="10" cols="10" name="<?php echo $this->get_field_name('content'); ?>" id="<?php echo $this->get_field_id('content'); ?>"><?php echo esc_html( $content ); ?></textarea>
</p>
<p>
	<label for="<?php echo $this->get_field_id('url'); ?>"><?php _e('URL', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('url'); ?>" name="<?php echo $this->get_field_name('url'); ?>" type="text" value="<?php echo $url; ?>" /></label>
</p>
<p>
	<label for="<?php echo $this->get_field_id('link_text'); ?>"><?php _e('Link text', 'hri-ckan' ); ?>: <input class="widefat" id="<?php echo $this->get_field_id('link_text'); ?>" name="<?php echo $this->get_field_name('link_text'); ?>" type="text" value="<?php echo $link_text; ?>" /></label>
</p>
		<?php

	}

	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['url'] = strip_tags($new_instance['url']);
		$instance['link_text'] = strip_tags($new_instance['link_text']);
		$instance['content'] = $new_instance['content'];
		$instance['color'] = ( isset($new_instance['color'] ) && $new_instance['color'] == 1 ) ? 1 : 0;
		
		$icon1keys = array_keys( hri_infobox::$icons1 );
		$icon2keys = array_keys( hri_infobox::$icons2 );
		
		$icon1 = ( isset( $new_instance['icon1'] ) && in_array( $new_instance['icon1'], $icon1keys ) ) ? $new_instance['icon1'] : '';
		$icon2 = ( isset( $new_instance['icon2'] ) && in_array( $new_instance['icon2'], $icon2keys ) ) ? $new_instance['icon2'] : '';
		
		$instance['icon1'] = $icon1;
		$instance['icon2'] = $icon2;

        return $instance;

	}

	function widget($args, $instance) {

		$title = apply_filters('widget_title', $instance['title']);
		$bluebox = ( $instance['color'] == 1 ) ? true : false;

		extract( $args );
		// From $args:
		/* @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */

		echo $before_widget;

		?><div class="infobox <?php if($bluebox) echo 'bluebox '; ?>clearfix"><?php

		if ( !empty($title) ) {

			?><div class="heading"><?php

			if( $instance['icon1'] ) { ?><div class="infobox-icon infobox-icon-<?php echo $instance['icon1']; ?>"></div><?php }

			?><h3><?php echo $title; ?></h3></div><?php

		}

		if( !empty( $instance['icon2'] ) ) {

			?><div class="circle-icon circle-icon-<?php echo $instance['icon2'] ?>"></div><?php

		}

		if( !empty( $instance['content'] ) ) eval( '?> <div>' . $instance['content'] . '</div> <?php ' );

		if( !empty( $instance['url'] ) ) {

			$link_text = isset( $instance['link_text'] ) ? $instance['link_text'] : $instance['url'];

			?><a class="arrow" href="<?php echo $instance['url']; ?>"><?php echo $link_text; ?></a><?php

		}

		?></div><?php

		echo $after_widget;

	}

}

?>
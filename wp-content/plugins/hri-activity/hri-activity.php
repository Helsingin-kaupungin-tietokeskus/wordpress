<?php

/*
Plugin Name: HRI activity stream
Plugin URI: http://www.hri.fi/
Description: Activity stream for HRI
Version: 0.5
Author: Barabra
Author URI: http://www.barabra.fi/
*/

load_plugin_textdomain('hri-activity', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
require_once( ABSPATH . '/wp-content/plugins/hri-activity/hri-activity-ajax.php' );

// Right now panel in dashboard
add_filter( 'dashboard_glance_items', 'add_hri_pending' );

global $blog_id;
if ( !defined( 'ORIGINAL_BLOG_ID' ) ) define( 'ORIGINAL_BLOG_ID', $blog_id );

switch_to_blog(1);
global $wpdb;
if ( !defined( 'ROOT_PREFIX' ) ) define( 'ROOT_PREFIX', $wpdb->prefix );
restore_current_blog();

$hri_post_type_keys = array('page','post','data','discussion','application','application-idea','data-request');

function add_hri_pending() {

	if(current_user_can('publish_posts')) {
?>
	<tr>
		<td style="border-bottom: 1px solid #ececec; padding-top: 20px; left: -2px" colspan="2"><p class="sub" style="top: 0;"><?php _e('Awaiting moderation','hri-activity'); ?></p></td>
	</tr>
<?php

		/**
		 * @var wpdb $wpdb;
		 */
		global $wpdb;
		$something = false;
		
		$prefix = ROOT_PREFIX;

		$res = $wpdb->get_results( "(SELECT COUNT(1) AS c, post_type, 1 AS source FROM {$prefix}posts WHERE post_status = 'pending' GROUP BY post_type) UNION
(SELECT COUNT(1) AS c, post_type, 2 AS source FROM {$prefix}2_posts WHERE post_status = 'pending' GROUP BY post_type) UNION
(SELECT COUNT(1) AS c, post_type, 3 AS source FROM {$prefix}3_posts WHERE post_status = 'pending' GROUP BY post_type) UNION
(SELECT COUNT(1) AS c, post_type, 4 AS source FROM {$prefix}4_posts WHERE post_status = 'pending' GROUP BY post_type)" );

		if ($res) {

			$something = true;

			$labels = array(
				'en_US' => array(
					'page' => array('page','pages'),
					'post' => array('post','posts'),
					'data' => array('data','datas'),
					'discussion' => array('discussion','discussions'),
					'application' => array('application','applications'),
					'application-idea' => array('application-idea','application ideas'),
					'data-request' => array('data request','data requests')
				),
				'fi_FI' => array(
					'page' => array('sivu','sivua'),
					'post' => array('artikkeli','artikkelia'),
					'data' => array('data','dataa'),
					'discussion' => array('keskustelun avaus','keskustelun avausta'),
					'application' => array('sovellus','sovellusta'),
					'application-idea' => array('sovellusidea','sovellusideaa'),
					'data-request' => array('datatoive','datatoivetta')
				)
			);

			global $locale;

			foreach ($res as $r) {

				switch_to_blog( $r->source );

?>
<tr>
	<td class="b"><a href="<?php echo home_url('/wp-admin/'); ?>edit.php?post_status=pending&post_type=<?php echo $r->post_type; ?>"><?php echo $r->c; ?></a></td>
	<td class="t"><a class="waiting" href="<?php echo home_url('/wp-admin/'); ?>edit.php?post_status=pending&post_type=<?php echo $r->post_type; ?>"><?php

$field = $r->c == 1 ? 0 : 1;

echo $labels[$locale][$r->post_type][$field];

?></a></td>
</tr>
<?php
				restore_current_blog();
			}
		}

		$query = "(SELECT COUNT(1) AS c, 1 AS source FROM {$prefix}comments WHERE comment_approved = '0') UNION
(SELECT COUNT(1) AS c, 2 AS source FROM {$prefix}2_comments WHERE comment_approved = '0') UNION
(SELECT COUNT(1) AS c, 3 AS source FROM {$prefix}3_comments WHERE comment_approved = '0') UNION
(SELECT COUNT(1) AS c, 4 AS source FROM {$prefix}4_comments WHERE comment_approved = '0');";

		$results = $wpdb->get_results($query);
		if ($results) {

			$something = true;

			foreach ($results as $r) {
				if ($r->c > 0) {

					switch_to_blog($r->source);

					$site = '';
					switch($r->source) {
						case 1: $site = '/'; break;
						case 2: $site = '/fi'; break;
						case 3: $site = '/en'; break;
						case 4: $site = '/se'; break;
					}
?>

<tr>
	<td class="b"><a href="<?php echo home_url('/wp-admin/edit-comments.php?comment_status=moderated'); ?>"><?php echo $r->c; ?></a></td>
	<td class="t"><a class="waiting" href="<?php echo home_url('/wp-admin/edit-comments.php?comment_status=moderated'); ?>"><?php

if ($r->c > 1) _e('comments on site','hri-activity'); else _e('comment on site','hri-activity');

echo " $site";

?></a></td>
</tr>

<?php

					restore_current_blog();

				}
			}
		}

		if( !$something ) {
?>
	<tr>
		<td colspan="2" class="t"><?php _e('Nothing!','hri-activity'); ?></td>
	</tr>
<?php
		}
	}
}

function hri_recent_activity() {

?><script type="text/javascript">
// <!--
jQuery(function($) {
	$('.cb').click(function(){
		$(this).toggleClass('cba');
	});
	function hri_recent_update() {
		var showstring = "";
		var comments = 0;
		$('.posttypes .cba').each(function(){
			showstring += $(this).children('input').val() + ",";
		});

		if($('#copt').hasClass('cba')){comments=1;}

		$.ajax({
			type: 'POST',
			url: '<?php echo home_url('/wp-admin/'); ?>admin-ajax.php',
			data: {
				action: "get_recent",
				count: 20,
				showstring: showstring,
				comments: comments
			},
			dataType: 'html',
			complete: function() {
				$('#recent_loader').hide();
			},
			success: function(data) {
				$('#recent_ajax').html(data).css({'height':'auto'}).show();
			}
		});
	}
	hri_recent_update();
	$('#hri_recent_update').click(function() {
		$('#recent_loader').show();
		$('#recent_ajax').css({ 'height' : $('#recent_ajax').css('height') }).html('');
		hri_recent_update();
	});
});
// -->
</script>
<div id="hri_recent_checkboxes">
<?php

	global $hri_post_type_keys;
	$post_types = get_post_types( array(), 'objects' );

	$cbs = array();
	foreach($hri_post_type_keys as $k) {
		$cbs[$k] = $post_types[$k]->labels->name;
	}

	?>
		<div class="posttypes"><?php
	// options for content types
	foreach($cbs as $k => $c) {
		?>
		<a class="cb cba"><?php echo $c; ?><input type="hidden" value="<?php echo $k; ?>" /></a>
		<?php
	}
	?></div><a id="copt" class="cb cba"><?php _e('Comments','hri-activity'); ?><input type="hidden" value="1" /></a>
		<a id="hri_recent_update" title="<?php _e('Update','hri-activity'); ?>"></a>
		<div class="clear"></div>

</div>
<div style="background:url(<?php echo get_bloginfo('template_url'), '/images/ajax-loader.gif'; ?>) 50% 50% no-repeat;height:32px;" id="recent_loader"></div>
	<div style="display:none;" id="recent_ajax"></div>
<?php

}

function hri_recent_admin_activity() {

	switch_to_blog(1);
	$count = get_option( 'hri_recent_number' );
	restore_current_blog();

	if ( $count == 0 ) $count = 10;

?>

<script type="text/javascript">
// <!--
jQuery(function($) {
	$('.cb').click(function(){
		$(this).toggleClass('cba');
	});
	function hri_recent_update() {
		var showstring = "";
		var comments = 0;
		$('.posttypes .cba').each(function(){
			showstring += $(this).children('input').val() + ",";
		});

		if($('#a_copt').hasClass('cba')){comments=1;}

		$.ajax({
			type: 'POST',
			url: '<?php echo home_url('/wp-admin/'); ?>admin-ajax.php',
			data: {
				action: "get_admin_recent",
				count: <?php echo $count; ?>,
				showstring: showstring,
				comments: comments
			},
			dataType: 'html',
			complete: function() {
				$('#recent_loader').hide();
			},
			success: function(data) {
				$('#recent_ajax').html(data).css({'height':'auto'}).show();
			}
		});
	}
	hri_recent_update();
	$('#hri_recent_admin_update').click(function() {
		$('#recent_loader').show();
		$('#recent_ajax').css({ 'height' : $('#recent_ajax').css('height') }).html('');
		hri_recent_update();
	});
});
// -->
</script>
<div id="hri_recent_checkboxes" class="hide-if-no-js">
<?php

	global $hri_post_type_keys;
	$hri_post_type_keys[] = 'help-page';
	
	$post_types = get_post_types( array(), 'objects' );

	$cbs = array();
	foreach($hri_post_type_keys as $k) {
		$cbs[$k] = $post_types[$k]->labels->name;
	}
?>
	<div class="options">
		<div class="posttypes"><?php
	// options for content types
	foreach($cbs as $k => $c) {
		?>
		<a class="cb cba"><?php echo $c; ?><input type="hidden" value="<?php echo $k; ?>" /></a>
		<?php
	}
	?></div><a id="a_copt" class="cb cba"><?php _e('Comments','hri-activity'); ?><input type="hidden" value="1" /></a>
		<a id="hri_recent_admin_update" class="button-primary"><?php _e('Update','hri-activity'); ?></a>
		<div class="clear"></div>
	</div>
	

</div>
<div style="background:url(/wp-admin/images/loading.gif) 50% 50% no-repeat;height:32px;" id="recent_loader" class="hide-if-no-js"></div>
<div style="display:none;" id="recent_ajax"></div>

<?php

}

function hri_dashboard_widget_controls() {

	switch_to_blog(1);
	if ( !$hri_recent_count = get_option( 'hri_recent_number' ) ) $hri_recent_count = 10;

	if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_POST['hri-recent-number']) ) {
		$hri_recent_count = absint( $_POST['hri-recent-number'] );
		update_option( 'hri_recent_number', $hri_recent_count );
	}

	$number = isset( $hri_recent_count ) ? (int) $hri_recent_count : 0;

	echo '<p><label for="hri-recent-number">' . __('Number of items to show:') . '</label>';
	echo ' <input id="hri-recent-number" name="hri-recent-number" type="text" value="' . $number . '" size="3" /></p>';
	restore_current_blog();

}

// HRI dashboard panel
function hri_dashboard_widget_content() {
	global $blog_id;
	hri_recent_admin_activity();
}

function hri_add_dashboard_widgets() {
	wp_add_dashboard_widget('hri_dashboard_widget', __('Recent activity','hri-activity'), 'hri_dashboard_widget_content', 'hri_dashboard_widget_controls');
	global $wp_meta_boxes;

	$wp_meta_boxes['dashboard']['side']['core']['hri_dashboard_widget'] = $wp_meta_boxes['dashboard']['normal']['core']['hri_dashboard_widget'];
	unset($wp_meta_boxes['dashboard']['normal']['core']['hri_dashboard_widget']);
}

add_action('wp_ajax_get_admin_recent', 'hri_recent_admin_activity_ajax');

add_action('wp_ajax_get_recent', 'hri_recent_activity_ajax');
add_action('wp_ajax_nopriv_get_recent', 'hri_recent_activity_ajax');
add_action('wp_dashboard_setup', 'hri_add_dashboard_widgets' );

require_once( ABSPATH . 'wp-content/plugins/hri-activity/hri-activity-widget.php' );
require_once( ABSPATH . 'wp-content/plugins/hri-activity/hri-activity-cron.php' );

add_action('widgets_init', function() { register_widget( 'hri_activity' ); });

add_action( 'admin_init', function() {
	wp_enqueue_style( 'hri-activity', home_url('/') . 'wp-content/plugins/hri-activity/hri-activity-admin.css' );
});
?>

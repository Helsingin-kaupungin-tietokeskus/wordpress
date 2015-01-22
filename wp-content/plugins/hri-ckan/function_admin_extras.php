<?php

global $blog_id;

if ( $blog_id != 1 ) {
	add_action( 'admin_menu', function() {
		remove_menu_page('edit.php?post_type=application');
		remove_menu_page('edit.php?post_type=data');
		remove_menu_page('edit.php?post_type=discussion');
		remove_menu_page('edit.php?post_type=help-page');
	} );
}

if ( $blog_id == 1 ) {
	add_action( 'admin_menu', function() {
		remove_menu_page('edit.php?post_type=application-idea');
		remove_menu_page('edit.php?post_type=data-request');
	} );
}

/* -------------------------------------------------------------------------------------------------------------------------------- HRI admin head */

// HRI-admin styles
add_action( 'admin_head', function() {
	?>
<style type="text/css">
#non-editable-meta table {width:100%;}
#non-editable-meta table td {padding: 3px 0;vertical-align: top;}
#hri_help ul {margin:6px 0 0 0;}
.hri_identifier{color:#999 !important;text-transform:uppercase;padding-left:10px !important;}
</style>
	<?php
});

/* -------------------------------------------------------------------------------------------------------------------------------- Non editable meta boxes for data */

// Function to create new meta box
function non_editable_meta() {
	add_meta_box('non-editable-meta', 'Meta', 'show_non_editable_meta', 'data', 'normal', 'low');
}

// Function to create actual HTML
function show_non_editable_meta() {

	global $post;
	$custom_field_keys = get_post_custom_keys($post->ID);

	?><table><?php

	if ( $custom_field_keys ) foreach ( $custom_field_keys as $key ) {

		if ( $key{0} != '_' ) {
			if ( strpos( $key, 'notes') !== false ) echo '<tr><td>' . $key . '</td><td>' . nl2br( get_post_meta($post->ID, $key, true)) . '</td></tr>';
			else echo '<tr><td>' . $key . '</td><td>' . get_post_meta($post->ID, $key, true) . '</td></tr>';
		}

	}

	?></table><?php

}

add_action('add_meta_boxes', 'non_editable_meta');

/* -------------------------------------------------------------------------------------------------------------------------------- Add help-pages to Help menu */

add_action( 'admin_head', function() {

	switch_to_blog(1);
	$args = array(
		'post_type' => 'help-page',
		'post_status' => 'publish',
		'echo' => false,
		'title_li' => false,
	);
	$helppages = wp_list_pages( $args );
	restore_current_blog();

	if( $helppages ) {

		$html = '<p><strong>' . __('HRI help pages','hri-ckan') . ':</strong></p><ul id="hri_help">' . $helppages . '</ul>';

		$screen = get_current_screen();
		
		if(is_object($screen)) {
			$screen->add_help_tab( array(
				'id' => 'hri_help',
				'title' => __('HRI help pages','hri-ckan'),
				'content' => $html
			));
		}
	}
});

/* -------------------------------------------------------------------------------------------------------------------------------- Data links for applications */

$add_datalink_to_content_types = array(
	'application', 'discussion', 'application-idea'
);

function do_data_link_box() {
	global $add_datalink_to_content_types;
	foreach ( $add_datalink_to_content_types as $add ) {
		add_meta_box('data_link', __('Linked data','hri-ckan'), 'data_link_box_html', $add, 'normal', 'high');
	}
}

add_action('add_meta_boxes', 'do_data_link_box');

add_action('admin_enqueue_scripts', function(){

	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-ui-autocomplete' );
});

add_action('admin_head', function(){

	global $post, $add_datalink_to_content_types;
	if( !in_array( $post->post_type, $add_datalink_to_content_types ) ) return false;
	
?>
<style type="text/css">
.hri_filter {color:#000;background:#e4e4e4; -moz-border-radius:10px;border-radius:10px; padding: 0 10px;display:block; margin: 2px 0;line-height:16px;cursor:pointer;width:400px;}
.hri_filter:hover {text-decoration:none;color:inherit;background: #e4e4e4 url(<?php bloginfo('template_url'); ?>/images/close.png) 99% 1px no-repeat;}
/* autocomplete */
.ui-autocomplete {position: absolute; background: #f9f9f9; border: 1px #ccc solid; padding: 2px;}
.ui-autocomplete li {list-style-type: none;}
.ui-autocomplete li a {padding:3px;}
#ui-active-menuitem {background:#fff; border:1px solid #a3d600; padding: 2px; color: #34a3db;text-decoration:none;}
</style>
<script type="text/javascript">

if($ === undefined) { $ = jQuery; }

// <!--
jQuery(function($) {

	function hri_linked(){
		var linkedstring='';
		$('#data_link .hri_filter input').each(function(){
			linkedstring+=$(this).val()+',';
		});
		$('#hri_linked_data_string').val(linkedstring);
	}
	$('#data_link .hri_filter').live('click', function() {
		$(this).fadeOut(300, function() {
			$(this).remove();
			hri_linked();
		});
	});

	$("#hri_new_linked").autocomplete({
		source: function(request, response) {
			$.ajax({
				url: "<?php echo ROOT_URL; ?>/wp-admin/admin-ajax.php",
				dataType: "json",
				data: {
					action: "get_data_titles",
					search_string: request.term
				},

				success: function(data) {
				    // Hide the loading image
                    $( "#datalink" ).css("background", "#F9F9F9");
                    if (data.length == 0 ){
                        response(["<?php _e('No search results','hri-ckan'); ?>"]);
                    }
					else{
                        response( $.map( data, function( item ) {
                            return {
                                label: item.title,
                                value: item.title,
                                id: item.id
                            }
                        }));
                    }

				},
                error: function(data) {
                    // response("[<?php _e('No search results','hri-ckan'); ?>]");
                    $( "#datalink" ).css("background", "#F9F9F9");
                }
			})
		},
		minLength: 2,
		select: function( event, ui ) {
			if ( typeof( ui.item.id ) != "undefined" ) {

				var filter = '<a class="hri_filter">'+ui.item.label+'<input type="hidden" value="'+ui.item.id+'" /></a>';
				$(filter).appendTo( $('#data_link_filters') );
				hri_linked();

			} else {
				return false;
			}
			$('#hri_new_linked').val('');
			return false;
		},
		search: function( event, ui ) {
			$( "#datalink" ).css("background", "#F9F9F9 url('<?php echo get_bloginfo('template_url'); ?>/images/ajax-loader_small.gif') no-repeat 98% center");
		},
		close: function(event, ui) {
			$( "#datalink" ).css("background", "#F9F9F9");
		}
	});

});
// -->
</script>
<?php
});

function data_link_box_html() {

	global $post,$wpdb;
	$linked = get_post_meta( $post->ID, '_link_to_data' );

	switch_to_blog(1);

	?><div id="data_link_filters" class="hri_filters"><?php

	if( !empty($linked) ) {

		foreach( $linked as &$l ) $l = (int) $l;

		$query = "SELECT ID,post_title FROM {$wpdb->posts} WHERE ID IN (" . implode( ',', $linked ) . ")";
		$res = $wpdb->get_results( $query );

		if($res) foreach($res as $r) {

			?><a class="hri_filter"><?php echo $r->post_title; ?><input type="hidden" value="<?php echo $r->ID ?>" /></a><?php

		}

	} ?>
		<input type="hidden" value="<?php if($linked) echo implode(',', $linked); ?>" name="hri_linked_data_string" id="hri_linked_data_string">
	</div>
	<h4><?php _e('Add new linked data'); ?></h4>
	<input type="text" size="80" name="hri_new_linked" id="hri_new_linked" /><?php

	restore_current_blog();

}

add_action('save_post', 'save_details');

function save_details($post_id) {

	global $post,$add_datalink_to_content_types;

	if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !in_array( $post->post_type, $add_datalink_to_content_types ) ) return $post_id;

	if ( isset($_POST['hri_linked_data_string']) ) {

		$ids = explode( ',', $_POST['hri_linked_data_string'] );
		if( end($ids) == '' ) array_pop($ids);

		$old_ids = get_post_meta( $post_id, '_link_to_data' );

		// add new ones
		if( $ids )foreach( $ids as $id ) {
			$id = (int) $id;
			if( !in_array( $id, $old_ids ) ) add_post_meta( $post_id, '_link_to_data', $id );
		}

		// delete old ones which was not in the POST
		if ($ids) {
			foreach ( $old_ids as $old ) {
				if( !in_array( $old, $ids ) ) delete_post_meta( $post_id, '_link_to_data', $old );
			}
		} else delete_post_meta( $post_id, '_link_to_data' );

	}

	return $post_id;

}

/* -------------------------------------------------------------------------------------------------------------------------------- AJAX functions for CKAN */

/** REST/Ajax function for checking a single user capability. */
function current_user_can_ajax() {
	
	$user_id    = urldecode($_POST['user_id']);
	$capability = urldecode($_POST['capability']);
	$capability = strtolower(str_replace(' ', ',', $capability));
	$user       = wp_get_current_user();

	$retval = 0;
	// If we're calling the function from www.hri.fi domain we can simply use:
	if(!empty($user)) {

		if(current_user_can($capability)) { $retval = 1; }
	}
	// CKAN will however, at times, technically revert to it's native domain (ckan.hri.fi)
	// so this function is called from ckan.hri.fi => cross-origin => no valid login given
	// => wp_get_current_user() and current_user_can() do not work. So simply use provided
	// $user_id instead.
	if(!$retval && !empty($user_id)) {

		if(user_can((int)$user_id, $capability)) { $retval = 1; }
	}
	
	die('' . $retval);
}
add_action( 'wp_ajax_current_user_can', 'current_user_can_ajax' );
add_action( 'wp_ajax_nopriv_current_user_can', 'current_user_can_ajax' );

/** REST/Ajax function for checking all user data. */
function get_current_user_ajax() {
	
	// http://stackoverflow.com/questions/49547/making-sure-a-web-page-is-not-cached-across-all-browsers
	header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
	header('Pragma: no-cache'); // HTTP 1.0.
	header('Expires: 0'); // Proxies.
	
	// Warning: All string data must be UTF-8 encoded. http://www.php.net/manual/en/function.json-encode.php
	$userdata = wp_get_current_user();

	die(json_encode($userdata));
}
add_action( 'wp_ajax_get_current_user', 'get_current_user_ajax' );
add_action( 'wp_ajax_nopriv_get_current_user', 'get_current_user_ajax' );
?>

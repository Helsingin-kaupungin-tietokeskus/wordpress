<?php

add_filter( 'tiny_mce_before_init', 'my_custom_tinymce' );

function my_custom_tinymce( $init ) {

	$init['theme_advanced_blockformats'] = 'p,h2,h3,h4';
	return $init;

}

add_filter( 'mce_buttons_2', function( $buttons ) {

	array_unshift( $buttons, 'styleselect' );
	return $buttons;

});

add_filter( 'tiny_mce_before_init', function($settings){

	$style_formats = array( array(
		'title' => 'Ingressi',
		'selector' => 'p',
		'classes' => 'caption'
	) );

	$settings['style_formats'] = json_encode( $style_formats );

	return $settings;

});

add_action('admin_init', 'add_button');

function add_button(){
	if (current_user_can('edit_posts') && current_user_can('edit_pages')) {
		add_filter('mce_external_plugins', 'add_plugin');
		add_filter('mce_buttons_2', 'register_button');
	}
}

function register_button($buttons){
	array_push($buttons, "quote");
	return $buttons;
}

function add_plugin($plugin_array){
	$plugin_array['quote'] = get_bloginfo('template_url') . '/js/editor_plugin.js';
	return $plugin_array;
}

/* Custom profiilikenttiä */

function adjust_contact_methods( $contactmethods ) {
	// remove unnecessary fields
	unset($contactmethods['aim']);
	unset($contactmethods['jabber']);
	unset($contactmethods['yim']);

	// add a few new ones
	$contactmethods['phone'] = 'Phone';
	return $contactmethods;
}
add_filter('user_contactmethods','adjust_contact_methods',10,1);

// Add extra fields to user profile and hide some unnecessary ones
function extra_user_profile_fields( $user ) { ?>
	<h3><?php _e("Professional information", "blank"); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="position"><?php _e("Title"); ?></label></th>
			<td>
				<input type="text" name="position" id="position" value="<?php echo esc_attr( get_the_author_meta( 'position', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e("Your professional title or position in your organization."); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="organization"><?php _e("Organization"); ?></label></th>
			<td>
				<input type="text" name="organization" id="organization" value="<?php echo esc_attr( get_the_author_meta( 'organization', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e("The name of your company or organization."); ?></span>
			</td>
		</tr>
		<tr>
			<th><label for="organization_home_page"><?php _e("Organization home page"); ?></label></th>
			<td>
				<input type="text" name="organization_home_page" id="organization_home_page" value="<?php echo esc_attr( get_the_author_meta( 'organization_home_page', $user->ID ) ); ?>" class="regular-text" /><br />
				<span class="description"><?php _e("The URL of your organization's home page. Remember to include <strong>http://</strong>"); ?></span>
			</td>
		</tr>
	</table>

	<script type="text/javascript">
		function cleanprofileform() {
			// hide the omplete Personal Options section
			jQuery("h3:contains('Personal Options')").hide();
			jQuery("h3:contains('Personal Options')").next("table").hide();
			// Hide nickname field (entire table row)
			jQuery("label[for^='nickname']").parents("tr").hide();
		}

		// clean up as soon as this is loaded
		cleanprofileform();

		jQuery(document).ready(function() {
			// once more when the dom is ready just to be on the safe side
			cleanprofileform();
		});
	</script>

	<h3><?php _e("Moderation notifications", "twentyten"); ?></h3>
	<table class="form-table" style="margin-bottom:20px">
		<tr>
			<th><?php _e('Comment notifications','twentyten'); ?></th>
			<td>
				<input type="checkbox" name="hri_notifications" id="hri_notifications" <?php if( get_the_author_meta( 'hri_notifications', $user->ID ) == '1' ) echo 'checked="checked" '; ?>/><label for="hri_notifications"><?php _e('Instant email notifications on flagged comments.','twentyten'); ?></label><br />

			</td>
		</tr>
		<tr>
			<th><label for="hri_digest_interval"><?php _e('Moderation digest','twentyten'); ?></label></th>
			<td><?php
$digest_interval = get_the_author_meta('hri_digest_interval', $user->ID);
$intervals = array(
	0 => __('Never', 'twentyten'),
	1 => __('Daily', 'twentyten'),
	2 => __('Weekly', 'twentyten')
);
?>
				<select style="width:15em" name="hri_digest_interval" id="hri_digest_interval">
<?php
foreach( $intervals as $s => $iv ) {
	echo '<option';
	if ( $s == $digest_interval ) {
		echo ' selected="selected"';
	}
	echo ' value="'.$s.'">'.$iv.'</option>';
} ?>
				</select>
					<br />
				<span class="description"><?php _e('Email digest for all pending content.','twentyten'); ?></span>
			</td>
		</tr>
	</table>

<?php }

// remove profile page's additional_capabilities list
add_action( 'additional_capabilities_display', function() { return false; });

function save_extra_user_profile_fields( $user_id ) {
	if (!current_user_can('edit_user', $user_id )) {
		return false;
	}
	update_user_meta( $user_id, 'position', trim(strip_tags($_POST['position'])) );
	update_user_meta( $user_id, 'organization', trim(strip_tags($_POST['organization'])) );
	update_user_meta( $user_id, 'organization_home_page', trim(strip_tags($_POST['organization_home_page'])) );

	$notifications = isset($_POST['hri_notifications']) ? 1 : 0;

	update_user_meta( $user_id, 'hri_notifications', $notifications );
	update_user_meta( $user_id, 'hri_digest_interval', (int) $_POST['hri_digest_interval'] );

}

// hook into profile forms
add_action( 'show_user_profile', 'extra_user_profile_fields' );
add_action( 'edit_user_profile', 'extra_user_profile_fields' );
// hook into save events
add_action( 'personal_options_update', 'save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'save_extra_user_profile_fields' );
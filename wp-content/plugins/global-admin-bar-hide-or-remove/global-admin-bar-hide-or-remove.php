<?php
/*
Plugin Name: Global Hide/Remove ToolBar: 1. Front End Toolbar Remover
Plugin URI: http://fischercreativemedia.com/wordpress-plugins/global-hide-admin-bar-plugin/
Description: Easily add a global option to hide/remove the new Admin bar in WP 3.1+. 
Author: Don Fischer
Author URI: http://www.fischercreativemedia.com/
Donate link: http://www.fischercreativemedia.com/wordpress-plugins/donate/
Version: 1.5
Text Domain: global-hide-remove-toolbar-plugin

Version info:
See change log in readme.txt file.

    Copyright (C) 2011-2013 Donald J. Fischer

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
error_reporting( E_ALL  & ~E_NOTICE );
//echo get_locale();
$path   = plugin_dir_path( __FILE__ ) . 'lang/';
//echo $path;
$loaded = $path;
$loaded2 = load_plugin_textdomain( 'global-hide-remove-toolbar-plugin', false,  $loaded);

if ( ! $loaded2 ){
    //print "File not found: $path"; 
    //exit;
}
//load_plugin_textdomain( 'global-hide-remove-toolbar-plugin', false, dirname( plugin_basename( __FILE__ )) . '/lang/');

global $show_admin_bar;
add_action( 'admin_init', 'global_adminbar_settings' );
add_action( 'admin_menu', 'global_adminbar_menu' );
add_filter( 'show_admin_bar', 'global_show_hide_admin_bar' );
add_action( 'admin_print_styles-profile.php', 'global_profile_hide_admin_bar' );
add_action( 'admin_print_styles-user-edit.php', 'global_profile_hide_admin_bar' );
add_filter( 'plugin_row_meta', 'global_adminbar_filter_plugin_links', 10, 2);
add_action( 'plugin_action_links_' . plugin_basename(__FILE__), 'global_adminbar_filter_plugin_actions');
register_activation_hook( __FILE__, 'myplugin_activate' );

function myplugin_activate() {
	$checkRoles 	= get_option('global-admin-bar-roles',0);
	$checkProfiles 	= get_option('global-admin-bar-profiles',0);
	$okRoles 		= get_usable_clean_roles();
	$pluginset 		= get_option( 'global-admin-bar-plugin-setting',0);
	$usersset		= get_option( 'global-admin-bar-plugin-user-setting',0);
	if($pluginset == 0){add_option( 'global-admin-bar-plugin-setting' , '1' );}
	if($usersset == 0){add_option( 'global-admin-bar-plugin-user-setting', '1' );}
	if($checkRoles == 0){add_option( 'global-admin-bar-roles', $okRoles );}
	if($checkProfiles == 0){add_option( 'global-admin-bar-profiles', $okRoles );} 
}

function global_adminbar_filter_plugin_actions($links){
	$new_links = array();
	$adminlink = get_bloginfo('url').'/wp-admin/';
	$fcmlink = 'http://www.fischercreativemedia.com/wordpress-plugins';
	$new_links[] = '<a href="'.$adminlink.'options-general.php?page=admin-bar-plugin">'.__('Settings','global-hide-remove-toolbar-plugin').'</a>';
	$new_links[] = '<a href="'.$fcmlink.'/donate/">'.__('Donate','global-hide-remove-toolbar-plugin').'</a>';
	return array_merge($links,$new_links );
}

function global_adminbar_filter_plugin_links($links, $file){
	if ( $file == plugin_basename(__FILE__) ){
		$adminlink = get_bloginfo('url').'/wp-admin/';
		$fcmlink = 'http://www.fischercreativemedia.com/wordpress-plugins';
		$links[] = '<a href="'.$adminlink.'options-general.php?page=admin-bar-plugin">'.__('Toolbar Settings','global-hide-remove-toolbar-plugin').'</a>';
		$links[] = '<a target="_blank" href="'.$fcmlink.'/global-hide-admin-bar-plugin/">'.__('FAQs','global-hide-remove-toolbar-plugin').'</a>';
		$links[] = '<a target="_blank" href="'.$fcmlink.'/donate/">'.__('Donate','global-hide-remove-toolbar-plugin').'</a>';
	}
	return $links;
}


function global_show_hide_admin_bar($showvar) {
	global $show_admin_bar;
	$theRoles = get_option('global-admin-bar-roles');
	$userRole = get_current_user_role();
	if(get_option('global-admin-bar-plugin-setting')=='1' && in_array($userRole,$theRoles)){
		$show_admin_bar = false;
		return false;
	}else{
		return $showvar ;
	}
}

function get_current_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return $role;
}
function get_profile_user_role() {
	global $wp_roles, $user_id;
	$user_id = (int) $user_id;
	$current_user = wp_get_current_user();
	$profileuser = get_user_to_edit($user_id);
	if($user_id != $current_user->ID){
		$roles = $profileuser->roles;
		$role = array_shift($roles);
		return $role;
	}
	return;
}

function global_profile_hide_admin_bar() {
	if(get_option('global-admin-bar-plugin-user-setting')=='1'){
		$checkProfiles 	= get_option('global-admin-bar-profiles');
		$userrole = get_profile_user_role();
		if(is_array($checkProfiles) && in_array($userrole,$checkProfiles)){
			echo '<style type="text/css">.show-admin-bar { display: none !important; }</style>';
		}
	}
	return;
}

function global_adminbar_menu(){
	$current_user = wp_get_current_user();
	if(is_multisite() && is_super_admin()){
		add_options_page(__('Global Hide/Remove Toolbar Plugin Options','global-hide-remove-toolbar-plugin'),__('Hide Toolbar Options','global-hide-remove-toolbar-plugin'),'manage_network', 'admin-bar-plugin', 'gabrhp_admin_bar_page' );
	}elseif(is_multisite() && !is_super_admin()){
	    $theRoles = get_option('global-admin-bar-roles');
	    if(!is_array($theRoles)){$theRoles = array();}
	    if(!in_array(get_current_user_role(),$theRoles)){
			add_options_page(__('Global Hide/Remove Toolbar Plugin Options','global-hide-remove-toolbar-plugin'),__('Hide Toolbar Options','global-hide-remove-toolbar-plugin'), 'manage_options', 'admin-bar-plugin', 'gabrhp_admin_bar_page' );
		}
	}elseif(!is_multisite() && current_user_can('manage_options')){
		add_options_page(__('Global Hide/Remove Toolbar Plugin Options','global-hide-remove-toolbar-plugin'),__('Hide Toolbar Options','global-hide-remove-toolbar-plugin'), 'manage_options', 'admin-bar-plugin', 'gabrhp_admin_bar_page' );
	}
}

function global_adminbar_settings() {
	register_setting( 'global-admin-bar-group', 'global-admin-bar-plugin-setting' );
	register_setting( 'global-admin-bar-group', 'global-admin-bar-plugin-user-setting' );
	register_setting( 'global-admin-bar-group', 'global-admin-bar-roles' );
	register_setting( 'global-admin-bar-group', 'global-admin-bar-profiles' );
	$checkRoles 	= get_option('global-admin-bar-roles');
	$checkProfiles 	= get_option('global-admin-bar-profiles');
		$okRoles = get_usable_clean_roles();
	if($checkRoles == '' ){
		update_option( 'global-admin-bar-roles', $okRoles );
	}
	if($checkProfiles == '' ){
		update_option( 'global-admin-bar-profiles', $okRoles );
	}
}

function get_usable_clean_roles() {
	global $wp_roles;
	$all_roles 		= $wp_roles->roles;
	$newArr 		= array();
	$editable_roles = apply_filters('editable_roles', $all_roles);
	if(count($editable_roles)>0){
		foreach($editable_roles as $key=>$roledata){
			$newArr[] = $key;
		}
	}
	return $newArr;
}

function get_usable_roles($name = 'roles') {
	if((is_multisite() && is_super_admin()) || (!is_multisite() && current_user_can('manage_options'))){
	    global $wp_roles;
	    $theRoles 		= get_option('global-admin-bar-'.$name);
	    $newArr 		= array();
	    $all_roles 		= $wp_roles->roles;
	    $editable_roles = apply_filters('editable_roles', $all_roles);
	    if(!is_array($theRoles)){$theRoles = array();}
	    if(count($editable_roles)>0){
	    	$newArr[] = '<ul style="width:400px;padding-left:8px;">';
	    	foreach($editable_roles as $key=>$roledata){
	    		if(in_array($key,$theRoles)){
	    			$newArr[] = '<li style="width:130px;float:left;">&nbsp;&nbsp;<input type="checkbox" checked="checked" name="global-admin-bar-'.$name.'[]" value="'.$key.'"/> '.$key .'</li>';
	    		} else {
	    			$newArr[] = '<li style="width:130px;float:left;">&nbsp;&nbsp;<input type="checkbox" name="global-admin-bar-'.$name.'[]" value="'.$key.'"/> '.$key .'</li>';
	    		}
	    	}
	    	$newArr[] = '</ul>';
	    	$newArr[] = '<div style="clear:both;"></div>';
	    }
	    return $newArr;
    }
}

function gabrhp_admin_bar_page(){
?>
<div class="wrap">
<h2> <?php _e('Global Hide/Remove Toolbar Plugin Options','global-hide-remove-toolbar-plugin');?></h2>
<div class="error settings_error"><h2><strong><?php _e('PLEASE NOTE','global-hide-remove-toolbar-plugin');?></strong></h2><p><?php _e('This plugin will NOT turn off the toolbar in the ADMIN area of the site, JUST from the Frontend! If you need to turn off both toolbars, deativate this plugin and activate the <strong>BRUTE FORCE</strong> version located in the plugin list (it was included with this plugin).','global-hide-remove-toolbar-plugin');?></p></div>
<form method="post" action="options.php">
    <?php settings_fields( 'global-admin-bar-group' ); ?>
    <table class="form-table">
		<tr valign="top">
			<td style="text-align: left; vertical-align: top;" colspan="2"><?php _e('This plugin is designed to turn off the <strong>FRONT END</strong> Toolbar that is displayed for logged in users in WordPress 3.1+. It may become obsolete if WordPress ever decides to add their own global option - but for now it is very helpful to have a way to turn it off or on.','global-hide-remove-toolbar-plugin');?><br/><br/></td>
		</tr>
		<tr valign="top">
			<td style="text-align: right; vertical-align: top;width:25px;"><input type="checkbox" name="global-admin-bar-plugin-setting" value="1" <?php if(get_option('global-admin-bar-plugin-setting')=='1'){echo 'checked="checked"' ;} ?> /></td><td style="text-align:left; vertical-align: top;line-height:14px;"><strong><?php _e('Hide/Remove Toolbar on front end for logged in users?','global-hide-remove-toolbar-plugin');?></strong></td>
		</tr>
		<tr valign="top">
			<td style="text-align: right; vertical-align: top;width:25px;">&nbsp;</td>
			<td style="text-align:left; vertical-align: top;line-height:14px;">
				<div style="margin:-10px 0 8px 15px;font-style: italic;"><?php _e('<u>Hide</u> only for the following user roles:','global-hide-remove-toolbar-plugin');?></div>
				<?php $uroles = get_usable_roles();echo implode("\n",$uroles);?>
			</td>
		</tr>
		<tr valign="top">
			<td style="text-align: right; vertical-align: top;width:25px;"><input type="checkbox" name="global-admin-bar-plugin-user-setting" value="1" <?php if(get_option('global-admin-bar-plugin-user-setting')=='1'){echo 'checked="checked"' ;} ?> /></td><td style="text-align:left; vertical-align: top;line-height:14px;"><strong><?php _e('Hide/Remove "Show Toolbar when viewing site" option on Profile Page?','global-hide-remove-toolbar-plugin');?></strong></td>
		</tr>
		<tr valign="top">
			<td style="text-align: right; vertical-align: top;width:25px;">&nbsp;</td>
			<td style="text-align:left; vertical-align: top;line-height:14px;">
				<div style="margin:-10px 0 8px 15px;font-style: italic;"><?php _e('<u>Hide</u> only for the following user roles:','global-hide-remove-toolbar-plugin');?></div>
				<?php $uroles = get_usable_roles('profiles');echo implode("\n",$uroles);?>
			</td>
		</tr>
    </table>
    <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes','global-hide-remove-toolbar-plugin') ?>" /></p>
</form>
</div>
<?php  
}
?>
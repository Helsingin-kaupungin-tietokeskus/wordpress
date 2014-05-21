<?php
/*
Copyright (C) 2008 NetWebLogic LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class TwitterGoodiesWidgetsAdmin {

	var $tgw_data;
	
	// Class initialization
	function TwitterGoodiesWidgetsAdmin() {
		//Make decision on what to display
		if(is_admin()){
			$this->tgw_data = get_option('tgw_data');
			add_action('admin_menu', array(&$this, 'menus'));
			add_action( 'wp_ajax_tgw_ajax_lists', array(&$this, 'ajax_lists') );
		}
	}
		
	function menus(){
		$page = add_options_page('Twitter Goodies Widgets', 'Twitter Goodies Widgets', 8, 'twitter-goodies-widgets', array(&$this, 'options'));
		add_action('admin_init', array(&$this, 'scripts'));
		add_action('admin_head-'.$page, array(&$this, 'options_head'));
	}

	function scripts() {
		if($_GET['page'] == "twitter-goodies-widgets"){
			wp_enqueue_script( "dtb-color", "/".PLUGINDIR."/twitter-goodies-widgets/colorpicker/farbtastic.js", array( 'jquery' ) );
			wp_enqueue_style( "dtb-color", "/".PLUGINDIR."/twitter-goodies-widgets/colorpicker/farbtastic.css" );
		}
	}
	
	function options_head() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				//$('#colorpicker').farbtastic('#twittergoodieswidgets_bgcolor');
			});
			jQuery(document).ready(function($){
				$(".twittergoodieswidgets-color").focus(function(e){
					$(this).next().fadeIn('slow', (function(){ $(this).css('display', 'inline'); }));
				});
				$(".twittergoodieswidgets-color").blur(function(e){
					$(this).next().fadeOut('slow', (function(){ $(this).css('display', 'none'); }) );
				});
				var twittergoodieswidgets_bgcolor = function(color){
					var textbox = $(this.wheel).parent().parent().prev();
					textbox.val(color);
					textbox.css('background-color', color);
				}
				$.farbtastic("#colorpicker_shell_background", twittergoodieswidgets_bgcolor).setColor($("#tgw_shell_background").val());
				$.farbtastic("#colorpicker_shell_color", twittergoodieswidgets_bgcolor).setColor($("#tgw_shell_color").val());
				$.farbtastic("#colorpicker_tweets_background", twittergoodieswidgets_bgcolor).setColor($("#tgw_tweets_background").val());
				$.farbtastic("#colorpicker_tweets_color", twittergoodieswidgets_bgcolor).setColor($("#tgw_tweets_color").val());
				$.farbtastic("#colorpicker_tweets_links", twittergoodieswidgets_bgcolor).setColor($("#tgw_tweets_links").val());
			});
		</script>
		<?php
	}
	
	function options() {
		
		$tgw_data = get_option('tgw_data');
		if( !is_array($tgw_data) ){
			add_option('tgw_data');
			$tgw_data = array() ;
		}
		
		if( $_GET['delete'] == '1' && array_key_exists($_GET['tgw_theme'], $tgw_data['themes']) ){
			//Delete the theme
			check_admin_referer('twitter-goodies-widgets-delete');
			unset($tgw_data['themes'][$_GET['tgw_theme']]);
			update_option('tgw_data', $tgw_data);
			?>
			<div class="updated"><p><strong><?php _e("Theme {$_GET['tgw_theme']} has been deleted."); ?></strong></p></div>
			<?php
		}
		
		if( $_POST['twittergoodieswidgets_submitted']==1 ){
			
			//Build the array of options here
			if(!$errors){
				check_admin_referer('twitter-goodies-widgets-edit');
				if( $_POST['twittergoodieswidgets_theme'] != '' ){
					unset($tgw_data['themes'][$_POST['twittergoodieswidgets_theme']]);
					foreach ($_POST as $postKey => $postValue){
						if( substr($postKey, 0, 4) == 'tgw_' ){
							//For now, no validation, since this is in admin area.
							$tgw_data['themes'][$_POST['twittergoodieswidgets_theme']][substr($postKey, 4)] = $postValue;
						}
					}
					update_option('tgw_data', $tgw_data);
					$_GET['tgw_theme'] = $_POST['twittergoodieswidgets_theme'];
				}
				?>
				<div class="updated"><p><strong><?php _e('Options saved. You can <a href="?page=twitter-goodies-widgets">add a new theme</a> or edit the one you just saved below.'); ?></strong></p></div>
				<?php
			}else{
				?>
				<div class="error"><p><strong><?php _e('There were issues when saving your settings. Please try again.'); ?></strong></p></div>
				<?php				
			}
		}
		
		//We get the right theme if requested, otherwise it's a brand new theme
		$tgw_theme = array();
		if( $_GET['tgw_theme'] != '' && array_key_exists($_GET['tgw_theme'], $tgw_data['themes']) ){
			$tgw_theme = $tgw_data['themes'][$_GET['tgw_theme']];
			$tgw_theme_name =  $_GET['tgw_theme'];
			?>
			<div class="updated"><p><strong><?php _e("You are currently editing the &quot;{$_GET['tgw_theme']}&quot; theme. <a href='?page=twitter-goodies-widgets'>Add a new theme</a>"); ?></strong></p></div>
			<?php
		}
		global $TwitterGoodiesWidgets;
		
		//Now display theme
		?>
		<div class="wrap nwl-plugin">
			<h2>Twitter Goodies Widgets Settings</h2>			
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Saved Thems</h3>
						<div class="inside">
							<p>Choose a theme to edit or delete <i>(to duplicate a theme, click to edit it and change the theme name before saving)</i></p>
								<?php
								$wp_nonce = wp_create_nonce('twitter-goodies-widgets-delete');
								if( is_array($tgw_data['themes']) && count($tgw_data['themes'])>0 ){
									foreach( array_keys($tgw_data['themes']) as $theme_key ){
										?>
										<p>
											<?php echo $theme_key ?> - 
											<a href="?page=twitter-goodies-widgets&tgw_theme=<?php echo $theme_key ?>">edit</a> | 
											<a href="?page=twitter-goodies-widgets&tgw_theme=<?php echo $theme_key ?>&_wpnonce=<?php echo $wp_nonce ?>&delete=1">delete</a>
										</p>
										<?php
									}
								}
								?>
								<p><a href="?page=twitter-goodies-widgets">Create a new Theme</a></p>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Plugin Information</h3>
						<div class="inside">
							<p>This plugin was developed by <a href="http://twitter.com/marcussykes">Marcus Sykes</a> @ <a href="http://netweblogic.com">NetWebLogic</a></p>
							<p>Please visit <a href="http://netweblogic.com/forums/">our forum</a> for plugin support.</p>
						</div>
					</div>
				</div>
				<div id="post-body">
					<div id="post-body-content">
						<form method="post" action="">
							<table class="form-table">
								<tbody id="twittergoodieswidgets-body">
									<tr valign="top">									
										<td scope="row" style="width:150px;">
											<label>Theme Name</label>
										</td>
										<td>
											<input type="text" name="twittergoodieswidgets_theme" value="<?php echo $tgw_theme_name ?>" /> 
											<br style="clear:both;"/>
											<i>This is for reference purposes only. It will appear in widget settings, so you save your theme settings and use it across various widgets.</i>
										</td>
									</tr>
									<tr valign="top">									
										<td scope="row" style="width:150px;">
											<label>Poll for new results?</label>
										</td>
										<td>
											<input type="checkbox" name="tgw_features_live" value="1" <?php echo ($tgw_theme['features_live'] == 1) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>	
									<tr valign="top">								
										<td scope="row">
											<label>Include scrollbar?</label>
										</td>
										<td>
											<input type="checkbox" name="tgw_features_scrollbar" value="1" <?php echo ($tgw_theme['features_scrollbar'] == 1) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Number of Tweets</label>
										</td>
										<td>
											<input type="text" name="tgw_rrp" value="<?php echo $tgw_theme['rrp'] ?>" /> 
										</td>
									</tr>
									<tr valign="top">								
										<td scope="row">
											<label>Show Avatars?</label>
										</td>
										<td>
											<input type="checkbox" name="tgw_features_avatars" value="1" <?php echo ($tgw_theme['features_avatars'] == 1) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>
									<tr valign="top">								
										<td scope="row">
											<label>Show Timestamps?</label>
										</td>
										<td>
											<input type="checkbox" name="tgw_features_timestamp" value="1" <?php echo ($tgw_theme['features_timestamp'] == 1) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>
									<tr valign="top">								
										<td scope="row">
											<label>Show hashtags?</label>
										</td>
										<td>
											<input type="checkbox" name="tgw_features_hashtags" value="1" <?php echo ($tgw_theme['features_hashtags'] == 1) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>									
									<tr valign="top">									
										<td scope="row" style="width:150px;">
											<label>Behaviour</label>
										</td>
										<td>
											Timed Interval <input type="radio" class="tgw_features_behaviour" name="tgw_features_behaviour" value="default" onclick="jQuery('.tgw_timed_interval').show()" <?php echo ($tgw_theme['features_behaviour'] == 'default') ? 'checked="checked"':'' ?> />&nbsp;&nbsp;&nbsp;
											Load all tweets <input type="radio" class="tgw_features_behaviour" name="tgw_features_behaviour" value="all" onclick="jQuery('.tgw_timed_interval').hide()" <?php echo ($tgw_theme['features_behaviour'] == 'all') ? 'checked="checked"':'' ?><?php echo ($tgw_theme['features_behaviour'] != 'all' && $tgw_theme['features_behaviour'] != 'default' ) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>
									<tr valign="top" class="tgw_timed_interval" <?php echo ($tgw_theme['features_behaviour'] != 'default') ? 'style="display:none"':'' ?>>								
										<td scope="row">
											<label>Loop?</label>
										</td>
										<td>
											<input type="checkbox" name="tgw_features_loop" value="1" <?php echo ($tgw_theme['features_loop'] == 1) ? 'checked="checked"':'' ?> /> 
										</td>
									</tr>
									<tr valign="top" class="tgw_timed_interval" <?php echo ($tgw_theme['features_behaviour'] != 'default') ? 'style="display:none"':'' ?>>
										<td scope="row">
											<label>Tweet Interval?</label>
										</td>
										<td>
											<input type="text" name="tgw_interval" value="<?php echo $tgw_theme['interval'] ?>" /> <i>Seconds</i>
										</td>
									</tr>	
									<tr valign="top">
										<td colspan="2">
											<h2 style="margin-bottom:0px; padding-bottom: 5px;">Colors</h2>
											<i>Use any valid CSS colour, e.g. #ff9900</i>
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Shell Background</label>
										</td>
										<td>
											<input type="text" name="tgw_theme_shell_background" id="tgw_shell_background" class="twittergoodieswidgets-color" value='<?php echo $tgw_theme['theme_shell_background'] ?>' style="float:left;" />
											<span style="position:absolute; background:#DEDEDE; border:1px solid #CDCDCD; display:none;" id="colorpicker_shell_background"></span> 
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Shell Text</label>
										</td>
										<td>
											<input type="text" name="tgw_theme_shell_color" id="tgw_shell_color" class="twittergoodieswidgets-color" value='<?php echo $tgw_theme['theme_shell_color'] ?>' style="float:left;" />
											<span style="position:absolute; background:#DEDEDE; border:1px solid #CDCDCD; display:none;" id="colorpicker_shell_color"></span> 
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Tweet Background</label>
										</td>
										<td>
											<input type="text" name="tgw_theme_tweets_background" id="tgw_tweets_background" class="twittergoodieswidgets-color" value='<?php echo $tgw_theme['theme_tweets_background'] ?>' style="float:left;" />
											<span style="position:absolute; background:#DEDEDE; border:1px solid #CDCDCD; display:none;" id="colorpicker_tweets_background"></span> 
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Tweet Text</label>
										</td>
										<td>
											<input type="text" name="tgw_theme_tweets_color" id="tgw_tweets_color" class="twittergoodieswidgets-color" value='<?php echo $tgw_theme['theme_tweets_color'] ?>' style="float:left;" />
											<span style="position:absolute; background:#DEDEDE; border:1px solid #CDCDCD; display:none;" id="colorpicker_tweets_color"></span> 
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Links</label>
										</td>
										<td>
											<input type="text" name="tgw_theme_tweets_links" id="tgw_tweets_links" class="twittergoodieswidgets-color" value='<?php echo $tgw_theme['theme_tweets_links'] ?>' style="float:left;" />
											<span style="position:absolute; background:#DEDEDE; border:1px solid #CDCDCD; display:none;" id="colorpicker_tweets_links"></span> 
										</td>
									</tr>
									<tr valign="top">
										<td colspan="2">
											<h2 style="margin-bottom:0px; padding-bottom: 5px;">Weight Dimensions</h2>
											<i>All dimensions are in pixels (px)</i>
										</td>
									</tr>
									<tr valign="top">								
										<td scope="row">
											<label>Width</label>
										</td>
										<td>
											<input type="text" name="tgw_width" value="<?php echo $tgw_theme['width'] ?>" /> 
											<br style="clear:both;"/>
											<i>If left blank, width will adjust automatically.</i>											 
										</td>
									</tr>
									<tr valign="top">
										<td scope="row">
											<label>Height</label>
										</td>
										<td>
											<input type="text" name="tgw_height" value="<?php echo $tgw_theme['height'] ?>" /> 
										</td>
									</tr>			
								</tbody>
								<tfoot>
									<tr valign="top">
										<td colspan="2">	
											<input type="hidden" name="twittergoodieswidgets_submitted" value="1" />
											<?php echo wp_nonce_field('twitter-goodies-widgets-edit'); ?>
											<p class="submit">
												<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
											</p>							
										</td>
									</tr>
								</tfoot>
							</table>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	function ajax_lists(){
		check_ajax_referer( 'tgw_ajax_lists' );
		if ( !$readme_contents = file_get_contents('http://twitter.com/goodies/list_of_lists?screen_name='.$_GET['username']) ) {
			$output = '{"lists":[]}';	
		}else{
			$output = $readme_contents;
		}
		if( isset($_GET['callback']) ){
			$output = $_GET['callback']."($output)";
		}
		echo $output;
		exit();		
	}
}

// Start this plugin once all other plugins are fully loaded
add_action( 'init', create_function( '', 'global $TwitterGoodiesWidgetsAdmin; $TwitterGoodiesWidgetsAdmin = new TwitterGoodiesWidgetsAdmin();' ), 11 );

?>
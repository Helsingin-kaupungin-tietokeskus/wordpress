<?php
/*
Plugin Name: Twitter Goodies Widgets
Plugin URI: http://netweblogic.com/wordpress/twitter-goodies-widgets/
Description: Twitter goodies widgets
Author: NetWebLogic LLC
Version: 1.2
Author URI: http://netweblogic.com
*/
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

class TwitterGoodiesWidgets {
	
	var $default_theme = array(
		  'rrp' => '10',
		  'theme_shell_background' => '#b1dfa4',
		  'theme_shell_color' => '#ab7373',
		  'theme_tweets_background' => '#f3d3d3',
		  'theme_tweets_color' => '#d41616',
		  'theme_tweets_links' => '#f9b3ee',
		  'width' => 'auto',
		  'height' => '200',
		  'features_live' => '1',
		  'features_scrollbar' => '0',
		  'features_timestamp' => '0',
		  'features_hashtags' => '0',
		  'features_avatars' => '1',
		  'features_loop' => '0',
		  'features_live' => '1',
		  'features_behaviour' => 'all',
		  'version' => '2',
		  'interval' => 6
	);
	var $tgw_data;
	
	// Class initialization
	function TwitterGoodiesWidgets() {
		//Make decision on what to display
		$this->tgw_data = get_option('tgw_data');
		add_shortcode('tgw', array(&$this, 'shortcode'));
		register_widget("TwitterGoodiesWidgetsWidget");
		if( !is_admin() ) wp_enqueue_script('twitter-widget', 'http://widgets.twimg.com/j/2/widget.js'); //Add twitter script
	}
	
	function shortcode( $atts = array() ){
		ob_start();
		//Change two keys in $atts
		$atts['tgw_theme'] = $atts['theme'];
		unset($atts['theme']);
		$atts['tgw_username'] = $atts['username'];
		unset($atts['username']);
		$atts['tgw_list'] = $atts['list'];
		unset($atts['list']);
		$this->display( $atts );
		return ob_get_clean();
	}
	
	function display( $instance, $widget_atts=array() ){
		if(is_array($instance)){
			//Merge all the options into one nice array
			extract($widget_atts);
			$theme_data = ( is_array($this->tgw_data['themes']) && array_key_exists($instance['tgw_theme'], $this->tgw_data['themes']) ) ? $this->tgw_data['themes'][$instance['tgw_theme']] : array() ;
			$instance_array = array_merge( $this->default_theme, $theme_data, $instance);
			//We won't accept a '' value if the default has something better
			foreach( $instance_array as $key => $value){
				if( $value == '' ){
					$instance_array[$key] = $this->default_theme[$key];
				}
			}
			
			//Remake array so we make the array multi-dimensional, which we then will convert into a JS object
			foreach($instance_array as $key => $value){
				if( substr($key, 0, 4) != 'tgw_' ){
					$key = explode('_', $key);
					//If there's a _ then we split it into an array
					if( count($key) == 1 )
						$instance_array_obj[$key[0]] = $value;
					elseif( count($key) == 2 )
						$instance_array_obj[$key[0]][$key[1]] = $value;
					elseif( count($key) == 3 )
						$instance_array_obj[$key[0]][$key[1]][$key[2]] = $value;
				}
			}
			
			//Add some extra values to the array 
			$instance_array_obj['interval'] = $instance_array_obj['interval'] * 1000 ;
			
			//Create the method to use depending on the widget type, each has it's own method
			switch ($instance_array_obj['type']) {
				case 'profile':
				case 'faves':
					$js_command = "setUser('{$instance['tgw_username']}').";
					break;
				case 'search':
					$js_command = '';
					break;
				case 'list':
					$js_command = "setList('{$instance['tgw_username']}','{$instance['tgw_list']}').";
					break;		
			}
			//Now convert it into a JS object
			$js_object = $this->php2js($instance_array_obj);
			echo $before_widget;
			?>
			<script type="text/javascript">
				new TWTR.Widget( <?php echo $js_object ?> ).render().<?php echo $js_command ?>start();
			</script>
			<?php
			echo $after_widget;
		}
	}
	
	function php2js( $array, $key = '' ) {
		//Thanks to Domenic Denicola @ http://www.php.net/manual/en/ref.array.php#55852
		// Base case of recursion: when the passed value is not a PHP array, just output it (in quotes).
		if (! is_array ( $array )) {
			// Handle null specially: otherwise it becomes "".
			if ($array === null) {
				return 'null';
			}			
			if( is_numeric($array) ){
				if($array == 1 || $array == 0 && !in_array($key, array('width', 'height', 'interval', 'rrp', 'version')))
					return ($array == 1) ? 'true':'false';
				else
					return $array;
			}else{
				return '"' . $array . '"';
			}
		}		
		// Open this JS object.
		$retVal = "{"."\n";		
		// Output all key/value pairs as "$key" : $value
		// * Output a JS object (using recursion), if $value is a PHP array.
		// * Output the value in quotes, if $value is not an array (see above).
		$first = true;
		foreach ( $array as $key => $value ) {
			// Add a comma before all but the first pair.
			if (! $first) {
				$retVal .= ', '."\n";
			}
			$first = false;
			
			$retVal .= $key . ' : ' . $this->php2js( $value , $key );
		}
		
		// Close and return the JS object.
		return $retVal . "\n". "}";
	}
	
}
//Include admin file if needed
if(is_admin()){
	include_once('twitter-goodies-widgets-admin.php');
}
include_once('twitter-goodies-widgets-widget.php');

//Shortcut
function twitter_goodies_widgets( $atts=array() ){
	global $TwitterGoodiesWidgets;
	if( get_class($TwitterGoodiesWidgets) == 'TwitterGoodiesWidgets' )
		echo $TwitterGoodiesWidgets->shortcode( $atts );	
}

// Start this plugin once all other plugins are fully loaded
add_action( 'widgets_init', create_function( '', 'global $TwitterGoodiesWidgets; $TwitterGoodiesWidgets = new TwitterGoodiesWidgets();' ), 10 );

?>
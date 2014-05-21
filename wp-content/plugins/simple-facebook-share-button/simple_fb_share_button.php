<?php
/*
Plugin Name: Simple Facebook Share Button
Plugin URI: http://www.ethitter.com/plugins/simple-facebook-share-button/
Description: <strong>On July 17, 2012, Facebook completely dropped support for the Share button, rendering this plugin useless. You should deactivate and delete it at your earliest convenience.</strong>
Author: Erick Hitter
Version: 2.1
Author URI: http://www.ethitter.com/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @since 2.0.4
 * @return false
 */
function SFBSB_default_options() {
	return false;
}

require( 'simple_fb_share_button_options.php' );

/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @return false
 */
function SFBSB_setup() {
	return false;
}

/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @return false
 */
function SFBSB_scripts() {
	return false;
}

/**
 * Remove plugin options on deactivation.
 *
 * @uses delete_option
 * @action register_deactivation_hook
 * @return false
 */
function SFBSB_deactivate() {
	delete_option('SFBSB');
}
register_deactivation_hook( __FILE__, 'SFBSB_deactivate' );

/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @return false
 */
function SFBSB_direct() {
	return false;
}

/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @return false
 */
function SFBSB_shortcode() {
	return;
}
add_shortcode( 'SFBSB', 'SFBSB_shortcode' );

/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @return string
 */
function SFBSB_auto( $content ) {
	return $content;
}

/**
 * Stub function to prevent fatal errors if a developer hooked into this somewhere.
 *
 * @return false
 */
function SFBSB_do() {
	return false;
}
?>
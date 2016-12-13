<?php
/**
 * @package assez_extensions
 * @version 1.0
 */
/*
Plugin Name: Assez Theme Extensions
Plugin URI: http://palthemes.com
Description: Extensions for Assez theme, For twitter widget, etc.
Author: PalThemes
Version: 1.0.0
Author URI: http://palthemes.com
*/


require_once plugin_dir_path(__FILE__) . 'widgets/storm-twitter/TwitterAPIExchange.php';
require plugin_dir_path(__FILE__) . 'widgets/assez-twitter-widget.php';
require plugin_dir_path(__FILE__) . 'widgets/assez-instagram-widget.php';

// dropcap shortcode
function assez_dropcap( $atts, $content, $tag ) {
	$output = '<span class="dropcap">' . $content . '</span>';
	return $output;
}

// spacer shortcode
function assez_spacer( $atts, $content, $tag ) {
	$spacer = shortcode_atts( array(
        'size' => '0',
    ), $atts );
	$output = '<div class="spacer" style="height:'. $spacer['size'] .'px">' . $content . '</div>';
	return $output;
}

// Run shortcode on text widget
add_filter('widget_text', 'do_shortcode');
add_shortcode('dropcap','assez_dropcap');
add_shortcode('spacer','assez_spacer');
?>
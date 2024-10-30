<?php
/**
 * @package Millioneyez
 * @version 3.4.16
 */
/*
Plugin Name: million eyez
Plugin URI: https://www.millioneyez.com/plugin/
Description: The million eyez plugin enables writers and photographers to interact in a novel way to co-produce visually rich stories. Gain new sources of imagery to enhance your content and increase engagement.
Author: million eyez
Version: 3.4.16
Author URI: http://www.millioneyez.com/

*/

$millioneyez_plugin_version = '3.4.16';

require_once 'global_settings.php';
require_once 'post_meta_box.php';
require_once 'content.php';
require_once 'utilities.php';

add_filter( 'plugin_action_links', 'millioneyez_plugin_action_links', 10, 2 );
add_action( 'init', 'millioneyez_settings_add_defaults' );

function millioneyez_settings_add_defaults() {
    global $millioneyez_plugin_version;
    $options = get_option('millioneyez_options');

    // this array has the default values for new users.
    $newOptionsArray = array(
        "hideConnect" => "true"
    );

    // this array has the general default values.
    $optionsArray = array(
        "key" => "",
        "secret" => "",
        "featured" => "true",
        "featuredType" => "responsive",
        "hideFeatured" => 0,
        "hideVia" => "false",
        "default_post_component_state" => "hidden",
        "component_size" => "full_width",
        "remove_shortcodes_from_the_excerpt" => 1,
        "hideConnect" => "false"
    );

    if (!isset($options["key"])) {
        foreach ($newOptionsArray as $key => $value) {
            $options[$key] = $value;
        }
    }

    foreach($optionsArray as $key => $value) {
        if ( !isset($options[$key]) ) {
            $options[$key] = $value;
        }
    }

    if ( $options['version'] != $millioneyez_plugin_version && $options['key'] ) {
        millioneyez_post_plugin_info_request($options['key'], $millioneyez_plugin_version);
        $options['version'] = $millioneyez_plugin_version;
    }

    update_option('millioneyez_options', $options);
}

// Display a Settings link on the main Plugins page
function millioneyez_plugin_action_links( $links, $file ) {

	if ( $file == plugin_basename( __FILE__ ) ) {
		$millioneyez_links = '<a href="'.get_admin_url().'admin.php?page=millioneyez_settings">'.__('Settings').'</a>';
		// make the 'Settings' link appear first
		array_unshift( $links, $millioneyez_links );
	}

	return $links;
}
?>

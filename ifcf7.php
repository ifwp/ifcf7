<?php
/**
 * Plugin Name: IFCF7
 * Plugin URI: https://github.com/ifwp/ifcf7
 * Description: Improvements and Fixes for Contact Form 7.
 * Version: 6.5.26
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: Luis del Cid
 * Author URI: http://luisdelcid.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ifcf7
 */

defined('ABSPATH') or die('Hi there! I\'m just a plugin, not much I can do when called directly.');
add_action('ifwp_after_setup_theme', function(){
	if(!defined('WPCF7_VERSION')){
		ifwp_tgmpa([
			[
				'name' => 'Contact Form 7',
				'slug' => 'contact-form-7',
			],
		], [
			'id' => 'ifcf7',
			'menu' => 'ifcf7-install-plugins',
			'strings' => [
				'menu_title' => 'IFCF7',
				'page_title' => __('One or more required modules are missing'),
				'parent_slug' => 'plugins.php',
			],
		]);
	}
});
add_action('ifwp_plugins_loaded', function(){
	ifwp_build_update_checker('https://github.com/ifwp/ifcf7', __FILE__, 'ifcf7');
	if(!defined('WPCF7_VERSION')){
		return;
	}
    ifwp_plugin_load('IFCF7');
});

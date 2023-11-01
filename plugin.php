<?php
/**
 * Plugin Template
 *
 * @package     WP LinkAnalyzer
 * @author      Haoua SOUALMIA
 * @copyright   2023 Haoua SOUALMIA
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: WP LinkAnalyzer
 * Version:     1.0.0
 * Description: WP LinkAnalyzer is a SEO tool for administrators. It enables you to crawl your website, analyze internal links, and create a visual sitemap to boost your site's SEO performance.
 * Author:      Haoua SOUALMIA
 */

namespace ROCKET_WP_CRAWLER;

define( 'ROCKET_CRWL_PLUGIN_FILENAME', __FILE__ ); // Filename of the plugin, including the file.

if ( ! defined( 'ABSPATH' ) ) { // If WordPress is not loaded.
	exit( 'WordPress not loaded. Can not load the plugin' );
}

// Load the dependencies installed through composer.
require_once __DIR__ . '/src/plugin.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/support/exceptions.php';

// Plugin initialization.
/**
 * Creates the plugin object on plugins_loaded hook
 *
 * @return void
 */
function wpc_crawler_plugin_init() {
	$wpc_crawler_plugin = new Rocket_Wpc_Plugin_Class();
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\wpc_crawler_plugin_init' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_activate' );
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\Rocket_Wpc_Plugin_Class::wpc_uninstall' );

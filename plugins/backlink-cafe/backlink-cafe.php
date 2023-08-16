<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://backlink.cafe
 * @since             1.0.0
 * @package           Backlink_Cafe
 *
 * @wordpress-plugin
 * Plugin Name:       Backlink Cafe
 * Plugin URI:        https://backlink.cafe
 * Description:       Earn more with your website. Set your price and accept guest posts and mentions relevant to your niche.
 * Version:           1.1.3
 * Author:            Backlink Cafe
 * Author URI:        https://backlink.cafe/authors
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       backlink-cafe
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('BACKLINK_CAFE_VERSION', '1.1.3');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-backlink-cafe-activator.php
 */
function activate_backlink_cafe()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-backlink-cafe-activator.php';
	Backlink_Cafe_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-backlink-cafe-deactivator.php
 */
function deactivate_backlink_cafe()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-backlink-cafe-deactivator.php';
	Backlink_Cafe_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_backlink_cafe');
register_deactivation_hook(__FILE__, 'deactivate_backlink_cafe');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-backlink-cafe.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_backlink_cafe()
{

	$plugin = new Backlink_Cafe();
	$plugin->run();

}
run_backlink_cafe();
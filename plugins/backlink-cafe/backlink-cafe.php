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
 * Version:           1.1.4
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
define('BACKLINK_CAFE_VERSION', '1.1.4');

add_filter('plugins_api', 'request_backlink_cafe_info', 20, 3);
add_filter('site_transient_update_plugins', 'backlink_cafe_update');

function request_backlink_cafe_info()
{
	$remote = wp_remote_get(
		'https://backlink.cafe/info.json',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			)
		)
	);

	if (is_wp_error($remote) || wp_remote_retrieve_response_code($remote) !== 200 || empty(wp_remote_retrieve_body($remote))) {
		return false;
	}

	$remote = json_decode(wp_remote_retrieve_body($remote));
	return $remote;
}

function backlink_cafe_info($res, $action, $args)
{
	if ('plugin_information' !== $action) {
		return $res;
	}

	if (plugin_basename(__DIR__) !== $args->slug) {
		return $res;
	}

	$remote = request_backlink_cafe_info();

	if (!$remote) {
		return $res;
	}

	$res = new stdClass();

	$res->name = $remote->name;
	$res->slug = $remote->slug;
	$res->version = $remote->version;
	$res->tested = $remote->tested;
	$res->requires = $remote->requires;
	$res->author = $remote->author;
	$res->author_profile = $remote->author_profile;
	$res->download_link = $remote->download_url;
	$res->trunk = $remote->download_url;
	$res->requires_php = $remote->requires_php;
	$res->last_updated = $remote->last_updated;

	$res->sections = array(
		'description' => $remote->sections->description,
		'installation' => $remote->sections->installation,
		'changelog' => $remote->sections->changelog
	);

	if (!empty($remote->banners)) {
		$res->banners = array(
			'low' => $remote->banners->low,
			'high' => $remote->banners->high
		);
	}

	return $res;
}

function backlink_cafe_update($transient)
{
	if (empty($transient->checked)) {
		return $transient;
	}

	$remote = request_backlink_cafe_info();

	if (
		$remote
		&& version_compare(BACKLINK_CAFE_VERSION, $remote->version, '<')
		&& version_compare($remote->requires, get_bloginfo('version'), '<=')
		&& version_compare($remote->requires_php, PHP_VERSION, '<')
	) {
		$res = new stdClass();
		$res->slug = plugin_basename(__DIR__);
		$res->plugin = plugin_basename(__FILE__);
		$res->new_version = $remote->version;
		$res->tested = $remote->tested;
		$res->package = $remote->download_url;

		$transient->response[$res->plugin] = $res;
	}

	return $transient;
}

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
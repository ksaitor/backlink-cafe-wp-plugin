<?php

/**
 * Fired during plugin activation
 *
 * @link       https://backlink.cafe
 * @since      1.0.0
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/includes
 * @author     Backlink Cafe <hi@backlink.cafe>
 */
class Backlink_Cafe_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-posts-service.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-backlink-cafe-admin.php';

		Backlink_Cafe_Admin::initiate_authentication('backlink-cafe');
		$response = Backlink_Cafe_Admin_Posts_Service::synchronize_blog_posts_to_server();

		if (array_key_exists('error', $response)) {
			deactivate_plugins('backlink-cafe');
			wp_die($response['error'], 'Plugin dependency check', array('back_link' => true));
		}
	}
}
<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://backlink.cafe
 * @since      1.0.0
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/includes
 * @author     Backlink Cafe <hi@backlink.cafe>
 */
class Backlink_Cafe_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'backlink-cafe',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);

	}



}
<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://backlink.cafe
 * @since      1.0.0
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 * @author     Backlink Cafe <hi@backlink.cafe>
 */
class Backlink_Cafe_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The website metadata from API
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $version    The current version of this plugin.
	 */
	private $website_info;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Backlink_Cafe_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Backlink_Cafe_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/backlink-cafe-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Backlink_Cafe_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Backlink_Cafe_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$deps_file = __DIR__ . '/js/index.asset.php';
		$dependency = [];
		$version = $this->version;

		if (file_exists($deps_file)) {
			$deps_file = require($deps_file);
			$dependency = $deps_file['dependencies'];
			$version = $deps_file['version'];
		}

		array_push($dependency, 'jquery');

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/index.js', $dependency, $this->version, false);
	}

	public function get_root_id()
	{
		return $this->plugin_name;
	}

	public function enqueue_resources()
	{
		$stripe_client_id = getenv('WORDPRESS_ENVIRONMENT') == 'development' ? 'ca_O9toC8wmOWUJrBMev3flRtrSnzv019O8' : 'ca_O9tojY86bO0l5O6hPOMulEOEiGbcr0re';

		$localize = array(
			'version' => $this->version,
			'root_id' => $this->get_root_id(),
			'plugin_name' => $this->plugin_name,
			'stripe_client_id' => $stripe_client_id,
			'access_token' => get_option($this->plugin_name . "_jwt_key")
		);

		wp_localize_script($this->plugin_name, 'wpBacklinkCafeBuild', $localize);
	}

	public function add_admin_menu()
	{
		add_menu_page(
			esc_html__('Backlink Cafe', 'backlink-cafe'),
			esc_html__('Backlink Cafe', 'backlink-cafe'),
			'manage_options',
			$this->plugin_name,
			array($this, 'add_setting_root_div')
		);
	}

	public function add_setting_root_div()
	{
		Backlink_Cafe_Admin::initiate_authentication($this->plugin_name);
		echo '<div id="' . $this->get_root_id() . '"></div>';
	}

	public static function initiate_authentication($plugin_name)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		$res = openssl_pkey_new(
			array(
				"private_key_bits" => 4096,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
				"default_md" => 'sha256',
			)
		);

		openssl_pkey_export($res, $private_key_pem);
		$details = openssl_pkey_get_details($res);
		$public_key_pem = $details['key'];
		$public_key = openssl_get_publickey($public_key_pem);
		$private_key = openssl_get_privatekey($res);

		if (!get_option($plugin_name . '_private_key')) {
			add_option($plugin_name . '_private_key', $private_key_pem);
		} else {
			update_option($plugin_name . '_private_key', $private_key_pem);
		}

		$domain = parse_url(get_site_url())['host'];
		if (parse_url(get_site_url())['port']) {
			$domain .= ':' . parse_url(get_site_url())['port'];
		}

		$response = Backlink_Cafe_Admin_Api_Service::website_auth_init(
			array(
				'domain' => $domain,
				'publicKeyPem' => $public_key_pem,
				'email' => get_option('admin_email'),
			)
		);

		if (is_array($response) && array_key_exists('error', $response)) {
			// TODO: Improve error handling;
			return;
		}
	}
}
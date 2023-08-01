<?php

/**
 * The admin-specific rest API endpoints
 *
 * @link       https://backlink.cafe
 * @since      1.0.0
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 */

/**
 * The admin-specific rest API endpoints
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 * @author     Backlink Cafe <hi@backlink.cafe>
 */

class Backlink_Cafe_Rest_Api
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

	private $api_version;

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
		$this->api_version = 'v1';
	}

	public function get_api_prefix()
	{
		return $this->plugin_name . '/' . $this->api_version;
	}

	public function load()
	{
		// Don't register the API endpoints if not admin.
		if ($this->is_admin()) {
			register_rest_route(
				$this->get_api_prefix(),
				'/get-offers',
				array(
					'methods' => 'GET',
					'callback' => array($this, 'get_offers'),
					'permission_callback' => array($this, 'is_admin'),
				)
			);

			register_rest_route(
				$this->get_api_prefix(),
				'/approve-offer',
				array(
					'methods' => 'POST',
					'callback' => array($this, 'approve_offer'),
					'permission_callback' => array($this, 'is_admin'),
				)
			);

			register_rest_route(
				$this->get_api_prefix(),
				'/reject-offer',
				array(
					'methods' => 'POST',
					'callback' => array($this, 'reject_offer'),
					'permission_callback' => array($this, 'is_admin'),
				)
			);

			register_rest_route(
				$this->get_api_prefix(),
				'/upsert-website',
				array(
					'methods' => 'POST',
					'callback' => array($this, 'update_website'),
					'permission_callback' => array($this, 'is_admin'),
				)
			);

			register_rest_route(
				$this->get_api_prefix(),
				'/get-me',
				array(
					'methods' => 'GET',
					'callback' => array($this, 'get_me'),
					'permission_callback' => array($this, 'is_admin'),
				)
			);

			register_rest_route(
				$this->get_api_prefix(),
				'/disconnect-stripe',
				array(
					'methods' => 'POST',
					'callback' => array($this, 'disconnect_stripe'),
					'permission_callback' => array($this, 'is_admin'),
				)
			);
		}


		register_rest_route(
			$this->get_api_prefix(),
			'/auth-callback',
			array(
				'methods' => 'POST',
				'callback' => array($this, 'auth_callback'),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$this->get_api_prefix(),
			'/approve-offer',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'approve_offer'),
				'permission_callback' => '__return_true'
			)
		);

		register_rest_route(
			$this->get_api_prefix(),
			'/reject-offer',
			array(
				'methods' => 'GET',
				'callback' => array($this, 'reject_offer'),
				'permission_callback' => '__return_true'
			)
		);
	}

	public function is_admin()
	{
		return in_array('administrator', wp_get_current_user()->roles);
	}

	public function get_offers(WP_Rest_Request $request)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		return Backlink_Cafe_Admin_Api_Service::get_offers(parse_url(get_site_url())['host']);
	}

	public function approve_offer(WP_Rest_Request $request)
	{
		if ($request->get_method() === 'GET') {
			$body = $request->get_query_params();
			if (!$body['securitySuffix']) {
				return array(
					'code' => 'rest_no_route',
					'message' => 'No route was found matching the URL and request method.',
					'data' => array(
						'status' => 404
					)
				);
			}
		} else {
			$body = $request->get_json_params();
		}


		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		$result = Backlink_Cafe_Admin_Api_Service::approve_offer($body);
		return $body;
		if (!array_key_exists('error', $result)) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-posts-service.php';
			Backlink_Cafe_Admin_Posts_Service::update_keyword_link_in_post(
				$result['blogPost']['cmsId'],
				$result['order']['anchor'],
				$result['order']['url'],
				0,
			);
		} else if ($request->get_method() === 'GET') {
			return array(
				'code' => 'rest_no_route',
				'message' => 'No route was found matching the URL and request method.',
				'data' => array(
					'status' => 404
				)
			);
		}

		return $result;
	}

	public function reject_offer(WP_Rest_Request $request)
	{
		if ($request->get_method() === 'GET') {
			$body = $request->get_query_params();
			if (!$body['securitySuffix']) {
				return array(
					'code' => 'rest_no_route',
					'message' => 'No route was found matching the URL and request method.',
					'data' => array(
						'status' => 404
					)
				);
			}
		} else {
			$body = $request->get_json_params();
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		$result = Backlink_Cafe_Admin_Api_Service::reject_offer($body);

		if (array_key_exists('error', $result) && $request->get_method() === 'GET') {
			return array(
				'code' => 'rest_no_route',
				'message' => 'No route was found matching the URL and request method.',
				'data' => array(
					'status' => 404
				)
			);
		}

		return $result;
	}

	public function update_website(WP_Rest_Request $request)
	{
		$body = $request->get_json_params();
		$body['id'] = get_option($this->plugin_name . '_website_id');
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		return Backlink_Cafe_Admin_Api_Service::update_website($body);
	}

	public function get_me(WP_Rest_Request $request)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		return Backlink_Cafe_Admin_Api_Service::get_me();
	}

	public function disconnect_stripe(WP_Rest_Request $request)
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/backlink-cafe-admin-api-service.php';
		return Backlink_Cafe_Admin_Api_Service::disconnect_stripe();
	}

	public function auth_callback(WP_Rest_Request $request)
	{
		$not_found_error = array(
			'code' => 'rest_no_route',
			'message' => 'No route was found matching the URL and request method.',
			'data' => array(
				'status' => 404
			)
		);
		$body = $request->get_json_params();
		$encrypted_jwt = implode(array_map('chr', $body['encryptedJWT']['data']));
		$private_key_pem = get_option($this->plugin_name . '_private_key');
		if (!$private_key_pem) {
			return $not_found_error;
		}

		$private_key = openssl_get_privatekey($private_key_pem);
		openssl_private_decrypt($encrypted_jwt, $decrypted, $private_key, OPENSSL_PKCS1_OAEP_PADDING);

		if (!$decrypted) {
			return $not_found_error;
		}

		if (!get_option($this->plugin_name . '_jwt_key')) {
			add_option($this->plugin_name . '_jwt_key', $decrypted);
		} else {
			update_option($this->plugin_name . '_jwt_key', $decrypted);
		}

		delete_option($this->plugin_name . '_private_key');

		return $not_found_error;
	}
}
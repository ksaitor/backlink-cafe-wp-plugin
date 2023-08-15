<?php

/**
 * Api service
 *
 * @link  https://backlink.cafe
 * @since 1.0.3
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 */

/**
 * Api service
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 * @author     Backlink Cafe <hi@backlink.cafe>
 */
class Backlink_Cafe_Admin_Api_Service
{
    private static function get_base_url()
    {
        return getenv('WORDPRESS_ENVIRONMENT') == 'development' ? 'http://host.docker.internal:3000' : 'https://backlink.cafe';
    }


    private static function request($method = 'GET', $path = '/', $data = null)
    {
        $http_header = array("Content-type: application/json");

        if (get_option('backlink-cafe_jwt_key')) {
            $http_header[] = "Authorization: Bearer " . get_option('backlink-cafe_jwt_key');
        }

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_HEADER => FALSE,
                CURLOPT_SSL_VERIFYPEER => FALSE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $http_header,
            )
        );

        if (is_null($data)) {
            $data = array();
        }

        if (defined('BACKLINK_CAFE_VERSION')) {
            $data['plugin_version'] = BACKLINK_CAFE_VERSION;
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_URL, self::get_base_url() . $path);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else if ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
            curl_setopt($ch, CURLOPT_URL, self::get_base_url() . $path . '?' . http_build_query($data));
        }

        $json_response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status === 404) {
            return array('error' => 'If the issue persist please upgrade to the latest version of the plugin or contact support.');
        }

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            return array('error' => $error);
        }

        curl_close($ch);

        $response = json_decode($json_response, true);
        return $response;
    }

    public static function get_offers($domain = null)
    {
        $params = array();
        if (!is_null($domain)) {
            $params['website'] = array(
                'domain' => $domain
            );
        }

        return self::request('GET', '/api/offer', $params);
    }

    public static function approve_offer($param)
    {
        return self::request('POST', '/api/offer/seller-accept', $param);
    }

    public static function reject_offer($param)
    {
        return self::request('POST', '/api/offer/seller-reject', $param);
    }

    public static function update_website($param)
    {
        return self::request(
            'POST',
            '/api/website/update',
            $param,
        );
    }

    public static function website_auth_init($param)
    {
        return self::request(
            'POST',
            '/api/website/auth/init',
            $param
        );
    }

    public static function get_me()
    {
        return self::request(
            'GET',
            '/api/website/auth/me'
        );
    }

    public static function disconnect_stripe()
    {
        return self::request(
            'POST',
            '/api/stripe/disconnect'
        );
    }

    public static function upsert_blog_post($post)
    {
        $title = $post->post_title;
        $content = $post->post_content;
        $published = $post->post_status === 'publish';
        $author = get_the_author_meta('display_name', $post->post_author);
        $published_at = $post->post_date;
        $link = get_permalink($post);
        $id = $post->ID;

        $domain = parse_url(get_site_url())['host'];
        if (parse_url(get_site_url())['port']) {
            $domain .= ':' . parse_url(get_site_url())['port'];
        }

        $payload = array(
            'url' => $link,
            'cmsId' => $id,
            'title' => $title,
            'content' => $content,
            'author' => $author,
            'published' => $published,
            'publishedAt' => $published_at,
            'website' => array(
                'connectOrCreate' => array(
                    'where' => array(
                        'domain' => $domain
                    ),
                    'create' => array(
                        'domain' => $domain
                    ),
                )
            )
        );

        return self::request('POST', '/api/blogpost/create', $payload);
    }
}
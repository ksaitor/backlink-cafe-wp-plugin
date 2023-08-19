<?php

/**
 * Posts service
 *
 * @link  https://backlink.cafe
 * @since 1.0.0
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 */

/**
 * Posts service
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 * @author     Backlink Cafe <hi@backlink.cafe>
 */
class Backlink_Cafe_Admin_Posts_Service
{
    public static function wrap_with_anchor_tag($content, $start, $end, $url)
    {
        $before = substr($content, 0, $start);
        $selected = substr($content, $start, $end - $start + 1);
        $after = substr($content, $end + 1);

        $wrappedContent = $before . '<a href="' . $url . '" rel="noopener">' . $selected . '</a>' . $after;

        return $wrappedContent;
    }

    public static function update_keyword_link_in_post($post_id, $keyword, $url, $index = 0)
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'partials/backlink-cafe-admin-keywords.php';

        $post = get_post($post_id);

        if (!$post) {
            return false; // Post not found
        }

        $original = $post->post_content;
        $final_content = $original;

        $matches = Backlink_Cafe_Admin_Keywords::fuzzy_keyword_search($final_content, $keyword);

        if (count($matches) === 0) {
            return false;
        }

        foreach ($matches as $key => $match) {
            if ($index === -1 || $key === $index) {
                $final_content = Backlink_Cafe_Admin_Posts_Service::wrap_with_anchor_tag($final_content, $match['startingIndex'], $match['endingIndex'], $url);
            }
        }

        $post->post_content = $final_content;
        wp_update_post($post);
        return true;
    }

    public static function synchronize_blog_posts_to_server()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . 'partials/backlink-cafe-admin-api-service.php';

        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'orderby' => array(
                'date' => 'DESC'
            ),
            'ignore_sticky_posts' => true,
        );

        $posts = new WP_Query($args);

        require_once plugin_dir_path(dirname(__FILE__)) . 'partials/backlink-cafe-admin-api-service.php';

        $response = array();

        foreach ($posts->posts as $post) {
            $response = Backlink_Cafe_Admin_Api_Service::upsert_blog_post($post);

            if (array_key_exists('error', $response)) {
                return $response;
            }
        }

        return $response;
    }

    public static function hook_after_post_saved($post_id, $post, $update)
    {
        if ($post->post_type !== 'post')
            return;
        require_once plugin_dir_path(dirname(__FILE__)) . 'partials/backlink-cafe-admin-api-service.php';
        Backlink_Cafe_Admin_Api_Service::upsert_blog_post($post);
    }

    public static function hook_after_post_deleted($post_id, $post)
    {
        if ($post->post_type !== 'post')
            return;
        require_once plugin_dir_path(dirname(__FILE__)) . 'partials/backlink-cafe-admin-api-service.php';
        Backlink_Cafe_Admin_Api_Service::upsert_blog_post($post);
    }
}
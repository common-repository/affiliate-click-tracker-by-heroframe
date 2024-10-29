<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//include the API class
require_once( plugin_dir_path( __FILE__ ) . '../external-api/heroframe-dashboard-api.php');

function heroframe_strip_protocol_www_from_url($url) {
    $url = str_replace('http://', '', $url);
    $url = str_replace('https://', '', $url);
    $url = str_replace('www.', '', $url);
    return $url;
}

function heroframe_check_post_updated($post_ID, $post_after, $post_before) {
    try {
        $tracked_posts = get_option('heroframe_tracked_pages');
        if (!$tracked_posts || empty($tracked_posts)) {
            return;
        }
        // check if a heroframe tracked post was updated
        if (array_key_exists($post_ID, $tracked_posts)) {
            // check if the slug (url) was updated
            if ($post_after->post_name != $post_before->post_name) {
                // update the slug in the $tracked_posts array
                $old_post_url = $tracked_posts[$post_ID];
                $new_post_url = get_permalink($post_ID);
                $tracked_posts[$post_ID] = heroframe_strip_protocol_www_from_url($new_post_url);
                update_option('heroframe_tracked_pages', $tracked_posts);
                // update in the Dashboard API
                $public_key = get_option('heroframe_public_key');
                $private_key = get_option('heroframe_private_key');
                if (!$public_key || !$private_key) {
                    return;
                }
                $dashboard_api = new HeroframeDashboardApi($public_key, $private_key);
                $response = $dashboard_api->heroframe_updateWebpageUrl($public_key, $private_key, $old_post_url, $new_post_url);
            }
        }
    } catch (Exception $e) {
    //pass handling
    }
}

add_action( 'post_updated', 'heroframe_check_post_updated', 10, 3 );

?>

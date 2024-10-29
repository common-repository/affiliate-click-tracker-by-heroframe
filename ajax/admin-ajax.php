<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . '../external-api/heroframe-dashboard-api.php');

function heroframe_wordpress_page_post_search($string){
    global $wpdb;
    $title = esc_sql($string);
    if(!$title) return;
    $sql = "
        SELECT ID, post_title
        FROM $wpdb->posts
        WHERE INSTR(post_title, %s) > 0
        AND post_type in ('page', 'post')
        AND post_status = 'publish'
        LIMIT 15
    ";
    $results = $wpdb->get_results($wpdb->prepare($sql, $title));
    return $results;
}

function heroframe_search_pages() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'heroframe_search_pages')) {
        die('Invalid nonce');
    }
    if (!isset($_POST['search'])) {
        die('Invalid search');
    }
    $search = sanitize_text_field($_POST['search']);
    $query_results = heroframe_wordpress_page_post_search($search);
    $results = array();

    foreach ($query_results as $post) {
        $results[] = array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'url' => get_permalink($post->ID)
        );
    }
    // return the results as a json object
    echo wp_json_encode($results);
    die();
}
add_action("wp_ajax_heroframe_search_pages", "heroframe_search_pages");

function heroframe_notifications() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'heroframe_notifications')) {
        die('Invalid nonce');
    }
    if (!isset($_POST['checked'])) {
        die('Invalid action');
    }
    $checked = sanitize_text_field($_POST['checked']);

   // update in the server
   $public_key = get_option('heroframe_public_key');
   $private_key = get_option('heroframe_private_key');
   if ($public_key == '' || $private_key == '') {
    return false;
   }
   $dashboard_api = new HeroframeDashboardApi($public_key, $private_key);
   $checked = $checked === 'true' ? true : false;
   $dashboard_api->heroframe_updateNotificationSettings($public_key, $private_key, $checked);
   update_option('heroframe_notifications', $checked);
   echo wp_json_encode(array('status' => 'ok', 'checked' => $checked));
   die();
}
add_action("wp_ajax_heroframe_notifications", "heroframe_notifications");

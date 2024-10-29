<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . '../admin-consts.php');

// GUI Action handler - When a user clicks the 'Stop Tracking' button
if (isset($_GET['stop_tracking'])) {
    //check nonce
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'heroframe_delete_page')) {
        heroframe_display_error_message('Invalid nonce. Please try again.');
        return;
    }
    $post_id = sanitize_text_field($_GET['stop_tracking']);
    $account_info = heroframe_get_api_account_info();
    heroframe_sync_tracked_pages($account_info);
    $tracked_pages = get_option('heroframe_tracked_pages');
    if ($tracked_pages == '') {
        $tracked_pages = array();
    }
    if (array_key_exists($post_id, $tracked_pages)) {
        // remove the page from the tracked pages list
        $post_url = $tracked_pages[$post_id];
        unset($tracked_pages[$post_id]);
        update_option('heroframe_tracked_pages', $tracked_pages);
        // deactivate it in the api
        $public_key = get_option('heroframe_public_key');
        $private_key = get_option('heroframe_private_key');
        $dashboard_api = new HeroframeDashboardApi($public_key, $private_key);
        $response = $dashboard_api->heroframe_deactivateWebpage($public_key, $private_key, $post_url);
        $response_status = $response['status'];
        $response_text = $response['response'];
        // try to parse response text to json
        $response_text = json_decode($response_text);
        if ($response_status == 200) {
            // deactivation successful
            heroframe_display_success_message('Page deactivation successful');
            return true;
        } else {
            // deactivation failed
            // add the page back to the tracked pages list
            $tracked_pages[$post_id] = $post_url;
            update_option('heroframe_tracked_pages', $tracked_pages);
            // check if text is in $response_text
            if ($response_text == null) {
                heroframe_display_error_message('Page deactivation failed. Please check your keys and try again. If the problem persists, please refer to the Help Center.');
                return false;
            } else {
                $error_message = $response_text->text;
                heroframe_display_error_message('Page deactivation failed. Please check your keys and try again. If the problem persists, please refer to the Help Center.
                <br />Error: '.$error_message);
                return false;
            }
        }
    }
}

// GUI Action handler - When a user clicks the 'Track' button
if (isset($_POST['track-submit']) && check_admin_referer('hero_track_button_clicked') && (!isset($_POST['s']) || (isset($_POST['s']) && $_POST['s'] == ''))) {
    // the track button has been pressed AND we've passed the security check
    // now we should try to track the page
    if (!isset($_POST['hero_track_save_post_id']) || $_POST['hero_track_save_post_id'] == '') {
        heroframe_display_error_message('Please search and click the page you want to track.');
        return;
    }
    $post_id = sanitize_text_field($_POST['hero_track_save_post_id']);
    heroframe_track_post($post_id);
}

// GUI Action handler - When a user clicks the 'Activate Plugin' button
 if (isset($_POST['hero_save_button']) && check_admin_referer('hero_button_clicked')) {
    // the button has been pressed AND we've passed the security check
    // now we should try to activate the plugin
    $public_key = sanitize_text_field($_POST['heroframe_public_key']);
    $private_key = sanitize_text_field($_POST['heroframe_private_key']);

    // check if the keys are valid
    if (!heroframe_verifyKeyString($public_key) || !heroframe_verifyKeyString($private_key)) {
        heroframe_display_error_message('Invalid API keys. Please check your keys and try again.');
        return;
    }

    $result = heroframe_api_activate_plugin($public_key, $private_key);
    if ($result == true) {
        // the activation failed
        // the activation was successful
        heroframe_display_success_message('Plugin activation successful');
        // save the api keys
        heroframe_save_option_values($heroframe_keys_form_map);
    }
}

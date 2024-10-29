<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Uses storages and API method
function heroframe_sync_tracked_pages($account_info) {
    $from_storage = get_option('heroframe_tracked_pages');

    if ($from_storage === false) {
        $from_storage = array();
    }
    $from_api = $account_info['tracked_urls'];
    if ($from_api === null) {
        $from_api = array();
    }
    //iterate through keys of $from_storage
    foreach ($from_storage as $page_id => $page_url) {
        // if the $page_url is not in $from_api, remove it from $from_storage
        if (!in_array($page_url, $from_api)) {
            unset($from_storage[$page_id]);
        }
    }
    //iterate through keys of $from_api
    $current_protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://");
    foreach ($from_api as $page_url) {
        // if the $page_url is not in $from_storage, add it to $from_storage
        if (!in_array($page_url, $from_storage)) {
            $page_id = url_to_postid($current_protocol . $page_url);
            $from_storage[$page_id] = $page_url;
        }
    }
    //update the option with the new $from_storage
    update_option('heroframe_tracked_pages', $from_storage);
}


// Uses API method
function heroframe_activate_new_website($public_key, $private_key, $url) {
    //perform api request
    $dashboard_api = new HeroframeDashboardApi($public_key, $private_key);
    $response = $dashboard_api->heroframe_activateWebsite($public_key, $private_key, $url);
    $response_status = $response['status'];
    $response_text = $response['response'];
    // try to parse response text to json
    $response_text = json_decode($response_text);
    if ($response_status == 200) {
        // activation successful
        update_option('heroframe_activation_status', '<span class="heroframeactive">Active</span>');
        return true;
    } else {
        //remove the validation scripts from the home page by deleting the options
        //update_option('heroframe_public_key', '');

        // activation failed
        // check if text is in $response_text
        if ($response_text == null) {
            heroframe_display_error_message('Plugin activation failed. Please check your keys and try again. If the problem persists, please refer to the Help Center.');
            return false;
        } else {
            $error_message = $response_text->text;
            heroframe_display_error_message('Plugin activation failed. Please check your keys and try again. If the problem persists, please refer to the Help Center.
            <br />Error: '.$error_message);
            return false;
        }
        }
}

// Uses API Method
function heroframe_query_api_account($public_key, $private_key) {
    $dashboard_api = new HeroframeDashboardApi($public_key, $private_key);
    $response = $dashboard_api->heroframe_getAccountInfo($public_key, $private_key);
    $response_status = $response['status'];
    $response_text = $response['response'];
    // try to parse response text to json
    $response_text = json_decode($response_text);
    if ($response_status == 200) {
        // user info worked.
        return $response_text->data;
    } else {
        // activation failed
        // check if text is in $response_text
        if ($response_text == null) {
            $error_message = 'Error logging in to Heroframe. Please try again later. If the problem persists, please refer to the Help Center.';
            return false;
            heroframe_display_error_message($error_message);
        } else {
            $error_message = $response_text->text;
            $error_message = 'Error logging in to Heroframe. Please try again later. If the problem persists, please refer to the Help Center.
            <br />Error: '.$error_message;
            heroframe_display_error_message($error_message);
            return false;
        }
    }
}

function heroframe_are_api_servers_up() {
    $dashboard_api = new HeroframeDashboardApi('', '');
    $response = $dashboard_api->heroframe_health_check();
    $response_status = $response['status'];
    $response_text = $response['response'];
    if ($response_status == 200) {
        return true;
    } else {
        return false;
    }
}

// Uses API Method
function heroframe_get_api_account_info() {
    $public_key = get_option('heroframe_public_key');
    $private_key = get_option('heroframe_private_key');
    if ($public_key == '' || $private_key == '') {
        return false;
    }
    if (!heroframe_verifyKeyString($public_key) || !heroframe_verifyKeyString($private_key)) {
        return false;
    }
    $info = heroframe_query_api_account($public_key, $private_key);
    if ($info == false) {
        return false;
    }
    $websites = $info->websites;
    //convert websites object to array
    $websites = json_decode(json_encode($websites), true);
    $current_website = get_site_url();
    $current_website_name = heroframe_get_website_name_from_url($current_website);
    if (!array_key_exists($current_website_name, $websites)) {
        return false;
    }
    $current_website_info = $websites[$current_website_name];
    $quota = $info->remaining_quota;
    $is_notifications_enabled = $info->notification_settings;
    update_option('heroframe_notifications', $is_notifications_enabled);
    return array('tracked_urls' => $current_website_info, 'quota' => $quota);
}

// Uses API Method
function heroframe_api_activate_plugin($public_key, $private_key) {
    // Install the validation scripts in the home page, by saving it in the options
    update_option('heroframe_public_key', $public_key);
    //update the private_key
    update_option('heroframe_private_key', $private_key);
    // get the current website url
    $current_website_url = get_site_url();
    // now check if the url is already activated by getting the user info
    $info = heroframe_query_api_account($public_key, $private_key);
    if ($info == false) {
        update_option('heroframe_activation_status', '<span class="heroframeinactive">Inactive</span>');
        return false;
    }
    $websites = $info->websites;
    //convert websites object to array
    $websites = json_decode(json_encode($websites), true);
    //iterate the keys of the 'websites' array
    foreach ($websites as $website_name => $value) {
        $current_website_name = heroframe_get_website_name_from_url($current_website_url);
        if ($current_website_name == $website_name) {
            // the website is already activated
            update_option('heroframe_activation_status', '<span class="heroframeactive">Active</span>');
            return true;
        }
    }
    // if it's not, activate it
    return heroframe_activate_new_website($public_key, $private_key, $current_website_url);
}

// Uses API Method
function heroframe_track_post($post_id) {
    $post_url = get_permalink($post_id);
    $tracked_pages = get_option('heroframe_tracked_pages');
    if ($tracked_pages == '') {
        $tracked_pages = array();
    }
    // get quota
    $info = heroframe_get_api_account_info();
    if ($info == false) {
        heroframe_display_error_message('Error tracking page. Please check your keys and try again.');
        return;
    }
    $quota = $info['quota'];
    $tracked_urls = $info['tracked_urls'];
    if ($quota == 0) {
        heroframe_display_error_message('You have reached your quota of tracked pages. Please <a target="_blank" href="https://app.heroframe.ai/dashboard/billing">top up</a> your account to track more pages.');
        return;
    }
    if (array_key_exists($post_id, $tracked_pages)) {
        heroframe_display_error_message('This page is already being tracked.');
        return;
    }
    // the page can be tracked.
    //add it to the tracked pages list
    $tracked_pages[$post_id] = $post_url;
    update_option('heroframe_tracked_pages', $tracked_pages);
    // activate it in the api
    $public_key = get_option('heroframe_public_key');
    $private_key = get_option('heroframe_private_key');
    $dashboard_api = new HeroframeDashboardApi($public_key, $private_key);
    $response = $dashboard_api->heroframe_activateWebpage($public_key, $private_key, $post_url);
    $response_status = $response['status'];
    $response_text = $response['response'];
    // try to parse response text to json
    $response_text = json_decode($response_text);
    if ($response_status == 200) {
        // activation successful
        heroframe_display_success_message('Page tracking successful');
        return true;
    } else {
        // activation failed
        // remove the page from the tracked pages list
        unset($tracked_pages[$post_id]);
        update_option('heroframe_tracked_pages', $tracked_pages);
        // check if text is in $response_text
        if ($response_text == null) {
            heroframe_display_error_message('Page tracking failed. Please check your keys and try again. If the problem persists, please refer to the Help Center.');
            return false;
        } else {
            $error_message = $response_text->text;
            heroframe_display_error_message('Page tracking failed. Please check your keys and try again. If the problem persists, please refer to the Help Center.
            <br />Error: '.$error_message);
            return false;
        }
    }
}

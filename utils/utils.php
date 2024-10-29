<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . '../admin/admin-consts.php' );

// Util function
function heroframe_get_website_name_from_url($url) {
    // Strip 'www.' and protocol from URL
    $url = strtolower(str_replace("www.", "", $url));
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://" . $url;
    }
    // Get just the domain name (including subdomain)
    $hostname = parse_url($url, PHP_URL_HOST);
    if ($hostname === false) {
        throw new Exception("Could not get hostname from URL");
    }
    return urlencode($hostname);
}

// Util function
function heroframe_display_success_message($message)
{
    echo wp_kses_post('<div class="updated"><p>'.$message.'</p></div>');
}

// Util function
function heroframe_display_error_message($message)
{
    echo wp_kses_post('<div class="error"><p>'.$message.'</p></div>');
}

// Storage Util function
function heroframe_save_option_values($heroframe_keys_map)
{
    //verify hero_button_clicked nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'hero_button_clicked')) {
        heroframe_display_error_message('Error: Nonce Security check');
        return;
    }
    //set all the values
    foreach ($heroframe_keys_map as $key => $value) {
        $val = santize_text_field($_POST[$value]);
        heroframe_set_option_value($heroframe_keys_map, $key, $val);
    }
}

// Storage Util function
function heroframe_get_option_value($heroframe_keys_map, $heroframe_defaults, $key)
{
    $value = get_option($heroframe_keys_map[$key]);
    if ($value == '') {
        $value = $heroframe_defaults[$key];
    }
    return wp_kses_post($value);
}

// Storage Util function
function heroframe_set_option_value($heroframe_keys_map, $key, $value)
{
    update_option($heroframe_keys_map[$key], sanitize_option($value));
}

function heroframe_verifyKeyString($string) {
    // Check if the string is exactly 32 characters long
    if (strlen($string) !== 32) {
        return false;
    }

    // Check if the string contains only alphanumeric characters
    if (!ctype_alnum($string)) {
        return false;
    }

    // If both conditions pass, return true
    return true;
}
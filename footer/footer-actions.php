<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . '../utils/utils.php' );

function heroframe_enqueue_publisher_id_script($key) {
	wp_enqueue_script('heroframe_publisher_id_script', plugins_url('heroframe_publisher_id.js', __FILE__), array(), '1.0', true);
	$script  = "window.heroframe_publisher_id = '" . esc_js($key) ."'; ";
	wp_add_inline_script('heroframe_publisher_id_script', $script, 'after');
}

function heroframe_footer_validation_script() {
    $key = get_option('heroframe_public_key');
    if ($key == '') {
        return;
    }
    if (!heroframe_verifyKeyString($key)) {
        return;
    }
  heroframe_enqueue_publisher_id_script($key);
}

add_action('wp_enqueue_scripts', 'heroframe_footer_validation_script');

function heroframe_add_footer_tracking_script() {
    $tracked_urls = get_option('heroframe_tracked_pages');

    if ($tracked_urls === false || count($tracked_urls) == 0) {
        return;
    }
    $post_id = get_the_ID();
    if (array_key_exists($post_id, $tracked_urls)) {
        // add heroframe tracking only if the page is tracked in the storage
        //generate utc timestamp
        $time = time();
        $src_url = $tracked_urls[$post_id];
        $query_string = 't='.$time;
        $query_string .= '&source='.urlencode(esc_url($src_url));
        $tracker_script_src = 'https://include.heroframe.ai/core/tracker.js?' . esc_attr($query_string);
        wp_enqueue_script('heroframe_tracking_script', $tracker_script_src, array(), '1.0', array(
            'strategy' => 'defer',
            'in_footer' => true
        ));
    }
}

add_action('wp_enqueue_scripts', 'heroframe_add_footer_tracking_script', 1000);

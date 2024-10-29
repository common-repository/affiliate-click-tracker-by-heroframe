<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class HeroframeDashboardAPI {
    private $base_url = "https://app.heroframe.ai/dashboard/api/";

    public function heroframe_health_check() {
        return $this->heroframe_make_request('health_check', array(), 'GET');
    }

    private function heroframe_make_request($endpoint, $data, $method = 'POST') {
        // Construct the URL by appending the endpoint to the base URL
        $url = $this->base_url . $endpoint;

        // Prepare the arguments for the POST request
        $args = array(
            'method'  => $method,  // The HTTP method to use
            'body'    => $data,  // Data to be sent in the POST request
            'timeout' => 30,     // Timeout for the request
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',  // Default for form data
            ),
        );

        if ($method === 'GET') {
            // If the method is GET, append the data to the URL
            $url = add_query_arg($data, $url);
            $response = wp_remote_get($url);
        } else {
            $response = wp_remote_post($url, $args);
        }

        // Check if the request was successful
        if (is_wp_error($response)) {
            // If there's an error, return the error message
            return array('status' => 0, 'error' => $response->get_error_message());
        }

        // Get the HTTP status code from the response
        $http_code = wp_remote_retrieve_response_code($response);

        // Get the body of the response
        $response_body = wp_remote_retrieve_body($response);

        // Return the status code and response body
        return array('status' => $http_code, 'response' => $response_body);
    }

    public function heroframe_activateWebsite($publisher_id, $publisher_secret, $url) {
        $data = array(
            'publisher_id' => $publisher_id,
            'publisher_secret' => $publisher_secret,
            'url' => $url
        );
        return $this->heroframe_make_request('activate-website', $data);
    }

    public function heroframe_activateWebpage($publisher_id, $publisher_secret, $url) {
        $data = array(
            'publisher_id' => $publisher_id,
            'publisher_secret' => $publisher_secret,
            'url' => $url
        );
        return $this->heroframe_make_request('activate-webpage', $data);
    }

    public function heroframe_deactivateWebpage($publisher_id, $publisher_secret, $url) {
        $data = array(
            'publisher_id' => $publisher_id,
            'publisher_secret' => $publisher_secret,
            'url' => $url
        );
        return $this->heroframe_make_request('deactivate-webpage', $data);
    }

    public function heroframe_getAccountInfo($publisher_id, $publisher_secret) {
        $data = array(
            'publisher_id' => $publisher_id,
            'publisher_secret' => $publisher_secret
        );
        return $this->heroframe_make_request('get-account-info', $data);
    }

    public function heroframe_updateWebpageUrl($publisher_id, $publisher_secret, $old_url, $new_url) {
        $data = array(
            'publisher_id' => $publisher_id,
            'publisher_secret' => $publisher_secret,
            'old_url' => $old_url,
            'new_url' => $new_url
        );
        return $this->heroframe_make_request('update-webpage-url', $data);
    }

    public function heroframe_updateNotificationSettings($publisher_id, $publisher_secret, $is_enabled) {
        $data = array(
            'publisher_id' => $publisher_id,
            'publisher_secret' => $publisher_secret,
            'is_enabled' => $is_enabled
        );
        return $this->heroframe_make_request('update-notification-settings', $data);
    }
}
?>

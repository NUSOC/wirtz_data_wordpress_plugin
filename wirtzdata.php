<?php

/**
 * Plugin Name: Wirtz Data
 * Description: A plugin to retrieve data from a CSV data using a shortcode.
 * Version: 1.0
 * Author: SOC External Affairs
 */


// Include the Composer autoload file
require_once __DIR__ . '/vendor/autoload.php';






/**
 * Shortcode to display the main search interface form 
 * a the CSV data. 
 *
 * @return string The output of the shortcode
 */
add_shortcode('wirtzdata', function () {

    // Only run on single pages/posts, not in loops or indexes
    if (!is_singular()) {
        return '';
    }

    // Check if user is currently logged into WordPress
    // If not authenticated, generate HTML with login URL and JavaScript redirect
    // This ensures only logged-in users can access the data
    if (!is_user_logged_in()) {
        // Get WordPress login URL using wp_login_url() function
        $login_url = wp_login_url();
        return <<<HTML
            You are being forward to the login page: <br> <a href="$login_url">$login_url</a>
            <br>
            <script>
                setTimeout(function() {
                    window.location.href = "$login_url";
                }, 100);
            </script>
        HTML;
    }

    // This is the primary hook to include anything in the /src folder
    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow();
    return $wirtzShow->startpoint(); // Ensure the output is returned
});




/**
 * Sýnir lista af leikritum eftir ári
 */
add_shortcode('wirtzdata_listplays', function () {

    // Only run on single pages/posts, not in loops or indexes
    if (!is_singular()) {
        return '';
    }


    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow();
    return $wirtzShow->listPlaysByYear();
});



/**
 * Test shortcode to display CSV headers
 * 
 * Creates a test shortcode that initializes the WirtzData model
 * and dumps the CSV headers. Used for debugging purposes.
 *
 * @return string Empty string after dumping headers
 */
add_shortcode('wirtzdata_test', function () {



    // Include the bootstrap file
    // $wirtzData = new StackWirtz\WordpressPlugin\Models\WirtzData();
    // dump($wirtzData->getHeaders());

    return "";
});


/**
 * Shortcode to display the NLP interface
 * 
 * Creates a shortcode that initializes the NLP class
 * and displays the NLP interface.
 *
 * @return string The rendered NLP interface
 */
add_shortcode('wirtzdata_NLP', function () {
    $nlp = new StackWirtz\WordpressPlugin\NLP();
    return $nlp->displayNLP();
});






/**
 * Display admin notice with search page link and auto-redirect
 * 
 * Shows a notice to subscriber users with a link to the Wirtz Data search page.
 * Also automatically redirects them to that page after a short delay.
 * The notice includes:
 * - Large font size search icon and link text
 * - Dismissible admin notice styling
 * - JavaScript redirect after 100ms with cache-busting
 */
add_action('admin_notices', function () {
    $user = wp_get_current_user();

    if ($user && in_array('subscriber', $user->roles)) {
        $post_id = get_option('wirtz_data_stright_to_search_after_login_location');


        if ($post_id > 0) {
            $url = get_permalink($post_id);
            printf(
                <<<HTML
                        <style>
                            
                        </style>
                        <div class="notice notice-info is-dismissible" style="font-size: 32px; float: left;" >
                          
                                🔎 You can access the Wirtz Data search page here: <a href="%s">%s</a>
                        </div>
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                setTimeout(function(){
                                    window.location.replace("%s?random=" + Date.now());
                                }, 100);
                            });
                        </script>
                        
                    HTML,
                esc_url($url),
                esc_url($url),
                esc_url($url)
            );
        }
    }
});



/**
 * Register activation hook to create the log table
 */
register_activation_hook(__FILE__, 'wirtzdata_create_log_table');

/**
 * Include the plugin settings file
 * 
 * Loads the wirtzdata-settings.php file which contains plugin configuration
 * and settings functionality.
 */
require_once 'wirtzdata-settings.php';

/**
 * Include the logging functionality
 */
require_once 'wirtzdata-log.php';



/**
 * Include VPN detection JavaScript functionality
 * 
 * Loads the wirtzdata-javascript-vpn.php file which contains code to detect
 * if users are accessing the site through a VPN connection. This helps ensure
 * proper access control and security measures.
 */
include_once 'wirtzdata-javascript-vpn.php';

/**
 * Handle direct path /data-deep-dive
 */
add_action('init', function() {
    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/data-deep-dive') {
        $deepDive = new StackWirtz\WordpressPlugin\WirtzDataDeepDive();
        echo $deepDive->deepDiveView();
        exit;
    }
});

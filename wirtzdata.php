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
 * Bootstrap function for the Wirtz Data plugin
 * 
 * Checks if the current page is appropriate for running the plugin functionality.
 * Returns early with an empty string if on front page, home page, archive page,
 * or in admin area.
 *
 * @return string Empty string if conditions are not met for plugin execution
 */
function writzdata_bootstrap()
{
    // If on the front page or home page, return an empty string
    if (is_front_page() || is_home() || is_archive()) {
        return '';
    }

    // only run if not in admin
    if (is_admin()) {
        return '';
    }
}



/**
 * Shortcode to display the main search interface form 
 * a the CSV data. 
 *
 * @return string The output of the shortcode
 */
add_shortcode('wirtzdata', function () {

    // Logic to check if the user is logged in. If not logged in, 
    // redirect to the login page.
    if (!is_user_logged_in()) {
        // Redirect to wp-admin login page
        $login_url = wp_login_url();

        return 'You must be logged in and have permission to use this utility: <br> <a href="' . esc_url($login_url) . '">Click here to log in</a>';
    }

    // Include the bootstrap file
    writzdata_bootstrap();

    // This is the primary hook to include anything in the /src folder
    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow();
    return $wirtzShow->startpoint(); // Ensure the output is returned
});




/**
 * Shortcode to display the list of plays by year
 */
add_shortcode('wirtzdata_listplays', function () {

    // Include the bootstrap file
    writzdata_bootstrap();
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
    writzdata_bootstrap();
    $wirtzData = new StackWirtz\WordpressPlugin\Models\WirtzData();

    dump($wirtzData->getHeaders());

    return "";
});





/**
 * Display admin notice for subscriber role users only
 * 
 * Shows a notification message in the WordPress admin area for users with the 'subscriber' role.
 * The notice contains a link to the Wirtz Data search page and automatically redirects to that page.
 *
 * Process:
 * 1. Gets the current logged in user
 * 2. Checks if user has subscriber role
 * 3. Gets the configured redirect URL from WordPress options
 * 4. Extracts post/page ID from the stored option value
 * 5. Generates permalink URL for the target page
 * 6. Outputs HTML notice with:
 *    - Dismissible info-style admin notice div
 *    - Link to search page
 *    - JavaScript that auto-redirects after 50ms delay
 * 
 * Pro tip: Using printf() with HEREDOC syntax for HTML is way better than 
 * writing separate echo statements like some kind of caveman. Your 
 * coworkers will thank you. Your code reviewer will weep tears of joy.
 * 
 * @uses wp_get_current_user() Get current user object
 * @uses get_option() Get redirect URL from WP options
 * @uses get_permalink() Get full URL for post/page
 * @uses esc_url() Escape URL for safe output
 */
add_action('admin_notices', function () {
    
    
    $user = wp_get_current_user();



   
        wp_set_auth_cookie($user_id, true);
        wp_set_current_user($user_id);
   

    if ($user && in_array('subscriber', $user->roles)) {
        $redirect_url = get_option('wirtz_data_stright_to_search_after_login_location');
        if ($redirect_url) {
            // Extract the ID from the Page_X or Post_X format
            $parts = explode('_', $redirect_url);
            $post_id = isset($parts[1]) ? intval($parts[1]) : 0;

            if ($post_id > 0) {
                $url = get_permalink($post_id);
                printf(<<<HTML
                        <style>
                            
                        </style>
                        <div class="notice notice-info is-dismissible">
                            <p style="font-size: 32px; float: left;" >
                                🔎 You can access the Wirtz Data search page here: <a href="%s">%s</a>
                            </p>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    setTimeout(function(){
                                        window.location.replace("%s?random=" + Date.now());
                                    }, 1000);
                                });
                            </script>
                        </div>
                    HTML,
                    esc_url($url),
                    esc_url($url),
                    esc_url($url)
                );
            }
        }
    }
});



/**
 * Include the plugin settings file
 * 
 * Loads the wirtzdata-settings.php file which contains plugin configuration
 * and settings functionality.
 */
require_once 'wirtzdata-settings.php';

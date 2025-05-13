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
 * Filter to redirect subscribers to a specific page after login
 * 
 * Checks if the logged in user is a subscriber and redirects them to a configured 
 * page or post after login. The redirect location is set in the plugin settings
 * using the 'wirtz_data_stright_to_search_after_login_location' option.
 * The option value should be in the format 'page_123' or 'post_123' where 123 is the post/page ID.
 *
 * @filter login_redirect
 * @param string $redirect_to The redirect destination URL
 * @param string $request The requested redirect URL 
 * @param WP_User|WP_Error $user WP_User object if login was successful, WP_Error if not
 * @return string Modified redirect URL if conditions are met, original URL otherwise
 * @since 1.0.0
 */
add_filter('login_redirect', function($redirect_to, $request, $user) {

    // Return early if redirect option is disabled
    if (!get_option('wirtz_data_stright_to_search_after_login', false)) {
        return $redirect_to;
    }

    $option_value = get_option('wirtz_data_stright_to_search_after_login_location');
    
    // Return early if option is empty
    if (empty($option_value)) {
        return $redirect_to;
    }

    if (isset($user->roles) && is_array($user->roles) && in_array('subscriber', $user->roles)) {
        if (preg_match('/^(page|post)_(\d+)$/', $option_value, $matches)) {
            $post_type = $matches[1]; // 'page' or 'post'
            $post_id = (int) $matches[2];

            // Optional: verify post type matches the ID
            $post = get_post($post_id);
            if ($post && $post->post_type === $post_type) {
                $url = get_permalink($post_id);
                if ($url) {
                    return $url;
                }
            }
        }
    }
    return $redirect_to;
}, 10, 3);









require_once 'wirtzdata-settings.php';
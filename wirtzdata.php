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
 * Include the plugin settings file
 * 
 * Loads the wirtzdata-settings.php file which contains plugin configuration
 * and settings functionality.
 */
require_once 'wirtzdata-settings.php';

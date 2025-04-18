<?php

/**
 * Plugin Name: Wirtz Data
 * Description: A plugin to retrieve data from a CSV data using a shortcode.
 * Version: 1.0
 * Author: Your Name
 */

    // Include the Composer autoload file
    require_once __DIR__ . '/vendor/autoload.php';

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



// Register the shortcode
add_shortcode('wirtzdata', function () {

    // Include the bootstrap file
    writzdata_bootstrap();

    // This is the primary hook to include anything in the /src folder
    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow();
    return $wirtzShow->startpoint(); // Ensure the output is returned
});


add_shortcode('wirtzdata_listplays', function () {

    // Include the bootstrap file
    writzdata_bootstrap();
    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow();
    return $wirtzShow->listPlaysByYear();
});




require_once 'wirtzdata-settings.php';

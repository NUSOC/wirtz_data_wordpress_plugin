<?php

/**
 * Plugin Name: Wirtz Data
 * Description: A plugin to retrieve data from a CSV data using a shortcode.
 * Version: 1.0
 * Author: Your Name
 */






// Register the shortcode
add_shortcode('wirtzdata', function () {
    
    // If on the front page or home page, return an empty string
    if (is_front_page() || is_home()) {
        return '';
    }

    // only run if not in admin
    if (is_admin()) {
        return '';
    }



    // Include the Composer autoload file
    require_once __DIR__ . '/vendor/autoload.php';

 




    // This is the primary hook to include anything in the /src folder
    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow();

    

    return $wirtzShow->startpoint(); // Ensure the output is returned
});




require_once 'wirtzdata-settings.php';
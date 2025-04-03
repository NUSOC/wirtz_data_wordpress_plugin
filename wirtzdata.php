<?php

/**
 * Plugin Name: Wirtz Data
 * Description: A plugin to retrieve data from a SQLite database using a shortcode.
 * Version: 1.0
 * Author: Your Name
 */






// Register the shortcode
add_shortcode('wirtzdata', function () {
    if (is_admin()) {
        return '';
    }

    // Include the Composer autoload file
    require_once __DIR__ . '/vendor/autoload.php';

    // Load the .env file
    if (file_exists(__DIR__ . '/.env')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
    }

    // Store environment variables in a global variable
    global $wirtz_env;
    $wirtz_env = $_ENV;


    // This is the primary hook to include anything in the /src folder
    $wirtzShow = new StackWirtz\WordpressPlugin\WirtzShow($wirtz_env);

    

    return $wirtzShow->startpoint(); // Ensure the output is returned
});

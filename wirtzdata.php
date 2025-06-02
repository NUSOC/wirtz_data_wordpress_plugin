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

    if ($user && in_array('subscriber', $user->roles)) {
        $redirect_url = get_option('wirtz_data_stright_to_search_after_login_location');
        if ($redirect_url) {
            // Extract the ID from the Page_X or Post_X format
            $parts = explode('_', $redirect_url);
            $post_id = isset($parts[1]) ? intval($parts[1]) : 0;

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
 * Initialize URL rewrite functionality for Wirtz search
 * 
 * Sets up URL rewriting to handle the /wirtz/search/forwarded/page endpoint and redirect to the
 * appropriate search page. This creates a clean URL structure for accessing the
 * Wirtz data search functionality. This page is a post or page where the short code
 * [wirtzdata] is found. It goes to the first one.
 *
 * Process:
 * 1. Adds rewrite rule to handle /wirtz/search/forwarded/page URL
 * 2. Registers wirtz_search_redirect query var
 * 3. Handles redirect by:
 *    - Looking for page with [wirtzdata] shortcode
 *    - Redirecting to that page if found
 *    - Falling back to home URL if not found
 *
 * @uses add_rewrite_rule() Add WordPress rewrite rule
 * @uses add_filter() Add filter for query vars
 * @uses get_query_var() Get query variable value
 * @uses get_posts() Get pages containing shortcode
 * @uses wp_redirect() Redirect to appropriate URL
 */

// Register rewrite rules during WordPress init
function wirtzdata_register_rewrites() {
    // Add rewrite rule
    add_rewrite_rule('^wirtz/search/forwarded/page/?$', 'index.php?wirtz_search_redirect=1', 'top');
}
add_action('init', 'wirtzdata_register_rewrites');

// Register query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'wirtz_search_redirect';
    return $vars;
});

// Handle redirect
add_action('template_redirect', function () {
    if (get_query_var('wirtz_search_redirect')) {
        $pages = get_posts([
            'post_type'      => array('page', 'post'),
            'post_status'    => 'publish',
            's'              => '[wirtzdata]',
            'posts_per_page' => 1,
        ]);

        if (!empty($pages)) {
            $redirect_url = get_permalink($pages[0]->ID);

            // Get search parameters from query string
            $params = array();
            $search_params = array('first', 'last', 'production', 'team', 'role', 'career', 'grad');
            foreach ($search_params as $param) {
                if (isset($_GET[$param])) {
                    $params[$param] = sanitize_text_field($_GET[$param]);
                }
            }

            // Add parameters to redirect URL if they exist
            if (!empty($params)) {
                $redirect_url = add_query_arg($params, $redirect_url);
            }

            wp_redirect($redirect_url);
        } else {
            wp_redirect(home_url()); // fallback
        }
        exit;
    }
});

// Flush rewrite rules on plugin activation
function wirtzdata_flush_rules() {
    wirtzdata_register_rewrites();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wirtzdata_flush_rules');
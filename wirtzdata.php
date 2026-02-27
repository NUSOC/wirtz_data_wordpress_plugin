<?php

/**
 * Plugin Name: Wirtz Data
 * Description: A plugin to retrieve data from a CSV data using a shortcode.
 * Version: 1.0
 * Author: SOC External Affairs
 */

// Include the Composer autoload file
require_once __DIR__ . '/vendor/autoload.php';

use StackWirtz\WordpressPlugin\WirtzShow;
use StackWirtz\WordpressPlugin\WirtzDataDeepDive;
use StackWirtz\WordpressPlugin\NLP;

// --- Global Functions & Setup ---

/**
 * Register activation hook to ensure the log table is created upon plugin activation.
 * This calls the function defined in wirtzdata-log.php.
 */
register_activation_hook(__FILE__, 'wirtzdata_create_log_table');

// Load other necessary files that are not namespaced or rely on global functions
require_once 'wirtzdata-settings.php';
require_once 'wirtzdata-log.php';
require_once 'wirtzdata-javascript-vpn.php';


/**
 * Main loader class to bootstrap plugin hooks and instantiate handlers.
 */
class WirtzLoader
{
    private $wirtzShow;
    private $nlp;
    private $deepDive;

    public function __construct()
    {
        // Instantiate handlers. WirtzShow constructor handles its own dependencies and checks.
        $this->wirtzShow = new WirtzShow();
        $this->nlp = new NLP();
        $this->deepDive = new WirtzDataDeepDive();
        
        $this->registerHooks();
    }

    public function registerHooks()
    {
        // Shortcodes
        add_shortcode('wirtzdata', [$this->wirtzShow, 'startpoint']);
        add_shortcode('wirtzdata_listplays', [$this->wirtzShow, 'listPlaysByYear']);
        add_shortcode('wirtzdata_test', [$this, 'handleTestShortcode']);
        add_shortcode('wirtzdata_NLP', [$this->nlp, 'displayNLP']);
        add_shortcode('wirtzdata_productions_by_year', [$this->wirtzShow, 'renderProductionByYearChart']);
        
        // Admin Notices / Redirects
        add_action('admin_notices', [$this, 'displaySubscriberNotice']);
        
        // Custom Routing Setup
        add_action('init', [$this, 'setupDeepDiveRoute']);
    }

    public function handleTestShortcode()
    {
        return "";
    }
    
    /**
     * Handles automatic redirection for subscribers on the admin dashboard.
     */
    public function displaySubscriberNotice()
    {
        $user = wp_get_current_user();

        if ($user && in_array('subscriber', $user->roles)) {
            $post_id = get_option('wirtz_data_stright_to_search_after_login_location');

            if ($post_id > 0) {
                $url = get_permalink($post_id);
                
                // Output minimal HTML/JS notice that redirects the user.
                printf(
                    <<<HTML
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

    /**
     * Handles the /data-deep-dive request by manually simulating WP query context.
     */
    public function setupDeepDiveRoute()
    {
        // Check the path directly on 'init' to trigger content load if it matches
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/data-deep-dive') {
            add_action('wp_loaded', function() { // Using wp_loaded like original for context setup
                global $post, $wp_query;
                
                // Manually emulate WP query context for the deep dive page
                $post = (object) [
                    'ID' => 0,
                    'post_name' => 'data-deep-dive',
                    'post_title' => 'Deep Dive Data View',
                    'post_content' => '',
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'ancestors' => []
                ];
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->post = $post;
                
                $deepDive = new WirtzDataDeepDive();
                $content = $deepDive->deepDiveView();
                
                // Output content and exit, mimicking original structure
                get_header();
                echo $content;
                get_footer();
                exit;
            });
        }
    }
}

// Initialize the loader on plugins_loaded to ensure all classes are available
add_action('plugins_loaded', function() {
    new WirtzLoader();
});

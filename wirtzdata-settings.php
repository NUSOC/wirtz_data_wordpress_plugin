<?php

use StackWirtz\WordpressPlugin\Handlers\SettingsFormHandler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Register the settings page
function wirtz_data_settings_page()
{
    add_menu_page(
        'Wirtz Data Settings',
        'Wirtz Data Settings',
        'manage_options',
        'wirtz-data-settings',
        'wirtz_data_settings_html'
    );
}
add_action('admin_menu', 'wirtz_data_settings_page');

// Display the settings page content
function wirtz_data_settings_html()
{
    // Handle file deletion
    if (isset($_POST['delete_csv_file']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'delete_csv_file') && current_user_can('manage_options')) {
        SettingsFormHandler::handleCsvFileDeletion();
    }
    
    // Set up Twig
    $loader = new FilesystemLoader(__DIR__ . '/src/templates');
    $twig = new Environment($loader);
    
    // Prepare data for template
    $logs = wirtzdata_get_logs();
    $log_headers = [];
    $log_data = [];
    
    if (!empty($logs)) {
        $log_headers = array_keys(get_object_vars($logs[0]));
        foreach ($logs as $log) {
            $log_data[] = array_values(get_object_vars($log));
        }
    }
    
    // Get settings errors as messages
    $messages = [];
    $errors = get_settings_errors('wirtz_csv_files');
    foreach ($errors as $error) {
        $messages[] = [
            'type' => $error['type'] === 'updated' ? 'success' : 'error',
            'text' => $error['message']
        ];
    }
    
    // Capture WordPress form elements
    ob_start();
    settings_fields('wirtz-data-group');
    $settings_fields = ob_get_clean();
    
    ob_start();
    do_settings_sections('wirtz-data-settings');
    $do_settings_sections = ob_get_clean();
    
    ob_start();
    submit_button();
    $submit_button = ob_get_clean();
    
    echo $twig->render('admin-settings.html.twig', [
        'messages' => $messages,
        'settings_fields' => $settings_fields,
        'do_settings_sections' => $do_settings_sections,
        'submit_button' => $submit_button,
        'csv_files_data' => SettingsFormHandler::getCsvFilesData(),
        'logs' => $log_data,
        'log_headers' => $log_headers
    ]);
}

// Register settings, sections, and fields
function wirtz_data_settings_register()
{
    // Register settings
    register_setting('wirtz-data-group', 'wirtz_csv_folder');
    register_setting('wirtz-data-group', 'ollama_api_endpoint');
    register_setting('wirtz-data-group', 'ollama_model');
    register_setting('wirtz-data-group', 'wirtz_data_stright_to_search_after_login_location');
    register_setting('wirtz-data-group', 'wirtz_allowed_netids');

    // Add settings section
    add_settings_section(
        'wirtz-data-group',
        '',
        null,
        'wirtz-data-settings'
    );

    // Add settings fields
    add_settings_field(
        'wirtz_csv_folder',
        'CSV FOLDER',
        'display_wirtz_csv_folder',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    
    add_settings_field(
        'wirtz_csv_files_list',
        'Current Files in CSV Folder',
        'display_wirtz_csv_files_list',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    
    add_settings_field(
        'ollama_api_endpoint',
        'Ollama API Endpoint',
        'display_ollama_api_endpoint',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    
    add_settings_field(
        'ollama_model',
        'Ollama Model',
        'display_ollama_model',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    
    add_settings_field(
        'wirtz_data_stright_to_search_after_login_location',
        'Redirect After Login Page',
        'display_wirtz_data_redirect_page',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    
    add_settings_field(
        'wirtz_allowed_netids',
        'Allowed NetIDs',
        'display_wirtz_allowed_netids',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
}
add_action('admin_init', 'wirtz_data_settings_register');

/**
 * Displays the input field for CSV folder path setting
 * 
 * This function creates a text input field that allows administrators to specify
 * the folder path where CSV files are stored. The setting is stored in the 
 * WordPress options table.
 *
 * @since 1.0.0
 * @return void
 */
function display_wirtz_csv_folder()
{
    $value = get_option('wirtz_csv_folder', '');
    echo '<input type="text" name="wirtz_csv_folder" value="' . esc_attr($value) . '" size="50"/>';
}

/**
 * Displays the current files in the CSV folder
 * 
 * This function shows a list of CSV files currently in the configured CSV folder.
 * It's a read-only display to help administrators see what files are available.
 *
 * @since 1.0.0
 * @return void
 */
function display_wirtz_csv_files_list()
{
    echo '<p><em>CSV files are displayed in the main settings area above.</em></p>';
}



/**
 * Displays the input field for Ollama API endpoint setting
 * 
 * This function creates a text input field that allows administrators to specify
 * the Ollama API endpoint URL. The default value is http://localhost:11434/api/generate.
 *
 * @since 1.0.0
 * @return void
 */
function display_ollama_api_endpoint()
{
    $value = get_option('ollama_api_endpoint', 'http://localhost:11434/api/generate');
    echo '<input type="text" name="ollama_api_endpoint" value="' . esc_attr($value) . '" size="50"/>';
}

/**
 * Displays the input field for Ollama model setting
 * 
 * This function creates a text input field that allows administrators to specify
 * the Ollama model to use. The default value is llama3.
 *
 * @since 1.0.0
 * @return void
 */
function display_ollama_model()
{
    $value = get_option('ollama_model', 'llama3');
    echo '<input type="text" name="ollama_model" value="' . esc_attr($value) . '" size="50"/>';
}

/**
 * Displays a dropdown to select a page or post for redirection after login
 * 
 * This function creates a dropdown that allows administrators to select
 * a page or post where users will be redirected after login.
 *
 * @since 1.0.0
 * @return void
 */
function display_wirtz_data_redirect_page()
{
    $selected_value = get_option('wirtz_data_stright_to_search_after_login_location', '');
    
    // Get all pages
    $pages = get_pages();
    
    // Get published posts
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1
    ]);
    
    echo '<select name="wirtz_data_stright_to_search_after_login_location">';
    echo '<option value="">-- Select a page or post --</option>';
    
    // Add pages to dropdown
    if (!empty($pages)) {
        echo '<optgroup label="Pages">';
        foreach ($pages as $page) {
            $selected = ($selected_value == $page->ID) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</optgroup>';
    }
    
    // Add posts to dropdown
    if (!empty($posts)) {
        echo '<optgroup label="Posts">';
        foreach ($posts as $post) {
            $selected = ($selected_value == $post->ID) ? 'selected="selected"' : '';
            echo '<option value="' . esc_attr($post->ID) . '" ' . $selected . '>' . esc_html($post->post_title) . '</option>';
        }
        echo '</optgroup>';
    }
    
    echo '</select>';
    echo '<p class="description">Select a page or post where users will be redirected after login.</p>';
}

/**
 * Displays the input field for allowed NetIDs setting
 * 
 * @since 1.0.0
 * @return void
 */
function display_wirtz_allowed_netids()
{
    $value = get_option('wirtz_allowed_netids', '');
    echo '<textarea name="wirtz_allowed_netids" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">These NetIDs are allowed elevated views. Enter NetIDs in a comma separated list with now spaces.</p>';
}

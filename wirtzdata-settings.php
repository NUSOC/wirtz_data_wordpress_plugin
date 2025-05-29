<?php

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
?>
    <div class="wrap">
        <h1>Wirtz Data Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wirtz-data-group');
            do_settings_sections('wirtz-data-settings');
            ?>
            <?php submit_button(); ?>
        </form>
    </div>

    <div>
        <?php
        $logs = wirtzdata_get_logs();
        if (!empty($logs)) {
            echo '<table class="widefat fixed striped">';
            echo '<thead><tr>';
            foreach (array_keys(get_object_vars($logs[0])) as $header) {
                echo '<th>' . esc_html($header) . '</th>';
            }
            echo '</tr></thead><tbody>';
            foreach ($logs as $log) {
                echo '<tr>';
                foreach (get_object_vars($log) as $value) {
                    echo '<td>' . esc_html($value) . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        ?>
    </div>
<?php
}

// Register settings, sections, and fields
function wirtz_data_settings_register()
{
    // Register settings
    register_setting('wirtz-data-group', 'wirtz_csv_folder');
    register_setting('wirtz-data-group', 'ollama_api_endpoint');
    register_setting('wirtz-data-group', 'ollama_model');

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
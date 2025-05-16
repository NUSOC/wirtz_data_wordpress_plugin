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
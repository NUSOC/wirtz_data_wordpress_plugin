<?php 

// Register the settings page
function wirtz_data_settings_page() {
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
function wirtz_data_settings_html() {
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
<?php
}

// Register settings, sections, and fields
function wirtz_data_settings_register() {
    // Register settings
    register_setting('wirtz-data-group', 'csv_folder');
    register_setting('wirtz-data-group', 'allowed_net_id');

    // Add settings section
    add_settings_section(
        'wirtz-data-group', 
        '', 
        null, 
        'wirtz-data-settings'
    );

    // Add settings fields
    add_settings_field(
        'csv_folder', 
        'CSV FOLDER', 
        'display_csv_folder', 
        'wirtz-data-settings', 
        'wirtz-data-group'
    );
    add_settings_field(
        'allowed_net_id', 
        'ALLOWED NetIDs', 
        'display_allowed_net_id', 
        'wirtz-data-settings', 
        'wirtz-data-group'
    );
}
add_action('admin_init', 'wirtz_data_settings_register');

function display_csv_folder() {
    $value = get_option('csv_folder', '');
    echo '<input type="text" name="csv_folder" value="' . esc_attr($value) . '" size="50"/>';
}

function display_allowed_net_id() {
    $value = get_option('allowed_net_id', '');
    echo '<textarea name="allowed_net_id" rows="5" cols="30">' . esc_attr($value) . '</textarea>';
}

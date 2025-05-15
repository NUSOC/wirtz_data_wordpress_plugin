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
    register_setting('wirtz-data-group', 'wirtz_allowed_net_id');
    register_setting('wirtz-data-group', 'wirtz_data_allow_all_netids');
    register_setting('wirtz-data-group', 'wirtz_data_stright_to_search_after_login');
    register_setting('wirtz-data-group', 'wirtz_data_stright_to_search_after_login_location');
    register_setting('wirtz-data-group', 'wirtz_data_subscriber_message_enabled');
    register_setting('wirtz-data-group', 'wirtz_data_subscriber_message_content');
    register_setting('wirtz-data-group', 'wirtz_data_subscriber_message_type');

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
        'wirtz_allowed_net_id',
        'ALLOWED NetIDs',
        'display_wirtz_allowed_net_id',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    add_settings_field(
        'wirtz_data_allow_all_netids',
        'Allow All NetIDs',
        'display_wirtz_data_allow_all_netids',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    add_settings_field(
        'wirtz_data_stright_to_search_after_login',
        'Go Straight to Search After Login',
        'display_wirtz_data_stright_to_search_after_login',
        'wirtz-data-settings',
        'wirtz-data-group'
    );
    add_settings_field(
        'wirtz_data_stright_to_search_after_login_location',
        'Search Page Location',
        'display_wirtz_data_stright_to_search_after_login_location',
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
 * Displays the textarea for allowed NetID settings
 * 
 * This function creates a textarea field that allows administrators to specify
 * which NetIDs are allowed to access the data. Multiple NetIDs can be entered,
 * one per line. The setting is stored in the WordPress options table.
 *
 * @since 1.0.0
 * @return void
 */
function display_wirtz_allowed_net_id()
{
    $value = get_option('wirtz_allowed_net_id', '');
    echo '<textarea name="wirtz_allowed_net_id" rows="5" cols="30">' . esc_attr($value) . '</textarea>';
}

/**
 * Displays the checkbox for allowing all NetIDs setting
 * 
 * This function creates a checkbox field that allows administrators to enable
 * access for all NetIDs, bypassing the allowed NetID list. When checked, any
 * NetID will be able to access the data. The setting is stored in the 
 * WordPress options table.
 *
 * @since 1.0.0
 * @return void
 */
function display_wirtz_data_allow_all_netids()
{
    $value = get_option('wirtz_data_allow_all_netids', '');
    echo '<input type="checkbox" name="wirtz_data_allow_all_netids" value="1" ' . checked(1, $value, false) . '/>';
    echo '<span class="description">Check this box to allow all NetIDs to access the data</span>';
}

/**
 * Displays a checkbox option to enable/disable direct redirection to search page after login
 * 
 * This function creates a checkbox field that allows administrators to configure whether
 * users should be automatically redirected to the search page after logging in.
 * The setting is stored in the WordPress options table.
 *
 * @since 1.0.0
 * @return void
 */
function display_wirtz_data_stright_to_search_after_login()
{
    $value = get_option('wirtz_data_stright_to_search_after_login', '');
    echo '<input type="checkbox" name="wirtz_data_stright_to_search_after_login" value="1" ' . checked(1, $value, false) . '/>';
    echo '<span class="description">Check this box to redirect users directly to search page after login</span>';
}

function display_wirtz_data_stright_to_search_after_login_location()
{
    $selected_id = get_option('wirtz_data_stright_to_search_after_login_location', '');

    // Get all published pages
    $pages = get_pages(array(
        'sort_column' => 'post_title',
        'sort_order' => 'ASC',
        'post_status' => 'publish'
    ));

    // Get all published posts
    $posts = get_posts(array(
        'post_type' => 'post',
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => 'publish',
        'numberposts' => -1
    ));

    echo '<select name="wirtz_data_stright_to_search_after_login_location" id="wirtz_data_stright_to_search_after_login_location">';
    echo '<option value="">-- Select a Page or Post --</option>';

    // Add pages group
    if (!empty($pages)) {
        echo '<optgroup label="Pages">';
        foreach ($pages as $page) {
            echo '<option value="page_' . esc_attr($page->ID) . '" ' . selected('page_' . $page->ID, $selected_id, false) . '>' .
                esc_html($page->post_title) . '</option>';
        }
        echo '</optgroup>';
    }

    // Add posts group
    if (!empty($posts)) {
        echo '<optgroup label="Posts">';
        foreach ($posts as $post) {
            echo '<option value="post_' . esc_attr($post->ID) . '" ' . selected('post_' . $post->ID, $selected_id, false) . '>' .
                esc_html($post->post_title) . '</option>';
        }
        echo '</optgroup>';
    }

    echo '</select>';
    echo '<p class="description">Select the page or post users will be redirected to after login if above checkbox is checked</p>';
}

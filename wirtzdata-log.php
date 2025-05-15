<?php

/**
 * Wirtz Data Logger
 * 
 * Handles logging functionality for the Wirtz Data plugin
 */

/**
 * Create the logging table during plugin activation
 */
function wirtzdata_create_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wirtz_data_log';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        what text NOT NULL,
        who varchar(100) NOT NULL,
        `when` datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Add a log entry to the database
 * 
 * @param string $what What happened
 * @param string $who Who performed the action (optional)
 * @return bool|int The number of rows inserted, or false on error
 */
function wirtzdata_log($what, $who = '') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wirtz_data_log';
    
    // If no user specified, try to get current user
    if (empty($who) && is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $who = $current_user->user_login;
    }
    
    return $wpdb->insert(
        $table_name,
        [
            'what' => $what,
            'who' => $who
            // 'when' will be automatically set to current timestamp
        ],
        ['%s', '%s']
    );
}

/**
 * Get logs from the database
 * 
 * @param array $args Query arguments
 * @return array Array of log entries
 */
function wirtzdata_get_logs($args = []) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wirtz_data_log';
    
    $defaults = [
        'limit' => 100,
        'offset' => 0,
        'orderby' => 'id',
        'order' => 'DESC'
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $query = "SELECT * FROM $table_name";
    $query .= " ORDER BY `{$args['orderby']}` {$args['order']}";
    $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $args['limit'], $args['offset']);
    
    return $wpdb->get_results($query);
}
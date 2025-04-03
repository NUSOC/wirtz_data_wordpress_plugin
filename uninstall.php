<?php
// This file handles the cleanup process when the plugin is uninstalled.
// It removes any stored options or data related to the plugin.

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove options or data related to the plugin
delete_option( 'wirtzdata_db_path' ); // Example option to delete
// Add any additional cleanup code as necessary
?>
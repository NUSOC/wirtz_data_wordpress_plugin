<?php

namespace StackWirtz\WordpressPlugin\Handlers;

class SettingsFormHandler
{
    /**
     * Handles CSV file deletion
     */
    public static function handleCsvFileDeletion()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $filename = sanitize_file_name($_POST['delete_csv_file']);
        $csv_folder = get_option('wirtz_csv_folder', '');
        
        if (empty($csv_folder) || !is_dir($csv_folder)) {
            add_settings_error('wirtz_csv_files', 'folder_error', 'CSV folder not configured or accessible.');
            return;
        }
        
        $file_path = $csv_folder . '/' . $filename;
        
        if (!file_exists($file_path) || pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
            add_settings_error('wirtz_csv_files', 'file_error', 'File not found or invalid file type.');
            return;
        }
        
        if (unlink($file_path)) {
            add_settings_error('wirtz_csv_files', 'file_deleted', 'File "' . $filename . '" deleted successfully.', 'updated');
        } else {
            add_settings_error('wirtz_csv_files', 'delete_error', 'Failed to delete file.');
        }
    }
    
    /**
     * Gets CSV files data for display
     */
    public static function getCsvFilesData()
    {
        $csv_folder = get_option('wirtz_csv_folder', '');
        
        if (empty($csv_folder)) {
            return ['error' => 'No CSV folder configured yet.'];
        }
        
        if (!is_dir($csv_folder)) {
            return ['error' => 'CSV folder path does not exist or is not accessible.'];
        }
        
        $files = glob($csv_folder . '/*.csv');
        
        if (empty($files)) {
            return ['error' => 'No CSV files found in the folder.'];
        }
        
        $file_data = [];
        foreach ($files as $file) {
            $file_data[] = [
                'filename' => basename($file),
                'size' => size_format(filesize($file)),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
                'nonce' => wp_create_nonce('delete_csv_file')
            ];
        }
        
        return ['files' => $file_data];
    }
}
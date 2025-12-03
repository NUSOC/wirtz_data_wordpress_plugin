<?php

namespace StackWirtz\WordpressPlugin\Handlers;

class SettingsFormHandler
{
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
                'modified' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        return ['files' => $file_data];
    }
}
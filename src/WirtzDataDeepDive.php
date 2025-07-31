<?php

namespace StackWirtz\WordpressPlugin;

use StackWirtz\WordpressPlugin\WirtzShow;
use StackWirtz\WordpressPlugin\Models\WirtzData;

class WirtzDataDeepDive extends WirtzShow
{
    public function deepDiveView()
    {
        $this->userAuthCheck();

        // Check if user is in allowed NetIDs list
        $current_user = wp_get_current_user();
        $allowed_netids = get_option('wirtz_allowed_netids', '');
        $allowed_list = array_map('trim', explode(',', $allowed_netids));

        // This if block checks if the current user's login is NOT in the allowed_list array
        // If the user is not allowed, it returns an error page rendered with Twig
        // The return statement stops further execution of the function at this point
        // So if user is not authorized, they only see the error and the rest of deepDiveView() never executes
        if (!in_array($current_user->user_login, $allowed_list)) {
            return $this->twig->render('generror.html.twig', [
                'error' => 'Access denied. You do not have permission to view this page.'
            ]);
        }

        // Get the report type from URL query string parameter 'report_type'
        // Defaults to 'none' if parameter is not set
        $report_type     = isset($_GET['report_type'])
            ? $_GET['report_type']
            : 'none';

        // Set up content variable to hold report output
        $content = '';

        // Switch statement to handle different report types
        switch($report_type) {
            // Add additional report cases here as needed
            
            default:
                $content = $this->getDefaultReport();
                break;
        }

        return $this->twig->render('deepdive.html.twig', [
            'data' => $this->wirtz_data->getData(),
            'returnPage' => $_SERVER['REQUEST_URI'],
            'content' => $content
        ]);
    }

    /**
     * Get default report content when no specific report type is selected
     * @return string Default report content
     */
    private function getDefaultReport() {
        return $this->twig->render('default-report.html.twig', [
            'data' => $this->wirtz_data->getData()
        ]);
    }
    
}

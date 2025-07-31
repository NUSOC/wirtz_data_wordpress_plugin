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

        if (!in_array($current_user->user_login, $allowed_list)) {
            wp_die('Access denied. You do not have permission to view this page.');
        }

        return $this->twig->render('deepdive.html.twig', [
            'data' => $this->wirtz_data->getData(),
            'returnPage' => $_SERVER['REQUEST_URI']
        ]);
    }
}

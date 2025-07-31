<?php

namespace StackWirtz\WordpressPlugin;

use StackWirtz\WordpressPlugin\WirtzShow;
use StackWirtz\WordpressPlugin\Models\WirtzData;

class WirtzDataDeepDive extends WirtzShow
{
    public function deepDiveView()
    {
        $this->userAuthCheck();
        
        return $this->twig->render('deepdive.html.twig', [
            'data' => $this->wirtz_data->getData(),
            'returnPage' => $_SERVER['REQUEST_URI']
        ]);
    }
}
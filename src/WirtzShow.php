<?php

namespace StackWirtz\WordpressPlugin;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use StackWirtz\WordpressPlugin\Models\WirtzData;



class WirtzShow
{
    private $twig, $wirtz_env, $wirtz_data;

    public function __construct($wirtz_env)
    {
        $this->wirtz_env = $wirtz_env;
        $this->wirtz_data = new WirtzData($wirtz_env);





        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/templates'); // Corrected template path
        $this->twig = new Environment($loader);
    }

    public function startpoint()
    {
        dump($this->wirtz_data->getUniqueYears());
        dump($this->wirtz_data->getUniqueProductions());
        dump(    $this->wirtz_data->getData());
        
    }
}

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
        // dump($this->wirtz_data->getUniqueYears());
        // dump($this->wirtz_data->getUniqueProductions());
        // dump($this->wirtz_data->getData());

        if (is_front_page() || is_home()) {
            // If on the front page or home page, return an empty string
            return '';
        }



        if (isset($_GET['first']) && isset($_GET['last'])) {
            $first = wp_kses(trim($_GET['first']), []);
            $last = wp_kses(trim($_GET['last']), []);
            dump([$first, $last]);

            $people = $this->wirtz_data->doSearch(
                $first,
                $last
            );
            dump($people);

        } else {
            $first = 0;
            $last = 0;
            $people = [];
        }

        return $this->twig->render(
            'startpoint.html.twig',
            [
                'people' => $people,
                'returnPage' => $currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            ]
        );
    }
}

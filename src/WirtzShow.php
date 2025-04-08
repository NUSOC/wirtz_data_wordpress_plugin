<?php


namespace StackWirtz\WordpressPlugin;

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use StackWirtz\WordpressPlugin\Models\WirtzData;



class WirtzShow
{
    private $twig, $wirtz_data;

    public function __construct()
    {
    
        $this->wirtz_data = new WirtzData();

     

        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/templates'); // Corrected template path
        $this->twig = new Environment($loader);

        $this->checks();
    }


    public function checks() {


        // does get_option('csv_folder') have value
        if (get_option('csv_folder') == '') {
            $error_message = "Trouble: No folder set in the settings page.";
            wp_die($error_message);
            
        }

          // is there a list in get_option('allowed_net_id')
        if (count(explode(',', get_option('allowed_net_id'))) == 1 && get_option('allowed_net_id') == '') {
            $error_message = "Trouble: No NetIDs set in the settings page.";
            wp_die($error_message);
        }
        
    }

    public function startpoint()
    {


        if (isset($_GET['first']) && isset($_GET['last'])) {
            $first = wp_kses(trim($_GET['first']), []);
            $last = wp_kses(trim($_GET['last']), []);

            // Check if first and last names are longer than 3 characters
            if (strlen($first) > 2 || strlen($last) > 2) {
                $people = $this->wirtz_data->doSearch(
                    $first,
                    $last,
                    wp_kses($_GET['sort'] ?? 'Name', []),
                );
            } else {
                $error_message = "Trouble: First or last names must be longer than two characters";
                $people = [];
            }

        // no search terms coming in     
        } else {
            $first = 0;
            $last = 0;
            $people = [];
        }





        return $this->twig->render(
            'startpoint.html.twig',
            [
                'sort' => wp_kses(trim($_GET['sort'] ?? ''), []),
                'first' => $first,
                'last' => $last,
                'error'=> $error_message ?? '',
                'people' => $people,
                'returnPage' => $currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            ]
        );
    }
}

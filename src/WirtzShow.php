<?php


namespace StackWirtz\WordpressPlugin;


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

        // run basic checks to ensure data is set up
        $this->checks();

    }


    public function userAuthCheck() {
        // Check if the user is logged in
        if (!is_user_logged_in()) {
            // User is not logged in, redirect to login page
            wp_redirect(wp_login_url());
            exit;
        }

        // Get the current user's NetID
        $current_user = wp_get_current_user();
        $netid = $current_user->user_login;

        // Check if the NetID is allowed
        $allowed_net_ids = explode(',', get_option('wirtz_allowed_net_id'));
        if (!in_array($netid, $allowed_net_ids)) {
            // NetID is not allowed, show an error message
            wp_die("You do not have permission to access this page.");
        }

        dump($allowed_net_ids);
    }

    public function checks()
    {


        // does get_option('csv_folder') have value
        if (get_option('wirtz_csv_folder') == '') {
            $error_message = "Trouble: No folder set in the settings page.";
            wp_die($error_message);
        }

        // is there a list in get_option('allowed_net_id')
        if (count(explode(',', get_option('wirtz_allowed_net_id'))) == 1 && get_option('wirtz_allowed_net_id') == '') {
            $error_message = "Trouble: No NetIDs set in the settings page.";
            wp_die($error_message);
        }
    }

    public function startpoint()
    {

        // check if user is logged in and has access
        $this->userAuthCheck();

        
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
                'error' => $error_message ?? '',
                'people' => $people,
                'returnPage' => $currentUrl = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
            ]
        );
    }


    public function listPlaysByYear()
    {


        // get year from query string if needed
        $currentyear = wp_kses(trim($_GET['currentyear'] ?? ''), "");

        // if $year is empty do nothing. If not empty, get the plays for that year
        if ($currentyear != '') {
            $plays = $this->wirtz_data->getPlaysfromYear($currentyear);
        } else {
            $plays = [];
        }

        // return to the template
        return $this->twig->render(
            'listplays.html.twig',
            [
                'currentyear'   => $currentyear,
                'years'         => $this->wirtz_data->getUniqueYears(),
                'returnPage'    => $_SERVER['REQUEST_URI'],
                'plays'         => $plays,
            ]
        );
    }
}

<?php


namespace StackWirtz\WordpressPlugin;


use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use StackWirtz\WordpressPlugin\Models\WirtzData;



class WirtzShow
{
    
    // Changed from private to protected to allow child class access
    protected $twig, $wirtz_data; 

    /**
     * Constructor for WirtzShow class
     * 
     * Initializes WirtzData model, sets up Twig template engine with templates directory,
     * and runs basic data setup checks
     *
     * @return void
     */
    public function __construct()
    {

        $this->wirtz_data = new WirtzData();



        // Set up Twig
        $loader = new FilesystemLoader(__DIR__ . '/templates'); // Corrected template path
        $this->twig = new Environment($loader);

        // run basic checks to ensure data is set up
        $this->checks();
    }


    /**
     * Checks user authentication
     * 
     * Verifies if user is logged in and redirects to login page if not.
     *
     * @return void
     */
    public function userAuthCheck()
    {

        // Check if the user is logged in
        if (!is_user_logged_in()) {
            // User is not logged in, redirect to login page
            wp_redirect(wp_login_url());
            exit;
        }
        
    }

    public function checks()
    {


        // does get_option('csv_folder') have value
        if (get_option('wirtz_csv_folder') == '') {
            $error_message = "Trouble: No folder set in the settings page.";
            wp_die($error_message);
        }

 
    }

    /**
     * [wirtzdata] shortcode basically
     * 
     * Handles the main search functionality for people
     * 
     * Checks user authentication, processes search parameters for first/last names,
     * validates input length, and returns rendered template with search results
     *
     * @return string Rendered Twig template with search results and parameters
     */
    public function startpoint()
    {

        // check if user is logged in and has access
        $this->userAuthCheck();



        // Sanitize and validate input parameters, converting 0 values to empty strings
        $first      = ($temp = sanitize_text_field(wp_unslash(trim($_GET['first'] ?? '')))) == 0 ? '' : $temp;
        $last       = ($temp = sanitize_text_field(wp_unslash(trim($_GET['last'] ?? '')))) == 0 ? '' : $temp;
        $production = ($temp = sanitize_text_field(wp_unslash(trim($_GET['production'] ?? '')))) == 0 ? '' : $temp;
        $team       = ($temp = sanitize_text_field(wp_unslash(trim($_GET['team'] ?? '')))) == 0 ? '' : $temp;
        $role       = ($temp = sanitize_text_field(wp_unslash(trim($_GET['role'] ?? '')))) == 0 ? '' : $temp;
        $career     = ($temp = sanitize_text_field(wp_unslash(trim($_GET['career'] ?? '')))) == 0 ? '' : $temp;
        $grad       = ($temp = sanitize_text_field(wp_unslash(trim($_GET['grad'] ?? '')))) == 0 ? '' : $temp;



        // Check if first and last names are longer than 3 characters Before calling the main search selection. Note that
        // The default search is now last name.
        if (strlen($first) > 2 || strlen($last) > 2 || strlen($production) > 4 || strlen($team) > 3 || strlen($role) > 3 || strlen($career) > 1 || strlen($grad) > 1) {
            $people = $this->wirtz_data->doSearch(
                $first,
                $last,
                $production,
                $team,
                $role,
                $career,
                $grad
            );
        } 


 

        //dump(WIRTZ_PLUGIN_URL);


        return $this->twig->render(
            'startpoint.html.twig',
            [
                'first' => $first ?? '',
                'last' => $last ?? '',
                'team' => $team ?? '',
                'production' => $production ?? '',
                'role' => $role ?? '',
                'career' => $career ?? '',
                'grad' => $grad ?? '',
                'error' => $error_message ?? '',
                'people' => $people ?? [],
                '_get' => array_map('sanitize_text_field', $_GET ?? []),
                'returnPage' => $currentUrl = $_SERVER['REQUEST_URI'],
                'pluginroot' => WIRTZ_PLUGIN_URL,
            ]
        );
    }


    /**
     * Lists plays for a specific year
     * 
     * Gets the year from the query string parameter 'currentyear',
     * retrieves plays for that year from wirtz_data, and renders
     * the results in a template with year selection options
     *
     * @return string Rendered Twig template with plays list and year selection
     */
    public function listPlaysByYear()
    {


        // get year from query string if needed and sanitize it
        $currentyear = sanitize_text_field(wp_unslash(trim($_GET['currentyear'] ?? '')));

        // redirect post
        $postlink = get_permalink(get_option('wirtz_data_stright_to_search_after_login_location', ''));
        


        
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
                'is_user_logged_in' => is_user_logged_in(),
                'currentyear'   => $currentyear,
                'years'         => $this->wirtz_data->getUniqueYears(),
                'returnPage'    => $_SERVER['REQUEST_URI'],
                'plays'         => $plays,
                'postlink'      => $postlink,
                'csvmoddate'    => $this->wirtz_data->lastFileModifiedAt(),
                'lastestfile'   => $this->wirtz_data->latestFile(),
                
            ]
        );
    }

    public function renderProductionByYearChart($skipChecks = false)
    {
        if (!is_user_logged_in()) {
            $login_url = wp_login_url();
            return "Please <a href='$login_url'>login</a> to view this chart.";
        }

        if (!$skipChecks && get_option('wirtz_csv_folder') == '') {
            return "Error: No CSV folder configured. Please set it in Wirtz Data Settings.";
        }

        $productionCountsByYear = $this->wirtz_data->getProductionCountsByYear();

        if (empty($productionCountsByYear)) {
            return "No production data found. Please check your CSV file.";
        }

        return $this->twig->render(
            'production-by-year.html.twig',
            [
                'productionCountsByYear' => $productionCountsByYear
            ]
        );
    }

    public function renderDashboard($skipChecks = false)
    {
        if (!is_user_logged_in()) {
            $login_url = wp_login_url();
            return "Please <a href='$login_url'>login</a> to view this dashboard.";
        }

        if (!$skipChecks && get_option('wirtz_csv_folder') == '') {
            return "Error: No CSV folder configured. Please set it in Wirtz Data Settings.";
        }

        $data = $this->wirtz_data->getData();
        $productionCountsByYear = $this->wirtz_data->getProductionCountsByYear();
        
        $totalProductions = 0;
        foreach ($productionCountsByYear as $year) {
            $totalProductions += count($year);
        }

        // Get file information
        $csvFile = $this->wirtz_data->latestFile();
        $fileInfo = [
            'name' => basename($csvFile),
            'modified' => $this->wirtz_data->lastFileModifiedAt(),
            'rowCount' => count($data)
        ];

        return $this->twig->render(
            'dashboard.html.twig',
            [
                'totalPeople' => count($data),
                'totalProductions' => $totalProductions,
                'totalYears' => count($productionCountsByYear),
                'productionCountsByYear' => $productionCountsByYear,
                'careerByYear' => $this->wirtz_data->getCareerByYear(),
                'teamByYear' => $this->wirtz_data->getTeamByYear(),
                'roleDistribution' => $this->wirtz_data->getRoleDistribution(),
                'rolesByYear' => $this->wirtz_data->getRolesByYear(),
                'gradByYear' => $this->wirtz_data->getGradByYear(),
                'fileInfo' => $fileInfo
            ]
        );
    }
}

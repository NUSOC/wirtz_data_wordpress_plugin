<?php

namespace StackWirtz\WordpressPlugin\Models;

class WirtzData
{

    private $wirtz_env, $lastest_file, $data, $headers;

    public function __construct()
    {
        $this->lastest_file = $this->latestFile();
        if ($fileHandle = fopen($this->lastest_file, 'r')) {
            $data = array();
            while (($row = fgetcsv($fileHandle)) !== false) {
                $data[] = $row;
            }
            fclose($fileHandle); // Don't forget to close the file
            $this->data = $this->convertToArrayWithColumnHeaders($data);
        } else {
            // TODO: Handle error
        }
    }


    /**
     * Returns the path of the latest CSV file in the specified folder.
     *
     * @return string The path of the latest CSV file.
     */
    public function latestFile()
    {

        //TODO: check if folder exists first
        $files = glob(get_option('wirtz_csv_folder') . '/*.csv');




        // Sort the files array by modification time in descending order
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Return the latest file
        return str_replace("//", "/", end($files));
    }


    /**
     * Returns the data as an array with column headers, where the first 
     * row serves as the keys for a multidimensional array.
     *
     * @return array
     */
    public function getData()
    {
        return (array) $this->data;
    }





    /**
     * Converts the provided array into a format with column headers.
     * This function takes the first row of the array as headers and uses 
     * them as keys for the subsequent rows. It also renames specific headers 
     * to shorter versions. For example, "First name" becomes "First", 
     * "Last name" becomes "Last", and "Graduation Year" becomes "Grad".
     * The function returns a new array where each row is an associative array 
     * with the headers as keys.
     *
     * @param array $data
     * @return array
     */
    private function convertToArrayWithColumnHeaders(array $data)
    {
        $result = [];
        $this->headers = array_shift($data); // Get the first row as headers

        foreach ($data as $row) {
            $tempArray = [];

            // correct the headers
            foreach ($this->headers as $key => $header) {
                if ($header == 'First name') {
                    $header = 'First';
                } elseif ($header == 'Last name') {
                    $header = 'Last';
                } elseif ($header == 'Graduation Year') {
                    $header = 'Grad';
                }

                $tempArray[$header] = $row[$key];
            }


            // adjust the values
            if (array_key_exists('Career', $tempArray)) {
                $tempArray['Career'] = str_replace('Undergraduate', 'UG', $tempArray['Career']);
            }

            if (array_key_exists('Grad', $tempArray)) {
                $tempArray['Grad'] = str_replace('.0', '', $tempArray['Grad']);
            }

            if (array_key_exists('Year', $tempArray)) {
                $tempArray['Year'] = trim(str_replace('Season.xlsx', '', $tempArray['Year']));
            }



            $result[] = $tempArray; // Add each row to the result array
        }

        return $result;
    }


    /**
     * Get an array of unique values for a given column.
     *
     * @param string $column The name of the column to get unique values for
     * @return array An array of unique values in ascending order
     */
    private function getUniqueColumn($column)
    {
        $uniqueValues = array_values(array_unique($this->getColumnValues($column)));
        asort($uniqueValues); // or arsort for reverse sort
        return $uniqueValues;
    }

    /**
     * Get the column values for a given column.
     *
     * @param string $column The name of the column to get values for
     * @return array An array of values in the given column
     */
    private function getColumnValues($column)
    {
        return array_column($this->getData(), $column);
    }

    /**
     * Get an array of unique Years
     */
    public function getUniqueYears()
    {
        return $this->getUniqueColumn('Year');
    }

    /**
     * Get an array of unique Productions
     */
    public function getUniqueProductions()
    {
        return $this->getUniqueColumn('Production');
    }

    /**
     * Get an array of unique Productions
     */
    public function getUniqueTeams()
    {
        return $this->getUniqueColumn('Team');
    }

    /**
     * Get an array of unique Teams
     */
    public function getUniqueRoles()
    {
        return $this->getUniqueColumn('Role');
    }

    /**
     * Get an array of unique Roles
     */
    public function getRoles()
    {
        return $this->getColumnValues('Role');
    }

    /**
     * Retrieves plays from a specific year, sorted by production in descending order.
     *
     * @param int $year The year to filter plays for.
     * @return array An array of plays from the specified year, ordered by production.
     */
    public function getPlaysfromYear($year)
    {
        // Filter data to include only rows where Year contains the specified year
        $result = array_filter($this->getData(), function ($row) use ($year) {
            return (stripos($row['Year'], $year) !== false);
        });

        // Store unique values of 'Production' in an associative array.
        $productions = [];
        foreach ($result as $play) {
            if (!in_array($play['Production'], $productions)) {
                $productions[] = $play['Production'];
            }
        }
        // Sort the $productions array based on 'Production' key value in ascending order.
        usort($productions, function ($a, $b) {
            return strcmp($a, $b);
        });

        // Return an array containing unique productions in descending order of production name.
        return $productions;
    }

    /**
     * Search for people by first and last name, or by production name.
     * Results can be sorted by production, last name, year, or first name.
     * 
     * @param string $first The first name to search for
     * @param string $last The last name to search for 
     * @param string $production The production name to search for
     * @param string $sort The sort order - 'production', 'last', 'year' or defaults to first name
     * @return array An array of matching people, sorted according to the sort parameter
     */
    public function doSearch($first, $last, $production, $team, $role, $career, $grad)
    {

        // We're just gonna default sorting by the last name
        $sort = 'Last';

        // Use sanitize_text_field() to safely log search parameters by removing special characters
        // This provides protection against malicious input in log files
        wirtzdata_log(sprintf(
            "doSearch is searching on [%s], [%s], [%s], [%s], [%s], [%s]",
            sanitize_text_field($first),
            sanitize_text_field($last),
            sanitize_text_field($production),
            sanitize_text_field($team),
            sanitize_text_field($role),
            sanitize_text_field($career),
            sanitize_text_field($grad)
        ));


        $terms = array_map('strtolower', compact('first', 'last', 'production', 'career', 'grad', 'team', 'role'));

        /**
         * Filters the data array based on search criteria:
         * - Checks if first name contains the search term (case insensitive)
         * - Checks if last name contains the search term (case insensitive) 
         * - Checks if production name contains the search term (case insensitive)
         * - Checks if team matches the specified team (case insensitive)
         * - Checks if role matches the specified role (case insensitive)
         * - Checks if career matches the specified career (case insensitive)
         * - Checks if graduation year matches the specified grad year (case insensitive)
         * 
         * Each condition is only applied if the corresponding search term is not empty.
         * Returns rows that match ALL provided search criteria.
         */
        $result = array_filter($this->getData(), function ($row) use ($terms) {
            return (!$terms['first'] || stripos($row['First'], $terms['first']) !== false) &&
                (!$terms['last'] || stripos($row['Last'], $terms['last']) !== false) &&
                (!$terms['production'] || stripos($row['Production'], $terms['production']) !== false) &&
                (!$terms['team'] || stripos($row['Team'], $terms['team']) !== false) &&
                (!$terms['role'] || stripos($row['Role'], $terms['role']) !== false) &&
                (!$terms['career'] || stripos($row['Career'], $terms['career']) !== false) &&
                (!$terms['grad'] || stripos($row['Grad'], $terms['grad']) !== false);
        });


        // dump($result);

       // default to Last
        $field = 'Last';
        usort($result, function ($a, $b) use ($field) {
            return strcasecmp($a[$field], $b[$field]);
        });

        return $result;
    }


    public function getHeaders()
    {
        return $this->headers;
    }
}

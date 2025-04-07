<?php

namespace StackWirtz\WordpressPlugin\Models;

class WirtzData
{

    private $wirtz_env, $lastest_file, $data;

    public function __construct($wirtz_env)
    {
        $this->wirtz_env = $wirtz_env;
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
        $files = glob($this->wirtz_env['CSVFOLDER'] . '/*.csv');


        // Sort the files array by modification time in descending order
        usort($files, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Return the latest file
        return str_replace("//", "/", end($files));
    }


    /**
     * Returns the data as an array with column headers, where the first row serves as the keys for a multidimensional array.
     *
     * @return array
     */
    public function getData()
    {
        return (array) $this->data;
    }



    /**
     * Converts the provided array into a format with column headers.
     *
     * @param array $data
     * @return array
     */
    private function convertToArrayWithColumnHeaders(array $data)
    {
        $result = [];
        $headers = array_shift($data); // Get the first row as headers

        foreach ($data as $row) {
            $tempArray = [];
            foreach ($headers as $key => $header) {
                if ($header == 'First name') {
                    $header = 'First';
                } elseif ($header == 'Last name') {
                    $header = 'Last';
                } elseif ($header == 'Graduation Year') {
                    $header = 'Grad';
                }
                $tempArray[$header] = $row[$key];
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


    public function getUniqueYears()
    {
        return $this->getUniqueColumn('Year');
    }

    public function getUniqueProductions()
    {
        return $this->getUniqueColumn('Production');
    }

    public function getUniqueTeams()
    {
        return $this->getUniqueColumn('Team');
    }

    public function getUniqueRoles()
    {
        return $this->getUniqueColumn('Role');
    }

    public function getRoles()
    {
        return $this->getColumnValues('Role');
    }



    public function doSearch($first, $last)
    {
        $first = strtolower($first);
        $last = strtolower($last);

        $result = array_filter($this->getData(), function ($row) use ($first, $last) {
            return (stripos($row['First'], $first) !== false && stripos($row['Last'], $last) !== false);
        });

        return array_values($result); // Re-index the array
    }
}

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
            $this->data = $data;
        } else {
            // TODO: Handle error
        }
    }


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


    public function getData()
    {
        return (array) $this->convertToArrayWithColumnHeaders($this->data);
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
                $tempArray[$header] = $row[$key];
            }
            $result[] = $tempArray; // Add each row to the result array
        }

        return $result;
    }
    

    private function getUniqueColumn($column) {
        $uniqueValues = array_values(array_unique(array_column($this->getData(), $column)));
        asort($uniqueValues); // or arsort for reverse sort
        return $uniqueValues;    
    }


    public function getUniqueYears() {
        return $this->getUniqueColumn('Year');
    }

    public function getUniqueProductions() {
        return $this->getUniqueColumn('Production');
    }


}

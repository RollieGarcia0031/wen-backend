<?php

namespace App\Util;

/**
 * Manipulates an associative array and converts it to
 * more readable version for json coversion
 */
class JSON_MAKER {

    /**
     * Takes an assiociative array and converts the array
     * aggravated to a json format
     *
     * This would require atleast 2 fields that are aray
     * aggravated with similar number of elements
     *
     * @param array $arrayInput
     * @param array $fields an array of string (atleast 2)
     *                      that is the field names to be
     *                      merged
     * @param string $json_name name of json containing aggravated
     *                          array
     */
    public static function json_agg(
        array $arrayInput,
        array $fields,
        string $json_name): array
    {
        // count the size of the array that is nested inside to be aggravated
        $aggSize = count($arrayInput[0]);

        $arrayInput = array_map(function($item) use ($json_name, $fields, $aggSize){
            // create a new json holder that will hold all of nested array
            $newJson = [];

            for ($i = 0; $i <= $aggSize; $i ++){
                // create a new array containing the grouped info
                $newArray = [];

                foreach ($fields as $field){
                    // insert the data from the array input
                    $newArray[$field] = $item[$field][$i]; 
                }

                $newJson[] = $newArray;
            }

            $item[$json_name] = $newJson; 
        }, $arrayInput);

        return $arrayInput;    
    }
}

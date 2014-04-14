<?php
    // bookImportFromJSON({...})
    // Given a response string from a googleapis.com/books request, this
    // imports all contained books into the database
    // Required arguments:
    // * $data
    // Optional arguments:
    // * $term
    function bookImportFromJSON($arguments, $noverbose=false) {
        $data = $arguments['data'];
        // If $data is a string, and not (JSON) an object/array, convert it
        if(is_string($data)) {
            $data = json_decode($data);
        }
        
        // Get the array of book items, if found
        $term = isset($arguments['term']) ? $arguments['term'] : false;
        $items = followPath($data, ['items']);
        if(!$items) {
            if($noverbose) {
                if($term) {
                    echo 'Nothing found for ' . $term . '!';
                } else {
                    echo 'Nothing found!';
                }
            }
            return false;
        }
        
        // Get the identifiers of each item
        $dbConn = getPDOQuick($arguments);
        foreach($items as $item) {
            // Check for identifiers of the book, and only do work if they exist
            $identifiers = followPath($item, ['volumeInfo', 'industryIdentifiers']);
            if(!$identifiers) {
                continue;
            }
            
            // Attempt to add it on the first ISBN style identifier
            foreach($identifiers as $identity) {
                if($identity->type == "ISBN_13" || $identity->type == "ISBN") {
                    if(bookImportFullCheck($dbConn, $identity->identifier)) {
                        // Only stop this item if that was successful
                        continue;
                    }
                }
            }
        }
    }
?>
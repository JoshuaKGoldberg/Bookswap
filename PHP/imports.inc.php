<?php
    // bookImportFromJSON({...})
    // Given a response string from a googleapis.com/books request, this
    // imports all contained books into the database
    // Required arguments:
    // * $data
    // Optional arguments:
    // * $term
    function bookImportFromJSON($arguments) {
        $data = $arguments['data'];
        // If $data is a string, and not (JSON) an object/array, convert it
        if(is_string($data)) {
            $data = json_decode($data);
        }
        
        // Get the array of book items, if found
        $term = isset($arguments['term']) ? $arguments['term'] : false;
        $items = followPath($data, ['items']);
        if(!$items) {
            return false;
        }
        
        // The book informations are stored in this array to be returned later
        $results = array();
        
        // Get enough info from each one to add it to the database
        $dbConn = getPDOQuick($arguments);
        foreach($items as $item) {
            $arguments['book'] = $item;
            
            // Check for identifiers of the book, and only do work if they exist
            $identifiers = followPath($item, ['volumeInfo', 'industryIdentifiers']);
            if(!$identifiers) {
                continue;
            }
            
            // Attempt to add it on the first ISBN style identifier
            foreach($identifiers as $identity) {
                if($identity->type == "ISBN_13" || $identity->type == "ISBN") {
                    $isbn = $arguments['isbn'] = $identity->identifier;
                    
                    $result = array(
                        'isbn' => $isbn,
                        'added' => false
                    );
                    
      
                    // Make sure the book doesn't already exist
                    if(doesBookAlreadyExist($dbConn, $isbn)) {
                        $result['status'] = 'already exists';
                    } else {
                        // If adding it was successful, add it to the ISBNs
                        if(bookProcessObject($arguments, true)) {
                            $result['status'] = 'added';
                            $result['added'] = true;
                        }
                        // Otherwise complain
                        else {
                            $result['status'] = 'failed';
                        }
                    }
                    
                    $result['title'] = getRowValue($dbConn, 'books', 'title', 'isbn', $isbn);
                    
                    array_push($results, $result);
                }
            }
        }
        
        return $results;
    }
    
    // bookProcessObject({...})
    // Processes a $book object from the Google API, with ->volumeInfo
    // Required arguments:
    // * {book}
    // * "isbn"
    function bookProcessObject($arguments, $noverbose=false) {
        $book = $arguments['book'];
        $isbn = $arguments['isbn'];
        
        // $book->volumeInfo is completely required for this
        if(!isset($book->volumeInfo) || !$book->volumeInfo) {
            return false;
        }
        $info = $book->volumeInfo;
        
        // Don't continue if the title or authors are missing
        if(!isset($info->title) || !isset($info->authors)) {
            return false;
        }
        
        // Copy the relevant information to variables
        $title = $info->title;
        $authors = $info->authors;
        $description = isset($info->description) ? explode("\n", $info->description)[0] : '';
        $publisher = isset($info->publisher) ? $info->publisher : '';
        $year = isset($info->publishedDate) ? $info->publishedDate : '';
        $pages = isset($info->pageCount) ? $info->pageCount : '';
        $googleID = isset($book->id) ? $book->id : '';
        
        // Don't continue if the title or authors are falsy
        if(!$title || !$authors) {
            return false;
        }
        
        // Pipe to the actual dbBooksAdd function
        $dbConn = getPDOQuick($arguments);
        if(dbBooksAdd($dbConn, $isbn, $googleID, $title, $authors, $description, $publisher, $year, $pages)) {
          if(!$noverbose) echo 'Yes';
          return true;
        }
        return false;
        
    }
    
    // Mention a book already exists
    function doesBookAlreadyExist($dbConn, $isbn) {
      return checkKeyExists($dbConn, 'books', 'isbn', $isbn);
    }
    
    // Navigate through the STD->pointers
    function followPath($object, $names) {
      $current = $object;
      foreach($names as $name) {
        if(isset($current->$name))
          $current = $current->$name;
        else return false;
      }
      return $current;
    }
?>
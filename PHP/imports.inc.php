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
    
    // Real function to add a book, if the ISBN isn't already there
    function bookImportFullCheck($dbConn, $isbn) {
      // Make sure the book doesn't already exist
      if(doesBookAlreadyExist($dbConn, $isbn)) {
        return;
      }
        
      // Since it doesn't, call Google to add it
      if(publicAddBook(array('isbn'=>$isbn), true)) {
        echo '<aside class="success">' . getLinkHTML('book', getRowValue($dbConn, 'books', 'title', 'isbn', $isbn), array('isbn'=>$isbn)) . ' added</aside>';
      } else {
        echo '<aside class="failure">' . $isbn . ' not added</aside>';
      }
    }
    
    // Mention a book already exists
    function doesBookAlreadyExist($dbConn, $isbn) {
      if(checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
        echo '<aside>ISBN ' . $isbn . ' is already in our database as ';
        echo getLinkHTML('book', getRowValue($dbConn, 'books', 'title', 'isbn', $isbn), array('isbn'=>$isbn));
        echo '</aside>';
        return true;
      }
      return false;
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
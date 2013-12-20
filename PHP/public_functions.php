<?php
  /* Functions the public may access via requests.js -> requests.php
  */
  require_once('pdo.inc.php');
  require_once('sql.inc.php');
  
  /* Helper functions to ensure argument safety
  */
  function ArgStrict($arg) {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $arg);
  }
  function ArgLoose($arg) {
    return $arg;
  }
  
  // publicCheckValidity({...})
  // Checks if a field (username, email) is taken and echos if it's unavailable
  // Required fields:
  // * "type"   - "username" or "email" 
  // * "value"  - the value to see if it's duplicate or not
  function publicCheckValidity($arguments, $noverbose=false) {
    $dbConn = getPDOQuick();
    $field = $arguments['type'];
    $value = $arguments['value'];
    if(checkKeyExists($dbConn, 'users', $field, $value))
      echo "The " . $field . " '" . $value . "' is already taken :(";
  }
  
  // publicCreateUser({...})
  // Public pipe to dbUsersAdd("username", "password")
  // Required fields:
  // * "username"
  // * "password"
  // * "email"
  function publicCreateUser($arguments, $noverbose=false) {
    $dbConn = getPDOQuick();
    $username = $arguments['j_username'];
    $password = $arguments['j_password'];
    $email = $arguments['j_email'];
    
    // Make sure the arguments aren't blank
    if(!$username || !$password || !$email) return;

    // If successful, log in
    if(dbUsersAdd($dbConn, $username, $password, $email, 0)) {
      $arguments['username'] = $arguments['j_username'];
      $arguments['password'] = $arguments['j_password'];
      $arguments['email'] = $arguments['j_email'];
      publicLogin($arguments, true);
      if(!$noverbose)
        echo 'Yes';
    }
  }
  
  // publicLogin({...})
  // Public pipe to loginAttempt("username", "password")
  // Required fields:
  // * "username"
  // * "password"
  function publicLogin($arguments, $noverbose=false) {
    $username = $arguments['username'];
    $password = $arguments['password'];
    if(loginAttempt($username, $password) && !$noverbose)
      echo 'Yes';
  }

  // publicAddBook({...})
  // Gets the info on a book from the Google API, then pipes it to dbBooksAdd
  // Required fields:
  // * "isbn"
  // https://developers.google.com/books/docs/v1/using
  // https://www.googleapis.com/books/v1/volumes?q=isbn:9780073523323&key=AIzaSyD2FxaIBhdLTA7J6K5ktG4URdCFmQZOCUw
  function publicAddBook($arguments, $noverbose=false) {
    $dbConn = getPDOQuick();
    $isbn = $arguments['isbn'];
    
    // Make sure the arguments aren't blank
    if(!$isbn) return;
    
    // Get the actual JSON contents and decode it
    $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . $isbn . '&key=' . getGoogleKey();
    $result = json_decode(getHTTPPage($url));
    
    // If there was an error, oh no!
    if(isset($result->error)) {
      echo $result->error->message;
      return;
    }
    
    // Attempt to get the first item in the list (which will be the book)
    if(!isset($result->items) || !isset($result->items[0])) return;
    $book = $result->items[0];
    
    // Attempt to get the book's info (stored as volumeInfo)
    if(!isset($book->volumeInfo)) return;
    $info = $book->volumeInfo;
    
    // Don't continue if the title or authors are missing or blank
    if(!isset($info->title) || !isset($info->authors)) return;
      
    $title = $info->title;
    $authors = $info->authors;
    $description = isset($info->description) ? explode("\n", $info->description)[0] : "";
    $publisher = isset($info->publisher) ? $info->publisher : "";
    $year = isset($info->publishedDate) ? $info->publishedDate : "";
    $pages = isset($info->pageCount) ? $info->pageCount : "";
    $googleID = isset($book->id) ? $book->id : "";
    
    // Title and authors can't be blank, but other fields can be
    if(!$title || !$authors) return;
    
    if(dbBooksAdd($dbConn, $isbn, $googleID, $title, $authors, $description, $publisher, $year, $pages)) {
      if(!$noverbose) echo 'Yes';
      return true;
    }
    return false;
  }

  // publicSearch({...})
  // Runs a search for a given value on a given field
  // Required fields:
  // * "column"
  // * "value"
  // This needs protection against SQL injections
  function publicSearch($arguments, $noverbose=false) {
    $dbConn = getPDOQuick();
    $value = '%' . ArgLoose($arguments['value']) . '%';
    
    // Prepare the initial query
    $query = ' SELECT * FROM `books` ';
    
    // Gather the required columns
    // $num_cols = (int) ArgStrict($arguments['num_cols']);
    // // (anything over 14 is ridiculous)
    // if($num_cols > 14) return;
    
    // // Add each of the columns into the search
    // for($i = 0; $i < $num_cols; ++$i) {
      // $column = ArgStrict($arguments[$i]);
      
      // $query .= ' WHERE ' . $column . ' LIKE :value ';
    // }
    
    $query .= ' WHERE `title` LIKE :value';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $durp = $stmnt->execute(array(':value' => $value));
    
    // Print the results out as HTML
    $results = $stmnt->fetchAll(PDO::FETCH_ASSOC);
    foreach($results as $result) {
      TemplatePrint("Books/Medium", 0, $result);
    }
  }

  // publicGetBookEntries({...})
  // Gets all entries for an isbn of the given action
  // Required fields:
  // * #isbn
  // * "action"
  function publicGetBookEntries($arguments, $noverbose=false) {
    $dbConn = getPDOQuick();
    $isbn = $arguments['isbn'];
    $action = $arguments['action'];
    
    // Prepare the initial query
    $query = '
      SELECT * FROM `entries`
      WHERE `isbn` LIKE :isbn
      AND `action` LIKE :action
    ';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $durp = $stmnt->execute(array(':isbn' => $isbn,
                                  ':action' => $action));
    
    // Return a JSON encoding of the results
    $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
  }
  
  // publicGetSIS
  // Simply outputs the HTTP page of the specified SIS page
  // No required fields!
  function publicGetSIS($arguments=false, $noverbose=false) {
    echo getHTTPPage(getSISAPI());
  }
  
  // publicBookImport({...})
  // Handler to go to ISBN or Full
  // Required fields:
  // * "type"
  function publicBookImport($arguments) {
    if(ArgStrict($arguments['type']) == 'full')
      return publicBookImportFull($arguments);
    else return publicBookImportISBN($arguments);
  }
  
  // publicBookImportISBN({...})
  // Goes through the motions of checking if an ISBN is in the database
  // If it isn't, it calls the function to add the book 
  // Required arguments:
  // * #isbn
  function publicBookImportISBN($arguments) {
    $isbn = ArgStrict($arguments['isbn']);
    
    // Make sure the ISBN is valid
    if(!(strlen($isbn) == 10 || strlen($isbn) == 13) || !is_numeric($isbn)) {
      echo 'Invalid ISBN given.';
      return;
    }
    
    // Does the ISBN exist?
    $dbConn = getPDOQuick();
    if(doesBookAlreadyExist($dbConn, $isbn))
      return;
    
    // Since it doesn't yet, attempt to add it
    $added = publicAddBook($arguments);
    
    // If that was successful, hooray!
    if($added) {
      echo '<aside class="success">ISBN ' . $isbn . ' was added to our database as ';
      echo getLinkHTML('book', getRowValue($dbConn, 'books', 'title', 'isbn', $isbn), array('isbn'=>$isbn));
      echo '</aside>';
    }
    // Otherwise nope
    else echo '<aside class="failure">ISBN ' . $isbn . ' returned no results.</aside>';
  }
  
  // publicBookImportFull({...})
  // Sends a request to the Google Books API for ISBNs
  // If it receives any, it attempts to add them to the database
  function publicBookImportFull($arguments) {
    $value = urlencode($arguments['value']);
    echo '<aside class="small">Results for ' . $value . '</aside>' . PHP_EOL;
    
    // Start the Google query
    $query = 'https://www.googleapis.com/books/v1/volumes?';
    // Add the search term
    $query .= 'q=' . $value;
    // Finish the query with the Google key
    $query .= '&key=' . getGoogleKey();
    
    // Run the query and get the results
    $result = json_decode(getHTTPPage($query));
    
    // Get the array of items, if it's found
    $items = followPath($result, ['items']);
    if(!$items) {
      echo 'Nothing found for "' . $value . '"';
      return;
    }
    
    // Since there are items, get their identifiers
    $dbConn = getPDOQuick();
    foreach($items as $item) {
      $identifiers = followPath($item, ['volumeInfo', 'industryIdentifiers']);
      if(!$identifiers) continue;
      
      // Using all the ISBN_13 identities...
      foreach($identifiers as $identity)
        if($identity->type == "ISBN_13" || $identity->type == "ISBN")
          // If it's successfully added, continue to the next item
          if(bookImportFullCheck($dbConn, $identity->identifier))
            continue;
    }
  }
  // Real function to add a book, if the ISBN isn't already there
  function bookImportFullCheck($dbConn, $isbn) {
    // Make sure the book doesn't already exist
    if(doesBookAlreadyExist($dbConn, $isbn))
      return;
      
    // Since it doesn't, call Google to add it
    if(publicAddBook(array('isbn'=>$isbn), true)) {
      echo '<aside class="success">' . getLinkHTML('book', getRowValue($dbConn, 'books', 'title', 'isbn', $isbn), array('isbn'=>$isbn)) . ' added</aside>';
    }
    else echo '<aside class="failure">' . $isbn . ' not added</aside>';
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
  
  // publicSearchFull({...})
  // Performs a full search of the book database
  // Required arguments:
  // * 'title'
  // * 'authors'
  // * 'description'
  // * 'year'
  // * 'isbn'
  // * 'publisher'
  function publicSearchFull($arguments) {
    $dbConn = getPDOQuick();
    $terms = array('title', 'authors', 'description', 'year', 'isbn', 'publisher');
    $parsed = array();
    
    $title = check_arg($arguments['search_input_title']);
    $authors = check_arg($arguments['search_input_authors']);
    $description = check_arg($arguments['search_input_description']);
    $year = check_arg($arguments['search_input_year']);
    $isbn = check_arg($arguments['search_input_isbn']);
    $publisher = check_arg($arguments['search_input_publisher']);
    $vals = [$title, $authors, $description, $year, $isbn, $publisher];
    
    $query = 'SELECT * ' . PHP_EOL;
    $query .= ' FROM `books` WHERE' . PHP_EOL;
    $filters = array();
    $settings = array();
    foreach($terms as $key=>$term) {
      if($vals[$key] != '%') {
        $filters[] =  ' `' . $term . '` LIKE :' . $term;
        $settings[$term] = '%' . $vals[$key] . '%';
      }
    }
    $query .= implode(' AND ' . PHP_EOL, $filters);
    
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute($settings);
                          
    $results = $stmnt->fetchAll(PDO::FETCH_ASSOC);
    $parsed = json_encode($results);
    echo $parsed;
  }
  function check_arg($value) {
    if(!$value || $value == '')
      return '%';
    return $value;
  }

  // publicPrintUserBooks({...})
  // Prints the formatted displays of the books on a user's list
  // Required arguments:
  // * #user_id
  // * 'format' (small, medium, large)
  // * 'action' (buy, sell)
  function publicPrintUserBooks($arguments, $noverbose=false) {
    $user_id = ArgStrict($arguments['user_id']);
    $format = ArgStrict($arguments['format']);
    $action = ArgStrict($arguments['action']);
    $dbConn = getPDOQuick();
    
    // Get each of the entries of that type
    $entries = dbEntriesGet($dbConn, $user_id, $action);
    
    // If there were none, stop immediately
    if(!$entries) {
      if(!$noverbose)
        echo '<aside>No results were found for this user!</aside>';
      return;
    }
    
    // For each one, query the book information
    $results = [];
    foreach($entries as $key=>$entry) {
      $results[$key] = dbBooksGet($dbConn, $entry['isbn']);
    }
    
    // If there weren't any results, complain if wanted
    if(!$results) {
      if(!$noverbose) {
        echo 'Nothing going!' . PHP_EOL;
        echo '<p>Perhaps you\'d like to <a href="">add more?</a></p>' . PHP_EOL;
      }
      return;
    }
    
    // For each of those books, print the sized format
    foreach($results as $result)
      TemplatePrint("Books/" . $format, 0, $result);
  }
  
  // publicPrintRecentListings({...})
  // Prints the site listings, in chronological order of most-recent-first
  // Optionally filters them by an identifier
  // Optional arguments:
  // * "identifier"
  // * "isbn"
  function publicPrintRecentListings($arguments) {
    // Check if there's an identifier
    if(isset($arguments['identifier'])) {
      $identifier = $arguments['identifier'];
      $isbn = $arguments['isbn'];
    }
    else $identifier = $isbn = false;
    
    // Get each of the recent entries
    $dbConn = getPDOQuick();
    $entries = dbEntriesGetRecent($dbConn, $identifier, $isbn);
    
    // If there are any, for each of those entries, print them out
    if(count($entries))
      foreach($entries as $entry)
        TemplatePrint("Entry", 0, $entry);
    else
      echo "nothing going!";
  }

  // publicEntryAdd({...})
  // Adds an entry regarding the book for the current user
  // Required arguments:
  // * "isbn"
  // * "action"
  // * "dollars"
  // * "cents"
  // * "state"
  function publicEntryAdd($arguments) {
    // Make sure there's a user, and get that user's info
    if(!UserLoggedIn()) {
      echo 'You must be logged in to add an entry.';
      return false;
    }
    
    // Fetch the necessary arguments
    $dbConn = getPDOQuick();
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    // (many from $arguments)
    $isbn = ArgStrict($arguments['isbn']);
    $action = ArgStrict($arguments['action']);
    $dollars = ArgStrict($arguments['dollars']);
    $cents = ArgStrict($arguments['cents']);
    $state = ArgStrict($arguments['state']);
    // (price is dollars + cents)
    $price = $dollars . '.' . $cents;
    
    // Send the query
    if(dbEntriesAdd($dbConn, $isbn, $user_id, $username, $action, $price, $state))
      echo 'Entry added successfully!';
  }
  
  // publicPrintRecommendationsDatabase({...})
  // Finds and prints all matching entries for a given user
  // Required arguments:
  // * #user_id
  function publicPrintRecommendationsDatabase($arguments) {
    $dbConn = getPDOQuick();
    $user_id = ArgStrict($arguments['user_id']);
    
    // Prepare the query
    // http://stackoverflow.com/questions/5505244/selecting-matching-mutual-records-in-mysql/5505280#5505280
    $query = '
      SELECT DISTINCT a.*
      FROM `entries` a
      # matching rows in entries against themselves
      INNER JOIN `entries` b
      # where ISBNs are the same, but users are not
      ON a.isbn = b.isbn
      AND b.user_id != a.user_id
      # and filter for not the given user
      WHERE a.user_id != :user_id
    ';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id));
    
    // Get the results to print them out
    $results = $stmnt->fetchAll(PDO::FETCH_ASSOC);
    if(empty($results))
      echo 'Nothing going!';
    else
      foreach($results as $result)
        TemplatePrint("Entry", 0, $result);
  }
  
  // publicPrintRecommendationsUser({...})
  // Finds and prints all matching entries between two users
  // Required arguments:
  // * #user_id_a
  // * #user_id_b
  function publicPrintRecommendationsUser($arguments) {
    $dbConn = getPDOQuick();
    $user_id_a = ArgStrict($arguments['user_id_a']);
    $user_id_b = ArgStrict($arguments['user_id_b']);
    
    // Prepare the query
    // http://stackoverflow.com/questions/5505244/selecting-matching-mutual-records-in-mysql/5505280#5505280
    $query = '
      SELECT DISTINCT a.*
      FROM `entries` a
      # matching rows in entries against themselves
      INNER JOIN `entries` b
      # where ISBNs are the same
      ON a.isbn = b.isbn
      # and filter on the users
      WHERE a.user_id LIKE :user_id_a
      AND b.user_id LIKE :user_id_b
    ';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id_a' => $user_id_a,
                          ':user_id_b' => $user_id_b));
    
    // Get the results to print them out
    $results = $stmnt->fetchAll(PDO::FETCH_ASSOC);
    if(empty($results))
      echo 'Nothing going!';
    else
      foreach($results as $result)
        TemplatePrint("Entry", 0, $result);
  }
?>
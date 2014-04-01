<?php
  /* Functions the public may access via requests.js -> requests.php
  */
  require_once('pdo.inc.php');
  require_once('sql.inc.php');
  require_once('db_actions.php');
  require_once('notifications.php');
  
  /* Helper functions to ensure argument safety
  */
  function ArgStrict($arg) {
    return preg_replace("/[^A-Za-z0-9 ]/", '', ArgLoose($arg));
  }
  function ArgLoose($arg) {
    return strip_tags($arg);
  }
  
  // publicCreateUser({...})
  // Public pipe to dbUsersAdd("username", "password")
  // Required fields:
  // * "username"
  // * "password"
  // * "email"
  function publicCreateUser($arguments, $noverbose=false) {
    $dbConn = getPDOQuick($arguments);
    $username = ArgLoose($arguments['j_username']);
    $password = $arguments['j_password'];
    $email = $arguments['j_email'];
    
    // Make sure the arguments aren't blank
    if(!$username || !$password || !$email) {
      echo 'Make sure to fill out all the fields!';
      return false;
    }
    
    // The password must be secure
    if(!isPasswordSecure($password)) {
      echo 'Your password isn\'t secure enough.';
      return false;
    }
    
    // The email must be an academic email
    if(!isStringEmail($email)) {
      if(!$noverbose) echo 'That email isn\'t an actual email! What are you doing, silly?';
      return false;
    }
    if(!isEmailAcademic($email)) {
      if(!$noverbose) echo 'Sorry, right now we\'re only allowing school emails. Please use a .edu email address.';
      return false;
    }
    
    // Also make sure that email isn't taken
    if(checkKeyExists($dbConn, 'users', 'email', $email)) {
      if(!$noverbose) echo 'The email \'' . $email . '\' is already taken :(';
      return false;
    }

    // If successful, log in
    if(dbUsersAdd($dbConn, $username, $password, $email)) {
      $arguments['username'] = $arguments['j_username'];
      $arguments['password'] = $arguments['j_password'];
      $arguments['email'] = $arguments['j_email'];
      publicLogin($arguments, true);
      if(!$noverbose)
        echo 'Yes';
      return true;
    }
    return false;
  }
  
  // publicResendVerificationEmail({...})
  // Sends an email 
  function publicResendVerificationEmail($arguments, $noverbose = false) {
    // Anonymous users don't need this
    if(!UserLoggedIn()) {
      if(!$noverbose) 
        echo 'You must be logged in.';
      return false;
    }
    // Neither do fully verified users
    if(UserVerified()) {
      if(!$noverbose)
        echo 'You\'re already verified!';
      return false;
    }
    
    // Get the user's verification code from the arguments, or failing that the database
    $dbConn = getPDOQuick($arguments);
    if(isset($arguments['code']))
      $code = $arguments['code'];
    else
      $code = getRowValue($dbConn, 'user_verifications', 'code', 'user_id', $_SESSION['user_id']);
    
    // If it doesn't exist, complain
    if(!$code) {
      if(!$noverbose)
        echo 'Couldn\'t find an entry for your verification...';
      return false;
    }
    
    // Send out the code as an email
    $username = $_SESSION['username'];
    $email = $_SESSION['email'];
    $recipient = '<' . $username . '> ' . $email;
    $subject = 'BookSwap Verification Time!';
    $message  = 'Hi there, ' . $username . '!' . PHP_EOL . PHP_EOL;
    $message .= 'Someone (hopefully you) made an account on ' . getSiteName() . '. If that\'s you, great! ';
    $message .= 'Visit this link to verify your account: ';
    $message .= getURL('verification') . '&user_id=' . $_SESSION['user_id'] . '&code=' . $code . PHP_EOL;
    $message .= 'If this wasn\'t you, don\'t worry about it.' . PHP_EOL . PHP_EOL;
    $message .= 'Cheers,' . PHP_EOL;
    $message .= '   -The BookSwap team';
    $status = mailFancy($recipient, $subject, $message); 
    
    if(!$noverbose)
      echo $status ? 'Yes' : 'Could not send verification email! Please try again.';
    return $status;
  }
  
  // publicSendWelcomeEmail({...})
  // Also sends an email...
  function publicSendWelcomeEmail($arguments, $noverbose=false) {
    $username = $_SESSION['username'];
    $email = $_SESSION['email'];
    $recipient = '<' . $username . '> ' . $email;
    $subject = 'BookSwap Verification Time!';
    $message  = 'Congratulations are in order, ' . $username . '!' . PHP_EOL . PHP_EOL;
    $message .= 'Your account on ' . getSiteName() . ' is now active. Go on and swap some books!' . PHP_EOL;
    $message .= '   -The BookSwap team';
    $status = mailFancy($recipient, $subject, $message); 
    
    if(!$noverbose)
      echo $status ? 'Yes' : 'Could not send welcome email! Please try again.';
    return $status;
  }
  
  // publicVerifyUser({...})
  // 
  // Required Arguments:
  // * nope
  function publicVerifyUser($arguments, $noverbose=false) {
    // The user must be authenticated, and logging into their own thing
    if(!UserLoggedIn()) {
      if(!$noverbose)
        echo 'You must be logged in to do this';
      return false;
    }
    $user_id = $arguments['user_id'];
    $code = $arguments['code'];

    // For security's sake, make sure the given user_id matches $_SESSION
    if($_SESSION['user_id'] != $user_id) {
      if(!$noverbose)
        echo 'The given user ID doesn\'t match the current user\'s';
      return false;
    }
    
    // Get the corresponding user's code from the database
    $dbConn = getPDOQuick($arguments);
    $code_actual = getRowValue($dbConn, 'user_verifications', 'code', 'user_id', $user_id);
    
    // If it doesn't match, complain
    if($code != $code_actual) {
      if(!$noverbose)
        echo 'Invalid code provided! Please try again.';
      print_r($arguments);
      echo PHP_EOL . '<br>' . PHP_EOL;
      echo PHP_EOL . '<br>' . PHP_EOL;
      print_r($_SESSION);
      return false;
    }
    
    // Otherwise, clear the verification and set the user role to normal
    setRowValue($dbConn, 'users', 'role', 'User', 'user_id', $user_id);
    $_SESSION['role'] = 'User';
    dbUserVerificationDeleteCode($dbConn, $user_id, true);
    
    if(!$noverbose)
      echo 'Yes';
    return true;
  }
  
  // publicLogin({...})
  // Public pipe to loginAttempt("username", "password")
  // Required fields:
  // * "username"
  // * "password"
  function publicLogin($arguments, $noverbose=false) {
    $dbConn = getPDOQuick($arguments);
    $email = $arguments['email'];
    $password = $arguments['password'];
    if(loginAttempt($dbConn, $email, $password) && !$noverbose) {
      echo 'Yes';
      return true;
    }
    return false;
  }
  
  // publicFacebookLogin({...})
  // 
  // Required fields:
  //  * "email"
  //  * "name"
  //  * "fb_id" 
  function publicFacebookLogin($arguments, $noverbose=false) {
	  $dbConn = getPDOQuick($arguments);
	  $email = $arguments['email'];
	  $username = $arguments['name'];
	  $fb_id = $arguments['fb_id'];
	  
	  // Attempt to log in with Facebook normally
	  $user_info = facebookLoginAttempt($dbConn, $fb_id);
    
    // If it didn't work, try to add the user, then log in again
	  if(!$user_info){
		  dbFacebookUsersAdd($dbConn, $username, $fb_id, $email, 0);
		  $user_info = facebookLoginAttempt($dbConn, $fb_id);
	  }
	  
    // If it didn't work (couldn't login or register), complain
	  if(!$user_info) {
      echo 'Could not log in...';
		  return false;
    }

    // Otherwise it's good
	  if(!$noverbose) {
		  echo 'Yes';
    }
    return true;
  }


  // publicEditUsername({...})
  // Edits the current user's username, and updates all related entries
  // Required fields:
  // * "username" (new value)
  function publicEditUsername($arguments, $noverbose=false) {
    // Make sure you're logged in
    if(!UserLoggedIn()) {
      if(!$noverbose) echo 'You must be logged in to edit a username.';
      return false;
    }
    $user_id = $_SESSION['user_id'];
    $username_old = $_SESSION['username'];
    $username_new = ArgLoose($arguments['value']);
    
    if(!$username_new || strlen($username_new) < 1) {
      echo "Invalid username given... :(\n";
      return false;
    }
    
    // Don't do anything if it's the same as before
    if($username_new == $username_old) {
      echo "Same username as before... :(\n";
      return false;
    }
    
    // Replace the user's actual username
    $dbConn = getPDOQuick($arguments);
    dbUsersRename($dbConn, $user_id, $username_new, 'user_id');
    
    // Reset the $_SESSION username to be that of the database's
    $_SESSION['username'] = getRowValue($dbConn, 'users', 'username', 'user_id', $user_id);
  }
  
  // publicAddBook({...})
  // Gets the info on a book from the Google API, then pipes it to dbBooksAdd
  // Required fields:
  // * "isbn"
  // https://developers.google.com/books/docs/v1/using
  // https://www.googleapis.com/books/v1/volumes?q=isbn:9780073523323&key=AIzaSyD2FxaIBhdLTA7J6K5ktG4URdCFmQZOCUw
  function publicAddBook($arguments, $noverbose=false) {
    $dbConn = getPDOQuick($arguments);
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
  // * "value"
  // Optional fields:
  // * "column"
  // * "format"
  // * "offset"
  function publicSearch($arguments, $noverbose=false) {
    $dbConn = getPDOQuick($arguments);
    $value_raw = ArgLoose($arguments['value']);
    $value = '%' . str_replace(' ', '%', $value_raw) . '%';
    $format = isset($arguments['format']) ? ArgStrict($arguments['format']) : 'Medium';
    
    // The user may give a different column to search on
    if(isset($arguments['column']))
      $column = strtolower(ArgStrict($arguments['column']));
    else $column = 'title';
    
    // Same witha an offset
    if(isset($arguments['offset']))
      $offset = (int) ArgStrict($arguments['column']);
    else $offset = 0;
    
    // Prepare the initial query
    $query = '
      SELECT * FROM `books` 
      WHERE `' . $column . '` LIKE :value
      LIMIT 7 OFFSET ' . $offset . '
    ';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $durp = $stmnt->execute(array(':value'  => $value));
    
    // Print the results out as HTML
    $results = $stmnt->fetchAll(PDO::FETCH_ASSOC);
    foreach($results as $result) {
      $result['is_search'] = true;
      TemplatePrint('Books/' . $format, 0, $result);
    }
    echo '<div class="search_end book">search on ';
    echo getLinkHTML('search', $value_raw, array('value'=>$value_raw));
    echo ': ' . count($results) . ' results ' . ($results ? 'shown' : 'found');
    if($offset) echo ' (starting from ' . ($offset + 1) . ')';
    echo '.';
  }

  // publicGetBookEntries({...})
  // Gets all entries for an isbn of the given action
  // Required fields:
  // * #isbn
  // * "action"
  function publicGetBookEntries($arguments, $noverbose=false) {
    $dbConn = getPDOQuick($arguments);
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
    $dbConn = getPDOQuick($arguments);
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
    $dbConn = getPDOQuick($arguments);
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
    $dbConn = getPDOQuick($arguments);
    
    // Get each of the entries of that type
    $entries = dbEntriesGet($dbConn, $user_id, $action);
    
    // If there were none, stop immediately
    if(!$entries) {
      if(!$noverbose)
        echo '<aside>Nothing going!</aside>';
        echo '<p>Perhaps you\'d like to ' . getLinkHTML('search', 'add more') . '?</p>' . PHP_EOL;
      return;
    }
    
    // For each one, query the book information, and print it out
    foreach($entries as $key=>$entry) {
      $results[$key] = dbBooksGet($dbConn, $entry['isbn']);
      TemplatePrint("Books/" . $format, 0, array_merge($entry, $results[$key]));
    }
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
    $dbConn = getPDOQuick($arguments);
    $entries = dbEntriesGetRecent($dbConn, $identifier, $isbn);
    
    // If there are any, for each of those entries, print them out
    if(count($entries))
      foreach($entries as $entry)
        TemplatePrint("Entry", 0, $entry);
    else
      echo "nothing going!";
  }

  // publicEntryAdd({...})
  // Adds an entry regarding a book for the current user
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
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    $dbConn = getPDOQuick($arguments);
    
    // Fetch the necessary arguments
    $isbn = ArgStrict($arguments['isbn']);
    $action = ArgStrict($arguments['action']);
    $dollars = ArgStrict($arguments['dollars']);
    $cents = ArgStrict($arguments['cents']);
    $state = ArgStrict($arguments['state']);
    // (price is dollars + cents)
    $price = $dollars . '.' . $cents;
    
    // Send the query
    $entry_id = dbEntriesAdd($dbConn, $isbn, $user_id, $action, $price, $state);
    if($entry_id !== false) {
      sendAllEntryNotifications($dbConn, $entry_id);
      echo 'Entry added successfully!';
    }
  }
  
  // publicEntryEditPrice({...})
  // Edits an entry price regarding a book for the current user
  // Required arguments:
  // * "isbn"
  // * "action"
  // * "dollars"
  // * "cents"
  function publicEntryEditPrice($arguments) {
    // Make sure there's a user, and get that user's info
    if(!UserLoggedIn()) {
      echo 'You must be logged in to add an entry.';
      return false;
    }
    $user_id = $_SESSION['user_id'];
    $dbConn = getPDOQuick($arguments);
    
    // Fetch the necessary arguments
    $isbn = ArgStrict($arguments['isbn']);
    $action = ArgStrict($arguments['action']);
    $dollars = ArgStrict($arguments['dollars']);
    $cents = ArgStrict($arguments['cents']);
    // (price is dollars + cents)
    $price = $dollars . '.' . $cents;
    
    // Send the query
    if(dbEntriesEditPrice($dbConn, $isbn, $user_id, $action, $price))
      echo 'Entry edited successfully!';
  }
  
  // publicEntryDelete({...})
  // Removes an entry regarding a book for the current user
  // Required arguments:
  // * "isbn"
  // * "action"
  function publicEntryDelete($arguments) {
    // Make sure there's a user, and get that user's info
    if(!UserLoggedIn()) {
      echo 'You must be logged in to delete an entry.';
      return false;
    }
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    $dbConn = getPDOQuick($arguments);
    
    // Fetch the necessary argument
    $isbn = ArgStrict($arguments['isbn']);
    $action = ArgStrict($arguments['action']);
    
    // Send the query and print the results
    $link = getLinkHTML('book', $isbn, array('isbn'=>$isbn));
    if(dbEntriesRemove($dbConn, $isbn, $user_id))
      echo $link . ' removed successfully!';
    else echo $link . ' removal failed, refresh and try again?';
  }
  
  // publicPrintRecommendationsDatabase({...})
  // Finds and prints all matching entries for a given user
  // Required arguments:
  // * #user_id
  function publicPrintRecommendationsDatabase($arguments) {
    $dbConn = getPDOQuick($arguments);
    $user_id = ArgStrict($arguments['user_id']);
    
    // Prepare the query
    // http://stackoverflow.com/questions/5505244/selecting-matching-mutual-records-in-mysql/5505280#5505280
    // http://stackoverflow.com/questions/16490120/select-from-same-table-where-two-columns-match-and-third-doesnt
    $query = '
      SELECT * FROM (
        SELECT a.* FROM
        `entries` a
        # matching rows in entries against themselves
        INNER JOIN `entries` b
        # not from the given user; ISBNs are the same, but users and actions are not
        ON  a.user_id <> :user_id
        AND a.isbn = b.isbn
        AND b.user_id = :user_id
        AND a.action <> b.action
      ) AS matchingEntries
      # while the above alias is not used, MySQL requires all derived tables to
      # have an alias
      NATURAL JOIN `books` NATURAL JOIN `users`
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
    $dbConn = getPDOQuick($arguments);
    $user_id_a = ArgStrict($arguments['user_id_a']);
    $user_id_b = ArgStrict($arguments['user_id_b']);
    
    // Prepare the query
    // http://stackoverflow.com/questions/5505244/selecting-matching-mutual-records-in-mysql/5505280#5505280
    $query = '
      SELECT * FROM (
        SELECT a.*
        FROM `entries` a
        # matching rows in entries against themselves
        INNER JOIN `entries` b
        # where ISBNs are the same, and the two user_ids match
        ON a.isbn = b.isbn
        AND a.user_id LIKE :user_id_a
        AND b.user_id LIKE :user_id_b
      ) AS matchingEntries
      # while the above alias is not used, MySQL requires all derived tables to
      # have an alias
      NATURAL JOIN `books` NATURAL JOIN `users`
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
        TemplatePrint('Entry', 0, $result);
  }
  
  // publicGetNumNotifications()
  // Returns the number of notifications the current user has, or -1 if the user is logged out
  function publicGetNumNotifications($arguments=[]) {
    $count = -1;
    if(UserLoggedIn()){
      $dbConn = getPDOQuick($arguments);
      $count = dbNotificationsCount($dbConn, $_SESSION['user_id']);
    }
    return $count;
  }
  
  // publicPrintNotifications() {
  // Finds and prints all notifications of the current user
  function publicPrintNotifications($arguments=[]) {
    $dbConn = getPDOQuick($arguments);
    $result = dbNotificationsGet($dbConn, $_SESSION['user_id']);
    if(empty($result))
      echo 'Nothing going!';
    else
      foreach($result as $notification)
        TemplatePrint('Notification', 0, $notification);
  }
  
  // publicDeleteNotification
  // Deletes a notification of the given id, if it belongs to the current user
  function publicDeleteNotification($arguments=[]) {
    if(!UserLoggedIn()) return;
    $dbConn = getPDOQuick($arguments);
    dbNotificationsRemove($dbConn, $arguments['notification_id']);
  }
?>

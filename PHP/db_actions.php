<?php
  /* Back-end actions the user is allowed to do
  */
  
  /* User Functions
  */
  
  // dbUsersGet("identity"[, "type"])
  // Gets the user of the given identity type (by default, username)
  // Sample usage: dbUsersGet($dbConn, $username, "username");
  function dbUsersGet($dbConn, $identity, $type='username', $noverbose=false) {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', $type, $identity)) {
      if(!$noverbose)
        echo 'No such ' . $type . ' exists: ' . $identity;
      return false;
    }
    
    // Prepare the initial query
    $query = '
      SELECT * FROM `users`
      WHERE `' . $type . '` = :identity
      LIMIT 1
    ';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':identity' => $identity));
    
    // Get the results
    return $stmnt->fetch();
  }
  
  // dbFacebookUsersGet("facebook ID", ...)
  // Gets the user info of a Facebook account
  // Sample usage: dbFacebookUsersGet($dbConn, $identity)
  function dbFacebookUsersGet($dbConn, $identity, $noverbose=false) {
	  // Prepare the query
	  $query = '
      SELECT * FROM `users`
        INNER JOIN `FacebookUsers`
      ON `FacebookUsers`.`user_id` = `users`.`user_id`
      WHERE `FacebookUsers`.`fb_id` = :identity
      LIMIT 1
	  ';
	  
	  // Run the query
	  $stmnt = getPDOStatement($dbConn, $query);
	  $stmnt->execute(array(':identity' => $identity));
	  return $stmnt->fetch();
  }

  // dbUsersAdd("username", "password", "email", #role)
  // Adds a user to `users`
  // Sample usage: dbUsersAdd($dbConn, $username, $password, $email, $role);
  function dbUsersAdd($dbConn, $username, $password, $email, $role='Unverified') {
    // Ensure the email isn't already being used
    if(checkKeyExists($dbConn, 'users', 'email', $email)) {
      echo $email . ' is already being used.';
      return false;
    }
    
    // If there's a password, create the salts
    if(!empty($password)){
      $salt = hash('sha256', uniqid(mt_rand(), true));
      $salted = hash('sha256', $salt . $password);
    }
    // No password means an alternate authenticated method (e.g. Facebook)
    else{
      $salt = "";
      $salted = "";
    }
    
    // Run the insertion query
    $query = '
      INSERT INTO  `users` (
        `username`, `password`, `email`, `role`, `salt`
      )
      VALUES (
        :username, :password, :email, :role, :salt
      )
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':username' => $username,
                          ':password' => $salted,
                          ':email'    => $email,
                          ':role'     => $role,
                          ':salt'     => $salt));
    
    // If unverified, also add an entry to `user_verifications`
    if(empty($role) || !$role || strcasecmp($role, 'unverified') == 0) {
      // First get the user_id 
      $user_id = getRowValue($dbConn, 'users', 'user_id', 'email', $email);
      
      // Run the query
      $query = '
        INSERT INTO `user_verifications` (
          `user_id`, `code`
          )
          VALUES (
            :user_id,  :code
          );
      ';
      $code = hash('sha256', uniqid(mt_rand(), true));
      $stmnt = getPDOStatement($dbConn, $query);
      $stmnt->execute(array(':user_id' => $user_id,
                            ':code'    => $code));
      
      // With this done, send a verification email 
      $arguments = array(
        'dbConn' => $dbConn,
        'code'   => $code,
      );
      publicResendVerificationEmail($arguments, true);
    }
    
    return true;
  }
  
  // dbFacebookUsersAdd("username", "facebook ID", "email", #role);
  // Adds a user to `users` and `FacebookUsers` using dbUsersAdd
  //	* Will not work if a user is in `users` with the same email
  //	* Will not work if a user is in `FacebookUsers` with
  //      the same user id or facebook ID
  function dbFacebookUsersAdd($dbConn, $username, $fb_id, $email, $role){
    // If adding the user to the database normally failed, stop
	  if(!dbUsersAdd($dbConn, $username, "", $email, $role)){
		  // email already exists in database
		  // have to handle merging of accounts
		  return false;
	  }
	  
	  $user_info = dbUsersGet($dbConn, $email, "email");
	  if(!$user_info) 
		return false; // dbUsersAdd didn't work?
		
	  $query = '
      INSERT INTO `FacebookUsers` (
        `fb_id`, `user_id`
      )
      VALUES (
        :fb_id, :user_id
      );
		';
	  $stmnt = getPDOStatement($dbConn, $query);
	  return $stmnt->execute(array(':fb_id'   => $fb_id,
                                 ':user_id' => $user_info['user_id']));
  }
  
  // dbUsersRemove("identity"[, "type"])
  // Removes a user from `users` of the given identity (by default, username)
  // Sample usage: dbUsersRemove($dbConn, $username, "username");
  function dbUsersRemove($dbConn, $identity, $type='username') {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', $type, $identity)) {
      echo 'No such ' . $type . ' exists: ' . $identity;
      return false;
    }
    
    // Run the deletion query
    $query = '
      DELETE FROM `users`
      WHERE `' . $type . '` = :identity
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':identity' => $identity));
  }
  
  // dbUsersRemove("identity", "username_new"[, "type"])
  // Renames a user from `users` of the given identity (by default, user_id)
  // Sample usage: dbUsersRename($dbConn, $user_id, $username_new, "user_id");
  function dbUsersRename($dbConn, $identity, $username_new, $type='user_id') {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', $type, $identity)) {
      echo 'No such ' . $type . ' exists: ' . $identity;
      return false;
    }
    
    // Run the rename query
    $query = '
      UPDATE `users` SET
      `username` =  :username_new
      WHERE `' . $type . '` = :identity
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':username_new' => $username_new,
                                 ':identity'     => $identity));
  }
  
  
  /* Book Functions
  */
  
  // dbBooksGet(#isbn)
  // Gets information on a book of the given ISBN
  // Sample usage: dbBooksGet($dbConn, $isbn);
  function dbBooksGet($dbConn, $isbn, $noverbose=false) {
    // Ensure the isbn exists in the database
    if(!checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
      if(!$noverbose) echo 'No such isbn exists: ' . $isbn;
      return false;
    }
    
    // Prepare the initial query
    $query = '
      SELECT * FROM `books`
      WHERE `isbn` = :isbn
    ';
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':isbn' => $isbn));
    
    // Get the results
    return $stmnt->fetch(PDO::FETCH_ASSOC);
  }
  
  // dbBooksAdd(#isbn, #googleID, "title", "authors", "description", "publisher", "year", "pages")
  // Adds a book to `books`
  // Authors may be given as an array or string (separated by endlines)
  // Sample usage: dbBooksAdd($dbConn, $googleID, $isbn, $title, $authors, $genre);
  function dbBooksAdd($dbConn, $isbn, $googleID, $title, $authors, $description, $publisher, $year, $pages) {
    // Ensure the isbn doesn't already exist
    if(checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
      echo 'The ISBN already exists: ' . $isbn;
      return false;
    }
    
    // Convert the $authors argument if needed
    if(is_array($authors))
      $authors = implode($authors, '\n');
    
    // Make sure $year only contains the 4-digit year string
    if(strlen($year) > 4) {
      $year = explode("-", $year);
      foreach($year as $sub)
        if(strlen($sub) == 4) {
            $year = $sub;
            break;
        }
    }
    
    // Run the insertion query
    $query = '
      INSERT INTO  `books` (
        `isbn`, `google_id`, `title`, `authors`, `description`, `publisher`, `year`, `pages`
      )
      VALUES (
        :isbn, :google_id,  :title, :authors, :description, :publisher, :year, :pages
      )
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':isbn'        => $isbn,
                                 ':google_id'   => $googleID,
                                 ':title'       => $title,
                                 ':authors'     => $authors,
                                 ':description' => $description,
                                 ':publisher'   => $publisher,
                                 ':year'        => $year,
                                 ':pages'       => $pages));
  }
  
  // (missing Remove)
  
  
  /* Entries Functions
  */
  
  // dbEntriesGet(#user_id[, "action"])
  // Gets all entries of a given user (optionally, of a given action)
  // Sample usage: dbEntriesGet($dbConn, $user_id);
  function dbEntriesGet($dbConn, $user_id, $action='') {
    // Ensure the user_id exists in the database
    if(!checkKeyExists($dbConn, 'users', 'user_id', $user_id)) {
      echo 'No such user exists: ' . $user_id;
      return false;
    }
    
    // Prepare the initial query and the initial arguments
    $query = '
      SELECT * FROM `entries`
      WHERE `user_id` = :user_id
    ';
    // Add in the extra filter, if needed
    if($action != '') {
      $query .= ' AND `action` = :action';
      $args = array(':user_id' => $user_id,
                    ':action'  => $action);
    }
    else $args = array(':user_id' => $user_id);
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute($args);
    
    return $stmnt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  // dbEntriesGetRecent([, "identifier", "value"][, #limit])
  // Gets the most recent entries, sorted most-recent-first
  // Sample usage: 
  // * dbEntriesGetRecent($dbConn);
  // * dbEntriesGetRecent($dbConn, "user_id", 7);     // only from user_id 7
  // * dbEntriesGetRecent($dbConn, 21);               // limit to 21 results
  // * dbEntriesGetRecent($dbConn, "user_id", 7, 21); // combine the two
  function dbEntriesGetRecent($dbConn, $identifier=false, $value, $limit = 0) {
    // Prepare the initial query
    $query = ' SELECT * FROM `entries` NATURAL JOIN `users` NATURAL JOIN `books`';
    $args = [];
    
    // Add in the extra filter, if needed
    if(is_string($identifier)) {
      $query .= ' WHERE `' . $identifier . '` = :value';
      $args[':value'] = $value;
    }
    // Else check for a limit given instead of $identifier, if needed
    else if(!$limit && is_numeric($identifier)) 
      $limit = (int) $identifier;
    
    // Set the ordering as most-recent-first
    $query .= ' ORDER BY `timestamp` DESC';
    
    // Set the limit, if it was specified
    if($limit) {
      $query .= ' LIMIT :limit';
      $args[':limit'] = $limit;
    }
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute($args);
    
    return $stmnt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  // dbBookEntriesGet(#isbn)
  // Gets all the entires related to an isbn (rather than user_id)
  function dbBookEntriesGet($dbConn, $isbn) {
    $query = '
      SELECT * FROM `entries`
      WHERE `isbn` = :isbn
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':isbn' => $isbn));
    
    return $stmnt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  // Sample usage: dbEntriesAdd(#isbn, #user_id, "username", "action"[, #price[, "state"]])
  // Adds an entry to `entries`
  // Returns the id of the entry on success, `false` on failure
  // Sample usage: dbEntriesAdd($dbConn, $isbn, $user_id, 'Buy', 12.34, 'Good');
  function dbEntriesAdd($dbConn, $isbn, $user_id, $action, $price=0, $state='Good') {
    // Ensure the isbn and user_id both exist in the database
    if(!checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
      echo 'No such ISBN exists: ' . $isbn;
      return false;
    }
    if(!checkKeyExists($dbConn, 'users', 'user_id', $user_id)) {
      echo 'No such user exists: ' . $user_id;
      return false;
    }
    
    // Make sure an entry doesn't already exist of this type
    $query = '
      SELECT * FROM `entries`
      WHERE `isbn` LIKE :isbn
      AND `user_id` LIKE :user_id
      AND `action` LIKE :action
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':isbn'    => $isbn,
                          ':user_id' => $user_id,
                          'action'   => $action));
    $results = $stmnt->fetch(PDO::FETCH_ASSOC);
    if(!empty($results)) {
      echo 'You already have an entry to ' . $action . ' this book!';
      return false;
    }
    
    // Query more information on the book (really just the title)
    $book_title = getRowValue($dbConn, 'books', 'title', 'isbn', $isbn);
    
    // Run the insertion query
    $query = '
      INSERT INTO `entries` (
        `isbn`, `user_id`, `price`, `state`, `action`
      ) VALUES (
        :isbn, :user_id, :price, :state, :action
      )
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    if ($stmnt->execute(array(
      ':isbn'      => $isbn,
      ':user_id'   => $user_id,
      ':price'     => $price,
      ':state'     => $state,
      ':action'    => $action
    ))) {
      return $dbConn->lastInsertId();
    } else {
      return false;
    }
  }
  
  // dbEntriesEditPrice(#isbn, #user_id, "action", #price)
  // Edits a pre-existing entry's price in `entries`, selected by isbn, user_id, and action
  // Sample usage: dbEntriesEditPrice($dbConn, $isbn, $user_id, 'buy', 14);
  function dbEntriesEditPrice($dbConn, $isbn, $user_id, $action, $price) {
    // Ensure the isbn and user_id both exist in the database
    if(!checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
      echo 'No such ISBN exists: ' . $isbn;
      return false;
    }
    if(!checkKeyExists($dbConn, 'users', 'user_id', $user_id)) {
      echo 'No such user exists: ' . $user_id;
      return false;
    }
    
    echo "Price is " . $price;
    
    // Run the edit query
    $query = '
      UPDATE `entries`
      SET `price` = :price
      WHERE
        `isbn` = :isbn 
          AND
        `action` = :action
    ';
    
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':isbn'   => $isbn,
                                 ':action' => $action,
                                 ':price'  => $price));
  }
  
  // dbEntriesRemove(#isbn, #user_id)
  // Removes an entry from `entries`
  // Sample usage: dbEntriesRemove($dbConn, $isbn, $user_id);
  function dbEntriesRemove($dbConn, $isbn, $user_id) {
    // Ensure the isbn and user_id both exist in the database
    if(!checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
      echo 'No such ISBN exists: ' . $isbn;
      return false;
    }
    if(!checkKeyExists($dbConn, 'users', 'user_id', $user_id)) {
      echo 'No such user exists: ' . $user_id;
      return false;
    }
    
    // Run the deletion query
    $query = '
      DELETE FROM `entries`
      WHERE
        `isbn` = :isbn
        AND
        `user_id` = :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':isbn'    => $isbn,
                                 ':user_id' => $user_id));
  }
  
  
  /* Notifications functions
  */
  
  // dbNotificationsGet(PDO, #userID)
  // Gets all notifications of a given user
  // Sample usage: dbNotificationsGet($dbConn, $user_id);
  function dbNotificationsGet($dbConn, $user_id) {
    $query = '
      SELECT 
        `entries`.*,
        `notifications_entry`.`notification_id`,
        `users`.`username`,
        `books`.`title`
      FROM `entries`

      # Get the `notification_id`
      INNER JOIN `notifications_entry`
      ON `entries`.`entry_id` = `notifications_entry`.`entry_id`

      # Get the `entry_id`
      INNER JOIN `notifications`
      ON `notifications_entry`.`notification_id` = `notifications`.`notification_id`
      
      # Get the `username`
      INNER JOIN `users`
      ON `entries`.`user_id` = `users`.`user_id`
      
      # Get the `title`
      INNER JOIN `books`
      ON `entries`.`isbn` = `books`.`isbn`
      
      # Filter on user_id
      WHERE `notifications`.`user_id` = :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id));
    // $stmnt->execute();
    
    return $stmnt->fetchAll(PDO::FETCH_ASSOC);
  }
  
  // dbNotificationsCount(PDO, #userID)
  // Counts the number of notifications for a given user
  // Sample usage: dbNotificationsCount($dbConn, $user_id);
  function dbNotificationsCount($dbConn, $user_id) {
    $query = '
      SELECT COUNT(*) FROM `notifications`
      WHERE `user_id` = :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array('user_id' => $user_id));
    $rows = $stmnt->fetch(PDO::FETCH_ASSOC);
    return $rows['COUNT(*)'];
  }
  
  // dbNotificationsRemove(#notification_id)
  // Removes a notification from `notifications` of the given id
  // Sample usage: dbUsersRemove($dbConn, $notification_id)
  function dbNotificationsRemove($dbConn, $notification_id) {
    // Ensure the notification exists in the database
    if(!checkKeyExists($dbConn, 'notifications_entry', 'notification_id', $notification_id)) {
      echo 'No such notification exists: ' . $notification_id;
      return false;
    }
    
    // Run the deletion query
    $query = '
      DELETE FROM `notifications`
      WHERE `notification_id` = :notification_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':notification_id' => $notification_id));
  }
  
  
  /* Common SQL Gets (misc)
  */
  
  // getUserInfo(PDO, #userID)
  // Gets all the info about a user from the database  
  function getUserInfo($dbConn, $userID) {
    // Grab and return that userID's information from the database
    return $dbConn->query('
      SELECT * FROM `users`
      WHERE `user_id` LIKE ' . filterUserID($userID) . '
      LIMIT 1
    ')->fetch(PDO::FETCH_ASSOC);
  }
  
  // getUserEntries(PDO, $userID[, 'action'])
  // Gets all entries from a user
  // Optionally filters on action type
  function getUserEntries($dbConn, $userID, $action='%') {
    // Prepare the query SQL
    $query = '
      SELECT * FROM `entries`
      WHERE `user_id` LIKE ' . filterUserID($userID) . '
      AND `action` LIKE :action
    ';
    // Create, run, and return a statement from the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':action' => $action));
    return $stmnt->fetchAll();
  }
?>

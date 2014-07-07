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
      LEFT JOIN `user_descriptions` 
        ON `users`.`user_id` = `user_descriptions`.`user_id`
      WHERE `users`.`' . $type . '` = :identity
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
    if(checkKeyExists($dbConn, 'users', 'email', $email)
      || checkKeyExists($dbConn, 'users', 'email_edu', $email)) {
      echo $email . ' is already being used.';
      return false;
    }
    
    // If there's a password, create the salts
    if(!empty($password)){
      $salt = getPasswordSalt();
      $salted = getPasswordSalted($password, $salt);
    }
    // No password means an alternate authenticated method (e.g. Facebook)
    else{
      $salt = "";
      $salted = "";
    }
    
    // email_edu is only populated if it's an .edu email (not from Facebook)
    if(endsWith($email, '.edu')) {
      $email_edu = $email;
    }
    else {
      $email_edu = '';
    }
    
    // Run the insertion query
    $query = '
      INSERT INTO  `users` (
        `username`, `password`, `email`, `email_edu`, `role`, `salt`
      )
      VALUES (
        :username, :password, :email, :email_edu, :role, :salt
      )
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':username'  => $username,
                          ':password'  => $salted,
                          ':email'     => $email,
                          ':email_edu' => $email_edu,
                          ':role'      => $role,
                          ':salt'      => $salt));
    
    // If unverified, also add an entry to `user_verifications`
    if(empty($role) || !$role || $role == 'Unverified') {
      $user_id = getRowValue($dbConn, 'users', 'user_id', 'email', $email);
      dbUserVerificationAddCode($dbConn, $user_id, $username, $email);
    }
    
    return true;
  }
  
    /**
     * Generates a random salt, to later be used for password encoding.
     * 
     * @return {String}
     * @todo Stop using sha256!
     */
    function getPasswordSalt() {
        return hash('sha256', uniqid(mt_rand(), true));
    }
    
    /**
     * Generates a hash of a password, to be used for password encoding.
     * 
     * 
     * @param {String} password   The actuall password the user submits
     * @param {String} salt   A random string obtained from getPasswordSalt()
     * @return {String}
     * @todo Stop using sha256!
     */
    function getPasswordSalted($password, $salt) {
        return hash('sha256', $salt . $password);
    }
  
  // dbUserVerificationAddCode(#user_id, "username");
  // Adds a random (sha256 hash) verification code for a user
  // Also sends a verification email to indicate this action
  function dbUserVerificationAddCode($dbConn, $user_id, $username, $email) {
    // Delete any preexisting verification emails for that user
    dbUserVerificationDeleteCode($dbConn, $user_id);
    
    // Run the actual insertion query
    $query = '
      INSERT INTO `user_verifications` (
        `user_id`, `code`
        )
        VALUES (
          :user_id,  :code
        );
    ';
    $code = getPasswordSalt();
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id,
                          ':code'    => $code));
    
    // If it's a valid .edu email, notifiy them with their verification code
    if(endsWith($email, '.edu')) {
      sendVerificationEmail($user_id, $username, $email, $code);
    }
  }
  
  // sendVerificationEmail(#user_id, "username", "email", "code")
  // Helper function to send a verification email to a user
  // Returns the bool status of the mail() call
  function sendVerificationEmail($user_id, $username, $email, $code) {
    require_once('templates.inc.php');
    return TemplateEmail($email, 'BookSwap Verification Time!', 'Emails/Verification', array(
        'user_id' => $user_id,
        'username' => $username,
        'email' => $email,
        'code' => $code
    ));
  }
  
  // dbUserVerificationDeleteCode($user_id)
  // Deletes the verification code for a user
  // If specified, sends a welcome email to indicate this action
  function dbUserVerificationDeleteCode($dbConn, $user_id, $do_email=false) {
    // Run the deletion query
    $query = '
       DELETE FROM `user_verifications`
       WHERE `user_id` = :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id));
    
    // Send an email if desired
    if($do_email) {
      $arguments = array(
        'dbConn' => $dbConn
      );
      publicUserSendWelcomeEmail($arguments, true);
    }
    return true;
  }
    
  // dbUserPasswordResetAddCode(#user_id, "username");
  // Adds a random (sha256 hash) password reset code for a user, and emails them
  // that code
  function dbUserPasswordResetAddCode($dbConn, $user_id, $username, $email) {
    // Delete any preexisting password reset codes for that user
    dbUserPasswordResetDeleteCode($dbConn, $user_id);
        
    // Run the actual insertion query
    $query = '
      INSERT INTO `password_resets` (
        `user_id`, `code`
        )
        VALUES (
          :user_id,  :code
        );
    ';
    $code = getPasswordSalt();
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id,
                          ':code'    => $code));
      
    return sendPasswordResetEmail($user_id, $username, $email, $code);
  }
  
  // sendPasswordResetEmail(#user_id, "username", "email", "code")
  // Helper function to send a password verification email to a user
  // Returns the bool status of the mail() call
  function sendPasswordResetEmail($user_id, $username, $email, $code) {
    require_once('templates.inc.php');
    return TemplateEmail($email, 'BookSwap Password Reset', 'Emails/PasswordReset', array(
        'code' => $code,
        'user_id' => $user_id,
        'username' => $username,
        'email' => $email
    ));
  }
  
  // dbUserPasswordResetGetCode($user_id)
  // Gets the password reset code for a user, if it doesn't exist
  function dbUserPasswordResetGetCode($dbConn, $user_id) {
    $query = '
      SELECT * FROM `password_resets`
      WHERE `user_id` LIKE :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id));
    return $stmnt->fetch(PDO::FETCH_ASSOC);
  }
  
  // dbUserPasswordResetDeleteCode($user_id)
  // Deletes the password reset code for a user
  function dbUserPasswordResetDeleteCode($dbConn, $user_id) {
    // Run the deletion query
    $query = '
       DELETE FROM `password_resets`
       WHERE `user_id` = :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':user_id' => $user_id));
    
    return true;
  }
  
  // dbFacebookUsersAdd("username", "facebook ID", "email", #role);
  // Adds a user to `users` and `FacebookUsers` using dbUsersAdd
  //	* Will not work if a user is in `users` with the same email
  //	* Will not work if a user is in `FacebookUsers` with
  //      the same user id or facebook ID
  function dbFacebookUsersAdd($dbConn, $username, $fb_id, $email, $role='Unverified'){
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
  
  // dbUsersEditEmail("identity", "email_new"[, "type"])
  // Edits an email from `users` of the given identity (by default, user_id)
  // Sample usage: dbUsersEditEmail($dbConn, $user_id, $email_new, "user_id");
  function dbUsersEditEmail($dbConn, $identity, $email_new, $type='user_id') {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', $type, $identity)) {
        echo 'No such ' . $type . ' exists: ' . $identity;
        return false;
    }

    // Run the change query
    $query = '
        UPDATE `users` SET
        `email` = :email_new
        WHERE `' . $type . '` = :identity
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':email_new' => $email_new,
                                 ':identity'  => $identity));
  }

  // dbUsersEditEmailEdu("identity", "email_new"[, "type"])
  // Edits an email from `users` of the given identity (by default, user_id)
  // Sample usage: dbUsersEditEmailEdu($dbConn, $user_id, $email_new, "user_id");
  function dbUsersEditEmailEdu($dbConn, $identity, $email_new, $type='user_id') {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', $type, $identity)) {
        echo 'No such ' . $type . ' exists: ' . $identity;
        return false;
    }

    // Run the change query
    $query = '
        UPDATE `users` SET
        `email_edu` = :email_new
        WHERE `' . $type . '` = :identity
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':email_new' => $email_new,
                                 ':identity'  => $identity));
  }
  
  // dbUsersEditPassword("identity", "password_raw"[, "type"])
  // Edits a password from `users` of the given identity (by default, user_id)
  // Sample usage: dbUsersEditPassword($dbConn, $user_id, $pass_new, "user_id");
  function dbUsersEditPassword($dbConn, $identity, $password_raw, $type='user_id') {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', $type, $identity)) {
        echo 'No such ' . $type . ' exists: ' . $identity;
        return false;
    }
    
    // Hash the password for security, with a new salt each time this is called
    $salt_new = getPasswordSalt();
    $password_new = getPasswordSalted($password_raw, $salt_new);
    
    // Run the change query
    $query = '
      UPDATE `users` 
      SET
        `password` = :password,
        `salt` = :salt
      WHERE `' . $type . '` = :identity
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':password'  => $password_new,
                                 ':salt'      => $salt_new,
                                 ':identity'  => $identity));
    
  }
  
  // dbUsersEditDescription("user_id", "password_raw")
  // Edits a description from `users_descriptions` of the user of the `user_id`
  // Sample usage: dbUsersEditDescriptions($dbConn, $user_id, "Hello world!");
  function dbUsersEditDescription($dbConn, $user_id, $description) {
    // Ensure the identity exists in the database
    if(!checkKeyExists($dbConn, 'users', 'user_id', $user_id)) {
        echo 'No such user exists: ' . $user_id;
        return false;
    }
    
    // Run the change query
    $query = '
      UPDATE `user_descriptions`
      SET `description` = :description
      WHERE `user_id` = :user_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':description' => $description,
                                 ':user_id' => $user_id));
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
  function dbEntriesGetRecent($dbConn, $identifier=false, $value, $limit = 35) {
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
      $query .= ' LIMIT ' . ((int) $limit);
    }
    
    // Run the query
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute($args);
    
    return $stmnt->fetchAll(PDO::FETCH_ASSOC);
  }
  
    // dbEntriesGetISBNs(#user_id, "action")
    // Gets all the unique ISBNs the user_id has entries for
    function dbEntriesGetISBNs($dbConn, $user_id, $action='') {
        // Ensure the user_id exists in the database
        if(!checkKeyExists($dbConn, 'users', 'user_id', $user_id)) {
            return false;
        }
        
        // Prepare the initial query and the initial arguments
        $query = '
            SELECT DISTINCT `isbn` FROM `entries`
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
  
  // dbBookEntriesGet(#isbn, "action")
  // Gets all the entires related to an isbn
  function dbBookEntriesGet($dbConn, $isbn, $user_id, $action='') {
    // Ensure the isbn exists in the database
    if(!checkKeyExists($dbConn, 'books', 'isbn', $isbn)) {
        return false;
    }
    
    // Prepare the initial query and the initial arguments
    $query = '
      SELECT * FROM `entries`
      INNER JOIN `users`
      ON `entries`.`user_id` = `users`.`user_id`
      WHERE `entries`.`isbn` = :isbn
        AND `entries`.`user_id` = :user_id
    ';
    // Add in the extra filter, if needed
    if($action != '') {
        $query .= ' AND `action` = :action';
        $args = array(':isbn' => $isbn,
                      ':user_id' => $user_id,
                      ':action'  => $action);
    }
    else {
        $args = array(':isbn' => $isbn,
                      ':user_id' => $user_id);
    }
    
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute($args);
    
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
  
  // dbEntriesEditPrice(#isbn, #entry_id, #price)
  // Edits a pre-existing entry's price in `entries`
  // Sample usage: dbEntriesEditPrice($dbConn, $entry_id, 14);
  function dbEntriesEditPrice($dbConn, $entry_id, $price) {
    // Ensure the entry_id exists in the database
    if(!checkKeyExists($dbConn, 'entries', 'entry_id', $entry_id)) {
      return false;
    }
    
    $query = '
      UPDATE `entries`
      SET `price` = :price
      WHERE `entry_id` = :entry_id
    ';
    
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':entry_id'   => $entry_id,
                                 ':price'  => $price));
  }
  
  // dbEntriesEditPrice(#isbn, #entry_id, "state")
  // Edits a pre-existing entry's price in `entries`
  // Sample usage: dbEntriesEditPrice($dbConn, $entry_id, 14);
  function dbEntriesEditState($dbConn, $entry_id, $state) {
    // Ensure the entry_id exists in the database
    if(!checkKeyExists($dbConn, 'entries', 'entry_id', $entry_id)) {
      return false;
    }
    
    $query = '
      UPDATE `entries`
      SET `state` = :state
      WHERE `entry_id` = :entry_id
    ';
    
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':entry_id'   => $entry_id,
                                 ':state'  => $state));
  }
  
  // dbEntriesRemove(#entry_id)
  // Removes an entry from `entries`
  // Sample usage: dbEntriesRemove($dbConn, $entry_id);
  function dbEntriesRemove($dbConn, $entry_id) {
    // Ensure the isbn and user_id both exist in the database
    if(!checkKeyExists($dbConn, 'entries', 'entry_id', $entry_id)) {
      return false;
    }
    
    // Run the deletion query
    $query = '
      DELETE FROM `entries`
      WHERE `entry_id` = :entry_id
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    return $stmnt->execute(array(':entry_id' => $entry_id));
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
  
  // getUserInfo(PDO, #user_id)
  // Gets all the info about a user from the database  
  function getUserInfo($dbConn, $user_id) {
    // Grab and return information from `users` joined with `user_descriptions`
    return $dbConn->query('
      SELECT * FROM `users`
      INNER JOIN `user_descriptions` 
        ON `users`.`user_id` = `user_descriptions`.`user_id`
      WHERE `users`.`user_id` LIKE ' . filterUserID($user_id) . '
      LIMIT 1
    ')->fetch(PDO::FETCH_ASSOC);
  }
  
  // getUserFromEmail(PDO, "email")
  // Gets all the info about a user from the database, from either email
  function getUserFromEmail($dbConn, $email) {
    $query = '
      SELECT * FROM `users`
      INNER JOIN `user_descriptions` 
        ON `users`.`user_id` = `user_descriptions`.`user_id`
      WHERE 
       `users`.`email` LIKE :email
        OR
        `users`.`email_edu` LIKE :email
      LIMIT 1
    ';
    $stmnt = getPDOStatement($dbConn, $query);
    $stmnt->execute(array(':email' => $email));
    return $stmnt->fetch(PDO::FETCH_ASSOC);
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
  
  
  /* Utility functions
  */
  function startsWith($haystack, $needle) {
    return $needle === "" || strpos($haystack, $needle) === 0;
  }
  function endsWith($haystack, $needle) {
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
  }

  function emailBeingUsed($dbConn, $email) {
    return getRowValue($dbConn, 'users', 'email', 'email', $email)
        && getRowValue($dbConn, 'users', 'email_edu', 'email_edu', $email);
  }
?>

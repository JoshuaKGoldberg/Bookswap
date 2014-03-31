<?php
  /* Scripts for logging into the site
  */
  
  // Runs through all the motions of attempting to log in with the given credentials
  // If successfull, the timetamp and users info are copied to $_SESSION
  // Otherwise $_SESSION['Fail Counter'] is incremented
  function loginAttempt($email, $password) {
    $dbConn = getPDOQuick();
    
    // First check if the passwords match
    $user_info = loginCheckPassword($dbConn, $email, $password);
    if(!$user_info) {
      // If they didn't, increase the session's fail counter
      if(!isset($_SESSION['Fail Counter']))
        $_SESSION['Fail Counter'] = 1;
      else ++$_SESSION['Fail Counter'];
      return false;
    }
    
    // Since they did, copy the user info over
    foreach($user_info as $key => $value)
      if(!is_numeric($key)) # Skip '0', '1', etc.
        $_SESSION[$key] = $value;
    $_SESSION['Logged In'] = time();
    
    return true;
  }
  
  // facebookLoginAttempt("Facebook ID")
  // Attempts to login with a given Facebook ID
  // If successful, the timestamp and users info are copied to $_SESSION
  function facebookLoginAttempt($fb_id){
	  $dbConn = getPDOQuick();
	  
	  // Check if Facebook ID exists in database
	  $user_info = dbFacebookUsersGet($dbConn, $fb_id);
	  if(!$user_info){
		   return false;
	  }
	  // It does, copy the user info over
	  foreach($user_info as $key=>$value)
		if(!is_numeric($key))
			$_SESSION[$key] = $value;
	  $_SESSION['Logged In'] = time();
	  
	  return true;
  }
  
  // loginCheckPassword("email", "password")
  // Returns whether the password matches
  // false is returned on failure
  // $user_info (an associative array of user info) is returned on success
  function loginCheckPassword($dbConn, $email, $password) {
    // Grab all relevant information about the user from the database
    $user_info = dbUsersGet($dbConn, $email, 'email');
    
    // Check if the user has a password in the database
    // (If they don't, they're probably using another authentication method)
    if(empty($user_info['salt']) || empty($user_info['password'])) return false;
    
    // Get the salt to hash the password, making sure they match
    $salted = hash('sha256', $user_info['salt'] . $password);
    return ($salted == $user_info['password']) ? $user_info : false;
  }
  
  // isStringEmail("string")
  // Determines if the given string is an email
  function isStringEmail($string) {
    return filter_var($string, FILTER_VALIDATE_EMAIL);
  }
  
  // isEmailAcademic("string")
  // Determines if the given string is from a .edu email
  // Does not check for EU or other styles of school emails
  function isEmailAcademic($string) {
    $test = '.edu';
    return substr_compare($string, $test, -strlen($test), strlen($test)) === 0;
  }
  
  // isPasswordSecure("string")
  // Checks if the password is 7+ characters, has upper&lowercase, symbol(s), and digit(s)
  function isPasswordSecure($string) {
    return (strlen($string) >= 7)
        && (preg_match('/[A-Z]/', $string))
        && (preg_match('/[a-z]/', $string))
        && (preg_match('/[^A-Z^a-z^0-9]/', $string))
        && (preg_match('/[0-9]/', $string));
  }
?>

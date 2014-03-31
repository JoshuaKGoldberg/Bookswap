<?php
  /* Settings.php
   * General site settings and important utility functions
  */
  function isInstalled() { return false; }
  function CheckInstallation($page) {
    if(!isInstalled()) {
      header('Location: install.php');
      return false;
    }
    return true;
  }
  
  // Important variables of the site location
  function getName() { return 'BookSwap'; }
  function getBase() { return ''; }
  function getCDir() { return ''; }
  function getTemplatesPre() { return 'Templates/'; }
  function getTemplatesExt() { return '.tpl.php'; }
  function getIncludesPre() { return 'PHP/'; }
  function getIncludesExt() { return '.inc.php'; }
  function getTemplateWrapping($name) { return getTemplatesPre() . $name . getTemplatesExt(); }
  function getIncludesWrapping($name) { return getIncludesPre() . $name . getIncludesExt(); }
  
  // Include files required for normal operation
  if(getCDir() != '') {
    chdir(getCDir()); 
    $inc_pre = getIncludesPre();
    $inc_ext = getIncludesExt();
    require_once($inc_pre . 'templates' . $inc_ext);
    require_once($inc_pre . 'pdo' . $inc_ext);
    require_once($inc_pre . 'sql' . $inc_ext);
  }
  
  // Database / Server Logins
  function getDBHost() { return ''; }
  function getDBUser() { return ''; }
  function getDBPass() { return ''; }
  function getDBName() { return 'bookswap'; }
  
  /* Google Books API
  */
  
  function getGoogleKey() { return ''; }
  function getGoogleLink($google_id) { return 'http://books.google.com/books?id=' . $google_id; }
  function getGoogleExport($google_id, $type) {
    $output = 'http://books.google.com/books/download/';
    $output .= '?id=' . $google_id . '&output=' . $type;
    return $output;
  }
  
  /* Facebook API 
   */
  function getFacebookKey() { return ''; }
  
  /* Templating & Including
  */
  
  // Helpers to print URLs and similar
  function getJS($filename) { return '<script type="text/javascript" src="JS/' . $filename . '.js"></script>'; }
  function getCSS($filename) { return '<link rel="stylesheet" type="text/css" href="CSS/' . $filename . '.css">'; }
  function getURL($url) { return getBase() . '/index.php?' . (is_string($url) ? 'page=' . $url : $url); }
  function getLinkHTML($url, $contents, $args=[]) {
    $output = getURL($url);
    foreach($args as $key=>$value)
      $output .= '&' . $key . '=' . $value;
    return '<a href="' . $output . '">' . $contents . '</a>';
  }
  function getLinkExternal($url, $text) {
    return '<a href="' . $url . '">' . $text . '</a>';
  }
  
  // General Site Info
  function getSchoolName() { return 'RPI'; }
  function getSiteName() { return getSchoolName() . ' ' . getName(); }
  function getSiteDescription() { return 'A hub for students to buy & sell textbooks on campus.'; }
  function getNumBooks() { return 'dozens of'; }
  
  // Default include files
  function getDefaultJS() { return ['jquery-2.0.3.min', 'requests', 'login', 'header', 'default']; }
  function getDefaultCSS() { return ['normalize']; }
  function getDefaultFonts() {
    $output = '';
    // $output .= '<link href="http://fonts.googleapis.com/css?family=Doppio+One" rel="stylesheet" type="text/css">';
    $output .= '<link href="http://fonts.googleapis.com/css?family=Lato:300" rel="stylesheet" type="text/css">';
    return $output;
  }
  
  /* User particulars
  */
  
  function getUserRoles() { return ['Unverified', 'User', 'Administrator']; }
  
  /* Book particulars
  */
  
  function getBookStateDefault() { return 'Good'; }
  function getBookStates() { return ['Terrible', 'Fair', 'Like New']; }
  function getBookActions() { return ['Buy', 'Sell']; }
  function getBookRatings() { return ['0', '1', '2', '3', '4', '5']; }
  
  // getActionOpposite("action")
  // Returns the opposite action (like Buy / Sell) for a transaction
  function getActionOpposite($action) {
    switch($action) {
      case 'Buy': return 'Sell';
      // Currently there are no others.
      default: return $action;
    }
  }
  

  /* Misc. Utilities
  */
  
  // 'Safe' way to ensure a session has been started
  function EnsureSessionStarted() {
    if(session_id() == '' || !isset($_SESSION['Started']) || !$_SESSION['Started'])
      session_start();
    $_SESSION['Started'] = true;
  }
  
  // Bool function - is the user logged in?
  function UserLoggedIn() {
    return isset($_SESSION['Logged In']) && $_SESSION['Logged In'];
  }
  
  // Bool function - is the user verified?
  function UserVerified() {
    return isset($_SESSION['Role']) && $_SESSION['Role'] != 'Unverified';
  }
  
  // Complains if the user goes where they shouldn't
  function AccessDenied() {
    echo '<section><h1 class="standard_main standard_vert">Sorry, you need to be logged in to go here!</h1></section>';
  }
  
  function getCurrency() { return '&#36;'; }
  function getPriceAmount($amount) { return getCurrency() . number_format($amount, 2, '.', ','); }
  
  // getHTTPPage("url")
  // Runs a cURL request on a page, returning the result
  function getHTTPPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $data = curl_exec($ch);
    if($data === FALSE)
      echo curl_error($ch);
    curl_close($ch);
    return $data;
  }
  
  // During installation, let users edit config functions using the web form
  function performSettingsReplacements($filename, $replacements) {
    function makeFunctionReplacer($name, $value) {
      if(is_string($value)) $value = '\'' . $value . '\'';
      if(is_bool($value)) $value = $value ? 'true' : 'false';
      return 'function ' . $name . '() { return ' . $value . '; }';
    }
    $contents = file_get_contents('settings.php');
    foreach($replacements as $name=>$value) {
      if(!function_exists($name)) continue;
      $name_old = makeFunctionReplacer($name, call_user_func($name));
      $name_new = makeFunctionReplacer($name, $value);
      $contents = str_replace($name_old, $name_new, $contents);
    }
    file_put_contents($filename, $contents);
    return $contents;
  }
?>

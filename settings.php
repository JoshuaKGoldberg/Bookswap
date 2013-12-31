<?php
  /* Settings.php
   * General site settings and important utility functions
  */
  
  // Important variables of the site location
  function getName() { return 'BookSwap'; }
  function getBase() { return false; }
  function getCDir() { return false; }
  function getTemplatesPre() { return 'Templates/'; }
  function getTemplatesExt() { return '.tpl.php'; }
  function getIncludesPre() { return 'PHP/'; }
  function getIncludesExt() { return '.inc.php'; }
  
  // Include files required for operation
  chdir(getCDir());
  $inc_pre = getIncludesPre();
  $inc_ext = getIncludesExt();
  require_once($inc_pre . 'templates' . $inc_ext);
  require_once($inc_pre . 'pdo' . $inc_ext);
  require_once($inc_pre . 'sql' . $inc_ext);
  
  // Database / Server Logins
  function getDBHost() { return 'localhost'; }
  function getDBUser() { return 'root'; }
  function getDBPass() { return ''; }
  function getDBName() { return 'bookswap'; }
  
  // Helpers to print the above URLs
  function getURL($url) { return getBase() . '/index.php?' . (is_string($url) ? 'page=' . $url : $url); }
  function getLinkHTML($url, $contents, $args=[]) {
    $output = getURL($url);
    foreach($args as $key=>$value)
      $output .= '&' . $key . '=' . $value;
    return '<a href="' . $output . '">' . $contents . '</a>';
  }
  
  /* Templating & Including
  */
  
  // General Site Info
  function getSchoolName() { return 'RPI'; }
  function getSiteName() { return getSchoolName() . ' ' . getName(); }
  function getSiteDescription() { return 'A hub for students to buy & sell textbooks on campus.'; }
  function getNumBooks() { return 'dozens of'; }
  
  
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
  
  
  /* Google Books API
  */
  
  function getGoogleKey() { return "AIzaSyD2FxaIBhdLTA7J6K5ktG4URdCFmQZOCUw"; }
  function getGoogleLink($google_id) { return 'http://books.google.com/books?id=' . $google_id; }
  function getGoogleExport($google_id, $type) {
    $output = 'http://books.google.com/books/download/';
    $output .= '?id=' . $google_id . '&output=' . $type;
    return $output;
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
?>
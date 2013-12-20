<?php
  /* Settings.php
   * General site settings and important utility functions
  */
  
  // Know where the site is located
  function getBase() { return "http://localhost/BooksExchange"; }
  function getURL($url) { return getBase() . '/index.php?page=' . $url; }
  function getLinkHTML($url, $contents, $args=[]) {
    $output = getURL($url);
    foreach($args as $key=>$value)
      $output .= '&' . $key . '=' . $value;
    return '<a href="' . $output . '">' . $contents . '</a>';
  }
  function getCurrency() { return '&#36;'; }
  
  /* Templating & Including
  */
  // function getTemplatesPre() { return 'Templates/'; }
  function getTemplatesPre() { return 'C:/xampp/htdocs/BooksExchange/Templates/'; }
  function getTemplatesExt() { return '.tpl.php'; }
  function getIncludesPre() { return 'PHP/'; }
  function getIncludesExt() { return '.inc.php'; }
  
  // Make sure required include files are included
  $inc_pre = getIncludesPre();
  $inc_ext = getIncludesExt();
  require_once($inc_pre . 'Templates' . $inc_ext);
  require_once($inc_pre . 'PDO' . $inc_ext);
  require_once($inc_pre . 'SQL' . $inc_ext);
  
  // General Site Info
  function getSchoolName() { return 'RPI'; }
  function getSiteName() { return getSchoolName() . ' BookSwap'; }
  function getSiteDescription() { return 'A hub for students to buy & sell textbooks on campus.'; }
  function getNumBooks() { return 'dozens of'; }
  
  // Database / Server Logins
  function getDBHost() { return 'localhost'; }
  function getDBUser() { return 'root'; }
  function getDBPass() { return ''; }
  function getDBName() { return 'BooksExchange'; }
  
  
  /* Book particulars
  */
  
  function getBookStateDefault() { return 'Good'; }
  function getBookStates() { return ['Terrible', 'Poor', 'Fair', 'Good', 'Very Good', 'Like New']; }
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
  

  /* Misc. Utilities
  */
  
  // Bool function - is the user logged in?
  function UserLoggedIn() {
    if(!isset($_SESSION)) session_start();
    return isset($_SESSION) && isset($_SESSION['Logged In']) && $_SESSION['Logged In'];
  }
  
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
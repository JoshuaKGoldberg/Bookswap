<?php
  /* Public API for running a limited subset of PHP functions
   * The allowed functions are defined by $allowed_functions
   * The JS::sendRequest function goes to this page and specifies a function to run
  */
  // This has to be used with a requested function
  if(!isset($_GET['Function'])) return;
  $function_name = preg_replace("/[^A-Za-z_0-9]/", '', $_GET['Function']);

  require_once('../settings.php');
  require_once('pdo.inc.php');
  require_once('sql.inc.php');
  require_once('db_actions.php');
  require_once('db_login.php');
  require_once('public_functions.php');
  
  // Functions the user may call via JS
  $allowed_functions = array(
    'publicCheckValidity', 'publicCreateUser', 'publicLogin',
    'publicAddBook', 'publicSearch', 'publicGetBookEntries',
    'publicGetSIS', 'publicBookImport',
    'publicPrintUserBooks', 'publicPrintRecentListings',
    'publicEntryAdd',
    'publicPrintRecommendationsDatabase',
    'publicPrintRecommendationsUser'
  );
  
  // If the user doesn't request one of these functions, quit
  if(!in_array($function_name, $allowed_functions)) return;
  
  // If it is, run the function
  call_user_func($function_name, $_GET);
?>
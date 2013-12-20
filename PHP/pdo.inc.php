<?php
  /* PDO.inc.php
   * Contains common functions for making and using PDOs
  */
  
  // getPDOQuick()
  // Gets a new PDO object with the default settings
  // Sample usage: $dbConn = getPDOQuick();
  function getPDOQuick() {
    return getPDO(getDBHost(), getDBName(), getDBUser(), getDBPass());
  } 
  
  // getPDO("dbHost", "dbName", "dbUser", "dbPass")
  // Gets a new PDO object with the given settings
  // Sample usage: $dbConn = getPDO($dbHost, $dbName, $dbUser, $dbPass);
  function getPDO($dbHost, $dbName, $dbUser, $dbPass='') {
    try {
      $dbConn = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
      // This helps with debugging (enables the PDOExceptions)
      $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } 
    catch(PDOException $err) {
      echo 'Error creating PDO: ' . $err->getMessage();
      $dbConn = false;
    }
    return $dbConn;
  }
  
  // getPDOStatement($dbConn, $query)
  // Runs the typical preparation function on the PDO object for a statement
  function getPDOStatement($dbConn, $query) {
    return $dbConn->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
  }
?>
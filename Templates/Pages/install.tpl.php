<?php
  /* Complete installation script
   * 
   * Starts the system off with a new, blank set of database tables
   * 1. Create the database if it doesn't yet exist
   * 2. Create the `users` table
   * 3. Create the `books` table
   * 4. Create the `history` table
  */
  
  // Get a connection to the server (no specific database yet)
  // These variables are already available via settings
  $dbHost = getDBHost();
  $dbName = getDBName();
  $dbPass = getDBPass();
  $dbUser = getDBUser();
  $dbConn = getPDO($dbHost, '', $dbUser, $dbPass);
  
  // 1. Create the database if it doesn't yet exist
  $dbConn->prepare('
    CREATE DATABASE IF NOT EXISTS ' . $dbName
  )->execute();
  // From now on, everything will be in that database
  $dbConn->exec('USE ' . $dbName);
  
  // 2. Create the `users` table
  // * These are identified by the user_id int
  // (this should also be extended for Facebook integration)
  $dbConn->exec('
    CREATE TABLE IF NOT EXISTS `users` (
      `user_id` INT(10) NOT NULL AUTO_INCREMENT,
      `username` VARCHAR(127) NOT NULL,
      `password` VARCHAR(127) NOT NULL,
      `email` VARCHAR(127) NOT NULL,
      `salt` VARCHAR(127) NOT NULL,
      `role` INT(1),
      PRIMARY KEY (`user_id`)
    )
  ');
  
  // 3. Create the `books` table
  // This refers to the known information on a book
  // * These are identified by their 13-digit ISBN number
  // * Google Book IDs are also kept for book.php
  // * The authors list is split by endline characters
  $dbConn->exec('
    CREATE TABLE IF NOT EXISTS `books` (
      `isbn` VARCHAR(15) NOT NULL,
      `google_id` VARCHAR(127),
      `title` VARCHAR(127),
      `authors` VARCHAR(255),
      `description` TEXT,
      `publisher` VARCHAR(127),
      `year` VARCHAR(15),
      `pages` VARCHAR(7),
      PRIMARY KEY (`isbn`)
    )
  ');
  
  // 4. Create the `entries` table
  // This contains the entries of books by users
  // * These are also identified by their 13-digit ISBN number
  // * Prices are decimels, as dollars
  // * State may be one of the $bookStates
  // * Action may be one of the $bookActions
  // (this should use `isbn` and `user_id` as foreign keys)
  $dbConn->exec('
    CREATE TABLE IF NOT EXISTS `entries` (
      `entry_id` INT(10) NOT NULL AUTO_INCREMENT,
      `isbn` VARCHAR(15) NOT NULL,
      `user_id` INT(10) NOT NULL,
      `username` VARCHAR(127) NOT NULL,
      `bookname` VARCHAR(127) NOT NULL,
      `price` DECIMAL(19,4),
      `state` ENUM(' . makeSQLEnum(getBookStates()) . '),
      `action` ENUM(' . makeSQLEnum(getBookActions()) . '),
      `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`entry_id`)
    )
  ');
?>
<section>
  <h4 class="standard_main standard_vert">The site should be installed, if it wasn't already. You may return to the home page.</h4>
</section>
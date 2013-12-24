<?php
  // http://www.php.net/session_destroy
  if(!isset($_SESSION)) session_start();
  $_SESSION = array();
  session_destroy();
  header('Location: ' . getBase());
?>
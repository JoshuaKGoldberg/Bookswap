<?php
  /* index.php
   * The main file that calls the templating files
  */
  require_once('settings.php');
  EnsureSessionStarted();
  
  TemplatePrint('Page');
?>
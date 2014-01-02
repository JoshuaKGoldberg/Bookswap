<?php
  /* index.php
   * The main file that calls the templating files
  */
  $time_start = microtime(true);
  require_once('settings.php');
  if(!CheckInstallation(isset($_GET['page']) ? $_GET['page'] : '')) return;
  EnsureSessionStarted();
  
  TemplatePrint('Page');
?>
<!-- This page took PHP <?php echo (microtime(true) - $time_start) * 1000; ?> milliseconds to generate -->
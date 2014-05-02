<?php
  // http://www.php.net/session_destroy
  if(!isset($_SESSION)) session_start();
  $_SESSION = array();
  session_destroy();
?>
<title><?php echo 'Logging out - ' . getSiteName(); ?></title>
<section>
  <h1 class="standard_main standard_vert">
    You have been logged out.
    <br>
    <small>Transport when ready!</small>
  </h1>
  <div id="goto"><?php echo getBase(); ?></div>
</section>
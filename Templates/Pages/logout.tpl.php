<?php
  // http://www.php.net/session_destroy
  if(!isset($_SESSION)) session_start();
  $_SESSION = array();
  session_destroy();
?>
<!-- <meta http-equiv="refresh" content="1.17; url=<?php echo getBase(); ?>"> -->
<section>
  <h1 class="standard_main standard_vert">
    You have been logged out.
    <br>
    <small>Transport when ready!</small>
  </h1>
  <div id="goto"><?php echo getBase(); ?></div>
</section>
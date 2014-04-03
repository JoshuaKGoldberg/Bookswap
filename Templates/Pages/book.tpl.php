<?php
  if(!isset($_GET['isbn'])) {
    echo '<section><h4 class="standard_main standard_vert">Please provide an ISBN!</h4></section>';
    return;
  }
  include_once('defaults.php');
  include_once('db_actions.php');
  include_once('pdo.inc.php');
    
  $isbn = $_GET['isbn'];
  $info = dbBooksGet(getPDOQuick(), $isbn, true);
  if(!$info) return TemplatePrint('Pages/404');
?>

<!-- Actual book info -->
<section>
  <div class="standard_main standard_width">
    <?php
      TemplatePrint('Books/Large', $tabs + 4, $info);
      TemplatePrint('Forms/AddEntry', $tabs + 4, $info);
    ?>
  </div>
</section>

<!-- Feed of listings -->
<section>
  <h1 class="standard_main standard_vert giant">book listings</h1>
  <div class="standard_main listings">
    <?php
      PrintRequest('publicPrintRecentListings', array(
        'identifier' => 'isbn',
        'isbn'=> $info['isbn']
      ));
    ?>
  </div>
</section>
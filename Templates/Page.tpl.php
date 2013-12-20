<?php
  /* Page.tpl.php
   * The base template called directly by index.php
  */
  
  // The minimum includes required for pages to run
  $css = [];
  $js = ['jquery-2.0.3.min', 'requests', 'login', 'search', 'default'];
  
  // The requested page to print
  $pageName = isset($_GET['page']) ? $_GET['page'] : "index";
  
  // (that page will also have its own .css and .js)
  $css[] = $pageName;
  $js[] = $pageName;
?>
<!DOCTYPE html>
<html>
  
    <head>
      <title><?php echo getSiteName(); ?></title>
      <?php
        // The default CSS file should be printed immediately, so it loads first
        echo '<link rel="stylesheet" type="text/css" href="CSS/default.css">' . PHP_EOL;
      ?>
    </head>
    
    <body onload="loadPrintedRequests()">
      <?php TemplatePrint("Header", $tabs + 6); ?>
        
      <!-- <div id="body_grad_top"></div> -->
      <?php
        TemplatePrint("Pages/" . $pageName, $tabs + 6);
        echo PHP_EOL;
      ?>
        
      <?php TemplatePrint("Footer", $tabs + 6); ?>
      
      <!-- Include Fonts -->
      <!-- <link href="http://fonts.googleapis.com/css?family=Doppio+One" rel="stylesheet" type="text/css"> -->
      <link href="http://fonts.googleapis.com/css?family=Lato:300" rel="stylesheet" type="text/css">
      <?php
        // Extra includes
        if(isset($_GET['css']))
          foreach(explode($_GET['css']) as $extra)
            $css[] = $extra;
        if(isset($_GET['js']))
          foreach(explode($_GET['js']) as $extra)
            $js[] = $extra;
      ?>
      
      <?php
        echo '<!-- Include CSS -->' . PHP_EOL;
        foreach($css as $filename)
          echo str_repeat(' ', $tabs + 6) . '<link rel="stylesheet" type="text/css" href="CSS/' . $filename . '.css">' . PHP_EOL;
      ?>
      
      <?php
        echo '<!-- Include JS -->' . PHP_EOL;
        foreach($js as $filename)
          echo str_repeat(' ', $tabs + 6) . '<script type="text/javascript" src="JS/' . $filename . '.js"></script>' . PHP_EOL;
      ?>
    </body>
</html>
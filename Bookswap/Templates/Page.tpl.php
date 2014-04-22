<?php
  /* Page.tpl.php
   * The base template called directly by index.php
  */
  
  // The minimum includes required for pages to run
  $css = getDefaultCSS();
  $js = getDefaultJS();
  
  // The requested page to print
  $pageName = isset($_GET['page']) ? $_GET['page'] : (UserLoggedIn() ? 'account' : 'index');
  // For unverified users, account should redirect to verification
  if($pageName === 'account' && !UserVerified())
    $pageName = 'verification';
  // For verified users, verification should redirect to account
  if($pageName === 'verification' && UserVerified())
    $pageName = 'account';
  
  // (that page will also have its own .css and .js)
  $css[] = $pageName;
  $js[] = $pageName;
?><!DOCTYPE html>
<html>
  
    <head>
      <title><?php echo getSiteName(); ?></title>
      <?php
        // The default CSS file should be printed immediately, so it loads first
        echo getCSS('default') . PHP_EOL;
  
        // Include JQuery before everything, so pages can have their own JS files using it
        echo getJS('jquery.min');
      ?>
    </head>
    
    <body onload="loadPrintedRequests()">
		<?php 
			if(getFacebookKey()):
		?>
		<!-- Facebook API -->

		<div id="fb-root"></div>
		<script>
			var fbKey = '<?php echo getFacebookKey(); ?>';
			var UserLoggedIn = <?php echo UserLoggedIn() ? "true" : "false"; ?>;
		</script>
		<?php echo getJS('fb'); ?>
		<?php
			endif;
		?>
      <?php TemplatePrint("Header", $tabs + 6); ?>
        
      <!-- <div id="body_grad_top"></div> -->
      <?php
        TemplatePrint("Pages/" . $pageName, $tabs + 6);
        echo PHP_EOL;
      ?>
        
      <?php TemplatePrint("Footer", $tabs + 6); ?>
      
      <!-- Include Fonts -->
      <?php echo getDefaultFonts(); ?>
      
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
          echo str_repeat(' ', $tabs + 6) . getCSS($filename) . PHP_EOL;
      ?>
      
      <?php
        echo '<!-- Include JS -->' . PHP_EOL;
        foreach($js as $filename)
          echo str_repeat(' ', $tabs + 6) . getJS($filename) . PHP_EOL;
      ?>
    </body>
</html>
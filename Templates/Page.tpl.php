<?php
  /* Page.tpl.php
   * The base template called directly by index.php
  */
  
  // The minimum includes required for pages to run
  $css = getDefaultCSS();
  $js = getDefaultJS();
  
  // If no $_TARGS is provided, use $_GET instead
  if(!isset($_TARGS) || empty($_TARGS)) {
    $_TARGS = $_GET;
  }
  
  // The requested page to print
  $pageName = isset($_TARGS['page']) ? $_TARGS['page'] : (UserLoggedIn() ? 'account' : 'index');
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
<html
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:og="http://ogp.me/ns#"
    xmlns:fb="https://www.facebook.com/2008/fbml"
>
  
    <head>
        <!-- Page: <?php echo $pageName; ?> (<?php echo $tabs; ?> tabs) -->
        
        <?php
            // The default CSS file should be printed immediately, so it loads first
            echo getCSS('default') . PHP_EOL;
        ?>
        <?php
            // Include JQuery before everything, so pages can have their own JS files using it
            echo getJS('jquery.min') . PHP_EOL;
        ?>

        <meta property="og:site_name" content="<?php echo getSiteName(); ?>"/>
        <meta property="og:image" content="<?php echo getImage('Logo.png'); ?>"/>
        
        <link rel="icon" type="image/png" href="<?php echo getImage('Icon.png'); ?>">
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
        if(isset($_TARGS['css']))
          foreach(explode($_TARGS['css']) as $extra)
            $css[] = $extra;
        if(isset($_TARGS['js']))
          foreach(explode($_TARGS['js']) as $extra)
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
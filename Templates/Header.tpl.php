<header>
  <div id="header_main" class="standard_main">
  
    <?php TemplatePrint("Header/Badge", $tabs + 6); ?>
    
    <?php if (UserLoggedIn()) {TemplatePrint("Header/Notifications", $tabs + 6);} ?>
    
    <?php TemplatePrint("Header/Search", $tabs + 6); ?>

  </div>
</header>

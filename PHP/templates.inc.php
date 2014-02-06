<?php
  /* Template.inc.php
   * Functions to print out the tpl.php files
  */
  
  // getTemplatePage("name")
  // Ensures a name is on a whitelist of allowed 
  function getTemplatePage($name) {
    $allowed_pages = array(
      'Entry', 'Footer', 'Header', 'Money', 'Page',
      'Books/Export', 'Books/Large', 'Books/Medium',
      'Forms/AddEntry', 'Forms/Money',
      'Header/Badge', 'Header/Search',
      'Pages/404', 'Pages/account', 'Pages/book', 'Pages/import',
      'Pages/index', 'Pages/logout', 'Pages/search'
    );
    // If the user doesn't request one of these pages, print 404 instead
    if(!in_array($name, $allowed_pages)) $name = '404';
    return getTemplateWrapping($name);
  }
  
  // TemplatePrint("name", #tabs, {_TARGS})
  // Prints out the template file of the given name
  // Template arguments are passed in as $_TARGS
  function TemplatePrint($name, $tabs=0, $_TARGS=[]) {
    $prefix = str_repeat(' ', $tabs);
    echo PHP_EOL;
    // echo PHP_EOL . $prefix . "<!-- " . $name . " -->" . PHP_EOL; 
    
    // Get the actual file name and retrieve the plain-text contents
    $filename = getTemplatePage($name); // getTemplatesPre() . $name . getTemplatesExt();
    $content = trim(file_get_contents($filename));
    
    // Put tabs at the beginning of each line of the file
    // This keeps the code nice and neat
    $content = TemplateTabify($content, $prefix);
    
    // Yes, it's eval. In this instance, that's ok:
    // * No code in here is touched by user input 
    // * This is the most simple, elegant way to include the templates
    eval('?><?php $tabs = ' . $tabs . ';?>' . $content);
  }
  
  // TemplateTabify("content", "prefix")
  // Puts the prefix (generally spaces) at the beginning of each line
  function TemplateTabify($content, $prefix) {
    return $prefix . str_replace(PHP_EOL, PHP_EOL . $prefix, $content);
  }
  
  // TemplatePrintSmall("name", {_TARGS})
  // Quickly prints out the template file of the given name
  // No extra comments or line breaks are used to surround it
  function TemplatePrintSmall($name, $_TARGS=[]) {
    // Get the actual file name and retrieve the plain-text contents
    $filename = getTemplatePage($name); // getTemplatesPre() . $name . getTemplatesExt();
    $content = trim(file_get_contents($filename));
    
    // Yes, it's eval. In this instance, that's ok:
    // * No code in here is touched by user input 
    // * This is the most simple, elegant way to include the templates
    eval('?><?php $tabs = 0;?>' . $content . PHP_EOL);
  }
  
  // PrintRequest("function_name", [args])
  // Prints an HTML form that will be picked up by JS on page load
  // JS will then send a public request to function_name with the arguments
  /* For example, to call a sample function from a template:
     PHP code
     --------
     <?php PrintRequest("PrintUserBooks", [$user_id, 'sell']); ?>
 
     Resultant HTML (line breaks for clarity)
     --------------
     <div class="php_request_load loading"
          request="PrintUserBooks"
          num_args = "2"
          arg0="4"
          arg1="sell">
       <hr />
       <div class="loader">loading</div>
     </div>

     Resultant JS call
     -----------------
     sendRequest("PrintUserBlocks", {"4", "sell"}, loadRequestAuto);
  */
  function PrintRequest($function_name, $args=[], $time=0) {
    $num_args = count($args);
    echo '<div class="php_request_load loading" request="' . $function_name . '" ';
    echo 'num_args = "' . $num_args . '"';
    $i = 0;
    // Print each argument in order, as key,value
    foreach($args as $key=>$value) {
      echo ' arg' . $i . '="' . $key . ',' . $value . '"';
      ++$i;
    }
    echo ' timeout=' . $time . '>' . PHP_EOL;
    echo '  <hr />' . PHP_EOL;
    echo '  <div class="loader">loading</div>' . PHP_EOL;
    echo '</div>';
  }
?>
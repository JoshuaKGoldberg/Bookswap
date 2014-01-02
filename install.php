<?php
  require_once('settings.php');
  // If the user doesn't have to install the site, go to index.php instead
  if(isInstalled()) {
    header('Location: index.php');
    return;
  }
  
  // Get the basic CSS and fonts for styling this page
  foreach(getDefaultCSS() as $css)
    echo getCSS($css) . PHP_EOL;
  echo getCSS('default') . PHP_EOL;
  echo getCSS('install') . PHP_EOL;
  echo getDefaultFonts() . PHP_EOL;
  
  // Get the contents of settings.php split into lines
  $settings_arr = preg_split ('/$\R?^/m', file_get_contents('settings.php'));
  
  // This will print out where a function is in that file
  function getFuncNameFancy($settings_arr, $name) {
    $loc = FuncArraySearch($name, $settings_arr);
    $output =  '<code class="big">' . $name . '()';
    $output .= '<small class="unemph small">(line ' . $loc . ')</small>';
    $output .= '</code>';
    return $output;
  }
  
  // Gets which member of an array has a substring 
  function FuncArraySearch($name, $arr) {
    for($i = 0, $len = count($arr); $i < $len; ++$i) 
      if(strpos($arr[$i], $name) !== false)
        return $i;
    return -1;
  }
  
  // The functions to be changed
  $host = $_SERVER['HTTP_HOST'];
  
  // The required functions to be changed
  $function_groups = array(
    'Site location' => array(
      'getBase' => array(
        'description' => 'The URL you will be accessing this site from',
        'suggestion' => $_SERVER['REQUEST_URI']
      ),
      'getCDir' => array(
        'description' => 'where this site is on your server',
        'suggestion' => getcwd()
      )
    ),
    'Database' => array(
      'getDBHost' => array(
        'description' => 'The hostname your MySQL database is located on',
        'suggestion' => in_array(strtolower($host), ['localhost', '127.0.0.1']) ? 'localhost' : 'mysql.' . $host
      ),
      'getDBUser' => array(
        'description' => 'The MySQL user with full read+write access to your database'
      ),
      'getDBPass' => array(
        'description' => 'The MySQL user\'s password'
      ),
      'getDBName' => array(
        'description' => 'The name of the MySQL database you\'d like to use',
        'suggestion' => strtolower(str_replace(' ', '', getName()))
      )
    ),
    'Google API' => array(
      'getGoogleKey' => array(
        'description' => 'The API key given to your Google account',
        'suggestion' => '</code>from <a href="https://developers.google.com/books/docs/v1/using#APIKey">this page</a><code>'
      )
    )
  );
  
  // Helper to count $function_groups
  function countKids($arr) {
    $output = 0;
    foreach($arr as $key=>$kid)
      $output += count($kid);
    return $output;
  }

?><!DOCTYPE html>
<html>
  
    <head>
      <title><?php echo getSiteName(); ?></title>
    </head>
      
    <body>
      
      <section>
        <h1 class="standard_main standard_vert"><?php echo getSiteName(); ?> Installation</h1>
        <p class="standard_main standard_vert medium">
          To install this website, you need to set the output of <?php echo countKids($function_groups); ?> functions in <code>settings.php</code>.
          These are used as the global configuration properties, namely regarding filepaths and the database.
        </p>
      </section>
      
      <section class="standard_vert">
        <div class="standard_main medium">
          <div>
            <?php
              $no_und = false;
              foreach($function_groups as $group=>$functions) {
                echo '<h1>' . $group . '</h1>' . PHP_EOL;
                foreach($functions as $name=>$info) {
                  if(is_array($info)) {
                    echo '<li><div class="what">' . getFuncNameFancy($settings_arr, $name) . '</div><div class="more">' . $info['description'] . '</div>';
                    if(isset($info['suggestion']) && $info['suggestion'])
                      echo ' (probably <code>' . $info['suggestion'] . '</code>)';
                    echo '</li>';
                  }
                  else echo $info;
                  echo PHP_EOL . PHP_EOL . str_repeat(' ', 12);
                }
              }
            ?>
          </div>
        </div>
      </section>
      
      <section>
        <h1 class="standard_main standard_vert">Whenever you're ready</h1>
        <div class="standard_main medium">Make <code>settings.php::isInstalled() { return true; }</code> instead of false, then refresh.</div>
        <br />
      </section>
      
    </body>

</html>
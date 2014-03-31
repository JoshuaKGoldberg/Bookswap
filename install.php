<?php
  require_once('settings.php');
  // If the user doesn't have to install the site, go to index.php instead
  if(isInstalled()) {
    header('Location: index.php');
    return;
  }
  
  // The new text is saved to the file, and split by lines
  // If the settings aren't writeable, this complains
  if(is_writable('settings.php'))
    $contents = performSettingsReplacements('settings.php', $_GET);
  else {
    echo '<header class="error">' . PHP_EOL;
    echo '<div class="standard_main standard_vert medium">settings.php is not writable - settings won\'t be saved and installation will fail.</div>' . PHP_EOL;
    echo '</header>' . PHP_EOL;
    $contents = file_get_contents('settings.php');
  }
  $settings_arr = preg_split('/$\R?^/m', $contents);
  
  // Get the basic CSS and fonts for styling this page
  foreach(getDefaultCSS() as $css)
    echo getCSS($css) . PHP_EOL;
  echo getCSS('default') . PHP_EOL;
  echo getCSS('install') . PHP_EOL;
  echo getDefaultFonts() . PHP_EOL;
  
  // Testing functions are done via install.js
  foreach(getDefaultJS() as $js)
    echo getJS($js) . PHP_EOL;
  echo getJS('install') . PHP_EOL;
  
  // Prints out a function name and where it is in the file ($settings_arr)
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
        return $i + 1;
    return -1;
  }
  
  // The functions to be changed
  $host = $_SERVER['HTTP_HOST'];
  
  // The required functions to be changed
  $function_groups = array(
    'Site location' => array(
      'getBase' => array(
        'description' => 'The URL you will be accessing this site from',
        'suggestion' => 'http://' . $_SERVER['HTTP_HOST'] . explode('/install.php', $_SERVER['REQUEST_URI'], 2)[0],
        'information' => 'This URL is what you should see in your browser\'s URL bar, including "http://". It\'s used to generate links within the site. Don\'t include a trailing slash.'
      ),
      'getCDir' => array(
        'description' => 'Where this site is on your server',
        'suggestion' => getcwd(),
        'information' => 'The directory path on your local machine where the site files are located. Don\'t include a trailing slash.'
      )
    ),
    'Database' => array(
      'getDBHost' => array(
        'description' => 'The hostname your MySQL database is located on',
        'suggestion' => in_array(strtolower($host), ['localhost', '127.0.0.1']) ? 'localhost' : 'mysql.' . $host,
        'information' => 'Your MySQL database\'s host should either be on localhost (for a local installation such as XAMPP), or the domain name / IP of your database server (for production servers).'
      ),
      'getDBUser' => array(
        'description' => 'The MySQL user with full read+write access to your database',
        'information' => 'This user must have general READ & WRITE priviliges to the database for installation and normal use. You may use root for testing, but it is not advisable on production servers.'
      ),
      'getDBPass' => array(
        'description' => 'The MySQL user\'s password',
        'type' => 'password'
      ),
      'getDBName' => array(
        'description' => 'The name of the MySQL database you\'d like to use',
        'suggestion' => strtolower(str_replace(' ', '', getName())),
        'information' => 'This database will be created if it does not yet exist, asuming your MySQL user has sufficient priviliges.'
      )
    ),
    'Optional Requirements' => array(
      'getGoogleKey' => array(
        'description' => 'A Google Books API key',
        'information' => 'In order to use the Import page, you must have a registered Google account and API key. Information on API keys is located on <a href="https://developers.google.com/books/docs/v1/using#APIKey">this page</a>.'
      ),
      'getFacebookKey' => array(
		'description' => 'A Facebook API key',
		'information' => 'In order to allow users to login with their Facebook account, you must have a Facebook Developer account and API key.'
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

  function printRequirements($function_groups, $settings_arr) {
    $no_und = false;
    foreach($function_groups as $group=>$functions) {
      echo PHP_EOL . str_repeat(' ', 12) . '<h1>' . $group . '</h1>' . PHP_EOL;
      foreach($functions as $name=>$info) {
        if(is_array($info)) {
          echo str_repeat(' ', 14) . '<li>' . PHP_EOL;
          echo str_repeat(' ', 16) . '<div class="what">' . getFuncNameFancy($settings_arr, $name) . '</div>' . PHP_EOL;
          
          if(isset($info['description'])) 
            echo str_repeat(' ', 16) . '<div class="more">' . $info['description'] . '</div>' . PHP_EOL;
          
          // if(!$suggestion) $suggestion = (isset($info['suggestion']) && $info['suggestion']) ? $info['suggestion'] : '';
          echo str_repeat(' ', 16) . '<input id="' . $name . '" type="' . (isset($info['type']) ? $info['type'] : 'text') . '" value="' . ((string) call_user_func($name)) . '" />' . PHP_EOL;
          
          if(isset($info['information']))
            echo str_repeat(' ', 16) . '<div class="info">' . $info['information'] . '</div>' . PHP_EOL;
          
          if(isset($info['suggestion']))
            echo str_repeat(' ', 16) . '<aside>We suggest "<code>' . $info['suggestion'] . '</code>"</aside>' . PHP_EOL;
          
          echo str_repeat(' ', 14) . '</li>';
        }
        echo PHP_EOL;
      }
    }
  }
  
?><!DOCTYPE html>
<html>
  
    <head>
      <title><?php echo getSiteName(); ?></title>
    </head>
      
    <body>
      
      <section>
        <h1 class="standard_main standard_vert"><?php echo getSiteName(); ?> - Installation</h1>
        <p class="standard_main standard_vert medium">
          Thank you for creating a new installation of BookSwap. This site stores global settings in the <code>settings.php</code> file. In order for this site to work, you'll have to give it some information about your environment (specifically, <strong><?php echo countKids($function_groups); ?></strong> functions), listed below.
          <!-- These are used as the global configuration properties, namely regarding filepaths and the database. -->
        </p>
        <p class="standard_main standard_vert medium">
          For each of the following functions, if their output is blank or wrong, change it on this page. When you're done, scroll to the bottom and click the 'test' button - we'll check to see if everything's working.
        </p>
      </section>
      
      <section class="standard_vert">
        <div class="standard_main medium">
          <div>
            <?php printRequirements($function_groups, $settings_arr); ?>
          </div>
        </div>
      </section>
      
      <section>
        <h1 class="standard_main standard_vert">When you're done</h1>
        <p id="results" class="standard_main standard_vert medium">When you're finished changing the configuration settings, click submit to test them.</p>
        <p class="standard_main standard_vert medium">
          <input type='submit' onclick='callSiteInstall()' />
        </p>
      </section>
      
    </body>

</html>

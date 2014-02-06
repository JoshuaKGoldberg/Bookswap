<?php
  /* Runs a series of tests to ensure the site is working properly */
  include_once('settings.php');
  include_once(getIncludesWrapping('pdo'));
  $num_errors = 0;
  
  function error($num1, $num2, $str) {
    echo '<h2>Error ' . $num1 . '-' . $num2 . ': ' . $str . '</h2>' . PHP_EOL;
  }
  function details($str) {
    echo '<aside>' . $str . '</aside>' . PHP_EOL;
  }
  
  $tests = array(
    'Settings files' => array(
      'description' => 'Does settings.php exist, and is it readable?',
      'tests' => array(
        array(
          'function' => function() { 
            if(!is_readable('settings.php')) return false;
            return true;
          },
          'error' => 'Could not open settings file.',
          'details' => 'The server could not read \'settings.php\'. Does it exist, and is it readable?'
        ),
        array(
          'function' => function() { return is_dir(getIncludesPre()); },
          'error' => 'Could not find the ' . getIncludesPre() . ' directory.',
          'details' => 'The server could not find the ' . getIncludesPre() . ' directory of include files. Does it exist, and is it readable?'
        ),
        array(
          'function' => function() { return is_readable(getIncludesWrapping('pdo')); },
          'error' => 'Could not read the database connection include file. Does it exist, and is it readable?'
        )
      )
    ),
    'Media files' => array(
      'description' => 'Do the CSS and JS directories exist, and are they readable?',
      'tests' => array(
        array(
          'function' => function() { return is_dir('CSS'); },
          'error' => 'Could not find the CSS directory.',
          'details' => 'The server could not find the CSS directory. Does it exist, and is it readable?'
        ),
        array(
          'function' => function() { return is_dir('JS'); },
          'error' => 'Could not find the JS directory.',
          'details' => 'The server could not find the JS directory. Does it exist, and is it readable?'
        ),
        array(
          'function' => function() { return is_readable('CSS/install.css'); },
          'error' => 'Could not open CSS/install.css.',
          'details' => 'The server could not find a sample CSS file at \'CSS/install.css\'. Does it exist, and is it readable?'
        ),
        array(
          'function' => function() { return is_readable('JS/install.js'); },
          'error' => 'Could not open JS/install.js.',
          'details' => 'The server could not find a sample JS file at \'JS/install.js\'. Does it exist, and is it readable?'
        )
      )
    ),
    'Database' => array(
      'description' => 'Can the database be accessed and used using the given credentials?',
      'tests' => array(
        array(
          'function' => function() { return getDBHost(); },
          'error' => 'You have a blank database host.',
          'details' => ''
        ),
        array(
          'function' => function() { return getDBUser(); },
          'error' => 'You have a blank database user.',
          'details' => ''
        ),
        array(
          'function' => function() { return getPDO(getDBHost(), getDBName(), getDBUser(), getDBPass()); },
          'error' => 'Unable to connect to the database',
          'details' => 'Your database credentials aren\'t working, so there must be a problem connecting to the database.'
        )
      )
    ),
    'External libraries' => array(
      'tests' => array(
        array(
          'function' => function() { return function_exists('curl_version'); },
          'error' => 'You do not have cURL installed.',
          'details' => 'cURL is required to quickly access external webpages.'
        )
      )
    )
  );
  
  $test_group_num = 0;
  foreach($tests as $title=>$test_group) {
    $test_num = 0;
    if(!isset($test_group['tests'])) continue;
    foreach($test_group['tests'] as $test) {
      $status = $test['function']();
      if(!$status) {
        error($test_group_num, $test_num, $test['error']);
        details($test['details']);
        ++$num_errors;
      }
      ++$test_num;
    }
    ++$test_group_num;
  }
  
  if($num_errors) {
    echo '<footer>You have ' . $num_errors . ' errors in your installation. Please fix them, then try again.</footer>';
    return false;
  }
  else {
    echo 'Ok!';
    return true;
  }
?>
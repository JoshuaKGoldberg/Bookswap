<?php
  $google_id = $_TARGS['google_id'];
  $options = array(
    'bibtex' => 'BiBTeX',
    'enw' => 'EndNote',
    'ris' => 'RefMan'
  );
  // Print each option, linked by its shortcut (index in this array)
  foreach($options as $key=>$value) {
    echo '<div class="book_export a-emph"><a href="' . getGoogleExport($google_id, $key) . '">' . $value . '</a></div>';
  }
?>
<?php 
  $publisher = $_TARGS['publisher'];
  echo getLinkHTML('search', $publisher, array(
    'column' => 'publisher',
    'value' => $publisher
  ));
?>

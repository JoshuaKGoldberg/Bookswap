<?php 
  $author = $_TARGS['author'];
  echo getLinkHTML('search', $author, array(
    'column' => 'author',
    'value' => $author
  ));
?>

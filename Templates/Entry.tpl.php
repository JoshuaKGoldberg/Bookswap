<div class='entry medium'>
<?php
  // Know whether the entry is from the current user
  if(!isset($_SESSION)) session_start();
  $user_id = $_TARGS['user_id'];
  $using_current = $_SESSION['user_id'] == $user_id;
  $username = $using_current ? 'you' : $_TARGS['username'];
  $plurals = $using_current ? '' : 's';
  
  echo '  ';
  echo getLinkHTML('account', $username, array('user_id'=>$user_id));
  echo ' want' . $plurals . ' to ' . strtolower($_TARGS['action']) . ' a ';
  echo strtolower($_TARGS['state']) . ' ';
  echo getLinkHTML('book', $_TARGS['bookname'], array('isbn'=>$_TARGS['isbn'])) . ' for ';
  TemplatePrintSmall("Money", $_TARGS);
?>
</div>
<div class='entry medium'>
<?php
  // Know whether the entry is from the current user
  $user_id = $_TARGS['user_id'];
  $using_current = UserLoggedIn() ? $_SESSION['user_id'] == $user_id : false;
  $username = $using_current ? 'you' : $_TARGS['username'];
  $plurals = $using_current ? '' : 's';
  
  echo '  ';
  echo getLinkHTML('account', $username, array('user_id'=>$user_id));
  echo ' want' . $plurals . ' to ' . strtolower($_TARGS['action']) . ' a ';
  echo strtolower($_TARGS['state']) . ' ';
  echo getLinkHTML('book', $_TARGS['title'], array('isbn'=>$_TARGS['isbn'])) . ' for ';
  TemplatePrintSmall("Money", $_TARGS);
?>
</div>

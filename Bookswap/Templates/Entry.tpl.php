<div class='entry medium'>
<?php
  // Know whether the entry is from the current user
  $user_id = $_TARGS['user_id'];
  $using_current = UserLoggedIn() ? $_SESSION['user_id'] == $user_id : false;
  $username = $using_current ? 'you' : $_TARGS['username'];
  $plurals = $using_current ? '' : 's';
  
  // User arguments are typically just user_id, but also notification_id if needed
  $user_args = array('user_id'=>$user_id);
  if(isset($_TARGS['notification_id']))
    $user_args['notification_id'] = $_TARGS['notification_id'];
  
  echo '  ';
  echo getLinkHTML('account', $username, $user_args);
  echo ' want' . $plurals . ' to ' . strtolower($_TARGS['action']) . ' a ';
  echo strtolower($_TARGS['state']) . ' ';
  echo getLinkHTML('book', $_TARGS['title'], array('isbn'=>$_TARGS['isbn'])) . ' for ';
  TemplatePrintSmall("Money", $_TARGS);
?>
</div>

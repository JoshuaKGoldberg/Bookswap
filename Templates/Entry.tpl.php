<div class='entry medium'>
<?php 
  echo '  ';
  echo getLinkHTML('account', $_TARGS['username'], array('user_id'=>$_TARGS['user_id']));
  echo ' wants to ' . strtolower($_TARGS['action']) . ' a ';
  echo strtolower($_TARGS['state']) . ' ';
  echo getLinkHTML('book', $_TARGS['bookname'], array('isbn'=>$_TARGS['isbn'])) . ' for ';
  TemplatePrintSmall("Money", $_TARGS);
?>
</div>
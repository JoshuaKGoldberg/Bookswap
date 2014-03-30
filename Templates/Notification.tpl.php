<div class="notification">
  <div><?php TemplatePrint('Entry', 2, $_TARGS); ?></div>
  <div class="small">
    <?php echo date('F j, g:i a', strtotime($_TARGS['timestamp'])); ?>
    &sdot;
    <a class="notification_contact" onclick="" href="">contact <?php 
      echo getLinkHTML('account', $_TARGS['username'], array(
        'user_id' => $_TARGS['user_id'],
        'notification_id' => $_TARGS['notification_id']
      ));
    ?></a>?
  </div>
</div>
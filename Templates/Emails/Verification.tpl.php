<h2>Hi there, <?php echo $_TARGS['username']; ?>!</h2>
<p>
    Someone (hopefully you) made an account on <?php echo getSiteName(); ?>.
    If that's you, great! 
    Visit <?php echo getLinkHTML('verification', 'this link', array(
        'user_id' => $_TARGS['user_id'],
        'code' => $_TARGS['code']
    )); ?> to verify your account.
    If this wasn't you, don't do that.
</p>
<p><em>   -<?php echo getSiteName(); ?> team</em></p>
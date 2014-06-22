<h2>Hi there, <?php echo $_TARGS['username']; ?>!</h2>
<p>
    Someone (hopefully you) can't remember your password on <?php echo getSiteName(); ?>.
    If that's you, you're in luck!
    Visit <?php echo getLinkHTML('passwordreset', 'this link', array(
        'code' => $_TARGS['code'],
        'user_id' => $_TARGS['user_id'],
        'username' => $_TARGS['username'],
        'email' => $_TARGS['email']
    )); ?> to verify your account.
    If this wasn't you, don't do that.
</p>
<p><em>   -<?php echo getSiteName(); ?> team</em></p>
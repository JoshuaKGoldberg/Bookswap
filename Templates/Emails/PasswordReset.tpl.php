<h2>Hi there, <?php echo $_TARGS['username']; ?>!</h2>
<p>
    Someone (hopefully you) can't remember your password on <?php echo getSiteName(); ?>.
    If that's you, you're in luck!
    Visit <?php echo getLinkHTML('password', 'this link', array(
        $_TARGS['user_id'] => $user_id,
        $_TARGS['code'] => $code
    )); ?> to verify your account.
    If this wasn't you, don't do that.
</p>
<p><em>   -<?php echo getSiteName(); ?> team</em></p>
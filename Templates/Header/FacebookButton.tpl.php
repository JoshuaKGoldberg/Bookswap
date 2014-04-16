<?php
    // Logged in users are offered a smaller button than anonymous
    $logged = UserLoggedIn();
    
    // If also logged in with Facebook, ignore this - they're good
    if(UserLoggedInFacebook()) {
        return;
    }
?>
<?php if($logged): ?>
<div id="facebook_button" onclick="FB.login();">
    <img src='https://fbstatic-a.akamaihd.net/rsrc.php/v2/yE/r/pO2d5bFWS8j.png'>
    Log In
</div>
<?php else: ?>  
<div id="facebook_button" onclick="FB.login();">Log In With Facebook</div>
<?php endif; ?>

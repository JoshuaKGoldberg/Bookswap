<?php
    $has_code = isset($_GET['code']);
    $submit_function = $has_code ? 'submitPasswordReset' : 'requestPasswordReset';
?><title><?php echo getSiteName(); ?> Password Reset</title>
<section>
    <div class="standard_main standard_width">
        
        <h1 class="standard_main standard_vert">
            Password Reset
        </h1>
        
        <form id="password_reset" class="standard_vert" onsubmit="event.preventDefault(); <?php echo $submit_function; ?>();">
            <p class="standard_main small">
                <?php if(!$has_code): ?>
                That was a great password you had, shame if you happened to forget it!
                <br />
                Enter your email and username here, and we'll email you a link to make a new password.
                <br />
                <br />
                <?php endif; ?>
                <input id="j_email" name="email" type="email" placeholder = "your email" />
                <input id="j_username" name="username" type="text" placeholder = "your username" />
                <?php if(!$has_code): ?>
                <br />
                <br />
                <input type="submit" class="medium pad=h" value="Get a link" />
                <?php endif; ?>
                <p id="reset_loader"></p>
            </p>
            
            <?php if($has_code): ?>
            <p class="standard_main standard_vert small">
                Put your new password here (twice, just to be sure) so we can set it for you.
                <br />
                <br />
                <input id="j_password" name="password" type="password" placeholder="new password" />
                <input id="j_password_confirm" name="password_confirm" type="password" class="verif_input" placeholder="again, just to be sure" />
                <br />
                <br />
                <input id="j_code" name="code" type="hidden" placeholder="reset code" />
                <input type="submit" class="big pad-h" value="Set my password">
            </p>
            <?php endif; ?>
        </form>
        
    </div>
</section>
  
<?php
  // The login scripts from index.js are very useful here
  echo getJS('index');
?>

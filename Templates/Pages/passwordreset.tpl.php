<?php

?>
<title><?php echo getSiteName(); ?> Password Reset</title>
<section>
    <div class="standard_main standard_width">
        
        <h1 class="standard_main standard_vert">
            Password Reset
        </h1>
        
        <p class="standard_main standard_vert small">
            You should have got an email with a link to this page. You may submit
            a new password here.
            <div id="reset_loader"></div>
        </p>
        
        <form id="password_reset" onsubmit="event.preventDefault(); sendPasswordResetForm();">
            <p class="standard_main standard_vert medium">
                <input id="j_password" name="password" type="password" placeholder="new password" />
                <input id="j_password_confirm" name="password_confirm" type="password" class="verif_input" placeholder="again, just to be sure" />
                <br />
                <br />
                <input id="j_email" name="email" type="email" placeholder = "your email" />
                <input id="j_username" name="username" type="text" placeholder = "your username" />
                <br />
                <br />
                <input id="j_code" name="code" type="text" placeholder="reset code" />
                <br />
                <br />
                <input type="submit" class="big pad-h" value="Submit">
            </p>
        </form>
        
    </div>
</section>
  
<?php
  // The login scripts from index.js are very useful here
  echo getJS('index');
?>

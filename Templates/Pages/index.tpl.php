<?php
  // If the user is logged in, just skip directly to the account page
  if(UserLoggedIn())
    header('Location: ' . getURL("account"));
?>
<section>
  <p class="standard_main pad-v big">
    <?php echo getSiteDescription(); ?><br />Best of all, it's free! 
  </p>
</section>

<section>
  <div id="user_login_container" class="standard_main pad-v big">
    <p id="user_login_text">
      We're free & easy to use, and always will be. No more tears, only books.
    </p>
    <div id="user_login_holder">
      <form onsubmit="event.preventDefault(); joinSubmit();">
        <div id="hold_username" class='input_holder'>
          <input id="j_username" type='text' name='username' placeholder='Username' />
          <div class="hold_complaint"></div>
        </div>
        <div id="hold_password" class='input_holder'>
          <input id="j_password" type='password' name='password' placeholder='Password' />
          <div class="hold_complaint"></div>
        </div>
        <div id="hold_password_confirm" class = 'input_holder'>
          <input id="j_password_confirm" type='password' name='password_confirm' placeholder='Re-enter password' />
          <div class="hold_complaint"></div>
        </div>
        <div id="hold_email" class='input_holder'>
          <input id="j_email" type='email' name='email' placeholder='Email' />
          <div class="hold_complaint"></div>
        </div>
        <input id="submit" type='submit' value='Sign Me Up!'/>
      </form>
    </div>
  </div> 
</section>

<section>
  <p class="standard_main pad-v big">
    By signing up for RPI Textbook Exchange you're giving yourself easy access to <?php echo getNumBooks(); ?> books made available by students just like you.
    <br />
    <!-- <small class="unemph"><a href="search.php">Take a look at our database!</a></small> -->
  </p>
</section>
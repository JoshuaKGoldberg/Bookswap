<section>
  <p class="standard_main pad-v big">
    <?php echo getSiteDescription(); ?><br />Best of all, it's free! 
  </p>
</section>

<section>
  <div id="user_login_container" class="standard_main pad-v big">
    <div class="half_holder">
    <div id="user_login_holder" class="half left">
      <form onsubmit="event.preventDefault(); joinSubmit();">
        <div id="hold_username" class='input_holder'>
          <input id="j_username" type='text' name='username' placeholder='Your Name' />
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
        <input id="j_submit" type='submit' value='Sign Me Up!'/>
      </form>
    </div>
    <p id="user_login_text" class="half right">
      We're free & easy to use, and always will be. No more tears, only books.
    </p>
  </div> 
</section>

<section>
  <p class="standard_main pad-v big">
    By signing up for RPI Textbook Exchange you're giving yourself easy access to <?php echo getNumBooks(); ?> books made available by students just like you.
    <br />
    <!-- <small class="unemph"><a href="search.php">Take a look at our database!</a></small> -->
  </p>
</section>
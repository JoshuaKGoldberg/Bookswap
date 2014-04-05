<?php
  // If there is no $_SESSION['email'], don't display anything
  if(!isset($_SESSION['email'])) {
    return AccessDenied();
  }
  
  // Email tests are located here
  require_once('db_login.php');
  
  // The user email should be stored in $_SESSION from login
  $email_edu = $_SESSION['email_edu'];
  $email = $_SESSION['email'];
  $email_is_edu = isEmailAcademic($email_edu) || isEmailAcademic($email);
?>
<section>
  <div class="standard_main standard_width">
    
    <h1 class="standard_main standard_vert">
      You'll need to <?php echo $email_is_edu ? 'verify your email' : 'provide a .edu address'; ?>.
      <div id="verif_loader" class="small"><?php echo $email; ?></div>
    </h1>
    
    <p class="standard_main standard_vert medium">
      <?php if($email_is_edu): ?>
      We've sent an email to <strong><?php echo $email_edu; ?></strong>. Click the link there to verify it.<br>
      <?php else: ?>
      Please enter one for us to send a verification email to.
      <form id="verif_email_create" action="sendVerifEmailForm();">
        <input id="email_edu" name="email_edu" type="email" class="medium" placeholder="a .edu address">
        <br>
        <br>
        <?php if(!$_SESSION['password'] || !strlen($_SESSION['password'])): ?>
        <br>
        <p class="standard_main standard_vert medium">
          If you'd like, you can also set a password to log in with either email.
          <br>
          <br>
          <input id="password" name="password" type="password" placeholder="a new password">
          <input id="password_confirm" name="password_confirm" type="password" placeholder="again, just to be sure">
        </p>
        <br>
        <?php endif; ?>
        <input type="submit" class="big pad-h">
      </form>
      <?php endif; ?>
    </p>
    
    <p class="standard_main standard_vert small">
      You can still log in with what you used to register.
      <br>
      Verifying your email as yours and from your institute helps keep our site safe and your recommendations relevant.
    </p>
    
  </div>
</section>
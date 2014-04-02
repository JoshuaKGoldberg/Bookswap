<?php
  // Email tests are located here
  require_once('db_login.php');
  
  // The user email should be stored in $_SESSION from login
  $email = $_SESSION['email'];
  // $email = 'goldbj5@rpi.edu';
  $email_is_edu = isEmailAcademic($email);
?>
<section>
  <div class="standard_main standard_width">
    <h1 class="standard_main standard_vert">
      You'll need to <?php echo $email_is_edu ? 'verify your email' : 'change to a .edu address'; ?>.
      <div id="verif_loader" class="small"></div>
    </h1>
    <p class="standard_main standard_vert medium">
      <?php if($email_is_edu): ?>We've sent an email to <strong><?php echo $email; ?></strong>. Click the link there to verify it.<br><?php endif; ?>
    </p>
    <p class="standard_main standard_vert small">
      Verifying your email as yours and from your institute helps keep our site safe and your recommendations relevant.
    </p>
  </div>
</section>
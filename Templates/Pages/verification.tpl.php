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
    <h1 class="standard_main standard_vert">You'll need to <?php echo $email_is_edu ? 'verify your email' : 'change to a .edu address'; ?>.</h1>
    <h2><?php echo $email; ?></h2>
    
    <?php if($email_is_edu): ?><p class="standard_main standard_vert">
      We've sent an email to <?php echo $email; ?>. Click the link there to verify it.
    </p><?php endif; ?>
    
    <aside>(click your email to change it)</aside>
    <p class="standard_main standard_vert">Verifying your email as from your institute helps keep our site safe and your recommendations relevant.</p>
  </div>
</section>
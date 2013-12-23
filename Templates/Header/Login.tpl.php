<?php $logged = UserLoggedIn(); ?>

<div id="badge" class="<?php echo $logged ? "logged" : "anon" ?>">
  
  <div id="badge_image">
    <img src="Images/ProfileDefault.jpg" />
  </div>
  
  <div id="badge_main">
    
    <div id="badge_main_contents">
      
      <form id="badge_login" onsubmit="event.preventDefault(); loginSubmit(event, this);">
        
        <?php if(!$logged): ?>
        
        <input id="username" type="text" placeholder="username" />
        <input id="password" type="password" placeholder="password" />
        
        <div id="login_submit_holder">
          <input id="login_submit" type="submit" value="Log Me In!" />
          <input id="login_forgot" type="button" value="Forgot?" onclick="alert('nope')" />
        </div>
        
        <?php endif; ?>
        
      </form>
      
    </div>
  </div>
  
</div>
<div id="badge" class="headblock <?php $logged = UserLoggedIn(); echo $logged ? "logged" : "anonymous"; ?>">
  
  <div id="badge_main">
    
    <?php if(!$logged): ?>
    <form id="badge_login" onsubmit="event.preventDefault(); loginSubmit(event, this);">
      
      <div id="login_information">
        
        <input id="email" type="email" placeholder="email address" />
        <input id="password" type="password" placeholder="password" />
        
      </div>  
      
      <div id="login_submit_holder">
        <input id="login_submit" class="a-emph" type="submit" value="Log Me In!" />
        <input id="login_forgot" class="a-emph" type="button" value="Forgot?" onclick="window.location = '<?php echo getURL('passwordreset'); ?>';" />
      </div>
      
    </form>
	
    <?php else: ?>
    <div id="badge_second">
      <?php 
        if($logged && isset($_SESSION['fb_id']) && $_SESSION['fb_id']) {
          echo getLinkHTML(false, '<img src="https://graph.facebook.com/' . $_SESSION['fb_id'] . '/picture?type=large" />');
        } else {
          echo getLinkHTML(false, '<img src="' . getBase() . '/Images/Home.gif" />');
        }
      ?>
      <?php 
        if($logged) {
          include('public_functions.php');
          $num = publicGetNumNotifications();
          if($num > 0) {
            echo '<div id="notif_icon">' . getLinkHTML('account#notifications', $num) . '</div>';
          }
        }
      ?>
    </div>
    <?php endif; ?>
    
  </div>
    
  <?php if(getFacebookKey()): ?>
  <div id="fb_holder" class="<?php echo UserLoggedIn() ? "user" : "anon" ?>">
    <fb:login-button id="fb_button" onmouseover="fbHoverOn()" onmouseout="fbHoverOff()" data-size="large" scope="email" data-auto-logout-link="true">Log in</fb:login-button>
  </div>
  <?php endif; ?>
  
</div>


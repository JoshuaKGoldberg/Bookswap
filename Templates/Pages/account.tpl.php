<?php
    if(!UserVerified()) return AccessDenied();

    // Check if the current page is the user's profile
    if(isset($_GET['user_id']) && isset($_SESSION['user_id'])) {
        $using_current = $_GET['user_id'] == $_SESSION['user_id'];
    } else {
        $using_current = UserLoggedIn();
    }

    // Normally, the information on this page is for the current user
    if($using_current) {
        $username = $_SESSION['username'];
        $user_id = $_SESSION['user_id'];
        $descriptor = 'you want to';
        $description = isset($_SESSION['description']) 
            ? $_SESSION['description'] : 'It looks like you don\'t have a description yet!';
    }
    // If it's from a specific user, query that from the database
    else if(isset($_GET['user_id'])) {
        include_once('defaults.php');
        include_once('db_actions.php');
        include_once('pdo.inc.php');

        $user_id = $_GET['user_id'];
        $user_info = dbUsersGet(getPDOQuick(), $user_id, 'user_id', false);
        if(!$user_info) {
          $username = 'No such user exists!';
          $descriptor = false;
          $user_info = array();
          $description = $user_info['description'];
        }
        else {
          $username = $user_info['username'];
          $descriptor = $username . ' wants to';
          $description = '';
        }
    }
    // Otherwise it's an anonymous user - anons don't get this
    else {
        return AccessDenied();
    }

    // Whether to print contact info on the screen
    $show_contact_info = $using_current || isset($user_info);

    /**
    * Prints contact information of the user (such as email), as static for other
    * users or as editable components for the current user.
    * 
    * @param {Integer} tabs   How many tabs to print before each line
    * @param {String} field   The value being printed (such as "goldbj5@rpi.edu")
    * @param {String} label   The label with the value (such as ".edu email")
    * @param {String} editor   An optional JS function to call for the editable
    *                          component; if provided, this is editable; if not,
    *                          this is static.
    */
    function printContactInfo($tabs, $value, $label, $editor='') {
        echo str_repeat('  ', $tabs) . '<div id="' . str_replace(' ', '', str_replace('.', '', $label)) . '" class="contact_block">' . PHP_EOL;

        // If a editor function is provided, the top div is fancy
        // (false until editEmail and editEmailEdu are implemented)
        echo str_repeat('  ', $tabs + 1) . '<div class="contact_block_up">';
        if($editor) { 
            PrintEditable($value, $editor, array('callback' => 'updateEmailDisplay'));
        } else {
            echo $value;
        }
        echo '</div>' . PHP_EOL;

        echo str_repeat('  ', $tabs + 1) . '<div class="contact_block_down">' . $label . '</div>' . PHP_EOL;
        echo str_repeat('  ', $tabs) . '</div>' . PHP_EOL;
    }

?>
<title><?php 
    if($using_current) {
        echo getSiteName();
    } else {
        echo $username . ' - ' . getSiteName();
    }
    ?></title>

<section id="account">
    <!-- Title / Username -->
    <?php if($using_current) echo '<section class="notice"><div class="standard_main">This is your account page. Click your info to change it.</div></section>' . PHP_EOL; ?>
    <h1 id="username" class="standard_main standard_vert giant"><?php
    if($using_current) {
        PrintEditable($username, 'publicUserEditUsername', array('callback' => 'updateSearchUsername'));
    } else {
        echo $username;
    }
    ?></h1>
    <?php if($descriptor): ?>

    <!-- Description -->
    <p id="user_description" class="standard_main"> <?php 
        if($using_current) {
            PrintEditable($description, 'publicUserEditDescription');
        } else {
            echo $description;
        }
    ?></p>

    <!-- Psuedo-menu -->
    <div id="links_menu" class="standard_main">
    <?php
        $out = '';
        $items = array(
            'top' => 'account',
            'books' => 'user_books',
            'recommendations' => 'user_books',
            'notifications' => 'notifications',
            'activity' => 'recent_activity'
        );
        
        foreach($items as $display=>$link) {
            $out .= '<a href="#' .  $link . '">' . $display . '</a> &sdot; ';
        }
        $out = substr($out, 0, -7);
        echo $out;
    ?>
    </div>

    <!-- Contact info -->
    <?php if($show_contact_info): ?>

    <div id="contact_info" class="standard_main standard_vert">
    <?php 
        echo PHP_EOL;
        // If on the current user, print callbacks to edit the info
        if($using_current) {
            printContactInfo(5, $_SESSION['email'], 'main email', 'publicUserEditEmail');
            printContactInfo(5, $_SESSION['email_edu'], '.edu email', 'publicUserEditEmailEdu');
        }
        // Otherwise just print the info all plain-like
        else {
            printContactInfo(5, $user_info['email'], 'main email');
            printContactInfo(5, $user_info['email_edu'], '.edu email');
        }
        echo PHP_EOL;
    ?>
    </div>
    <?php endif; ?>

    <!-- Lists of books the user wants -->
    <div id="user_books" class="standard_main half_holder">
        <div id="user_books_buy" class="half left">
          <h2>Books <?php echo $descriptor; ?> buy</h2>
          <hr />
          <?php 
            PrintRequest("publicPrintUserBooks", array(
              'user_id' => $user_id,
              'size' => 'Medium',
              'action' => 'Buy'
            ));
          ?>
        </div>
        <div id="user_books_sell" class="half right">
          <h2>Books <?php echo $descriptor; ?> sell</h2>
          <hr />
          <?php 
            PrintRequest("publicPrintUserBooks", array(
              'user_id' => $user_id,
              'size' => 'Medium',
              'action' => 'Sell'
            ));
          ?>
        </div>
        <div class="half_after"></div>
    </div>
    <?php else: ?>
    <aside class='user_id_no'>(ID <?php echo $user_id; ?>)</aside>
    <?php endif; ?>
    <br>
</section>

<?php if(UserLoggedIn()): ?>

<?php if($using_current): ?>
<!-- Notifications -->
<section id="notifications">
    <h1 class="standard_main standard_vert giant">notifications</h1>
    <div class="standard_main standard_vert">
    <?php
      PrintRequest('publicPrintNotifications');
    ?>
    </div>
</section>
<?php endif; ?>

<!-- Recommended trades -->
<section id="recommendations">
    <h1 class="standard_main standard_vert giant">
    <?php
      echo 'all recommendations for you';
      if(!$using_current) {
        echo ' and ' . $username;
      }
    ?>
    </h1>
    <div class="standard_main standard_vert">
    <?php
      // If you're the current user, find all matching entries in the entire database
      if($using_current)
        PrintRequest('publicPrintRecommendationsDatabase', array('user_id' => $user_id));
      // Otherwise you're another user, find the matches between the two of you
      else PrintRequest('publicPrintRecommendationsUser', array(
        'user_id_a' => $_SESSION['user_id'],
        'user_id_b' => $user_id
      ));
    ?>
    </div>
</section>

<?php endif; ?>

<!-- Display a feed of the most recently placed stuff -->
<?php if($using_current): ?>
<section id="recent_activity">
    <h1 class="standard_main standard_vert giant">recent <?php echo getSchoolName(); ?> listings</h1>
    <div class="standard_main listings">
    <?php PrintRequest("publicPrintRecentListings", array("verbose" => true)); ?>
    </div>
</section>
<?php endif; ?>

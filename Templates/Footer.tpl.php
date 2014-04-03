<footer>
  <div class="standard_main">
    <div>
      <?php
        if(UserVerified())
          $links = ['Account', 'Search', 'Import', 'Log Out'];
        else if(UserLoggedIn())
          $links = ['Account', 'Search', 'Log Out'];
        else
          $links = ['Search'];
        for($i = 0, $len = count($links); $i < $len; ++$i)
          $links[$i] = getLinkHTML(strtolower(str_replace(' ', '', $links[$i])), $links[$i]);
        $links[$i] = getLinkExternal('https://github.com/Diogenesthecynic/Bookswap/', 'Github');
        echo implode('<span>&sdot;</span>', $links);
      ?>
    </div>
      <?php
        $choices = array(
          'aspiring, active',
          'beautiful, benefitial',
          'cool, capable',
          'dynamic, divergent',
          'excellent, endearing',
          'fun, friendly',
          'great, gorgeous',
          'happy, healthy',
          'intriguing, inpsirational',
          'jolly, joyous',
          'key, kosher',
          'lovely, luschous',
          'magnificent, multifaceted',
          'neat, nifty',
          'otherworldly, opulent',
          'practical, precocious',
          'quick, qualified',
          'robust, reliable',
          'safe, smooth',
          'thrilling, therapeutic',
          'unorthodox, upbeat',
          'vibrant, veritable',
          'wholesome, worldly',
          'youthful, yummy',
          'zesty, zany'
        );
        $choice = $choices[array_rand($choices)];
        echo 'A' . (in_array($choice[0], array('a', 'e', 'i', 'o', 'u')) ? 'n ' : ' ') . $choice . ' RCOS project.';
      ?>
    <br>
    Josh Goldberg, Harish Lall, Evan MacGregor
    <br>
    <small>also Albert Armea, Javier Camino, and Aaron Sedlacek</small>
  </div>
</footer>

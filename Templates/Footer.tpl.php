<footer>
  <div class="standard_main">
    <div>
      <?php
        if(UserLoggedIn())
          $links = ['Account', 'Search', 'Import', 'Log Out'];
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
          'n aspiring, active',
          ' fun, friendly'
        );
        echo 'A' . $choices[array_rand($choices)] . ' RCOS project.';
      ?>
    <br>
    Albert Armea, Javier Camino, Josh Goldberg, Harish Lall, Evan MacGregor, and Aaron Sedlacek.
  </div>
</footer>
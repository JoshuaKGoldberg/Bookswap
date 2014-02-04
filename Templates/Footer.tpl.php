<footer>
  <div class="standard_main">
    <div><?php
      if(UserLoggedIn()) {
        $links = ['Account', 'Search', 'Import', 'Log Out'];
        for($i = 0, $len = count($links); $i < $len; ++$i)
          $links[$i] = getLinkHTML(strtolower(str_replace(' ', '', $links[$i])), $links[$i]);
        echo implode('<span>&sdot;</span>', $links);
      }
    ?></div>
    A fun, friendly RCOS project. Albert Armea, Sebastian Basch, Javier Camino, Josh Goldberg, Harish Lall, Evan MacGregor, and Aaron Sedlack.
  </div>
</footer>

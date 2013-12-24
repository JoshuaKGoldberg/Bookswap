<footer>
  <div class="standard_main">
    <div><?php
      if(UserLoggedIn()) {
        $links = ['Account', 'Import', 'Log Out'];
        for($i = 0, $len = count($links); $i < $len; ++$i)
          $links[$i] = getLinkHTML(strtolower(str_replace(' ', '', $links[$i])), $links[$i]);
        echo implode('<span>&sdot;</span>', $links);
      }
    ?></div>
    Josh Goldberg & Acquaintances. A future RCOS project.
  </div>
</footer>
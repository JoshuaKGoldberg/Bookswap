<footer>
  <div class="standard_main">
    <div><?php
      if(UserLoggedIn()) {
        $links = ['Account', 'Import'];
        for($i = 0, $len = count($links); $i < $len; ++$i)
          $links[$i] = getLinkHTML($links[$i], $links[$i]);
        echo implode('<span>&sdot;</span>', $links);
      }
    ?></div>
    Josh Goldberg & Acquaintances. A future RCOS project.
  </div>
</footer>
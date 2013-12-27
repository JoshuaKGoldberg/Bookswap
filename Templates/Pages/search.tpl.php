<section>
  <h1 class="standard_main standard_vert giant">Search</h1>
  
  <form class="standard_width" id="search_form" onsubmit="event.preventDefault(); searchFull();">
    <input id="search_input" type="text" placeholder="fully search our database from here" />
    <select id="search_change">
      <?php
        $options = ['Title', 'Author(s)', 'Description', 'Publisher', 'Year', 'ISBN'];
        echo '<option>' . implode('</option><option>', $options) . '</option>';
      ?>
    </select>
    <input id="search_submit" type="submit" value="Go!" />
  </form>
  
  <div id="search_full_res_holder" class="standard_main standard_vert">
    <div id="search_full_results"></div>
  </div>
</section>
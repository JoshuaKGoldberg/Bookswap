<section>
  <h1 class="standard_main standard_vert giant">Search</h1>

  <div id="search_form">
    <form id="search_form_wrapper" class="standard_main" onsubmit="event.preventDefault(); searchFull();">
      <div id="input_wrapper">
        <input id="search_input" type="text" placeholder="fully search our database from here"/>
      </div>
      <select id="search_change">
        <?php
          $options = ['All', 'Title', 'Author(s)', 'Description', 'Publisher', 'Year', 'ISBN'];
          echo '<option>' . implode('</option><option>', $options) . '</option>';
        ?>
      </select>
      <select id="search_limit">
        <?php
          $options = ['Limit...', '10', '25', '50', '100'];
          echo '<option>' . implode('</option><option>', $options) . '</option>';
        ?>
      </select>
    <button id="search_submit" type="submit">Go!</button>
    </form>
  </div>
  
  <div id="search_full_res_holder" class="standard_main standard_vert">
    <div id="search_full_results"></div>
  </div>
</section>
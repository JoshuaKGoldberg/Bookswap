<div id="header_search">
  
  <div id="header_search_results">
    <div id="header_search_results_contents"></div>
  </div>
  
  <form id="header_search_form" onkeydown="searchStart();" onsubmit="event.preventDefault(); searchStartFull();">
    <input id="header_search_input" type="text" placeholder="search <?php echo getSiteName(); if(UserLoggedIn()) echo ', ' . $_SESSION['username']; ?>" />
  </form>
  
  <div id="header_search_submit" onclick="searchStartFull();"></div>
  
</div>
<div id="header_search" class="headblock">
  
  <form id="header_search_form" onkeydown="searchStart(event);" onsubmit="event.preventDefault(); searchStartFull();">
    <input id="header_search_submit" type="submit" value="Go!" />
    
    <input id="header_search_input" type="text" placeholder="search <?php echo getSiteName(); if(UserLoggedIn()) echo ', ' . htmlentities($_SESSION['username']); ?>" />
  
    <div id="header_search_results" class="hidden">
      <div id="header_search_results_contents"></div>
    </div>
  </form>
  
</div>
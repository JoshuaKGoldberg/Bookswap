/* Searches (done by the header search bar)
*/

// Start the searches
function searchStart() {
  setTimeout(function() {
    var value = $("#header_search_input").val().trim(),
        columns;
    
    // Make sure only the most recent request is continued
    if(!window.search_request_num) window.search_request_num = 1;
    else ++window.search_request_num;
    
    // If nothing was searched, clear it
    if(!value) {
      $("#header_search_results_contents").html("");
      return;
    }
    
    // If a number is given, just search on ISBN
    if(!isNaN(value))
      columns = ["isbn"];
    // Otherwise search on a few fields
    columns = ["title", "authors", "description"];
    
    // Let the search function know how many columns, and what the term is
    columns.num_cols = columns.length;
    columns.value = value;
    
    // Run a search on each of the columns
    sendRequest("publicSearch", columns, function() { searchGetResult(arguments[0], window.search_request_num) });
  });
}

// Place search results in #header_search_results_contents
function searchGetResult(results, count) {
  // Make sure this is the latest request
  if(count < window.search_request_num) return;
  
  // If there aren't any results, complain
  if(!results) {
    $("#header_search_results_contents").html(searchGetNoResultsComplaint());
    return;
  }
  
  $("#header_search_results_contents").html(results);
}

function searchGetNoResultsComplaint() {
  return "<aside class='book'>No results found!</aside>";
}

// Redirects the search to the full page
function searchStartFull() {
  window.location = "index.php?page=search&value=" + $("#header_search_input").val().trim();
}
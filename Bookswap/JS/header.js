/* Searches (done by the header search bar)
*/

// Start the searches
function searchStart(event) {
  var input = $("#header_search_input");
  
  // The escape key means to cancel everything
  if(event.keyCode == 27) {
    $("#header_search").removeClass("searching");
    $("#header_search_input").val("");
    $("#header_search_results").addClass("hidden");
    return;
  }
  
  // All other events timeout a search
  setTimeout(function() {
    var value = input.val().trim(),
        columns;
    
    // If nothing was searched, clear it
    if(!value) {
      $("#header_search").removeClass("searching");
      $("#header_search_results").addClass("hidden");
      $("#header_search_results_contents").html("");
      return;
    }
    
    // Since something is being searched, add the .searching class
    $("#header_search").addClass("searching");
    
    // Search on these fields by default
    columns = ["title", "authors", "description"];
    // If a 10-digit number is given, also search on ISBN
    if(!isNaN(value) && value.length == 10)
      columns.push("isbn");
    
    // Let the search function know how many columns, and what the term is
    columns.num_cols = columns.length;
    columns.value = value;
    
    // Run a search on each of the columns
    sendRequest("publicSearch", columns, function(results) { searchGetResult(results, value); });
  });
}

// Place search results in #header_search_results_contents
function searchGetResult(results, value) {
  var input = $("#header_search_input");
  // Make sure this is the current request by seeing if the current input value is the same as the query
  if(value != input.val().trim())
    return;
  
  // Visually enable the search results
  $("#header_search").removeClass("searching");
  $("#header_search_results").removeClass("hidden");
  
  // If there aren't any results, complain instead
  $("#header_search_results_contents").html(results || searchGetNoResultsComplaint())
}

function searchGetNoResultsComplaint() {
  return "<aside class='book'>No results found!</aside>";
}

// Redirects the search to the full page
function searchStartFull() {
  window.location = "index.php?page=search&value=" + $("#header_search_input").val().trim();
}


/* Facebook interactions
*/

// Called when the #fb_button element is being hovered (onmouseover)
function fbHoverOn() { $("#fb_holder").addClass("hover"); }
function fbHoverOff() { $("#fb_holder").removeClass("hover"); }
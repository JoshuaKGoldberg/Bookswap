// Starts the page off with any settings from the URL
$(document).ready(function() {
  var value;
  
  // Check if a type of search is given (name, author, etc.)
  console.log("hi",$.QueryString, $.QueryString["type"]);
  if(value = $.QueryString["type"]) {
    console.log("Got type", value);
    $("#search_change").val(decodeURIComponent(value));
  }
  
  // Check if a search value is given via the URL
  if(value = $.QueryString["value"]) {
    $("#search_input").val(decodeURIComponent(value));
    searchFull(value);
  }
});


function searchFull(value) {
  if(!value)
    if(!(value = $("#search_input").val()))
      return;
  
  sendRequest("publicSearch", {
    value: value,
    format: 'Large',
    column: $("#search_change").val()
  }, searchFullResults);
}

function searchFullResults(results) {
  $("#search_full_results").html(results);
}
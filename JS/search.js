// Check if a value is given via the URL
$(document).ready(function() {
  var value = $.QueryString["value"];
  if(!value) return;
  $("#search_input").val(value);
  searchFull(value);
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
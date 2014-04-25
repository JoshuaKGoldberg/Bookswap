function callSiteInstall() {
  var inputs = $("input[type=text]"),
      query = '',
      input, name, i, len;
  for(i = 0, len = inputs.length; i < len; ++i) {
    input = inputs[i];
    name = input.id;
    query += name + '=' + encodeURIComponent(input.value) + '&';
  }
  $("#results").html("Thinking...");
  return $.ajax({
    url: "tests.php?" + query
  }).done(readSiteInstall(query));
}

function readSiteInstall(query) {
  return function(results) {
    $("#results").html(results);
    if(results == 'Ok!') 
      setTimeout(function() { window.location = location.protocol + '//' + location.host + location.pathname + '?' + query; }, 1400);
  }
}
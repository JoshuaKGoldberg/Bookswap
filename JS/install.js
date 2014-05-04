function callSiteInstall(event) {
    var inputs = $("input[type=text], input[type=password]"),
      query = '',
      input, name;
    $("#results").html("Thinking...");

    // If there was an event, this was called from the submit button
    // That means the replacement functions should be sent to tests.php
    if(event && event.preventDefault) {
        event.preventDefault();
    
        // Create a query from the inputs to change the settings.php file
        inputs.each(function(i, input) {
            query += input.id + '=' + encodeURIComponent(input.value) + '&';
        });
    }
    
    // Submit a request to tests.php, with the generated function for completion
    return $.ajax({
        url: "tests.php?" + query
    }).done(readSiteInstall(query));
}

function readSiteInstall(query) {
    return function(results) {
        $("#results").html(results);
        
        if(results === "Thinking hard...") {
            callSiteInstall();
            return;
        }
        
        if(results == 'Ok!') {
            setTimeout(function() {
                var loc = '';
                loc += location.protocol;
                loc += '//';
                loc += location.host;
                loc += location.pathname;
                loc += '?' + query;
                window.location = loc;
            }, 1400);
        }
    }
}

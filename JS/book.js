// Gathers information about a submit-entry request, then submits it
// Can't use automated sendRequestForm because IDs might be duplicate
function entryAddSubmit(event, isbn) {
    var form = event.target,
        settings = {
            isbn: isbn,
            title: form.getElementsByClassName("entry_title")[0].innerText,
            state: form.getElementsByClassName("entry_state")[0].value,
            action: form.getElementsByClassName("entry_action")[0].value,
            dollars: form.getElementsByClassName("num_dollars")[0].value,
            cents: form.getElementsByClassName("num_cents")[0].value
        };
    sendRequest("publicEntryAdd", settings, function(result) {
        entryAddFinish(result, event.target, settings);
    });

    // Mention it's working
    $(form).find("input[type=submit]").val("thinking...");
}

function entryAddFinish(result, form, settings) {
    // Display the result in the HTML
    var displayer = form.getElementsByClassName("entry_results");
    displayer[0].innerHTML = result;
    $(form).find("input[type=submit]").val("Go!");

    // If it's a success, check for FB integration, then reload the page
    if(result == "Entry added successfully!") {
        FB.getLoginStatus(function(status) {
            // If logged in, try to post to Facebook, *then* reload
            if(status.status.trim().toLowerCase() === "connected") {
                facebookPost("Hey I want to " 
                        + settings.action.toLowerCase() + " a copy of a " 
                        + settings.title + " for "
                        + '$' + settings.dollars + '.' + settings.cents + ". "
                        + "Any takers?\n\n "
                        + window.location.href,
                    function() {
                        window.location.reload();
                    }
                );
            }
            // Otherwise just reload immediately
            else {
                window.location.reload();
            }
        });
    }
}
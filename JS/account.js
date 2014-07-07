// When ready, if there's a "notification_id" parameter, set that notification as visited
$(document).ready(function () {
    setNotificationVisited($.QueryString.notification_id);
});

// On visiting page=account with &notification_id=XYZ, delete it in the database
function setNotificationVisited(notification_id) {
    if(!notification_id) {
        return;
    }
    sendRequest("publicDeleteNotification", {
        "notification_id": notification_id
    });
}

// Updates the search bar after the username is edited
// This should have been passed as the callback in account.tpl.php
function updateSearchUsername(results, settings) {
    var input = $("#header_search_input"),
        is_ok = !results.length,
        blurb = input.attr('placeholder'),
        last = blurb.lastIndexOf(settings.value_old);

    // Remove the previous updater_username
    $(".updater_username").remove();

    // If the results aren't blank, something happened. Complain.
    if (!is_ok) {
        $("#username")
            .after($("<div class='updater updater_username'>" + results + "</div>"))
            .find("span")
            .text(settings.value_old);
    }

    input.attr('placeholder', blurb.slice(0, last) + settings[is_ok ? "value" : "value_old"]);
}

// Displays any messages regarding API calls to update a book
function updateEmailDisplay(results, settings) {
    // Remove any previous updater_email
    $(".updater_email").remove();

    // If there's no message, all is well
    if (!results) {
        return;
    }

    $("#contact_info")
        .append($("<div class='updater_email'>" + results + "</div>"))
        .text(results);
}


/* Entries - editing
*/

function onEntryEditClick(event, entry_id) {
    var target = $(event.target),
        parent = target.closest(".entry-table tr"),
        price = parent.attr("price");

    // If it's currently being edited, submit the edit
    if (parent.hasClass("editing")) {
        parent.removeClass("editing");
        onEntryEditSubmit(parent, entry_id);
    }
    // If it's not being edited, switch everything to being edited
    else {
        parent.addClass("editing");
        onEntryEditClickOn(parent, entry_id);
    }
}

function onEntryEditClickOn(parent, entry_id) {
    var pricer = parent.find(".entry-price"),
        stater = parent.find(".entry-state"),
        butter = parent.find(".entry-edit");
    
    pricer.html(getEntryInputPrice(pricer.attr("data-entry-price")));
    stater.html(getEntryInputState(stater.attr("data-entry-state"), parent.attr("data-entry-states").split(",")));
}

function getEntryInputPrice(price) {
    var output = "<span class='entry-price-edit' data-entry-price='" + price + "'>";
        // Split the price into ['', '#dollars', '#cents']
        price = price.split(/[^0-9]/),
        dollas = price[0],
        cents = Number(price[1]);
    
    // Dollars
    output += "<span class='dollas'>$</span>";
    output += "<input class='edit-dollas' type='number' value='" + dollas + "'/>";
    
    // Cents
    output += "<select class='edit-cents'>";
    output += "<option>00</option>";
    for (var i = 25; i <= 75; i += 25) {
        output += "<option " + (i == cents ? "selected" : "") + ">" + i + "</option>";
    }
    
    output += "</select></span>";
    return output;
}

function getEntryInputState(state, states) {
    var output = "<select class='entry-state-edit' data-entry-state='" + state + "'>";
    
    for(var i = 0; i < states.length; i += 1) {
        output += "<option " + (states[i] == state ? "selected" : "") + ">" + states[i] + "</option>";
    }
    
    output += "</select>";
    return output;
}

function onEntryEditSubmit(parent, entry_id) {
    var pricer = parent.find(".entry-price"),
        stater = parent.find(".entry-state"),
        butter = parent.find(".entry-edit"),
        price, state;
    
    entryEditCollectValues(pricer, stater, entry_id);
    
    price = pricer.attr("data-entry-price");
    state = stater.attr("data-entry-state");
    
    sendRequest("publicEntryEdit", {
        "entry_id": entry_id,
        "price": price,
        "state": state
    }, onEntrySubmitCompletion(parent, entry_id));
}

function entryEditCollectValues(pricer, stater, entry_id) {
    collectEntryPrice(pricer);
    collectEntryState(stater);
}

function collectEntryPrice(pricer) {
    var dollars = pricer.find(".edit-dollas").val(),
        cents = pricer.find(".edit-cents").val();
        price = dollars + "." + cents;
    
    pricer.attr("data-entry-price", price);
    pricer.html("$" + dollars + "." + cents.slice(0, 2));
}

function collectEntryState(stater) {
    var val = stater.find("select").val();
    stater.attr("data-entry-state", val);
    stater.html(val);
}

function onEntrySubmitCompletion(parent, entry_id) {
    return function (resultsRaw) {
        var results = JSON.parse(resultsRaw);
        if(results.failure) {
            alert(results.message);
        }
    };
}


/* Entries - deleting
*/

function onEntryEditDelete(event, entry_id) {
    var target = $(event.target),
        parent = target.closest(".entry-table tr");
    
    sendRequest("publicEntryDelete", {
        "entry_id": entry_id
    }, onEntrySubmitCompletion(parent, entry_id));
    
    parent.remove();
}
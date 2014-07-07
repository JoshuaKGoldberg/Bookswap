// When the document loads, replace the Amazon search link with a direct
// link to the book's page by 10-digit ISBN (ASIN)... except no, some of those
// links will be no good, so don't do this.
// Ex. ISBN13 9780307387646 -> ASIN 030738764X (actually B000VSW7X4)
$(document).ready(function() {
    /*
    var view_amazon = document.getElementById("view_amazon"),
        isbn = view_amazon.getAttribute("isbn");
    view_amazon.setAttribute("href", 
            "http://amazon.com/dp/" + convertISBN(isbn));
    */
});

// Gathers information about a submit-entry request, then submits it
// Can't use automated sendRequestForm because IDs might be duplicate
function entryAddSubmit(event, isbn, title) {
    var form = $(event.target),
        price = form.find(".num_dollars")[0].value + '.' + form.find(".num_cents")[0].value,
        settings = {
            "title": title,
            "isbn": isbn,
            "price": price,
            "state": form.find(".entry_state").val(),
            "action": form.find(".entry_action").val()
        };
    sendRequest("publicEntryAdd", settings, function (resultsRaw) {
        entryAddFinish(resultsRaw, event.target, settings);
    });

    // Mention it's working
    $(form).find("input[type=submit]").val("thinking...");
}

function entryAddFinish(resultsRaw, form, settings) {
    var result = JSON.parse(resultsRaw),
        displayer = form.getElementsByClassName("entry_results");
    
    displayer[0].innerHTML = result.message;
    $(form).find("input[type=submit]").val("Go!");

    // If it's a success, check for FB integration
    if(result.status === "success") {
        debugger;
        FB.getLoginStatus(function(status) {
            // If logged in, try to post to Facebook, *then* reload
            if(status.status.trim().toLowerCase() === "connected") {
                facebookPost("Hey I want to " 
                        + settings.action.toLowerCase() + " a copy of a " 
                        + settings.title + " for "
                        + '$' + settings.price + ". "
                        + "Any takers?\n\n "
                        + window.location.href,
                    location.reload.bind(location)
                );
            }
            // Otherwise just reload immediately
            else {
                window.location.reload();
            }
        });
    }
}

/**
 * Function to convert a 13-digit ISBN to 10-digit
 * 
 * @param {String} isbn   The ISBN to be converted to a 10-digit equivalent
 * @return {String}   A 10-digit ISBN equivalent to the given ISBN
 * @remarks   This was obtained from the Library of Congress' ISBN converter,
 *            located at http://pcn.loc.gov/isbncnvt.html
 *            10-digit ISBNS are useful because the ASIN (Amazon Standard
 *            Identification Number) is generally the same as the 10-digit ISBN
 *            (not always, but close enough). We don't deal with international
 *            shenanigans, so that shouldn't be a concern.
 * @example   Getting the Amazon URL for a book by ISBN
 *            var url = "http://amazon.com/dp/" + convertISBN(9782020253802);
 */
function convertISBN(isbn) {
    var isbn_str = String(isbn),
        total = 0;

    // Validate & convert a 10-digit ISBN
    if (isbn_str.length === 10) {
        // Test for 10-digit ISBNs:
        // Formulated number must be divisible by 11
        // 0234567899 is a valid number
        for (var x = 0; x < 9; x += 1) {
            total = total + (isbn_str.charAt(x) * (10 - x));
        }

        // check digit
        z = isbn_str.charAt(9);
        if (z == "X") {
            z = 10;
        }

        // validate ISBN
        if ((total + z * 1) % 11 != 0) { // modulo function gives remainder

        } else {
            // convert the 10-digit ISBN to a 13-digit ISBN
            isbn_str = "978" + isbn_str.substring(0, 9);
            total = 0;
            for (var x = 0; x < 12; x++) {
                if ((x % 2) == 0) {
                    y = 1;
                } else {
                    y = 3;
                }
                total = total + (isbn_str.charAt(x) * y);
            }
            z = (10 - (total % 10)) % 10;
        }
    }

    // Validate & convert a 13-digit ISBN
    else {
        // Test for 13-digit ISBNs
        // 9780234567890 is a valid number
        for (var x = 0; x < 12; x++) {
            if ((x % 2) == 0) {
                y = 1;
            } else {
                y = 3;
            }
            total = total + (isbn_str.charAt(x) * y);
        }

        // check digit
        z = isbn_str.charAt(12);

        // validate ISBN: modulo function gives remainder
        if ((10 - (total % 10)) % 10 == z) { 
            // convert the 13-digit ISBN to a 10-digit ISBN
            if ((isbn_str.substring(0, 3) === "978")) {
                isbn_str = isbn_str.substring(3, 12);
                total = 0;
                for (var x = 0; x < 9; x++) {
                    total = total + (isbn_str.charAt(x) * (10 - x));
                }
                z = (11 - (total % 11)) % 11;
                if (z == 10) {
                    z = "X";
                }
            }
        }
    }

    return isbn_str + z;
}

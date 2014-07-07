/* Book Imports
 * Allow the user to add in any amount of text
 * This parses the text for 10- and 13-digit ISBNs
 * Those ISBNs are send to publicISBNCheck
 * The type is passed in as either 'isbn' or 'full'
 */
// On page load, if ?import=<any>, immediately check to import that
$(document).ready(function () {
    if ($.QueryString["import"]) {
        $("#import_full input[type=text]").val($.QueryString["import"]);
        importBook(undefined, "full");
    }
});

// importBook(event, "type")
// Takes the input from the respective input[type=text] and pipes it to PHP
// If it's ISBN(s), it performs splitting and parsing 
function importBook(event, type) {
    setTimeout(function () {
        // Look at all the potential ISBNs, split by whitespace
        var values = $("#import_" + type + " input").val(),
            jthinker = $("#import_" + type + "_thinking"),
            thinker = jthinker[0],
            value, i;

        // Don't allow tiny searches
        if (values.length <= 2) {
            jthinker.text("searches must be > 2 characters");
            return;
        }
        
        jthinker.text("thinking...");

        // If this is on ISBN, filter that
        if (type == 'isbn') {
            values = filterISBNs(values);
            // Make values unique, so repeats aren't spammed
            values = getDistinctArray(values);
        }
        // Otherwise it's a single search
        else {
            values = [values];
        }

        // Don't allow repeat queries across types
        values = values.filter(function (value) {
            if (!window.repeat_vals) {
                window.repeat_vals = [value];
                return true;
            }
            var ok = window.repeat_vals.indexOf(value) === -1;
            window.repeat_vals.push(value);
            return ok;
        });
        if (!values) {
            return;
        }
        
        for (i in values) {
            value = values[i];
            // If the value isn't an ISBN, and should be, ignore it
            if (type == 'isbn' && !isValidISBN(value)) return;

            // Otherwise see what happens if you try to add it
            if (!thinker.thinking) thinker.thinking = 1;
            else ++thinker.thinking;
            jthinker.addClass("thinking");
            sendRequest("publicBookImport", {
                type: type,
                isbn: value,
                value: value
            }, function (result) {
                importBookGetResult(result, type);
            });
        }
    });
}

// Filters a text for all ISBNs
function filterISBNs(value) {
    var raws = value.replace(new RegExp('-', 'g'), "").match(/\S+/g),
        output = [],
        raw, i;
    for (i in raws) {
        raw = raws[i].replace(/\D/g, '');
        if (isValidISBN(raw)) {
            output.push(raw);
        }
    }
    return output;
}

// Only allows numbers of length 13
// (no longer accept length-10, since they may start with 0)
function isValidISBN(value) {
    return value && !isNaN(value) && (value.length == 10 || value.length == 13);
}

// Used to limit excessive duplicates
// http://stackoverflow.com/questions/9229645/remove-duplicates-from-javascript-array#answer-14740171
function getDistinctArray(arr) {
    var dups = {},
        hash, is_dup;
    return arr.filter(function (a) {
        hash = a.valueOf();
        is_dup = dups[hash];
        dups[hash] = true;
        return !is_dup;
    });
}

function importBookGetResult(resultsRaw, type) {
    var results = JSON.parse(resultsRaw),
        parent = $("#import_" + type + "_results"),
        url = location.origin + location.pathname + "?page=book&isbn=";
        
    console.log(results);
    
    if(results.status === "failure") {
        parent.prepend(
            $("<p>")
                .addClass("import import-failed")
                .html("Import failed! " + results["message"])
        );
    } else {
        results.message.forEach(function (result) {
            parent.prepend(
                $("<p>")
                    .text(" " + result["status"])
                    .addClass("import import-" + (result["added"] ? "added" : "failed"))
                    .prepend(
                        $("<a>")
                            .attr("href", url + result["isbn"])
                            .text(result["title"])
                    )
            );
        });
    }    
    
    // Decrement the amount of known thinking
    var jthinker = $("#import_" + type + "_thinking"),
        thinker = jthinker[0];
    
    --thinker.thinking;
    
    if (!thinker.thinking) {
        jthinker.removeClass("thinking");
    }
}
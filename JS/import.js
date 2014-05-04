/* Book Imports
 * Allow the user to add in any amount of text
 * This parses the text for 10- and 13-digit ISBNs
 * Those ISBNs are send to publicISBNCheck
 * The type is passed in as either 'isbn' or 'full'
*/

// On page load, if ?import=<any>, immediately check to import that
$(document).ready(function() {
    if($.QueryString.import) {
        $("#import_full input[type=text]").val($.QueryString.import);
        importBook(undefined, "full");
    }
});

// importBook(event, "type")
// Takes the input from the respective input[type=text] and pipes it to PHP
// If it's ISBN(s), it performs splitting and parsing 
function importBook(event, type) {
  setTimeout(function() {
    // Look at all the potential ISBNs, split by whitespace
    var values = $("#import_" + type + " input").val(),
        jthinker = $("#import_" + type + "_thinking"),
        thinker = jthinker[0],
        value, i;
    
    // Don't allow tiny searches
    if(values.length <= 2) {
      jthinker.text("searches must be > 2 characters");
      return;
    }
    else jthinker.text("thinking...");
    
    // If this is on ISBN, filter that
    if(type == 'isbn') {
      values = filterISBNs(values);
      // Make values unique, so repeats aren't spammed
      values = getDistinctArray(values);
    }
    // Otherwise it's a single search
    else values = [values];
    
    // Don't allow repeat queries across types
    values = values.filter(importValuesNotRepeatedFilter);
    
    if(!values) return;
    for(i in values) {
      value = values[i];
      // If the value isn't an ISBN, and should be, ignore it
      if(type == 'isbn' && !isValidISBN(value)) return;
      
      // Otherwise see what happens if you try to add it
      if(!thinker.thinking) thinker.thinking = 1;
      else ++thinker.thinking;
      jthinker.addClass("thinking");
      sendRequest("publicBookImport", {
        type: type,
        isbn: value,
        value: value
      }, function(result) { importBookGetResult(result, type); });
    }
  });
}

// Filters a text for all ISBNs
function filterISBNs(value) {
  var raws = value.replace(new RegExp('-', 'g'), "").match(/\S+/g),
      output = [],
      raw, i;
  for(i in raws) {
    raw = raws[i].replace(/\D/g,'');
    if(isValidISBN(raw)) {
      // if(raw.length == 10) {
        // output.push('978' + raw);
        // output.push('979' + raw);
      // }
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

function importValuesNotRepeatedFilter(me) {
  if(!window.repeat_vals) {
    window.repeat_vals = [me];
    return true;
  }
  var ok = window.repeat_vals.indexOf(me) == -1;
  window.repeat_vals.push(me);
  return ok;
}

// Used to limit excessive duplicates
// http://stackoverflow.com/questions/9229645/remove-duplicates-from-javascript-array#answer-14740171
function getDistinctArray(arr) {
  var dups = {},
      hash, is_dup;
  return arr.filter(function(a) {
      hash = a.valueOf();
      is_dup = dups[hash];
      dups[hash] = true;
      return !is_dup;
  });
}

function importBookGetResult(result, type) {
  // If the result begins with 'Yes', remove that
  if(result.indexOf("Yes") == 0)
    result = result.substr(3);
  
  // If the result is still good, add it
  if(result && result.toLowerCase() != "no") {
    var child = document.createElement("p");
    child.innerHTML = result;
    $("#import_" + type + "_results").prepend(child);
  }
  // Decrement the amount of known thinking
  var jthinker = $("#import_" + type + "_thinking"),
      thinker = jthinker[0];
  --thinker.thinking;
  if(!thinker.thinking)
    jthinker.removeClass("thinking");
}

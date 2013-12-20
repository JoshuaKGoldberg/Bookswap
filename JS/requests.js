/* Our Small Requests Framework */
/*
This enables users to run a PHP function (func_name) with
a set of arguments (settings), and send the results to a
callback function (callback).

The callback function must:
* Exist in php/public_functions.php
* Have its name declared in $allowed_functions in settings.php

For example, the following will console.log each given argument:

PHP function
------------
function publicHelloWorld($arguments) {
  foreach($arguments as $key=>$argument) {
    echo 'Argument is ' . $key . ' -> ' . $argument . '\n';
  }
}

Javascript function
-------------------
sendRequest("publicHelloWorld", {
  first_variable: 'my first value',
  second_variable: 'hello momma!',
  third_variable: 'we're done here.'
}, function(text) { console.log(text); });

Output
------
"
Argument is first_variable -> my first value
Argument is second_variable -> hello momma!
Argument is third_variable -> we're done here.
"

*/
/* Auto-loading
In addition, the PHP function PHP/templates.inc.php::PrintRequest
prints an HTML div with the details of a sendRequest call. It will
be detected by loadPrintedRequests, and run.

For example, to call a sample function from a template:

PHP code
--------
<?php PrintRequest("PrintUserBooks", [$user_id, 'sell']); ?>

Resultant HTML (line breaks for clarity)
--------------
<div class="php_request_load loading"
     request="PrintUserBooks"
     num_args = "2"
     arg0="4"
     arg1="sell">
  <hr />
  <div class="loader">loading</div>
</div>

Resultant JS call
-----------------
sendRequest("PrintUserBlocks", {"4", "sell"}, loadRequestAuto);
 
*/

// sendRequest("func_name", {settings}[, callback])
// * Sends an AJAX request to a PHP function func_name
// * Arguments are given by the settings object
// * Callback is called when it's done
function sendRequest(func_name, settings, callback) {
  var url = "PHP/Requests.php?",
      args = [],
      s_name;
  
  // Generate the list of arguments
  settings["Function"] = func_name;
  for(s_name in settings)
    args.push(s_name + "=" + settings[s_name]);
  
  // Add those arguments to the url
  url += args.join("&");
  
  // Create and return the jqXHR object
  return $.ajax({
    url: url
  }).done(callback || function() {});
}

// sendRequestForm("func_name", ["ids"][, callback[, validation]])
// A specialized helper that calls sendRequest with the values of forms
// Form IDs are given in [ids]
// An extra optional validation function is allowed
function sendRequestForm(func_name, ids, callback, validation) {
  var settings = {},
      elem, id, i;
      
  // For each of the given IDs:
  for(i in ids) {
    id = ids[i];
    // If an element matches that ID, add it to settings
    elem = document.getElementById(id);
    settings[id] = elem ? elem.value : "";
  }
  
  // A validation of true means ensureNoBlanks
  if(validation === true)
    validation = ensureNoBlanks;
  
  // If a validation function is provided, make sure it's ok
  if(validation && !validation(settings))
    return;

  // Since you're good, run the request normally
  sendRequest(func_name, settings, callback);
}

// ensureNoBlanks({arg1[, arg2[, ...]]})
// Makes sure everything given to a form 
function ensureNoBlanks(settings) {
  for(var i in settings)
    if(!settings[i])
      return false;
  return true;
}

// Finds each div.php_request_load, and makes them call their printed request
function loadPrintedRequests() {
  $(".php_request_load").each(loadPrintedRequest);
}
// Given a div from loadPrintedRequests, find its attributes and make the actual request
function loadPrintedRequest(i, div) {
  var request = div.getAttribute("request"),
      num_args = Number(div.getAttribute("num_args")),
      args = [],
      timeout = div.getAttribute("timeout") || 0,
      argtext, commaloc, i;
  
  // Each argument i is in the form 'argi="argname,argument"'
  for(i = 0; i < num_args; ++i) {
    argtext = div.getAttribute("arg" + i);
    commaloc = argtext.indexOf(',');
    args[argtext.substr(0, commaloc)] = argtext.substr(commaloc + 1);
  }
  
  // With all the arguments parsed, send the request as scheduled
  setTimeout(function() { 
    sendRequest(request, args, function(result) {
      loadPrintedRequestResults(result, div); }
    );
  }, timeout);
}
// Puts the result from loadPrintedRequest in a div
function loadPrintedRequestResults(result, div) {
  div.outerHTML = result;
}
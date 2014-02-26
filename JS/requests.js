/* Our Small Requests Framework */
/*
This enables users to run a PHP function (func_name) with
a set of arguments (settings), and send the results to a
callback function (callback).

The callback function must:
* Have its name in php/requests.php::$allowed_functions
* Exist in php/public_functions.php

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

// sendRequest("func_name", {settings}[, callback])
// * Sends an AJAX request to a PHP function func_name
// * Arguments are given by the settings object
// * Callback is called when it's done
function sendRequest(func_name, settings, callback) {
  var url = "PHP/requests.php?",
      args = [],
      s_name;
  
  // Generate the list of arguments
  settings["function"] = func_name;
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


// See templates.inc.php::PrintRequest
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

/* Editable Components */
/*
  This is the front-end counterpart to PHP/templates.inc.php::PrintEditable.
  
  When an 'editable' element is clicked, it turns into a user-editable form.
  Upon submission of that form, sendRequest() is used with a provided PHP function.
  
  This is notably missing some optional features, such as:
  * multiple forms
  * custom forms (e.g. <select> w/<option>s)
  * result validation
  
  Default values are:
  * 'type' => 'text'
  * 'index' => 'value'
*/
function editClick(func_name, settings, event) {
  var target = event.target,
      value_old = settings.value_old = target.innerText || target.innerHTML,
      click_old = settings.click_old = target.onclick,
      settings = settings || {},
      type = settings.type || 'text',
      index = settings.index || 'value',
      output = '';

  output += "<form>";
  // Having multiple input types means printing them all
  if(settings.hasOwnProperty("types")) {
    output += "<div class='" + target.className + "'>";
    for(var i = 0, types = settings.types, len = types.length; i < len; ++i) {
      output += "  <input type='" + (types[i] || 'text') + "' />";
    }
    output += "</div>";
  }
  // Otherwise, only having one input is easy
  else {
    output += "  <input class='" + target.className + "' type='" + (settings.type || 'text') + "' />";
  }
  output += "</form>";
  target.innerHTML = output;
  
  target.className += " editing";
  target.onclick = false;
  target.getElementsByTagName("input")[0].setAttribute("value", value_old);
  
  target.onsubmit = function(event) {
    event.preventDefault();
    editSubmit(event.target, func_name, settings);
  };
}

// When an editable component is submitted, collect the difference in values
// A request is sent to the desired func_name, and if given, the callback is provided
function editSubmit(form, func_name, settings) {
  var input = form.getElementsByTagName("input")[0],
      index = settings.index || 'value',
      value_old = settings.value_old,
      click_old = settings.click_old,
      value = $(input).val();
  
  settings[index] = value;
  settings[index + "_old"] = value_old;
  
  sendRequest(func_name, settings, function(results) {
    form.parentNode.onclick = click_old;
    form.outerHTML = value;
    if(settings.callback) {
      if(!window[settings.callback] || !(window[settings.callback] instanceof Function))
        console.warn(settings.callback + " is not a valid function.");
      else window[settings.callback](results, settings);
    }
  });
}
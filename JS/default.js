// Gathers information about a submit-entry request, then submits it
// Can't use automated sendRequestForm because IDs might be duplicate
function entryAddSubmit(event, isbn) {
  window.form = event.target;
  var form = event.target,
      title = form.getElementsByClassName("entry_title")[0].innerText,
      state = form.getElementsByClassName("entry_state")[0].value,
      action = form.getElementsByClassName("entry_action")[0].value,
      dollars = form.getElementsByClassName("num_dollars")[0].value,
      cents = form.getElementsByClassName("num_cents")[0].value;
      
  sendRequest("publicEntryAdd", {
    isbn: isbn,
    title: title,
    state: state,
    action: action,
    dollars: dollars,
    cents: cents
  }, function(result) { entryAddFinish(result, form); });
}

function entryAddFinish(result, form) {
  // Display the result in the HTML
  var displayer = form.getElementsByClassName("entry_results");
  displayer[0].innerHTML = result;
  
  // If it's a success, reload th epage
  if(result == "Entry added successfully!")
    window.location.reload();
}
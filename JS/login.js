/* Logging in
*/

function loginSubmit() {
  sendRequestForm("publicLogin", ["username", "password"], loginComplete, true);
  $("#login_submit").val("Thinking...");
}

function loginComplete(text) {
  // If the result is 'Yes', it was successful
  if(text == 'Yes') {
    var message = "You've successfully logged in! ";
    message += "You should be redirected to ";
    message += "<a href='/account.php'>your profile page</a>";
    message += " shortly; if not, click that link.";
    $("#login_form_inside").html("<aside>" + message + "</aside>");
    window.location.reload();
  }
  // Otherwise the information was incorrect
  else {
    $("#login_form_inside input:not([type=submit])")
      .css("background-color", "#fee")
      .css("border", "1px solid #733");
    $("#login_submit").val("try again!");
  }
}
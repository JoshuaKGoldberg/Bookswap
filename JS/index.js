/* Index (namely, joining)
*/

function joinSubmit() {
  sendRequestForm("publicCreateUser", ["j_username", "j_password", "j_password_confirm", "j_email"], joinComplete, joinEnsure);
  $("#user_login_text").text("Thinking...");
}

function joinEnsure(settings) {
  if(!ensureNoBlanks(settings)) return false;
  if(settings.j_password != settings.j_password_confirm) return false;
  return true;
}

function joinComplete(result) {
  console.log("Got result", result);
  // If the login attempt was successful, refresh
  if(result == "Yes") {
    window.scrollTo(0)
    location.reload();
  }
  // Otherwise complain
  $("#user_login_text").text(result);
}
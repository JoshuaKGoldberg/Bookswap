// Check if there's a user_id and verification code in the arguments
$(document).ready(function() {
  // If there is, send a request to verify the user's email
  if($.QueryString.user_id && $.QueryString.code) {
    setVerifStatusText("Thinking...");
    sendRequest("publicVerifyUser", $.QueryString, receiveVerifStatus);
  }
});

// When the publicVerifyUser request returns, handle the result with this
function receiveVerifStatus(result) {
  if(result == "Yes") {
    setVerifStatusText("You're good!");
    window.location.reload();
  }
  else setVerifStatusText(result);
}

// Sets the innerHTML of #verif_loader
// It has to start with a <br> for styling
function setVerifStatusText(str) {
  $("#verif_loader").html("<br>" + str);
}

function sendVerifEmailForm() {
  sendRequestForm("publicSetVerificationEmail", 
    ["j_email", "j_password", "j_password_confirm"],
    verifComplete, verifEnsure);
}

function verifComplete(result) {
  console.log("Got", result);
  // If the attempt was successful, refresh
  if(result == "Yes") {
    location.reload();
  }
  // Otherwise complain
  $("#email_display").text(result);
}

function verifEnsure(settings) {
  // If there's no email, nope
  if(!settings.j_email) {
    $("#email_display").html("Please give us an email... :(");
    return false;
  }
  
  // The email must be an .edu address
  if(!endsWith(settings.j_email, '.edu')) {
    $("#email_display").html("Your email has to end with '.edu'");
    return false;
  }
  
  // If a password is provided...
  if(settings.j_password) {
    // The passwords have to be sure
    if(sayPasswordSecurity(settings.j_password)) {
      $("pass_display").html("Your password isn't secure enough!");
      return false;
    }
    
    // The passwords have to match
    if(settings.j_password != settings.j_password_confirm) {
      $("#pass_display").html("The passwords don't match...");
      return false;
    }
  }
  
  $("#email_display").text("Thinking...");
  return true;
}
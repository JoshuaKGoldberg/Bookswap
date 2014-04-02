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
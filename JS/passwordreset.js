$(document).ready(function () {
    if($.QueryString.code) {
        $("#j_code").val($.QueryString.code);
    }
    
    if($.QueryString.email) {
        $("#j_email").val($.QueryString.email);
    }
    
    if($.QueryString.username) {
        $("#j_username").val($.QueryString.username);
    }
});

function setPasswordResetStatusText(str) {
    $("#reset_loader").html(str);
}

function sendPasswordResetForm() {
    sendRequestForm("publicUserPerformPasswordReset",
        ["j_code", "j_email", "j_username", "j_password", "j_password_confirm"],
        verifComplete, verifEnsure);
}

function verifEnsure(settings) {
    // If there's no password, nope
    if(!settings.j_password) {
        setPasswordResetStatusText("Please provide a password!");
        return false;
    }
    
    // If there's no password copy, nope
    if(!settings.j_password_confirm) {
        setPasswordResetStatusText("Please repeat your password, just to be sure.");
        return false;
    }
    
    // If the password isn't secure, nope
    if(sayPasswordSecurity(settings.j_password)) {
        setPasswordResetStatusText("The password isn't secure enough...");
        return false;
    }
    
    // If the passwords don't match, nope
    if(settings.j_password != settings.j_password_confirm) {
        setPasswordResetStatusText("The passwords don't match...");
        return false;
    }
    
    // If there's no email, nope
    if(!settings.j_email) {
        setPasswordResetStatusText("Please provide an email, just to be secure.");
        return false;
    }
    
    // If there's no username, nope
    if(!settings.j_username) {
        setPasswordResetStatusText("Please provide a username, just to be secure.");
        return false;
    }
    
    setPasswordResetStatusText("Thinking...");
    return true;
}

function verifComplete(result) {
    console.log("Got", result);
}
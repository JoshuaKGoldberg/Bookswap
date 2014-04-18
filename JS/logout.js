$(document).ready(function() {
    var reloading = false,
        attempt;
    
    // When a call goes through, stop trying and reload
    // The #goto element will contain the base URL to redirect
    function doReload() {
        clearTimeout(attempt);
        window.location.href = $("#goto").text();
    }
    
    // Function to be called when logging out
    function tryLoggingOut() {
        FB.getLoginStatus(function(response) {
            // If the Facebook user is logged in, log them out, then redirect
            // The #goto element will contain the base URL to redirect
            if(response) {
                // A "connected" response indicates being logged in
                if(response.status === "connected") {
                    FB.logout(doReload);
                }
                // Otherwise just redirect
                else {
                    doReload();
                }
            } 
            // Otherwise just redirect
            else {
                doReload();
            }
        });
    }
    
    // Continously attempt to log out until it works
    setInterval(tryLoggingOut, 350);
});
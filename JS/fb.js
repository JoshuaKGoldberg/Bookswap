window.fbAsyncInit = function() {
				FB.init({
					appId      : fbKey,
					status     : false,
					xfbml      : true
				});
				if(!UserLoggedIn){
					FB.getLoginStatus(function(response){
						if(response.status == 'connected'){
							var FBUID = response.authResponse.userID;
							facebookLoginSubmit(FBUID);
						}
					});
				}
				FB.Event.subscribe('auth.login', function(response) {
					if(response.status == 'connected'){
						var FBUID = response.authResponse.userID;
						if(!UserLoggedIn){
							facebookLoginSubmit(FBUID);
						}
					}
					else{
						//call log out
					}
				});
			};
			(function(d, s, id){
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {return;}
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/all.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));

function facebookLoginSubmit(FBUID){
	FB.api('/me', function(response){
			sendRequest("publicFacebookLogin", 
			{name: response.name, email: response.email, fb_id: FBUID},
			loginComplete);
		});
}

function facebookPost(message) {
    FB.login(function(){
        FB.api("/me/feed", "post", {
            "message": message
        });
    }, {
        "scope": "publish_actions"
    });
}
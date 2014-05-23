<?php
    /**
    * Semi-internal page for querying public API functions
    * 
    * This page acts as a communicator between the outside world (which sends 
    * GET-style requests here) and some internal scripts (listed by name in 
    * getAllowedFunctions()). The JS/PHP requests framework directly calls this
    * page, as does the more externally used api.php page.
    * The allowed functions are defined by $allowed_functions
    * The JS::sendRequest function goes to here and specifies a function to run
    */
    // This has to be used with a requested function
    if(isset($_GET['function'])) {
        $function_name = $_GET['function'];
    } else if(isset($_GET['Function'])) {
        $function_name = $_GET['Function'];
    } else {
        return;
    }
    $function_name = preg_replace("/[^A-Za-z_0-9]/", '', $function_name);

    require_once('../settings.php');
    require_once('../defaults.php');
    require_once('db_actions.php');
    require_once('db_login.php');
    require_once('public_functions.php');
    EnsureSessionStarted();

    // Functions the user may call via JS
    $allowed_functions = array(
        'publicTest',
        'publicUserCreate', 'publicUserVerify',
        'publicUserSetVerificationEmail', 'publicUserSendWelcomeEmail', 
        'publicUserLogin', 'publicUserLoginFacebook', 
        'publicUserEditUsername', 'publicUserEditPassword',
        'publicUserEditEmail', 'publicUserEditEmailEdu',
        'publicSearch', 'publicBookGetEntries', 'publicBookImport',
        'publicPrintUserBooks', 'publicPrintRecentListings',
        'publicEntryAdd', 'publicEntryEditPrice', 'publicEntryDelete',
        'publicPrintRecommendationsDatabase',
        'publicPrintRecommendationsUser',
        'publicGetNumNotifications', 'publicPrintNotifications', 
        'publicDeleteNotification'
    );

    // If the user doesn't request one of these functions, quit
    if(!in_array($function_name, $allowed_functions)) return;

    // If it is, run the function
    call_user_func($function_name, $_GET);
?>

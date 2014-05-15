<?php
    /**
     * Public BookSwap API 
     * 
     * Public interface for the available functions in PHP/public_functions.php.
     * This should be used instead of direct public_functions.php calls (except
     * for internal use, such as requests.js). Although this is slightly slower,
     * it hides the internal calls and automatically enables verbosity.
     * 
     * @example http://www.rpibookswap.com/api.php?function=Test
     * @example http://www.rpibookswap.com/api.php?function=Test&format=json
     * @param String function   The function from PHP/public_functions to call.
     *                          If this does not start with "public", that will
     *                          be prepended to the string.
     * @param String format   The format for output
     * @todo Instead of just printing "Provide a function.", use an actual help
     *       page.
     * @package BookSwap
     */
    
    require_once('defaults.php');
    require_once('settings.php');
    require_once('PHP/public_functions.php');
    
    // This page is useless without a provided function
    if(!isset($_GET['function'])) {
        echo 'Provide a function.';
        return;
    }
    
    // Automatically prepend "public" to function names, as per the standard
    // e.g. "Test" becomes "publicTest"
    if(!startsWith($_GET['function'], 'public')) {
        $_GET['function'] = 'public' . $_GET['function'];
    }
    
    // If verbosity level is not given by the user, set it to the default
    if(!isset($_GET['verbose'])) {
        $_GET['verbose'] = getDefaultAPIVerbosity();
    }
    
    // If format is not given by the user, set it to the default
    if(!isset($_GET['format'])) {
        $_GET['format'] = getDefaultAPIFormat();
    }
    
    // Build the query based on all $_GET parts, and execute
    $path = getBase() . '/PHP/requests.php?' . http_build_query($_GET);
    echo getHTTPPage($path);
?>
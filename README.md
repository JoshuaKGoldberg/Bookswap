<h1>BookSwap</h1>

BookSwap is a simple service that keeps track of users, books, and book transactions users would like to do. It will recommend exchanges to users based on others who want to buy or sell the same books as them.


<h2>Installation</h2>

This requires PHP and MySQL (generally an <a href='http://www.apachefriends.org/en/xampp.html'>*AMP</a> installation or server). Go to `index.php?page=install` to install the site.


<h2>Books</h2>

Books are kept in the `books` database, keyed by their ISBN. They also store Google ID and basic information, such as title, author, and description. 
To view a book, go to `index.php?page=books&isbn=<ISBN>`.


<h3>Importing Books</h3>

The page `index.php?page=import` presents two services to add a book. They both plug into the <a href='https://developers.google.com/books/docs/v1/using'>Google Books API</a> to search for book information.

The first, viewable to all users, searches on ISBN, and will bring at most one book per search.

The second, viewable only to administrators, performs a general search given an input string. Google limits their results to at most 10 (no result offsets are used on requests).


<h2>Entries</h2>

Each entry is keyed to a user by user_id and to a book by ISBN. It has a timestamp, monetary amount, and action ('buy' or 'sell').

Users may be matched to other user's entries using simple logic: for each entry they have, all other entries that have the same ISBN but different users and actions are a match.


<h2>Templating</h2>

BookSwap follows a Drupal-style system of page templates. The driving function behind it all is `TemplatePrint` (see PHP/templates.inc.php), which takes in the name of a template, how many tabs to indent it by, and an optional list of arguments. Templates and general PHP functions may call TemplatePrint, making it easy to print blocks on the page.


<h2>Requests</h2>

See JS/requests.js and PHP/requests.php. BookSwap uses a quick and clean framework for requesting data from the server and printing it onto the page. There are two driving functions:

* JS/requests.js::`sendRequest(func_name, settings, callback)`
  
  Runs a PHP function (`func_name`) with a set of arguments (`settings`), and send the results to a callback function (`callback`). The function name must be in requests.php::$allowed_functions, for security.

* PHP/templates.inc.php::`PrintRequest($function_name, $args=[], $time=0)`

  Prints an HTML div onto the page with the `.php_request_load` class, and information on the request (function name, number of arguments, and the arguments). The requests.js::`loadPrintedRequests()` function is run on page startup, and runs the corresponding `sendRequest`.

  
<h2>History</h2>

BookSwap started as a term project for Web Systems Development. The team members were T.J. Callahan, Josh Goldberg, Evan MacGregor, Candice Poon, and Scott Sacci.
<?php 
  if(!UserLoggedIn()) return AccessDenied();
  if(!getGoogleKey())
    echo '<div class="error"><div class="standard_main">You do not have a Google key supplied in your settings file. None of this will work.</div></div>';
?>
<section>
  <h1 class="standard_main standard_vert">import by ISBN</h1>
  
  <div class="standard_main">
    <div id='import_isbn'>
      <input class="import" oninput="importBook(event, 'isbn');" type='text' placeholder='ISBN(s)' />
    </div>
    <div id="import_isbn_thinking" class="thinker"></div>
    <div id="import_isbn_results"></div>
    <h3 class="standard_main standard_vert">Give us the ISBN, and we'll do the rest.</h3>
  </div>
</section>

<section>
  <h1 class="standard_main standard_vert">import by Google Books API</h1>
  
  <div class="standard_main">
    <div id='import_full'>
      <form id='import_full_form' onsubmit="event.preventDefault(); importBook(event, 'full');" >
        <input class="import" type='text' placeholder='search terms' />
        <input type='submit' value='Go!' />
      </form>
    </div>
    <div id="import_full_thinking" class="thinker"></div>
    <div id="import_full_results"></div>
    <h3 class="standard_main standard_vert">Because you're an administrator, you may search better.</h3>
  </div>
</section>
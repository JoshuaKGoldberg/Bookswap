// Starts the page off with any settings from the URL
$(document).ready(function() {
  var value;
  
  // Check if a type of search is given (name, author, etc.)
  console.log( "hi",$.QueryString, $.QueryString["type"] );
  if( value = $.QueryString["type"] ) {
    console.log("Got type", value);
    $( "#search_change" ).val( decodeURIComponent( value ) );
  }

  var prev_val, set = false;
  if ( prev_val = value ) set = true;
  // Check if a search value is given via the URL
  if( value = $.QueryString["value"] ) {
    $( "#search_input" ).val( decodeURIComponent( value ) );
    if ( set ) searchFull( value, prev_val );
    else searchFull( value, value );
  }
});


function searchFull( value, prev_val ) {
  if( !value )
    if( !( value = $( "#search_input" ).val() ) )
      return;

  console.log( value,prev_val,column,limit,offset,total );

  var reset = false;
  if ( !prev_val ) {
    if ( prev_val != value )
      reset = true;
  }

  var column = $( "#search_change" ).val();
  if ( !column ) {
    if ( !$.QueryString["column"] )
      column = "Title";
    else column = $.QueryString["column"];
  }

  var limit = $( "#search_limit" ).val();
  if ( !limit || limit == "Limit..." ) {
    if ( !$.QueryString["limit"] )
      limit = 7;
    else limit = parseInt( $.QueryString["limit"] );
  }

  var offset = parseInt( $.QueryString["offset"] );
  if ( !offset ) {
    if ( !$.QueryString["offset"] )
      offset = 0;
  }

  var total = parseInt( $.QueryString["total"] );
  if ( !total ) {
    if ( !$.QueryString["total"] )
      total = 0;
  }

  if ( reset ) {
    offset = 0;
    total = 0;
  }

  console.log( value,column,limit,offset,total );

  sendRequest( "publicSearch", {
     value: value,
    format: 'Large',
    column: column,
     limit: limit,
    offset: offset,
     total: total
  }, searchFullResults );
}

function searchFullResults( results ) {
  $( "#search_full_results" ).html( results ); // .append( ) ?
}
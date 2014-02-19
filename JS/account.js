// Entry delete function
function makeUpdateEntryDelete(event, isbn, action) {
  var target = $(event.target),
      parent = target.closest(".book.book-medium"),
      pricer = parent.find(".price"),
      price = parent.attr("price");
  
  // If it's currently being deleted, cancel that
  if(target.hasClass("deleting")) {
    target.removeClass("deleting");
    pricer.html(price);
  }
  // Else set it to being in that process
  else {
    target.addClass("deleting");
    var input_out = "<input type='button' class='go_delete' value='really?' ";
    input_out += "onclick='doEntryDelete(event, \"";
    input_out += isbn + "\", \"" + action + "\")' />";
    pricer.html(input_out);
  }
}
function doEntryDelete(event, isbn, action) {
  var target = $(event.target).val("ok..."),
      parent = target.closest(".book.book-medium");
  sendRequest("publicEntryDelete", {
    isbn: isbn,
    action: action
  }, function(result) {
    parent.html(result).addClass("deleted");
  });
}

// Entry edit function
function makeUpdateEntryEdit(event, isbn, action) {
  var target = $(event.target),
      parent = target.closest(".book.book-medium"),
      price = parent.attr("price");
  
  // If it's currently being edited, cancel that
  if(target.hasClass("editing")) {
    target.removeClass("editing")
      .closest(".book")            // up to the parent book
      .find(".price").html(price); // down to the price label
  }
  // Else set it to being edited
  else {
    target.addClass("editing");
    var parent = target.closest(".book.book-medium");;
    parent.find(".book_entry.price").html(getPriceSelect(price, isbn, action));
  }
}

function getPriceSelect(price, isbn, action) {
  var output = "<span class='dollas'>$</span>",
      // Split the price into ['', '#dollars', '#cents']
      price = price.split(/[^0-9]/),
      dollas = price[1],
      cents = Number(price[2]);
  // Dollars
  output += "<input class='edit_dollas' type='number' value='" + dollas + "'/>";
  // Cents
  output += "<select class='edit_cents'>";
  output += "<option>00</option>";
  for(var i = 25; i <= 75; i += 25)
    output += "<option " + (i == cents ? "selected" : "") + ">" + i + "</option>";
  output += "</select>";
  output += "<input type='button' class='go_edit' value='ok' ";
  output += "onclick='doEntryEdit(event, \"";
  output += isbn + "\", \"" + action + "\")' />";
  return output;
}

function doEntryEdit(event, isbn, action) {
  var target = $(event.target).val("..."),
      parent = target.closest(".book.book-medium"),
      pricer = parent.find(".price"),
      dollars = parent.find(".edit_dollas").val(),
      cents = parent.find(".edit_cents").val(),
      price = '$' + dollars + '.' + cents;
  sendRequest("publicEntryEditPrice", {
    isbn: isbn,
    action: action,
    dollars: dollars,
    cents: cents
  }, function(result) {
    pricer.html(price);
    parent.attr("price", price);
  });
}

// Updates the search bar after the username is edited
// This should have been passed as the callback in account.tpl.php
function updateSearchUsername(results, settings) {
  var input = $("#header_search_input"),
      blurb = input.attr('placeholder'),
      last = blurb.lastIndexOf(settings.value_old);
  input.attr('placeholder', blurb.slice(0, last) + settings.value);
  console.log("settings are", settings);
}
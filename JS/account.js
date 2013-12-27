
function makeUpdateEntryDelete(event, isbn, action) {
  var target = $(event.target),
      parent = target.closest(".book.book-medium");
  return sendRequest("publicEntryDelete", {
    isbn: isbn,
    action: action
  }, function(result) {
    parent.html(result).addClass("done");
  });
}
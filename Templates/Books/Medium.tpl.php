<?php
  $isbn = $_TARGS['isbn'];
  $google_id = $_TARGS['google_id'];
  $title = $_TARGS['title'];
  $authors = $_TARGS['authors'];
  $description = $_TARGS['description'];
  $publisher = $_TARGS['publisher'];
  $year = $_TARGS['year'];
  $pages = $_TARGS['pages'];
?>
<div class="book book-medium">
  <img src="http://bks2.books.google.com/books?id=<?php echo $google_id; ?>&printsec=frontcover&img=1&zoom=5&source=gbs_api" />
  <div class="holder">
    <!-- Entry information (if it exists) -->
    <?php if(isset($_TARGS['price'])): ?>
    <div class="entry book_entry price"><?php echo getPriceAmount($_TARGS['price']); ?></div>
    <?php endif; ?>
    
    <!-- Book information -->
    <h3><?php echo getLinkHTML('book', $title, array('isbn'=>$isbn)); ?> <aside>(<?php echo $year; ?>)</h3>
    <div class="extra"><?php echo str_replace('\n', ', ', $authors); ?></div>
    <aside><?php echo $publisher . ', ' . $pages . ' pages'; ?></aside>
  </div>
</div> 
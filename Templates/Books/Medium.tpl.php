<?php
  // The required arguments
  $isbn = $_TARGS['isbn'];
  $google_id = $_TARGS['google_id'];
  $title = $_TARGS['title'];
  $authors = $_TARGS['authors'];
  $description = $_TARGS['description'];
  $publisher = $_TARGS['publisher'];
  $year = $_TARGS['year'];
  $pages = $_TARGS['pages'];
  
  // Optional arguments
  $price = isset($_TARGS['price']) ? getPriceAmount($_TARGS['price']) : false;
  if(UserLoggedIn() && isset($_TARGS['user_id']))
    $current_user = $_TARGS['user_id'] == $_SESSION['user_id'];
?>
<div class="book book-medium" <?php if($price) echo 'price="' . $price . '"'; ?>>
  <?php
    echo getLinkHTML('book', '<img src="http://bks2.books.google.com/books?id='. $google_id . '&printsec=frontcover&img=1&zoom=5" />', array('isbn'=>$isbn));
  ?>
  <div class="holder">
    <!-- Entry information (if it's given) -->
    <?php if($price): ?>
    <div class="entry book_entry price"><?php echo $price; ?></div>
    <?php if($current_user): ?>
    <div class="entry book_entry changes">
      <?php
        $action = $_TARGS['action'];
        $func_in = 'event, "' . $isbn . '", "' . $action . '"';
        $func_del = 'makeUpdateEntryDelete(' . $func_in . ')';
        $func_edit = 'makeUpdateEntryEdit(' . $func_in . ')';
      ?>
      <div class="entry_changes entry_delete" onclick='<?php echo $func_del; ?>'></div>
      <div class="entry_changes entry_edit" onclick='<?php echo $func_edit; ?>'></div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Book information -->
    <h3><?php echo getLinkHTML('book', $title, array('isbn'=>$isbn)); ?> <aside>(<?php echo $year; ?>)</h3>
    <div class="extra"><?php echo str_replace('\n', ', ', $authors); ?></div>
    <aside><?php echo $publisher . ', ' . $pages . ' pages'; ?></aside><!-- </div> -->
  </div>
</div> 
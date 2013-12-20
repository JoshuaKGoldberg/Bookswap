<?php
  $google_id = $_TARGS['google_id'];
  $title = $_TARGS['title'];
  $year = isset($_TARGS['year']) ? $_TARGS['year'] : false;
  $authors = $_TARGS['authors'];
  $description = isset($_TARGS['description']) ? $_TARGS['description'] : false;
  $pages = isset($_TARGS['pages']) ? $_TARGS['pages'] : false;
  $publisher = isset($_TARGS['publisher']) ? $_TARGS['publisher'] : false;
?>
<div class="book book_large">
  <h1 class="standard_main standard_vert"><?php echo $title; ?></h1> 
  <img src="http://bks2.books.google.com/books?id=<?php echo $google_id; ?>&printsec=frontcover&img=1&zoom=1&source=gbs_api" />
  <div class="display_book_holder">
    <div class="display_book_info">
      <h2><?php echo str_replace('\n', ', ', $authors); ?>
      <?php if($year): ?><aside>(<?php echo $year; ?>)</aside><?php endif; ?>
      </h2>
    </div>
    <div class="display_book_description">
      <blockquote><?php echo htmlentities($description); ?></blockquote>
    </div>
    <?php if($publisher || $pages): ?>
    <div class="display_book_extra">
      <aside>
        <?php if($pages): echo $pages; ?> pages <?php endif; ?>
        <?php if($publisher && $pages) echo '/'; ?>
        <?php if($publisher): ?>Published by <strong><?php echo $publisher; ?></strong><?php endif; ?>
      </aside>
    </div>
    <?php endif; ?>
  </div>
  <div class="display_book_after"></div>
</div>
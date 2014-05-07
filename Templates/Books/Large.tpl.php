<?php
  $isbn = $_TARGS['isbn'];
  $google_id = $_TARGS['google_id'];
  $title = $_TARGS['title'];
  $year = isset($_TARGS['year']) ? $_TARGS['year'] : false;
  $authors = $_TARGS['authors'];
  $description = isset($_TARGS['description']) ? $_TARGS['description'] : false;
  $pages = isset($_TARGS['pages']) ? $_TARGS['pages'] : false;
  $publisher = isset($_TARGS['publisher']) ? $_TARGS['publisher'] : false;
  
  // Search results are treated differently
  $is_search = isset($_TARGS['is_search']) && $_TARGS['is_search'];
  if($is_search) {
    if(strlen($description) > 490) {
      $description = substr($description, 0, 487);
      $description = substr($description, 0, strrpos($description, ' ')) . '...';
    }
  }
?>
<div class="book book_large">
  <?php if(!$is_search): ?>
  <!-- Main book heading -->
  <h1 class="standard_main standard_vert"><?php echo $title; ?></h1> 
  <?php endif; ?>
  
  <!-- Left-floating image (dominates a left column) -->
  <img src="http://bks2.books.google.com/books?id=<?php echo $google_id; ?>&printsec=frontcover&img=1&zoom=1&source=gbs_api" />
  
  <!-- Main holder for the book template of the page -->
  <div class="display_book_holder">
    <!-- Quick book info (author, year, Google link) -->
    <div class="display_book_info">
      <h2>
        <div class="view_externals">
            <span>View on</span>
            <a id="view_google" target="_blank" href="<?php echo getGoogleLink($google_id); ?>">Google</a>
            &middot;
            <!-- This should be replaced via Javascript with the direct link -->
            <a id="view_amazon" target="_blank" isbn="<?php echo $isbn; ?>" href="http://www.amazon.com/s/?field-keywords=<?php echo $isbn; ?>">Amazon</a>
        </div>
        <?php if($is_search): ?>
        <h2 class="book_title"><?php echo getLinkHTML('book', $title, array('isbn'=>$isbn)); ?></h1>
        <?php endif; ?>
        <div class="book_author_info">
          <span class="authors"><?php 
            $authors = explode('\n', $authors);
            foreach($authors as $author) {
              TemplatePrint('Author', 3, array('author' => $author));
            }
          ?></span>
          <aside><?php if($year) echo '(' . $year . ')'; ?></aside>
        </div>
      </h2>
    </div>
    
    <!-- Large area for the book description -->
    <div class="display_book_description">
      <blockquote><?php echo htmlentities($description); ?></blockquote>
    </div>
    
    <!-- Right-floating export links -->
    <div class="display_book_export"><?php TemplatePrint('Books/Export', $tabs + 4, $_TARGS); ?></div>
    
    <!-- If there's information on publisher or number of pages, print some -->
    <?php if($publisher || $pages): ?>
    <div class="display_book_extra">
      <aside>
        <?php if($pages): echo $pages; ?> pages <?php endif; ?>
        <?php if($publisher && $pages) echo '/'; ?>
        <?php if($publisher): ?>Published by <strong><?php TemplatePrint('Publisher', 3, array('publisher' => $publisher)); ?></strong><?php endif; ?>
      </aside>
    </div>
    <?php endif; ?>
  </div>
  <div class="display_book_after"></div>
</div>

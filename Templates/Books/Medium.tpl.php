<?php
    $isbn = $_TARGS['isbn'];
    $google_id = $_TARGS['google_id'];
    $title = $_TARGS['title'];
    $authors = $_TARGS['authors'];
    $description = $_TARGS['description'];
    $publisher = $_TARGS['publisher'];
    $year = $_TARGS['year'];
    $pages = $_TARGS['pages'];
    $user_id = $_TARGS['user_id'];
    
    $action = isset($_TARGS['action']) ? $_TARGS['action'] : false;

    // if(UserLoggedIn()) {
        // if(isset($_TARGS['user_id'])) {
            // $user_id = $_TARGS['user_id'];
            // $current_user = $user_id == $_SESSION['user_id'];
        // } else {
            // $user_id = $current_user = false;
        // }
    // }
?>
<div class="book book-medium">
    <?php
        echo getLinkHTML('book', '<img src="http://bks2.books.google.com/books?id=' . $google_id . '&printsec=frontcover&img=1&zoom=5" />', array('isbn'=>$isbn));
    ?>
    <div class="book-holder">
        <h3><?php 
            echo getLinkHTML('book', $title, array('isbn'=>$isbn)); 
        ?></h3>
        <div class="extra"><?php 
                $authors = explode('\n', $authors);
                foreach($authors as $author) {
                    TemplatePrint('Author', 3, array('author' => $author));
                }
            ?>
            <aside>(<?php echo $year; ?>)</aside>
            <div class="book-extra-info">
                <aside>
                    <?php 
                        TemplatePrint('Publisher', 3, array('publisher' => $publisher));
                        echo ', ' . $pages . ' pages';
                    ?>
                </aside>
            </div>
        </div>
        <div class="book-entries entries entries-small"><?php
            if($user_id !== false && !isset($_TARGS['from_search'])) {
                printRequest('publicPrintBookEntries', array(
                    'size' => 'Small',
                    'user_id' => $user_id,
                    'isbn' => $isbn,
                    'action' => $action
                ));
            }
        ?></div>
    </div>
</div>
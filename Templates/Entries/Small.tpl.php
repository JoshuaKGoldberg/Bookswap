<tr class='entry entry-small small' 
    data-entry-id="<?php echo $_TARGS['entry_id']; ?>"
    data-entry-states="<?php echo implode(',', getBookStates()); ?>">
    <?php
        // Know whether the entry is from the current user
        $user_id = $_TARGS['user_id'];
        $using_current = UserLoggedIn() ? $_SESSION['user_id'] == $user_id : false;

        // User arguments are typically just user_id, but also notification_id if needed
        $user_args = array('user_id' => $user_id);
        if(isset($_TARGS['notification_id'])) {
            $user_args['notification_id'] = $_TARGS['notification_id'];
        }
    ?>
    
    <td class="entry-price" data-entry-price="<?php echo $_TARGS['price']; ?>"><?php 
        TemplatePrintSmall("Money", $_TARGS);
    ?></td>
    
    <td class="entry-state" data-entry-state="<?php echo $_TARGS['state']; ?>"><?php 
        echo $_TARGS['state'];
    ?></td>
    
    <td class="entry-date" data-entry-date="<?php echo $_TARGS['timestamp']; ?>"><?php
        echo date('M jS \'y', strtotime($_TARGS['timestamp']));
    ?></td>

    <td class="entry book-entry entry-changes-cell">
        <?php
            $action = $_TARGS['action'];
            $func_in = 'event, "' . $_TARGS['entry_id'] . '", "' . $action . '"';
            $func_del = 'onEntryEditDelete(' . $func_in . ')';
            $func_edit = 'onEntryEditClick(' . $func_in . ')';
        ?>
        <div class="entry-changes entry-delete" onclick='<?php echo $func_del; ?>'></div>
        <div class="entry-changes entry-edit" onclick='<?php echo $func_edit; ?>'></div>
    </td>
</tr>

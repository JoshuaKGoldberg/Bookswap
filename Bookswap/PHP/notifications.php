<?php
require_once('pdo.inc.php');

function sendEntryNotification($dbConn, $userId, $entryId) {
  // Write the general notification
  $query = '
    INSERT INTO `notifications` (`user_id`, `type`, `time_sent`)
    VALUES (:user_id, "entry", NOW());
  ';
  $statement = getPDOStatement($dbConn, $query);
  $statement->execute(array(':user_id' => $userId));

  // Write the entry details
  $query = '
    INSERT INTO `notifications_entry` (`notification_id`, `entry_id`)
    VALUES (:notification_id, :entry_id);
  ';
  $statement = getPDOStatement($dbConn, $query);
  $statement->execute(array(
    ':notification_id' => $dbConn->lastInsertId(),
    ':entry_id' => $entryId
  ));

  // TODO: Send email/SMS/Facebook notification based on preferences
}

function sendAllEntryNotifications($dbConn, $entryId) {
  // Get the entry
  $query = '
    SELECT `isbn`, `user_id`, `action`
    FROM `entries` WHERE `entry_id`=:entry_id;
  ';
  $statement = getPDOStatement($dbConn, $query);
  $statement->execute(array(':entry_id' => $entryId));
  $entry = $statement->fetch();
  if (!$entry) {
    echo 'This entry does not exist.';
    return;
  }

  // Get the users we need to notify
  $query = '
    SELECT `user_id` FROM `entries`
    WHERE `user_id` <> :user_id
    AND `isbn` = :isbn
    AND `action` <> :action;
  ';
  $statement = getPDOStatement($dbConn, $query);
  $statement->execute(array(
    ':user_id' => $entry['user_id'],
    ':isbn' => $entry['isbn'],
    ':action' => $entry['action']
  ));

  // Write the notifications for each user
  while ($user = $statement->fetch()) {
    sendEntryNotification($dbConn, $user['user_id'], $entryId);
  }
}
?>

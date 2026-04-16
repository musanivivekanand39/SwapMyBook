<?php

function ensureNotificationColumns(mysqli $conn): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $ownerSeenExists = false;
    $requesterSeenExists = false;

    $result = mysqli_query($conn, "SHOW COLUMNS FROM swap_requests LIKE 'owner_seen'");
    if ($result) {
        $ownerSeenExists = mysqli_num_rows($result) > 0;
        mysqli_free_result($result);
    }

    $result = mysqli_query($conn, "SHOW COLUMNS FROM swap_requests LIKE 'requester_seen'");
    if ($result) {
        $requesterSeenExists = mysqli_num_rows($result) > 0;
        mysqli_free_result($result);
    }

    if (!$ownerSeenExists) {
        mysqli_query($conn, "ALTER TABLE swap_requests ADD COLUMN owner_seen TINYINT(1) NOT NULL DEFAULT 0");
    }

    if (!$requesterSeenExists) {
        mysqli_query($conn, "ALTER TABLE swap_requests ADD COLUMN requester_seen TINYINT(1) NOT NULL DEFAULT 1");
    }

    $checked = true;
}
?>

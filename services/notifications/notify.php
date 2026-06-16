<?php
function notify($conn, $business_id, $title, $body)
{
    $business_id = $conn->real_escape_string((int) $business_id);
    $title = $conn->real_escape_string($title);
    $body = $conn->real_escape_string($body);
    $conn->query("INSERT INTO notifications(business_id, title, body) VALUES('$business_id', '$title', '$body')");
}

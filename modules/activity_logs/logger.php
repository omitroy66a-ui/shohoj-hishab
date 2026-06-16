<?php
function saveLog($conn, $user_id, $module, $action, $description)
{
    $user_id = (int) $user_id;
    $module = trim($module);
    $action = trim($action);
    $description = trim($description);

    $stmt = $conn->prepare("INSERT INTO activity_logs(user_id, module_name, action_name, description) VALUES(?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('isss', $user_id, $module, $action, $description);
        $stmt->execute();
        $stmt->close();
    }
}

<?php
require_once "auth.php";
function getSelectedIds(): array
{
    $ids = $_POST["user_ids"] ?? [];
    if (!is_array($ids)) {
        return [];
    }
    $validIds = [];
    foreach ($ids as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT) !== false && (int)$id > 0) {
            $validIds[] = (int)$id;
        }
    }
    return array_values(array_unique($validIds));
}
function updateUsersStatus(mysqli $conn, array $ids, string $status): bool
{
    if (empty($ids)) {
        return false;
    }
    $sql = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    foreach ($ids as $id) {
        $stmt->bind_param("si", $status, $id);

        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }
    $stmt->close();
    return true;
}
function deleteUsers(mysqli $conn, array $ids): bool
{
    if (empty($ids)) {
        return false;
    }
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    foreach ($ids as $id) {
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }
    $stmt->close();
    return true;
}
function deleteUnverifiedUsers(mysqli $conn): bool
{
    $sql = "DELETE FROM users WHERE status = 'unverified'";

    return $conn->query($sql) === true;
}
$action = $_POST["action"] ?? "";
$selectedIds = getSelectedIds();
$successMessage = "";
$errorMessage = "";
switch ($action) {
    case "block":
        if (empty($selectedIds)) {
            $errorMessage = "Please select at least one user.";
        } elseif (updateUsersStatus($conn, $selectedIds, "blocked")) {
            $successMessage = "Selected user(s) have been blocked.";
        } else {
            $errorMessage = "Unable to block selected user(s).";
        }
        break;
    case "unblock":
        if (empty($selectedIds)) {
            $errorMessage = "Please select at least one user.";
        } elseif (updateUsersStatus($conn, $selectedIds, "active")) {
            $successMessage = "Selected user(s) have been unblocked.";
        } else {
            $errorMessage = "Unable to unblock selected user(s).";
        }
        break;
    case "delete":
        if (empty($selectedIds)) {
            $errorMessage = "Please select at least one user.";
        } elseif (deleteUsers($conn, $selectedIds)) {
            $successMessage = "Selected user(s) have been deleted.";
        } else {
            $errorMessage = "Unable to delete selected user(s).";
        }
        break;
    case "delete_unverified":
        if (deleteUnverifiedUsers($conn)) {
            $successMessage = "All unverified users have been deleted.";
        } else {
            $errorMessage = "Unable to delete unverified users.";
        }
        break;
    default:
        $errorMessage = "Invalid action.";
        break;
}
if ($successMessage !== "") {
    header(
        "Location: dashboard.php?success=" .
        urlencode($successMessage)
    );
    exit;
}
header(
    "Location: dashboard.php?error=" .
    urlencode($errorMessage)
);
exit;
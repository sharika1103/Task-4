<?php
require_once "config/database.php";
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$userId = $_SESSION["user_id"];
$sql = "SELECT id, name, email, status
        FROM users
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
$currentUser = $result->fetch_assoc();
if ($currentUser["status"] === "blocked") {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
$stmt->close();
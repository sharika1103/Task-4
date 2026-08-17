<?php
require_once "config/database.php";
$message = "";
$token = $_GET["token"] ?? "";
if ($token === "") {
    $message = "Invalid verification link.";
} else {
    $sql = "SELECT id, status
            FROM users
            WHERE verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows !== 1) {

        $message = "Invalid or expired verification link.";
    } else {
        $user = $result->fetch_assoc();
        if ($user["status"] === "blocked") {
            $message = "Your account is blocked.";
        } elseif ($user["status"] === "active") {
            $message = "Your account is already active.";
        } else {
            $updateSql = "UPDATE users
                          SET status = 'active',
                              verification_token = NULL
                          WHERE id = ?
                          AND status = 'unverified'";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $user["id"]);
            $updateStmt->execute();
            if ($updateStmt->affected_rows === 1) {
                $message = "Your account has been successfully activated.";
            } else {
                $message = "Unable to activate your account.";
            }
            $updateStmt->close();
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Verification</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-body text-center p-4">
            <h3 class="mb-3">
                Email Verification
            </h3>
            <p>
                <?php echo htmlspecialchars($message); ?>
            </p>
            <a href="login.php" class="btn btn-primary">
                Go to Login
            </a>
        </div>
    </div>
</div>
</body>
</html>
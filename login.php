<?php
session_start();
require_once "config/database.php";
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $message = "Please enter email and password.";

    } else {

        $sql = "SELECT id, name, email, password, status
                FROM users
                WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if ($user["status"] === "blocked") {

                $message = "Your account is blocked.";

            } elseif (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];

                $updateSql = "UPDATE users
                              SET last_login = NOW()
                              WHERE id = ?";

                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("i", $user["id"]);
                $updateStmt->execute();
                $updateStmt->close();

                header("Location: dashboard.php");
                exit;

            } else {

                $message = "Invalid email or password.";
            }

        } else {

            $message = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
<h2>Login</h2>
<?php if ($message !== ""): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="POST">

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Login</button>

</form>
</body>
</html>
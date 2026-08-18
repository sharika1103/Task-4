<?php
require __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once "config/database.php";
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    if ($name === "" || $email === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $verificationToken = bin2hex(random_bytes(32));
        $sql = "INSERT INTO users
                (name, email, password, verification_token)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssss",
            $name,
            $email,
            $hashedPassword,
            $verificationToken
        );
        if ($stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sharikasuhi10@gmail.com';
                $mail->Password   = '*******';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->setFrom(
                    'sharikasuhi10@gmail.com',
                    'Task 4 User Management'
                );
                $mail->addAddress($email, $name);
                $mail->isHTML(true);
                $mail->Subject = 'Verify Your Email';
                $verificationLink =
                    'http://localhost/Task%204/verify.php?token='
                    . $verificationToken;
                $mail->Body = "
                    <h2>Email Verification</h2>
                    <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    <p>Thank you for registering.</p>
                    <p>Please click the button below to verify your email:</p>
                    <p>
                        <a href='" . $verificationLink . "'
                           style='
                           background:#007bff;
                           color:white;
                           padding:10px 20px;
                           text-decoration:none;
                           border-radius:5px;
                           display:inline-block;
                           '>
                           Verify Email
                        </a>
                    </p>
                    <p>Or copy this link into your browser:</p>
                    <p>" . $verificationLink . "</p>
                ";
                $mail->send();
                $message = "Registration successful! Please check your email to verify your account.";
            } catch (Exception $e) {
                $message = "Registration successful, but verification email could not be sent.";
            }
        } else {
            if ($conn->errno === 1062) {
                $message = "This email is already registered.";
            } else {
                $message = "Registration failed.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration</title>
</head>
<body>
<h2>Register</h2>
<?php if ($message !== ""): ?>
    <p>
        <?php echo htmlspecialchars($message); ?>
    </p>
<?php endif; ?>
<form method="POST">
    <label>Name:</label><br>
    <input type="text" name="name">
    <br><br>
    <label>Email:</label><br>
    <input type="email" name="email">
    <br><br>
    <label>Password:</label><br>
    <input type="password" name="password">
    <br><br>
    <button type="submit">Register</button>
</form>
</body>
</html>

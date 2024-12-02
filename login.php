<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php'; // File to handle database connection
include 'includes/header.php'; // File to include predefined header

$message = '';         // Initialize message
$messageClass = '';    // Initialize message CSS class

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify the password and start the session
    if ($user && password_verify($password, $user['Password'])) {
        // Store user data in the session
        $_SESSION['user_id'] = $user['UserID'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Role'];

        // Redirect to the homepage or dashboard
        header("Location: index.php"); // Redirect to homepage
        exit(); // Ensure no further code runs
    } else {
        // Set the error message
        $message = "Neteisingas vartotojo vardas arba slaptažodis.";
        $messageClass = 'error'; // Assign error class
    }
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Prisijungti</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .notification {
            margin: 20px auto;
            padding: 15px;
            border-radius: 5px;
            max-width: 400px;
            text-align: center;
        }
        .notification.success {
            background-color: #4CAF50;
            color: white;
        }
        .notification.error {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
<main>
    <h2>Prisijungti</h2>

    <!-- Display Notifications -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" style="max-width: 400px; margin: auto;">
        <label for="username">Vartotojo vardas:</label>
        <input type="text" id="username" name="username" required style="width: 100%; padding: 10px; margin-bottom: 10px;">

        <label for="password">Slaptažodis:</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; margin-bottom: 10px;">

        <button type="submit" style="width: 100%; padding: 10px; background-color: #333; color: white; border: none; border-radius: 5px;">Prisijungti</button>
    </form>
</main>
</body>
</html>

<?php include 'includes/footer.php'; ?>
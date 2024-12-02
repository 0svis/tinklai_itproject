<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // Retrieve the user ID from the session

// Initialize notification variables
$message = '';
$messageClass = '';

// Handle credit request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
    $amount = $_POST['amount'];
if ($amount > 0) {
        try {
            // Insert credit request into database
            $stmt = $pdo->prepare("INSERT INTO CreditRequests (UserID, Amount, Status) VALUES (?, ?, 'Laukiama')");
            $stmt->execute([$user_id, $amount]);
			 $message = "Kreditų prašymas išsiųstas!";
            $messageClass = ''; // Default success style
        } catch (Exception $e) {
            $message = "Klaida išsiunčiant prašymą: " . $e->getMessage();
            $messageClass = 'error'; // Error style
        }
	} else {
        $message = "Prašome įvesti teigiamą/tinkamą skaičių prašomų kreditų";
        $messageClass = 'error'; // Error style
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prašyti kreditų</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main>
        <h2>Prašyti papildomų kreditų</h2>
		<!-- Display Notification if Available -->
        <?php if ($message): ?>
            <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Credit Request Form -->
        <form method="POST" action="credit_request.php">
            <label for="amount">Suma:</label>
            <input type="number" name="amount" id="amount" min="1" required>
            <button type="submit">Išsiųsti užklausą</button>
			</form>
    </main>
</body>
</html>
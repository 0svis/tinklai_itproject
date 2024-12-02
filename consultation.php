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

$user_id = $_SESSION['user_id'];
$message = '';
$messageClass = '';

// Fetch available consultants for booking
$stmt = $pdo->query("SELECT ConsultantID, Name FROM Consultant WHERE Status = 'Prisijunges'");
$consultants = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $consultant_id = $_POST['consultant_id'];
    $date = $_POST['date'];
    $user_message = trim($_POST['message']); // Capture user's message

    // Validate the date (cannot be in the past)
    $currentDate = date('Y-m-d');
    if ($date < $currentDate) {
        $message = "Negalite užsakyti konsultacijos praeities datai.";
        $messageClass = 'error';
    } else {
        // Check user's current credits (minimum 5 required)
        $creditCheck = $pdo->prepare("SELECT Credits FROM User WHERE UserID = ?");
        $creditCheck->execute([$user_id]);
        $userCredits = $creditCheck->fetchColumn();

        if ($userCredits >= 5) {
            // Insert consultation request into the database without deducting credits
            $pdo->prepare("INSERT INTO Consultation (UserID, ConsultantID, Date, Status, ChatLog) VALUES (?, ?, ?, 'Laukiama', ?)")
                ->execute([$user_id, $consultant_id, $date, $user_message]);

            $message = "Konsultacija sėkmingai užsakyta!";
            $messageClass = 'success';
        } else {
            $message = "Nepakanka kreditų! Reikalingi bent 5 kreditai.";
            $messageClass = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Užsakyti konsultaciją</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
    <h2>Užsakyti konsultaciją</h2>

    <!-- Display Notification if Available -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" action="consultation.php">
        <label for="consultant">Pasirinkite konsultantą:</label>
        <select name="consultant_id" id="consultant" required>
            <?php foreach ($consultants as $consultant): ?>
                <option value="<?= $consultant['ConsultantID'] ?>"><?= htmlspecialchars($consultant['Name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="date">Pasirinkite datą:</label>
        <input type="date" name="date" id="date" min="<?= date('Y-m-d') ?>" required> <!-- HTML-level validation -->

        <label for="message">Žinutė konsultantui:</label>
        <textarea name="message" id="message" rows="3" placeholder="Įveskite žinutę konsultantui..." required></textarea>

        <button type="submit">Užsakyti konsultaciją</button>
    </form>
</main>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// **1. Update Permission Check**
// Allow both 'Consultant' and 'Administrator' roles to access the page
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Consultant', 'Administrator'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$messageClass = '';

// Handle Add FAQ form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faq'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    if (!empty($question) && !empty($answer)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO FAQ (Question, Answer) VALUES (?, ?)");
            $stmt->execute([$question, $answer]);
            $message = "DUK straipsnis sėkmingai pridėtas!";
            $messageClass = 'success';
        } catch (PDOException $e) {
            $message = "Klaida pridedant DUK straipsnį: " . $e->getMessage();
            $messageClass = 'error';
        }
    } else {
        $message = "Visi laukai yra privalomi!";
        $messageClass = 'error';
    }
}

// **2. Handle Delete FAQ action with Role Check**
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_faq'])) {
    // Only administrators can delete FAQs
    if ($_SESSION['role'] !== 'Administrator') {
        header("Location: manage_faq.php");
        exit();
    }

    $faq_id = intval($_POST['faq_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM FAQ WHERE FAQ_ID = ?");
        $stmt->execute([$faq_id]);
        $message = "DUK straipsnis sėkmingai ištrintas!";
        $messageClass = 'success';
    } catch (PDOException $e) {
        $message = "Klaida trinant DUK straipsnį: " . $e->getMessage();
        $messageClass = 'error';
    }
}

// Fetch all FAQs
$stmt = $pdo->query("SELECT FAQ_ID, Question, Answer FROM FAQ");
$faqs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Valdyti DUK</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Existing styles */
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #555;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #333;
        }

        .delete-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f44336; /* Red background */
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-button:hover {
            background-color: #d32f2f; /* Darker red on hover */
        }

        .form-container {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .faq-container {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
            justify-content: flex-start;
        }

        .faq-item {
            width: 300px;
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin: 10px;
        }

        .faq-item h3 {
            margin-bottom: 10px;
        }

        h2 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<main>
    <h2>Valdyti DUK Straipsnius</h2>

    <!-- Display Notifications -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Add FAQ Form -->
    <div class="form-container">
        <h3>Pridėti naują DUK straipsnį</h3>
        <form method="POST" action="manage_faq.php">
            <label for="question">Klausimas:</label>
            <textarea name="question" id="question" rows="3" required></textarea>

            <label for="answer">Atsakymas:</label>
            <textarea name="answer" id="answer" rows="5" required></textarea>

            <button type="submit" name="add_faq" class="button">Pridėti DUK</button>
        </form>
    </div>

    <!-- Existing FAQs -->
    <h3>Esami DUK Straipsniai</h3>
    <div class="faq-container">
        <?php foreach ($faqs as $faq): ?>
            <div id="faq<?= $faq['FAQ_ID'] ?>" class="faq-item">
                <h3><?= htmlspecialchars($faq['Question']) ?></h3>
                <a href="edit_faq.php?id=<?= htmlspecialchars($faq['FAQ_ID']) ?>" class="button">Redaguoti</a>
                <!-- **3. Show Delete Button Only to Administrators** -->
                <?php if ($_SESSION['role'] === 'Administrator'): ?>
                    <form method="POST" action="manage_faq.php" style="margin-top: 10px;">
                        <input type="hidden" name="faq_id" value="<?= htmlspecialchars($faq['FAQ_ID']) ?>">
                        <button type="submit" name="delete_faq" class="delete-button" onclick="return confirm('Ar tikrai norite ištrinti šį straipsnį?')">Ištrinti</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
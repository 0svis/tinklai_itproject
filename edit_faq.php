<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// Check if the user is authorized
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Consultant', 'Administrator'])) {
    header("Location: login.php");
    exit();
}

// Fetch FAQ article based on the provided ID
if (!isset($_GET['id'])) {
    header("Location: manage_faq.php");
    exit();
}

$faq_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT FAQ_ID, Question, Answer FROM FAQ WHERE FAQ_ID = ?");
$stmt->execute([$faq_id]);
$faq = $stmt->fetch();

if (!$faq) {
    header("Location: manage_faq.php");
    exit();
}

// Handle FAQ update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['faq_id'], $_POST['question'], $_POST['answer'])) {
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);

    // Update the FAQ in the database
    $updateStmt = $pdo->prepare("UPDATE FAQ SET Question = ?, Answer = ? WHERE FAQ_ID = ?");
    $updateStmt->execute([$question, $answer, $faq_id]);

    // Redirect back to manage FAQ page with a success message
    header("Location: manage_faq.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Redaguoti DUK</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #555;
        }

        .button.secondary {
            background-color: #D22B2B;
        }

        .button.secondary:hover {
            background-color: #811331;
        }
    </style>
</head>
<body>
<main>
    <h2>Redaguoti DUK Straipsnį</h2>

    <form method="POST" action="edit_faq.php?id=<?= htmlspecialchars($faq_id) ?>">
        <input type="hidden" name="faq_id" value="<?= htmlspecialchars($faq['FAQ_ID']) ?>">
        <label for="question">Klausimas:</label>
        <textarea name="question" id="question" rows="3" required><?= htmlspecialchars($faq['Question']) ?></textarea>
        
        <label for="answer">Atsakymas:</label>
        <textarea name="answer" id="answer" rows="5" required><?= htmlspecialchars($faq['Answer']) ?></textarea>
        
        <button type="submit" class="button">Išsaugoti</button>
    </form>

    <a href="manage_faq.php" class="button secondary">Grįžti į DUK Valdymą</a>
</main>
</body>
</html>
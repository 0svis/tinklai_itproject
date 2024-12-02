<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

$message = '';
$messageClass = '';

// Fetch top 10 consultants based on most reviews and best ratings
$stmt = $pdo->query("
    SELECT Name, Rating, RatingCount, Status 
    FROM Consultant 
    WHERE RatingCount > 0
    ORDER BY Rating DESC, RatingCount DESC
    LIMIT 10
");
$consultants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Konsultantų Lyderių Lentelė</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .leaderboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .consultant-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 15px;
            text-align: center;
            width: 250px;
        }

        .consultant-card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .consultant-card p {
            margin: 5px 0;
            color: #555;
        }
    </style>
</head>
<body>
<main>
    <h2>Konsultantų Lyderių Lentelė</h2>

    <?php if ($consultants): ?>
        <div class="leaderboard-container">
            <?php foreach ($consultants as $consultant): ?>
                <div class="consultant-card">
                    <h3><?= htmlspecialchars($consultant['Name']) ?></h3>
                    <p><strong>Statusas:</strong> <?= htmlspecialchars($consultant['Status']) ?></p>
                    <p><strong>Reitingas:</strong> <?= number_format($consultant['Rating'], 2) ?></p>
                    <p><strong>Įvertinimų Skaičius:</strong> <?= htmlspecialchars($consultant['RatingCount']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Kol kas nėra įvertintų konsultantų.</p>
    <?php endif; ?>
</main>
</body>
</html>
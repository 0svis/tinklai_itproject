<?php
// index.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';
include 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pagalbos portalas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
        }
        main {
            width: 100%;
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
            box-sizing: border-box;
        }
        .consultant-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .consultant-box {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .consultant-box h3 {
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
<main>
    <section class="introduction">
        <h2>Sveiki atvykę į Pagalbos Portalą</h2>
        <p>Pagalbos Portalas skirtas padėti Jums rūpimais klausimais. Visada galit pasikonsultuoti su ekspertu arba peržvelgti DUK sekciją.</p>
    </section>
	<h2>Prisijungę konsultantai:</h2>
    <div class="consultant-container">
        <?php
        // Fetch online consultants
        $stmt = $pdo->query("SELECT ConsultantID, Name, Status, Rating FROM Consultant WHERE Status = 'Prisijunges'");
        $consultants = $stmt->fetchAll();
        foreach ($consultants as $consultant) {
            echo '<div class="consultant-box">';
            echo '<h3>' . htmlspecialchars($consultant['Name']) . '</h3>';
            echo '<p>Statusas: ' . htmlspecialchars($consultant['Status']) . '</p>';
            echo '<p>Reitingas: ' . number_format($consultant['Rating'], 2) . '</p>'; // Updated line
            echo '</div>';
        }
        ?>
    </div>

    <section class="faq-section">
        <h2>Dažnai užduodami klausimai (DUK)</h2>
        <div class="faq-container">
            <?php
            $faq_stmt = $pdo->query("SELECT FAQ_ID, Question FROM FAQ");
            $faqs = $faq_stmt->fetchAll();
            foreach ($faqs as $faq) {
                echo '<div class="faq-item">';
                echo '<h3>' . htmlspecialchars($faq['Question']) . '</h3>';
                echo '<a href="faq_detail.php?id=' . $faq['FAQ_ID'] . '" class="faq-item-link">Skaityti daugiau</a>';
                echo '</div>';
            }
            ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
// view_faq.php
include 'includes/header.php';
include 'includes/db.php';  // Database connection

$stmt = $pdo->query("SELECT * FROM FAQ");
$faqs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Peržiūrėti DUK</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
	<h2>Dažniausiai užduodami klausimai</h2>
    <?php if (!empty($faqs)): ?>
        <div class="faq-container">
            <?php
			foreach ($faqs as $faq) {
                echo '<a href="faq_detail.php?id=' . $faq['FAQ_ID'] . '" target="_blank" class="faq-item-link">';
                echo '<div class="faq-item">';
                echo '<h3 class="faq-question">' . htmlspecialchars($faq['Question']) . '</h3>';
                echo '</div>';
                echo '</a>';
				 }
            ?>
        </div>
    <?php else: ?>
        <p>DUK šiuo metu yra neprieinami arba nėra jokių DUK straipsnių</p>
    <?php endif; ?>
</main>
	<?php include 'includes/footer.php'; ?>
</body>
</html>
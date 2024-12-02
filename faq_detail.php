<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "No FAQ ID provided.";
    exit();
}
$faq_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM FAQ WHERE FAQ_ID = ?");
$stmt->execute([$faq_id]);
$faq = $stmt->fetch();

if (!$faq) {
    echo "FAQ not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQ Detail</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main class="faq-detail-main">
    <h2><?= htmlspecialchars($faq['Question']) ?></h2>
    <p><?= htmlspecialchars($faq['Answer']) ?></p>
    <a href="view_faq.php" class="back-link">Grįžti atgal į dažniausiai užduodamus klausimus</a>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
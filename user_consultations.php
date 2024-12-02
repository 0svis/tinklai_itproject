<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// Peradresuoti į prisijungimo puslapį, jei vartotojas nėra prisijungęs
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageClass = '';

// Tvarkyti atsiliepimų ar žinučių siuntimą
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['consultation_id'])) {
    $consultation_id = $_POST['consultation_id'];

    // Siųsti žinutę, jei konsultacija nėra užbaigta
    if (isset($_POST['send_chat']) && $_POST['status'] != 'Uzbaigta') {
        $chat_message = $_POST['chat_message'];
        $stmt = $pdo->prepare("SELECT ChatLog FROM Consultation WHERE ConsultationID = ?");
        $stmt->execute([$consultation_id]);
        $chatLog = $stmt->fetchColumn();

        // Pridėti naują žinutę prie pokalbių žurnalo
        $newChatLog = $chatLog . "\nNaudotojas: " . $chat_message;
        $updateStmt = $pdo->prepare("UPDATE Consultation SET ChatLog = ? WHERE ConsultationID = ?");
        $updateStmt->execute([$newChatLog, $consultation_id]);
        $message = "Žinutė išsiųsta!";
        $messageClass = 'success';
    }
}

// Gauti vartotojo konsultacijų istoriją
$stmt = $pdo->prepare("
    SELECT c.ConsultationID, c.Date, c.Status, cons.Name AS ConsultantName, c.CreditCost, c.ChatLog
    FROM Consultation c
    JOIN Consultant cons ON c.ConsultantID = cons.ConsultantID
    LEFT JOIN Feedback f ON c.ConsultationID = f.ConsultationID
    WHERE c.UserID = ? 
      AND f.ConsultationID IS NULL 
      AND c.Status != 'Atmesta'
    ORDER BY c.Date DESC
");
$stmt->execute([$user_id]);
$consultations = $stmt->fetchAll();

// Gauti pasirinktos konsultacijos detales
$selectedConsultation = null;
if (isset($_GET['id'])) {
    $consultation_id = $_GET['id'];
    $stmt = $pdo->prepare("
        SELECT c.ConsultationID, c.Date, c.Status, cons.Name AS ConsultantName, c.CreditCost, c.ChatLog
        FROM Consultation c
        JOIN Consultant cons ON c.ConsultantID = cons.ConsultantID
        WHERE c.ConsultationID = ? AND c.UserID = ?
    ");
    $stmt->execute([$consultation_id, $user_id]);
    $selectedConsultation = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Mano konsultacijos</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
    <h2>Mano konsultacijos</h2>

    <!-- Rodyti pranešimą, jei yra -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="consultation-container">
        <div class="consultation-list">
            <h3>Konsultacijų sąrašas</h3>
            <table class="consultation-table">
                <thead>
                    <tr>
                        <th>Konsultantas</th>
                        <th>Data</th>
                        <th>Kaina (Kreditai)</th>
                        <th>Būsena</th>
                        <th>Veiksmai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultations as $consultation): ?>
                        <tr>
                            <td><?= htmlspecialchars($consultation['ConsultantName']) ?></td>
                            <td><?= htmlspecialchars($consultation['Date']) ?></td>
                            <td><?= htmlspecialchars($consultation['CreditCost']) ?></td>
                            <td><?= htmlspecialchars($consultation['Status']) ?></td>
                            <td>
                                <a href="user_consultations.php?id=<?= $consultation['ConsultationID'] ?>">Peržiūrėti</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Konsultacijos detalių sekcija -->
        <?php if ($selectedConsultation): ?>
            <div class="consultation-details">
                <h3>Konsultacijos detalės</h3>
                <p><strong>Konsultantas:</strong> <?= htmlspecialchars($selectedConsultation['ConsultantName']) ?></p>
                <p><strong>Data:</strong> <?= htmlspecialchars($selectedConsultation['Date']) ?></p>
                <p><strong>Kaina (Kreditai):</strong> <?= htmlspecialchars($selectedConsultation['CreditCost']) ?></p>
                <p><strong>Būsena:</strong> <?= htmlspecialchars($selectedConsultation['Status']) ?></p>

                <div class="chat-container">
                    <p><strong>Žinutės:</strong></p>
                    <div class="chat-log">
                        <?= nl2br(htmlspecialchars($selectedConsultation['ChatLog'])); ?>
                    </div>
                </div>

                <a href="user_consultations.php" class="close-button">Grįžti</a>
            </div>
        <?php else: ?>
            <p>Pasirinkite konsultaciją iš sąrašo, kad peržiūrėtumėte detales ir bendrautumėte su konsultantu.</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// Patikrina, ar vartotojas prisijungęs ir ar turi Konsultanto arba Administratoriaus vaidmenį
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Consultant', 'Administrator'])) {
    header("Location: login.php");
    exit();
}

// Gauk vartotojo ID iš sesijos ir patikrina, ar jis yra konsultantas ar administratorius
$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['role'] === 'Administrator';
$message = '';
$messageClass = '';

// Tik konsultantams: gauti ConsultantID ir vardą
$consultant_id = null;
$consultant_name = null;
if (!$is_admin) {
    $stmt = $pdo->prepare("SELECT ConsultantID, Name FROM Consultant WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $consultant = $stmt->fetch();
    $consultant_id = $consultant['ConsultantID'];
    $consultant_name = $consultant['Name'];

    if (!$consultant_id) {
        header("Location: login.php");
        exit();
    }
} else {
    // Administratoriams nustatomas bendras pavadinimas
    $consultant_name = "Administratorius";
}

// Tvarkykite konsultacijų veiksmus: Priėmimas, Atmetimas, Užbaigimas ir Žinučių siuntimas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['consultation_id'])) {
    $consultation_id = $_POST['consultation_id'];

    // Priimti konsultaciją
    if (isset($_POST['accept'])) {
        $credit_cost = $_POST['credit_cost'];

        // Patikrina, ar naudotojas turi pakankamai kreditų
        $userCreditCheck = $pdo->prepare("SELECT Credits FROM User WHERE UserID = (SELECT UserID FROM Consultation WHERE ConsultationID = ?)");
        $userCreditCheck->execute([$consultation_id]);
        $userCredits = $userCreditCheck->fetchColumn();

        if ($userCredits >= $credit_cost) {
            // Nuskaityti kreditus ir atnaujinti konsultacijos kainą
            $pdo->prepare("UPDATE User SET Credits = Credits - ? WHERE UserID = (SELECT UserID FROM Consultation WHERE ConsultationID = ?)")
                ->execute([$credit_cost, $consultation_id]);

            $updateStmt = $pdo->prepare("UPDATE Consultation SET Status = 'Priimta', CreditCost = ? WHERE ConsultationID = ?");
            $updateStmt->execute([$credit_cost, $consultation_id]);
            $message = "Konsultacija priimta ir kreditai nuskaityti!";
            $messageClass = 'success';
        } else {
            $message = "Naudotojas neturi pakankamai kreditų.";
            $messageClass = 'error';
        }

    // Atmesti konsultaciją
    } elseif (isset($_POST['reject'])) {
        $updateStmt = $pdo->prepare("UPDATE Consultation SET Status = 'Atmesta' WHERE ConsultationID = ?");
        $updateStmt->execute([$consultation_id]);
        $message = "Konsultacija atmesta!";
        $messageClass = 'error';

    // Užbaigti konsultaciją
    } elseif (isset($_POST['complete'])) {
        $updateStmt = $pdo->prepare("UPDATE Consultation SET Status = 'Uzbaigta' WHERE ConsultationID = ?");
        $updateStmt->execute([$consultation_id]);
        $message = "Konsultacija užbaigta!";
        $messageClass = 'success';

    // Siųsti žinutę pokalbyje
    } elseif (isset($_POST['send_chat'])) {
        $chat_message = $_POST['chat_message'];
        $stmt = $pdo->prepare("SELECT ChatLog FROM Consultation WHERE ConsultationID = ?");
        $stmt->execute([$consultation_id]);
        $chatLog = $stmt->fetchColumn();

        $newChatLog = $chatLog . "\n" . $consultant_name . ": " . $chat_message;
        $updateStmt = $pdo->prepare("UPDATE Consultation SET ChatLog = ? WHERE ConsultationID = ?");
        $updateStmt->execute([$newChatLog, $consultation_id]);
    }
}

// Gauk šiandienos datą
$currentDate = date('Y-m-d');

// Užklausos pritaikymas pagal vaidmenį
// Adjust the query for consultations based on the user's role
if ($is_admin) {
    // Administratoriai mato visas neužbaigtas konsultacijas
    $stmt = $pdo->prepare("SELECT Consultation.ConsultationID, User.Username, Consultation.Date, Consultation.CreditCost, Consultation.Status, Consultation.ChatLog 
                           FROM Consultation 
                           JOIN User ON Consultation.UserID = User.UserID 
                           WHERE Consultation.Status != 'Uzbaigta'
                           ORDER BY Consultation.Date ASC");
    $stmt->execute();
} else {
    // Konsultantai mato tik savo neužbaigtas konsultacijas
    $stmt = $pdo->prepare("SELECT Consultation.ConsultationID, User.Username, Consultation.Date, Consultation.CreditCost, Consultation.Status, Consultation.ChatLog 
                           FROM Consultation 
                           JOIN User ON Consultation.UserID = User.UserID 
                           WHERE ConsultantID = ? 
                             AND Consultation.Status != 'Uzbaigta'
                           ORDER BY Consultation.Date ASC");
    $stmt->execute([$consultant_id]);
}

$consultations = $stmt->fetchAll();

// Gauk pasirinktos konsultacijos detales, jei ji pasirinkta
$selectedConsultation = null;
if (isset($_GET['id'])) {
    $consultation_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT Consultation.ConsultationID, User.Username, Consultation.Date, Consultation.CreditCost, Consultation.Status, Consultation.ChatLog 
                           FROM Consultation 
                           JOIN User ON Consultation.UserID = User.UserID 
                           WHERE ConsultationID = ?");
    $stmt->execute([$consultation_id]);
    $selectedConsultation = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Konsultacijų Sąrašas ir Detalės</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
    <h2>Konsultacijų Sąrašas ir Detalės</h2>

    <!-- Rodyti pranešimą, jei yra -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="consultation-container">
        <div class="consultation-list">
            <h3>Konsultacijų Sąrašas</h3>
            <table class="consultation-table">
                <thead>
                    <tr>
                        <th>Vartotojas</th>
                        <th>Data</th>
                        <th>Kaina (Kreditai)</th>
                        <th>Būsena</th>
                        <th>Veiksmai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($consultations as $consultation): ?>
                        <tr>
                            <td><?= htmlspecialchars($consultation['Username']) ?></td>
                            <td><?= htmlspecialchars($consultation['Date']) ?></td>
                            <td><?= htmlspecialchars($consultation['CreditCost']) ?></td>
                            <td><?= htmlspecialchars($consultation['Status']) ?></td>
                            <td>
                                <a href="answer_consultation.php?id=<?= $consultation['ConsultationID'] ?>">Peržiūrėti</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Konsultacijos Detalių Sekcija -->
        <?php if ($selectedConsultation): ?>
            <div class="consultation-details">
                <h3>Konsultacijos Detalės</h3>
                <p><strong>Vartotojas:</strong> <?= htmlspecialchars($selectedConsultation['Username']) ?></p>
                <p><strong>Data:</strong> <?= htmlspecialchars($selectedConsultation['Date']) ?></p>
                <p><strong>Kaina (Kreditai):</strong> <?= htmlspecialchars($selectedConsultation['CreditCost']) ?></p>
                <p><strong>Būsena:</strong> <?= htmlspecialchars($selectedConsultation['Status']) ?></p>
                <p><strong>Vartotojo Žinutė:</strong> <?= nl2br(htmlspecialchars($selectedConsultation['ChatLog'])) ?></p>

                <?php if ($selectedConsultation['Status'] == 'Laukiama'): ?>
                    <!-- Priimti arba atmesti formą -->
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="consultation_id" value="<?= $selectedConsultation['ConsultationID'] ?>">
                        <label for="credit_cost">Nustatyti kainą (kreditai):</label>
                        <input type="number" name="credit_cost" min="0" value="<?= $selectedConsultation['CreditCost'] ?>" required>
                        <button type="submit" name="accept">Priimti</button>
                        <button type="submit" name="reject">Atmesti</button>
                    </form>
                <?php elseif ($selectedConsultation['Status'] == 'Priimta'): ?>
                    <!-- Pokalbių forma ir užbaigimo mygtukas -->
                    <div class="chat-container">
                        <p><strong>Žinutės:</strong></p>
                        <div class="chat-log">
                            <?= nl2br(htmlspecialchars($selectedConsultation['ChatLog'])); ?>
                        </div>
                        <form method="POST" class="chat-form">
                            <input type="hidden" name="consultation_id" value="<?= $selectedConsultation['ConsultationID'] ?>">
                            <textarea name="chat_message" rows="2" placeholder="Įveskite žinutę..." required></textarea>
                            <button type="submit" name="send_chat">Siųsti</button>
                        </form>
                        <form method="POST" class="complete-form">
                            <input type="hidden" name="consultation_id" value="<?= $selectedConsultation['ConsultationID'] ?>">
                            <button type="submit" name="complete">Žymėti kaip užbaigtą</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Uždaryti mygtukas, kad paslėptų detales -->
                <a href="answer_consultation.php" class="close-button">Uždaryti</a>
            </div>
        <?php else: ?>
            <p>Pasirinkite konsultaciją iš sąrašo, kad peržiūrėtumėte detales.</p>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
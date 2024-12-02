<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$message = '';
$messageClass = '';

// Function to translate roles into Lithuanian
function translateRole($role) {
    switch ($role) {
        case 'Unregistered':
            return 'Neregistruotas';
        case 'Registered':
            return 'Registruotas';
        case 'Consultant':
            return 'Konsultantas';
        case 'Administrator':
            return 'Administratorius';
        default:
            return $role;
    }
}

// Fetch user details and consultation history
$stmt = $pdo->prepare("SELECT Username, Email, Role, Credits FROM User WHERE UserID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if the user is a consultant and fetch their availability status
$availability = null;
if ($user['Role'] === 'Consultant') {
    $availabilityStmt = $pdo->prepare("SELECT Status FROM Consultant WHERE UserID = ?");
    $availabilityStmt->execute([$user_id]);
    $availability = $availabilityStmt->fetchColumn();
}

// Fetch consultation history for the user
$consultationsStmt = $pdo->prepare("
    SELECT c.ConsultationID, c.Date, c.Status, cons.Name as ConsultantName, c.CreditCost
    FROM Consultation c
    JOIN Consultant cons ON c.ConsultantID = cons.ConsultantID
    WHERE c.UserID = ?
    ORDER BY c.Date DESC
");
$consultationsStmt->execute([$user_id]);
$consultations = $consultationsStmt->fetchAll();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['Password'];

    try {
        $updateStmt = $pdo->prepare("UPDATE User SET Email = ?, Password = ? WHERE UserID = ?");
        $updateStmt->execute([$email, $password, $user_id]);
        $message = "Profilis sėkmingai atnaujintas!";
        $messageClass = ''; // Success style
        $user['Email'] = $email; // Update displayed email
    } catch (PDOException $e) {
        $message = "Klaida atnaujinant profilį: " . $e->getMessage();
        $messageClass = 'error'; // Error style
    }
}

// Handle availability update if the user is a consultant
if ($user['Role'] === 'Consultant' && isset($_POST['update_availability'])) {
    $availability = $_POST['availability'];
    try {
        $updateAvailabilityStmt = $pdo->prepare("UPDATE Consultant SET Status = ? WHERE UserID = ?");
        $updateAvailabilityStmt->execute([$availability, $user_id]);
        $message = "Prieinamumas sėkmingai atnaujintas!";
        $messageClass = 'success';
    } catch (PDOException $e) {
        $message = "Klaida atnaujinant prieinamumą: " . $e->getMessage();
        $messageClass = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Jūsų Profilis</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
    <h2>Jūsų Profilis</h2>

    <!-- Display Notification if Available -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Display User Info and Edit Form -->
    <div class="profile-section">
        <p><strong>Naudotojo vardas:</strong> <?= htmlspecialchars($user['Username']) ?></p>
        <p><strong>El. paštas:</strong> <?= htmlspecialchars($user['Email']) ?></p>
        <p><strong>Rolė:</strong> <?= htmlspecialchars(translateRole($user['Role'])) ?></p>
        <p><strong>Kreditai:</strong> <?= htmlspecialchars($user['Credits']) ?></p>
        
        <h3>Redaguoti Profilį</h3>
        <form method="POST" action="profile.php">
            <label for="email">El. paštas:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required>
            
            <label for="password">Naujas Slaptažodis:</label>
            <input type="password" id="password" name="password" placeholder="Jei nenorite keisti slaptažodžio įveskite esamą slaptažodį">

            <button type="submit" name="update_profile">Atnaujinti Profilį</button>
        </form>
    </div>

    <!-- Display Availability Update for Consultants -->
    <?php if ($user['Role'] === 'Consultant'): ?>
        <div class="profile-section">
            <h3>Pakeisti Prieinamumą</h3>
            <form method="POST" action="profile.php">
                <label for="availability">Prieinamumo Būsena:</label>
                <select id="availability" name="availability" required>
                    <option value="Available" <?= $availability === 'Available' ? 'selected' : '' ?>>Prieinamas</option>
                    <option value="Busy" <?= $availability === 'Busy' ? 'selected' : '' ?>>Užimtas</option>
                    <option value="Offline" <?= $availability === 'Offline' ? 'selected' : '' ?>>Neprisijungęs</option>
                </select>
                <button type="submit" name="update_availability">Atnaujinti Prieinamumą</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Display Consultation History -->
    <h3>Konsultacijų Istorija</h3>
    <?php if ($consultations): ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; border: 1px solid #ddd;">
            <thead>
                <tr>
                    <th style="border: 1px solid #ddd; padding: 10px; background-color: #f2f2f2;">Data</th>
                    <th style="border: 1px solid #ddd; padding: 10px; background-color
                    <th style="border: 1px solid #ddd; padding: 10px; background-color: #f2f2f2;">Konsultantas</th>
                    <th style="border: 1px solid #ddd; padding: 10px; background-color: #f2f2f2;">Būsena</th>
                    <th style="border: 1px solid #ddd; padding: 10px; background-color: #f2f2f2;">Kreditų Kaina</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($consultations as $consultation): ?>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?= htmlspecialchars($consultation['Date']) ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?= htmlspecialchars($consultation['ConsultantName']) ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?= htmlspecialchars($consultation['Status']) ?></td>
                        <td style="border: 1px solid #ddd; padding: 10px;"><?= htmlspecialchars($consultation['CreditCost']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Neturite konsultacijų istorijos.</p>
    <?php endif; ?>
</main>
</body>
</html>
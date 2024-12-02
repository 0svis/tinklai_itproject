<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// Check if user is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Administrator') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageClass = '';

// Handle role and credit updates (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    $new_credits = $_POST['credits'];

    $updateStmt = $pdo->prepare("UPDATE User SET Role = ?, Credits = ? WHERE UserID = ?");
    $updateStmt->execute([$new_role, $new_credits, $user_id]);

    $message = "Naudotojo informacija atnaujinta sėkmingai!";
    $messageClass = 'success';
}

// Handle user deletion (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $deleteStmt = $pdo->prepare("DELETE FROM User WHERE UserID = ?");
    $deleteStmt->execute([$user_id]);

    $message = "Naudotojas sėkmingai pašalintas.";
    $messageClass = 'success';
}

// Handle search query (GET request)
$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT UserID, Username, Role, Credits FROM User WHERE Username LIKE ?");
    $stmt->execute(['%' . $searchQuery . '%']);
} else {
    // Fetch all users if no search query is entered
    $stmt = $pdo->query("SELECT UserID, Username, Role, Credits FROM User");
}
$users = $stmt->fetchAll();

// Map database roles to Lithuanian translations
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
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Valdyti naudotojus</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<main>
    <h2>Valdyti naudotojus</h2>
    <!-- Display Notification if Available -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="manage_users.php" class="search-form">
        <label for="search">Ieškoti pagal vardą:</label>
        <input type="text" name="search" id="search" placeholder="Įveskite naudotojo vardą" value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit">Ieškoti</button>
    </form>

    <table class="user-table">
        <thead>
            <tr>
                <th>Naudotojo vardas</th>
                <th>Rolė</th>
                <th>Kreditai</th>
                <th>Veiksmai</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <!-- Display the translated role in the table -->
                        <td><?= htmlspecialchars($user['Username']) ?></td>
                        <td><?= htmlspecialchars(translateRole($user['Role'])) ?></td>
                        <td><?= htmlspecialchars($user['Credits']) ?></td>
                        <td>
                            <!-- Edit Form for Role and Credits -->
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">
                                <label for="role">Rolė:</label>
                                <select name="role" required>
                                    <option value="Unregistered" <?= $user['Role'] == 'Unregistered' ? 'selected' : '' ?>>Neregistruotas</option>
                                    <option value="Registered" <?= $user['Role'] == 'Registered' ? 'selected' : '' ?>>Registruotas</option>
                                    <option value="Consultant" <?= $user['Role'] == 'Consultant' ? 'selected' : '' ?>>Konsultantas</option>
                                    <option value="Administrator" <?= $user['Role'] == 'Administrator' ? 'selected' : '' ?>>Administratorius</option>
                                </select>
                                <label for="credits">Kreditai:</label>
                                <input type="number" name="credits" min="0" value="<?= $user['Credits'] ?>" required>
                                <button type="submit" name="update_user">Atnaujinti</button>
                            </form>
                            <!-- Delete Form -->
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">
                                <button type="submit" name="delete_user" onclick="return confirm('Ar tikrai norite ištrinti šį naudotoją?')">Ištrinti</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nerasta naudotojų su tokiu vardu.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</main>
<?php include 'includes/footer.php'; ?>
</body>
</html>
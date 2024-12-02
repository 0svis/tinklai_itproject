<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/header.php';
include 'includes/db.php';

// Check if the user is an administrator
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Administrator') {
    header("Location: login.php");
    exit();
}

$message = '';         // Initialize message
$messageClass = '';    // Initialize message CSS class

$requests = []; // Initialize an empty array to avoid undefined variable warning

try {
    // Fetch pending credit requests from the database
    $stmt = $pdo->query("SELECT RequestID, UserID, Amount FROM CreditRequests WHERE Status = 'Laukiama'");
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle adding a new consultant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_consultant'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role = 'Consultant';

    try {
        // Insert the consultant into the User table (with email)
        $stmt = $pdo->prepare("INSERT INTO User (Username, Password, Email, Role, Credits) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$username, $password, $email, $role]);

        // Retrieve the newly created UserID
        $user_id = $pdo->lastInsertId();

        // Insert the consultant into the Consultant table
        $pdo->prepare("INSERT INTO Consultant (UserID, Name, Rating, Status) VALUES (?, ?, 0, 'Prisijunges')")
            ->execute([$user_id, $_POST['name']]);

        $message = "Konsultantas pridėtas sėkmingai!";
        $messageClass = 'success';
    } catch (Exception $e) {
        $message = "Klaida pridedant konsultantą: " . $e->getMessage();
        $messageClass = 'error';
    }
}

// Handle credit request approval/decline
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_request'])) {
        $request_id = $_POST['request_id'];
        $amount = $_POST['amount'];
        $user_id = $_POST['user_id'];

        try {
            $pdo->prepare("UPDATE User SET Credits = Credits + ? WHERE UserID = ?")
                ->execute([$amount, $user_id]);

            $pdo->prepare("UPDATE CreditRequests SET Status = 'Patvirtinta' WHERE RequestID = ?")
                ->execute([$request_id]);
            $message = "Kreditų prašymas patvirtintas!";
            $messageClass = 'success';
        } catch (Exception $e) {
            $message = "Klaida tvirtinant prašymą: " . $e->getMessage();
            $messageClass = 'error';
        }
    }
    if (isset($_POST['decline_request'])) {
        $request_id = $_POST['request_id'];

        try {
            $pdo->prepare("UPDATE CreditRequests SET Status = 'Atmesta' WHERE RequestID = ?")
                ->execute([$request_id]);
            $message = "Kreditų prašymas atmestas!";
            $messageClass = 'error'; // Error style for decline
        } catch (Exception $e) {
            $message = "Klaida atmetant prašymą: " . $e->getMessage();
            $messageClass = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Admin valdymo pultas</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        button[name="approve_request"]:hover {
            background-color: #45a049;
        }
        button[name="decline_request"]:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
<main>
    <h2>Admin valdymo pultas</h2>

    <!-- Display Notification if Available -->
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Form to Add Consultant -->
    <h3>Pridėti konsultantą</h3>
    <form method="POST" action="admin_dashboard.php">
        <label for="name">Konsultanto vardas, pavardė:</label>
        <input type="text" name="name" id="name" required>
        <label for="username">Slapyvardis:</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Slaptažodis:</label>
        <input type="password" name="password" id="password" required>
        <label for="email">El. paštas:</label>
        <input type="email" name="email" id="email" required>

        <button type="submit" name="add_consultant">Pridėti konsultantą</button>
    </form>

    <!-- Pending Credit Requests Section -->
    <h3>Laukiančios kreditų užklausos</h3>
    <div class="requests-container" style="text-align: center;">
        <?php if (!empty($requests)): ?>
            <?php foreach ($requests as $request): ?>
                <div class="request-item" style="display: inline-block; text-align: center;">
                    <?php
                    // Fetch username from User table based on UserID
                    $username_stmt = $pdo->prepare("SELECT Username FROM User WHERE UserID = ?");
                    $username_stmt->execute([$request['UserID']]);
                    $username = $username_stmt->fetchColumn();
                    ?>
                    <p><strong>Slapyvardis:</strong> <?= htmlspecialchars($username) ?></p>
                    <p><strong>Naudotojo ID:</strong> <?= htmlspecialchars($request['UserID']) ?></p>
                    <p><strong>Prašoma suma:</strong> <?= htmlspecialchars($request['Amount']) ?></p>
                    
                    <form method="POST" action="admin_dashboard.php" style="display: inline-block;">
                        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['RequestID']) ?>">
                        <input type="hidden" name="amount" value="<?= htmlspecialchars($request['Amount']) ?>">
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($request['UserID']) ?>">
                        <button type="submit" name="approve_request" style="background-color: #4CAF50; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: background-color 0.3s;">Patvirtinti</button>
                        <button type="submit" name="decline_request" style="background-color: #f44336; color: white; border: none; padding: 10px 20px; cursor: pointer; transition: background-color 0.3s;">Atmesti</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Nėra laukiančių kreditų užklausų</p>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
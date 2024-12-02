<?php
// Database connection setup
include 'includes/db.php'; // File to handle database connection
include 'includes/header.php'; // File to include predefined header

$message = '';    
$messageClass = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'Registered';
    $credits = 0;
	
	$stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ? OR Email = ?");
    $stmt->execute([$username, $email]);
    $existingUser = $stmt->fetch();
	
        if ($existingUser) {
        $message = "Šis vartotojo vardas el. paštas yra jau naudojamas. Bandykite pakeisti duomenis";
        $messageClass = 'error';
    } else {
        // Insert the user data into the database with email
        $sql = "INSERT INTO User (Username, Email, Password, Role, Credits) VALUES (:username, :email, :password, :role, :credits)";
        $stmt = $pdo->prepare($sql);
			try {
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $password,
                ':role' => $role,
                ':credits' => $credits
            ]);
            $message = "Vartotojas užregistruotas sėkmingai!";
            $messageClass = ''; // Default success style
        } catch (PDOException $e) {
            $message = "Klaida: " . $e->getMessage();
            $messageClass = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registruotis</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Registruotis</h2>
    <?php if ($message): ?>
        <div class="notification <?= htmlspecialchars($messageClass) ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
	
	 <form method="POST" action="register.php">
        <label for="username">Vartotojo vardas:</label>
        <input type="text" id="username" name="username" required>
		 
		 <label for="email">Elektroninis Paštas:</label>
        <input type="email" id="email" name="email" required>
		 
		 <label for="password">Slaptažodis:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Registruotis</button>
    </form>
</body>
</html>

<?php include 'includes/footer.php'; ?>
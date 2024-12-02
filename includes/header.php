<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pagalbos portalas</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <h1>Pagalbos portalas</h1>
    <nav class="navbar">
        <div class="nav-center">
            <a href="index.php" class="nav-button">Pagrindinis</a>
            
            <?php if ($isLoggedIn): ?>
                <!-- Dropdown for Logged-in User Options -->
                <div class="dropdown">
                    <button class="dropbtn">Meniu</button>
                    <div class="dropdown-content">
                        <a href="consultation.php">Užsakyti konsultaciją</a>
                        <a href="user_consultations.php">Užsakytos konsultacijos</a>
                        <a href="view_faq.php">DUK</a>
						<a href="leaderboard.php">Konsultantai-lyderiai</a>
                    </div>
                </div>

                <?php if ($role === 'Consultant' || $role === 'Administrator'): ?>
                    <div class="dropdown">
                        <button class="dropbtn">Konsultanto meniu</button>
                        <div class="dropdown-content">
							<a href="manage_faq.php">Valdyti DUK</a>
                            <a href="view_feedback.php">Peržiūrėti atsiliepimus</a>
                            <a href="answer_consultation.php">Atsakyti į užklausas</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($role === 'Administrator'): ?>
                    <div class="dropdown">
                        <button class="dropbtn">Admin Meniu</button>
                        <div class="dropdown-content">
                            <a href="admin_dashboard.php">Administratoriaus skydelis</a>
                            <a href="manage_users.php">Valdyti naudotojus</a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Right-aligned Profile/Account Dropdown with Person Icon -->
                <div class="dropdown account-dropdown">
                    <button class="dropbtn">
                        <svg width="24" height="24" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            <path fill-rule="evenodd" d="M8 9a5 5 0 0 0-5 5v1h10v-1a5 5 0 0 0-5-5z"/>
                        </svg>
                    </button>
                    <div class="dropdown-content">
                        <a href="profile.php">Profilis</a>
                        <a href="credit_request.php">Prašyti kreditų</a>
                        <a href="logout.php" class="logout-link">Atsijungti</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="nav-button">Prisijungti</a>
                <a href="register.php" class="nav-button">Registruotis</a>
                <a href="view_faq.php" class="nav-button">DUK</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'includes/db.php';
include 'includes/header.php';

// Check if the user is logged in and their role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Consultant', 'Administrator'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['role'] === 'Administrator';
$consultant_id = null;

// If the user is not an administrator, retrieve consultant ID
if (!$is_admin) {
    $stmt = $pdo->prepare("SELECT ConsultantID FROM Consultant WHERE UserID = ?");
    $stmt->execute([$user_id]);
    $consultant = $stmt->fetch();
    $consultant_id = $consultant['ConsultantID'] ?? null;

    if (!$consultant_id) {
        echo "Konsultanto ID nerastas. Prisijunkite kaip konsultantas arba bandykite iš naujo.";
        exit();
    }
}

// Fetch consultants for the administrator to filter
$consultants = [];
if ($is_admin) {
    $stmt = $pdo->query("SELECT ConsultantID, Name FROM Consultant ORDER BY Name ASC");
    $consultants = $stmt->fetchAll();
}

// Get the selected consultant ID for filtering (default to consultant's own feedback for consultants)
$selected_consultant_id = $is_admin ? ($_GET['consultant_id'] ?? null) : $consultant_id;

// Build the query to fetch feedback
$query = "
    SELECT 
        f.Rating, 
        f.Comments, 
        u.Username AS User, 
        c.Date AS ConsultationDate, 
        co.Name AS ConsultantName
    FROM Feedback f
    JOIN User u ON f.UserID = u.UserID
    JOIN Consultation c ON f.ConsultationID = c.ConsultationID
    JOIN Consultant co ON f.ConsultantID = co.ConsultantID
";

$params = [];
if ($selected_consultant_id) {
    $query .= " WHERE f.ConsultantID = ?";
    $params[] = $selected_consultant_id;
} elseif (!$is_admin) {
    // For consultants, only show their own feedback
    $query .= " WHERE f.ConsultantID = ?";
    $params[] = $consultant_id;
}

$query .= " ORDER BY f.FeedbackID DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <title>Atsiliepimų Peržiūra</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        main {
            margin: 20px auto;
            padding: 20px;
            max-width: 1500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
        }

        table thead {
            background-color: #333;
            color: #fff;
        }

        table th, table td {
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        table tr:hover {
            background-color: #e0f7fa;
        }

        h1 {
            margin: 0;
        }

        .filter-form {
            margin-bottom: 20px;
        }

        .filter-form label {
            font-weight: bold;
        }

        .filter-form select {
            padding: 5px;
            margin-left: 10px;
        }

        .filter-form button {
            padding: 5px 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        p {
            margin: 10px 0;
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <h2><?= $is_admin ? 'Atsiliepimų Peržiūra (Administratorius)' : 'Atsiliepimai jūsų įvykdytoms konsultacijoms' ?></h2>
    <main>
        <?php if ($is_admin): ?>
            <!-- Filter form for administrators -->
            <form class="filter-form" method="GET" action="view_feedback.php">
                <label for="consultant_id">Pasirinkite konsultantą:</label>
                <select name="consultant_id" id="consultant_id">
                    <option value="">Visi konsultantai</option>
                    <?php foreach ($consultants as $consultant): ?>
                        <option value="<?= htmlspecialchars($consultant['ConsultantID']) ?>"
                            <?= $selected_consultant_id == $consultant['ConsultantID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($consultant['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtruoti</button>
            </form>
        <?php endif; ?>

        <?php if ($feedbacks): ?>
            <table>
                <thead>
                    <tr>
                        <th>Naudotojas</th>
                        <th>Įvertinimas</th>
                        <th>Komentaras</th>
                        <th>Konsultacijos data</th>
                        <th>Konsultantas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <tr>
                            <td><?= htmlspecialchars($feedback['User']) ?></td>
                            <td><?= number_format($feedback['Rating'], 2) ?> / 5</td>
                            <td><?= htmlspecialchars($feedback['Comments'] ?? 'Nepalikta jokio komentaro') ?></td>
                            <td><?= htmlspecialchars($feedback['ConsultationDate']) ?></td>
                            <td><?= htmlspecialchars($feedback['ConsultantName']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nerasta jokių atsiliepimų.</p>
        <?php endif; ?>
    </main>
</body>
</html>
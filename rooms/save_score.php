<?php
session_start();
require_once('../dbcon.php');
$db_connection = getDB();

header('Content-Type: application/json');

if (!isset($_SESSION['team_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Niet ingelogd']);
    exit;
}

$teamId   = $_SESSION['team_id'];
$teamName = $_SESSION['team_name'];
$roomId   = (int)($_POST['room_id']   ?? 0);
$timeLeft = (int)($_POST['time_left'] ?? 0);
$lives    = (int)($_POST['lives_left'] ?? 0);

if ($roomId < 1 || $roomId > 3) {
    echo json_encode(['ok' => false, 'error' => 'Ongeldige kamer']);
    exit;
}

$score = $timeLeft + ($lives * 20);

try {
    $stmt = $db_connection->prepare(
        "INSERT INTO scores (team_id, team_name, room_id, time_left, lives_left, score)
         VALUES (:tid, :tname, :rid, :tl, :ll, :sc)"
    );
    $stmt->execute([
        ':tid'   => $teamId,
        ':tname' => $teamName,
        ':rid'   => $roomId,
        ':tl'    => $timeLeft,
        ':ll'    => $lives,
        ':sc'    => $score,
    ]);
    echo json_encode(['ok' => true, 'score' => $score]);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

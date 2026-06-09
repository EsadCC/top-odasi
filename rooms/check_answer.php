<?php
session_start();
require_once('../dbcon.php');
$db_connection = getDB();

header('Content-Type: application/json');

// Alleen ingelogde teams
if (!isset($_SESSION['team_id'])) {
    echo json_encode(['ok' => false, 'error' => 'Niet ingelogd']);
    exit;
}

$riddleId   = (int)($_POST['riddle_id']  ?? 0);
$userAnswer = trim($_POST['answer']      ?? '');

if ($riddleId < 1 || $userAnswer === '') {
    echo json_encode(['ok' => false, 'error' => 'Ongeldige invoer']);
    exit;
}

// Haal het correcte antwoord op uit de database
$stmt = $db_connection->prepare("SELECT answer FROM riddles WHERE id = :id");
$stmt->execute([':id' => $riddleId]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['ok' => false, 'error' => 'Raadsel niet gevonden']);
    exit;
}

$correct = strtolower(trim($row['answer']));
$given   = strtolower($userAnswer);

echo json_encode(['ok' => true, 'correct' => ($given === $correct)]);

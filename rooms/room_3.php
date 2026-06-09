<?php
session_start();
require_once('../dbcon.php');
$db_connection = getDB();

if (!isset($_SESSION['team_id'])) {
    header('Location: ../login.php');
    exit;
}

$teamName = $_SESSION['team_name'];
$stmt     = $db_connection->query("SELECT id, riddle, hint FROM riddles WHERE roomId = 3");
$riddles  = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kamer 3 — Het Kerkhof</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body data-room-id="3">

<div class="page">

  <a class="back-link" href="dashboard.php">← Terug naar dashboard</a>

  <div class="page-header">
    <h1>💀 Het Vervloekte Kerkhof</h1>
    <p>De doden rusten hier niet. Los de raadsels op voor middernacht.</p>
    <span class="team-label">Team: <?= htmlspecialchars($teamName) ?></span>
  </div>

  <div class="timer-wrap">
    <span class="timer-label">⏱ Tijd over:</span>
    <span id="timer-display">05:00</span>
  </div>

  <div class="lives-wrap">
    Levens: <span id="lives-display"></span>
  </div>

  <div class="progress-wrap">
    <p class="progress-label" id="progress-label">0 / <?= count($riddles) ?> raadsels opgelost</p>
    <div class="progress-bar-bg">
      <div class="progress-bar-fill" id="progress-fill"></div>
    </div>
  </div>

  <div class="container">
    <?php foreach ($riddles as $index => $riddle) : ?>
    <div class="box"
         onclick="openModal(<?= $index ?>)"
         data-index="<?= $index ?>"
         data-id="<?= $riddle['id'] ?>"
         data-riddle="<?= htmlspecialchars($riddle['riddle']) ?>"
         data-hint="<?= htmlspecialchars($riddle['hint'] ?? '') ?>">
      <span class="box-icon">🔒</span>
      Raadsel <?= $index + 1 ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- WIN -->
  <div class="win-screen" id="win-screen">
    <h2>Je hebt overleefd! 🏆</h2>
    <p>Team <strong><?= htmlspecialchars($teamName) ?></strong> heeft The Dark House volledig verslagen.<br>Je bent echte overlevers.</p>
    <div class="win-score" id="win-score"></div>
    <a class="btn btn-solid" href="dashboard.php">↩ Terug naar dashboard</a>
    &nbsp;
    <a class="btn" href="add_review.php">★ Laat een review achter</a>
  </div>

  <!-- VERLIES: levens op -->
  <div class="lose-screen-lives" id="lose-screen-lives">
    <h2>Je bent gevangen... 💀</h2>
    <p>Team <strong><?= htmlspecialchars($teamName) ?></strong> heeft 3 keer een fout antwoord gegeven.<br>De doden hebben jullie opgeslokt in het kerkhof.</p>
    <a class="btn btn-solid" href="room_3.php">↩ Opnieuw proberen</a>
    &nbsp;
    <a class="btn" href="dashboard.php">← Terug naar dashboard</a>
  </div>

  <!-- VERLIES: tijd op -->
  <div class="lose-screen-time" id="lose-screen-time">
    <h2>De tijd is om... ⌛</h2>
    <p>Team <strong><?= htmlspecialchars($teamName) ?></strong> had niet genoeg tijd om te ontsnappen.<br>Het middernachtklokje heeft geslagen. De geesten nemen jullie mee.</p>
    <a class="btn btn-solid" href="room_3.php">↩ Opnieuw proberen</a>
    &nbsp;
    <a class="btn" href="dashboard.php">← Terug naar dashboard</a>
  </div>

</div>

<section class="overlay" id="overlay" onclick="closeModal()"></section>

<section class="modal" id="modal">
  <h2>Escape Room Vraag</h2>
  <p id="riddle"></p>
  <span class="hint-toggle" id="hint-toggle" onclick="toggleHint()">Toon hint</span>
  <div id="hint-text"></div>
  <input type="text" id="answer" placeholder="Typ je antwoord...">
  <div class="modal-actions">
    <button class="btn btn-solid" onclick="checkAnswer()">Verzenden</button>
    <button class="btn btn-muted" onclick="closeModal()">Sluiten</button>
  </div>
  <p id="feedback"></p>
</section>

<script src="../js/app.js"></script>
</body>
</html>

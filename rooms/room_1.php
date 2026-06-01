<?php
require_once('../dbcon.php');

try {
    $stmt    = $db_connection->query("SELECT * FROM riddles WHERE roomId = 1");
    $riddles = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Databasefout: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kamer 1 — De Verlaten Kelder</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="page">

  <a class="back-link" href="../index.php">← Terug naar de ingang</a>

  <div class="page-header">
    <h1>🕯 De Verlaten Kelder</h1>
    <p>Een donkere, vochtige kelder vol geheimen. De muren fluisteren...</p>
    <span class="team-label">Team: ...</span>
  </div>

  <!-- Levens -->
  <div class="lives-wrap">
    Levens: <span id="lives-display"></span>
  </div>

  <!-- Voortgang -->
  <div class="progress-wrap">
    <p class="progress-label" id="progress-label">0 / <?= count($riddles) ?> raadsels opgelost</p>
    <div class="progress-bar-bg">
      <div class="progress-bar-fill" id="progress-fill"></div>
    </div>
  </div>

  <!-- Raadsel-boxen -->
  <div class="container">
    <?php foreach ($riddles as $index => $riddle) : ?>
    <div class="box"
         onclick="openModal(<?= $index ?>)"
         data-index="<?= $index ?>"
         data-riddle="<?= htmlspecialchars($riddle['riddle']) ?>"
         data-answer="<?= htmlspecialchars($riddle['answer']) ?>"
         data-hint="<?= htmlspecialchars($riddle['hint'] ?? '') ?>">
      <span class="box-icon">🔒</span>
      Raadsel <?= $index + 1 ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- WIN SCHERM -->
  <div class="win-screen" id="win-screen">
    <h2>Je bent ontsnapt! 🕯</h2>
    <p>Je hebt alle raadsels van De Verlaten Kelder opgelost.<br>Durf jij de volgende kamer aan?</p>
    <a class="btn btn-solid" href="room_2.php">→ Naar De Operatiekamer</a>
  </div>

  <!-- VERLIES SCHERM -->
  <div class="lose-screen" id="lose-screen">
    <h2>Je bent gevangen... 💀</h2>
    <p>Je levens zijn op. De duisternis heeft je opgeslokt.<br>Probeer het opnieuw.</p>
    <a class="btn btn-solid" href="room_1.php">↩ Opnieuw proberen</a>
    &nbsp;
    <a class="btn" href="../index.php">← Terug naar de ingang</a>
  </div>

</div><!-- /.page -->

<!-- Overlay -->
<section class="overlay" id="overlay" onclick="closeModal()"></section>

<!-- Modal -->
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

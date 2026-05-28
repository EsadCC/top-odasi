<?php
require_once('../dbcon.php');

try {
  $stmt = $db_connection->query("SELECT * FROM riddles WHERE roomId = 3");
  $riddles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Databasefout: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kamer 3 — Het Kerkhof</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <div class="page">
    <a class="back-link" href="../index.php">← Terug naar de ingang</a>

    <div class="page-header">
      <h1>💀 Het Vervloekte Kerkhof</h1>
      <p>De doden rusten hier niet. Los de raadsels op voor middernacht.</p>
      <span class="team-label">Team: ...</span>
    </div>

    <!-- Progress bar -->
    <div class="progress-wrap">
      <p class="progress-label" id="progress-label">0 / <?php echo count($riddles); ?> opgelost</p>
      <div class="progress-bar-bg">
        <div class="progress-bar-fill" id="progress-fill"></div>
      </div>
    </div>

    <!-- Raadsel-boxen -->
    <div class="container">
      <?php foreach ($riddles as $index => $riddle) : ?>
      <div
        class="box"
        onclick="openModal(<?php echo $index; ?>)"
        data-index="<?php echo $index; ?>"
        data-riddle="<?php echo htmlspecialchars($riddle['riddle']); ?>"
        data-answer="<?php echo htmlspecialchars($riddle['answer']); ?>"
        data-hint="<?php echo htmlspecialchars($riddle['hint'] ?? ''); ?>"
      >
        <span class="box-icon">🔒</span>
        Raadsel <?php echo $index + 1; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Win scherm -->
    <div class="win-screen" id="win-screen">
      <h2>Je bent ontsnapt!</h2>
      <p>Je hebt The Dark House volledig verslagen. Goed gedaan!</p>
      <a class="room-btn" href="../index.php">↩ Terug naar de ingang</a>
    </div>
  </div>

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
      <button class="btn-submit" onclick="checkAnswer()">Verzenden</button>
      <button class="btn-close" onclick="closeModal()">Sluiten</button>
    </div>
    <p id="feedback"></p>
  </section>

  <script src="../js/app.js"></script>
</body>
</html>

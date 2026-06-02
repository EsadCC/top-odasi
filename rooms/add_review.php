<?php
session_start();
require_once('../dbcon.php');

if (!isset($_SESSION['team_id'])) {
    header('Location: ../login.php');
    exit;
}

$teamName = $_SESSION['team_name'];
$success = '';
$error = '';

$rooms = $db_connection->query("SELECT * FROM rooms ORDER BY id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamNamePost = trim($_POST['team_name'] ?? '');
    $roomId       = (int)($_POST['room_id']   ?? 0);
    $rating       = (int)($_POST['rating']    ?? 0);
    $difficulty   = (int)($_POST['difficulty'] ?? 0);
    $feedback     = trim($_POST['feedback']   ?? '');

    if ($teamNamePost === '') {
        $error = 'Vul je teamnaam in.';
    } elseif ($roomId < 1 || $roomId > 3) {
        $error = 'Selecteer een kamer.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Geef een beoordeling van 1 tot 5.';
    } elseif ($difficulty < 1 || $difficulty > 5) {
        $error = 'Geef een moeilijkheidsgraad van 1 tot 5.';
    } else {
        $stmt = $db_connection->prepare(
            "INSERT INTO reviews (team_name, room_id, rating, difficulty, feedback) VALUES (:team, :room, :rating, :diff, :fb)"
        );
        $stmt->execute([
            ':team'   => $teamNamePost,
            ':room'   => $roomId,
            ':rating' => $rating,
            ':diff'   => $difficulty,
            ':fb'     => $feedback ?: null,
        ]);
        $success = 'Je review is opgeslagen. Bedankt!';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review toevoegen — The Dark House</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-page">

  <a class="back-link" href="dashboard.php">← Terug naar dashboard</a>

  <div class="admin-header">
    <h1>★ Review toevoegen</h1>
    <p>Hoe was jouw ervaring in The Dark House?</p>
  </div>

  <?php if ($success): ?>
    <div class="form-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="form-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-card">
    <h2>Jouw review</h2>
    <form method="POST" action="">

      <div class="form-row">
        <div class="form-group">
          <label for="team_name">Teamnaam</label>
          <input type="text" id="team_name" name="team_name"
                 value="<?= htmlspecialchars($_POST['team_name'] ?? $teamName) ?>">
        </div>

        <div class="form-group">
          <label for="room_id">Kamer</label>
          <select id="room_id" name="room_id">
            <option value="">— Selecteer kamer —</option>
            <?php foreach ($rooms as $room): ?>
              <option value="<?= $room['id'] ?>"
                <?= (isset($_POST['room_id']) && $_POST['room_id'] == $room['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($room['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="rating">Beoordeling (1–5 sterren)</label>
          <select id="rating" name="rating">
            <option value="">— Kies —</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <option value="<?= $i ?>" <?= (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'selected' : '' ?>>
                <?= $i ?> <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="difficulty">Moeilijkheid (1–5)</label>
          <select id="difficulty" name="difficulty">
            <option value="">— Kies —</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <option value="<?= $i ?>" <?= (isset($_POST['difficulty']) && $_POST['difficulty'] == $i) ? 'selected' : '' ?>>
                <?= $i ?> — <?= ['Makkelijk','Redelijk','Gemiddeld','Moeilijk','Heel moeilijk'][$i-1] ?>
              </option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="feedback">Feedback <span style="color:var(--muted)">(optioneel)</span></label>
        <textarea id="feedback" name="feedback" placeholder="Wat vond je van de kamer?"><?= htmlspecialchars($_POST['feedback'] ?? '') ?></textarea>
      </div>

      <button type="submit" class="btn btn-solid">Review opslaan</button>
    </form>
  </div>

</div>
</body>
</html>

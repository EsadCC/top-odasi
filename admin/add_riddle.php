<?php
require_once('../dbcon.php');
$db_connection = getDB();

$success = '';
$error   = '';

try {
    $rooms = $db_connection->query("SELECT * FROM rooms ORDER BY id")->fetchAll();
} catch (PDOException $e) {
    die("Databasefout: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $riddle = trim($_POST['riddle']  ?? '');
    $answer = trim($_POST['answer']  ?? '');
    $hint   = trim($_POST['hint']    ?? '');
    $roomId = (int)($_POST['room_id'] ?? 0);

    if ($riddle === '') {
        $error = 'Vul een raadsel in.';
    } elseif ($answer === '') {
        $error = 'Vul een antwoord in.';
    } elseif ($roomId < 1) {
        $error = 'Selecteer een kamer.';
    } else {
        try {
            $stmt = $db_connection->prepare(
                "INSERT INTO riddles (riddle, answer, hint, roomId) VALUES (:r, :a, :h, :rid)"
            );
            $stmt->execute([
                ':r'   => $riddle,
                ':a'   => $answer,
                ':h'   => $hint ?: null,
                ':rid' => $roomId,
            ]);
            $success = 'Raadsel succesvol toegevoegd!';
        } catch (PDOException $e) {
            $error = 'Databasefout: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Raadsel toevoegen — Admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-page">

  <a class="back-link" href="../index.php">← Terug naar de ingang</a>

  <div class="admin-header">
    <h1>🔐 Raadsel toevoegen</h1>
    <p>Admin — voeg een nieuw raadsel toe aan een kamer.</p>
  </div>

  <nav class="admin-nav">
    <a class="btn btn-solid" href="show_all_riddles.php">Alle raadsels</a>
    <a class="btn" href="../rooms/add_team.php">Team aanmaken</a>
    <a class="btn" href="../rooms/show_all_teams.php">Alle teams</a>
    <a class="btn" href="../rooms/show_all_reviews.php">Alle reviews</a>
  </nav>

  <?php if ($success): ?>
    <div class="form-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="form-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-card">
    <h2>Nieuw raadsel</h2>
    <form method="POST" action="">

      <div class="form-group">
        <label for="room_id">Kamer</label>
        <select id="room_id" name="room_id">
          <option value="">— Selecteer kamer —</option>
          <?php foreach ($rooms as $room): ?>
            <option value="<?= $room['id'] ?>"
              <?= (isset($_POST['room_id']) && $_POST['room_id'] == $room['id']) ? 'selected' : '' ?>>
              Kamer <?= $room['id'] ?>: <?= htmlspecialchars($room['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="riddle">Raadsel</label>
        <textarea id="riddle" name="riddle"
                  placeholder="Typ hier het raadsel..."><?= htmlspecialchars($_POST['riddle'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label for="answer">Antwoord</label>
        <input type="text" id="answer" name="answer"
               placeholder="Het correcte antwoord"
               value="<?= htmlspecialchars($_POST['answer'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label for="hint">Hint <span style="color:var(--muted)">(optioneel)</span></label>
        <input type="text" id="hint" name="hint"
               placeholder="Een kleine aanwijzing voor spelers"
               value="<?= htmlspecialchars($_POST['hint'] ?? '') ?>">
      </div>

      <button type="submit" class="btn btn-solid">Raadsel opslaan</button>
    </form>
  </div>

</div>
</body>
</html>

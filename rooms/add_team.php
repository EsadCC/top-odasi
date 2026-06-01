<?php
require_once('../dbcon.php');

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamName = trim($_POST['team_name'] ?? '');
    // Verzamel alle niet-lege leden
    $members = array_filter(array_map('trim', $_POST['members'] ?? []), fn($m) => $m !== '');

    if ($teamName === '') {
        $error = 'Vul een teamnaam in.';
    } elseif (count($members) < 2) {
        $error = 'Voeg minimaal twee teamleden toe.';
    } else {
        try {
            // Team opslaan
            $stmt = $db_connection->prepare("INSERT INTO teams (team_name) VALUES (:name)");
            $stmt->execute([':name' => $teamName]);
            $teamId = $db_connection->lastInsertId();

            // Leden opslaan
            $stmtM = $db_connection->prepare(
                "INSERT INTO team_members (team_id, member_name) VALUES (:tid, :name)"
            );
            foreach ($members as $member) {
                $stmtM->execute([':tid' => $teamId, ':name' => $member]);
            }

            $success = "Team \"{$teamName}\" is aangemaakt met " . count($members) . " leden!";
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
  <title>Team aanmaken — The Dark House</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-page">

  <a class="back-link" href="../index.php">← Terug naar de ingang</a>

  <div class="admin-header">
    <h1>👥 Team aanmaken</h1>
    <p>Maak een nieuw team aan voor de escape room.</p>
  </div>

  <nav class="admin-nav">
    <a class="btn" href="show_all_teams.php">Alle teams</a>
    <a class="btn" href="../admin/add_riddle.php">Raadsel toevoegen</a>
    <a class="btn" href="../admin/show_all_riddles.php">Alle raadsels</a>
    <a class="btn" href="add_review.php">Review toevoegen</a>
    <a class="btn" href="show_all_reviews.php">Alle reviews</a>
  </nav>

  <?php if ($success): ?>
    <div class="form-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="form-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="form-card">
    <h2>Nieuw team</h2>
    <form method="POST" action="">

      <div class="form-group">
        <label for="team_name">Teamnaam</label>
        <input type="text" id="team_name" name="team_name"
               placeholder="bijv. De Doodgravers"
               value="<?= htmlspecialchars($_POST['team_name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Teamlid 1</label>
        <input type="text" name="members[]" placeholder="Naam van teamlid 1"
               value="<?= htmlspecialchars($_POST['members'][0] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Teamlid 2</label>
        <input type="text" name="members[]" placeholder="Naam van teamlid 2"
               value="<?= htmlspecialchars($_POST['members'][1] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Teamlid 3 <span style="color:var(--muted)">(optioneel)</span></label>
        <input type="text" name="members[]" placeholder="Naam van teamlid 3"
               value="<?= htmlspecialchars($_POST['members'][2] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Teamlid 4 <span style="color:var(--muted)">(optioneel)</span></label>
        <input type="text" name="members[]" placeholder="Naam van teamlid 4"
               value="<?= htmlspecialchars($_POST['members'][3] ?? '') ?>">
      </div>

      <button type="submit" class="btn btn-solid">Team aanmaken</button>
    </form>
  </div>

</div>
</body>
</html>

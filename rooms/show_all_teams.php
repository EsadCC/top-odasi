<?php
require_once('../dbcon.php');

try {
    // Haal alle teams op
    $teams = $db_connection->query("SELECT * FROM teams ORDER BY created_at DESC")->fetchAll();

    // Haal per team de leden op
    $stmtMembers = $db_connection->prepare(
        "SELECT member_name FROM team_members WHERE team_id = :tid"
    );
} catch (PDOException $e) {
    die("Databasefout: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Alle teams — The Dark House</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="admin-page">

  <a class="back-link" href="../index.php">← Terug naar de ingang</a>

  <div class="admin-header">
    <h1>👥 Alle teams</h1>
    <p>Overzicht van alle geregistreerde teams.</p>
  </div>

  <nav class="admin-nav">
    <a class="btn btn-solid" href="add_team.php">+ Nieuw team</a>
    <a class="btn" href="../admin/add_riddle.php">Raadsel toevoegen</a>
    <a class="btn" href="../admin/show_all_riddles.php">Alle raadsels</a>
    <a class="btn" href="add_review.php">Review toevoegen</a>
    <a class="btn" href="show_all_reviews.php">Alle reviews</a>
  </nav>

  <div class="table-wrap">
    <?php if (empty($teams)): ?>
      <div class="empty-state">Nog geen teams aangemaakt.</div>
    <?php else: ?>
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Teamnaam</th>
            <th>Teamleden</th>
            <th>Aangemaakt op</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($teams as $team):
            $stmtMembers->execute([':tid' => $team['id']]);
            $members = $stmtMembers->fetchAll(PDO::FETCH_COLUMN);
          ?>
          <tr>
            <td><?= $team['id'] ?></td>
            <td><?= htmlspecialchars($team['team_name']) ?></td>
            <td>
              <?php foreach ($members as $m): ?>
                <span class="badge"><?= htmlspecialchars($m) ?></span>
              <?php endforeach; ?>
              <?php if (empty($members)): ?><span style="color:var(--muted)">—</span><?php endif; ?>
            </td>
            <td style="color:var(--muted)"><?= date('d-m-Y H:i', strtotime($team['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
